<?php

declare(strict_types=1);

/**
 * @author Dawnc
 * @date   2022-09-09
 */

namespace WLib\Client;

use MongoDB\BSON\ObjectId;
use MongoDB\Driver\BulkWrite;
use MongoDB\Driver\Manager;
use MongoDB\Driver\Query;
use MongoDB\Driver\WriteConcern;
use WLib\WConfig;

class MongoClient
{
    protected Manager $manager;

    protected string $uri;

    /**
     * @param string $name 数据库名称
     * @param string $ip   ip地址
     * @param int    $port 端口号
     */
    public function __construct(
        protected string $name,
        protected string $host = '127.0.0.1',
        protected int $port = 27017,
        protected string $username = '',
        protected string $password = '',
    ) {
        if ($this->username && $this->password) {
            $this->uri = sprintf("mongodb://%s:%s@%s:%s", $this->username, rawurlencode($this->password), $this->host,
                $this->port);
        } else {
            $this->uri = sprintf("mongodb://%s:%s", $this->host, $this->port);
        }
        $this->manager = new Manager($this->uri);
    }

    public static function getInstance(): self
    {
        $conf = WConfig::get('mongo.default');

        $host = $conf['host'] ?? '127.0.0.1';
        $port = $conf['port'] ?? 27017;
        $database = $conf['database'] ?? 'test';
        $username = $conf['username'] ?? '';
        $password = $conf['password'] ?? '';
        return new self($database, $host, $port, $username, $password);
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * 批量插入
     * @param string $table
     * @param array  $data
     * @return array
     */
    public function insertBatch(string $table, array $data): array
    {
        $writeConcern = new WriteConcern(WriteConcern::MAJORITY, 100);
        $bulk = new BulkWrite();
        $ids = [];
        foreach ($data as $v) {
            if (!isset($v['_id'])) {
                $id = new ObjectId();
                $v['_id'] = $id;
                $ids[] = (string)$id;
            } else {
                $ids [] = (string)$v['_id'];
            }
            $bulk->insert($v);
        }
        $this->manager->executeBulkWrite("{$this->name}.$table", $bulk, $writeConcern);
        return $ids;
    }

    /**
     * 单条插入
     * @param string $table
     * @param array  $data
     * @return string 写入的 _id
     */
    public function insert(string $table, array $data): string
    {
        $ids = $this->insertBatch($table, [$data]);
        return $ids[0] ?? '';
    }

    /**
     * 更新数据
     * @param string $table
     * @param array  $data
     * @param array  $where
     * @return int 修改的行数
     */
    public function update(string $table, array $data, array $where, array $options = []): int
    {
        $writeConcern = new WriteConcern(WriteConcern::MAJORITY, 100);
        $bulk = new BulkWrite();
        $bulk->update($where, $data, $options);
        $result = $this->manager->executeBulkWrite("{$this->name}.$table", $bulk, $writeConcern);
        return $result->getModifiedCount() ?: 0;
    }

    /**
     * 删除数据
     * @param string $table
     * @param array  $where
     * @param array  $opts ['limit'=> "是否删除匹配的第一条记录 默认是"]
     * @return int 删除的行数
     */
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

    /**
     * 查询
     * @param string $table
     * @param array  $filter
     * @param array  $options
     * @return array
     */
    public function query(string $table, array $filter, array $options = []): array
    {
        $query = new Query($filter, $options);
        $cursor = $this->manager->executeQuery($this->name . "." . $table, $query);
        $cursor->setTypeMap(['root' => 'array', 'document' => 'array', 'array' => 'array']);
        return $cursor->toArray();
    }

    /**
     * 记录是否存在 存在返回 ObjectId 不存在返回 空字符串
     * @param string $table
     * @param array  $filter
     * @param array  $options
     * @return string
     */
    public function exist(string $table, array $filter, array $options = []): string
    {
        $options['limit'] = 1;
        // 返回指定字段
        $options['projection'] = ['_id' => 1];

        $query = new Query($filter, $options);
        $cursor = $this->manager->executeQuery($this->name . "." . $table, $query);
        $data = $cursor->toArray();

        return isset($data[0]) ? (string)($data[0]->_id) : "";
    }

    /**
     * @return Manager
     */
    public function getManager(): Manager
    {
        return $this->manager;
    }

}
