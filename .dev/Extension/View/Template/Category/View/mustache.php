<?php
/**
 * @package   Molajo
 * @copyright 2013 Amy Stephen. All rights reserved.
 * @license   http://www.opensource.org/licenses/mit-license.html MIT License
 */
 ?>
{{# items }}
<header>
    <h2>{{title}}</h2>

    <h3>{{hello}}</h3>
</header>

<img src="{{gravatar}}" alt="{{name}}" class="alignright"/>
{{{intro}}}
{{{fulltext}}}
<footer>
    {{start_publishing_datetime}}
</footer>
{{/ items }}
{{{dashboard}}}
{{{placeholder}}}
