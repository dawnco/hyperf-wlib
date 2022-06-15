<?php

declare(strict_types=1);

/**
 * @author Hi Developer
 * @date   2022-04-16
 */

namespace WLib\DbSample;


use Hyperf\DB\DB;

class DbSampleConnection
{

    /**
     * @var DB
     */
    protected $hyperfDb;

    public function __construct(DB $hyperfDb)
    {
        $this->hyperfDb = $hyperfDb;
    }

    public function getLine(string $sql, array $bind = []): \stdClass|null
    {
        $ret = $this->hyperfDb->fetch($sql, $bind);
        return $ret ? (object)$ret : null;
    }

    public function getData(string $sql, array $bind = []): array
    {
        $ret = $this->hyperfDb->query($sql, $bind) ?: [];
        foreach ($ret as $k => $v) {
            if (!is_object($v)) {
                $ret[$k] = (object)$v;
            }
        }
        return $ret;
    }

    public function getVar(string $sql, array $bind = []): mixed
    {
        $ret = $this->hyperfDb->fetch($sql, $bind);
        return $ret ? array_shift($ret) : null;
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
            array_push($whereData, sprintf('`%s` = %s', $field, '?'));
            $bind[] = $value;
        }
        $whereSql = 'WHERE ' . implode(' AND ', $whereData);

        $query = "UPDATE `{$table}` SET {$updateData} {$whereSql}";
        $this->hyperfDb->execute($query, $bind);
    }

    public function insert(string $table, array $data)
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

        $this->hyperfDb->insert($sql, array_values($data));
    }

    public function insertGetId(string $table, array $data): int
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

        $lastId = $this->hyperfDb->insert($sql, array_values($data));
        return $lastId ?: 0;
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

        $this->hyperfDb->execute($query, $bind);
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

        $this->hyperfDb->execute($query, $bind);
    }

    public function execute(string $sql, array $bind = []): void
    {
        $this->hyperfDb->execute($sql, $bind);
    }

}
