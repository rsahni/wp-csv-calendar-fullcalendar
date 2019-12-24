<?php
/**
* Plugin Name: Timetable Calendar
* Description: This is the Timetable Calendar using Full Calendar jQuery plugin.
* Version: 1.0
* Author: Rahul Sahni
**/

//Create a DB when Activated
global $timetable_calendar_db_version;
$timetable_calendar_db_version = '1.0';

function timetable_calendar_install() {
	global $wpdb;
	global $timetable_calendar_db_version;

	$table_name = $wpdb->prefix . 'timetable_calendar';
	
	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE $table_name (
		id mediumint(9) NOT NULL PRIMARY KEY AUTO_INCREMENT,
		eventTitle varchar(200) NOT NULL,
        startDateTime varchar(200) NOT NULL,
        endDateTime varchar(200) DEFAULT '' NOT NULL,
        createdOn timestamp DEFAULT CURRENT_TIMESTAMP NOT NULL
	) $charset_collate;";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );

	add_option( 'timetable_calendar_db_version', $timetable_calendar_db_version );
}

// run the install scripts upon plugin activation
register_activation_hook(__FILE__,'timetable_calendar_install');

// Add menu
function plugin_menu() {

   add_menu_page("Event Calendar", "Event Calendar","manage_options", "eventCalendar", "displayList");

}
add_action("admin_menu", "plugin_menu");

function displayList(){
   include "displaylist.php";
}


//CUSTOM JS FUNCTIONS
add_action( 'wp_enqueue_scripts', 'my_functions' );
function my_functions() {
    wp_register_script( 'bootstrap-4', plugin_dir_url( __FILE__ ) . 'js/bootstrap-4.4.1-dist/bootstrap.min.js', array( 'jquery' ), '1.0', true );
    wp_register_script( 'full-calendar-package-1', plugin_dir_url( __FILE__ ) . 'js/fullcalendar/packages/core/main.js', array( 'jquery' ), '1.0', true );
    wp_register_script( 'full-calendar-package-daygrid', plugin_dir_url( __FILE__ ) . 'js/fullcalendar/packages/daygrid/main.js', array( 'jquery' ), '1.0', true );
    wp_register_script( 'full-calendar-package-timegrid', plugin_dir_url( __FILE__ ) . 'js/fullcalendar/packages/timegrid/main.js', array( 'jquery' ), '1.0', true );
    wp_register_script( 'full-calendar-package-bootstrap', plugin_dir_url( __FILE__ ) . 'js/fullcalendar/packages/bootstrap/main.js', array( 'jquery' ), '1.0', true );
} 

//CUSTOM CSS
add_action( 'wp_enqueue_scripts', 'my_css' );
function my_css() {
    wp_register_style('bootstrap-css-4', plugin_dir_url( __FILE__ ) . 'css/bootstrap-4.4.1-dist/css/bootstrap.min.css' );
    wp_register_style('fullcalendar-core-css', plugin_dir_url( __FILE__ ) . 'js/fullcalendar/packages/core/main.css' );
    wp_register_style('fullcalendar-daygrid-css', plugin_dir_url( __FILE__ ) . 'js/fullcalendar/packages/daygrid/main.css' );
    wp_register_style('fullcalendar-timegrid-css', plugin_dir_url( __FILE__ ) . 'js/fullcalendar/packages/timegrid/main.css' );
    wp_register_style('fullcalendar-bootstrap-css', plugin_dir_url( __FILE__ ) . 'js/fullcalendar/packages/bootstrap/main.css' );
    wp_register_style('timetable-style', plugin_dir_url( __FILE__ ) . 'css/style.css' );
}

//INCLUDE JS IF SHORTCODE EXIST
add_action( 'wp_print_styles', 'form_my_include' );
function form_my_include() {

    global $post;

    if (strstr($post->post_content, 'event_calendar')) {
        wp_enqueue_script('bootstrap-4');
        wp_enqueue_script('full-calendar-package-1');
        wp_enqueue_script('full-calendar-package-daygrid');
        wp_enqueue_script('full-calendar-package-timegrid');
        wp_enqueue_script('full-calendar-package-bootstrap');
        wp_enqueue_style('bootstrap-css-4');
        wp_enqueue_style('fullcalendar-core-css');
        wp_enqueue_style('fullcalendar-daygrid-css');
        wp_enqueue_style('fullcalendar-timegrid-css');
        wp_enqueue_style('fullcalendar-bootstrap-css');
        wp_enqueue_style('timetable-style');
    }
}

//SHORTCODE
function event_calendar_shortcode($atts){
    global $wpdb;

    extract(shortcode_atts(array(
        'navigation'   	=> "true"
    ), $atts));

    $nextPrev = 'prev,next today';

    if($navigation === "true"){
        $nextPrev = 'prev,next today';
    }else{
        $nextPrev = 'today';
    }
    // Table name
    $tablename = $wpdb->prefix."timetable_calendar";

    $firstDay = date("Y-m-d", strtotime("first day of this month")) . ' ' . '00:00:00';
    $lastDay = date("Y-m-d", strtotime("last day of this month")). ' ' . '23:59:59';

    $entriesList = $wpdb->get_results("SELECT * FROM ".$tablename." WHERE startDateTime BETWEEN '$firstDay' AND '$lastDay'");
    $eventData = array();
    if(count($entriesList) > 0){
        foreach($entriesList as $entry){
            $eventTitle = $entry->eventTitle;
            $startDateTime = $entry->startDateTime;
            $endDateTime = $entry->endDateTime;

            $eventScheduleList=array(
                "title" => $eventTitle,
                "start" => $startDateTime,
                "end" => $endDateTime,
                "allDay" => false
            );

            array_push($eventData, $eventScheduleList);
         }
    }
    $encodedEventSchedule = json_encode($eventData);

    return "<div id='calendar'></div>
    <style>
        #calendar{
            max-width: 900px;
            margin: 40px auto;
        }
    </style>
    <script>
        window.onload = function() {
            var calendarEl = document.getElementById('calendar');
            var eventSchedule = '$encodedEventSchedule';
            var calendar = new FullCalendar.Calendar(calendarEl, {
                height: 700,
                timeZone: 'local',
                scrollTime: '09:00:00',
                plugins: [ 'dayGrid', 'timeGrid', 'bootstrap' ],
                themeSystem: 'bootstrap',
                header: {
                    left: '$nextPrev',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth'
                },
                eventLimit: true,
                events: JSON.parse(eventSchedule)
            });

            calendar.render();
        };

    </script>";
}
add_shortcode('event_calendar', 'event_calendar_shortcode');