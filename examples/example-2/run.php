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
$api->set_timeperiod("2018");
$api->set_campus("canterbury");

$lists = $api->get_lists("LW509");
/*
function print_cats($level, $category) {
    echo str_repeat('-', $level) . " " . $category->get_name() . "\n";
    foreach ($category->get_parents() as $cat) {
        print_cats($level + 1, $cat);
    }
}
*/
foreach ($lists as $list) {
    echo "---------------------------------\n";
    echo $list."\n";
/*
    foreach ($list->get_categories() as $category) {
        print_cats(1, $category);
    }
*/
}