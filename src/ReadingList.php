<?php
/**
 * Reading Lists API for is-dev applications.
 *
 * @copyright  2014 Skylar Kelty <S.Kelty@kent.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace unikent\ReadingLists;

/**
 * This class represents a reading list.
 */
class ReadingList {
    /**
     * Our Base URL.
     *
     * @internal
     * @var string
     */
    private $baseurl;

    /**
     * The list ID.
     *
     * @internal
     * @var string
     */
    private $id;

    /**
     * The parsed list.
     *
     * @internal
     * @var array
     */
    private $data;

    /**
     * Constructor.
     *
     * @internal
     * @param string $baseurl The base URL.
     * @param string $id The ID of the list
     * @param array $data The JSON data (decoded).
     */
    public function __construct($baseurl, $id, $data) {
        $this->id = $id;
        $this->data = $data;
        $this->baseurl = $baseurl;
    }

    /**
     * Which time period is this list in?
     */
    public function get_time_period() {
        $period = $this->data[Parser::INDEX_LISTS_TIME_PERIOD][0]['value'];
        return substr($period, strpos($period, Parser::INDEX_TIME_PERIOD) + strlen(Parser::INDEX_TIME_PERIOD));
    }

    /**
     * Grab list campus.
     */
    public function get_campus() {
        return $this->baseurl == API::MEDWAY_URL ? 'Medway' : 'Canterbury';
    }

    /**
     * Grab list base URL.
     */
    public function get_base_url() {
        return $this->baseurl;
    }

    /**
     * Grab list URL.
     */
    public function get_url() {
        return $this->get_base_url() . '/' . Parser::INDEX_LISTS . $this->id;
    }

    /**
     * Name of a list.
     */
    public function get_name() {
        return $this->data[Parser::INDEX_NAME_SPEC][0]['value'];
    }

    /**
     * Counts the number of items in a list.
     */
    public function get_item_count() {
        $data = $this->data;

        $count = 0;
        if (isset($data[Parser::INDEX_LISTS_LIST_ITEMS])) {
            foreach ($data[Parser::INDEX_LISTS_LIST_ITEMS] as $things) {
                if (preg_match('#/items/#', $things['value'])) {
                    $count++;
                }
            }
        }

        return $count;
    }

    /**
     * Get the time a list was last updated.
     *
     * @param bool $asstring Return the time as a contextual string?
     */
    public function get_last_updated($asstring = false) {
        $data = $this->data;
        $time = null;

        if (isset($data[Parser::INDEX_LISTS_LIST_UPDATED])) {
            $time = $data[Parser::INDEX_LISTS_LIST_UPDATED][0]['value'];
            $time = strtotime($time);
        }

        if ($asstring && $time) {
            return $this->contextual_time($time);
        }

        return $time;
    }

    /**
     * Convert timestamp to contextual time
     * 
     * @author Pete Karl II (http://peterthelion.com/)
     * @link http://snipt.net/pkarl/pkarlcom-contextualtime/
     * @link http://pkarl.com/articles/contextual-user-friendly-time-and-dates-php/
     * @link https://gist.github.com/hakre/2397187
     * @param int $timestamp The timestamp to return
     */
    private static function contextual_time($timestamp) {
        $largets = time();

        $n = $largets - $smallts;
        if ($n <= 1) {
            return 'less than 1 second ago';
        }

        if ($n < (60)) {
            return $n . ' seconds ago';
        }

        if ($n < (60 * 60)) {
            $minutes = round($n / 60);
            return 'about ' . $minutes . ' minute' . ($minutes > 1 ? 's' : '') . ' ago';
        }

        if ($n < (60 * 60 * 16)) {
            $hours = round($n / (60 * 60));
            return 'about ' . $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
        }

        if ($n < (time() - strtotime('yesterday'))) {
            return 'yesterday';
        }

        if ($n < (60 * 60 * 24)) {
            $hours = round($n / (60 * 60));
            return 'about ' . $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
        }

        if ($n < (60 * 60 * 24 * 6.5)) {
            return 'about ' . round($n / (60 * 60 * 24)) . ' days ago';
        }

        if ($n < (time() - strtotime('last week'))) {
            return 'last week';
        }

        if (round($n / (60 * 60 * 24 * 7)) == 1) {
            return 'about a week ago';
        }

        if ($n < (60 * 60 * 24 * 7 * 3.5)) {
            return 'about ' . round($n / (60 * 60 * 24 * 7)) . ' weeks ago';
        }

        if ($n < (time() - strtotime('last month'))) {
            return 'last month';
        }

        if (round($n / (60 * 60 * 24 * 7 * 4)) == 1) {
            return 'about a month ago';
        }

        if ($n < (60 * 60 * 24 * 7 * 4 * 11.5)) {
            return 'about ' . round($n / (60 * 60 * 24 * 7 * 4)) . ' months ago';
        }

        if ($n < (time() - strtotime('last year'))) {
            return 'last year';
        }

        if (round($n / (60 * 60 * 24 * 7 * 52)) == 1) {
            return 'about a year ago';
        }

        if ($n >= (60 * 60 * 24 * 7 * 4 * 12)) {
            return 'about ' . round($n / (60 * 60 * 24 * 7 * 52)) . ' years ago';
        }

        return false;
    }

    /**
     * To string.
     */
    public function __toString() {
        $string = $this->get_name();
        $string .= " (" . $this->get_url() . ")";
        $string .= " with " . $this->get_item_count() . " items.";

        $lm = $this->get_last_updated(true);
        if (!empty($lm)) {
            $string .= " Last modified: " . $lm;
        }

        return $string;
    }
}
