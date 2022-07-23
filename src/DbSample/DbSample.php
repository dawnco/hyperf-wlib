<?php

declare(strict_types=1);

namespace WLib\DbSample;


/**
 * hyperf 极简数据库快捷操作
 * Class Db
 * @package WLib\DbSample
 * @method static insert(string $table, array $data)
 * @method static insertBatch(string $table, array $data)
 * @method static delete(string $table, array $data)
 * @method static update(string $table, array $data, array $data)
 * @method static upsert(string $table, array $data, array $key)
 * @method static query(string $sql, array $bindings = [])
 * @method static execute(string $sql, array $bindings = [])
 * @method static getData(string $sql, array $bindings)
 * @method static getLine(string $query, array $bindings = [])
 * @method static getVar(string $query, array $bindings = [])
 */
class DbSample
{

    protected static array $instance = [];

    public static function __callStatic($name, $arguments)
    {
        $db = self::connection();
        return $db->{$name}(...$arguments);
    }

    public static function connection(string $poolName = 'default'): DbSampleConnection
    {
        if (!isset(self::$instance[$poolName])) {
            self::$instance[$poolName] = make(DbSampleConnection::class, [
                'hyperfDb' => \Hyperf\DB\DB::connection($poolName),
            ]);
        }
        return self::$instance[$poolName];
    }


}
