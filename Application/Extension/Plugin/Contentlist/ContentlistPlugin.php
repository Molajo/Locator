<?php
/**
 * @package   Molajo
 * @copyright 2013 Amy Stephen. All rights reserved.
 * @license   http://www.opensource.org/licenses/mit-license.html MIT License
 */
namespace Molajo\Plugin\Contentlist;

use Molajo\Plugin\AbstractPlugin;


/**
 * Contentlist
 *
 * @package     Molajo
 * @license     MIT
 * @since       1.0
 */
class ContentlistPlugin extends AbstractPlugin
{

    /**
     * Retrieves Contentlist of data, according to parameters
     *
     * @return boolean
     * @since   1.0
     */
    public function onAfterReadall()
    {

        if (strtolower($this->get('template_view_path_node', '', 'parameters')) == 'contentlist') {
        } else {
            return true;
        }

        /** Retrieve Data */
        $controller_class_namespace = $this->controller_namespace;
        $controller                 = new $controller_class_namespace();
        $controller->getModelRegistry(
            $this->get('model_type', '', 'parameters'),
            $this->get('model_name', '', 'parameters')
        );
        $controller->setDataobject();
        $controller->connectDatabase();

        $controller->set('get_customfields', 2, 'model_registry');
        $controller->set('use_special_joins', 1, 'model_registry');
        $controller->set('process_plugins', 1, 'model_registry');

        $prefix = $controller->get('primary_prefix', 'a', 'model_registry');

        $criteria_status = $this->get('criteria_status', '', 'parameters');
        $criteria_status = '1,2';
        if ($criteria_status == '') {
        } else {
            $controller->model->query->where(
                $controller->model->database->qn($prefix . '.' . 'status')
                . ' IN (' . $criteria_status . ')'
            );
        }

        $this->row = $controller->getData('list');

        return true;

        $ordering = $this->parameters->criteria_ordering;
        if ($ordering == 'Popular') {
            $ordering  = 'a.ordering'; //@todo hits
            $direction = 'ASC';

        } elseif ($ordering == 'Ordering') {
            $ordering  = 'a.ordering';
            $direction = 'ASC';

        } elseif ($ordering == 'Stickied') {
            $ordering  = 'a.stickied';
            $direction = 'ASC';

        } elseif ($ordering == 'Featured') {
            $ordering  = 'a.featured';
            $direction = 'ASC';

        } else {
            $ordering  = 'a.start_publishing_datetime';
            $direction = 'DESC';
        }
        $controller->model->query->order($controller->model->database->qn($ordering) . ' ' . $direction);

        $controller->set('model_offset', 0, 'model_registry');
        $count = $this->parameters->criteria_count;
        if ((int)$count == 0) {
            $count = 5;
        }
        $controller->set('model_count', $count);

    }
}
