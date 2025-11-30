<?php
namespace App\Rss;

use App\Util;
use RuntimeException;
use SimpleXMLElement;

class RssFetcher
{
    private string $url;

    public function __construct(string $url)
    {
        $this->url = $url;
    }

    public function fetch(): array
    {
        $ctx = stream_context_create([
            'http' => [
                'timeout' => 15,
                'user_agent' => 'RIA-RSS-Aggregator/1.0',
            ]
        ]);
        $xmlStr = @file_get_contents($this->url, false, $ctx);
        if ($xmlStr === false) {
            throw new RuntimeException('Failed to fetch RSS: ' . $this->url);
        }
        $xml = @simplexml_load_string($xmlStr);
        if (!$xml) {
            throw new RuntimeException('Invalid RSS XML');
        }
        return $this->parse($xml);
    }

    private function parse(SimpleXMLElement $xml): array
    {
        $items = [];
        $nsContent = 'https://purl.org/rss/1.0/modules/content/';
        foreach ($xml->channel->item as $item) {
            $guid = (string)$item->guid;
            if ($guid === '') {
                $guid = (string)$item->link;
            }
            $title = (string)$item->title;
            $link = (string)$item->link;
            $description = (string)$item->description;
            $pubDate = Util::parseRssDate((string)$item->pubDate);

            $content = null;
            $contentNode = $item->children($nsContent);
            if ($contentNode && isset($contentNode->encoded)) {
                $content = (string)$contentNode->encoded;
            }

            $categories = [];
            foreach ($item->category as $cat) {
                $categories[] = trim((string)$cat);
            }

            $imageUrl = null;
            if (isset($item->enclosure)) {
                $attrs = $item->enclosure->attributes();
                if ($attrs && isset($attrs['url'])) {
                    $imageUrl = (string)$attrs['url'];
                }
            }

            $media = $item->children('https://search.yahoo.com/mrss/');
            if ($imageUrl === null && $media && isset($media->content)) {
                $attrs = $media->content->attributes();
                if ($attrs && isset($attrs['url'])) {
                    $imageUrl = (string)$attrs['url'];
                }
            }

            $items[] = [
                'guid' => $guid,
                'title' => $title,
                'link' => $link,
                'description' => $description,
                'content' => $content,
                'image_url' => $imageUrl,
                'pub_date' => $pubDate,
                'categories' => $categories,
            ];
        }
        return $items;
    }

}
