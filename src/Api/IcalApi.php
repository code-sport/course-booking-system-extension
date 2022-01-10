<?php

namespace CBSE\Api;

use CBSE\Database\CourseInfoDate;
use CBSE\Database\CoursesForAthlete;
use CBSE\Database\CoursesForHead;
use CBSE\Helper\DateTimeZoneHelper;
use CBSE\Helper\UserHelper;
use DateTime;
use Spatie\IcalendarGenerator\Components\Calendar;
use Spatie\IcalendarGenerator\Components\Event;
use Spatie\IcalendarGenerator\Properties\TextProperty;
use WP_HTTP_Response;
use WP_REST_Request;
use WP_REST_Response;

class IcalApi
{
    private string $format;

    public function __construct(string $format = 'json')
    {
        $this->format = $format;
    }


    public function callback(WP_REST_Request $request): WP_HTTP_Response
    {
        $params = $request->get_params();
        $userId = $params['id'];
        $token = $params['token'];

        if (get_userdata($userId) === false)
        {
            return new WP_REST_Response(null, 404);
        }

        if (!$this->userHasToken($userId, $token))
        {
            return new WP_REST_Response(null, 401);
        }
        $icalData = $this->loadDataForUser($userId);
        switch ($this->format)
        {
            case 'json':
                $response = new WP_REST_Response($icalData, 200);
                break;
            case 'ics':
                header('Content-Type: text/calendar; charset=utf-8');
                header('Content-Disposition: attachment; filename="my-courses.ics');
                echo $this->generateCalender($icalData);
                // See: https://stackoverflow.com/a/50023272/14815278
                exit();
            default:
                $response = new WP_REST_Response('Format is missing', 500);
        }
        return $response;


    }

    private function userHasToken(int $userId, string $token): bool
    {
        $expectedToken = get_user_meta($userId, 'cbse_api_token', true);

        return !empty($expectedToken) && strcasecmp($expectedToken, $token) === 0;
    }

    private function loadDataForUser($userId)
    {
        $pastDays = 90;
        $futureDays = 30;

        $data = array();

        $coursesForAthlete = new CoursesForAthlete($userId, $pastDays, $futureDays);
        $data['bookings'] = $coursesForAthlete->getBookings();

        if (UserHelper::isUserCoach($userId))
        {
            $coursesForCoach = new CoursesForHead($userId, $pastDays, $futureDays);
            $data['coach'] = $coursesForCoach->getTimeslots();
        }

        return $data;
    }

    private function generateCalender(array $icalData): string
    {
        $refreshIntervalInMinutes = 60;
        $calendar = Calendar::create(__('My courses', CBSE_LANGUAGE_DOMAIN))->timezone(DateTimeZoneHelper::FromWordPress());
        $events = array();

        if (array_key_exists('bookings', $icalData))
        {
            $property = TextProperty::create('CATEGORIES', __('Athlete', CBSE_LANGUAGE_DOMAIN));
            foreach ($icalData['bookings'] as $booking)
            {
                $title = $booking->getEvent()->post_title;
                $events[] = Event::create()->name($title)->startsAt($booking->dateStart)->endsAt($booking->dateEnd)->description($booking->eventDescription)->uniqueIdentifier($booking->bookingId)->appendProperty($property);
            }
        }

        if (array_key_exists('coach', $icalData))
        {
            foreach ($icalData['coach'] as $timeslot)
            {
                $events[] = $this->timeslotToEvent($timeslot);
            }
        }

        $calendar = $calendar->event($events);

        return $calendar->refreshInterval($refreshIntervalInMinutes)->get();
    }

    private function timeslotToEvent($timeslot): Event
    {
        $courseInfo = new CourseInfoDate($timeslot->courseId, DateTime::createFromFormat('Y-m-d', $timeslot->date));
        $tile = "{$courseInfo->getEvent()->post_title} ({$timeslot->bookings}|{$timeslot->waitings}|{$courseInfo->getEvent()->attendance})";
        $start = $courseInfo->getCourseStart();
        $end = $courseInfo->getCourseEnd();
        $property = TextProperty::create('CATEGORIES', __('Coach', CBSE_LANGUAGE_DOMAIN));
        $description = '';
        if (!empty($timeslot->description))
        {
            $description .= $timeslot->description . PHP_EOL . PHP_EOL;
        }
        $description .= '----------------------------------' . PHP_EOL;
        $description .= __('Bookings', CBSE_LANGUAGE_DOMAIN) . PHP_EOL;
        $description .= '----------------------------------' . PHP_EOL . PHP_EOL;
        foreach ($courseInfo->getBookings() as $booking)
        {
            $lastName = trim($booking->lastName);
            $firstName = trim($booking->firstName);
            $description .= "- {$lastName}, {$firstName}" . PHP_EOL;
        }
        $description .= PHP_EOL;

        $event = Event::create($tile)->startsAt($start)->endsAt($end)->description(str_replace("\r\n", "\n", $description))->appendProperty($property);
        return $event;
    }
}