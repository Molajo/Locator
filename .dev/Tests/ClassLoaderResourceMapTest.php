<?php
/**
 * Class Locator Tests
 *
 * @package   Molajo
 * @copyright 2013 Amy Stephen. All rights reserved.
 * @license   http://www.opensource.org/licenses/mit-license.html MIT License
 */
namespace Molajo\Locator\Tests;

use PHPUnit_Framework_TestCase;

/**
 * Class Locator Tests
 *
 * @package   Molajo
 * @copyright 2013 Amy Stephen. All rights reserved.
 * @license   http://www.opensource.org/licenses/mit-license.html MIT License
 * @since     1.0
 */
class ClassLocatorResourceMap extends PHPUnit_Framework_TestCase
{
    /**
     * Locator Adapter
     *
     * @var    object  Molajo/Kernel/Locator/Adapter
     * @since  1.0
     */
    protected $loader;

    /**
     * Exclude when these values are found in the path
     *
     * @var    array
     * @since  1.0
     */
    protected $exclude_in_path_array = array(
        '.dev',
        '.travis.yml',
        '.DS_Store',
        '.git',
        '.',
        '..',
        '.gitattributes',
        '.gitignore'
    );

    /**
     * Exclude these pairs during build
     *
     * @var    array
     * @since  1.0
     */
    protected $exclude_path_array = array(
        'Kernel/Event/Service' => 'Molajo\\Kernel\\Event\\Service',
        'User/Service'         => 'Molajo\\User\\Service'
    );

    /**
     * Valid extensions
     *
     * @var    array
     * @since  1.0
     */
    protected $valid_extensions_array = array();

    /**
     * Set up
     *
     * @return  void
     * @since   1.0
     */
    protected function setUp()
    {
        $file_extensions          = array();
        $file_extensions['Class'] = '.php';
        $namespace_prefixes       = array();

        $paths = array();
        $paths[] = '.dev/Classes';
        $namespace_prefixes['Molajo\\Test'] = $paths;

        $paths = array();
        $paths[] = '.dev/Psr0/Molajo/Locator';
        $namespace_prefixes['Molajo\\Locator'] = $paths;

        $base_path = BASE_FOLDER;
        $rebuild_map = true;
        $resource_map_filename = __DIR__ . '/' . 'ResourceMap.json';

        $class                 = 'Molajo\\Kernel\\Locator\\Utilities\\ResourceMap';
        $resource_map_instance = new $class (
            $namespace_prefixes,
            $base_path,
            $rebuild_map,
            $resource_map_filename,
            $this->exclude_in_path_array,
            $this->exclude_path_array,
            $this->valid_extensions_array
        );

        $class            = 'Molajo\\Kernel\\Locator\\Handler\\ClassLocator';
        $handler_instance = new $class (
            $file_extensions,
            $namespace_prefixes,
            $base_path,
            $rebuild_map,
            $resource_map_filename,
            $this->exclude_in_path_array,
            $this->exclude_path_array,
            $this->valid_extensions_array,
            $resource_map_instance
        );

        $class            = 'Molajo\\Kernel\\Locator\\Adapter';
        $this->locator = new $class (
            $handler_instance,
            'Class'
        );
    }

    /**
     * Retrieve path from namespace resource map
     *
     * @covers  Molajo\Locator\Handler\Default::validate
     * @return  void
     * @since   1.0
     */
    public function testValidateNamespace1()
    {
        $class = 'Molajo\Test\System\CacheMock';
        $cache  = new $class();

        $this->assertEquals(1, $cache->get('foo'));
        $this->assertEquals(2, $cache->get('bar'));

        return;
    }

    /**
     * Class reloads from previous, lower case class name
     *
     * @covers  Molajo\Locator\Handler\Default::validate
     * @return  void
     * @since   1.0
     */
    public function testValidateNamespace2()
    {
        $class = 'molajo\test\system\cachemock';
        $cache  = new $class();

        $this->assertEquals(3, $cache->get('baz'));

        return;
    }

    /**
     * Class is not found - with class exists
     *
     * @covers  Molajo\Locator\Handler\Default::validate
     * @return  void
     * @since   1.0
     */
    public function testValidateNamespaceNotFound()
    {
        $class = 'Molajo\Test\System\CacheMock\XYZ';
        $found = false;
        if (class_exists($class)) {
            $found = true;
        }
        $this->assertEquals(false, $found);

        return;
    }

    /**
     * Class is found - with class exists
     *
     * @covers  Molajo\Locator\Handler\Default::validate
     * @return  void
     * @since   1.0
     */
    public function testValidateNamespaceExists()
    {
        $class = 'Molajo\Test\System\StandardMock';
        $found = false;
        if (class_exists($class)) {
            $found = true;
        }
        $this->assertEquals(true, $found);

        return;
    }

    /**
     * Retrieve path from namespace resource map
     *
     * @covers  Molajo\Locator\Handler\Default::validate
     * @return  void
     * @since   1.0
     */
    public function testFindLowercaseClass()
    {
        $class = 'molajo\test\system\configurationmock';
        $cache  = new $class();

        $this->assertEquals(3, $cache->get('baz'));

        return;
    }

    /**
     * Matching file - but does not match file extension
     *
     * @covers  Molajo\Locator\Handler\Default::validate
     * @return  void
     * @since   1.0
     */
    public function testDoNotFindXMLFileWithName()
    {
        $class = 'Molajo\Test\phpunit';
        $found = false;
        if (class_exists($class)) {
            $found = true;
        }
        $this->assertEquals(false, $found);

        return;
    }

    /**
     * Tear down
     *
     * @return void
     * @since   1.0
     */
    protected function tearDown()
    {
        $resource_map_filename = __DIR__ . '/' . 'ResourceMap.json';
        unlink($resource_map_filename);
    }
}
