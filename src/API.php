<?php
/**
 * Reading Lists API for is-dev applications.
 *
 * @copyright  2015 Skylar Kelty <S.Kelty@kent.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace unikent\ReadingLists;

/**
 * Reading Lists API.
 *
 * @example ../examples/example-1/run.php How to grab a module's reading lists.
 * @example ../examples/example-2/run.php How to grab a module's reading lists for a campus in a given year.
 */
class API
{
    /**
     * URL of the Canterbury Reading Lists system.
     */
    const CANTERBURY_URL = 'https://kent.rl.talis.com';

    /**
     * URL of the Medway Reading Lists system.
     */
    const MEDWAY_URL = 'https://medway.rl.talis.com';

    /**
     * CURL Timeout.
     *
     * @internal
     * @var int
     */
    private $_timeout;

    /**
     * Our Campus.
     *
     * @internal
     * @var string
     */
    private $_campus;

    /**
     * Our Timeperiod.
     *
     * @internal
     * @var string
     */
    private $_timeperiod;

    /**
     * A Cache Layer.
     *
     * @internal
     * @var mixed
     */
    private $_cache;

    /**
     * Constructor.
     */
    public function __construct($year = '2015') {
        $this->set_timeout(0);
        $this->set_campus(array('canterbury', 'medway'));
        $this->set_timeperiod($year);
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
    public function set_timeperiod($timeperiod) {
        $map = $this->get_time_period_map();
        foreach ($map as $campus => $periods) {
            if (isset($periods[$timeperiod])) {
                $this->_timeperiod = $timeperiod;
                return;
            }
        }

        throw new \Exception("Invalid time period: '$timeperiod'.");
    }

    /**
     * Set a cache object.
     * This API expects it can call "set($key, $value)" and "get($key)" and wont try to do anything else.
     *
     * @param object $cache An object with get and set methods.
     */
    public function set_cache_layer($cache) {
        if (!method_exists($cache, 'set') || !method_exists($cache, 'get')) {
            throw new \Exception("Invalid cache layer - must have set and get.");
        }

        $this->_cache = $cache;
    }

    /**
     * You need a time period map!
     * An easy-ish way to get these is to inspect the selects here:
     * http://resourcelists.kent.ac.uk/admin/rollover.html
     *
     * @internal
     */
    private function get_time_period_map() {
        return array(
            'canterbury' => array(
                '2023' => '63dbb5954e9df',
                '2022' => '620a769f80a98',
                '2021' => '5f91f0c7ee6ec',
                '2020' => '5de52d41dce91',
                '2019' => '5c9d6b52769e6',
                '2018' => '5ac2ca4b4d604',
                '2017' => '56f353645c000',
                '2016' => '56f35361efece',
                '2015' => '53304cef6ea1f',
                '2014' => '53304cb6f3d4d',
                '2013' => '2',
                '2012' => '1'
            ),
            'medway' => array(
                '2023' => '63dbb5b92e7b6',
                '2022' => '620a7706a8707',
                '2021' => '60875c95590ae',
                '2020' => '5de52d7c0b84f',
                '2019' => '5c9d6b4bd4720',
                '2018' => '5ac2cb0a265b3',
                '2017' => '56f353e523d25',
                '2016' => '56f353d1e77a6',
                '2015' => '53304d5bab1d4',
                '2014' => '53304d3387393',
                '2013' => '2',
                '2012' => '1'
            )
        );
    }

    /**
     * Returns a list item, given a URL.
     */
    public function get_item($url) {
        $raw = $this->curl($url . '.json');
        $json = json_decode($raw, true);
        if (!$json) {
            return null;
        }

        return new Item($this, $url, $json);
    }

    /**
     * Returns a list, given an ID.
     *
     * @param string $id List ID.
     * @param string $campus Campus.
     */
    public function get_list($id, $campus = 'current') {
        if ($campus == 'current') {
            $campus = $this->_campus;
        }

        if (is_array($campus)) {
            throw new \Exception("Campus cannot be an array!");
        }

        $url = self::CANTERBURY_URL;
        if ($campus == 'medway') {
            $url = self::MEDWAY_URL;
        }

        $raw = $this->curl($url . '/sections/' . $id . '.json');
        $parser = new Parser($this, $url, $raw);
        return $parser->get_list($id);
    }

    /**
     * Returns a list of reading lists for a given module code.
     *
     * @internal
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
        $raw = str_replace("http:\/\/resourcelists.kent.ac.uk", "https:\/\/kent.rl.talis.com", $raw);
        $raw = str_replace("http:\/\/medwaylists.kent.ac.uk", "https:\/\/medway.rl.talis.com", $raw);

        // Parse the dodgy-looking result into List objects.
        $parser = new Parser($this, $url, $raw);
        if (!$parser->is_valid()) {
            return array();
        }

        $lists = $parser->get_lists($timeperiod);
        $lists = array_map(function($list) use ($url, $parser) {
            return $parser->get_list($list);
        }, $lists);
        uasort($lists, function ($a, $b) {
            return strcmp($a->get_name(), $b->get_name());
        });

        return $lists;
    }

    /**
     * Returns a category, given an ID (which is a URL).
     *
     * @internal
     * @param string $url The category URL.
     */
    public function get_category($url) {
        // Curl the json for this category.
        $raw = $this->curl("{$url}.json");

        $baseurl = strpos($url, API::MEDWAY_URL) !== false ? API::MEDWAY_URL : API::CANTERBURY_URL;
        $parser = new Parser($this, $baseurl, $raw);
        if (!$parser->is_valid()) {
            return array();
        }

        return $parser->get_category($url);
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
     *
     * @internal
     * @param string $url The URL to curl.
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

        $result = str_replace("http:\/\/resourcelists.kent.ac.uk", "https:\/\/kent.rl.talis.com", $result);
        $result = str_replace("http:\/\/medwaylists.kent.ac.uk", "https:\/\/medway.rl.talis.com", $result);

        if ($this->_cache !== null) {
            $this->_cache->set($url, $result);
        }

        return $result;
    }
}
