<?php

/**
 * Layout Plugin Base Class
 *
 * @package is-layouts
 * @since   1.0.0
 */
class BaseLayout {

    protected static $_instance = null;
    private $layouts = [
        'post_with_title' => 'Posts with title only',
        'post_grid_view' => 'Posts with grid view',
        'posts_with_list' => 'Posts with list view',
        'post_single_column' => 'Posts with single column',
    ];

    function __construct() {
        add_action('pre_get_posts', array($this, 'layout_posts_order'));
        add_action('save_post', array($this, 'save_layout_metaboxes'));
        //add_filter('get_terms', array($this, 'layout_get_object_terms'), 10, 3);
        //add_filter('wp_get_object_terms', array($this, 'layout_get_object_terms'), 10, 3);
    }
    
    /*public function layout_get_object_terms($terms) {
        $tags = ['category'];
        if (is_admin() && isset($_GET['orderby']))
            return $terms;
        foreach ($terms as $key => $term) {
            if (is_object($term) && isset($term->taxonomy)) {
                $taxonomy = $term->taxonomy;
                if (!in_array($taxonomy, $tags))
                    return $terms;
            } else {
                return $terms;
            }
        }
        usort($terms, array($this, 'taxcmp'));
        return $terms;
    }
    
    public function taxcmp($a, $b) {
        if ($a->term_order == $b->term_order)
            return 0;
        return ( $a->term_order < $b->term_order ) ? -1 : 1;
    }
    */
    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    public function layout_meta_box_callback($post) {
        $value = get_post_meta($post->ID, '_layout_style', true);
        $html = "<select name='layout_style' class='layout_class layout_input' id='is_layout_style'>";
        $html .= "<option>-choose layout-</option>";
        foreach ($this->layouts as $slug => $layout) {
            if ($value == $slug) {
                $html .= "<option selected='selected' value='{$slug}'>{$layout}</option>";
            } else {
                $html .= "<option value='{$slug}'>{$layout}</option>";
            }
        }
        //echo '<textarea style="width:100%" id="global_notice" name="global_notice">' . esc_attr($value) . '</textarea>';
        $html .= "</select>";

        echo $html;
    }

    public function category_id_meta_box_callback($post) {
        $categories = get_categories(['parent' => null]);
        // Add a nonce field so we can check for it later.
        wp_nonce_field('global_notice_nonce', 'global_notice_nonce');
        //echo "<pre>"; print_r($categories);
        $value = get_post_meta($post->ID, '_category_id', true);
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

    public function selected_posts_meta_box_callback($post) {
        $selected_posts = get_post_meta($post->ID, '_layout_posts', true);
        $category_id = get_post_meta($post->ID, '_category_id', true);
        $posts = $this->getSortedPosts($selected_posts, $category_id);
        $content = '';
        if ($posts && $category_id) {
            $content .= "<div id='is_layout_admin_posts' class='layout_posts_wrap row'>";
            foreach ($posts as $key => $post) {
                $isSelected = in_array($post->ID, $selected_posts) ? 'selected' : '';
                $checked = in_array($post->ID, $selected_posts) ? 'checked' : '';
                $content .= "<div id='post_{$post->ID}' class='layout_single_post col-md-3 {$isSelected}'>";
                $content .= "<input {$checked} value='{$post->ID}' type='checkbox' name='layout_posts[]' class='hidden post_selector'/>";
                $content .= "<div class='layout_post_thumb'>" . $this->getPostThumbnail($post, 'thumb') . "</div>";
                $content .= "<div class='layout_post_title'>{$post->post_title}</div>";
                $content .= "</div>";
            }
            $content .= "<div>";
        }
        $html = "<div class='selected_post_outer'>";
        $html .= "<div id='__search_wrap' class='__search_wrap'><input type='text' placeholder='search for posts...' id='layout_post_search' class='search_input'>";
        $html .= "<div id='__post_search_result' class='post_search_result'></div></div>";
        $html .= "<div class='selected_posts' id='IS_Layout_posts'>{$content}</div>";
        $html .= "</div>";

        echo $html;
    }

    public function layout_posts_order($wp_query) {
        $objects = ['home_layouts', 'categories_layouts'];
        if (empty($objects))
            return false;
        
        if (is_admin()) {
            if (isset($wp_query->query['post_type']) && !isset($_GET['orderby'])) {
                if (in_array($wp_query->query['post_type'], $objects)) {
                    $wp_query->set('orderby', 'menu_order');
                    $wp_query->set('order', 'ASC');
                }
            }
        } else {

            $active = false;

            if (isset($wp_query->query['post_type'])) {
                if (!is_array($wp_query->query['post_type'])) {
                    if (in_array($wp_query->query['post_type'], $objects)) {
                        $active = true;
                    }
                }
            } else {
                if (in_array('post', $objects)) {
                    $active = true;
                }
            }
            if (!$active)
                return false;

            if (isset($wp_query->query['suppress_filters'])) {
                if ($wp_query->get('orderby') == 'date')
                    $wp_query->set('orderby', 'menu_order');
                if ($wp_query->get('order') == 'DESC')
                    $wp_query->set('order', 'ASC');
            } else {
                if (!$wp_query->get('orderby'))
                    $wp_query->set('orderby', 'menu_order');
                if (!$wp_query->get('order'))
                    $wp_query->set('order', 'ASC');
            }
        }
    }

    protected function get_post_arg($label = 'Home', $icon = false) {
        $labels = array(
            'name' => _x('Layouts', 'Post Type General Name', 'is-layouts'),
            'singular_name' => _x('Layout', 'Post Type Singular Name', 'is-layouts'),
            'menu_name' => __("{$label} Layouts", 'is-layouts'),
            'all_items' => __('All Layouts', 'is-layouts'),
            'view_item' => __('View Layout', 'is-layouts'),
            'add_new_item' => __('Add New Layout', 'is-layouts'),
            'add_new' => __('Add New Layout', 'is-layouts'),
            'edit_item' => __('Edit Layout', 'is-layouts'),
            'update_item' => __('Update Layout', 'is-layouts'),
            'search_items' => __('Search Layout', 'is-layouts'),
            'not_found' => __("Not {$label} layout found. Please add some layouts.", 'is-layouts'),
            'not_found_in_trash' => __('Not found in Trash', 'is-layouts'),
        );

        $args = array(
            'label' => __('layouts', 'is-layouts'),
            'description' => __("{$label} layout posts", 'is-layouts'),
            'labels' => $labels,
            // Features this CPT supports in Post Editor
            'supports' => array('title', 'page-attributes'),
            // You can associate this CPT with a taxonomy or custom taxonomy. 
            /* A hierarchical CPT is like Pages and can have
             * Parent and child items. A non-hierarchical CPT
             * is like Posts.
             */
            'hierarchical' => false,
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_nav_menus' => true,
            'show_in_admin_bar' => true,
            'menu_position' => 5,
            'can_export' => true,
            'menu_icon' => $icon,
            'has_archive' => true,
            'exclude_from_search' => false,
            'publicly_queryable' => true,
            'capability_type' => 'page',
        );

        return $args;
    }

    /**
     * When the post is saved, saves our custom data.
     *
     * @param int $post_id
     */
    function save_layout_metaboxes($post_id) {
        //echo "<pre>"; print_r($_POST); die;
        // Check if our nonce is set.
        if (!isset($_POST['global_notice_nonce'])) {
            return;
        }

        // Verify that the nonce is valid.
        if (!wp_verify_nonce($_POST['global_notice_nonce'], 'global_notice_nonce')) {
            return;
        }

        // If this is an autosave, our form has not been submitted, so we don't want to do anything.
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Check the user's permissions.
        if (isset($_POST['post_type']) && ('home_layouts' == $_POST['post_type']) || 'categories_layouts' == $_POST['post_type']) {

            if (!current_user_can('edit_page', $post_id)) {
                return;
            }
        } else {

            if (!current_user_can('edit_post', $post_id)) {
                return;
            }
        }

        /* OK, it's safe for us to save the data now. */
        // Make sure that it is set.
        if (isset($_POST['layout_style'])) {
            // Sanitize user input.
            $layout_style = sanitize_text_field($_POST['layout_style']);

            // Update the meta field in the database.
            update_post_meta($post_id, '_layout_style', $layout_style);
        }

        if (isset($_POST['parent_category_id'])) {
            // Sanitize user input.
            $parent_cat = sanitize_text_field($_POST['parent_category_id']);

            // Update the meta field in the database.
            update_post_meta($post_id, '_parent_category_id', $parent_cat);
        }


        if (isset($_POST['category_id'])) {
            // Sanitize user input.
            $category_id = sanitize_text_field($_POST['category_id']);

            // Update the meta field in the database.
            update_post_meta($post_id, '_category_id', $category_id);
        }

        if (isset($_POST['layout_posts'])) {
            // Sanitize user input.
            $layout_posts = $_POST['layout_posts'];
            // Update the meta field in the database.
            update_post_meta($post_id, '_layout_posts', $layout_posts);
        }
    }

    private function getSortedPosts($selectedposts, $category_id) {
        if (!$selectedposts) {
            return array();
        }
        $selected = $selectedtmp = $otherposts = [];
        $posts = get_posts(array('category' => $category_id, 'numberposts' => 50));
        foreach ($posts as $key => $post) {
            if (in_array($post->ID, $selectedposts)) {
                $selectedtmp[$post->ID] = $post;
            } else {
                $otherposts[] = $post;
            }
        }
        //sort selected posts
        foreach ($selectedposts as $key => $postid) {
            $selected[] = $selectedtmp[$postid];
        }
        return array_merge($selected, $otherposts);
    }

    private function getPostThumbnail($post, $size) {
        if (has_post_thumbnail($post)) {
            return get_the_post_thumbnail($post->ID, $size);
        } else {
            return '<img src="' . IS_LAYOUTS_URL . '/assets/images/default-image.png" alt="<?php the_title(); ?>" />';
        }
    }

}
