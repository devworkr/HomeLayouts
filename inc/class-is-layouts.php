<?php
/**
 * Home Layouts setup
 *
 * @package is-layouts
 * @since   1.0.0
 */
defined('ABSPATH') || exit;

class IsLayouts {

    /**
     * seo-review version.
     *
     * @var string
     */
    public $version = '1.0.0';

    /**
     * The single instance of the class.
     *
     * @var IsLayouts
     * @since 1.0.0
     */
    protected static $_instance = null;

    /**
     * Main IsLayouts Instance.
     *
     * Ensures only one instance of IsLayouts is loaded or can be loaded.
     *
     * @since 1.0.0
     * @static
     * @return IsLayouts.
     */
    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * IsLayouts Constructor.
     */
    public function __construct() {
        $this->define_constants();
        $this->includes();
        $this->init_hooks();

        //do_action('seo_review_loaded');
    }

    /**
     * Hook into actions and filters.
     *
     * @since 1.0.0
     */
    private function init_hooks() {
        register_activation_hook(IS_LAYOUTS_PLUGIN_FILE, array($this, 'layout_plugin_install'));
        add_action('init', array($this, 'init'), 0);
        // register admin and front end scripts
        add_action('wp_enqueue_scripts', array($this, 'is_layouts_enqueue'), 0);
        add_action('admin_enqueue_scripts', array($this, 'admin_layout_scripts'), 0);

        // register post types for layouts 
        add_action('init', 'LayoutsPostsTypes::init');
        add_action('init', 'LayoutsCategoriesTypes::init');
        
        // initilize the shortcode class
        add_action('init', array('HomeLayouts_Shortcodes', 'init'));

        // ajax handler for category posts 
        add_action('wp_ajax_get_category_posts', array(&$this, 'get_category_posts'));
        add_action('wp_ajax_search_layout_posts', array(&$this, 'search_layout_posts'));
        add_action('wp_ajax_select_from_search', array(&$this, 'select_from_search'));
        add_action('wp_ajax_get_parent_category', array(&$this, 'get_parent_category'));
        //reorder the layout posts
        add_action('wp_ajax_update_layout_order', array(&$this, 'update_layout_order'));
        
        $tax = sanitize_key(@$_REQUEST['taxonomy']);
        /* add browse and text field to upload image and add an fontawesome icon */
        add_action($tax . "_add_form_fields", array($this, 'layout_add_new_iconfield'), 10, 2);
        add_action($tax . "_edit_form_fields", array($this, 'layout_edit_iconfield'), 10, 2);

        /* save the image or font awesome icon */
        add_action("edited_" . $tax, array($this, 'layout_save_iconfield'), 10, 2);
        add_action("create_" . $tax, array($this, 'layout_save_iconfield'), 10, 2);

        /* show cloumn and their respective icon and images */
        add_filter('manage_edit-' . $tax . '_columns', array($this, 'layout_category_column'));
        add_filter('manage_' . $tax . '_custom_column', array($this, 'layout_category_column_data'), 10, 3);

        /* Ajax calls for image saving */
        add_action('wp_ajax_layout_new_icon', array($this, 'layout_ajax_new_icon'));

        /* custom image sizes for icons */
        add_image_size('layout_icon_small', 20, 20, true);
        add_image_size('layout_icon_medium', 40, 40, true);
        add_image_size('layout_icon_large', 60, 60, true);
    }

    public function layout_plugin_install() {
        
    }

    /**
     * Define IsLayouts Constants.
     */
    private function define_constants() {
        $this->define('IS_LAYOUTS_ABSPATH', dirname(IS_LAYOUTS_PLUGIN_FILE) . '/');
        $this->define('IS_LAYOUTS_BASENAME', plugin_basename(IS_LAYOUTS_PLUGIN_FILE));
        $this->define('IS_LAYOUTS_URL', plugins_url(basename(IS_LAYOUTS_ABSPATH)));
        $this->define('IS_LAYOUTS_VERSION', $this->version . time());
    }

    /**
     * Include required core files used in admin and on the frontend.
     */
    public function includes() {
        include_once IS_LAYOUTS_ABSPATH . '/inc/ClassBase.php';
        include_once IS_LAYOUTS_ABSPATH . '/inc/home_layouts_posts.php';
        include_once IS_LAYOUTS_ABSPATH . '/inc/categories_layouts_posts.php';
        include_once IS_LAYOUTS_ABSPATH . '/inc/class_shortcodes.php';
    }

    /**
     * Init plugin when WordPress Initialises.
     */
    public function init() {
        
    }

    /**
     * Define constant if not already set.
     *
     * @param string      $name  Constant name.
     * @param string|bool $value Constant value.
     */
    private function define($name, $value) {
        if (!defined($name)) {
            define($name, $value);
        }
    }

    public function admin_layout_scripts() {
        global $pagenow;
        if ($pagenow != 'post.php' && $pagenow != 'post-new.php') {
            wp_enqueue_media();
        }
        wp_enqueue_style('seo_review_style', IS_LAYOUTS_URL . '/assets/css/is_admin_styles.css', array(), IS_LAYOUTS_VERSION);
        wp_enqueue_style('bootstrap', IS_LAYOUTS_URL . '/assets/css/bootstrap.min.css', array(), IS_LAYOUTS_VERSION);
        wp_enqueue_script('seo_review_script', IS_LAYOUTS_URL . '/assets/js/is_admin_script.js', array('jquery', 'jquery-ui-sortable', 'jquery-ui-draggable'), IS_LAYOUTS_VERSION);
    }

    /* enque seo scrits  */

    public function is_layouts_enqueue() {

        wp_enqueue_script('seo_review_script', IS_LAYOUTS_URL . '/assets/js/is_script.js', array('jquery'), IS_LAYOUTS_VERSION);

        wp_enqueue_style('seo_review_style', IS_LAYOUTS_URL . '/assets/css/is_styles.css', array(), IS_LAYOUTS_VERSION);

        //wp_localize_script('seo_review_script', 'seo_review_object', array('ajax_url' => admin_url('admin-ajax.php')));
    }

    public function get_category_posts() {
        if (isset($_POST['category']) && !empty($_POST['category'])) {
            $category = $_POST['category'];
            $posts = get_posts(array('category' => $category, 'numberposts' => 50));
            $content = "<div id='is_layout_admin_posts' class='layout_posts_wrap row'>";
            foreach ($posts as $key => $post) {
                $content .= "<div id='post_{$post->ID}' class='layout_single_post col-md-3'>";
                $content .= "<input value='{$post->ID}' type='checkbox' name='layout_posts[]' class='hidden post_selector'/>";
                $content .= "<div class='layout_post_thumb'>" . $this->getPostThumbnail($post, 'thumb') . "</div>";
                $content .= "<div class='layout_post_title'>{$post->post_title}</div>";
                $content .= "</div>";
            }
            $content .= "<div>";
            header('Content-Type: application/json');
            die(json_encode(array('status' => 'success', 'content' => $content)));
        } else {
            header('Content-Type: application/json');
            die(json_encode(array('status' => 'fail', 'message' => 'please select category.')));
        }
    }

    private function getPostThumbnail($post, $size) {
        if (has_post_thumbnail($post)) {
            return get_the_post_thumbnail($post->ID, $size);
        } else {
            return '<img src="' . IS_LAYOUTS_URL . '/assets/images/default-image.png" alt="<?php the_title(); ?>" />';
        }
    }

    public function update_layout_order() {
        global $wpdb;

        parse_str($_POST['order'], $data);

        if (!is_array($data))
            return false;
        $id_arr = array();
        foreach ($data as $key => $values) {
            foreach ($values as $position => $id) {
                $id_arr[] = $id;
            }
        }

        $menu_order_arr = array();
        foreach ($id_arr as $key => $id) {
            $results = $wpdb->get_results("SELECT menu_order FROM $wpdb->posts WHERE ID = " . intval($id));
            foreach ($results as $result) {
                $menu_order_arr[] = $result->menu_order;
            }
        }

        sort($menu_order_arr);
        foreach ($data as $key => $values) {
            foreach ($values as $position => $id) {
                $wpdb->update($wpdb->posts, array('menu_order' => $position), array('ID' => intval($id)));
            }
        }
    }
    
    /* show browse and font awesome icon option while add category */

    public function layout_add_new_iconfield() {
        ?>
        <div style=" display:table; width:100%; padding-right:10px; padding-bottom:20px; " >
            <label><?php echo __("Category Icons", "templatic_cat_icon"); ?></label> 
        </div>
        <div  id="layout_icon_type_image" style="margin-bottom:20px; clear:both; position:relative; float:left; width:45px; z-index:999;" >
            <div id="layout_preview_img" style="clear:both; margin:10px 0;" >
            </div>
            <input id="layout_icon_img" type="hidden" size="36" name="layout_icon_img" value="http://"  />

            <div style="display:none;" class="layout_remove" id='layout_remove'><a style="background:#fff; padding:4px; font-weight:bold; -webkit-border-radius: 25px;
                                                                                 -moz-border-radius: 25px; border-radius: 25px; font-size:13px; padding:0 5px; position:absolute; right:-4px; top:-2px; cursor:pointer;  " ><?php _e("X", "templatic_cat_icon") ?></a></div>
            <input id="img" class="layout_icon_button button" type="button" value="Upload Image" style="clear:both;" />
        </div>
        <div style="clear:both;"></div>

        <?php
    }

    /* show cloumn and theie respective icon and images */

    public function layout_category_column($columns) {
        $columns["layout_icon"] = __("Icon", "templatic_cat_icon");
        return $columns;
    }

    /* show cloumn and their respective icon and images */

    public function layout_category_column_data($deprecated, $column, $post_id) {

        if ($column == 'layout_icon') {
            global $wpdb;
            $term_table = $wpdb->prefix . "terms";
            $sql = "select * from $term_table where term_id=" . $post_id;
            $term = $wpdb->get_results($sql);
            if ($term[0]->term_font_icon) {
                return $icon = $term[0]->term_font_icon;
            } else {
                $icons = get_option("templtax_" . $post_id);

                if (!is_array($icons))
                    return;
                foreach ($icons as $size => $attach_id) {
                    if ($attach_id > 0) {
                        $img = wp_get_attachment_image($attach_id, 'layout_icon_medium');
                        return $img;
                    }
                }
            }
        }
    }

    /* show browse and font awesome icon option while add category */

    public function layout_edit_iconfield($term) {
        $id = $term->term_id;
        $layout_term_type = $term->term_type;
        $icons = get_option("templtax_" . $id);
        ?>			
        <table class='form-table templ-form-table'>
            <tbody>
            
            <?php
            if (isset($icons['img'])) {
                $attach_id = $icons['img'];
                $img = wp_get_attachment_image($attach_id, "layout_icon_medium");
            } else {
                $attach_id = 0;
                $img = '';
            }


            $layout_display = 'display:none;';
            if ($layout_term_type == 'layout_upload_img' && @$img != '') {
                $layout_display = 'display:block;';
            }

            /* set term icon */
            $term_icon = '';
            if ($term->term_font_icon != '0') {
                $term_icon = $term->term_font_icon;
            }
            ?>
            <tr  id="layout_icon_type_image" <?php if (@$layout_term_type == '' || $layout_term_type == 'layout_upload_img') { ?> style="display:block" <?php } else { ?> style="display:none" <?php } ?>>
                <th><label><?php echo __("Category Icons", "templatic_cat_icon"); ?></label></th>
                <td>
                    <div style="margin-bottom:20px; clear:both; position:relative; float:left; width:45px; z-index:999;" >
                        <div id="layout_preview_img" class="layout_icon_preview" ><?php echo $img; ?></div>
                        <input id="layout_icon_img" type="hidden" name="layout_icon_img" value="<?php echo $attach_id; ?>" />
                        <div>
                            <a  class="layout_remove" id="img" style="background:#fff; padding:4px; font-weight:bold; -webkit-border-radius: 25px;
                                -moz-border-radius: 25px; border-radius: 25px; font-size:13px; padding:0 5px; position:absolute; right:-4px; top:-4px; cursor:pointer;<?php echo $layout_display; ?>">
            <?php _e("X", "templatic_cat_icon"); ?>
                            </a>
                        </div>
                        <input id="img" class="button layout_icon_button" type="button" value="Upload Image" style="clear:both;"  />
                    </div>
                </td>
            </tr>

        <?php
    }
    
    /* Ajax Functions to save image */
    public function layout_ajax_new_icon()
    {
        //retrieve image
        if (! isset($_POST["img_url"]))
                die ("Error: No URL");

        $attach_id = $_POST["attach_id"];
        $size = $_POST["size"]; // which size are we doing? 

        $local_file = get_attached_file($attach_id);

        // generate metadata on basis of sizes
        $attach_data = wp_generate_attachment_metadata($attach_id, $local_file);
        wp_update_attachment_metadata( $attach_id,  $attach_data );	

        // return new image URL
        $data = array("newimg" => image_downsize($attach_id, "layout_icon_medium"),
                                  "size" => $size	
                                );

        header('Content-Type: application/json');
        echo json_encode($data);
        exit();

    }
    
    /* save image and font awesome icon option while add/edit category */
    public function layout_save_iconfield($term_id)
    {
        $icons = array(); 

        if (isset($_POST["layout_icon_img"]))
        {
                $attach_id = $_POST["layout_icon_img"];

                if ($attach_id > 0)
                {
                        $local_file = get_attached_file($attach_id);

                        $attach_data = wp_generate_attachment_metadata($attach_id, $local_file);
                        wp_update_attachment_metadata( $attach_id,  $attach_data );	
                        $icons['img'] = $attach_id;
                }
        }

        /* then save the taxonomy metadata */
        update_option("templtax_" . $term_id,$icons);

        global $wpdb;
        $term_table=$wpdb->prefix."terms";		
        $cat_icon=$_POST['layout_font_icon'];
        $layout_select_icon_type=$_POST['layout_select_icon_type'];	
        /*update the service price value in terms table field*/
        if(isset($_POST['layout_select_icon_type']) ){
                $sql="update $term_table set term_font_icon='".$cat_icon."' , term_type ='".$layout_select_icon_type."' where term_id=".$term_id;
                $wpdb->query($sql);
        }

    }
    
    public function search_layout_posts() {
        
        if (isset($_POST['value'])) {
            $value = $_POST['value'];
            $category = $_POST['category'];
            $selected = $_POST['selected'];
            $args = array('s' => $value, 'cat' => $category, 'post__not_in' => $selected);
            $the_query = new WP_Query($args);
            if ($the_query->have_posts()) {
                $html = "";
                while ($the_query->have_posts()) {
                    $the_query->the_post();
                    //whatever you want to do with each post
                    $html.="<span data-id='".get_the_ID()."' class='__post'>".get_the_title()."</span>";
                }
                $response = array('status' => 'success', 'content' => $html);
            } else {
                $response = array('status' => 'failed', 'message' => '<span class="__no_resut">no post found with keyword.</span>');
            }
            
            header('Content-Type: application/json');
            die(json_encode($response));
        }
    }
    
    public function select_from_search() {
        header('Content-Type: application/json');
        if(isset($_POST['post_id'])) {
            $post_id = $_POST['post_id'];
            $post = get_post($post_id);
            if(!$post) {
                die(json_encode(array('status' => 'fail', 'message' => 'invalid post')));
            }
            
            $content = "<div id='post_{$post->ID}' class='layout_single_post col-md-3 selected'>";
            $content .= "<input value='{$post->ID}' type='checkbox' name='layout_posts[]' class='hidden post_selector'/>";
            $content .= "<div class='layout_post_thumb'>" . $this->getPostThumbnail($post, 'thumb') . "</div>";
            $content .= "<div class='layout_post_title'>{$post->post_title}</div>";
            $content .= "</div>";
            
            die(json_encode(array('status' => 'success', 'content' => $content)));
        }else{
            die(json_encode(array('status' => 'fail', 'message' => 'invalid post')));
        }
    }
    
    public function get_parent_category() {
        if(isset($_POST['category'])) {
            $cat_id = $_POST['category'];
            $categories = get_categories(['parent' => $cat_id]);
            $html = "<option value=''>-choose Category-</option>";
            foreach ($categories as $key => $category) {
                $html .= "<option value='{$category->term_id}'>{$category->name}</option>";
            }
            header('Content-Type: application/json');
            die(json_encode(array('status' => 'success', 'content' => $html)));
        }
    }
}
