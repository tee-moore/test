<?php
/*
Plugin Name:  advanced-category
Plugin URI:   
Description:  This plugin adds image and video fields to categories
Version:      1.0
Author:       Timur Panchenko
Author URI:   2teemoore@gmail.com
Text Domain:  
Domain Path:  /languages
License:      GPL2
License URI:  https://www.gnu.org/licenses/gpl-2.0.html
*/

if ( ! class_exists( 'AC_Plugin' ) ) {

class AC_Plugin {

    public function __construct()
    {
    //
    }

    /*
    * Initialize the class and start calling our hooks and filters
    */
    public function init()
    {
        add_action( 'init', array ( $this, 'advanced_category_locale' ));
        add_action( 'wp_enqueue_scripts', array ( $this, 'advanced_category_enqueue_scripts' ));
        add_action( 'admin_enqueue_scripts', array ( $this, 'advanced_category_admin_enqueue_scripts' ));
        add_action( 'category_add_form_fields', array ( $this, 'advanced_category_fields' ), 10, 2 );
        add_action( 'category_edit_form_fields', array ( $this, 'advanced_category_fields' ), 10, 2 );
        add_action( 'edited_category', array ( $this, 'advanced_category_save_fields' ), 10, 1 );
        add_action( 'create_category', array ( $this, 'advanced_category_save_fields' ), 10, 1 );
        add_action( 'term_description', array ( $this, 'advanced_category_description' ), 10, 2 );
    }

    /**
     * Add textdomain
     */
    function advanced_category_locale()
    {
         load_plugin_textdomain( 'advanced_category', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
    }

    /**
     * Add scripts and styles for front
     */
    function advanced_category_enqueue_scripts()
    {
        wp_enqueue_script( 'advanced-category-script', plugins_url('/js/advanced-category-script.js', __FILE__), array('jquery'), null, true);
        wp_enqueue_style( 'advanced-category-style', plugins_url('/css/advanced-category-style.css', __FILE__), array(), null, 'all' );
        wp_localize_script('advanced-category-script', 'myajax', 
            array(
                'url' => admin_url('admin-ajax.php')
            )
        );
    }


    /**
     * Add scripts and styles for admin
     */
    function advanced_category_admin_enqueue_scripts()
    {
        wp_enqueue_media();
        wp_enqueue_script( 'advanced-category-admin-script', plugins_url('/js/advanced-category-script-admin.js', __FILE__), array('jquery'), null, true);
        wp_enqueue_style( 'advanced-category-admin-style', plugins_url('/css/advanced-category-style-admin.css', __FILE__), array(), null, 'all' );
    }

    /**
     * Add image and video fields to category taxonomy
     */
    function advanced_category_fields($term)
    {

        $default_image = plugins_url('img/default.png', __FILE__);
        $default_video = "https://img.youtube.com/vi//0.jpg";

        $thumbnail_id = get_term_meta($term->term_id, 'advanced_category_thumbnail', true);
        $thumbnail_id = ($thumbnail_id) ? $thumbnail_id : 0;
        $image_attributes = wp_get_attachment_image_src($thumbnail_id, 'thumbnail');
        $src = ($image_attributes) ? $image_attributes[0] : $default_image;
        
        $video_link = get_term_meta($term->term_id, 'advanced_category_video', true);
        
        if($video_link){
            $video_info = explode('/', $video_link);
            $code = end($video_info);
            
            if (preg_match("/watch/", $code)) {
                $video_code = $this->extractYoutubeVideoID($video_link);
                $video_thumb = "https://img.youtube.com/vi/" . $video_code . "/0.jpg";
            } else {
                $video_code = $this->getVimeoId($video_link);
                $video_thumb = $this->getVimeoThumb($video_code);
            }

        } else {
            $video_thumb = $default_video;
        }

        if (current_filter() == 'category_edit_form_fields'){
            ?>
            <tr class="form-field">
                <th valign="top" scope="row"><label for="term_fields[thumbnail]"><?php _e('Image for category', 'advanced_category'); ?></label></th>
                <td>
                    <div class="upload">
                        <img id="thumbnail" data-src="<?php echo $default_image; ?>" src="<?php echo $src; ?>" width="150px" height="150px" />
                        <div>
                            <input type="hidden"  id="term_fields[advanced_category_thumbnail]" name="term_fields[advanced_category_thumbnail]" value="<?php echo $thumbnail_id; ?>" />
                            <button type="submit" class="upload_image_button button"><?php _e('Upload', 'advanced_category'); ?></button>
                            <button type="submit" class="remove_image_button button">&times;</button>
                        </div>
                    </div>
                    <span class="description"><?php _e('Please upload image for category', 'advanced_category'); ?></span>
                </td>
            </tr> 
            <tr class="form-field">
                <th valign="top" scope="row"><label for="term_fields[advanced_category_video]"><?php _e('Video for category', 'advanced_category'); ?></label></th>
                <td>
                    <div class="upload">
                        <img id="video_thumbnail" src="<?php echo $video_thumb; ?>" width="150px" height="150px" />
                        <div>
                            <input type="hidden"  id="term_fields[advanced_category_video]" name="term_fields[advanced_category_video]" value="<?php echo $video_link; ?>" />
                            <button type="submit" class="upload_video_button button"><?php _e('Insert', 'advanced_category'); ?></button>
                            <button type="submit" class="remove_video_button button">&times;</button>
                        </div>
                    </div>    
                </td>
                <p class="description"><?php _e('Please upload image for category', 'advanced_category'); ?></p>
            </tr> 
        <?php } elseif (current_filter() == 'category_add_form_fields') {
            ?>
            <div class="form-field">
                <label for="term_fields[advanced_category_thumbnail]"><?php _e('Image for category', 'advanced_category'); ?></label>
                <div class="upload">
                    <img id="thumbnail" data-src="<?php echo $default_image; ?>" src="<?php echo $src; ?>" width="150px" height="150px" />
                    <div>
                        <input type="hidden"  id="term_fields[advanced_category_thumbnail]" name="term_fields[advanced_category_thumbnail]" value="<?php echo $thumbnail_id; ?>" />
                        <button type="submit" class="upload_image_button button"><?php _e('Upload', 'advanced_category'); ?></button>
                        <button type="submit" class="remove_image_button button">&times;</button>
                    </div>
                </div>
                <p class="description"><?php _e('Please upload image for category', 'advanced_category'); ?></p>
            </div>
            <div class="form-field">
                <label for="term_fields[advanced_category_video]"><?php _e('Video for category', 'advanced_category'); ?></label>
                <div class="upload">
                    <img id="video_thumbnail" src="<?php echo $video_thumb; ?>" width="150px" height="150px" />
                    <div>
                        <input type="hidden"  id="term_fields[advanced_category_video]" name="term_fields[advanced_category_video]" value="" />
                        <button type="submit" class="upload_video_button button"><?php _e('Insert', 'advanced_category'); ?></button>
                        <button type="submit" class="remove_video_button button">&times;</button>
                    </div>
                </div>
                <p class="description"><?php _e('Please upload image for category', 'advanced_category'); ?></p>
            </div>
        <?php
        }
    }

    /**
     * Save fields value
     */
    function advanced_category_save_fields($term_id)
    {
        if (!isset($_POST['term_fields'])) {
            return;
        }

        foreach ($_POST['term_fields'] as $key => $value) {
            update_term_meta($term_id, $key, sanitize_text_field($value));
        }
    }

    /**
     * Output image and video fields on category page
     */
    function advanced_category_description($description, $category)
    {
        if( !is_admin() ){
            
            $output = "";
            
            $thumbnail_id = get_term_meta($category, 'advanced_category_thumbnail', true);
            if($thumbnail_id){
                $image_attributes = wp_get_attachment_image_src($thumbnail_id, 'medium');
                $src = ($image_attributes[0]) ? $image_attributes[0] : $default_image;
                $img = "<div class='taxonomy-thumbnail'>" . __('Category image:', 'advanced_category') . "<img class='thumbnail' src='$src;' /></div>";
                $output .= $img;
            }

            $video_link = get_term_meta($category, 'advanced_category_video', true);
            if($video_link){
                $video_info = explode('/', $video_link);
                $code = end($video_info);
                
                if (preg_match("/watch/", $code)) {
                    $video_code = $this->extractYoutubeVideoID($video_link);
                    $video_thumb = "<div class='taxonomy-video'>" . __('Category video:', 'advanced_category') . "<a target='_blank' href='$video_link'><img class='thumbnail' src='https://img.youtube.com/vi/$video_code/0.jpg' /></a></div>";
                    $output .= $video_thumb;
                } else {
                    $video_code = $this->getVimeoId($video_link);
                    $video_pic = $this->getVimeoThumb($video_code, 'large');
                    $video_thumb = "<div class='taxonomy-video'>" . __('Category video:', 'advanced_category') . "<a target='_blank' href='$video_link'><img class='thumbnail' src='" . $video_pic . "' /></a></div>";
                    $output .= $video_thumb;
                }

            } else {
                $video_thumb = $default_video;
            }

            return $output . $description;
        }

        return $description;
    }

    function extractYoutubeVideoID($url)
    {
        $regExp = "/^.*((youtu.be\/)|(v\/)|(\/u\/\w\/)|(embed\/)|(watch\?))\??v?=?([^#\&\?]*).*/";
        preg_match($regExp, $url, $video);
        return $video[7];
    }

    function getVimeoId($url)
    {
        if (preg_match('#(?:https?://)?(?:www.)?(?:player.)?vimeo.com/(?:[a-z]*/)*([0-9]{6,11})[?]?.*#', $url, $m)) {
            return $m[1];
        }
        return false;
    }

    function getVimeoThumb($id, $size = 'small')
    {
        $arr_vimeo = unserialize(file_get_contents("http://vimeo.com/api/v2/video/$id.php"));
        switch ($size) {
            case 'small':
                return $arr_vimeo[0]['thumbnail_small'];
                break;
            case 'medium':
                return $arr_vimeo[0]['thumbnail_medium'];
                break;
            case 'large':
                return $arr_vimeo[0]['thumbnail_large'];
                break;
        }
    }
}
 
$AC_Plugin = new AC_Plugin();
$AC_Plugin->init();
 
}