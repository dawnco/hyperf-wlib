<?php

declare(strict_types=1);

/**
 * @author Dawnc
 * @date   2022-04-16
 */

namespace WLib\Db;


use Hyperf\Database\ConnectionInterface;
use Hyperf\DbConnection\Db;


class WDbConnect
{

    protected string $poolName;

    public function __construct($poolName)
    {
        $this->poolName = $poolName;
    }

    private function getConnection(): ConnectionInterface
    {
        return Db::connection($this->poolName);
    }

    public function getLine(string $sql, array $bind = [])
    {
        return $this->getConnection()->selectOne($sql, $bind) ?: null;
    }

    public function getData(string $sql, array $bind = []): array
    {
        return $this->getConnection()->select($sql, $bind);
    }

    public function getVar(string $sql, array $bind = []): mixed
    {
        $ret = $this->getConnection()->selectOne($sql, $bind);
        if ($ret) {
            $arr = (array)$ret;
            return array_shift($arr);
        } else {
            return null;
        }
    }

    public function upsert(string $table, array $data, array $where): void
    {
        $whereData = [];
        $bind = [];
        foreach ($where as $field => $value) {
            $whereData[] = sprintf('`%s` = %s', $field, '?');
            $bind[] = $value;
        }
        $whereSql = 'WHERE ' . implode(' AND ', $whereData);

        if ($this->getVar("SELECT 1 FROM `$table` $whereSql", $bind)) {
            $this->update($table, $data, $where);
        } else {
            $this->insert($table, $data);
        }

    }

    public function update(string $table, array $data, array $where): void
    {
        $updateData = [];
        $whereData = [];

        $bind = [];
        foreach ($data as $field => $value) {
            $updateData[] = sprintf('`%s` = %s', $field, '?');
            $bind[] = $value;
        }
        $updateData = implode(', ', $updateData);

        foreach ($where as $field => $value) {
            $whereData[] = sprintf('`%s` = %s', $field, '?');
            $bind[] = $value;
        }
        $whereSql = 'WHERE ' . implode(' AND ', $whereData);

        $query = "UPDATE `{$table}` SET {$updateData} {$whereSql}";
        $this->getConnection()->update($query, $bind);
    }

    public function insert(string $table, array $data): void
    {
        $fields = [];
        $values = [];
        foreach ($data as $field => $value) {
            $fields[] = "`{$field}`";
            $values[] = '?';
        }
        $insert_fields = implode(', ', $fields);
        $insert_data = implode(', ', $values);
        $sql = "INSERT INTO `{$table}` ({$insert_fields}) values ({$insert_data})";

        $connection = $this->getConnection();
        $connection->insert($sql, array_values($data));

    }

    public function insertGetId(string $table, array $data): int
    {
        $this->insert($table, $data);
        $connection = $this->getConnection();
        $o = $connection->selectOne("SELECT LAST_INSERT_ID() as id");
        if ($o) {
            return (int)$o->id;
        } else {
            return 0;
        }
    }

    public function insertBatch(string $table, array $data): void
    {
        $insertField = [];
        foreach ($data as $value) {
            foreach ($value as $field => $row) {
                $insertField[] = "`{$field}`";
            }
            break; // 只处理一次
        }
        $insertField = implode(', ', $insertField);

        $bind = [];
        foreach ($data as $field => $value) {
            $insertData = [];
            foreach ($value as $row) {
                $insertData[] = '?';
                $bind[] = $row;
            }
            $insertDataStr[] = '(' . implode(', ', $insertData) . ')';
        }

        $query = "INSERT INTO `{$table}` ({$insertField}) values " . implode(',', $insertDataStr);

        $this->getConnection()->insert($query, $bind);
    }

    public function delete(string $table, array $where): void
    {
        $bind = [];

        foreach ($where as $field => $value) {
            $whereData[] = sprintf('`%s` = %s', $field, '?');
            $bind[] = $value;
        }
        $whereSql = 'WHERE ' . implode(' AND ', $whereData);

        $query = "DELETE FROM `{$table}` {$whereSql}";

        $this->getConnection()->delete($query, $bind);
    }

    public function execute(string $sql, array $bind = []): void
    {
        $this->getConnection()->statement($sql, $bind);
    }

}
