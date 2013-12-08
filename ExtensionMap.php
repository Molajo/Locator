<?php
/**
 * Extension Map
 *
 * @package    Molajo
 * @copyright  2013 Amy Stephen. All rights reserved.
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 */
namespace Molajo\Resource;

use stdClass;
use Exception;
use CommonApi\Resource\ExtensionsInterface;
use CommonApi\Exception\RuntimeException;

/**
 * Extensions
 *
 * @package    Molajo
 * @copyright  2013 Amy Stephen. All rights reserved.
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 * @since      1.0
 */
class ExtensionMap implements ExtensionsInterface
{
    /**
     * Stores an array of key/value parameters settings
     *
     * @var    object
     * @since  1.0
     */
    protected $parameters = null;

    /**
     * Resource Instance
     *
     * @var    object
     * @since  1.0
     */
    protected $resources;

    /**
     * Extensions Filename
     *
     * @var    string
     * @since  1.0
     */
    protected $extensions_filename;

    /**
     * Constructor
     *
     * @param  object $resources
     * @param  object $parameters
     * @param  string $extensions_filename
     *
     * @since  1.0
     */
    public function __construct(
        $resources,
        $parameters,
        $extensions_filename = null
    ) {
        $this->resources           = $resources;
        $this->parameters          = $parameters;
        $this->extensions_filename = $extensions_filename;
    }

    /**
     * Catalog Types
     *
     * @return  $this
     * @since   1.0
     * @throws  \CommonApi\Exception\RuntimeException
     */
    public function createMap()
    {
        $controller = $this->resources->get(
            'query:///Molajo//Datasource//CatalogTypes.xml',
            array('Parameters' => $this->parameters)
        );

        $controller->setModelRegistry('check_view_level_access', 0);
        $controller->setModelRegistry('process_events', 0);
        $controller->setModelRegistry('query_object', 'list');

        $controller->model->query->where(
            $controller->model->database->qn($controller->getModelRegistry('prefix', 'a'))
            . ' . '
            . $controller->model->database->qn('id')
            . ' IN ('
            . (int)$this->parameters->reference_data->catalog_type_plugin_id . ', '
            . (int)$this->parameters->reference_data->catalog_type_theme_id . ', '
            . (int)$this->parameters->reference_data->catalog_type_page_view_id . ', '
            . (int)$this->parameters->reference_data->catalog_type_template_view_id . ', '
            . (int)$this->parameters->reference_data->catalog_type_wrap_view_id . ', '
            . (int)$this->parameters->reference_data->catalog_type_menuitem_id . ', '
            . (int)$this->parameters->reference_data->catalog_type_resource_id
            . ')'
        );

        try {
            $results = $controller->getData();
        } catch (Exception $e) {
            throw new RuntimeException ($e->getMessage());
        }

        $catalog_type             = new stdClass();
        $catalog_type->names      = array();
        $catalog_type->ids        = array();
        $catalog_type->extensions = array();

        foreach ($results as $item) {
            $catalog_type->ids[$item->id]        = $item->title;
            $catalog_type->names[$item->title]   = $item->id;
            $catalog_type->extensions[$item->id] = $this->getExtensions($item->id, $item->model_name);
        }

        if (version_compare(PHP_VERSION, '5.4.0', '>=')) {
            file_put_contents($this->extensions_filename, json_encode($catalog_type, JSON_PRETTY_PRINT));
        } else {
            file_put_contents($this->extensions_filename, json_encode($catalog_type));
        }

        return $catalog_type;
    }

    /**
     * Retrieve Extension information for Catalog Type
     *
     * @param   int    $catalog_type_id
     * @param   string $catalog_type_model_name
     *
     * @return  array|stdClass
     * @since   1.0
     * @throws  \CommonApi\Exception\RuntimeException
     */
    protected function getExtensions($catalog_type_id, $catalog_type_model_name)
    {
        $controller
            = $this->resources->get(
            'query:///Molajo//Datasource//ExtensionInstances.xml',
            array('Parameters' => $this->parameters)
        );

        $controller->setModelRegistry('check_view_level_access', 0);
        $controller->setModelRegistry('process_events', 0);
        $controller->setModelRegistry('id', $catalog_type_id);
        $controller->setModelRegistry('get_customfields', 1);
        $controller->setModelRegistry('query_object', 'list');

        $controller->model->query->select(
            $controller->model->database->qn($controller->getModelRegistry('primary_prefix', 'a'))
            . ' . '
            . $controller->model->database->qn('id')
        );

        $controller->model->query->select(
            $controller->model->database->qn($controller->getModelRegistry('primary_prefix', 'a'))
            . ' . '
            . $controller->model->database->qn('alias')
        );

        $controller->model->query->where(
            $controller->model->database->qn($controller->getModelRegistry('primary_prefix', 'a'))
            . ' . '
            . $controller->model->database->qn('id')
            . ' <> '
            . $controller->model->database->qn($controller->getModelRegistry('primary_prefix', 'a'))
            . ' . '
            . $controller->model->database->qn('catalog_type_id')
        );

        $controller->model->query->where(
            $controller->model->database->qn($controller->getModelRegistry('primary_prefix', 'a'))
            . ' . '
            . $controller->model->database->qn('catalog_type_id')
            . ' = '
            . (int)$catalog_type_id
        );

        $controller->model->query->where(
            $controller->model->database->qn($controller->getModelRegistry('primary_prefix', 'a'))
            . ' . '
            . $controller->model->database->qn('status')
            . ' > '
            . ' 0 '
        );

        $controller->model->query->where(
            $controller->model->database->qn('catalog')
            . ' . '
            . $controller->model->database->qn('application_id')
            . ' = '
            . (int)$this->parameters->application->id
        );

        $controller->model->query->where(
            $controller->model->database->qn('catalog')
            . ' . '
            . $controller->model->database->qn('enabled')
            . ' = 1 '
        );

        $controller->model->query->where(
            $controller->model->database->qn('application_extension_instances')
            . ' . '
            . $controller->model->database->qn('application_id')
            . ' = '
            . $controller->model->database->qn('catalog')
            . ' . '
            . $controller->model->database->qn('application_id')
        );

        $controller->model->query->order(
            $controller->model->database->qn($controller->getModelRegistry('primary_prefix', 'a'))
            . ' . '
            . $controller->model->database->qn('alias')
        );

        try {
            $extensions = $controller->getData();

        } catch (Exception $e) {
            throw new RuntimeException ($e->getMessage());
        }

        if (is_array($extensions) && count($extensions) > 0) {
        } else {
            return array();
        }

        $temp             = new stdClass();
        $temp->ids        = array();
        $temp->names      = array();
        $temp->extensions = array();

        foreach ($extensions as $item) {
            $temp->ids[$item->id]        = $item->alias;
            $temp->names[$item->alias]   = $item->id;
            $temp->extensions[$item->id] = $this->getExtension($item->id, $item->alias, $catalog_type_model_name);
        }

        return $temp;
    }

    /**
     * Retrieve specific Extension Information
     *
     * @param   int    $id
     * @param   string $alias
     * @param   string $catalog_type_model_name
     *
     * @return  object
     * @since   1.0
     * @throws  \CommonApi\Exception\RuntimeException
     */
    protected function getExtension($id, $alias, $catalog_type_model_name)
    {
        $catalog_type_model_name = ucfirst(strtolower($catalog_type_model_name));
        $alias                   = ucfirst(strtolower($alias));
        $model                   = 'Molajo'
            . '//' . $catalog_type_model_name
            . '//' . $alias
            . '//Configuration.xml';

        $controller = $this->resources->get(
            'query:///' . $model,
            array('Parameters' => $this->parameters)
        );

        $controller->setModelRegistry('check_view_level_access', 0);
        $controller->setModelRegistry('process_events', 0);
        $controller->setModelRegistry('get_customfields', 1);
        $controller->setModelRegistry('primary_key_value', $id);
        $controller->setModelRegistry('query_object', 'item');

        try {
            $extension = $controller->getData();
        } catch (Exception $e) {
            echo 'RenderingExtensionsServiceProvider: Extension not found: ' . $alias;
            throw new RuntimeException ($e->getMessage());
        }

        return $extension;
    }
}
