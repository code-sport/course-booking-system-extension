<?php

namespace CBSE\Model;

use DateInterval;
use DateTime;

class Covid19Status
{
    private string $id;
    private string $name;
    private ?DateInterval $validFrom = null;
    private ?DateInterval $validTo = null;

    public function __construct(string $id, string $name, ?DateInterval $validFrom, ?DateInterval $validTo)
    {
        $this->id = $id;
        $this->name = $name;
        $this->validFrom = $validFrom;
        $this->validTo = $validTo;
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
            $dateStart = $date->add($this->getValidFrom());
            $validStart = $dateStart <= $today;
        }
        else
        {
            $validStart = true;
        }

        if ($this->getValidTo() != null)
        {
            $dateEnd = $date->add($this->getValidTo());
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
    public function getValidFrom(): DateInterval
    {
        return $this->validFrom;
    }

    /**
     * @return DateInterval
     */
    public function getValidTo(): DateInterval
    {
        return $this->validTo;
    }
}