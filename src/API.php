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
 * Reading Lists API.
 * 
 * @example ../examples/example-1.php How to grab a module's reading lists.
 */
class API
{
    const CANTERBURY_URL = 'http://resourcelists.kent.ac.uk';
    const MEDWAY_URL = 'http://medwaylists.kent.ac.uk';

    /** CURL Timeout. */
    private $_timeout;

    /** Our Campus. */
    private $_campus;

    /** Our Timeperiod. */
    private $_timeperiod;

    /** A Cache Layer. */
    private $_cache;

    /**
     * Constructor.
     */
    public function __construct() {
        $this->set_timeout(0);
        $this->set_campus(array('canterbury', 'medway'));
        $this->set_timeperiod();
    }

    /**
     * Set a custom CURL timeout
     *
     * @param int $timeout CURL Timeout in ms
     */
    public function set_timeout($timeout) {
        $this->_timeout = $timeout;
    }

    /**
     * Set the campus we want lists from.
     * Defaults to all lists.
     *
     * @param string $campus Canterbury or Medway, or an array of both.
     */
    public function set_campus($campus) {
        $campus = !is_array($campus) ? array($campus) : $campus;

        $this->_campus = array();
        foreach ($campus as $c) {
            $c = strtolower($c);
            if (!in_array($c, array('canterbury', 'medway'))) {
                throw new \Exception("Invalid campus: '{$campus}'.");
            }

            $this->_campus[] = $c;
        }
    }

    /**
     * Set a time period (year).
     *
     * @param string $timeperiod Time period to CURL. Defaults to latest.
     */
    public function set_timeperiod($timeperiod = null) {
        if ($timeperiod === null) {
            $timeperiod = date("Y");
        }
        $this->_timeperiod = $timeperiod;
    }

    /**
     * Set a cache object.
     * This API expects it can call "set($key, $value)" and "get($key)" and wont try to do anything else.
     */
    public function set_cache_layer($cache) {
        if (!method_exists($cache, 'set') || !method_exists($cache, 'get')) {
            throw new \Exception("Invalid cache layer - must have set and get.");
        }

        $this->_cache = $cache;
    }

    /**
     * You need a time period map :)
     */
    private function get_time_period_map() {
        return array(
            'canterbury' => array(
                '2014' => '53304cb6f3d4d',
                '2013' => '2',
                '2012' => '1'
            ),
            'medway' => array(
                '2014' => '53304d3387393',
                '2013' => '2',
                '2012' => '1'
            )
        );
    }

    /**
     * Returns a list of reading lists for a given module code.
     *
     * @param string $modulecode Module Code
     * @param string $campus Which campus do you want lists for? (canterbury, medway, both)
     * @return array An array of URLs to reading lists
     */
    private function get_lists_for($modulecode, $campus) {
        $modulecode = strtolower($modulecode);
        $campus = strtolower($campus);

        // Work out which Talis TP we want.
        $timeperiod = $this->get_time_period_map();
        $timeperiod = $timeperiod[$campus];
        $timeperiod = $timeperiod[$this->_timeperiod];

        $url = self::CANTERBURY_URL;
        if ($campus == 'medway') {
            $url = self::MEDWAY_URL;
        }

        // Curl the lists json for this module out of the modules knowledge group.
        $raw = $this->curl("{$url}/modules/{$modulecode}/lists.json");

        // Parse the dodgy-looking result into List objects.
        $parser = new Parser($url, $raw);
        if (!$parser->is_valid()) {
            return array();
        }

        $lists = $parser->get_lists($timeperiod);
        $lists = array_map(function($list) use ($url, $parser) {
            return $parser->get_list($list);
        }, $lists);

        uasort($lists, function ($a, $b) {
            return strcmp($a["name"], $b["name"]);
        });

        return $lists;
    }

    /**
     * Returns a list of reading lists for a given module code.
     *
     * @param string $modulecode Module Code
     * @return array An array of URLs to reading lists
     */
    public function get_lists($modulecode) {
        $modulecode = strtolower($modulecode);

        $lists = array();
        foreach ($this->_campus as $campus) {
            $result = $this->get_lists_for($modulecode, $campus);
            $lists = array_merge($lists, $result);
        }

        return $lists;
    }

    /**
     * CURL shorthand.
     */
    protected function curl($url) {
        if ($this->_cache !== null) {
            $v = $this->_cache->get($url);
            if ($v) {
                return $v;
            }
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,            $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER,         false);
        curl_setopt($ch, CURLOPT_HTTP_VERSION,   CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_HTTPHEADER,     array('Content-Type: text/plain'));

        if ($this->_timeout > 0) {
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->_timeout);
            curl_setopt($ch, CURLOPT_TIMEOUT, $this->_timeout);
        }

        $result = curl_exec($ch);

        if ($this->_cache !== null) {
            $this->_cache->set($url, $result);
        }

        return $result;
    }
}