<?php
/**
 * Database Dependency Injector
 *
 * @package   Molajo
 * @license   http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 2013 Amy Stephen. All rights reserved.
 */
namespace Molajo\Service\Database;

use Exception;
use Molajo\IoC\Handler\AbstractInjector;
use Molajo\IoC\Api\ServiceHandlerInterface;
use Molajo\IoC\Exception\ServiceHandlerException;
use Molajo\Database\Adapter;

/**
 * Database Dependency Injector
 *
 * @author    Amy Stephen
 * @license   http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 2013 Amy Stephen. All rights reserved.
 * @since     1.0
 */
class DatabaseInjector extends AbstractInjector implements ServiceHandlerInterface
{
    /**
     * Constructor
     *
     * @param  $options
     *
     * @since  1.0
     */
    public function __construct(array $options = array())
    {
        $options['service_name']             = basename(__DIR__);
        $options['store_instance_indicator'] = true;
        $options['service_namespace']        = 'Molajo\\Database\\Adapter';

        parent::__construct($options);
    }

    /**
     * Instantiate a new handler and inject it into the Adapter for the ServiceHandlerInterface
     * Retrieve a list of Interface dependencies and return the data ot the controller.
     *
     * @return  array
     * @since   1.0
     * @throws  \Molajo\IoC\Exception\ServiceHandlerException
     */
    public function setDependencies(array $reflection = null)
    {
        parent::setDependencies($reflection);

        $this->dependencies              = array();
        $this->dependencies['Resources'] = array();
        $this->dependencies['Parameters'] = array();

        return $this->dependencies;
    }

    /**
     * Instantiate Class
     *
     * @return  object
     * @since   1.0
     * @throws  ServiceHandlerException
     */
    public function instantiateService()
    {
        $this->dependencies['Resources']->setNamespace(
            'molajo\\dataobject',
            BASE_FOLDER . $this->dependencies['Parameters']->site->base_folder . '/Dataobject',
            true
        );

        $configuration = $this->dependencies['Resources']->get('xml:///Molajo//Dataobject//Database.xml');

        $options                    = array();
        $options['db_type']         = $configuration['db_type'];
        $options['db_host']         = $configuration['db_host'];
        $options['db_user']         = $configuration['db_user'];
        $options['db_password']     = $configuration['db_password'];
        $options['db_name']         = $configuration['db'];
        $options['db_prefix']       = $configuration['db_prefix'];
        $options['process_plugins'] = $configuration['process_plugins'];
        $options['select']          = true;

        try {
            $handler = $this->getHandler($options);

            $this->service_instance = $this->getAdapter($handler);

        } catch (Exception $e) {
            echo $e->getMessage();

            throw new ServiceHandlerException
            ('IoC: Injector Instance Failed for ' . $this->service_namespace
            . ' failed.' . $e->getMessage());
        }

        return $this;
    }

    /**
     * Get the Database specific Adapter Handler
     *
     * @param   string $handler
     *
     * @return  object
     * @since   1.0
     * @throws  ServiceHandlerException
     */
    protected function getHandler($options)
    {
        $class = 'Molajo\\Database\\Handler\\Joomla';

        try {
            $handler = new $class($options);

        } catch (Exception $e) {
            throw new ServiceHandlerException
            ('Database: Could not instantiate Database Adapter Handler ' . $class);
        }

        return $handler;
    }

    /**
     * Get Database Adapter, inject with specific Database Adapter Handler
     *
     * @param   object $handler
     *
     * @return  object
     * @since   1.0
     * @throws  ServiceHandlerException
     */
    protected function getAdapter($handler)
    {
        try {
            $adapter = new Adapter($handler);

        } catch (Exception $e) {
            throw new ServiceHandlerException
            ('Database: Could not instantiate Adapter');
        }

        return $adapter;
    }
}
