<?php
/**
 * Reading Lists API for is-dev applications.
 *
 * @package    ReadingLists
 * @copyright  2014 Skylar Kelty <S.Kelty@kent.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace ReadingLists;

/**
 * Aspire Lists parser class.
 */
class Parser
{
    const INDEX_TIME_PERIOD = 'config/timePeriod';
    const INDEX_LISTS = 'lists/';
    const INDEX_LISTS_TIME_PERIOD = 'http://lists.talis.com/schema/temp#hasTimePeriod';
    const INDEX_LISTS_LIST_ITEMS = 'http://purl.org/vocab/resourcelist/schema#contains';
    const INDEX_LISTS_LIST_UPDATED = 'http://purl.org/vocab/resourcelist/schema#lastUpdated';
    const INDEX_NAME_SPEC = 'http://rdfs.org/sioc/spec/name';
    const INDEX_PARENT_SPEC = 'http://rdfs.org/sioc/spec/parent_of';

    /** Our Base URL */
    private $baseurl;

    /** The raw, decoded, JSON */
    private $raw;

    /** The parsed list */
    private $data;

    /**
     * Constructor.
     *
     * @param string $data The raw data from the CURL
     */
    public function __construct($baseurl, $data) {
        $this->baseurl = $baseurl;
        if (strrpos($this->baseurl, '/') !== strlen($this->baseurl) - 1) {
            $this->baseurl = $this->baseurl . '/';
        }

        $this->raw = json_decode($data, true);
        if (!$this->raw) {
            $this->raw = array();
        }

        $this->data = array();
    }

    /**
     * Do we have data?
     */
    public function is_valid() {
        return !empty($this->raw);
    }

    /**
     * Shorthand method.
     */
    private function get_dataset($index, $apiindex) {
        if (isset($this->data[$index])) {
            return $this->data[$index];
        }

        $data = array();
        foreach ($this->raw as $k => $v) {
            $pos = strpos($k, $apiindex);
            if ($pos !== 0) {
                continue;
            }

            $data[] = substr($k, strlen($apiindex));
        }

        $this->data[$index] = $data;

        return $data;
    }

    /**
     * Grab all known time periods.
     */
    public function get_timeperiods() {
        return $this->get_dataset('timeperiods', $this->baseurl . self::INDEX_TIME_PERIOD);
    }

    /**
     * Grabs all known lists.
     */
    public function get_all_lists() {
        return $this->get_dataset('lists', $this->baseurl . self::INDEX_LISTS);
    }

    /**
     * Grabs lists for a specific time period.
     */
    public function get_lists($timeperiod) {
        $lists = array();
        foreach ($this->get_all_lists() as $list) {
            $object = $this->get_list($list);
            if ($object->get_time_period() == $timeperiod) {
                $lists[] = $list;
            }
        }

        return $lists;
    }

    /**
     * Returns a list object for a specific list.
     */
    public function get_list($id) {
        $key = $this->baseurl . self::INDEX_LISTS . $id;
        if (isset($this->raw[$key])) {
            return new reading_list($id, $this->raw[$key]);
        }

        return null;
    }
}
