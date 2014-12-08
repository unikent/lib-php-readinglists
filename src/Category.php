<?php
/**
 * Reading Lists API for is-dev applications.
 *
 * @copyright  2014 Skylar Kelty <S.Kelty@kent.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace unikent\ReadingLists;

/**
 * This class represents a reading list category.
 */
class Category
{
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
     * The list ID.
     *
     * @internal
     * @var string
     */
    private $id;

    /**
     * The parsed category.
     *
     * @internal
     * @var array
     */
    private $data;

    /**
     * Constructor.
     *
     * @internal
     * @param API $api The API.
     * @param string $baseurl The base URL.
     * @param string $id The ID of the list
     * @param array $data The JSON data (decoded).
     */
    public function __construct($api, $baseurl, $id, $data) {
        $this->id = $id;
        $this->api = $api;
        $this->data = $data;
        $this->baseurl = $baseurl;
    }

    /**
     * Grab list URL.
     */
    public function get_id() {
        return substr($this->id, strrpos($this->id, '/') + 1);
    }

    /**
     * Returns the name of the category.
     */
    public function get_name() {
        return $this->data[Parser::INDEX_NAME_SPEC][0]['value'];
    }

    /**
     * Returns the parent categories.
     */
    public function get_parents() {
        $data = $this->data;

        if (empty($data[Parser::INDEX_PARENT_SPEC])) {
            return array();
        }

        // Okay. We first grab all of our categories.
        $categories = array();
        foreach ($data[Parser::INDEX_PARENT_SPEC] as $category) {
            $categories[] = $this->api->get_category($category['value']);
        }

        return $categories;
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
        return $this->id;
    }
}