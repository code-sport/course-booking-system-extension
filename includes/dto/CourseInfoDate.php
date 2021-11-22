<?php

namespace CBSE\Dto;

require_once 'DtoBase.php';

use CBSE\Helper\ArrayHelper;
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
    private $column_meta;
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
        $this->column_meta = get_post($this->timeslot->column_id);
        $this->substitutes = $this->loadSubstitutes();
        $this->bookings = $this->loadBookings();
    }

    /**
     * @return mixed
     */
    private function loadTimeslots()
    {
        global $wpdb;
        return $wpdb->get_row($wpdb->prepare("SELECT `column_id`, `event_id`, `event_start`, `event_end`, `description`, `user_id` FROM `" . $this->datebaseTableName('mp_timetable_data') . "` WHERE `id` = %d;", $this->courseId));
    }

    /**
     * @return mixed
     */
    private function loadSubstitutes()
    {
        global $wpdb;
        return $wpdb->get_row($wpdb->prepare("SELECT `user_id` FROM `" . $this->datebaseTableName('mp_timetable_substitutes') . "` WHERE `course_id` = %d AND `date` = '%s';", $this->courseId, $this->date->format('Y-m-d')));
    }

    private function loadBookings(): array
    {
        global $wpdb;
        $bookings_raw = $wpdb->get_results($wpdb->prepare("SELECT `booking_id`, `user_id` FROM `" . $this->datebaseTableName('mp_timetable_bookings') . "` WHERE `course_id` =  %d AND `date` = %s;", $this->courseId, $this->date->format('Y-m-d')));
        $bookings = array();
        foreach ($bookings_raw as $booking)
        {
            $user_meta = get_userdata($booking->user_id);
            $booking->first_name = $user_meta->first_name;
            $booking->last_name = $user_meta->last_name;
            $booking->nickname = $user_meta->nickname;
            $booking->covid19_status = get_the_author_meta('covid-19-status', $booking->user_id);
            // TODO Validate status with date
            $bookings[] = $booking;
        }
        usort($bookings, fn($a, $b) => strcmp($a->last_name, $b->last_name));

        return $bookings;
    }

    /**
     * @return false|WP_Error|WP_Term[]
     */
    public function getEventCategories()
    {
        $exclude = get_option('cbse_general_options')['categories_exclude'];
        return ArrayHelper::excludeAndColumn($this->eventCategories, $exclude, 'name');
    }

    /**
     * @return false|WP_Error|WP_Term[]
     */
    public function getEventTags()
    {
        $exclude = get_option('cbse_general_options')['tags_exclude'];
        return ArrayHelper::excludeAndColumn($this->eventTags, $exclude, 'name');
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
    public function getBookings(): array
    {
        return $this->bookings;
    }


    public function getCourseDateTimeString(): string
    {
        $date_string = $this->getCourseDateString();
        $time_start_string = $this->getCourseStartTimeString();
        $time_end_string = $this->getCourseEndTimeString();
        return "{$date_string} {$time_start_string} - {$time_end_string}";
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


}
