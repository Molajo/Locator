<?php
/**
 * Site Controller Dependency Injector
 *
 * @package   Molajo
 * @license   http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 2013 Amy Stephen. All rights reserved.
 */
namespace Molajo\Service\Site;

use Exception;
use stdClass;
use Molajo\IoC\Handler\AbstractInjector;
use Molajo\IoC\Api\ServiceHandlerInterface;
use Molajo\IoC\Exception\ServiceHandlerException;

/**
 * Site Controller Dependency Injector
 *
 * @author    Amy Stephen
 * @license   http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 2013 Amy Stephen. All rights reserved.
 * @since     1.0
 */
class SiteInjector extends AbstractInjector implements ServiceHandlerInterface
{
    /**
     * Constructor
     *
     * @param   $options
     *
     * @since   1.0
     */
    public function __construct(array $options = array())
    {
        $options['service_namespace'] = 'Molajo\\Controller\\SiteController';
        $options['service_name']      = basename(__DIR__);

        parent::__construct($options);
    }

    /**
     * Identify Class Dependencies for Constructor Injection
     *
     * @return  array
     * @since   1.0
     * @throws  \Molajo\IoC\Exception\ServiceHandlerException
     */
    public function setDependencies(array $reflection = null)
    {
        /**
         * To make certain all dependencies are filled before Site runs and continues
         * scheduling from the Resources schedule
         */
        $options                                 = array();
        $this->dependencies                      = array();
        $this->dependencies['Resources']         = $options;
        $this->dependencies['Registry']          = $options;
        $this->dependencies['Dispatcher']        = $options;
        $this->dependencies['Exceptionhandling'] = $options;
        $this->dependencies['Fieldhandler']      = $options;
        $this->dependencies['Request']           = $options;

        return $this->dependencies;
    }

    /**
     * Instantiate Class
     *
     * @return  void
     * @since   1.0
     * @throws  ServiceHandlerException
     */
    public function instantiateService()
    {
        $host           = $this->dependencies['Request']->host;
        $base_url       = $this->dependencies['Request']->base_url;
        $path           = $this->dependencies['Request']->path;
        $reference_data = $this->dependencies['Resources']->get('xml:///Molajo//Application//Defines.xml');
        $sites          = $this->sites();

        try {
            $class = $this->service_namespace;

            $this->service_instance = new $class(
                $host,
                $base_url,
                $path,
                $reference_data,
                $sites
            );

        } catch (Exception $e) {

            throw new ServiceHandlerException
            ('IoC instantiateService Failed: ' . $this->service_namespace . '  ' . $e->getMessage());
        }

        return;
    }

    /**
     * Installed Sites
     *
     * @return  $this
     * @since   1.0
     */
    public function sites()
    {
        $sitexml = $this->dependencies['Resources']->get('xml:///Molajo//Application//Sites.xml');

        if (count($sitexml) > 0) {
        } else {
            return $this;
        }

        $sites = array();

        foreach ($sitexml as $item) {
            $site                   = new stdClass();
            $site->id               = (string)$item['id'];
            $site->name             = (string)$item['name'];
            $site->site_base_url    = (string)$item['base'];
            $site->site_base_folder = (string)$item['folder'];
            $sites[]                = $site;
        }

        return $sites;
    }
}
