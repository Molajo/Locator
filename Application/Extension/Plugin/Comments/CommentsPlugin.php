<?php
/**
 * @package   Molajo
 * @copyright 2013 Amy Stephen. All rights reserved.
 * @license   http://www.opensource.org/licenses/mit-license.html MIT License
 */
namespace Molajo\Plugin\Comments;

use Molajo\Plugin\AbstractPlugin;


/**
 * @package     Molajo
 * @license     MIT
 * @since       1.0
 */
class CommentsPlugin extends AbstractPlugin
{
    /**
     * This method is used to retrieve data input for the Comment, Commentform, and Comment Template Views
     *
     * @return boolean
     * @since   1.0
     */
    public function onBeforeInclude()
    {
        if (strtolower($this->get('template_view_path_node', '', 'parameters')) == 'comment' ||
            strtolower($this->get('template_view_path_node', '', 'parameters')) == 'comments' ||
            strtolower($this->get('template_view_path_node', '', 'parameters')) == 'commentform'
        ) {
        } else {
            return true;
        }

        $results = $this->getParentKeys();
        if ($results === false) {
            return true;
        }

        $parent_model_type = $results['parent_model_type'];
        $parent_model_name = $results['parent_model_name'];
        $parent_source_id  = $results['parent_source_id'];

        $controller_class_namespace = $this->controller_namespace;
        $controller                 = new $controller_class_namespace();
        $controller->getModelRegistry(CATALOG_TYPE_RESOURCE_LITERAL, 'Comments');
        $controller->setDataobject();
        $controller->connectDatabase();

        $controller->set('get_customfields', 2, 'model_registry');
        $controller->set('use_special_joins', 1, 'model_registry');
        $controller->set('check_view_level_access', 1, 'model_registry');

        $parentController = new $controller_class_namespace();
        $parentController->getModelRegistry($parent_model_type, $parent_model_name);
        $parentController->setDataobject();
        $parentController->connectDatabase();

        $open = $this->getCommentsOpen(
            $controller,
            $parentController,
            $parent_model_type,
            $parent_model_name,
            $parent_source_id
        );
        $this->set('parent_comments_open', $open, 'parameters');

        $method = 'get' . ucfirst(strtolower($this->get('template_view_path_node', '', 'parameters')));

        $results = $this->$method(
            $controller,
            $parentController,
            $parent_model_type,
            $parent_model_name,
            $parent_source_id
        );

        $this->registry->set(
            'Template',
            $this->get('template_view_path_node', '', 'parameters'),
            $results
        );

        echo '<pre>';
        var_dump(
            $this->registry->get(
                'Template',
                $this->get('template_view_path_node', '', 'parameters')
            )
        );

        return true;
    }

    /**
     * getParentKeys - retrieve the values which identify the parent for the requested comments
     *
     * If comments are required for a view that is not the primary request, the parent variables
     *  can be defined on the include statement, as shown below:
     *
     * <include type=template name=Comment wrap=none parent_model_type=<?php echo $parent_model_type; ?> parent_model_name=<?php echo $parent_model_name; ?> parent_source_id=<?php echo &parent_source_id'; ?>/>
     *      Note: Include statements must not break on multiple lines
     *
     * @return array|bool
     * @since   1.0
     */
    public function getParentKeys()
    {
        $parent_model_type = $this->get('parent_model_type', '', 'parameters');
        $parent_model_name = $this->get('parent_model_name', '', 'parameters');
        $parent_source_id  = (int)$this->get('parent_source_id', 0, 'parameters');

        if ($parent_model_type == ''
            || $parent_model_name == ''
            || $parent_source_id == 0
        ) {
            $parent_model_type = $this->registry->get(
                'RouteParameters',
                'request_model_type',
                'parameters'
            );
            $parent_model_name = $this->registry->get(
                'RouteParameters',
                'request_model_name',
                'parameters'
            );
            $parent_source_id  = (int)$this->registry->get(
                'RouteParameters',
                'criteria_source_id',
                'parameters'
            );
        }

        if ($parent_model_type == ''
            || $parent_model_name == ''
            || $parent_source_id == 0
        ) {
            return false;
        }

        return array(
            'parent_model_type' => $parent_model_type,
            'parent_model_name' => $parent_model_name,
            'parent_source_id'  => $parent_source_id
        );
    }

    /**
     * Retrieve Data for Comment Heading
     *
     * @param   $controller
     * @param   $parentController
     * @param   $parent_model_type
     * @param   $parent_model_name
     * @param   $parent_source_id
     *
     * @return bool
     * @since   1.0
     */
    public function getComment(
        $controller,
        $parentController,
        $parent_model_type,
        $parent_model_name,
        $parent_source_id
    ) {
        $primary_prefix = $controller->get('primary_prefix', 'a', 'model_registry');

        $controller->model->query->select('count(*)');
        $controller->model->query->where(
            $controller->model->database->qn($primary_prefix)
            . '.' . $controller->model->database->qn('root')
            . ' = ' . (int)$parent_source_id
        );

        $count = $controller->getData('result');

        $results                     = array();
        $temp_row                    = new \stdClass();
        $temp_row->count_of_comments = $count;

        if ($count == 0) {
            $temp_row->title        = $this->language->translate('COMMENTS_TITLE_NO_COMMENTS');
            $temp_row->content_text = $this->language->translate('COMMENTS_TEXT_NO_COMMENTS');

        } else {
            $temp_row->title        = $this->language->translate('COMMENTS_TITLE_HAS_COMMENTS');
            $temp_row->content_text = $this->language->translate('COMMENTS_TEXT_HAS_COMMENTS');
        }

        if ((int)$this->get('parent_comments_open', 1) == 1) {
            $temp_row->closed_comment = $this->language->translate('COMMENTS_ARE_CLOSED');
            $temp_row->closed         = 1;
        } else {
            $temp_row->closed_comment = '';
            $temp_row->closed         = 0;
        }

        $results[] = $temp_row;

        return $results;
    }

    /**
     * Retrieve Comments
     *
     * @param   $controller
     * @param   $parentController
     * @param   $parent_model_type
     * @param   $parent_model_name
     * @param   $parent_source_id
     *
     * @return bool
     * @since   1.0
     */
    public function getComments(
        $controller,
        $parentController,
        $parent_model_type,
        $parent_model_name,
        $parent_source_id
    ) {

        $primary_prefix = $controller->get('primary_prefix', 'a', 'model_registry');

        $controller->set('root', (int)$parent_source_id, 'parameters');

        $controller->model->query->where(
            $controller->model->database->qn($primary_prefix)
            . '.' . $controller->model->database->qn('root')
            . ' = ' . (int)$parent_source_id
        );
        $controller->model->query->order(
            $controller->model->database->qn($primary_prefix)
            . '.' . $controller->model->database->qn('lft')
        );

        $results = $controller->getData('list');

        return $results;
    }

    /**
     * Retrieve Data for Comment Heading
     *
     * @param   $controller
     * @param   $parentController
     * @param   $parent_model_type
     * @param   $parent_model_name
     * @param   $parent_source_id
     *
     * @return bool
     * @since   1.0
     */
    public function getCommentform(
        $controller,
        $parentController,
        $parent_model_type,
        $parent_model_name,
        $parent_source_id
    ) {
        $results  = array();
        $temp_row = new \stdClass();

        if ((int)$this->get('parent_comments_open', 1, 'parameters') == 1) {
            $temp_row->closed_comment = $this->language->translate('COMMENTS_ARE_CLOSED');
            $temp_row->closed         = 1;
        } else {
            $temp_row->closed_comment = '';
            $temp_row->closed         = 0;
        }

        $temp_row->parent_model_type = $parent_model_type;
        $temp_row->parent_model_name = $parent_model_name;
        $temp_row->parent_source_id  = $parent_source_id;

        $results[] = $temp_row;

        return $results;

        /** Get configuration menuitem settings for this resource */
        $menuitem = $this->content_helper->getResourceMenuitemParameters('Configuration', 17000);

        /** Create Tabs */
        $namespace = 'Comments';

        $page_array = $this->registry->get('ConfigurationMenuitemParameters', 'commentform_page_array');
        $page_array = '{{Comments,visitor*,email*,website*,ip*,spam*}}';

        /*
        visitor_name
        email_address
        website
        ip_address
        spam_protection
        */

        $tabs = Services::Form()->setPageArray(
            'System',
            'Comments',
            'Comments',
            $page_array,
            'comments_page_',
            'Comment',
            'Commenttab',
            17000,
            array()
        );

        $this->set('model_type', 'xxxx', 'parameters');
        $this->set('model_name', 'Edit', 'parameters');
        $this->set('model_query_object', 'item', 'parameters');

        $this->registry->set('Template', 'Commentform', $tabs);

        return true;
    }

    /**
     * Determine if Comments are still open for Content
     *
     * @param   $controller
     * @param   $parentController
     * @param   $parent_model_type
     * @param   $parent_model_name
     * @param   $parent_source_id
     *
     * @return bool
     * @since   1.0
     */
    public function getCommentsOpen(
        $controller,
        $parentController,
        $parent_model_type,
        $parent_model_name,
        $parent_source_id
    ) {

        $primary_prefix = $parentController->get('primary_prefix');

        $parentController->set('primary_key_value', (int)$parent_source_id, 'model_registry');

        $parentController->model->query->select(
            $parentController->model->database->qn($primary_prefix)
            . '.' .
            $parentController->model->database->qn('start_publishing_datetime')
        );

        $published = $parentController->getData('result');
        if ($published === false) {
            return false;
        }

        $converted = $this->date->convertCCYYMMDD($published);
        if ($converted === false) {
            return false;
        }

        $actual = $this->date->getNumberofDaysAgo($converted);

        $open_days = $this->get('enable_response_comment_form_open_days', 90, 'parameters');

        if ($actual > $open_days) {
            $o = false;
        }

        if ((int)$this->get('parent_comments_open', 1, 'parameters') == 1) {
        } else {
            $this->set('parent_comments_open', $o, 'parameters');
        }

        return $this->get('parent_comments_open', null, 'parameters');
    }
}
