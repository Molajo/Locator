<?php
/**
 * @package   Molajo
 * @copyright 2013 Amy Stephen. All rights reserved.
 * @license   http://www.opensource.org/licenses/mit-license.html MIT License
 */
namespace Molajo\Plugin\Feed;

use Molajo\Plugin\AbstractPlugin;


/**
 * Feed
 *
 * @package     Molajo
 * @license     MIT
 * @since       1.0
 */
class FeedPlugin extends AbstractPlugin
{

    /**
     * Retrieves feed of data, according to parameters
     *
     * @return boolean
     * @since   1.0
     */
    public function onAfterReadall()
    {
        return true;

        if (strtolower($this->get('template_view_path_node', '', 'parameters')) == 'feed') {
        } else {
            return true;
        }

        $rss_feed = $this->parameters->criteria_rss_feed;
        if ($rss_feed == '') {
            $rss_feed = 'http://wordpress.com/rss';
        }

        $count = $this->parameters->criteria_count;
        if ((int)$count == 0) {
            $count = 5;
        }

        $rss = new \DOMDocument();
        $rss->load($rss_feed);

        $feed = array();
        foreach ($rss->getElementsByTagName('item') as $node) {
            $item = array(
                'title' => $node->getElementsByTagName('title')->item(0)->nodeValue,
                'desc'  => $node->getElementsByTagName('description')->item(0)->nodeValue,
                'link'  => $node->getElementsByTagName('link')->item(0)->nodeValue,
                'date'  => $node->getElementsByTagName('pubDate')->item(0)->nodeValue,
            );
            array_push($feed, $item);
        }

        $temp_query_results = array();

        for ($x = 0; $x < $count; $x ++) {
            $temp_row = new \stdClass();

            $temp_row->title          = str_replace(' & ', ' &amp; ', $feed[$x]['title']);
            $temp_row->link           = $feed[$x]['link'];
            $temp_row->description    = $feed[$x]['desc'];
            $temp_row->published_date = date('l F d, Y', strtotime($feed[$x]['date']));

            $temp_query_results[] = $temp_row;
        }

        $this->row = $temp_query_results;

        return true;
    }
}
