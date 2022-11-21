<?php

declare(strict_types=1);

/**
 * @author Dawnc
 * @date   2022-09-01
 */

namespace WLib\DbWait;


use Hyperf\Database\ConnectionInterface;
use Hyperf\DbConnection\Db;
use WLib\Db\WDbConnect;


class WDbWaitConnect extends WDbConnect
{

    public function getLine(string $sql, array $bind = [])
    {
        return wait(function () use ($sql, $bind) {
            return parent::getLine($sql, $bind);
        });
    }

    public function getData(string $sql, array $bind = []): array
    {
        return wait(function () use ($sql, $bind) {
            return parent::getData($sql, $bind);
        });
    }

    public function getVar(string $sql, array $bind = []): mixed
    {
        return wait(function () use ($sql, $bind) {
            return parent::getVar($sql, $bind);
        });
    }

    public function upsert(string $table, array $data, array $where): void
    {
        wait(function () use ($table, $data, $where) {
            parent::upsert($table, $data, $where);
        });
    }

    public function update(string $table, array $data, array $where): void
    {
        wait(function () use ($table, $data, $where) {
            parent::update($table, $data, $where);
        });
    }

    public function insert(string $table, array $data): void
    {
        wait(function () use ($table, $data) {
            parent::insert($table, $data);
        });
    }

    public function insertGetId(string $table, array $data): int
    {
        return wait(function () use ($table, $data) {
            return parent::insertGetId($table, $data);
        });
    }

    public function insertBatch(string $table, array $data): void
    {
        wait(function () use ($table, $data) {
            parent::insertBatch($table, $data);
        });
    }

    public function insertOnReplace(string $table, array $data): int
    {
        return wait(function () use ($table, $data) {
            return parent::insertOnReplace($table, $data);
        });
    }

    public function insertOnIgnore(string $table, array $data): int
    {
        return wait(function () use ($table, $data) {
            return parent::insertOnIgnore($table, $data);
        });
    }

    public function insertOnDuplicate(string $table, array $data, array $update): int
    {
        return wait(function () use ($table, $data, $update) {
            parent::insertOnDuplicate($table, $data, $update);
        });
    }


    public function delete(string $table, array $where): void
    {
        wait(function () use ($table, $where) {
            parent::delete($table, $where);
        });
    }

    public function execute(string $sql, array $bind = []): void
    {
        wait(function () use ($sql, $bind) {
            parent::execute($sql, $bind);
        });
    }

}
