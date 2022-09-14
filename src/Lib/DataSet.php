<?php

declare(strict_types=1);

/**
 * @author Dawnc
 * @date   2022-08-23
 */

namespace WLib\Lib;

/**
 * 数据集合
 */
class DataSet
{

    private array $data = [];

    /**
     * 获取配置值
     * @param int|string|null $key
     * @param mixed|null      $default
     * @return mixed
     */
    public function get(int|string|null $key, mixed $default = null): mixed
    {

        if (is_null($key)) {
            return $this->data;
        }

        if (is_int($key)) {
            return $this->data[$key] ?? $default;
        }

        if (!str_contains($key, '.')) {
            return $this->data[$key] ?? $default;
        }

        $conf = $this->data;
        foreach (explode(".", $key) as $n) {
            if (isset($conf[$n])) {
                $conf = $conf[$n];
            } else {
                return $default;
            }
        }
        return $conf;
    }

    /**
     * 设置值
     * @param int|string $key 可以点分割 比如  app.redis
     * @param mixed      $value
     * @return void
     */
    public function set(int|string $key, mixed $value): void
    {


        if (is_int($key)) {
            $this->data[$key] = $value;
            return;
        }

        if (!str_contains($key, ".")) {
            $this->data[$key] = $value;
            return;
        } else {
            $keys = explode(".", $key);
        }

        $array = &$this->data;
        while (count($keys) > 1) {
            $key = array_shift($keys);
            if (!isset($array[$key]) || !is_array($array[$key])) {
                $array[$key] = [];
            }
            $array = &$array[$key];
        }
        $array[array_shift($keys)] = $value;

    }

    public function incr(int|string|null $key, int|float $step = 1): void
    {
        $val = $this->get($key);
        if ($val) {
            $this->set($key, $val + $step);
        } else {
            $this->set($key, $step);
        }
    }

    public function getData(): array
    {
        return $this->data;
    }

    /**
     * 写入csv
     * 如果 data 是二维数组 转换为 csv文件格式字符串
     * @param string $file  写入的文件名
     * @param array  $title 格式 ['field'=>"名称", 'field2'=>"名称2"]
     * @return void
     */
    public function csvFile(string $file, array $title = []): void
    {
        $this->data2csvFile($this->data, $file, $title);
    }

    /**
     * @param array  $data
     * @param array  $title
     * @param string $file
     * @return void
     */
    public function data2csvFile(array $data, string $file, array $title = []): void
    {


        $fp = fopen($file, 'w');

        if (!$title) {
            $title = array_keys($data[0]);
            $keys = $title;
        } else {
            $keys = array_keys($title);
        }

        fputcsv($fp, array_values($title));

        foreach ($data as $row) {
            $fields = [];
            foreach ($keys as $k) {
                $fields[] = $row[$k] ?? '';
            }
            fputcsv($fp, $fields);
        }
        fclose($fp);
    }
}
