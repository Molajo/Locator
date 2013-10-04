<?php
/**
 * Plugin
 *
 * @package   Molajo
 * @license   http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 2013 Amy Stephen. All rights reserved.
 */
namespace Molajo\Plugin;

use OutOfRangeException;


/**
 * Base Plugin Class
 *
 * At various points in the Application, Events are Scheduled and data passed to the Event Service.
 *
 * The Event Service triggers all Plugins registered for the Event, one at a time, passing the
 * data into this Plugin.
 *
 * As each triggered Plugin finishes, the Event Service retrieves the class properties, returning
 * the values to the scheduling process.
 *
 * The base plugin takes care of getting and setting values and providing connectivity to Helper
 * functions and other commonly used data and connections.
 *
 * @author    Amy Stephen
 * @license   http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 2013 Amy Stephen. All rights reserved.
 * @since     1.0
 */
abstract class AbstractPlugin
{
    /**
     * Get the current value (or default) of the specified property
     *
     * @param   string $key
     * @param   mixed  $default
     * @param   string $property
     *
     * @return  mixed
     * @since   1.0
     * @throws  OutOfRangeException
     */
    public function get($key, $default = null, $property = '')
    {
        $value = null;

        if (in_array($key, $this->property_array) && $property == '') {
            $value = $this->$key;

            return $value;
        }

        if ($property == 'model_registry') {
            if (isset($this->model_registry->$key)) {
                return $this->model_registry->$key;
            }
            $this->model_registry->$key = $default;

            return $this->model_registry->$key;
        }

        if ($property == 'model') {
            return $this->model->$key;
        }

        if (isset($this->parameters->$key)) {
            return $this->parameters->$key;
        }

        $this->parameters->$key = $default;

        return $this->parameters->$key;
    }

    /**
     * Set the value of a property
     *
     * Initially, the setter is used by the plugin_event processPluginclass method
     *  to establish initial property values sent in by the scheduling method
     *
     * Changes to data will be used collected and used by the Mvc
     *
     * @param string $key
     * @param string $value
     * @param string $property
     *
     * @return mixed
     * @since   1.0
     * @throws  OutOfRangeException
     */
    public function set($key, $value = null, $property = '')
    {
        if (in_array($key, $this->property_array) && $property == '') {
            $this->$key = $value;

            if ($key == 'model_registry') {
                if (isset($this->model_registry['model_registry_name'])) {
                    $this->set('model_registry_name', $this->model_registry['model_registry_name'], 'model_registry');
                }
            }

            return $this->$key;
        }

        if ($property == 'model_registry') {
            $this->model_registry->$key = $value;

            return $this->model_registry->$key;
        }

        if ($property == 'model') {
            $this->model->$key = $value;

            return $this->model->$key;
        }

        $this->parameters->$key = $value;

        return $this->parameters->$key;
    }

    /**
     * Retrieve Fields for a specified Data Type
     *
     * @param string $type
     *
     * @return array
     * @since   1.0
     */
    public function retrieveFieldsByType($type)
    {
        $results = array();

        foreach ($this->model_registry['fields'] as $field) {
            if ($field['type'] == $type) {
                $results[] = $field;
            }
        }

        return $results;
    }

    /**
     * Get Field Definition for specific Field Name
     *
     * @param string $name
     *
     * @return bool
     * @since   1.0
     */
    public function getField($name)
    {

        foreach ($this->model_registry['fields'] as $field) {

            //if ((int) $field['foreignkey'] = 0) {

            //} else {
            //    if ($field['as_name'] == '') {
            if ($field['name'] == $name) {
                return $field;
            }
            //    } else {
            //        if ($field['foreignkey'] == $name) {
            //            return $field;
            //         }
            //     }
            //  }
        }

        return false;
    }

    /**
     * getFieldValue retrieves the actual field value from the 'normal' or special field
     *
     * @param   $field
     *
     * @return bool
     * @since   1.0
     */
    public function getFieldValue($field)
    {
        if (is_array($field)) {
        } else {
            return false;
        }

//        if (isset($field['as_name'])) {
//            if ($field['as_name'] == '') {
        $name = $field['name'];
//            } else {
//                $name = $field['as_name'];
//            }
//        } else {
//            $name = $field['name'];
//        }

        if (isset($this->row->$name)) {
            return $this->row->$name;

        } elseif (isset($field->customfield)) {
            //@todo review this - it seems unnecessary
            if ($this->registry->exists($this->get('model_name', '', 'parameters') . $field->customfield, $name)) {
                return $this->registry->get(
                    $this->get('model_name', '', 'parameters') . $field->customfield,
                    $name
                );
            }
        }

        return false;
    }

    /**
     * saveField adds a field to the 'normal' or special field group
     *
     * @param   $field
     * @param   $new_field_name
     * @param   $value
     *
     * @return void
     * @since   1.0
     */
    public function saveField($field, $new_field_name, $value)
    {
        if (is_array($field)) {
            $name = $field['name'];
        } else {
            $name = $new_field_name;
        }

        if (isset($this->row->$name)) {
            $this->row->$name = $value;

        } elseif (isset($this->parameters[$name])) {
            $this->parameters[$name] = $value;

        } else {
            if (is_object($this->row)) {
            } else {
                $this->row = new \stdClass();
            }
            $this->row->$new_field_name = $value;
        }

        return;
    }

    /**
     * saveForeignKeyValue
     *
     * @param   $new_field_name
     * @param   $value
     *
     * @return void
     * @since   1.0
     */
    public function saveForeignKeyValue($new_field_name, $value)
    {
        if (isset($this->row->$new_field_name)) {
            return;
        }
        $this->row->$new_field_name = $value;

        return;
    }

    /**
     * Runs before Route and after Services and Helpers have been instantiated
     *
     * @return bool
     * @since   1.0
     */
    public function onAfterInitialise()
    {
        return true;
    }

    /**
     * Scheduled after Route has been determined. Parameters contain all instruction to produce primary request.
     *
     * @return bool
     * @since   1.0
     */
    public function onAfterRoute()
    {
        return true;
    }

    /**
     * Scheduled after core Authorise to augment, change authorisation process or override a failed test
     *
     * @return bool
     * @since   1.0
     */
    public function onAfterAuthorise()
    {
        return true;
    }

    /**
     * After Route and Permissions, the Theme/Page are parsed
     *
     * @return bool
     * @since   1.0
     */
    public function onBeforeParse()
    {
        return true;
    }

    /**
     * After the body render is complete and before the document head rendering starts
     *
     * @return bool
     * @since   1.0
     */
    public function onBeforeParseHead()
    {
        return true;
    }

    /**
     * On after parsing and rendering is complete
     *
     * @return bool
     * @since   1.0
     */
    public function onAfterParse()
    {
        return true;
    }

    /**
     * Pre-read processing
     *
     * @return bool
     * @since   1.0
     */
    public function onBeforeRead()
    {
        return true;
    }

    /**
     * Post-read processing - one row at a time
     *
     * @return bool
     * @since   1.0
     */
    public function onAfterRead()
    {
        return true;
    }

    /**
     * Post-read processing - all rows at one time from query_results
     *
     * @return bool
     * @since   1.0
     */
    public function onAfterReadall()
    {
        return true;
    }

    /**
     * After the Read Query has executed but Before Query results are injected into the View
     *
     * @return bool
     * @since   1.0
     */
    public function onBeforeRenderView()
    {
        return true;
    }

    /**
     * After the View has been rendered but before the output has been passed back to the Includer
     *
     * @return bool
     * @since   1.0
     */
    public function onAfterRenderView()
    {
        return true;
    }

    /**
     * plugin_event fires after execute for both display and non-display task
     *
     * @return bool
     * @since   1.0
     */
    public function onAfterExecute()
    {
        return true;
    }

    /**
     * Plugin that fires after all views are rendered
     *
     * @return bool
     * @since   1.0
     */
    public function onAfterResponse()
    {
        return true;
    }

    /**
     * Pre-create processing
     *
     * @return bool
     * @since   1.0
     */
    public function onBeforeCreate()
    {
        return true;
    }

    /**
     * Post-create processing
     *
     * @return bool
     * @since   1.0
     */
    public function onAfterCreate()
    {
        return true;
    }

    /**
     * Before update processing
     *
     * @return bool
     * @since   1.0
     */
    public function onBeforeUpdate()
    {
        return true;
    }

    /**
     * After update processing
     *
     * @return bool
     * @since   1.0
     */
    public function onAfterUpdate()
    {
        return true;
    }

    /**
     * Pre-delete processing
     *
     * @return bool
     * @since   1.0
     */
    public function onBeforeDelete()
    {
        return true;
    }

    /**
     * Post-delete processing
     *
     * @return bool
     * @since   1.0
     */
    public function onAfterDelete()
    {
        return true;
    }

    /**
     * Before logging in processing
     *
     * @return bool
     * @since   1.0
     */
    public function onBeforeLogin()
    {
        return true;
    }

    /**
     * After Logging in event
     *
     * @return bool
     * @since   1.0
     */
    public function onAfterLogin()
    {
        return true;
    }

    /**
     * Before logging out processing
     *
     * @return bool
     * @since   1.0
     */
    public function onBeforeLogout()
    {
        return true;
    }

    /**
     * After Logging out event
     *
     * @return bool
     * @since   1.0
     */
    public function onAfterLogout()
    {
        return true;
    }
}
