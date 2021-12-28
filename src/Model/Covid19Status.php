<?php

namespace CBSE\Admin\User\Model;

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

    public function isValid(DateTime $date): bool
    {
        $today = new DateTime("today");

        if ($this->getValidFrom() != null)
        {
            $dateStart = clone $date;
            $dateStart->add($this->getValidFrom());
            $validStart = $dateStart <= $today;
        }
        else
        {
            $validStart = true;
        }

        if ($this->getValidTo() != null)
        {
            $dateEnd = clone $date;
            $dateEnd->add($this->getValidTo());
            $validEnd = $today <= $dateEnd;
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
    public function isPlusStatus(DateTime $date): bool
    {
        return ($this->getPlusValidTo() != null)
            && Covid19Status::isInUpToInterval($date, $this->getPlusValidTo());
    }

    /**
     * @return DateInterval|null
     */
    public function getPlusValidTo(): ?DateInterval
    {
        return $this->plusValidTo;
    }

    public static function isInUpToInterval(DateTime $dateCertificate, DateInterval $param): bool
    {
        $today = new DateTime("today");
        $dateEnd = clone $dateCertificate;
        $dateEnd->add($param);
        return $today < $dateEnd;
    }
}
