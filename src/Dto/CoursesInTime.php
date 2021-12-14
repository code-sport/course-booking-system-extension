<?php

namespace CBSE\Dto;

use Analog\Analog;
use DateTime;

class CoursesInTime extends DtoBase
{
    private DateTime $timeFrom;
    private DateTime $timeTo;
    private $courses;

    public function __construct(DateTime $timeFrom, DateTime $timeTo)
    {
        $this->timeFrom = $timeFrom;
        $this->timeTo = $timeTo;
        $this->loadFromDatabase();

        Analog::log(get_class($this) . ' - ' . __FUNCTION__ . ' - ' . $timeFrom->format('c') . '-' . $timeTo->format('c') . ' -> ' . count($this->courses));
    }

    private function loadFromDatabase()
    {
        global $wpdb;

        $query = "SELECT `id` AS `course_id`, `column_id`, `event_id`, `event_start`, `event_end`, `" . $this->datebaseTableName('mp_timetable_bookings') . "`.`date` as date, `" . $this->datebaseTableName('mp_timetable_substitutes') . "`.`user_id` AS substitutes_user_id, `" . $this->datebaseTableName('mp_timetable_data') . "`.user_id, TIMESTAMP(`" . $this->datebaseTableName('mp_timetable_bookings') . "`.`date`,`event_start`) as event_start_timestamp";
        $query .= " FROM `" . $this->datebaseTableName('mp_timetable_data') . "`";
        $query .= " JOIN `" . $this->datebaseTableName('mp_timetable_bookings') . "`";
        $query .= ' ON `' . $this->datebaseTableName('mp_timetable_data') . '`.`id` = `' . $this->datebaseTableName('mp_timetable_bookings') . '`.`course_id`';
        $query .= " LEFT JOIN `" . $this->datebaseTableName('mp_timetable_substitutes') . "`";
        $query .= " ON `" . $this->datebaseTableName('mp_timetable_data') . "`.`id` = `" . $this->datebaseTableName('mp_timetable_substitutes') . "`.`course_id`";
        $query .= " AND `" . $this->datebaseTableName('mp_timetable_bookings') . "`.`date` = `" . $this->datebaseTableName('mp_timetable_substitutes') . "`.`date`";
        $query .= " WHERE TIMESTAMP(`" . $this->datebaseTableName('mp_timetable_bookings') . "`.`date`,`event_start`) >= '%s'";
        $query .= " AND  TIMESTAMP(`" . $this->datebaseTableName('mp_timetable_bookings') . "`.`date`,`event_start`) < '%s'";
        $query .= " GROUP BY `" . $this->datebaseTableName('mp_timetable_bookings') . "`.`date`, `" . $this->datebaseTableName('mp_timetable_bookings') . "`.`course_id`";
        $query .= " ORDER BY `" . $this->datebaseTableName('mp_timetable_bookings') . "`.`date` ASC, `" . $this->datebaseTableName('mp_timetable_data') . "`.`event_start` ASC;";
        $this->courses = $wpdb->get_results($wpdb->prepare($query, $this->timeFrom->format('Y-m-d H:i:s'),
            $this->timeTo->format
            ('Y-m-d H:i:s')));
    }

    /**
     * @return mixed
     */
    public function getCourses()
    {
        return $this->courses;
    }
}
