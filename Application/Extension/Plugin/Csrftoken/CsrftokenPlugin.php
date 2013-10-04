<?php
/**
 * @package   Molajo
 * @copyright 2013 Amy Stephen. All rights reserved.
 * @license   http://www.opensource.org/licenses/mit-license.html MIT License
 */
namespace Molajo\Plugin\Csrftoken;

use Molajo\Plugin\AbstractPlugin;
use Molajo\Plugin\Api\CreateEventInterface;
use Molajo\Plugin\Api\UpdateEventInterface;

/**
 * Csrftoken
 *
 * @package     Molajo
 * @license     MIT
 * @since       1.0
 */
class CsrftokenPlugin extends AbstractPlugin implements CreateEventInterface, UpdateEventInterface
{

    /**
     * After View Rendering, look for </form> Statement, if found, add CSRF Protection
     *
     * @return boolean
     * @since   1.0
     */
    public function onAfterRenderView()
    {
        return;

        $rendered = $this->get('rendered_output');

        if ($rendered === null
            || trim($rendered) == ''
        ) {
            return true;
        }

        $beginFormPattern = '/<form(.*)>/';

        preg_match_all($beginFormPattern, $rendered, $forms);

        if (count($forms[0]) == 0) {
            return true;
        }

        $replaceThis = array();
        $withThis    = array();
        $i           = 0;

        $distinct = array_unique($forms[0]);

        foreach ($distinct as $match) {

            $formToken = serialize(rand(rand(50000, 500000), rand(500000, 50000000)));

            $withThis[] = $match
                . chr(10)
                . '<div class="catch"><input name="info" type="text" value=""></div>'
                . chr(10)
                . '<input type="hidden" name="' . $formToken . '" value="token">';

            $replaceThis[] = $match;
            $i ++;
        }

        $temp = str_replace($replaceThis, $withThis, $rendered);

        if ($rendered == $temp) {
            return true;
        }

        $this->set('rendered_output', $temp);

        return true;
    }

    /**
     * Pre-create processing
     *
     * @return boolean
     * @since   1.0
     */
    public function onBeforeCreate()
    {
        //unique
        return true;
    }

    /**
     * Pre-update processing
     *
     * @param   $this ->row
     * @param   $model
     *
     * @return boolean
     * @since   1.0
     */
    public function onBeforeUpdate()
    {
        //reserved words - /edit
        return true;
    }
}
