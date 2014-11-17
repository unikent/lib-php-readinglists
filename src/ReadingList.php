<?php
/**
 * Reading Lists API for is-dev applications.
 *
 * @package    ReadingLists
 * @copyright  2014 Skylar Kelty <S.Kelty@kent.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace unikent\ReadingLists;

/**
 * This class represents a reading list.
 */
class ReadingList {
    /** Our Base URL */
    private $baseurl;

    /** The list ID */
    private $id;

    /** The parsed list */
    private $data;

    /**
     * Constructor.
     */
    public function __construct($id, $data) {
        $this->id = $id;
        $this->data = $data;
    }

    /**
     * Which time period is this list in?
     */
    public function get_time_period() {
        $period = $this->data[Parser::INDEX_LISTS_TIME_PERIOD][0]['value'];
        return substr($period, strpos($period, Parser::INDEX_TIME_PERIOD) + strlen(Parser::INDEX_TIME_PERIOD));
    }

    /**
     * Grab list URL.
     */
    public function get_url() {
        return Parser::INDEX_LISTS . $this->id;
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
                if (preg_match('/\/items\//', $things['value'])) {
                    $count++;
                }
            }
        }

        return $count;
    }

    /**
     * Get the time a list was last updated.
     */
    public function get_last_updated() {
        $data = $this->data;
        $time = null;

        if (isset($data[Parser::INDEX_LISTS_LIST_UPDATED])) {
            $time = $data[Parser::INDEX_LISTS_LIST_UPDATED][0]['value'];
            $time = strtotime($time);
        }

        return $time;
    }

    /**
     * To string.
     */
    public function __toString() {
        $string = $this->get_name();
        $string .= " (" . $this->get_url() . ")";
        $string .= " with " . $this->get_item_count() . " items.";

        $lm = $this->get_last_updated();
        if ($lm !== null) {
            $string .= " Last modified: " . $lm;
        }

        return $string;
    }
}
