<?php

namespace Tests;

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Query\QueryBuilder;
use PHPUnit\Framework\TestCase;
use Slim\App;

abstract class BaseTest extends TestCase
{
    protected static $container;
    protected static $db;

    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    public static function setUpBeforeClass(): void
    {
        $settings = require __DIR__ . '/../config/api/settings.php';
        $app = new App(['settings' => $settings]);
        self::$container = $app->getContainer();
        $settings = self::$container->get('settings');

        require __DIR__ . '/../config/api/dependencies.php';

        self::$db = DriverManager::getConnection($settings['db']);
    }

    public static function tearDownAfterClass(): void
    {
        $files = glob(getenv('DOCUMENT_LOCAL_PATH') . '*'); // get all file names
        foreach($files as $file){ // iterate files
            if(is_file($file))
                unlink($file); // delete file
        }

        $enqueueFile = __DIR__ . '/../../runtime/queue/' . getenv('ENQUEUE_APP_STORAGE_NAME');
        if (file_exists($enqueueFile)) {
            unlink($enqueueFile);
        }

        $qb = new QueryBuilder(self::$db);
        $qb->delete('storage_document_status');
        $qb->delete('storage_document');
        $qb->execute();

        self::$db = null;
    }
}