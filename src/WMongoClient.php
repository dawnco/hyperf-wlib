<?php

declare(strict_types=1);

/**
 * @author Dawnc
 * @date   2022-09-09
 */

namespace WLib;

use MongoDB\BSON\ObjectId;
use MongoDB\Driver\BulkWrite;
use MongoDB\Driver\Manager;
use MongoDB\Driver\Query;
use MongoDB\Driver\WriteConcern;

class WMongoClient
{
    protected Manager $manager;

    public function __construct(protected string $name, protected string $ip = '127.0.0.1', protected int $port = 27017)
    {
        $this->manager = new Manager("mongodb://$ip:$port");
    }

    /**
     * @param string $table
     * @param array  $data
     * @return array 插入的 id
     */
    public function insertBatch(string $table, array $data): array
    {
        $writeConcern = new WriteConcern(0, 100);
        $bulk = new BulkWrite();
        $ids = [];
        foreach ($data as $v) {
            if (!isset($v['_id'])) {
                $id = new ObjectId();
                $ids[] = $id->__toString();
                $v['_id'] = $id;
            }
            $bulk->insert($v);
        }
        $this->manager->executeBulkWrite("{$this->name}.$table", $bulk, $writeConcern);
        return $ids;
    }

    public function insert(string $table, array $data): string
    {
        $ids = $this->insertBatch($table, [$data]);
        return $ids[0] ?? '';
    }

    public function update(string $table, array $data, array $where)
    {
        $writeConcern = new WriteConcern(WriteConcern::MAJORITY, 100);
        $bulk = new BulkWrite();
        $bulk->update($data, $where);
        $this->manager->executeBulkWrite("{$this->name}.$table", $bulk, $writeConcern);
    }

    public function delete(
        string $table,
        array $where,
        array $opts = []
    ): int {
        $writeConcern = new WriteConcern(WriteConcern::MAJORITY, 100);
        $bulk = new BulkWrite();

        if (!isset($opts['limit'])) {
            //只删一条
            $opts['limit'] = true;
        }

        $bulk->delete($where, $opts);
        $result = $this->manager->executeBulkWrite("{$this->name}.$table", $bulk, $writeConcern);
        return $result->getDeletedCount();
    }

    public function query($table, array $filter, array $options = []): array
    {
        $query = new Query($filter, $options);
        $cursor = $this->manager->executeQuery($this->name . "." . $table, $query);
        $cursor->setTypeMap(['root' => 'array', 'document' => 'array', 'array' => 'array']);
        return $cursor->toArray();
    }
}
