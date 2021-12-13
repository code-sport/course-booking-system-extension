<?php

namespace CBSE\Dto;

use CBSE\Helper\ArrayHelper;
use CBSE\UserCovid19Status;
use DateTime;
use WP_Error;
use WP_Post;
use WP_Term;

class CourseInfoDate extends DtoBase
{
    private int $courseId;
    private DateTime $date;
    private $timeslot;
    private $event;
    private $eventMeta;
    private $eventCategories;
    private $eventTags;
    private $column;
    private $columnMeta;
    private $substitutes;
    private array $bookings;


    public function __construct(int $courseId, DateTime $date)
    {
        $this->courseId = $courseId;
        $this->date = $date;

        $this->timeslot = $this->loadTimeslots();
        $this->event = get_post($this->timeslot->event_id);
        $this->eventMeta = get_post($this->timeslot->event_id);
        $this->eventCategories = get_the_terms($this->timeslot->event_id, 'mp-event_category');
        $this->eventTags = get_the_terms($this->timeslot->event_id, 'mp-event_tag');
        $this->column = get_post($this->timeslot->column_id);
        $this->columnMeta = get_post($this->timeslot->column_id);
        $this->substitutes = $this->loadSubstitutes();
        $this->bookings = $this->loadBookings();
    }

    /**
     * @return mixed
     */
    private function loadTimeslots()
    {
        global $wpdb;
        return $wpdb->get_row(
            $wpdb->prepare("SELECT `column_id`, `event_id`, `event_start`, `event_end`, `description`, `user_id` FROM `" . $this->datebaseTableName('mp_timetable_data') . "` WHERE `id` = %d;", $this->courseId));
    }

    /**
     * @return mixed
     */
    private function loadSubstitutes()
    {
        global $wpdb;
        return $wpdb->get_row(
            $wpdb->prepare("SELECT `user_id` FROM `" . $this->datebaseTableName('mp_timetable_substitutes') . "` WHERE `course_id` = %d AND `date` = '%s';", $this->courseId, $this->date->format('Y-m-d')));
    }

    private function loadBookings(): array
    {
        global $wpdb;
        $bookingsRaw = $wpdb->get_results(
            $wpdb->prepare("SELECT `booking_id`, `user_id` FROM `" . $this->datebaseTableName('mp_timetable_bookings') . "` WHERE `course_id` =  %d AND `date` = %s;", $this->courseId, $this->date->format('Y-m-d')));
        $bookingList = array();
        foreach ($bookingsRaw as $booking)
        {
            $userMeta = get_userdata($booking->user_id);
            $booking->firstName = $userMeta->first_name;
            $booking->lastName = $userMeta->last_name;
            $booking->nickname = $userMeta->nickname;
            $covid19Status = new UserCovid19Status($booking->user_id);
            $booking->covid19_status = $covid19Status->getStatusOrEmpty();
            $booking->flags = $covid19Status->getFlags();
            $bookingList[] = $booking;
        }

        return $bookingList;
    }

    public function __toString()
    {
        return "CourseInfoDate: ID {$this->courseId}\n";
    }

    /**
     * @return array|WP_Post|null
     */
    public function getColumn()
    {
        return $this->column;
    }

    /**
     * @return array|WP_Post|null
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @return array|WP_Post|null
     */
    public function getEventMeta()
    {
        return $this->eventMeta;
    }

    /**
     * @return mixed
     */
    public function getTimeslot()
    {
        return $this->timeslot;
    }

    /**
     * @return array
     */
    public function getBookingsAlphabeticallySortedByLastName(): array
    {
        $bookingList = $this->getBookings();
        usort($bookingList, fn($a, $b) => strcmp($a->lastName, $b->lastName));
        return $bookingList;
    }

    /**
     * @return array
     */
    public function getBookings(): array
    {
        return $this->bookings;
    }

    public function getCourseDateTimeString(): string
    {
        $courseDateString = $this->getCourseDateString();
        $courseStartTimeString = $this->getCourseStartTimeString();
        $courseEndTimeString = $this->getCourseEndTimeString();
        return "{$courseDateString} {$courseStartTimeString} - {$courseEndTimeString}";
    }

    public function getCourseDateString(): string
    {
        return $this->date->format(get_option('date_format'));
    }

    public function getCourseStartTimeString(): string
    {
        return date(get_option('time_format'), strtotime($this->timeslot->event_start));
    }

    public function getCourseEndTimeString(): string
    {
        return date(get_option('time_format'), strtotime($this->timeslot->event_end));
    }

    public function getCourseDate(): DateTime
    {
        return $this->date;
    }

    /**
     * @return int
     */
    public function getCourseId(): int
    {
        return $this->courseId;
    }

    /**
     * @return mixed
     */
    public function getSubstitutes()
    {
        return $this->substitutes;
    }

    /**
     * @return array|WP_Post|null
     */
    public function getColumnMeta()
    {
        return $this->columnMeta;
    }

    public function getEventCategoriesAsString($default = ''): string
    {
        return ($this->getEventCategories() != null
            && !empty($this->getEventCategories())) ?
            implode(", ", $this->getEventCategories()) : $default;
    }

    /**
     * @return false|WP_Error|WP_Term[]
     */
    public function getEventCategories()
    {
        $exclude = get_option('cbse_general_options')['categories_exclude'];
        if ($exclude != '0' && is_array($this->eventCategories))
        {
            return ArrayHelper::excludeAndColumn($this->eventCategories, $exclude, 'name');
        }
        else
        {
            return null;
        }
    }

    public function getEventTagsAsString(string $default = ''): string
    {
        if ($this->getEventTagsByName() != null && !empty($this->getEventTagsByName()))
        {
            return implode(", ", $this->getEventTagsByName());
        }
        else
        {
            return $default;
        }
    }

    /**
     * @return false|WP_Error|WP_Term[]
     */
    public function getEventTagsByName()
    {
        $exclude = get_option('cbse_general_options')['tags_exclude'];
        if ($exclude != '0' && is_array($this->eventTags))
        {
            return ArrayHelper::excludeAndColumn($this->eventTags, $exclude, 'name');
        }
        else
        {
            return null;
        }
    }

    /**
     * @return false|WP_Error|WP_Term[]
     */
    public function getEventTags()
    {
        return $this->eventTags;
    }


}
