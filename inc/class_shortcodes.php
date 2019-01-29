<?php

/**
 * Home Page Layouts shortcodes
 *
 * @package is-layouts
 * @since   1.0.0
 */
class HomeLayouts_Shortcodes {

    function __construct() {
        add_action('pre_get_posts', array($this, 'layout_posts_order'));
        add_action('save_post', array($this, 'save_layout_metaboxes'));
    }
    public function init() {
        add_shortcode('home_layouts', __CLASS__ . '::home_layouts_display');
        add_shortcode('categories_layouts', __CLASS__ . '::categories_layouts_display');
    }
    
    public function categories_layouts_display($arg) {
        $parent = isset($arg['parent']) ? $arg['parent'] : false;
        if(!$parent) {
            return ;
        }

        $layouts = self::get_categories_layouts($parent);
        do_action('before_home_layouts');
        if(count($layouts)) {
            foreach ($layouts as $key => $layout) {
                $postmeta = get_post_meta($layout->ID);
                $layout = isset($postmeta['_layout_style']) ? $postmeta['_layout_style'][0] : '';
                $categoryid = isset($postmeta['_category_id']) ? $postmeta['_category_id'][0] : '';
                $posts = isset($postmeta['_layout_posts']) ? unserialize($postmeta['_layout_posts'][0]) : '';
                $category = get_term($categoryid);
                $isClass = new IsLayouts();
                $category->attachment = $isClass->layout_category_column_data(false, 'layout_icon', $categoryid);
                $templatefile = IS_LAYOUTS_ABSPATH . "templates/{$layout}.php";
                if(file_exists($templatefile)){
                    require $templatefile;
                }
            }
        }else{
            //default layout 
            $categories = get_categories(array('parent' => $parent, 'number' => 20));
            //if has child categories
            if(count($categories)) {
                $layout = "default";
                foreach ($categories as $key => $category) {
                    $posts = get_posts(array(
                        'numberposts'   => 1, // get all posts.
                        'tax_query'     => array(
                            array(
                                'taxonomy'  => 'category',
                                'field'     => 'id',
                                'terms'     => $category->term_id,
                            ),
                        ),
                        'fields'        => 'ids', // Only get post IDs
                    ));

                    $templatefile = IS_LAYOUTS_ABSPATH . "templates/{$layout}.php";
                    if(file_exists($templatefile)){
                        require $templatefile;
                    }
                }
            } else {
                if ( have_posts() ) {
                    while ( have_posts() ) : the_post(); ?>
                        <div class="col-md-12 white-box">
                            <div class="white-img-box">
                                <a href="<?php the_permalink(); ?>">
                                    <?php the_post_thumbnail(); ?>
                                </a>
                                <h3>
                                    <a href="<?php the_permalink(); ?>">
                                        <?php the_title(); ?>
                                    </a>
                                </h3>
                                <p></p>
                                <p><?php the_excerpt(); ?></p>
                                <p></p>
                            </div>
                        </div>
                    <?php endwhile;
                    
                    // Previous/next page navigation.
                    the_posts_pagination( array(
                            'prev_text'          => __( 'Previous page', 'is-layouts' ),
                            'next_text'          => __( 'Next page', 'is-layouts' ),
                            'before_page_number' => '<span class="meta-nav screen-reader-text">' . __( 'Page', 'is-layouts' ) . ' </span>',
                    ) );
                }
            }
        }    
        do_action('after_home_layouts');
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
    
    public function get_categories_layouts($cat_id) {
        $args = array(
            'post_type' => 'categories_layouts',
            'numberposts' => 50,
            'orderby' => 'menu_order',
            'order' => 'ASC',
            'meta_query' => array(
                array(
                    'key' => '_parent_category_id',
                    'value' => $cat_id,
                    'compare' => '='
                )
            )
            
            
        );
        $posts = get_posts($args);
        
        return $posts; 
    }
}
