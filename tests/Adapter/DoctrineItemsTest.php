<?php

declare(strict_types=1);

namespace tests\Libero\ContentApiBundle\Adapter;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Libero\ContentApiBundle\Adapter\DoctrineItems;
use Libero\ContentApiBundle\Model\Items;
use Libero\ContentApiBundle\Test\ItemsTestCase;
use function md5;
use function uniqid;

/**
 * @group database
 */
final class DoctrineItemsTest extends ItemsTestCase
{
    /** @var Connection */
    private static $connection;
    /** @var DoctrineItems */
    private static $doctrineItems;

    /**
     * @beforeClass
     */
    public static function setUpSchema() : void
    {
        self::$connection = DriverManager::getConnection(['url' => $_SERVER['DB_URI'] ?? 'sqlite://memory']);

        self::$doctrineItems = new DoctrineItems(self::$connection, 'test_'.md5(uniqid('', true)));

        foreach (self::$doctrineItems->getSchema()->toSql(self::$connection->getDatabasePlatform()) as $query) {
            self::$connection->exec($query);
        }
    }

    /**
     * @after
     */
    public function cleanTables() : void
    {
        foreach (self::$doctrineItems->getSchema()->getTables() as $table) {
            self::$connection->exec(self::$connection->getDatabasePlatform()->getTruncateTableSQL($table->getName()));
        }
    }

    /**
     * @afterClass
     */
    public static function dropSchema() : void
    {
        foreach (self::$doctrineItems->getSchema()->toDropSql(self::$connection->getDatabasePlatform()) as $query) {
            self::$connection->exec($query);
        }
    }

    protected function createItems() : Items
    {
        return self::$doctrineItems;
    }
}
