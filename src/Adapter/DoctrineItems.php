<?php

declare(strict_types=1);

namespace Libero\ContentApiBundle\Adapter;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\FetchMode;
use Doctrine\DBAL\LockMode;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;
use IteratorAggregate;
use Libero\ContentApiBundle\Exception\ItemNotFound;
use Libero\ContentApiBundle\Exception\UnexpectedVersionNumber;
use Libero\ContentApiBundle\Exception\VersionNotFound;
use Libero\ContentApiBundle\Model\ItemId;
use Libero\ContentApiBundle\Model\ItemListPage;
use Libero\ContentApiBundle\Model\Items;
use Libero\ContentApiBundle\Model\ItemVersion;
use Libero\ContentApiBundle\Model\ItemVersionNumber;
use Traversable;
use function array_column;
use function array_map;
use function array_pop;
use function count;
use function rewind;
use function sprintf;

final class DoctrineItems implements IteratorAggregate, Items
{
    private const TRAVERSING_LIMIT = 100;

    private $connection;
    private $tableItems;
    private $tableVersions;

    public function __construct(Connection $connection, string $tablePrefix)
    {
        $this->connection = $connection;
        $this->tableItems = "{$tablePrefix}_items";
        $this->tableVersions = "{$tablePrefix}_versions";
    }

    public function add(ItemVersion $item) : void
    {
        $this->connection->transactional(
            function () use ($item) : void {
                try {
                    $current = $this->getCurrentVersion($item->getId(), LockMode::PESSIMISTIC_READ);
                } catch (ItemNotFound $e) {
                    $this->insertNewItem($item);

                    return;
                }

                $next = $current->next();

                if ($item->getVersion() > $next) {
                    throw new UnexpectedVersionNumber($item->getId(), $item->getVersion(), $next);
                }

                if ($item->getVersion() < $next) {
                    $this->updateVersion($item);

                    return;
                }

                $this->insertVersion($item);
            }
        );
    }

    public function remove(ItemId $id, ?ItemVersionNumber $version) : void
    {
        if (null === $version) {
            $this->connection->transactional(
                function () use ($id) : void {
                    $this->deleteItem($id);
                }
            );

            return;
        }

        $this->connection->transactional(
            function () use ($id, $version) : void {
                try {
                    $current = $this->getCurrentVersion($id, LockMode::PESSIMISTIC_READ);
                } catch (ItemNotFound $e) {
                    return;
                }

                if ($version > $current) {
                    return;
                }

                if ($version < $current) {
                    throw new UnexpectedVersionNumber($id, $version, $current);
                }

                if (1 === $current->toInt()) {
                    $this->deleteItem($id);

                    return;
                }

                $this->deleteVersion($id, $current);
            }
        );
    }

    public function get(ItemId $id, ?ItemVersionNumber $version = null) : ItemVersion
    {
        if (null === $version) {
            $sql = 'SELECT version.id, version.version, version.content, version.hash'
                ." FROM {$this->tableItems} item JOIN {$this->tableVersions} version"
                .' ON item.id = version.id AND item.current_version = version.version WHERE item.id = :id';
            $results = $this->connection->executeQuery($sql, ['id' => (string) $id]);

            $row = $results->fetch(FetchMode::ASSOCIATIVE);

            if (false === $row) {
                throw new ItemNotFound($id);
            }

            return $this->rowToItemVersion($row);
        }

        $sql = 'SELECT item.id, version.version, version.content, version.hash'
            ." FROM {$this->tableItems} item LEFT JOIN {$this->tableVersions} version"
            .' ON item.id = version.id AND version.version = :version WHERE item.id = :id';
        $results = $this->connection->executeQuery($sql, ['id' => (string) $id, 'version' => $version->toInt()]);

        $row = $results->fetch(FetchMode::ASSOCIATIVE);

        if (false === $row) {
            throw new ItemNotFound($id);
        }

        if (null === $row['version']) {
            throw new VersionNotFound($id, $version);
        }

        return $this->rowToItemVersion($row);
    }

    public function list(int $limit = 10, ?string $cursor = null) : ItemListPage
    {
        if (null !== $cursor && ((string) (int) $cursor) !== $cursor) {
            return new ItemListPage([], null);
        }

        $query = $this->connection->createQueryBuilder();

        $query
            ->addSelect('item.id')
            ->addSelect('item.sequence')
            ->from($this->tableItems, 'item')
            ->orderBy('item.sequence')
            ->setMaxResults($limit + 1);

        if ($cursor) {
            $query
                ->where($query->expr()->gte('item.sequence', ':cursor'))
                ->setParameter('cursor', (int) $cursor);
        }

        $results = $query->execute();
        $ids = $results->fetchAll(FetchMode::ASSOCIATIVE);

        if (empty($ids)) {
            return new ItemListPage([], null);
        }

        if (count($ids) > $limit) {
            $newCursor = (string) array_pop($ids)['sequence'];
        }

        $ids = array_map([ItemId::class, 'fromString'], array_column($ids, 'id'));

        return new ItemListPage($ids, $newCursor ?? null);
    }

    public function count() : int
    {
        $results = $this->connection->executeQuery("SELECT COUNT(item.id) count FROM {$this->tableItems} item");

        return (int) $results->fetch(FetchMode::COLUMN);
    }

    public function getIterator() : Traversable
    {
        $cursor = 0;

        while (true) {
            $results = $this->connection->executeQuery(
                'SELECT version.id, version.version, version.content, version.hash, item.sequence'
                ." FROM {$this->tableItems} item JOIN {$this->tableVersions} version"
                .' ON item.id = version.id AND item.current_version = version.version AND item.sequence > :sequence'
                .' ORDER BY item.sequence LIMIT '.self::TRAVERSING_LIMIT,
                ['sequence' => $cursor]
            );

            for ($i = 1; $i <= self::TRAVERSING_LIMIT; $i++) {
                $result = $results->fetch(FetchMode::ASSOCIATIVE);

                if (false === $result) {
                    return;
                }

                yield $this->rowToItemVersion($result);
            }

            if (!isset($result)) {
                return;
            }

            $cursor = $result['sequence'];
        }
    }

    public function getSchema() : Schema
    {
        $schema = new Schema();

        $table = $schema->createTable($this->tableItems);
        $table->addColumn('id', Type::STRING);
        $table->addColumn('current_version', Type::INTEGER);
        $table->addColumn('sequence', Type::INTEGER, ['autoincrement' => true]);
        $table->setPrimaryKey(['sequence']);
        $table->addUniqueIndex(['id']);

        $table = $schema->createTable($this->tableVersions);
        $table->addColumn('id', Type::STRING);
        $table->addColumn('version', Type::INTEGER);
        $table->addColumn('content', Type::BLOB);
        $table->addColumn('hash', Type::STRING);
        $table->setPrimaryKey(['id', 'version']);

        return $schema;
    }

    private function rowToItemVersion(array $row) : ItemVersion
    {
        ['id' => $id, 'version' => $version, 'content' => $content, 'hash' => $hash] = $row;

        // Some drivers, eg pdo_sqlite, unfortunately return strings rather than resources.
        // Doctrine doesn't provide an equivalent of `PDOStatement::bindColumn()` either.
        // This might cause memory problems if the content is too large.
        $content = Type::getType('blob')->convertToPHPValue($content, $this->connection->getDatabasePlatform());

        return new ItemVersion(ItemId::fromString($id), ItemVersionNumber::create($version), $content, $hash);
    }

    private function getCurrentVersion(ItemId $id, int $lockMode = LockMode::NONE) : ItemVersionNumber
    {
        $platform = $this->connection->getDatabasePlatform();

        $sql = sprintf(
            'SELECT item.current_version FROM %s WHERE item.id = :id %s',
            $platform->appendLockHint("{$this->tableItems} item", $lockMode),
            $this->getLockModeSql($lockMode)
        );

        $results = $this->connection->executeQuery($sql, ['id' => (string) $id]);
        $current = $results->fetch(FetchMode::COLUMN);

        if (false === $current) {
            throw new ItemNotFound($id);
        }

        return ItemVersionNumber::create($current);
    }

    private function deleteItem(ItemId $id) : void
    {
        $this->connection->delete($this->tableItems, ['id' => (string) $id]);
        $this->connection->delete($this->tableVersions, ['id' => (string) $id]);
    }

    private function deleteVersion(ItemId $id, ItemVersionNumber $current) : void
    {
        $this->connection->update(
            $this->tableItems,
            ['current_version' => $current->previous()],
            ['id' => (string) $id]
        );

        $this->connection->delete(
            $this->tableVersions,
            ['id' => (string) $id, 'version' => $current->toInt()]
        );
    }

    private function insertNewItem(ItemVersion $item) : void
    {
        if (1 !== $item->getVersion()->toInt()) {
            throw new UnexpectedVersionNumber(
                $item->getId(),
                $item->getVersion(),
                ItemVersionNumber::fromInt(1)
            );
        }

        rewind($item->getContent());

        $this->connection->insert(
            $this->tableVersions,
            [
                'id' => (string) $item->getId(),
                'version' => $item->getVersion()->toInt(),
                'content' => $item->getContent(),
                'hash' => $item->getHash(),
            ],
            ['content' => ParameterType::LARGE_OBJECT]
        );

        $this->connection->insert(
            $this->tableItems,
            [
                'id' => (string) $item->getId(),
                'current_version' => $item->getVersion()->toInt(),
            ]
        );
    }

    private function insertVersion(ItemVersion $item) : void
    {
        rewind($item->getContent());

        $this->connection->insert(
            $this->tableVersions,
            [
                'id' => (string) $item->getId(),
                'version' => $item->getVersion()->toInt(),
                'content' => $item->getContent(),
                'hash' => $item->getHash(),
            ],
            ['content' => ParameterType::LARGE_OBJECT]
        );

        $this->connection->update(
            $this->tableItems,
            ['current_version' => $item->getVersion()->toInt()],
            ['id' => (string) $item->getId()]
        );
    }

    private function updateVersion(ItemVersion $item) : void
    {
        rewind($item->getContent());

        $this->connection->update(
            $this->tableVersions,
            ['content' => $item->getContent(), 'hash' => $item->getHash()],
            ['id' => (string) $item->getId(), 'version' => $item->getVersion()->toInt()],
            ['content' => ParameterType::LARGE_OBJECT]
        );
    }

    private function getLockModeSql(int $lockMode = LockMode::NONE) : string
    {
        $platform = $this->connection->getDatabasePlatform();

        if ($lockMode === LockMode::PESSIMISTIC_WRITE) {
            return $platform->getWriteLockSQL();
        }

        if ($lockMode === LockMode::PESSIMISTIC_READ) {
            return $platform->getReadLockSQL();
        }

        return '';
    }
}
