<?php
namespace App\Cron;

use App\Bootstrap;
use App\Service\ImportService;
use Exception;

class RssImportCommand
{
    public function run(): int
    {
        try {
            $cfg = Bootstrap::config();
            $feedUrl = (string) $cfg['feeds']['ria_archive'];
            $svc = new ImportService($feedUrl);
            $res = $svc->run();
            fwrite(STDOUT, sprintf(
                "Imported total=%d, inserted=%d, updated=%d\n",
                isset($res['total']) ? (int)$res['total'] : 0,
                isset($res['inserted']) ? (int)$res['inserted'] : 0,
                isset($res['updated']) ? (int)$res['updated'] : 0
            ));
            return 0;
        } catch (Exception $e) {
            fwrite(STDERR, 'Error: ' . $e->getMessage() . "\n");
            return 1;
        }
    }
}
