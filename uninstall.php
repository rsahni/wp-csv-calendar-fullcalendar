<?php
/**
 * Runs on Uninstall of timetable calendar
 *
 * @package   timetable calendar
 * @author    Rahul Sahniu
 * @license   GPL-2.0+
 * @link      www.rahulsahni.in
 */

// if uninstall.php is not called by WordPress, die
if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}
 
// drop a custom database table
global $wpdb;
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}timetable_calendar");