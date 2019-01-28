<?php

/**
 * Categories Layout Post Type
 *
 * @package is-layouts
 * @since   1.0.0
 */
class LayoutsCategoriesTypes extends BaseLayout {

    protected static $_instance = null;
    
    const posttype = 'categories_layouts';
    
    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    // Our custom post type function
    public static function init() {
        register_post_type(self::posttype, parent::instance()->get_post_arg('Category', 'dashicons-category'));
        add_action('add_meta_boxes', array(self::instance(), 'add_meta_boxes'));
    }
    
    public function add_meta_boxes() {
        add_meta_box(
                'layout_style', __('Layout', 'is-layouts'), array(&$this, 'layout_meta_box_callback'), self::posttype
        );
        add_meta_box(
                'parent_category_id', __('Parent Category', 'is-layouts'), array(&$this, 'parent_category_id_meta_box_callback'), self::posttype
        );
        add_meta_box(
                'category_id', __('Category', 'is-layouts'), array(&$this, 'category_id_meta_box_callback'), self::posttype
        );

        add_meta_box(
                'selected_posts', __('Posts', 'is-layouts'), array(&$this, 'selected_posts_meta_box_callback'), self::posttype
        );
    }
    
    public function category_id_meta_box_callback($post) {
        $categories = array();
        $parent_cat = get_post_meta($post->ID, '_parent_category_id', true);
        $value = get_post_meta($post->ID, '_category_id', true);
        if($parent_cat) {
            $categories = get_categories(array('parent' => $parent_cat));
        }
        // Add a nonce field so we can check for it later.
        wp_nonce_field('global_notice_nonce', 'global_notice_nonce');
        //echo "<pre>"; print_r($categories);
        
        $html = "<select name='category_id' class='layout_category layout_input' id='is_layout_category'>";
        $html .= "<option value=''>-choose Category-</option>";
        foreach ($categories as $key => $category) {
            if ($category->term_id == $value) {
                $html .= "<option selected='selected' value='{$category->term_id}'>{$category->name}</option>";
            } else {
                $html .= "<option value='{$category->term_id}'>{$category->name}</option>";
            }
        }
        //echo '<textarea style="width:100%" id="global_notice" name="global_notice">' . esc_attr($value) . '</textarea>';
        $html .= "</select>";

        echo $html;
    }
    
    public function parent_category_id_meta_box_callback($post) {
        $categories = get_categories(['parent' => null]);
        // Add a nonce field so we can check for it later.
        wp_nonce_field('global_notice_nonce', 'global_notice_nonce');
        //echo "<pre>"; print_r($categories);
        $value = get_post_meta($post->ID, '_parent_category_id', true);
        $html = "<select name='parent_category_id' class='layout_parent_category layout_input' id='is_layout_parent_category'>";
        $html .= "<option value=''>-choose Category-</option>";
        foreach ($categories as $key => $category) {
            if ($category->term_id == $value) {
                $html .= "<option selected='selected' value='{$category->term_id}'>{$category->name}</option>";
            } else {
                $html .= "<option value='{$category->term_id}'>{$category->name}</option>";
            }
        }
        //echo '<textarea style="width:100%" id="global_notice" name="global_notice">' . esc_attr($value) . '</textarea>';
        $html .= "</select>";

        echo $html;
    }
    /*public function layout_post_categories() {
        add_submenu_page(
                'edit.php?post_type=home_layouts', __('Test Settings', 'menu-test'), __('Test Settings', 'menu-test'), 'manage_options', 'testsettings', 'mt_settings_page'
        );
    }*/   

}
