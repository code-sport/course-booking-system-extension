# Course Booking System Extension
This plugin for WordPress is an extension for the [Course Booking System](https://de.wordpress.org/plugins/course-booking-system/) from [ComMotion](https://commotion.online/).

## Requirements
- [Course Booking System](https://de.wordpress.org/plugins/course-booking-system/)
- [Basic-Auth](https://github.com/WP-API/Basic-Auth) is necessary that the data can be read from the api.

## Installation

### Via git
- `WP-ROOT/wp-content/plugins`
- clone in this folder this repository

### Via WordPress installer
- Download the zip from the releases
- Go into your WordPress instance and select plugins and then install.
- click on upload plugin
- select the downloaded file
- press now install and afterwords 'Course Booking System Extension'.

## Functionality

### API
You need the `id` from an event `wp-json/wp/v2/mp-event`.
With this event-`id` you can load the overview of the courses: `/wp-json/wp/v2/course-booking-system-extension/event/<ID>/courses`.
On this you select the course-`id` and can load the info about the course `wp-json/wp/v2/course-booking-system-extension/course/<ID>`.
The participants you will get on `wp-json/wp/v2/course-booking-system-extension/course/<ID>/date/<DATE:yyyy-mm-dd>`.

### Shortcode

#### Bookings overview for head of event
Add the shortcode `[cbse_event_head_courses]` to a page or post and in this the overview for the head of event will be shown.
