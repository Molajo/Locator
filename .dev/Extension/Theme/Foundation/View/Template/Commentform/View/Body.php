<?php
Use Molajo\Service\Type;

/**
 * @package   Molajo
 * @copyright 2013 Amy Stephen. All rights reserved.
 * @license   http://www.opensource.org/licenses/mit-license.html MIT License
 */

if ($this->row->closed == 1) {
    return;
} ?>
<include:template name=formbegin form_name=comments/>
    <fieldset class="two-up">

        <legend><?php echo $this->language_instance->translate('Your Response'); ?></legend>

        <div class="row">
            <div class="two mobile-one columns">
                <label for="visitor_name" class="required right inline">Name:</label>
            </div>
            <div class="ten mobile-three columns">
                <input id="visitor_name" name="visitor_name" required type="text" placeholder="First and Last Name"/>
            </div>
        </div>

        <div class="row">
            <div class="two mobile-one columns">
                <label for="email_address" class="required right inline">Email:</label>
            </div>
            <div class="ten mobile-three columns">
                <input id="email_address" name="email_address" required type="email"
                       placeholder="Your email address will not be published." class="eight"/>
            </div>
        </div>

        <div class="row">
            <div class="two mobile-one columns">
                <label for="website" class="right inline">Website:</label>
            </div>
            <div class="ten mobile-three columns">
                <input id="website" name="website" type="url" placeholder="Website will be shared with site visitors."
                       class="eight"/>
            </div>
        </div>

        <div class="row">
            <div class="two mobile-one columns">
                <label for="comment" class="required right inline">Comment:</label>
            </div>
            <div class="ten mobile-three columns">
                <textarea id="comment" name="comment" required placeholder="Your response..."></textarea>
            </div>
        </div>

        <div class="row">
            <div class="ten mobile-one columns">
                <label class="right inline"></label>
            </div>
            <div class="two mobile-three columns">
                <button class="[radius, round] button">Save</button>
            </div>
        </div>

        <input name="model_name" type="hidden" value="Comments"/>
        <input name="model_type" type="hidden" value="Resource"/>
        <input name="parent_model_type" type="hidden" value="<?php echo $this->row->parent_model_type; ?>"/>
        <input name="parent_model_name" type="hidden" value="<?php echo $this->row->parent_model_name; ?>"/>
        <input name="parent_source_id" type="hidden" value="<?php echo $this->row->parent_source_id; ?>"/>
    </fieldset>
    <include:template name=formend/>
