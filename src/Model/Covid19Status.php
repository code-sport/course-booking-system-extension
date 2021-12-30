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
        if ($this->getValidFrom() != null)
        {
            $dateStart = clone $date;
            $dateStart->add($this->getValidFrom());
            $validStart = $dateStart <= $courseDate;
        }
        else
        {
            $validStart = true;
        }

        if ($this->getValidTo() != null)
        {
            $dateEnd = clone $date;
            $dateEnd->add($this->getValidTo());
            $validEnd = $courseDate <= $dateEnd;
        }
        else
        {
            $validEnd = true;
        }

        return $validStart && $validEnd;
    }

    /**
     * @return DateInterval
     */
    public function getValidFrom(): ?DateInterval
    {
        return $this->validFrom;
    }

    /**
     * @return DateInterval
     */
    public function getValidTo(): ?DateInterval
    {
        return $this->validTo;
    }

    /**
     * @return bool
     */
    public function isPlusStatus(DateTime $date, DateTime $courseDate): bool
    {
        return ($this->getPlusValidTo() != null)
            && Covid19Status::isInUpToInterval($date, $this->getPlusValidTo(), $courseDate);
    }

    /**
     * @return DateInterval|null
     */
    public function getPlusValidTo(): ?DateInterval
    {
        return $this->plusValidTo;
    }

    public static function isInUpToInterval(DateTime $dateCertificate, DateInterval $param, DateTime $courseDate): bool
    {
        $dateEnd = clone $dateCertificate;
        $dateEnd->add($param);
        return $courseDate < $dateEnd;
    }
}
