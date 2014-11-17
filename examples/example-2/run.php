<?php
/**
 * Reading Lists API for is-dev applications.
 *
 * @package    ReadingLists
 * @copyright  2014 Skylar Kelty <S.Kelty@kent.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("vendor/autoload.php");

$api = new \unikent\ReadingLists\API();
$api->set_timeout(4000);
$api->set_timeperiod("2013");
$api->set_campus("canterbury");

$lists = $api->get_lists("EN902");

foreach ($lists as $list) {
    echo "---------------------------------\n";
    echo $list;
}