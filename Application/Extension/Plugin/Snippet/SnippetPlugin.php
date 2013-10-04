<?php
/**
 * @package   Molajo
 * @copyright 2013 Amy Stephen. All rights reserved.
 * @license   http://www.opensource.org/licenses/mit-license.html MIT License
 */
namespace Molajo\Plugin\Snippet;

use Molajo\Plugin\AbstractPlugin;


/**
 * Snippet
 *
 * @package     Molajo
 * @license     MIT
 * @since       1.0
 */
class SnippetPlugin extends AbstractPlugin
{

    /**
     * Parses field types of Text into snippets, stripped of HTML tags, forced to a specific length
     *
     * @return boolean
     * @since   1.0
     */
    public function onAfterRead()
    {
        if (defined('ROUTE')) {
        } else {
            return true;
        }

        $fields = $this->retrieveFieldsByType('text');

        $snippetLength = $this->get('criteria_snippet_length', 200, 'parameters');

        if (is_array($fields) && count($fields) > 0) {

            foreach ($fields as $field) {

                $name = $field['name'];

                $fieldValue = $this->getFieldValue($field);

                if ($fieldValue === false || $fieldValue === null) {
                } else {

                    $newFieldValue = substr(strip_tags($fieldValue), 0, $snippetLength);

                    if (trim($newFieldValue) == trim(strip_tags($fieldValue))) {
                    } else {
                        $newFieldValue .= '...';
                    }

                    if ($newFieldValue === false || $newFieldValue === null) {
                    } else {

                        $newFieldName = $name . '_' . 'snippet';
                        $this->saveField(null, $newFieldName, $newFieldValue);
                    }
                }
            }
        }

        return true;
    }
}
