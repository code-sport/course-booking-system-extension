<div class="cbse-courses-single">
    <p class="cbse-date"><?= $args['courseInfo']->column->post_title ?>
        , <?= date(get_option('date_format'), strtotime($args['timeslot']->date)) ?>
        <?= date(get_option('time_format'), strtotime($args['timeslot']->event_start)) ?>
        - <?= date(get_option('time_format'), strtotime($args['timeslot']->event_end)) ?></p>
    <h3 class="cbse-course-title"><?= $args['courseInfo']->event->post_title ?></h3>
    <p><?= __('Bookings') ?></p>
    <ol>
        <?php foreach ($args['bookings'] as $booking) { ?>
            <li><?= $booking->last_name ?>, <?= $booking->first_name ?>
                <?php if (!empty($booking->covid19_status)) : ?>
                    (<?php __($booking->covid19_status) ?>)
                <?php endif; ?>
            </li>
        <?php } ?>
    </ol>

    <p> (<?= $args['timeslot']->bookings ?> | <?= $args['timeslot']->waitings ?>
        | <?= $args['courseInfo']->event_meta->attendance ?>)
        <button type="button" class="cbse cbse_participants_via_email"
                data-button="<?= json_encode(array("course_id" => $args['timeslot']->course_id, "date" => $args['timeslot']->date)) ?>"><?= __('Participants via email') ?></button>
        #<?= $args['timeslot']->course_id ?>
    </p>

</div>
