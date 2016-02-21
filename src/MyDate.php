<?php

class MyDate
{
    /**
     * @param string $start
     * @param string $end
     * @return stdClass
     */
    public static function diff($start, $end)
    {
        $dateDiff = new DateDiff($start, $end);

        return (object)array(
            'years' => $dateDiff->getYears(),
            'months' => $dateDiff->getMonths(),
            'days' => $dateDiff->getDays(),
            'total_days' => $dateDiff->getTotalDays(),
            'invert' => $dateDiff->getInvert(),
        );
    }
}
