<?php
namespace App\Setup;

use App\Bootstrap;
use Exception;

class DatabaseSetupCommand
{
    public function run(array $argv): int
    {
        $args = $argv;
        array_shift($args);

        $allowCreate = false;
        $schemaPath = realpath(dirname(__DIR__, 3) . '/db/schema.sql');

        foreach ($args as $a) {
            if ($a === '--help' || $a === '-h') {
                $this->printUsage();
                return 0;
            }
            if ($a === '--create-db') {
                $allowCreate = true;
                continue;
            }
            if (str_starts_with($a, '--schema=')) {
                $schemaPath = substr($a, 9);
            }
        }

        try {
            $config = Bootstrap::config();
            $init = new DatabaseInitializer($config);

            fwrite(STDOUT, "Проверка существования БД... ");
            $exists = $init->databaseExists();
            fwrite(STDOUT, $exists ? "найдена\n" : "не найдена\n");

            if (!$exists) {
                if (!$allowCreate) {
                    fwrite(STDERR, "База данных отсутствует. Запустите с флагом --create-db для её создания.\n");
                    return 2;
                }
                fwrite(STDOUT, "Создание БД... ");
                $init->createDatabaseIfMissing();
                fwrite(STDOUT, "готово\n");
            }

            fwrite(STDOUT, "Применение схемы: $schemaPath\n");
            $init->applySchema($schemaPath);
            fwrite(STDOUT, "Схема применена успешно.\n");
            return 0;
        } catch (Exception $e) {
            fwrite(STDERR, "Ошибка инициализации: " . $e->getMessage() . "\n");
            return 1;
        }
    }

    public function printUsage(): void
    {
        fwrite(STDOUT, "\nИнициализация БД для RSS-агрегатора\n\n".
            "Использование:\n".
            "  php bin/setup.php [--create-db] [--schema=path/to/schema.sql]\n\n".
            "Опции:\n".
            "  --create-db        Создать базу данных, если она отсутствует (CREATE DATABASE).\n".
            "  --schema=PATH      Путь к SQL-схеме (по умолчанию db/schema.sql).\n\n");
    }
}
