<?php
/**
 * @package   Molajo
 * @copyright 2013 Amy Stephen. All rights reserved.
 * @license   http://www.opensource.org/licenses/mit-license.html MIT License
 */
namespace Molajo\Plugin\Copyright;

use Molajo\Plugin\AbstractPlugin;


/**
 * @package     Molajo
 * @license     MIT
 * @since       1.0
 */
class CopyrightPlugin extends AbstractPlugin
{
    /**
     * Prepares formatted copyright statement with year span
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
        $current_year = $this->current_date->format('Y');

        $first_year_field = $this->getField('copyright_first_year');
        if ($first_year_field === false) {
            $first_year = null;
        } else {
            $first_year = $this->getFieldValue($first_year_field);
        }

        if ($first_year == null || $first_year == '') {
            $ccDateSpan = $current_year;

        } elseif ($first_year == $current_year) {
            $ccDateSpan = $first_year;

        } else {
            $ccDateSpan = $first_year . '-' . $current_year;
        }

        $copyright_holder_field = $this->getField('copyright_holder');
        if ($copyright_holder_field === false) {
            $copyright_holder = null;
        } else {
            $copyright_holder = $this->getFieldValue($copyright_holder_field);
        }
        if ($copyright_holder == null || $copyright_holder == '') {
            $copyright_holder = 'Molajo';
        }

        $copyright_statement = '&#169;' . ' ' . $ccDateSpan . ' ' . $copyright_holder;
        $this->saveField(null, 'copyright_statement', $copyright_statement);

        return true;
    }
}
