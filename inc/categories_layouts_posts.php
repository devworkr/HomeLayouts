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
        /* add categories to admin menu  */
        add_action('admin_menu', array(self::instance(), 'admin_categories_menu'));
        add_filter('parse_query', array(self::instance(), 'filter_posts_by_categories'));

        //active filter class in submenu
        add_filter('parent_file', array(self::instance(), 'Is_active_filter_menu'));

        add_filter('manage_edit-' . self::posttype . '_columns', array(self::instance(), 'add_post_columns'));
        add_filter('post_class', array(self::instance(), 'add_class_on_filter'));
    }

    /**
     * Add new columns to the post table
     *
     * @param Array $columns - Current columns on the list post
     */
    function add_post_columns($columns) {
        $new = array();
        $date = $columns['date'];  // save the tags column
        unset($columns['date']);   // remove it from the columns list
        foreach ($columns as $key => $value) {
            $new[$key] = $value;
        }
        $new['category_meta'] = 'Category';
        $new['parent_category_meta'] = 'Parent Category';
        $new['date'] = $date;
        
        return $new;
    }
    
    public function add_class_on_filter($classes) {
        if(isset($_GET['category']) && $_GET['category']) {
            $classes[] = 'filter_by_category';
        }
        return $classes;
    }

    public function admin_categories_menu() {

        $wp_term = get_categories(array('parent' => null, 'number' => 20, 'hide_empty' => false));
        if ($wp_term) {
            foreach ($wp_term as $term) {
                // add_submenu_page( string $parent_slug, string $page_title, string $menu_title, string $capability, string $menu_slug,callable $function = '' )
                add_submenu_page('edit.php?post_type=' . self::posttype, $term->name, $term->name, 'manage_options', 'edit.php?post_type=' . self::posttype . '&category=' . $term->term_id, '');
            }
        }
    }

    public function Is_active_filter_menu($parent_file) {
        global $submenu_file;
        if (isset($_GET['category']) && $_GET['category']) {
            $category = $_GET['category'];
            $submenu_file = 'edit.php?post_type=categories_layouts&category=' . $category;
        }
        return $parent_file;
    }

    /**
     * if submitted filter by post meta
     * 
     * make sure to change META_KEY to the actual meta key
     * and POST_TYPE to the name of your custom post type
     * @author Ohad Raz
     * @param  (wp_query object) $query
     * 
     * @return Void
     */
    function filter_posts_by_categories($query) {
        global $pagenow;
        $category = null;
        if (isset($_GET['category'])) {
            $category = $_GET['category'];
        }

        if ($category && is_admin() && $pagenow == 'edit.php') {
            $query->query_vars['meta_key'] = '_parent_category_id';
            $query->query_vars['meta_value'] = $category;
        }
    }

    public function filter_categories_() {
        echo "hiiiii";
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

    public function IS_Categories_Filter() {
        
    }

    public function category_id_meta_box_callback($post) {
        $categories = array();
        $parent_cat = get_post_meta($post->ID, '_parent_category_id', true);
        $value = get_post_meta($post->ID, '_category_id', true);
        if ($parent_cat) {
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

}
