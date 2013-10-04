<?php
/**
 * Create Event Plugin
 *
 * @package   Molajo
 * @license   http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 2013 Amy Stephen. All rights reserved.
 */
namespace Molajo\Plugin;

use Molajo\Plugin\Api\CreateEventInterface;

/**
 * Create Event Plugin
 *
 * @author    Amy Stephen
 * @license   http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 2013 Amy Stephen. All rights reserved.
 * @since     1.0
 */
class CreateEventPlugin extends UpdateEventPlugin implements CreateEventInterface
{
    /**
     * Pre-create processing
     *
     * @return  $this
     * @since   1.0
     * @throws  \Molajo\Plugin\Exception\CreateEventException
     */
    public function onBeforeCreate()
    {

    }

    /**
     * Post-create processing
     *
     * @return  $this
     * @since   1.0
     * @throws  \Molajo\Plugin\Exception\CreateEventException
     */
    public function onAfterCreate()
    {

    }
}
