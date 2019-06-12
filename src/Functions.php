<?php

/**
 * Set Core Functions
 *
 * @name Functions
 * @since 1.0.0
 */
abstract class Functions
{

    const DOMAIN_NAME = DOMAIN_LANG;

    /**
     * Get template part
     *
     * @param $group_template
     * @param $file
     * @param array $args
     * @return bool|string
     */
    static public function getTemplatePart($group_template, $file, $args = array(), $template_directory = 'tpl'){
        $filename = TEMPLATEPATH.'/' . $template_directory . '/'.$group_template.'/'.$file.'.php';
        
        $retour = false;

        // On recupere le fichier demandé
        ob_start();

        if(file_exists($filename)){
            include $filename;
        }

        $retour .= ob_get_contents();
        ob_end_clean();

        return $retour;
    }

    /**
     * Get assets directory uri
     *
     * @param $type
     * @return string
     */
    static public function getAssetsDirectory($type = null){
        $tUri = get_template_directory_uri() . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR .  $type;
        return rtrim($tUri, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    }

    /**
     * Get template part
     *
     * @param $group_template
     * @param $file
     * @param array $args
     * @return bool|string
     */
    static public function getTranslation($string, $domain = 'default', $args = []){
        return !empty($args) ? vsprintf(__($string, $domain), $args) : __($string, $domain);
    }

    /**
     * Return post_level by post_id
     *
     * @param int|\WP_Post $postID
     * @return int|bool
     */
    static function getPostLevelByPostID($postID = 0) {
        global $post;
        $postID = ( is_object($postID) ) ? $post->ID : $postID;
        return count(get_post_ancestors($postID));
    }

    /**
     * Return post_name by post_id
     *
     * @param int|\WP_Post $postID
     * @return string|bool
     */
    static function getSlugByPostID($postID = 0) {
        global $post;
        $postID = ( is_object($postID) ) ? $post->ID : $postID;
        $post_data = get_post($postID, ARRAY_A);
        $slug = !empty($post_data['post_name']) ? $post_data['post_name'] : false;
        return $slug;
    }

    /**
     * Get Gravatar Image
     * @param $email
     * @param int $s
     * @param string $d
     * @param string $r
     * @param bool $img
     * @param array $atts
     * @return string
     */
    static function getGravatarImg( $email, $s = 80, $d = 'mm', $r = 'g', $img = false, $atts = array() ) {
        $url = 'https://www.gravatar.com/avatar/';
        $url .= md5( strtolower( trim( $email ) ) );
        $url .= "?s=$s&d=$d&r=$r";
        if ( $img ) {
            $url = '<img src="' . $url . '"';
            foreach ( $atts as $key => $val )
                $url .= ' ' . $key . '="' . $val . '"';
            $url .= ' />';
        }
        return $url;
    }

    /**
     * Truncate text with ... or custom pad
     * @param $string
     * @param $width
     * @param string $pad
     * @return mixed|string
     */
    static function truncated($string, $width, $pad="...") {
        if(strlen($string) > $width) {
            $string = str_replace("\n",' ',$string);
            $string = wordwrap($string, $width);
            $string = substr($string, 0, strpos($string, "\n")). $pad;
        }
        return $string;
    }

    /**
     * Get Image Src
     * @param $imageId
     * @param string $size
     * @return bool|mixed|string
     */
    static function getImageSrc($imageId, $size = 'thumbnail') {
        $img = wp_get_attachment_image_src($imageId, $size);
        $imageSrc = !empty($img) ? reset($img) : false;
        $uploadDir = wp_upload_dir();

        return (!empty($imageSrc) && file_exists(str_replace($uploadDir['baseurl'], $uploadDir['basedir'], $imageSrc))) ? $imageSrc : \ProjectFunctions::getAssetsDirectory('images/default') . $size . '.png';
    }

    /**
     * Format date to retrieve twitter date format
     * @param $datetime
     * @param bool $full
     * @return string
     */
    static function getDateAgo($datetime, $full = false) {
        $now = new DateTime;
        $ago = new DateTime($datetime);
        $diff = $now->diff($ago);

        $diff->w = floor($diff->d / 7);
        $diff->d -= $diff->w * 7;

        $string = array(
            'y' => 'year',
            'm' => 'month',
            'w' => 'week',
            'd' => 'day',
            'h' => 'hour',
            'i' => 'minute',
            's' => 'second',
        );

        foreach ($string as $k => &$v) {
            if ($diff->$k) {
                $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
            } else {
                unset($string[ProjectFunctions::getTranslation($k, self::DOMAIN_NAME)]);
            }
        }


        if (!$full) $string = array_slice($string, 0, 1);
        return $string ? implode(', ', $string) . ' ago' : 'just now';
    }

    /**
     * Upload Files
     * @param $name
     * @return array|bool
     */
    static function uploadFile($name){
        if(empty($_FILES[$name])){
            return false;
        }
        if ( ! function_exists( 'wp_handle_upload' ) ) {
            require_once( ABSPATH . 'wp-admin/includes/file.php' );
        }

        $uploadedfile = $_FILES[$name];

        $upload_overrides = array( 'test_form' => false );

        $movefile = wp_handle_upload( $uploadedfile, $upload_overrides );

        if ( $movefile && !isset( $movefile['error'] ) ) {
            return $movefile;
        } else {
            wp_send_json_error($movefile);
        }

        return true;
    }

    /**
     * Create Attachment Post
     * @param $post_id
     * @param $file
     * @return int|WP_Error
     */
    static function uploadMedia($post_id, $file){
        $attach_id = wp_insert_attachment(
            [
                'post_mime_type' => $file['type'],
                'guid' => $file['url'],
            ],
            $file['file'],
            $post_id
        );
        require_once( ABSPATH . 'wp-admin/includes/image.php');
        $attach_data = wp_generate_attachment_metadata($attach_id, $file['file']);
        wp_update_attachment_metadata($attach_id, $attach_data);

        return $attach_id;
    }
}