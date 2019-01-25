<?php

/**
 * Home Page Layouts shortcodes
 *
 * @package is-layouts
 * @since   1.0.0
 */
class HomeLayouts_Shortcodes {

    public function init() {
        add_shortcode('home_layouts', __CLASS__ . '::home_layouts_display');
    }

    public function home_layouts_display() {
        $layouts = self::get_home_layouts();
        do_action('before_home_layouts');
        foreach ($layouts as $key => $layout) {
            $postmeta = get_post_meta($layout->ID);
            $layout = isset($postmeta['_layout_style']) ? $postmeta['_layout_style'][0] : '';
            $categoryid = isset($postmeta['_category_id']) ? $postmeta['_category_id'][0] : '';
            $posts = isset($postmeta['_layout_posts']) ? unserialize($postmeta['_layout_posts'][0]) : '';
            $category = get_term($categoryid);
            $isClass = new IsLayouts();
            $category->attachment = $isClass->layout_category_column_data(false, 'layout_icon', $categoryid);
            ///echo "<pre>"; print_r($category); die;
            $templatefile = IS_LAYOUTS_ABSPATH . "templates/{$layout}.php";
            if(file_exists($templatefile)){
                require $templatefile;
            }
        }

        do_action('after_home_layouts');

    }

    private function get_home_layouts() {
        $args = array(
            'post_type' => 'home_layouts',
            'order' => 'ASC',
            'numberposts' => 50
        );
        $posts = get_posts($args);
        
        return $posts; 
    }

}
