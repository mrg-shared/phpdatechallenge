<?php

class Date
{
    /**
     * @var string
     */
    private $date;

    /**
     * @var string
     */
    private $dateFormat;

    /**
     * @var int
     */
    private $year;

    /**
     * @var int
     */
    private $month;

    /**
     * @var int
     */
    private $day;

    /**
     * @param string $date
     * @param string $dateFormat
     */
    public function __construct($date, $dateFormat = 'YYYY/MM/DD')
    {
        $this->date = $date;
        $this->dateFormat = $dateFormat;

        $this->breakdownDateIntoYearMonthAndDay();
    }

    private function breakdownDateIntoYearMonthAndDay()
    {
        $datePartials = str_split($this->date);
        $dateFormatMap = str_split($this->dateFormat);

        $year = $month = $day = '';
        foreach ($dateFormatMap as $position => $datePartial) {
            switch ($datePartial) {
                case 'Y':
                    $year .= $datePartials[$position];
                    break;
                case 'M':
                    $month .= $datePartials[$position];
                    break;
                case 'D':
                    $day .= $datePartials[$position];
                    break;
            }
        }

        $this->year = $year !== '' ? (int)$year : null;
        $this->month = $month !== '' ? (int)$month : null;
        $this->day = $day !== '' ? (int)$day : null;

        $this->throwExceptionIfNotValidDate();
    }

    private function throwExceptionIfNotValidDate()
    {
        $this->throwExceptionIfNotValidYear();
        $this->throwExceptionIfNotValidMonth();
        $this->throwExceptionIfNotValidDay();
    }

    private function throwExceptionIfNotValidYear()
    {
        if (is_null($this->year)) {
            throw new Exception('Year cannot be extracted');
        }
    }

    private function throwExceptionIfNotValidMonth()
    {
        if (is_null($this->month)) {
            throw new Exception('Month cannot be extracted');
        }
        if ($this->month > 12 || $this->month < 1) {
            throw new Exception('Invalid month');
        }
    }

    private function throwExceptionIfNotValidDay()
    {
        if (is_null($this->day)) {
            throw new Exception('Day cannot be extracted');
        }

        if (
            $this->day < 1 ||
            ($this->month === 2 && !self::isLeapYear($this->year) && $this->day > 28) ||
            ($this->month === 2 && self::isLeapYear($this->year) && $this->day > 29) ||
            (in_array($this->month, self::getMonthsWith30Days()) && $this->day > 30) ||
            (in_array($this->month, self::getMonthsWith31Days()) && $this->day > 31)
        ) {
            throw new Exception('Invalid day');
        }
    }

    /**
     * @param int $year
     * @param int $month
     * @return int
     */
    public static function getDaysInMonth($year, $month)
    {
        if (in_array($month, self::getMonthsWith30Days())) {
            return 30;
        } elseif (in_array($month, self::getMonthsWith31Days())) {
            return 31;
        } elseif ($month === 2) {
            return self::isLeapYear($year) ? 29 : 28;
        }
    }

    /**
     * @return array
     */
    public static function getMonthsWith30Days()
    {
        return array(4, 6, 9, 11);
    }

    /**
     * @return array
     */
    public static function getMonthsWith31Days()
    {
        return array(1, 3, 5, 7, 8, 10, 12);
    }

    /**
     * @param int $year
     * @return bool
     */
    public static function isLeapYear($year)
    {
        if ($year % 4 !== 0) {
            return false;
        } elseif ($year % 100 !== 0) {
            return true;
        } elseif ($year % 400 === 0) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * @return int
     */
    public function getYear()
    {
        return $this->year;
    }

    /**
     * @return int
     */
    public function getMonth()
    {
        return $this->month;
    }

    /**
     * @return int
     */
    public function getDay()
    {
        return $this->day;
    }
}
