<?php
/**
 * @package   Molajo
 * @copyright 2013 Amy Stephen. All rights reserved.
 * @license   http://www.opensource.org/licenses/mit-license.html MIT License
 */
namespace Molajo\Plugin\Templatelist;

use Molajo\Plugin\AbstractPlugin;


/**
 * @package     Molajo
 * @license     MIT
 * @since       1.0
 */
class TemplatelistPlugin extends AbstractPlugin
{
    /**
     * Prepares data for the Administrator Grid  - run TemplatelistPlugin after AdminmenuPlugin
     *
     * @return boolean
     * @since   1.0
     */
    public function onAfterReadAll()
    {
        if (defined('ROUTE')) {
        } else {
            return true;
        }

        if (strtolower($this->get('template_view_path_node', '', 'parameters')) == 'list') {
        } else {
            return true;
        }

        if (isset($this->parameters->list_model_name)) {
        } else {
            return false;
        }
        $model_name = $this->parameters->list_model_name;

        if (isset($this->parameters->list_model_type)) {
            $model_type = $this->parameters->list_model_type;
        } else {
            $model_type = CATALOG_TYPE_RESOURCE_LITERAL;
        }
        if ($model_type == '') {
            $model_type = CATALOG_TYPE_RESOURCE_LITERAL;
        }

        $controller_class_namespace = $this->controller_namespace;
        $controller                 = new $controller_class_namespace();
        $controller->getModelRegistry($model_type, $model_name, 1);

        $primary_prefix = $controller->get('primary_prefix', 'a', 'model_registry');

        if (isset($this->parameters->list_ordering)) {
            $ordering = $this->parameters->list_ordering;
        } else {
            $ordering = '';
        }
        if (isset($this->parameters->list_model_ordering_direction)) {
            $direction = $this->parameters->list_model_ordering_direction;
        } else {
            $direction = 'ASC';
        }

        if ($ordering == '' || $ordering === null) {
        } else {
            if ($direction == '' || $direction === null) {
                $controller->model->query->order($controller->model->database->qn($ordering));
            } else {
                $controller->model->query->order(
                    $controller->model->database->qn($ordering)
                    . ' ' . $controller->model->database->qn($direction)
                );
            }
        }

        if (isset($this->parameters->list_model_offset)) {
            $offset = $this->parameters->list_model_offset;
        } else {
            $offset = 0;
        }

        if (isset($this->parameters->list_model_count)) {
            $count = $this->parameters->list_model_count;
        } else {
            $count = 0;
        }
        if ($count == 0) {
            if (isset($this->parameters->list_model_use_pagination)) {
                $pagination = $this->parameters->list_model_use_pagination;
            } else {
                $pagination = 0;
            }
        } else {
            $pagination = 1;
        }

        if ($pagination == 1) {
        } else {
            $pagination = 0;
        }

        $controller->set('model_offset', $offset);
        $controller->set('model_count', $count);
        $controller->set('use_pagination', $pagination);

        $this->row = $controller->getData('list');

        return true;
    }
}
