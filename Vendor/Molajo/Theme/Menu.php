<?php
/**
 * @package   Molajo
 * @copyright 2013 Amy Stephen. All rights reserved.
 * @license   http://www.opensource.org/licenses/mit-license.html MIT License
 */
namespace Molajo\Theme;


/**
 * Menu
 *
 * @package     Molajo
 * @subpackage  Service
 * @since       1.0
 */
class Menu
{
    /**
     * Retrieves an array of active menuitems, including the current menuitem and its parents
     *
     * @param int $current_menuitem_id
     *
     * @return array|bool
     * @since   1.0
     */
    public function getMenuBreadcrumbIds($current_menuitem_id)
    {
        if ($current_menuitem_id == 0) {
            return false;
        }

        $controller_class_namespace = $this->controller_namespace;
        $controller                 = new $controller_class_namespace();
        $controller->getModelRegistry('datasource', 'MenuitemsNested', 1);

        $controller->model->query->where(
            $controller->model->database->qn('current_menuitem.id')
            . ' = ' . (int)$current_menuitem_id
        );
        $controller->model->query->where($controller->model->database->qn('a.status') . ' > 0');

        $controller->model->query->order('a.lft DESC');

        $controller->set('model_offset', 0, 'model_registry');
        $controller->set('model_count', 999999, 'model_registry');

        $query_results = $controller->getData('list');

        $look_for_parent = 0;

        $select = array();
        $i      = 0;
        foreach ($query_results as $item) {

            if ($look_for_parent == 0) {
                $select[]        = $i;
                $look_for_parent = $item->parent_id;

            } else {
                if ($look_for_parent == $item->id) {
                    $select[]        = $i;
                    $look_for_parent = $item->parent_id;
                }
            }
            $i ++;
        }

        rsort($select);
        $breadcrumbs = array();
        foreach ($select as $index) {
            $breadcrumbs[] = $query_results[$index];
        }

        return $breadcrumbs;
    }

    /**
     * Retrieve requested menu, format data, build link, verify ACL
     *
     * @param int $menu_id
     * @param int $current_menu_item
     *
     * @return array|bool
     * @since   1.0
     */
    public function get($menu_id, $current_menu_item = 0, $bread_crumbs = array())
    {
        $controller_class_namespace = $this->controller_namespace;
        $controller                 = new $controller_class_namespace();
        $controller->getModelRegistry('System', 'Menuitems', 1);

        $controller->set('get_customfields', 0, 'model_registry');
        $controller->set('use_special_joins', 0, 'model_registry');
        $controller->set('process_plugins', 0, 'model_registry');
        $controller->set('check_view_level_access', 1, 'model_registry');

        $controller->model->query->select($controller->model->database->qn('a.id'));
        $controller->model->query->select($controller->model->database->qn('a.extension_id'));
        $controller->model->query->select($controller->model->database->qn('a.catalog_type_id'));
        $controller->model->query->select($controller->model->database->qn('a.title'));
        $controller->model->query->select($controller->model->database->qn('a.subtitle'));
        $controller->model->query->select($controller->model->database->qn('a.path'));
        $controller->model->query->select($controller->model->database->qn('a.alias'));
        $controller->model->query->select($controller->model->database->qn('a.root'));
        $controller->model->query->select($controller->model->database->qn('a.parent_id'));
        $controller->model->query->select($controller->model->database->qn('a.lvl'));
        $controller->model->query->select($controller->model->database->qn('a.lft'));
        $controller->model->query->select($controller->model->database->qn('a.rgt'));
        $controller->model->query->select($controller->model->database->qn('a.home'));
        $controller->model->query->select($controller->model->database->qn('a.parameters'));
        $controller->model->query->select($controller->model->database->qn('a.ordering'));

        $controller->model->query->where($controller->model->database->qn('a.extension_id') . ' = ' . (int)$menu_id);
        $controller->model->query->where($controller->model->database->qn('a.status') . ' > 0');

        $controller->model->query->where($controller->model->database->qn('catalog.enabled') . ' = 1');

        $controller->model->query->order('a.lft');

        $controller->set('model_offset', 0, 'model_registry');
        $controller->set('model_count', 999999, 'model_registry');
        $controller->set('get_customfields', 2, 'model_registry');
        $controller->set('use_special_joins', 1, 'model_registry');
        $controller->set('process_plugins', 0, 'model_registry');

        $query_results = $controller->getData('list');
        if ($query_results === false) {
            return array();
        }

        foreach ($query_results as $item) {

            $item->menu_id = $item->extension_id;

            if ($item->id == $current_menu_item && (int)$current_menu_item > 0) {
                $item->css_class = 'current';
                $item->current   = 1;
            } else {
                $item->css_class = '';
                $item->current   = 0;
            }

            $item->active = 0;
            foreach ($bread_crumbs as $crumb) {
                if ($item->id == $crumb->id) {
                    $item->css_class .= ' active';
                    $item->active = 1;
                }
            }

            $item->css_class = trim($item->css_class);

            if ($this->application->get('url_sef', 1) == 1) {
                $item->url = Services::Url()->getApplicationURL($item->catalog_sef_request);
            } else {
                $item->url = Services::Url()->getApplicationURL('index.php?id=' . (int)$item->id);
            }

            if ($item->subtitle == '' || $item->subtitle == null) {
                $item->link_text = $item->title;
            } else {
                $item->link_text = $item->subtitle;
            }

            $item->link = $item->url;
        }

        return $query_results;
    }
}
