<?php
namespace App;

class Cache
{
    private mixed $mc;
    private mixed $prefix;

    public function __construct($mc = null, $prefix = null)
    {
        $this->mc = $mc ?: Bootstrap::cache();
        $cfg = Bootstrap::config()['memcached'];
        $this->prefix = $prefix ?: (isset($cfg['prefix']) ? $cfg['prefix'] : 'app:');
    }

    private function nsKey(): string
    {
        return $this->prefix . 'news_ns';
    }

    public function getNamespaceVersion(): int
    {
        $ns = $this->mc->get($this->nsKey());
        if (!is_int($ns)) {
            $ns = 1;
            $this->mc->set($this->nsKey(), $ns);
        }
        return $ns;
    }

    public function bumpNamespace(): void
    {
        $key = $this->nsKey();
        $val = $this->mc->get($key);
        if (!is_int($val)) {
            $val = 1;
            $this->mc->set($key, $val);
        }
        $this->mc->increment($key);
    }

    private function withNs($key): string
    {
        return $this->prefix . $this->getNamespaceVersion() . ':' . $key;
    }

    public function get($key)
    {
        return $this->mc->get($this->withNs($key));
    }

    public function set($key, $value, $ttl = 300): bool
    {
        return $this->mc->set($this->withNs($key), $value, (int)$ttl);
    }

    public function abort($key): bool
    {
        return $this->mc->delete($this->withNs($key));
    }
}
