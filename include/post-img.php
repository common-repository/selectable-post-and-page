<?php
/**
 * get and resize image of post and page
 * @package Selectable Post and Page
 * @since 1.0.0
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

class Post_Img {
    
    private $site_uri;
    private $site_dir;
    private $crop;
    private $os;


    public function __construct() {
        $this->site_dir = ABSPATH;
        $this->site_uri = site_url() . '/';
        $this->crop = true;
        $this->os = substr( php_uname( 's' ), 0, 3 );
    }
    
    /*
     * function to get size of image
     * @package Selectable Post and Page
     * @since 1.0.0
     */
    public function get_size( $_size = 'spp-widget-thumb' ) {
        global $_wp_additional_image_sizes;
        $sizes = array(
            'width'  => $_wp_additional_image_sizes[ $_size ]['width'],
            'height' => $_wp_additional_image_sizes[ $_size ]['height'],
            'crop'   => $_wp_additional_image_sizes[ $_size ]['crop'],
        );
        
        return $sizes;
    }


    /*
     * function to get and resize image of post
     * @package Selectable Post and Page
     * @since 1.0.0
     */
    public function get_image($img, $new_width, $new_height){
        //echo "In " . $img;
        $resized_image = '';
        if ( strlen($img) > 0) {
            $path_img = esc_attr( trim($img) );

            $localpath = str_replace( $this->site_uri, $this->site_dir, $path_img );
            //echo $localpath;
            if ( strtolower( $this->os ) !== 'win' ) {
                $path = str_replace('\\', '/', $localpath );
            } else {
                $path = str_replace('/', '\\', $localpath );
                //$path = $localpath;
            }
            $suffix = "{$new_width}x{$new_height}";

            $th = pathinfo( $path_img );
            $ext = $th['extension'];

            $name = wp_basename( $path_img, ".$ext" );
            $new_name = $name . '-' . $suffix . '.' . $ext;
            
            $dest_path = str_replace($name. '.' . $ext, '', $path );

            //$result = image_resize( $path, $new_width, $new_height, $crop, $suffix, $destination_dir );
            //$result = image_resize( $path, $new_width, $new_height, $this->crop, $suffix );
            
            //echo $path . '<br/>' . $dest_path;
            /*
            $result = wp_get_image_editor( $path );
            if ( ! is_wp_error( $result ) ) {
                $result->resize( $new_width, $new_height, $this->crop );
                //$result->set_quality( 80 ); //100
                $result->save( $new_name );
                
                $resized_image = $new_name;
                //$resized_image = str_replace( $name, $name . '-' . $suffix,  $path_img);
            }*/
            
            
            $editor = wp_get_image_editor( $path );
            if ( !is_wp_error( $editor ) ) {
                //return $editor;
                $editor->set_quality( 80 );

                $resized = $editor->resize( $new_width, $new_height, $this->crop );
                if ( !is_wp_error( $resized ) ) {
                    //return $resized;

                    $dest_file = $editor->generate_filename( $suffix, $dest_path );
                    $saved = $editor->save( $dest_file );

                    if ( !is_wp_error( $saved ) ) {
                        //return $saved;
                        // $resized_image = $dest_file;
                        $resized_image =str_replace( $name, $name . '-' . $suffix,  $path_img);
                    } else {
                        $resized_image = '';
                    }
                }
            }
            
            //$resized_image = str_replace( $name, $name . '-' . $suffix,  $resized_image);
 
            //return $dest_file;

            //print_r($result);
            /*
            if (!is_wp_error ($result)) {
                if (file_exists ($result)) {
                    //echo "Good";
                    $result = str_replace ('/', '\\', $result);

                    // For Linux OS
                    if ( strtolower( $this->os ) !== 'win' ) {
                        $result = str_replace ('\\', '/', $result);
                    }
                    $this->site_dir = str_replace('/', '\\', $this->site_dir);
                    $result = str_replace ($this->site_dir, $this->site_uri, $result);

                    // For Windows OS
                    if ( strtolower( $this->os ) === 'win' ) {
                        $result = str_replace ('\\', '/', $result);
                    }
                    $resized_image = $result;
                } else {
                    $resized_image = '';
                    //$resized_image = "http://crypto.net/ic/uploads/2015/04/NoImage_l.png";
                }
            }*/
        }
        //return $dest_file;
        //echo $resized_image;
        return $resized_image;
    }
}

global $pim;
$pim = new Post_Img();
