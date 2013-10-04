<?php
/**
 * @package   Molajo
 * @copyright 2013 Amy Stephen. All rights reserved.
 * @license   http://www.opensource.org/licenses/mit-license.html MIT License
 */
namespace Molajo\Plugin\Ipaddress;

use Molajo\Plugin\AbstractPlugin;


/**
 * IP Address
 *
 * @package     Molajo
 * @license     MIT
 * @since       1.0
 */
class IpaddressPlugin extends AbstractPlugin
{
    /**
     * Pre-create processing
     *
     * @return boolean
     * @since   1.0
     */
    public function onBeforeCreate()
    {
        $fields = $this->retrieveFieldsByType('ip_address');

        $ip_address = Services::Client()->get('ip_address');

        if (is_array($fields) && count($fields) > 0) {
            foreach ($fields as $field) {
                $this->saveField($field, $field['name'], $ip_address);
            }
        }

        return true;
    }

    /**
     * Pre-update processing
     *
     * @return boolean
     * @since   1.0
     */
    public function onBeforeUpdate()
    {
        // No updates allowed for activity
        return true;
    }
}
