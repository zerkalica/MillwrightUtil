<?php
namespace Millwright\Util;

/**
 * Date hacks and fixes
 */
final class DateUtil
{
    const SQL_DATE      = 'Y-m-d';
    const SQL_DATE_TIME = 'Y-m-d H:i:s';
    const INTL_DAY_WEEK_YEAR = 'dd MM YYYY';

    /**
     * Set or modify date time object
     *
     * @param \DateTime|null &$to
     * @param \DateTime|null $from
     *
     * @return \DateTime
     */
    public static function setDateTime(\DateTime &$to = null, \DateTime $from = null)
    {
        if (null === $from) {
            $to = null;
        } else {
            if (null === $to) {
                $to = PhpUtil::cloneObject($from);
            } else {
                $to->setTimestamp($from->getTimestamp());
                $to->setTimezone($from->getTimezone());
            }
        }

        return $to;
    }

    /**
     * Intl date format lacalized
     *
     * @param  \DateTime $date
     * @param  string $pattern @see http://userguide.icu-project.org/formatparse/datetime
     * @return string
     */
    public static function intlFormat(\DateTime $date, $pattern = self::INTL_DAY_WEEK_YEAR)
    {
        $df = new \IntlDateFormatter(null, \IntlDateFormatter::FULL, \IntlDateFormatter::FULL, null, \IntlDateFormatter::GREGORIAN, $pattern);

        return $df->format($date);
    }

    /**
     * Explode date
     *
     * @param \DateTime $date
     *
     * @return array (year, month, day, hour, minute, second)
     */
    public static function explodeDate(\DateTime $date)
    {
        return explode('-', $date->format('Y-m-d-H-i-s'));
    }

    /**
     * Add time interval
     *
     * @param \DateTime $date
     * @param integer   $hour
     * @param integer   $minute
     * @param integer   $second
     *
     * @return \DateTime
     */
    public static function addTimeInterval(\DateTime $date, $hour = 0, $minute = 0, $second = 0)
    {
        $newDate  = PhpUtil::cloneObject($date);
        $interval = new \DateInterval(sprintf('PT%sH%sM%sS', $hour, $minute, $second));
        $newDate->add($interval);

        return $newDate;
    }

    /**
     * Add date interval
     *
     * @param \DateTime $date
     * @param integer   $year
     * @param integer   $month
     * @param integer   $day
     *
     * @return \DateTime
     */
    public static function addDateInterval(\DateTime $date, $year = 0, $month = 0, $day = 0)
    {
        $newDate  = PhpUtil::cloneObject($date);
        $interval = new \DateInterval(sprintf('P%sY%sM%sD', $year, $month, $day));
        $newDate->add($interval);

        return $newDate;
    }

    /**
     * Get sql-formatted date
     *
     * @param \DateTime $date
     *
     * @return string
     */
    public static function sqlDate(\DateTime $date)
    {
        return $date->format(self::SQL_DATE);
    }

    /**
     * Get sql-formatted date time
     *
     * @param \DateTime $date
     *
     * @return string
     */
    public static function sqlDateTime(\DateTime $date)
    {
        return $date->format(self::SQL_DATE_TIME);
    }

    /**
     * Get sql-formatted date time, aligned to day start or end
     *
     * @param \DateTime $date
     * @param boolean   $isStart is start of the day
     *
     * @return string
     */
    public static function sqlDateTimeAligned(\DateTime $date, $isStart)
    {
        $date = clone $date;
        if ($isStart) {
            $date->setTime(0, 0, 0);
        } else {
            $date->setTime(23, 59, 59);
        }

        return self::sqlDateTime($date);
    }
}
