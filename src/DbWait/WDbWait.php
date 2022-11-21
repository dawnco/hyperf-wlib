<?php

declare(strict_types=1);

/**
 * @author Dawnc
 * @date   2022-05-27
 */

namespace WLib\DbWait;


/**
 * Class WDbWait 再 携程中执行 sql, 避免连接池被占用
 * @package WLib\DbWait
 * @method static insert(string $table, array $data)
 * @method static insertGetId(string $table, array $data)
 * @method static insertBatch(string $table, array $data)
 * @method static insertOnReplace(string $table, array $data)
 * @method static insertOnIgnore(string $table, array $data)
 * @method static insertOnDuplicate(string $table, array $data, array $update)
 * @method static delete(string $table, array $data)
 * @method static update(string $table, array $data, array $where)
 * @method static upsert(string $table, array $data, array $where)
 * @method static execute(string $sql, array $bindings = [])
 * @method static getData(string $sql, array $bindings = [])
 * @method static getLine(string $query, array $bindings = [])
 * @method static getVar(string $query, array $bindings = [])
 */
class WDbWait
{

    protected static array $instance = [];

    public static function __callStatic($name, $arguments)
    {
        $db = self::connection();
        return $db->{$name}(...$arguments);
    }

    public static function connection(string $poolName = 'default'): WDbWaitConnect
    {
        if (!isset(self::$instance[$poolName])) {
            self::$instance[$poolName] = new WDbWaitConnect($poolName);
        }
        return self::$instance[$poolName];
    }

}
