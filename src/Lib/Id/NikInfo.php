<?php

declare(strict_types=1);

/**
 * 通过NIK号码获取信息
 * @author Dawnc
 * @date   2022-08-11
 */

namespace WLib\Lib\Id;


/**
 * 印尼身份证信息
 */
class NikInfo
{

    protected int $birthdayTimestamp = 0;
    protected int $gender = 0;

    public array $info = [];

    /**
     * @param string $idNumber
     * @param int    $now 指定时间戳 默认当前时间
     */
    public function __construct(protected string $idNumber, protected int $now = 0)
    {

        if ($this->now == 0) {
            $this->now = time();
        }

        if (!ctype_digit($idNumber)) {
            // 不是数字
            return;
        }

        if (strlen($idNumber) != 16) {
            // 长度不对
            return;
        }
        $this->info = [
            'province' => substr($idNumber, 0, 2),
            'city' => substr($idNumber, 2, 2),
            'district' => substr($idNumber, 4, 2),
            'DD' => substr($idNumber, 6, 2),
            'MM' => substr($idNumber, 8, 2),
            'YY' => substr($idNumber, 10, 2)
        ];


        // 计算出生年月日
        $day = intval($this->info['DD']);

        if ($day <= 0 || ($day > 31 && $day < 41) || $day > 71) {
            return;
        }

        // 计算出生年月日
        $day = intval($this->info['DD']);
        if ($day <= 0 || ($day > 31 && $day < 41) || $day > 71) {
            return;
        }
        if ($day > 40) {
            $this->gender = 2; //  // 女性
            $day -= 40;
        } else {
            $this->gender = 1; //  // 男
        }

        $month = intval($this->info['MM']);
        if ($month < 1 || $month > 12) {
            return;
        }
        if (($day == 31 && in_array($month, [2, 4, 6, 9, 11])) || ($month == 2 && $day > 29)) {
            return;
        }
        $dateTime = $this->getDate();
        $year = intval($this->info['YY']);
        $_year = intval($dateTime->setTimestamp($this->now)->format("Y"));
        $century = floor($_year / 100);
        if ($year >= $_year % 100) {
            $year += ($century - 1) * 100;
        } else {
            $year += $century * 100;
        }


        $year = intval($year);
        $month = intval($month);
        $day = intval($day);

        // 非闰年
        if ($month == 2 && $day == 29 && $dateTime->setTimestamp(mktime(0, 0, 0, 2, 1, $year))->format("L") != '1') {
            return;
        }
        $this->birthdayTimestamp = mktime(0, 0, 0, $month, $day, $year);

        $tmp1 = $dateTime->setTimestamp($this->now)->format("n,j,Y");
        $t = array_map('intval', explode(",", $tmp1));
        if ($t[0] != $month || $t[1] != $day || $t[2] != $year) {
            return;
        }

    }

    /**
     * 获取生日 空字符表示串解析失败
     * @return string  格式  2001-12-39
     */
    public function getBirthday(): string
    {
        if ($this->birthdayTimestamp) {
            return $this->getDate()->setTimestamp($this->birthdayTimestamp)->format("Y-m-d");
        } else {
            return '';
        }
    }


    /**
     * 0 表示串解析失败
     * @return int
     */
    public function getGender(): int
    {
        return $this->gender;
    }

    /**
     * 0 表示串解析失败
     * @return int
     */
    public function getAge(): int
    {
        if (!$this->birthdayTimestamp) {
            return 0;
        }
        $dt0 = $this->getDate();
        $dt0->setTimestamp($this->birthdayTimestamp);
        $dt1 = $this->getDate();
        $dt1->setTimestamp($this->now);
        $interval = $dt1->diff($dt0);

        return (int)$interval->y;
    }

    protected function getDate(): \DateTime
    {
        $timezone = new \DateTimeZone('Asia/Jakarta');
        return new \DateTime('now', $timezone);
    }

}
