<?php

class DateDiff
{
    /**
     * @var Date
     */
    private $start;

    /**
     * @var Date
     */
    private $end;

    /**
     * @var int
     */
    private $years = 0;

    /**
     * @var int
     */
    private $months = 0;

    /**
     * @var int
     */
    private $days = 0;

    /**
     * @var int
     */
    private $totalDays = 0;

    /**
     * @var bool
     */
    private $inverted = false;

    /**
     * @param string $start
     * @param string $end
     */
    public function __construct($start, $end)
    {
        $this->setStartAndEnd($start, $end);

        $this->calculateDiff();
    }

    private function setStartAndEnd($start, $end)
    {
        $realStartDate = min(array($start, $end));
        $realEndDate = max(array($start, $end));
        $this->start = new Date($realStartDate);
        $this->end = new Date($realEndDate);
        $this->inverted = $start > $end;
    }

    private function calculateDiff()
    {
        $start = $this->start;
        $end = $this->end;

        $occurancesOf29Feb = 0;
        for ($year = $start->getYear(); $year <= $end->getYear(); $year++) {
            $is29FebIncluded = Date::isLeapYear($year) ? $this->is29FebIncludedInYear($year) : false;
            if ($is29FebIncluded) {
                $occurancesOf29Feb++;
            }

            if ($this->addFullYear($year)) {
                continue;
            }

            for ($month = 1; $month <= 12; $month++) {
                if ($this->isMonthToBeIncluded($year, $month)) {
                    $this->addMonthAndDays($year, $month);
                }
            }
        }
    }

    /**
     * @param int $year
     * @return bool
     */
    private function addFullYear($year)
    {
        $start = $this->start;
        $end = $this->end;

        if ($year > $start->getYear() && $year < $end->getYear()) {
            $this->years++;
            $this->totalDays += Date::isLeapYear($year) ? 366 : 365;
            return true;
        }

        if ($year > $start->getYear() && self::formatDateForIso(array($start->getMonth(), $start->getDay())) === self::formatDateForIso(array($end->getMonth(), $end->getDay()))) {
            $this->years++;
            $this->totalDays += Date::isLeapYear($year) ? 366 : 365;
            return true;
        }

        return false;
    }

    /**
     * @param int $year
     * @param int $month
     * @return bool
     */
    private function isMonthToBeIncluded($year, $month)
    {
        $start = $this->start;
        $end = $this->end;
        
        if (
            self::formatDateForIso(array($year, $month)) >= self::formatDateForIso(array($start->getYear(), $start->getMonth())) &&
            self::formatDateForIso(array($year, $month)) <= self::formatDateForIso(array($end->getYear(), $end->getMonth()))
        ) {
            return true;
        }
        return false;
    }

    /**
     * @param int $year
     * @param int $month
     * @return bool
     */
    private function addMonthAndDays($year, $month)
    {
        $start = $this->start;
        $end = $this->end;
        
        if (
            self::formatDateForIso(array($year, $month)) > self::formatDateForIso(array($start->getYear(), $start->getMonth())) &&
            self::formatDateForIso(array($start->getMonth(), $start->getDay())) === self::formatDateForIso(array($end->getMonth(), $end->getDay()))
        ) {
            $this->months++;
            $this->totalDays += Date::getDaysInMonth($year, $month);
            return true;
        }

        $totalDaysBefore = $this->totalDays;
        
        if ($year === $start->getYear()) {
            if ($month === $start->getMonth()) {
                // start and end dates are within the same month and year
                if ($year === $end->getYear() && $month === $end->getMonth()) {
                    $daysDifference = $end->getDay() - $start->getDay();
                    $this->days += $daysDifference;
                    $this->totalDays += $daysDifference;

                // start and end are in different months
                } else {
                    $daysDifference = Date::getDaysInMonth($year, $month) - $start->getDay();
                    $this->days += $daysDifference;
                    $this->totalDays += $daysDifference;
                }

            } elseif ($month > $start->getMonth()) {
                $this->months++;
                $this->totalDays += Date::getDaysInMonth($year, $month);
            }

        } elseif ($year === $end->getYear()) {
            if ($month === $end->getMonth()) {
                $daysDifference = Date::getDaysInMonth($year, $month) - $end->getDay();
                $this->days += $daysDifference;
                $this->totalDays += $daysDifference;

            } elseif ($month < $end->getMonth()) {
                $this->months++;
                $this->totalDays += Date::getDaysInMonth($year, $month);
            }
        }

        return $totalDaysBefore < $this->totalDays ? true : false;
    }

    /**
     * @param int $year
     * @return bool
     */
    private function is29FebIncludedInYear($year)
    {
        $start = $this->start;
        $end = $this->end;

        if ($year === $start->getYear()) {
            // dates diff is for the same calendar year
            if ($year === $end->getYear()) {
                if ($this->isAfter29Feb($start)) {
                    return false;
                } elseif ($this->isBefore29Feb($end)) {
                    return false;
                }

            // dates diff is in two different years
            } else {
                if ($this->isAfter29Feb($start)) {
                    return false;
                }
            }

        // dates diff is in two different years
        } elseif ($year === $end->getYear()) {
            if ($this->isBefore29Feb($end)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param Date $date
     * @return bool
     */
    private function isAfter29Feb(Date $date)
    {
        return $this->formatDateForIso(array($date->getMonth, $date->getDay)) > '0229';
    }

    /**
     * @param Date $date
     * @return bool
     */
    private function isBefore29Feb(Date $date)
    {
        return $this->formatDateForIso(array($date->getMonth, $date->getDay)) < '0229';
    }

    /**
     * @param array $datePartials
     * @return string
     */
    private function formatDateForIso(array $datePartials)
    {
        $isoFormatted = '';
        foreach ($datePartials as $datePartial) {
            $isoFormatted .= str_pad($datePartial, 2, '0', STR_PAD_LEFT);
        }
        return $isoFormatted;
    }

    /**
     * @return int
     */
    public function getYears()
    {
        return $this->years;
    }

    /**
     * @return int
     */
    public function getMonths()
    {
        return $this->months;
    }

    /**
     * @return int
     */
    public function getDays()
    {
        return $this->days;
    }

    /**
     * @return int
     */
    public function getTotalDays()
    {
        return $this->totalDays;
    }

    /**
     * @return bool
     */
    public function isInverted()
    {
        return $this->inverted;
    }
}
