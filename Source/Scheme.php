<?php
/**
 * Scheme
 *
 * @package    Molajo
 * @copyright  2014 Amy Stephen. All rights reserved.
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 */
namespace Molajo\Resource;

use stdClass;
use CommonApi\Resource\SchemeInterface;
use CommonApi\Exception\RuntimeException;

/**
 * Scheme
 *
 * @package    Molajo
 * @copyright  2014 Amy Stephen. All rights reserved.
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 * @since      1.0
 */
class Scheme implements SchemeInterface
{
    /**
     * Scheme array =>
     *    Scheme Name =>
     *      Extensions list
     *      Handler
     *
     * @var    array
     * @since  1.0
     */
    protected $scheme_array = array();

    /**
     * Constructor
     *
     * @param  string $scheme_filename
     *
     * @since  1.0
     */
    public function __construct($scheme_filename)
    {
        $this->readSchemes($scheme_filename);
    }

    /**
     * Get Scheme (or all schemes)
     *
     * @param   string $scheme
     *
     * @return  object|array
     * @since   1.0
     */
    public function getScheme($scheme = '')
    {
        $scheme = strtolower($scheme);

        if (isset($this->scheme_array[$scheme])) {
            return $this->scheme_array[$scheme];
        }

        return $this->scheme_array;
    }

    /**
     * Read File and populate scheme array
     *
     * @param  string $filename
     *
     * @return  void
     * @since   1.0
     * @throws  \CommonApi\Exception\RuntimeException
     */
    protected function readSchemes($filename)
    {
        $this->scheme_array = array();

        if (file_exists($filename)) {
        } else {
            throw new RuntimeException ('Scheme Class: filename not found - ' . $filename);
        }

        $input = file_get_contents($filename);

        $schemes = json_decode($input);

        if (count($schemes) == 0) {
            return;
        }

        foreach ($schemes as $values) {

            $scheme_name = '';
            $handler     = '';
            $extensions  = array();

            foreach ($values as $key => $value) {

                if ($key == 'Name') {
                    $scheme_name = $value;
                } elseif ($key == 'Handler') {

                    $handler = $value;
                } elseif ($key == 'RequireFileExtensions') {
                    $extensions = $value;
                } else {
                    throw new RuntimeException ('Resource File ' . $filename . ' unknown key: ' . $key);
                }
            }

            if ($scheme_name == '') {
                throw new RuntimeException ('Resource File ' . $filename . ' must provide Name for each Scheme.');
            }

            if ($handler == '') {
                $handler = $scheme_name;
            }

            if (is_array($extensions)) {
            } elseif (trim($extensions) == '') {
                $extensions = array();
            } else {
                $temp         = $extensions;
                $extensions   = array();
                $extensions[] = $temp;
            }

            $this->setScheme($scheme_name, $handler, $extensions, false);
        }

        return;
    }

    /**
     * Define Scheme, associated Handler and allowable file extensions (empty array means any extension allowed)
     *
     * @param   string $scheme_name
     * @param   string $handler
     * @param   array  $extensions
     * @param   bool   $replace
     *
     * @return  $this
     * @since   1.0
     * @throws  \CommonApi\Exception\RuntimeException
     */
    public function setScheme($scheme_name, $handler = 'File', array $extensions = array(), $replace = false)
    {
        $scheme = new stdClass();

        $scheme->name = strtolower(trim($scheme_name));
        if ($scheme->name == '') {
            throw new RuntimeException ('Resource File ' . $scheme_name . ' must provide Name for each Scheme.');
        }

        $scheme->handler = ucfirst(strtolower(trim($handler))) . 'Handler';

        $scheme->handler_class = 'Molajo\\Resource\\Handler\\' . $scheme->handler;

        if (class_exists($scheme->handler_class)) {
        } else {
            throw new RuntimeException ('Resource Scheme Handler Class: ' . $scheme->handler_class);
        }

        $scheme->include_file_extensions = $extensions;

        $this->scheme_array[$scheme->name] = $scheme;

        return $this;
    }
}
