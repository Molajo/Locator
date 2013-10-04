<?php
/**
 * Metadata Service
 *
 * @package   Molajo
 * @license   http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 2013 Amy Stephen. All rights reserved.
 */
namespace Molajo\Theme\Handler;


use Molajo\Theme\Exception\DocumentException;

/**
 * The Metadata Service collects Document Head information
 *
 * @author    Amy Stephen
 * @license   http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 2013 Amy Stephen. All rights reserved.
 * @since     1.0
 */
class Metadata
{
    /**
     * Title
     *
     * @var    string
     * @since  1.0
     */
    protected $title = null;

    /**
     * Description
     *
     * @var    string
     * @since  1.0
     */
    protected $description = null;

    /**
     * Language
     *
     * @var    string
     * @since  1.0
     */
    protected $language = null;

    /**
     * Language Direction
     *
     * @var    string
     * @since  1.0
     */
    protected $direction = null;

    /**
     * HTML5
     *
     * @var    object
     * @since  1.0
     */
    protected $html5 = null;

    /**
     * Line end
     *
     * @var    object
     * @since  1.0
     */
    protected $line_end = null;

    /**
     * Mimetype
     *
     * @var    object
     * @since  1.0
     */
    protected $mimetype = 'text/html';

    /**
     * Request Date
     *
     * @var    string
     * @since  1.0
     */
    protected $request_date = null;

    /**
     * Modified Date
     *
     * @var    string
     * @since  1.0
     */
    protected $modified_date = null;

    /**
     * Array
     *
     * @var    object
     * @since  1.0
     */
    protected $metadata = null;

    /**
     * List of Properties
     *
     * @var    object
     * @since  1.0
     */
    protected $property_array = array(
        'title',
        'description',
        'direction',
        'language',
        'html5',
        'line_end',
        'mimetype',
        'request_date',
        'metadata',
        'head_query',
        'metadata_query',
        'dummy_query'
    );

    /**
     * Initialise the class for Metadata processing
     *
     * @since   1.0
     */
    public function __construct()
    {
        $this->metadata = array();
    }

    /**
     * Get the current value (or default) of the specified key
     *
     * @param   string $key
     * @param   mixed  $default
     *
     * @return  mixed
     * @since   1.0
     */
    public function getxxxx($key = null, $default = null)
    {
        $key = strtolower($key);

        if (in_array($key, $this->property_array)) {

            $query_results = array();

            if (strtolower($key) == 'metadata_query') {
                return $this->getMetadata();

            } elseif (strtolower($key) == 'dummy_query') {
                $temp_row = new \stdClass();

                $temp_row->name              = 'dummy row';
                $temp_row->application_html5 = $this->html5;
                $temp_row->end               = $this->line_end;

                $query_results[] = $temp_row;

                return $query_results;
            }

            if ($this->$key === null) {
                $this->$key = $default;
            }

            return $this->$key;
        }

        if (isset($this->metadata[$key])) {
        } else {
            $this->metadata[$key] = $default;
        }

        return $this->metadata[$key];
    }

    /**
     * Get the current value (or default) of the specified key
     *
     * @param   string $key
     * @param   mixed  $default
     *
     * @return  mixed
     * @since   1.0
     * @throws  ApplicationException
     */
    public function get($key, $default = null)
    {
        $key = strtolower($key);

        if (in_array($key, $this->property_array)) {
        } else {
            throw new DocumentException
            ('Metadata Service: attempting to get value for unknown key: ' . $key);
        }

        if ($this->key === null) {
            $this->$key = $default;
        }

        return $this->$key;
    }

    /**
     * Set the value of the specified key
     *
     * @param   string $key
     * @param   array  $value
     *
     * @return  mixed
     * @since   1.0
     */
    public function set($key, $value = array())
    {
        $key = strtolower($key);

        if (in_array($key, $this->property_array)) {
        } else {
            throw new DocumentException
            ('Metadata Service: attempting to set value for unknown key: ' . $key);
        }

        $this->$key = $value;

        return $this->$key;
    }

    /**
     * Get Metadata - called from the Mvc as a query
     *
     * @return array
     * @since   1.0
     */
    protected function getMetadata()
    {
        $query_results = array();

        $temp_row = new \stdClass();

        $temp  = $this->get('title', '');
        $title = $temp[0];
        if (trim($title) == '') {
            $title = $parameters->site->name;
        }

        $temp_row->title              = $this->get('title', '');
        $temp_row->description        = $this->get('description', '');
        $temp_row->mimetype           = $this->get('mimetype', 'text/html');
        $temp_row->base               = SITE_BASE_URL;
        $temp_row->base_url           = BASE_URL;
        $temp_row->last_modified      = $this->get('modified_datetime', $this->get('request_date'));
        $temp_row->language_direction = $this->get('direction');
        $temp_row->language           = $this->get('language');
        $temp_row->application_html5  = $this->get('html5');
        $temp_row->line_end           = $this->get('line_end');

        $query_results[] = $temp_row;

        return $query_results;
    }

    /**
     * Remove item
     *
     * @param   string $key
     *
     * @return  mixed
     * @since   1.0
     * @throws  DocumentException
     */
    public function remove($key)
    {
        // TODO: Implement remove() method.
    }
}
