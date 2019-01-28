<?php

/**
 * Home Layout Post Types
 *
 * @package is-layouts
 * @since   1.0.0
 */
class LayoutsPostsTypes extends BaseLayout {

    protected static $_instance = null;

    const posttype = 'home_layouts';

    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    // Our custom post type function
    public static function init() {
        register_post_type(self::posttype, parent::instance()->get_post_arg('Home', 'dashicons-admin-home'));
        add_action('add_meta_boxes', array(self::instance(), 'add_meta_boxes'));
        add_filter('manage_edit-' . self::posttype . '_columns', array(self::instance(), 'add_post_columns'));
        add_action('manage_posts_custom_column', array(self::instance(), 'action_custom_columns_content'), 10, 2);
    }
    
    
    public function action_custom_columns_content($column_id, $post_id) {
        //run a switch statement for all of the custom columns created
        switch ($column_id) {
            case 'category_meta':
                $_category_id = get_post_meta($post_id, '_category_id', true);
                $cat = get_the_category_by_ID($_category_id);
                echo $cat;
                break;
            case 'parent_category_meta':
                $_parent_category_id = get_post_meta($post_id, '_parent_category_id', true);
                $cat = get_the_category_by_ID($_parent_category_id);
                echo $cat;
                break;
        }
    }

    /**
     * Add new columns to the post table
     *
     * @param Array $columns - Current columns on the list post
     */
    public function add_post_columns($columns) {

        $new = array();
        $date = $columns['date'];  // save the tags column
        unset($columns['date']);   // remove it from the columns list
        foreach ($columns as $key => $value) {
            $new[$key] = $value;
        }
        $new['category_meta'] = 'Category';
        $new['date'] = $date;
        return $new;
    }

    public function add_meta_boxes() {
        add_meta_box(
                'layout_style', __('Layout', 'is-layouts'), array(&$this, 'layout_meta_box_callback'), self::posttype
        );
        add_meta_box(
                'category_id', __('Category', 'is-layouts'), array(&$this, 'category_id_meta_box_callback'), self::posttype
        );

        add_meta_box(
                'selected_posts', __('Posts', 'is-layouts'), array(&$this, 'selected_posts_meta_box_callback'), self::posttype
        );
    }

    /* public function layout_post_categories() {
      add_submenu_page(
      'edit.php?post_type=home_layouts', __('Test Settings', 'menu-test'), __('Test Settings', 'menu-test'), 'manage_options', 'testsettings', 'mt_settings_page'
      );
      } */
}
