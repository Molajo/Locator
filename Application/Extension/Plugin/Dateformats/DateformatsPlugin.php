<?php
/**
 * @package   Molajo
 * @copyright 2013 Amy Stephen. All rights reserved.
 * @license   http://www.opensource.org/licenses/mit-license.html MIT License
 */
namespace Molajo\Plugin\Dateformats;

use Molajo\Plugin\AbstractPlugin;


/**
 * Date Formats
 *
 * @package     Molajo
 * @license     MIT
 * @since       1.0
 */
class DateformatsPlugin extends AbstractPlugin
{
    /**
     * Pre-create processing
     *
     * @return boolean
     * @since   1.0
     */
    public function onBeforeCreate()
    {
        $fields = $this->retrieveFieldsByType('datetime');

        if (is_array($fields) && count($fields) > 0) {

            foreach ($fields as $field) {

                $name = $field['name'];

                $fieldValue = $this->getFieldValue($field);

                if ($name == 'modified_datetime') {

                    $this->saveField($field, $name, $this->model->now);

                    $modifiedByField = $this->getField('modified_by');
                    $modifiedByValue = $this->getFieldValue($modifiedByField);
                    if ($modifiedByValue === false) {
                        $this->saveField(
                            $modifiedByField,
                            'modified_by',
                            $this->user->get('id')
                        );
                    }

                } elseif ($fieldValue === false
                    || $fieldValue == '0000-00-00 00:00:00'
                ) {

                    $this->saveField($field, $name, $this->model->now);

                    if ($name == 'created_datetime') {
                        $createdByField = $this->getField('created_by');
                        $createdByValue = $this->getFieldValue($createdByField);
                        if ($createdByValue === false) {
                            $this->saveField(
                                $createdByField,
                                'created_by',
                                $this->user->get('id')
                            );
                        }

                    } elseif ($name == 'activity_datetime') {
                        $createdByField = $this->getField('user_id');
                        $createdByValue = $this->getFieldValue($createdByField);
                        if ($createdByValue === false) {
                            $this->saveField($createdByField, 'user_id', $this->user->get('id'));
                        }

                    }
                }
            }
        }

        return true;
    }

    /**
     * After-read processing
     *
     * Adds formatted dates to 'normal' or special fields recordset
     *
     * @return boolean
     * @since   1.0
     */
    public function onAfterRead()
    {
        $fields = $this->retrieveFieldsByType('datetime');

        try {
            $this->date->convertCCYYMMDD('2011-11-11');
            /** Skip when Date Service is not available (likely startup) */
        } catch (Exception $e) {
            return true;
        }

        if (is_array($fields) && count($fields) > 0) {

            foreach ($fields as $field) {

                $name = $field['name'];

                /** Retrieves the actual field value from the 'normal' or special field */
                $fieldValue = $this->getFieldValue($field);

                if ($fieldValue === false
                    || $fieldValue == '0000-00-00 00:00:00'
                    || $fieldValue == ''
                    || $fieldValue == null
                    || substr($name, 0, 12) == 'list_select_'

                ) {

                } else {

                    $newFieldValue = $this->date->convertCCYYMMDD($fieldValue);

                    if ($newFieldValue === false) {
                        $ccyymmdd = false;
                    } else {

                        /** Creates the new 'normal' or special field and populates the value */
                        $ccyymmdd = $newFieldValue;
                        $new_name = $name . '_ccyymmdd';
                        $this->saveField(null, $new_name, $newFieldValue);
                        $fieldValue = $newFieldValue;
                    }

                    /** Using newly formatted date, calculate NN days ago */
                    $newFieldValue = $this->date->getNumberofDaysAgo($fieldValue);

                    if ($newFieldValue === false) {
                        $this->saveField(null, $name . '_n_days_ago', null);
                        $this->saveField(null, $name . '_ccyy', null);
                        $this->saveField(null, $name . '_mm', null);
                        $this->saveField(null, $name . '_dd', null);
                        $this->saveField(null, $name . '_month_name_abbr', null);
                        $this->saveField(null, $name . '_month_name', null);
                        $this->saveField(null, $name . '_time', null);
                        $this->saveField(null, $name . '_day_number', null);
                        $this->saveField(null, $name . '_day_name_abbr', null);
                        $this->saveField(null, $name . '_day_name', null);

                    } else {

                        /** Creates the new 'normal' or special field and populates the value */
                        $new_name = $name . '_n_days_ago';
                        $this->saveField(null, $new_name, $newFieldValue);
                    }

                    /** Pretty Date */
                    $newFieldValue = $this->date->getPrettyDate($fieldValue);

                    if ($newFieldValue === false) {
                    } else {

                        /** Creates the new 'normal' or special field and populates the value */
                        $new_name = $name . '_pretty_date';
                        $this->saveField(null, $new_name, $newFieldValue);
                    }

                    /** Date Parts */
                    if ($ccyymmdd === false) {
                    } else {
                        $this->saveField(null, $name . '_ccyy', substr($ccyymmdd, 0, 4));
                        $this->saveField(null, $name . '_mm', (int)substr($ccyymmdd, 5, 2));
                        $this->saveField(null, $name . '_dd', (int)substr($ccyymmdd, 8, 2));

                        $this->saveField(
                            null,
                            $name . '_ccyy_mm_dd',
                            substr($ccyymmdd, 0, 4) . '-' . substr($ccyymmdd, 5, 2) . '-' . substr($ccyymmdd, 8, 2)
                        );

                        $newFieldValue = $this->date->getMonthName((int)substr($ccyymmdd, 5, 2), true);
                        $this->saveField(null, $name . '_month_name_abbr', $newFieldValue);

                        $newFieldValue = $this->date->getMonthName((int)substr($ccyymmdd, 5, 2), false);
                        $this->saveField(null, $name . '_month_name', $newFieldValue);

                        $dateObject = $this->date->getDate($fieldValue);
                        $this->saveField(null, $name . '_time', date_format($dateObject, 'G:ia'));

                        $newFieldValue = (int)date_format($dateObject, 'N');
                        $this->saveField(null, $name . '_day_number', $newFieldValue);

                        $newFieldValue = $this->date->getDayName((int)date_format($dateObject, 'N'), true);
                        $this->saveField(null, $name . '_day_name_abbr', $newFieldValue);

                        $newFieldValue = $this->date->getDayName((int)date_format($dateObject, 'N'), false);
                        $this->saveField(null, $name . '_day_name', $newFieldValue);
                    }
                }
            }
        }

        return true;
    }
}
