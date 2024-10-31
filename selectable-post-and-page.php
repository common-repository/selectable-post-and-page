<?php
/*
Plugin Name: Selectable Post and Page
Plugin URI: https://wordpress.org/plugins/selectable-post-and-page/
Description: Add a widget that can display posts from random, a single category and page.
Author: HappyMox
Text Domain: selectable-post-and-page
Version: 1.3.4
Author URI: http://happymox.wordpress.com
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if( !defined( 'WP_SPP_VERSION' ) ) {
    define( 'WP_SPP_VERSION', '1.3.4' ); // Version of plugin
}

if( !defined( 'WP_SPP_DIR' ) ) {
    define( 'WP_SPP_DIR', dirname( __FILE__ ) ); // Plugin dir
}

if( !defined( 'WP_SPP_URL' ) ) {
    define( 'WP_SPP_URL', plugin_dir_url( __FILE__ ) ); // Plugin url
}

/**
 * Load Text Domain
 * This gets the plugin ready for translation
 * 
 * @package Selectable Post and Page
 * @since 1.0.0
 */
function spp_load_textdomain() {
    load_plugin_textdomain( 'selectable-post-and-page', false, dirname( plugin_basename(__FILE__) ) . '/languages/' );
}
add_action('plugins_loaded', 'spp_load_textdomain');

// Script File
require_once( WP_SPP_DIR . '/include/spp-script.php' );

// Post Image Class file
require_once( WP_SPP_DIR . '/include/post-img.php' );

/**
 * Main class extending WP_Widget
 * @package Selectable Post and Page
 * @since 1.0.0
 */
class Selectable_Post_And_Page extends WP_Widget {
    
    private $template;
    private $temp_path = array(
        'default' => 'default',
        'full' => 'full-image',
        'list'=> 'list-view'
    );

    function __construct() {
        $this->template = array(
            'default' => esc_html__('Default', 'selectable-post-and-page'),
            'full' => esc_html__('Full Image', 'selectable-post-and-page'),
            'list'=> esc_html__('No image list of titles', 'selectable-post-and-page')
        );
        
        $SPP_widget = array( 
            'classname' => 'Selectable_Post_And_Page', 
            'description' => 'HappyMox: Display your selected posts and pages' 
        );
        
        parent::__construct(
                'Selectable_Post_And_Page', 
                'Selectable Post and Page', 
                $SPP_widget 
        );
        
        // Ajax call to update option
        add_action( 
                'wp_ajax_wp_spp_category_result_edit_form', 
                array( $this, 'wp_spp_category_result_edit_form' ) 
        );
        add_action( 
                'wp_ajax_nopriv_wp_spp_category_result_edit_form', 
                array( $this, 'wp_spp_category_result_edit_form' ) 
        );
        
        // custom image cropped for SPP 100x100
        add_image_size( 'spp-widget-thumb', 100, 100, true );
        
        // custom image cropped for SPP 400x340
        add_image_size( 'spp-widget-feature', 400, 340, true );
    }
    
    // Show the Widget
    function widget( $args, $instance ){
	global $post;
	$post_old = $post; // Save the post object.
	extract( $args, EXTR_SKIP );
	
        echo $before_widget;
        
        $title = apply_filters( 'widget_title', esc_attr( $instance['title'] ) );
        if( !isset($instance[ 'hide_widget_title' ]) ) {
            if( strlen( $title ) > 0 ){
                echo $before_title.$title.$after_title;
            }
	}
        
        // Get array of post info.
        if( isset( $instance["random_post"] ) ) {
            $qry_arg = array(
                'posts_per_page' => $instance["random_post_num"],
                'post_status' => 'publish',
                'post_type'=> strtolower( $instance['post_type'] ),
                'orderby' => 'rand'
            );
        } else if( isset( $instance["posts"] ) ){
            $qry_arg = array( 'post__in' => $instance["posts"],
                'post_status' => 'publish',
                'post_type'=> strtolower( $instance['post_type'] ),
                'orderby' => $instance['orderby'],
                'order' => $instance[ 'order_type' ]);
        } else {
            $qry_arg = '';
        }
        
        if ( $qry_arg != '' ) {
            $cat_posts = new WP_Query( $qry_arg );
        }

	// Excerpt length filter
	$function = create_function(
                '$length', 
                "return " . $instance['excerpt_length'] . ";"
        );
        // Excerpt More link filter
        $more_ = create_function( 
                '$more',  
                "return '...<div class=\"read-more\">"
                . "<a href=\"' . get_permalink() . '\">" 
                . __( '> ' . $instance['more_text'], 'selectable-post-and-page' ) 
                . "</a></div>';"
        );
        

	if( absint( $instance['excerpt_length'] ) > 0 ){
            add_filter( 'excerpt_length', $function, 999 );
            //Display read more link.
            if( isset( $instance[ 'read_more_link' ] ) ) {
                add_filter( 'excerpt_more', $more_, 20 );
            }
        }
        
        /* 
         * variable is get image of post
         */
        global $pim;
        if( isset( $cat_posts ) ){
            while( $cat_posts->have_posts() ){
                $cat_posts->the_post();
                
                preg_match_all( '/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', get_the_content(), $matches );
                $img = (isset($matches[1][0]))?$matches[1][0]:'';//$no_image;
                
                require WP_SPP_DIR . '/template/' . $this->temp_path[ $instance['layout'] ]. '.php';

            }
        }
	echo $after_widget;

	remove_filter( 'excerpt_length', $function );
        remove_filter( 'excerpt_more', $more_ );
	$post = $post_old; // Restore the post object.
        wp_reset_postdata();
    }

    // Form Processing
    function update( $new_instance, $old_instance ){
        return $new_instance;
    }
    
    // The Configuration Form
    function form( $instance ){
        $wid_number = $this->number . ',' . $this->id_base;
        $instance = wp_parse_args( ( array ) $instance, array( 'title' => '' ) );
        $title = $instance[ 'title' ];
        $cat = isset( $instance[ 'cat' ] ) ? $instance[ 'cat' ] : 1;
        $excerpt_length = isset( $instance[ 'excerpt_length' ] ) ? $instance[ 'excerpt_length' ] : 0;
        $posts = isset( $instance[ 'posts' ] ) ? $instance[ 'posts' ] : array();
        $order_value = isset( $instance[ 'order_type' ] ) ? $instance[ 'order_type' ] : 'ASC';
        $order_by_value = isset( $instance[ 'orderby' ] ) ? $instance[ 'orderby' ] : 'title';
        $instance[ 'hide_widget_title' ] = isset( $instance[ 'hide_widget_title' ] ) ? $instance[ 'hide_widget_title' ] : false;
        $instance[ 'read_more_link' ] = isset( $instance[ 'read_more_link' ] ) ? $instance[ 'read_more_link' ] : false;
        $instance['post_type'] = isset( $instance[ 'post_type' ] ) ? $instance['post_type'] : 'Post';
        $instance[ 'post_date' ] = isset( $instance[ 'post_date' ] ) ? $instance[ 'post_date' ] : false;
        $instance[ 'random_post' ] = isset( $instance[ 'random_post' ] ) ? $instance[ 'random_post' ] : false;
        $instance[ 'random_post_num' ] = ( isset( $instance[ 'random_post_num' ] ) && $instance[ 'random_post_num' ] > 0 ) ? $instance[ 'random_post_num' ] : 1;
        
        $instance['more_text'] = isset( $instance[ 'more_text' ] ) ? $instance['more_text'] : 'More';
        $instance['date_text'] = isset( $instance[ 'date_text' ] ) ? $instance['date_text'] : 'Date';
        
        $instance['layout'] = isset( $instance[ 'layout' ] ) ? $instance['layout'] : 'default';
        
        $output = '';
        
        $cat = ($instance['post_type'] == 'Page')?false:$cat;
        
        $args = array( 'posts_per_page' => 100,
            'cat' => $cat,
            'post_status' => 'publish',
            'post_type' => strtolower( $instance['post_type'] ),
            'orderby' => $order_by_value,
            'order' => $order_value
        );
        $selected_posts = new WP_Query( $args );

        if( isset( $selected_posts ) ) {
            $num = 1;
            while( $selected_posts->have_posts() ){
                $selected_posts->the_post();
                $select = ( in_array( get_the_ID(), $posts ) ) ? 'checked="checked"' : '';
                $output .= '<label for="' . $this->get_field_id("posts") . $num . '">';
                $output .= '<input type="checkbox" id="' . $this->get_field_id("posts") . $num . '" name="'. $this->get_field_name("posts").'[]" value="'. get_the_ID() .'"'. $select.' />';
                $output .= get_the_title();
                $output .= '</input>';
                $output .= '</label><br/>';
                $num++;
            }
        }
        ?>
	<p>
            <label for="<?php echo $this->get_field_id("title"); ?>">
                <?php _e( 'Title', 'selectable-post-and-page' ); ?>:
                <input class="spp-widget-title" id="<?php echo $this->get_field_id("title"); ?>" name="<?php echo $this->get_field_name("title"); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
            </label>
	</p>
        
        <p>
            <label for="<?php echo $this->get_field_id( "hide_widget_title" );?>">
                <input type="checkbox" id="<?php echo $this->get_field_id( "hide_widget_title" ); ?>" name="<?php echo $this->get_field_name( "hide_widget_title" ); ?>" <?php checked( (bool) $instance['hide_widget_title'], true ); ?> />
                <?php _e( "Hide Widget title", 'selectable-post-and-page' ); ?>
            </label>
        </p>

        <p>
            <h4><?php _e( 'Options for Selectable Post(s)', 'selectable-post-and-page' );?></h4>
        </p>
        <p class="spp-post-types spp-option-bg">
            
            <?php $post_types = array( 'Post' => 'Post(s)', 'Page' => 'Page(s)' );
            foreach( $post_types as $value => $desc ){
                $chk = ( $value == $instance['post_type'] )?' checked="checked"' : '';?>
            
                <input type="radio" class="post-types" id="<?php echo $this->get_field_id( "post_type" ) . $value;?>" name="<?php echo $this->get_field_name( "post_type" )?>" value="<?php echo $value; ?>"<?php echo $chk;?>/>
                <label for="<?php echo $this->get_field_id( "post_type" ) . $value; ?>">
                    <?php _e( $desc, 'selectable-post-and-page' ); ?>
                </label>
            <?php }?>
                
            <input type="hidden" id="<?php echo $this->get_field_id("saved_posts"); ?>" name="<?php echo $this->get_field_name("saved_posts"); ?>" value="<?php echo implode(',', $posts);?>"/>
        </p>
        
        <p class="spp-option-bg spp-category-row"<?php echo ($instance['post_type'] == 'Page')?'style="display: none;"':'';?>>
            <label>
                <input type="hidden" id="<?php echo $this->get_field_id("my_id"); ?>" class="my_id" name="<?php echo $this->get_field_name("my_id"); ?>" value="<?php echo $wid_number; ?>"/>
                <?php _e( 'Category ', 'selectable-post-and-page' ); ?>:
                <?php wp_dropdown_categories( array( 'name' => $this->get_field_name("cat"), 'selected' => $cat ) ); ?>
            </label>
	</p>
        <p class="spp-option-bg">
            <label for="<?php echo $this->get_field_id( "orderby" )?>">
                <?php _e( 'Order By :', 'selectable-post-and-page' ); ?>

                <select id="<?php echo $this->get_field_id( "orderby" )?>" name="<?php echo $this->get_field_name( "orderby" )?>">
                    <?php $order_by = array( 'title' => 'Title', 'date' => 'Published date', 'ID' => 'Post_ID', 'author' => 'Author');
                    foreach( $order_by as $value => $desc ) {
                        $obv_selected = ( $value == $order_by_value )?' selected="selected"':'';?>
                    <option value="<?php echo $value; ?>"<?php echo $obv_selected; ?>><?php echo $desc;?></option>
                    <?php }?>
                </select>
            </label>
        </p>
        <p class="spp-option-bg">
            <label for="<?php echo $this->get_field_id( "order_type" )?>">
                <?php _e( 'Order Type :', 'selectable-post-and-page' ); ?>
            </label>
            
            <select id="<?php echo $this->get_field_id( "order_type" )?>" name="<?php echo $this->get_field_name( "order_type" )?>">
                <?php $order_type = array( "ASC" => "Ascending", "DESC" => "Descending" ); 
                foreach( $order_type as $type => $key ) {
                    $ov_selected = ( $type == $order_value )?' selected="selected"':'';?>
                <option value="<?php echo $type; ?>"<?php echo $ov_selected; ?>><?php echo $key; ?></option>
                <?php }?>
            </select>
        </p>
        
        <p>
            <input type="checkbox" id="<?php echo $this->get_field_id( "random_post" )?>" name="<?php echo $this->get_field_name( "random_post" )?>" <?php checked( (bool) $instance['random_post'], true ); ?> />
            <label for="<?php echo $this->get_field_id( "random_post" )?>">
                <?php _e( 'Random post', 'selectable-post-and-page' ); ?>
            </label>
        </p>
        
        <p class="spp-random-post-num"<?php echo ($instance['random_post'] == false)?' style="display: none;"':'' ?>>
            <label for="<?php echo $this->get_field_id( "random_post_num" ); ?>">
                <?php _e( 'Number of posts to show:', 'selectable-post-and-page' ); ?>
            </label>
            <input type="text" id="<?php echo $this->get_field_id( "random_post_num" ); ?>" name="<?php echo $this->get_field_name( "random_post_num" ); ?>" value="<?php echo absint($instance['random_post_num']); ?>" size="3" />
	</p>
        
        <div class="spp-result-list"<?php echo ($instance['random_post'] != false)?' style="display: none;"':'' ?>>
            <h4><?php _e( 'Selectable Posts:', 'selectable-post-and-page' ); ?></h4>
            <div class="<?php echo 'selectablepost-'.$this->number . $this->id_base. ' spp-category_result'; ?>">
               <?php echo $output; ?>
            </div>
        </div>
        
        <p class="spp-excerpt-length">
            <label for="<?php echo $this->get_field_id( "excerpt_length" ); ?>">
                <?php _e( 'Excerpt length (in words):', 'selectable-post-and-page' ); ?>
            </label>
            <input type="text" id="<?php echo $this->get_field_id( "excerpt_length" ); ?>" name="<?php echo $this->get_field_name( "excerpt_length" ); ?>" value="<?php echo absint($excerpt_length); ?>" size="3" />
	</p>
        
        <!--------- Begin: More ------------->
        <p>
            <input type="checkbox" id="<?php echo $this->get_field_id( "read_more_link" )?>" name="<?php echo $this->get_field_name( "read_more_link" )?>" <?php checked( (bool) $instance['read_more_link'], true ); ?> />
            <label for="<?php echo $this->get_field_id( "read_more_link" ); ?>">
                <?php _e( 'Display "more" link? (with text)', 'selectable-post-and-page' ); ?>
            </label>
        </p>
        <p class="spp-right-2">
            <label for="<?php echo $this->get_field_id( "more_text" ); ?>">
                <?php _e( 'Text (for more):', 'selectable-post-and-page' ); ?>
            </label>
            <input type="text" id="<?php echo $this->get_field_id( "more_text" ); ?>" name="<?php echo $this->get_field_name( "more_text" ); ?>" value="<?php echo $instance['more_text']?>" size="23" />
        </p>
        
        <!--------- Begin: Date ------------->
        <p>
            <input type="checkbox" id="<?php echo $this->get_field_id( "post_date" )?>" name="<?php echo $this->get_field_name( "post_date" )?>" <?php checked( (bool) $instance['post_date'], true ); ?> />
            <label for="<?php echo $this->get_field_id( "post_date" ); ?>">
                <?php _e( 'Display Post\'s Date', 'selectable-post-and-page' ); ?>
            </label>
        </p>
        <p class="spp-right-2">
            <label for="<?php echo $this->get_field_id( "date_text" ); ?>">
                <?php _e( 'Text (for date):', 'selectable-post-and-page' ); ?>
            </label>
            <input type="text" id="<?php echo $this->get_field_id( "date_text" ); ?>" name="<?php echo $this->get_field_name( "date_text" ); ?>" value="<?php echo $instance['date_text']?>" size="23" />
        </p>
        
        <!--------- Begin: Template layout ------------>
        <p>
            <h4><?php _e( 'Post template design', 'selectable-post-and-page' );?></h4>
        </p>
        <p>
            <input type="hidden" id="<?php echo $this->get_field_id( "layout" ); ?>" name="<?php echo $this->get_field_name( "layout" ); ?>" value="<?php echo $instance['layout']?>" />
        </p>
        <table class="spp-template-layout">
            <tr>
                <?php foreach ($this->template as $key => $label): ?>
                <td<?php echo ($instance['layout'] == $key)?' class="selected"':'';?> key-value="<?php echo $key;?>">
                    <img id="<?php echo $key;?>" src="<?php echo WP_SPP_URL;?>assets/image/layout-<?php echo $key;?>.png" title="<?php echo $label;?>">
                </td>
                <?php endforeach; ?>
            </tr>
        </table>
    <?php
    }
    
    
    //Get category result edit form (Ajax result)
    function wp_spp_category_result_edit_form() {
        //posted datas
        $select_cat = sanitize_text_field( wp_unslash( $_GET['cat'] ) );
        $wid_id = sanitize_text_field( wp_unslash( $_GET['id'] ) );
        $post_type = sanitize_text_field( wp_unslash( $_GET['post_type'] ) );
        $order = sanitize_text_field( wp_unslash( $_GET['order'] ) );
        $orderby = sanitize_text_field( wp_unslash( $_GET['orderby'] ) );
        $saved_posts = explode( ',', sanitize_text_field( wp_unslash( $_GET['saved_posts'] ) ));
        $widget_info = explode( ',', $wid_id );
        
        //query info
        if ( count( $widget_info ) > 0 ) {
            $args = array( 'posts_per_page' => 100,
                'cat' => $select_cat,
                'post_status' => 'publish',
                'post_type' => $post_type,
                'orderby' => $orderby,
                'order' => $order
                );
            $catposts = new WP_Query( $args );
        }
        
        //Get posts
        if( isset( $catposts ) ){
            $out = ''; 
            $num = 1;
             while( $catposts->have_posts() ){
                $catposts->the_post();
                $select = ( in_array( get_the_ID(), $saved_posts ) ) ? 'checked="checked"' : '';
                $out .= '<label for="widget-' . $widget_info[1] . '-' . $widget_info[0] . '-posts' . $num . '">';
                $out .= '<input type="checkbox" class="checkbox" id="widget-' . $widget_info[1] . '-' . $widget_info[0] . '-posts' . $num . '" name="widget-'.$widget_info[1].'['.$widget_info[0].'][posts][]" value="'. get_the_ID() .'"'. $select.' />';
                $out .= get_the_title();
                $out .= '</input>';
                $out .= '</label><br/>';
                $num++;
            }
            wp_die( $out );
        } else {
            wp_die('Nothing found!');
        }
        wp_reset_postdata();
    }
}

//add_action( 'widgets_init', create_function( '', 'return register_widget("Selectable_Post_And_Page");' ) ); 
add_action( 'widgets_init', 'wp_spp_widget_init' );
function wp_spp_widget_init() {
    register_widget("Selectable_Post_And_Page");
}

register_activation_hook( WP_SPP_DIR, 'SPP_active' );
function SPP_activate(){
    //add_action( 'widgets_init', create_function( '', 'return register_widget("Selectable_Post_And_Page");' ) );
}




