<?php
$current = strtotime(date("Y-m-d"));
$date = strtotime($args['timeslot']->date);
$difference = floor(($date - $current) / (60 * 60 * 24));

$time = '';
if ($difference == 0)
{
    $time = 'today';
}
elseif ($difference < 0)
{
    $time = 'past';
}
elseif ($difference > 0)
{
    $time = 'future';
}
?>
<div
        class="cbse-courses-single <?= $time ?> <?= date("Y-m-d", $date) ?> course-<?= $args['timeslot']->course_id ?>">
    <p class="cbse-date"><?= $args['courseInfo']->getColumn()->post_title ?>
        , <?= date(get_option('date_format'), strtotime($args['timeslot']->date)) ?>
        <?= date(get_option('time_format'), strtotime($args['timeslot']->event_start)) ?>
        - <?= date(get_option('time_format'), strtotime($args['timeslot']->event_end)) ?></p>
    <h3 class="cbse-course-title"><?= $args['courseInfo']->getEvent()->post_title ?></h3>
    <p><?= __('Bookings', 'course-booking-system-extension') ?></p>
    <ol>
        <?php
        foreach ($args['courseInfo']->getBookings() as $booking) { ?>
            <li><?= trim($booking->last_name) ?>, <?= trim($booking->first_name) ?>
                <?php
                if (!empty($booking->covid19_status)) : ?>
                    (<?php
                    _e($booking->covid19_status, 'course-booking-system-extension') ?>)
                <?php
                endif; ?>
            </li>
        <?php
        } ?>
    </ol>

    <p>
        <span class="cbse-course-summary">
        (<?= $args['timeslot']->bookings ?> | <?= $args['timeslot']->waitings ?>
                | <?= $args['courseInfo']->getEventMeta()->attendance ?>)
        </span>
        <button
                type="button"
                class="cbse cbse_participants_via_email"
                data-button='<?= json_encode(array('course_id' => $args['timeslot']->course_id, 'date' => $args['timeslot']->date)) ?>'
        >
            <?= __('Participants via email', 'course-booking-system-extension') ?>
        </button>
    </p>

</div>
