<?php

namespace CBSE\Dto\Bookings;

use CBSE\Helper\DateTimeZoneHelper;
use DateTime;
use WP_Post;
use function get_post;

class AthleteBooking
{
    public int $bookingId;
    public int $courseId;
    public string $date;
    public DateTime $dateStart;
    public DateTime $dateEnd;
    public int $columnId;
    public int $eventId;
    public string $eventStart;
    public string $eventEnd;
    public string $eventDescription;

    public function __construct(object $queryResult)
    {
        $this->bookingId = $queryResult->bookingId;
        $this->courseId = $queryResult->courseId;
        $this->date = $queryResult->date;
        $this->dateStart = DateTime::createFromFormat('Y-m-d G:i:s', $queryResult->date . ' ' . $queryResult->eventStart, DateTimeZoneHelper::fromWordPress());
        $this->dateEnd = DateTime::createFromFormat('Y-m-d G:i:s', $queryResult->date . ' ' . $queryResult->eventEnd, DateTimeZoneHelper::fromWordPress());
        $this->columnId = $queryResult->columnId;
        $this->eventId = $queryResult->eventId;
        $this->eventStart = $queryResult->eventStart;
        $this->eventEnd = $queryResult->eventEnd;
        $this->eventDescription = $queryResult->eventDescription;
    }

    /**
     * @return array|WP_Post|null
     */
    public function getColumn()
    {
        return get_post($this->columnId);
    }

    /**
     * @return array|WP_Post|null
     */
    public function getEvent()
    {
        return get_post($this->eventId);
    }
}
