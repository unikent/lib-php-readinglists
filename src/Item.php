<?php
/**
 * Reading Lists API for is-dev applications.
 *
 * @copyright  2015 Skylar Kelty <S.Kelty@kent.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace unikent\ReadingLists;

/**
 * This class represents an item in a reading list.
 */
class Item
{
    /**
     * Our API.
     *
     * @internal
     * @var API
     */
    private $api;

    /**
     * Our URL.
     *
     * @internal
     * @var string
     */
    private $url;

	/**
	 * Item data.
	 * 
	 * @internal
	 * @var array
	 */
	private $data;

	/**
	 * Constructor.
	 *
	 * @internal
	 */
	public function __construct($api, $url, $data) {
		$this->api = $api;
		$this->url = $url;
		$this->data = $data;
	}

	/**
	 * Returns the name of the item.
	 */
	public function get_name() {
		print_r($this->data());
	}

	/**
	 * Returns the URL of the item.
	 */
	public function get_url() {
		return $this->url;
	}
}
