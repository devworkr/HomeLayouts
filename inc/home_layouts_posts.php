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
