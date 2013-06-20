<?php

/**
 * @package   Molajo
 * @copyright 2013 Amy Stephen. All rights reserved.
 * @license   http://www.opensource.org/licenses/mit-license.html MIT License
 */

 ?>
<h5 class="subheader">
    <?php echo $this->row->title; ?>
</h5>
<p><?php echo $this->row->content_text_snippet; ?></p>
<a class="button" href="<?php echo $this->row->catalog_sef_request; ?>">Push it.<br/> You know you want to to.</a>