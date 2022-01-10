<?php

namespace CBSE\Dto\Bookings;


class AthleteBookings
{
    private $bookings = array();

    public function __construct(array $queryResults)
    {
        foreach ($queryResults as $queryResult)
        {
            $this->bookings[] = new AthleteBooking($queryResult);
        }
    }

    /**
     * @return array
     */
    public function getBookings(): array
    {
        return $this->bookings;
    }
}
