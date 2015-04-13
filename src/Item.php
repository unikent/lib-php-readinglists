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
	 * Returns the resource list URL.
	 */
	public function get_resource_url() {
		$info = $this->data[$this->url];
		return $info['http://purl.org/vocab/resourcelist/schema#resource'][0]['value'];
	}

	/**
	 * Returns a value from the resource metadata.
	 * @internal
	 */
	private function get_values($scheme) {
		$resourceurl = $this->get_resource_url();
		$info = $this->data[$resourceurl];
		$values = array();
		foreach ($info[$scheme] as $k => $v) {
			$values[$k] = $v['value'];
		}
		return $values;
	}

	/**
	 * Returns a value from the resource metadata.
	 * @internal
	 */
	private function get_value($scheme) {
		$arr = $this->get_values($scheme);
		return reset($arr[]);
	}

	/**
	 * Returns the name of the item.
	 */
	public function get_name() {
		return $this->get_value('http://purl.org/dc/terms/title');
	}

	/**
	 * Returns the date of the item.
	 */
	public function get_date() {
		return $this->get_value('http://purl.org/dc/terms/date');
	}

	/**
	 * Returns the isbn of the item.
	 */
	public function get_isbn() {
		return $this->get_value('http://purl.org/ontology/bibo/isbn13');
	}

	/**
	 * Returns the edition of the item.
	 */
	public function get_edition() {
		return $this->get_value('http://purl.org/ontology/bibo/edition');
	}

	/**
	 * Returns the item's place of publication.
	 */
	public function get_place_of_publication() {
		return $this->get_value('http://rdvocab.info/elements/placeOfPublication');
	}

	/**
	 * Returns the subjects of the item.
	 */
	public function get_subjects() {
		return $this->get_values('http://purl.org/dc/elements/1.1/subject');
	}

	/**
	 * Returns the name of a given author.
	 * 
	 * @internal
	 */
	private function get_author($url) {
		$info = $this->data[$url];
		return $info['http://xmlns.com/foaf/0.1/name'][0]['value'];
	}

	/**
	 * Returns the authors of the item.
	 */
	public function get_authors() {
		$authors = array();

		$url = $this->get_value('http://purl.org/ontology/bibo/authorList');
		$info = $this->data[$url];
		foreach ($info as $k => $v) {
			if (strpos($k, '#type') !== false) {
				continue;
			}

			$authors[] = $this->get_author($v[0]['value']);
		}

		return $authors;
	}

	/**
	 * Returns the name of a publisher.
	 */
	public function get_publisher($url) {
		$url = $this->get_value('http://purl.org/dc/terms/publisher');
		$info = $this->data[$url];
		return $info['http://xmlns.com/foaf/0.1/name'][0]['value'];
	}

	/**
	 * Returns the URL of the item.
	 */
	public function get_url() {
		return $this->url;
	}
}
