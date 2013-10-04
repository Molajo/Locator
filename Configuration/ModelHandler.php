<?php
/**
 * Configuration: Xml: Model Handler
 *
 * @package   Molajo
 * @license   http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 2013 Amy Stephen. All rights reserved.
 */
namespace Molajo\Resources\Configuration;

use Molajo\Registry\Api\RegistryInterface;
use Molajo\Resources\Api\ConfigurationInterface;
use Molajo\Resources\Api\ConfigurationDataInterface;
use Molajo\Resources\Api\ResourceAdapterInterface;
use Molajo\Resources\Exception\ConfigurationException;

/**
 * Configuration: Xml: Model Handler
 *
 * @author    Amy Stephen
 * @license   http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 2013 Amy Stephen. All rights reserved.
 * @since     1.0
 */
class ModelHandler extends AbstractHandler implements ConfigurationInterface
{
    /**
     * Constructor
     *
     * @param ConfigurationDataInterface $dataobject
     * @param RegistryInterface          $registry
     * @param ResourceAdapterInterface   $resource
     *
     * @since  1.0
     */
    public function __construct(
        ConfigurationDataInterface $dataobject,
        RegistryInterface $registry,
        ResourceAdapterInterface $resource
    ) {
        parent::__construct($dataobject, $registry, $resource);
    }

    /**
     * Load registry for requested model resource, returning name of registry collection
     *
     * @param   string $model_type
     * @param   string $model_name
     * @param   object $xml
     *
     * @return  string  Name of registry model
     * @since   1.0
     * @throws  ConfigurationException
     */
    public function getConfiguration($model_type, $model_name, $xml)
    {
        $model_registry = ucfirst(strtolower($model_name)) . ucfirst(strtolower($model_type));

        $xml = $this->getIncludeCode($xml);
        if ($xml === false) {
            throw new ConfigurationException
            ('Configuration: getDataobject cannot process XML file for Model Type: '
            . $model_type . ' Model Name: ' . $model_name);
        }

        if (isset($xml->model)) {
            $xml = $xml->model;
        }

        $this->registry->createRegistry($model_registry);

        $this->inheritDefinition($model_registry, $xml);

        $this->setModelRegistry($model_registry, $xml);

        $this->registry->set($model_registry, 'model_name', $model_name);
        $this->registry->set($model_registry, 'model_type', $model_type);
        $this->registry->set($model_registry, 'model_registry_name', $model_registry);

        $data_object = $this->registry->get($model_registry, 'data_object', '');

        if ($data_object == '') {
            $data_object = 'Database';
            $this->registry->set($model_registry, 'data_object', $data_object);
        }

        $dataObjectRegistry = ucfirst(strtolower($data_object)) . 'Dataobject';

        if ($this->registry->exists($dataObjectRegistry)) {
        } else {
            $this->resource->get('xml:///Molajo//Dataobject//' . ucfirst(strtolower($data_object)) . '.xml');
        }

        foreach ($this->registry->get($dataObjectRegistry) as $key => $value) {
            $this->registry->set($model_registry, 'data_object_' . $key, (string)$value);
        }

        $this->setElementsRegistry(
            $model_registry,
            $xml,
            'fields',
            'field',
            $this->dataobject->get('valid_field_attributes')
        );

        $this->setElementsRegistry(
            $model_registry,
            $xml,
            'joins',
            'join',
            $this->dataobject->get('valid_join_attributes')
        );

        $this->setElementsRegistry(
            $model_registry,
            $xml,
            'foreignkeys',
            'foreignkey',
            $this->dataobject->get('valid_foreignkey_attributes')
        );

        $this->setElementsRegistry(
            $model_registry,
            $xml,
            'criteria',
            'where',
            $this->dataobject->get('valid_criteria_attributes')
        );

        $this->setElementsRegistry(
            $model_registry,
            $xml,
            'children',
            'child',
            $this->dataobject->get('valid_children_attributes')
        );

        $this->setElementsRegistry(
            $model_registry,
            $xml,
            'plugins',
            'plugin',
            $this->dataobject->get('valid_plugin_attributes')
        );

        $this->setElementsRegistry(
            $model_registry,
            $xml,
            'values',
            'value',
            $this->dataobject->get('valid_value_attributes')
        );

        $this->getCustomFields($model_registry, $xml);

        return $this->registry->getArray($model_registry);
    }

    /**
     * Store Configuration Data in Registry
     *
     * @param   string $model_registry
     * @param   object $xml
     *
     * @return  bool
     * @since   1.0
     * @throws  ConfigurationException
     */
    public function setModelRegistry($model_registry, $xml)
    {
        $modelArray = $this->dataobject->get('valid_model_attributes');

        foreach ($xml->attributes() as $key => $value) {

            if (in_array($key, $modelArray)) {
                $this->registry->set($model_registry, $key, (string)$value);

            } else {
                throw new ConfigurationException
                ('Configuration: setModelRegistry encountered Invalid Model Attribute ' . $key);
            }
        }

        $this->registry->set(
            $model_registry,
            'model_name',
            $this->registry->get($model_registry, 'name')
        );

        return $this;
    }
}