<?php

declare(strict_types=1);

/**
 * @author Dawnc
 * @date   2021-11-06
 */

namespace WLib;

class WDate
{
    private static array $map = [
        'uk' => 'Europe/London',
        'cn' => 'Asia/Shanghai',
        'id' => 'Asia/Jakarta',
        'ng' => 'Africa/Lagos',
        'in' => 'Asia/Kolkata', // 印度
        'mx' => 'America/Mexico_City', //墨西哥
    ];

    private \DateTime $dateTime;

    public function __construct(string $countryISO2 = '')
    {
        $this->dateTime = new \DateTime();
        if ($countryISO2) {
            $this->setCountry($countryISO2);
        }
    }

    public static function getInstance($country = 'cn'): static
    {
        return new static($country);
    }

    public function setCountry(string $countryISO2): static
    {
        $this->dateTime->setTimezone(new \DateTimeZone(self::$map[$countryISO2]));
        return $this;
    }

    /**
     * @param int $timestamp
     */
    public function setTimestamp(int $timestamp): static
    {
        $this->dateTime->setTimestamp($timestamp);
        return $this;
    }

    /**
     * 返回对应时区的 Ymd 格式
     * @param int    $timestamp
     * @param string $zone
     * @return string
     */
    public function format($format = 'Y-m-d H:i:s'): string
    {
        return $this->dateTime->format($format);
    }

    /**
     * 获取时间戳
     * @return int
     */
    public function getTimestamp(): int
    {
        return $this->dateTime->getTimestamp();
    }

    /**
     * @param string $date 2021-01-01 or 2021-01-01 23:25:56
     * @return $this
     */
    public function setDateTimeStr(string $datetime): static
    {
        $d = str_replace([" ", ":"], "-", $datetime);
        $a = explode("-", $d);

        $this->dateTime->setDate((int)$a[0], (int)$a[1], (int)$a[2]);

        if (count($a) > 3) {
            $this->dateTime->setTime((int)$a[3], (int)$a[4], (int)$a[5]);
        } else {
            $this->dateTime->setTime(0, 0, 0);
        }
        return $this;
    }

    /**
     * 设置为今天开始的时间
     */
    public function dayBegin(): static
    {
        $this->setDateTimeStr($this->format("Y-m-d"));
        return $this;
    }

    /**
     * 设置为今天结束的时间
     */
    public function dayEnd(): static
    {
        $this->setDateTimeStr($this->format("Y-m-d 23:59:59"));
        return $this;
    }

    /**
     * @param string $modifier
     * @return $this
     */
    public function modify(string $modifier): static
    {
        $this->dateTime->modify($modifier);
        return $this;
    }

    public static function setDefaultTimezone(string $countryISO2): void
    {
        date_default_timezone_set(self::$map[$countryISO2]);
    }

}
