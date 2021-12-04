<?php

namespace CBSE\Dto;

use CBSE\Model\Covid19Status;
use DateInterval;

class Covid19StatusItems
{
    private  static ?Covid19StatusItems $instance = null;
    private Covid19Status $tested;
    private Covid19Status $vaccinated;
    private Covid19Status $recovered;
    private $status = array();

    private function __construct()
    {
        $this->tested = new Covid19Status(
            'tested',
            __('tested', CBSE_LANGUAGE_DOMAIN),
            new DateInterval('PT0H'),
            new DateInterval('PT24H')
        );
        $this->status[] = $this->tested;

        $this->vaccinated = new Covid19Status(
            'vaccinated',
            __('vaccinated', CBSE_LANGUAGE_DOMAIN),
            new DateInterval('P14D'),
            new DateInterval('P9M')
        );
        $this->status[] += $this->vaccinated;

        $this->recovered = new Covid19Status(
            'recovered',
            __('recovered', CBSE_LANGUAGE_DOMAIN),
            new DateInterval('P28D'),
            new DateInterval('P6M')
        );
        $this->status[] += $this->recovered;


    }

    /**
     * @return Covid19StatusItems
     */
    public function getInstance(): Covid19StatusItems
    {
        if (Covid19StatusItems::$instance == null)
        {
            Covid19StatusItems::$instance = new Covid19StatusItems();
        }
        return Covid19StatusItems::$instance;
    }

    /**
     * @return Covid19Status
     */
    public function getTested(): Covid19Status
    {
        return $this->tested;
    }

    /**
     * @return Covid19Status
     */
    public function getVaccinated(): Covid19Status
    {
        return $this->vaccinated;
    }

    /**
     * @return Covid19Status
     */
    public function getRecovered(): Covid19Status
    {
        return $this->recovered;
    }
}