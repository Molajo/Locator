<?php
/**
 * @package   Molajo
 * @copyright 2013 Amy Stephen. All rights reserved.
 * @license   http://www.opensource.org/licenses/mit-license.html MIT License
 */
namespace Molajo\Plugin\Pagetypeapplication;

use Molajo\Plugin\AbstractPlugin;


/**
 * @package     Molajo
 * @license     MIT
 * @since       1.0
 */
class PagetypeapplicationPlugin extends AbstractPlugin
{
    /**
     * Prepares Configuration Data
     *
     * @return boolean
     * @since    1.0
     */
    public function onBeforeParse()
    {
        if (strtolower($this->get('page_type', '', 'parameters')) == PAGE_TYPE_APPLICATION) {
        } else {
            return true;
        }

        $resource_model_type = $this->get('model_type', '', 'parameters');
        $resource_model_name = $this->get('model_name', '', 'parameters');

        $this->content_helper->getResourceExtensionParameters(
            (int)$this->get('criteria_extension_instance_id', 0, 'parameters')
        );

        /** Array - All Pages in Set
         * {{Basic,basic}}{{User,user}}{{Media,media}}{{System,system}}{{Views,views}}{{Form,form}}{{Item,item}}{{List,list}}
         */
        $temp = $this->get('application_array');

        $pages = explode('{{', $temp);

        /** Determine Current Page of Set */
        $temp    = $this->registry->get('parameters', 'request_filters', array());
        $filters = explode(',', $temp);

        $page = 1;
        if ($filters == '' || count($filters) == 0) {
            $page = 1;
        } else {
            foreach ($filters as $x) {
                if (trim($x) == '') {
                } else {
                    $pair = explode(':', $x);
                    if (strtolower($pair[0]) == 'page') {
                        $page = (int)$pair[1];
                        break;
                    }
                }
            }
        }

        if ($page < count($pages)) {
        } else {
            $page = 1;
        }
        $page_number = $page;

        /** Submenu: Links to various Form Pages (Tabs) - ex. Basic, Metadata, Fields, etc. */
        $pageArray = array();
        $i         = 0;
        foreach ($pages as $item) {
            if ($item == '') {
            } else {
                $i ++;
                $temp_row     = new \stdClass();
                $temp_row->id = $i;
                if ($i == $page_number) {
                    $temp_row->current = 1;
                } else {
                    $temp_row->current = 0;
                }
                $temp_row->id    = $i;
                $temp_row->title = substr($item, 0, strpos($item, ','));
                $temp_row->url   = $this->registry->get('Page', 'page_url') . '/page/' . $i;

                $pageArray[] = $temp_row;
            }
        }
        $this->registry->set('Page', 'SectionSubmenu', $pageArray);

        /** Even tho links are created to each form page, generate Form for the current page, only */
        $current_page = '{{' . $pages[$page_number];

        /**
         * $pageFieldsets - contains two fields: page_count and page_array
         *
         *     page_count - the number of pages created (will be 1 for this use)
         *
         *     page_array: several fields that will be used by the primary view to display titles
         *        and create the include that contains the form fieldsets
         *
         *    Example page_array: Basic Page (tab 1)
         *         page_title: Basic
         *         page_title_extended: Articles Basic Configuration
         *         page_namespace: application
         *         page_link: applicationbasic
         *
         *         Form View to include and the Registry containing Form contents:
         *             fieldset_template_view: Formpage
         *             fieldset_template_view_parameter: Formpageapplicationbasic
         *
         */
        $form = Services::Form();

        $pageFieldsets = $form->setPageArray(
            $current_page,
            strtolower($this->get('page_type', '', 'parameters')),
            $resource_model_type,
            $resource_model_name,
            $this->get('criteria_extension_instance_id', 0, 'parameters'),
            array()
        );

        /** Set the View Model Parameters and Populate the Registry used as the Model */
        $current_page = $form->getPages($pageFieldsets[0]->page_array, $pageFieldsets[0]->page_count);

        $controller->set('request_model_type', $this->get('model_type', '', 'parameters'), 'model_registry');
        $controller->set('request_model_name', $this->get('model_name', '', 'parameters'), 'model_registry');

        $controller->set('model_type', 'Dataobject', 'model_registry');
        $controller->set('model_name', 'Primary', 'model_registry');
        $controller->set('model_query_object', 'list', 'model_registry');

        $controller->set('model_type', 'list', 'model_registry');
        $controller->set('model_name', 'Primary', 'model_registry');

        $this->registry->set(
            'Primary',
            'Data',
            $current_page
        );

        return true;
    }

}
