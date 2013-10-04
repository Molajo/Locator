<?php
/**
 * @package   Molajo
 * @copyright 2013 Amy Stephen. All rights reserved.
 * @license   http://www.opensource.org/licenses/mit-license.html MIT License
 */
namespace Molajo\Plugin\Mockdata;

use Molajo\Plugin\AbstractPlugin;


/**
 * @package     Molajo
 * @license     MIT
 * @since       1.0
 */
class MockdataPlugin extends AbstractPlugin
{

    /**
     * Creates text, adds images, video, smilies, assigns created_by
     *
     * {image}250,250,box{/image}
     * {blockquote}{cite:xYZ}*.*{/blockquote}
     * <iframe.+?src="(.+?)".+?<\/iframe>
     *
     * @return boolean
     * @since   1.0
     */
    public function onAfterRead()
    {
        return;
        $fields = $this->retrieveFieldsByType('text');

        if (is_array($fields) && count($fields) > 0) {

            foreach ($fields as $field) {

                $name = $field['name'];

                $fieldValue = $this->getFieldValue($field);

                if ($fieldValue === false) {
                } else {
                    $value = $this->search($fieldValue);

                    if ($value === false) {
                    } else {
                        $this->saveField($field, $name, $value);
                    }
                }

            }
        }

        return true;
    }
}
