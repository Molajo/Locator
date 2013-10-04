<?php
/**
 * Display Controller
 *
 * @package   Molajo
 * @license   http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 2013 Amy Stephen. All rights reserved.
 */
namespace Molajo\Controller;

use stdClass;
use Molajo\Controller\Api\DisplayControllerInterface;
use Molajo\Controller\Exception\DisplayControllerException;

/**
 * The display controller uses parameter values provided by the Theme Includer to render output for
 * specified model, theme, page view, template view, and/or wrap view values. The display controller
 * schedules events for before and after view rendering.
 *
 * @package   Molajo
 * @license   http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 2013 Amy Stephen. All rights reserved.
 * @since     1.0
 */
class DisplayController extends ReadController implements DisplayControllerInterface
{
    /**
     * Class Constructor
     *
     * @param  array $options
     *
     * @since  1.0
     */
    public function __construct(array $options = array())
    {
        parent::__construct($options);
    }

    /**
     * Display Controller
     *
     * Interact with the model to connect to a data object, execute a query, schedule events, render theme
     *  output, push data into the views and returned rendered output to Theme Includer.
     *
     * @return  string  Rendered output
     * @since   1.0
     */
    public function execute()
    {
        /** The Theme Includer renders the Theme Include File -- no additional data required */
        if (strtolower($this->getParameter('includer_name')) == strtolower(CATALOG_TYPE_THEME_LITERAL)) {
        } else {

            $value = $this->registry->get(
                $this->getParameter('template_view_model_registry'),
                'process_plugins'
            );

            $this->set('process_template_plugins', $value, 'model_registry');

            $this->getData($this->getParameter('model_query_object'));

            if (count($this->query_results) == 0
                && (int)$this->getParameter('criteria_display_view_on_no_results', 0) == 0
            ) {
                return '';
            }
        }

        if (strtolower($this->getParameter('includer_name'))
            == strtolower(CATALOG_TYPE_THEME_LITERAL)
        ) {

            $this->renderTheme();
        } else {
            if (strtolower($this->getParameter('includer_name'))
                == strtolower(CATALOG_TYPE_WRAP_VIEW_LITERAL)
            ) {

                $this->rendered_output = $this->query_results;
                $this->renderWrapView();
            } else {

                $this->set('view_css_id', $this->getParameter('template_view_css_id'), 'parameters');
                $this->set('view_css_class', $this->getParameter('template_view_css_class'), 'parameters');

                $this->parameters->view->path     = $this->getParameter('template_view_path');
                $this->parameters->view->path_url = $this->getParameter('template_view_path_url');

                $this->renderView();

                $this->onAfterRenderView();

                if ($this->getParameter('wrap_view_path_node') == '') {
                } else {
                    $this->renderWrapView();
                }
            }
        }

        return $this->rendered_output;
    }

    /**
     * RenderTheme is first output rendered, driven by Theme Includer, and the source of
     *  include statements during parsing. All rendered output is recursively scanned for include statements.
     *  For that reason, <include type=value values can be embedded into Views and content.
     *
     * @return  $this
     * @since   1.0
     * @throws  DisplayControllerException
     */
    public function renderTheme()
    {
        $file_path = $this->registry->get('parameters', 'theme_path_include');

        if (file_exists($file_path)) {
        } else {
            $name = $this->registry->get('parameters', 'theme_path_node');
            throw new DisplayControllerException('DisplayController: Theme ' . $name . ' not found at ' . $file_path);
        }

        $this->row            = new stdClass();
        $this->row->page_name = $this->registry->get('parameters', 'page_view_path_node');

        ob_start();
        include $file_path;
        $output = ob_end_clean();

        $this->rendered_output = $output;

        return $this;
    }

    /**
     * Wrap Template View Rendered Output using specified Wrap View
     *
     * @return  $this
     * @since   1.0
     */
    public function renderWrapView()
    {
        $this->query_results   = array();
        $this->query_results[] = $this->rendered_output;

        $this->set('view_css_id', $this->getParameter('wrap_view_css_id'), 'parameters');
        $this->set('view_css_class', $this->getParameter('wrap_view_css_class'), 'parameters');

        $this->set('view_path', $this->getParameter('wrap_view_path'));
        $this->set('view_path_url', $this->getParameter('wrap_view_path_url'));

        $this->renderView();

        return $this;
    }

    /**
     * Two ways Template Views are rendered:
     *
     * 1. If there is a Custom.php file in the Template View folder, then all query
     *      results are pushed into the View using the $this->query_results array/object.
     *      The Custom.php View must handle it's own loop iteration, if necessary, and
     *      reference the results set via an index , ex. $this->query_results[0]->name
     *
     *      Note: neither onBeforeRenderView or onAfterRenderView are scheduled for Custom.php Views.
     *      The View can schedule this Event prior to the rendering for each row using:
     *          <?php $this->onBeforeRenderView(); ?>
     *      And following the rendering of the View for the row, using:
     *          <?php $this->onBeforeRenderView(); ?>
     *
     * 2. Otherwise, the Header.php, and/or Body.php, and/or Footer.php Template View(s)
     *      are used, with data injected into the View, one row at a time. within the views,
     *      data is referenced using the $this->row object, ex. $this->row->name
     *      Header.php (if existing) - used one time for the first row in the resultset
     *      Body.php (if existing) - once for each row within the query results
     *      Footer.php (if existing) - used one time for the last row in the resultset
     *
     * @return  $this
     * @since   1.0
     */
    public function renderView()
    {
//@todo when close to done - do encoding - bring in filters given field definitions

        ob_start();

        /** 1. view responsible for loop processing */
        if (file_exists($this->getParameter('view_path') . '/View/Custom.php')) {
            include $this->getParameter('view_path') . '/View/Custom.php';
        } else {

            /** 2. controller manages loop */
            $total_rows  = count($this->query_results);
            $row_count   = 1;
            $first       = 1;
            $even_or_odd = 'odd';

            if (count($this->query_results) > 0) {

                foreach ($this->query_results as $this->row) {

                    if ($row_count == $total_rows) {
                        $last_row = 1;
                    } else {
                        $last_row = 0;
                    }

                    $this->set('row_count', $row_count, 'parameters');
                    $this->set('even_or_odd', $even_or_odd, 'parameters');
                    $this->set('total_rows', $total_rows, 'parameters');
                    $this->set('last_row', $last_row, 'parameters');
                    $this->set('first', $first, 'parameters');

                    $this->onBeforeRenderView();

                    if ($first === true) {
                        if (file_exists($this->getParameter('view_path') . '/View/Header.php')) {
                            include $this->getParameter('view_path') . '/View/Header.php';
                        }
                    }

                    if (file_exists($this->getParameter('view_path') . '/View/Body.php')) {
                        include $this->getParameter('view_path') . '/View/Body.php';
                    }

                    if ($even_or_odd == 'odd') {
                        $even_or_odd = 'even';
                    } else {
                        $even_or_odd = 'odd';
                    }

                    $row_count ++;
                    $first = false;

                    if ($last_row == 1) {
                        if (file_exists($this->getParameter('view_path') . '/View/Footer.php')) {
                            include $this->getParameter('view_path') . '/View/Footer.php';
                        }
                    }

                    $this->onAfterRenderView();
                }
            }
        }

        $output = ob_end_clean();

        $this->rendered_output = $output;

        return $this;
    }

    /**
     * Schedule Event onBeforeRenderView Event
     *
     * Useful for preprocessing of input prior to rendering or evaluation of content for
     *  possible inclusion of related information. Include statements could be added to
     *  the input, images resized, links to keywords added, blockquotes, and so on.
     *
     *  Method runs one time for each input row to View.
     *
     *  Not available to custom.php file Views since the Controller does not manage the looping
     *  in that case.
     *
     * @return  $this
     * @since   1.0
     */
    public function onBeforeRenderView()
    {
        $arguments = array(
            'model'                             => $this->get('model'),
            'model_registry'                    => $this->get('model_registry'),
            'parameters'                        => $this->get('parameters'),
            'query_results'                     => array(),
            'row'                               => $this->row,
            'rendered_output'                   => null,
            'include_parse_sequence'            => array(),
            'include_parse_exclude_until_final' => array()
        );

        $arguments = $this->event->scheduleEvent(
            'onBeforeRenderView',
            $arguments,
            $this->get('plugins')
        );

        $this->setPluginResultProperties($arguments);

        return $this;
    }

    /**
     * Schedule Event onAfterRenderView Event
     *
     * Processing follows completion of a single row rendering. Can be used to add
     *  include statement or additional information.
     *
     *  Method runs one time for each input row to View.
     *
     *  Not available to custom.php file Views since the Controller does not manage the looping
     *  in that case.
     *
     * @return  $this
     * @since   1.0
     */
    public function onAfterRenderView()
    {
        $arguments = array(
            'model'                             => $this->get('model'),
            'model_registry'                    => $this->get('model_registry'),
            'parameters'                        => $this->get('parameters'),
            'query_results'                     => array(),
            'row'                               => array(),
            'rendered_output'                   => $this->get('rendered_output'),
            'include_parse_sequence'            => array(),
            'include_parse_exclude_until_final' => array()
        );

        $arguments = $this->event->scheduleEvent(
            'onAfterRenderView',
            $arguments,
            $this->get('plugins')
        );

        $this->setPluginResultProperties($arguments);

        return $this;
    }
}
