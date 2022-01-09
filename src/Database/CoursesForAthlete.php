<?php

namespace CBSE\Database;


use CBSE\Dto\Bookings\AthleteBookings;

class CoursesForAthlete extends DatabaseBase
{
    private int $userId;
    private int $pastDays;
    private int $futureDays;
    private AthleteBookings $courses;

    public function __construct(int $userId, int $pastDays = 7, int $futureDays = 7)
    {
        do_action('qm/debug', "CoursesForAthlete -> userId: {$userId}, pastDays: {$pastDays}, futureDays: {$futureDays}");

        $this->userId = $userId;
        $this->pastDays = $pastDays;
        $this->futureDays = $futureDays;

        $this->loadFromDatabase();

    }

    private function loadFromDatabase()
    {
        global $wpdb;

        $query = 'SELECT `booking_id` as bookingId,`course_id` as courseId,`date`';
        $query .= ',`column_id` AS columnId,`event_id` AS eventId,`event_start` AS eventStart,`event_end` as eventEnd,`description` AS eventDescription';
        $query .= " FROM `{$this->datebaseTableName('mp_timetable_bookings')}` as booking";
        $query .= " LEFT JOIN `{$this->datebaseTableName('mp_timetable_data')}` as data ON booking.course_id = data.id";
        $query .= ' WHERE booking.`user_id` = %d';
        $query .= ' AND DATE(`date`) > (NOW() - INTERVAL %d DAY)';
        $query .= ' AND DATE(`date`) < (NOW() + INTERVAL %d DAY)';
        $query .= ' ORDER BY `date` ASC;';

        $results = $wpdb->get_results($wpdb->prepare($query, $this->userId, $this->pastDays, $this->futureDays));
        $this->courses = new AthleteBookings($results);

    }


    public function getBookings(): array
    {
        return $this->courses->getBookings();
    }
}