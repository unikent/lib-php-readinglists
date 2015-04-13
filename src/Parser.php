<?php
/**
 * Reading Lists API for is-dev applications.
 *
 * @copyright  2014 Skylar Kelty <S.Kelty@kent.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace unikent\ReadingLists;

/**
 * Aspire Lists parser class.
 * 
 * @internal
 */
class Parser
{
    const INDEX_TIME_PERIOD = 'config/timePeriod';
    const INDEX_LIST = 'sections/';
    const INDEX_LISTS = 'lists/';
    const INDEX_LISTS_TIME_PERIOD = 'http://lists.talis.com/schema/temp#hasTimePeriod';
    const INDEX_LISTS_LIST_ITEMS = 'http://purl.org/vocab/resourcelist/schema#contains';
    const INDEX_LISTS_LIST_UPDATED = 'http://purl.org/vocab/resourcelist/schema#lastUpdated';
    const INDEX_NAME_SPEC = 'http://rdfs.org/sioc/spec/name';
    const INDEX_PARENT_SPEC = 'http://rdfs.org/sioc/spec/parent_of';
    const INDEX_CHILDREN_SPEC = 'http://rdfs.org/sioc/spec/container_of';
    const INDEX_CHILD_SPEC = 'http://rdfs.org/sioc/spec/has_parent';

    /**
     * Our API.
     *
     * @internal
     * @var API
     */
    private $api;

    /**
     * Our Base URL.
     *
     * @internal
     * @var string
     */
    private $baseurl;

    /**
     * The raw, decoded, JSON.
     *
     * @internal
     * @var array
     */
    private $raw;

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
     * @param API $api The API.
     * @param string $baseurl The base URL of the system.
     * @param string $data The raw data from the CURL.
     */
    public function __construct($api, $baseurl, $data) {
        $this->api = $api;
        $this->baseurl = $baseurl;

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
     *
     * @param string $index The index to find.
     * @param string $apiindex The index of the API.
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
        return $this->get_dataset('timeperiods', $this->baseurl . '/' . self::INDEX_TIME_PERIOD);
    }

    /**
     * Grabs all known lists.
     */
    public function get_all_lists() {
        return $this->get_dataset('lists', $this->baseurl . '/' . self::INDEX_LISTS);
    }

    /**
     * Grabs lists for a specific time period.
     *
     * @param string $timeperiod Get all lists within a timeperiod.
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
     *
     * @param string $id Returns a list with a specified ID.
     */
    public function get_list($id) {
        $key = $this->baseurl . '/' . self::INDEX_LIST . $id;
        if (isset($this->raw[$key])) {
            return new ReadingList($this->api, $this->baseurl, $id, $this->raw[$key]);
        }

        return null;
    }

    /**
     * So, this is a category.
     * Return a sensible object.
     * 
     * @param string $url The category URL.
     */
    public function get_category($url) {
        if (isset($this->raw[$url])) {
            return new Category($this->api, $this->baseurl, $url, $this->raw[$url]);
        }

        return null;
    }
}
