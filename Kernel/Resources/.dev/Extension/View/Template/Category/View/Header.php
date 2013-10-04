<?php

/**
 * @package   Molajo
 * @copyright 2013 Amy Stephen. All rights reserved.
 * @license   http://www.opensource.org/licenses/mit-license.html MIT License
 */
?>
<div class="row">
    <div class="eleven columns">
        <h2><?php echo $this->row->title; ?></h2>
    </div>
    <div class="one columns">
        <form action="<?php echo $this->row->catalog_id_url ?>/edit">
            <input type="submit" class="submit button small" value="Edit">
        </form>
    </div>
</div>
