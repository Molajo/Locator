<?php
/**
 * @package   Molajo
 * @copyright 2013 Amy Stephen. All rights reserved.
 * @license   http://www.opensource.org/licenses/mit-license.html MIT License
 */
namespace Molajo\Plugin\Images;

use Molajo\Plugin\AbstractPlugin;


/**
 * Date Formats
 *
 * @package     Molajo
 * @license     MIT
 * @since       1.0
 */
class ImagesPlugin extends AbstractPlugin
{
    /**
     * Pre-create processing
     *
     * @return boolean
     * @since   1.0
     */
    public function onBeforeCreate()
    {
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
        $fields = $this->retrieveFieldsByType('image');

        if (is_array($fields) && count($fields) > 0) {

            foreach ($fields as $field) {

                $name = $field['name'];

                $fieldValue = $this->getFieldValue($field);

                if ($fieldValue === false) {

                } else {

                    $newFieldValue = $this->date->convertCCYYMMDD($fieldValue);

                    if ($newFieldValue === false) {
                        $ccyymmdd = false;
                    } else {

                        $ccyymmdd = $newFieldValue;
                        $new_name = $name . '_ccyymmdd';
                        $this->saveField(null, $new_name, $newFieldValue);
                        $fieldValue = $newFieldValue;
                    }
                }
            }
        }

        return true;
    }
}
