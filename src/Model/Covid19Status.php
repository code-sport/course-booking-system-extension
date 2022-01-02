<?php

namespace CBSE\Model;

use DateInterval;
use DateTime;

class Covid19Status
{
    private string $id;
    private string $name;
    private ?DateInterval $validFrom;
    private ?DateInterval $validTo;
    private ?DateInterval $plusValidTo;

    public function __construct(string $id, string $name, ?DateInterval $validFrom, ?DateInterval $validTo, ?DateInterval $plusValidTo)
    {
        $this->id = $id;
        $this->name = $name;
        $this->validFrom = $validFrom;
        $this->validTo = $validTo;
        $this->plusValidTo = $plusValidTo;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    public function isValid(DateTime $date, DateTime $courseDate): bool
    {
        if ($this->getValidFrom($date) != null)
        {
            $validStart = Covid19Status::isAfterTime($courseDate, $this->getValidFrom($date));
        }
        else
        {
            $validStart = true;
        }

        if ($this->getValidTo($date) != null)
        {
            $validEnd = Covid19Status::isInTime($courseDate, $this->getValidTo($date));
        }
        else
        {
            $validEnd = true;
        }

        return $validStart && $validEnd;
    }

    /**
     * @param DateTime $date
     *
     * @return DateTime|null
     */
    public function getValidFrom(DateTime $date): ?DateTime
    {
        if ($this->validFrom)
        {
            $dateReturn = clone $date;
            $dateReturn->add($this->validFrom);
            return $dateReturn;
        }
        return null;
    }

    public static function isAfterTime(DateTime $timeCheck, DateTime $afterTime): bool
    {
        return $afterTime < $timeCheck;
    }

    /**
     * @param DateTime $date
     *
     * @return DateTime|null
     */
    public function getValidTo(DateTime $date): ?DateTime
    {
        if ($this->validTo)
        {
            $dateReturn = clone $date;
            $dateReturn->add($this->validTo);
            return $dateReturn;
        }
        return null;
    }

    public static function isInTime(DateTime $timeCheck, DateTime $dateEnd): bool
    {
        return $timeCheck <= $dateEnd;
    }

    public function getValidFromFormatted(DateTime $date): string
    {
        if ($this->getValidFrom($date))
        {
            return $this->getValidFrom($date)->format(get_option('date_format'));
        }
        return '';
    }

    public function getValidToFormatted(DateTime $date): string
    {
        if ($this->getValidTo($date))
        {
            return $this->getValidTo($date)->format(get_option('date_format'));
        }
        return '';
    }

    /**
     * @param DateTime $date
     * @param DateTime $courseDate
     *
     * @return bool
     */
    public function isPlusStatus(DateTime $date, DateTime $courseDate): bool
    {
        return ($this->getPlusValidTo($date) != null) && Covid19Status::isInTime($courseDate, $this->getPlusValidTo($date));
    }

    /**
     * @return DateInterval|null
     */
    public function getPlusValidTo(DateTime $date): ?DateTime
    {
        if ($this->plusValidTo)
        {
            $dateReturn = clone $date;
            $dateReturn->add($this->plusValidTo);
            return $dateReturn;
        }
        return null;
    }

    public function getPlusValidToFormatted(DateTime $date): string
    {
        if ($this->getPlusValidTo($date))
        {
            return $this->getPlusValidTo($date)->format(get_option('date_format'));
        }
        return '';
    }
}
