<?php
/**
 * @package   Molajo
 * @copyright 2013 Amy Stephen. All rights reserved.
 * @license   http://www.opensource.org/licenses/mit-license.html MIT License
 */
namespace Extension\View\Template\Bargraph\Plugin\Bargraph;

use Molajo\Event\Plugins\Plugin;
use Molajo\Service;


/**
 * @package     Molajo
 * @license     MIT
 * @since       1.0
 */
class BargraphPlugin extends Plugin
{
    /**
     * Prepare data for graph
     *
     * @return void
     * @since   1.0
     */
    public function onBeforeParse()
    {
        return;

        //move this into  on after read all -- build graph after summary query
        if (APPLICATION_ID == 2) {
        } else {
            return true;
        }

        if (strtolower($this->get('template_view_path_node', '', 'parameters')) == 'bargraph') {
        } else {
            return true;
        }

        //@todo remove hack
        $this->registry->set(
            'Parameters',
            'bargraph_input',
            '{2008,100}{2009,80}{2010,25}{2011,50}{2013,60}'
        );

        $graphOptions = $this->registry->get('parameters', 'bargraph_input');
        if (trim($graphOptions) == '') {
            return true;
        }

        $graphOptions = str_replace('{', ' ', $graphOptions);
        $temp         = explode('}', $graphOptions);

        $query_results = array();

        $i = 1;
        foreach ($temp as $set) {
            $temp2 = explode(',', $graphOptions);

            $row = new \stdClass();

            $row->id        = $i;
            $row->css_id    = ' id="data' . $i . '"';
            $row->css_class = ' class="bar portletbargraph' . $i . '"';
            $row->title     = $temp2[0];
            $row->value     = $temp2[1];

            $query_results[] = $row;
        }

        $this->row = $query_results;

        return true;
    }

    protected function createCSS()
    {
        /* get the stylesheet */
        $stylesheet = @is_file($_GET['stylesheet']) && strtolower(
            substr(strrchr($file_name, '.'), 1)
        ) == 'css' ? $_GET['stylesheet'] : 'default.css';

        /* set the header information */
//will be output as css
        header('Content-type: text/css');
//set an expiration date
        $days_to_cache = 10;
        header('Expires: ' . gmdate('D, d M Y H:i:s', time() + (60 * 60 * 24 * $days_to_cache)) . ' GMT');

        /* set the dynamic information */
//default css variable information
        $default = array(
            'body_font_size'  => '16px',
            'body_text_color' => '#00f'
        );

//red css variable information
        $red = array(
            'body_font_size'  => '10px',
            'body_text_color' => '#f00'
        );

        /* extract the propery array's information */
        extract($_GET['theme'] && ${$_GET['theme']} ? ${$_GET['theme']} : $default);

        /* load in the stylesheet */
        $content = preg_replace('/\$([\w]+)/e', '$0', @file_get_contents($stylesheet));

        /* spit it out */
        echo $content;

    }
}
