<?php
/**
 * Set Core Functions
 *
 * @name Functions
 * @since 1.0.0
 * @update 2.0.0
 */
abstract class Functions
{
    /**
     * Return post_level by post_id
     *
     * @param int|\WP_Post $postID
     * @return int|bool
     */
    static function get_post_level_by_post_id( $postID = 0 )
    {
        global $post;

        $postID = ( is_object( $postID ) ) ? $post->ID : $postID;

        return count( get_post_ancestors( $postID ) );
    }

    /**
     * Return post_name by post_id
     *
     * @param int|\WP_Post $postID
     * @return string|bool
     */
    static function get_slug_by_post_id( $postID = 0 )
    {
        global $post;

        $postID     = ( is_object( $postID ) ) ? $post->ID : $postID;
        $post_data  = get_post( $postID, ARRAY_A );

        $slug = $post_data[ 'post_name' ] ?? false;

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
    static function get_gravatar_img( $email, $s = 80, $d = 'mm', $r = 'g', $img = false, $atts = [] )
    {
        $url = self::get_gravatar_url( $email, $s, $d, $r, $img, $atts );

        if ( $img ) {
            $url = '<img src="' . $url . '"';
            foreach ( $atts as $key => $val )
                $url .= ' ' . $key . '="' . $val . '"';
            $url .= ' />';
        }

        return $url;
    }

    /**
     * Get Gravatar URL
     * @param $email
     * @param int $s
     * @param string $d
     * @param string $r
     * @param bool $img
     * @param array $atts
     * @return string
     */
    static function get_gravatar_url( $email, $s = 80, $d = 'mm', $r = 'g', $img = false, $atts = [] )
    {
        $url = 'https://www.gravatar.com/avatar/';
        $url .= md5( strtolower( trim( $email ) ) );
        $url .= "?s=$s&d=$d&r=$r";

        return $url;
    }

    /**
     * Truncate text with ... or custom pad
     *
     * @param $string
     * @param $width
     * @param string $pad
     * @return mixed|string
     */
    static function truncated( $string, $width, $pad = '...')
    {
        if( strlen( $string ) > $width ) {
            $string = str_replace( "\n",' ', $string) ;
            $string = wordwrap( $string, $width );
            $string = substr( $string, 0, strpos( $string, "\n" ) ). $pad;
        }

        return $string;
    }

    /**
     * Format date to retrieve twitter date format
     *
     * @param $datetime
     * @param bool $full
     * @return string
     */
    static function get_date_ago( $datetime, $full = false )
    {
        $now    = new DateTime;
        $ago    = new DateTime( $datetime );
        $diff   = $now->diff( $ago );

        $diff->w = floor( $diff->d / 7 );
        $diff->d -= $diff->w * 7;

        $string = [
            'y' => 'year',
            'm' => 'month',
            'w' => 'week',
            'd' => 'day',
            'h' => 'hour',
            'i' => 'minute',
            's' => 'second',
        ];

        foreach ( $string as $k => &$v ) {
            if ( $diff->$k ) {
                $v = $diff->$k . ' ' . $v . ( $diff->$k > 1 ? 's' : '' );
            } else {
                unset( $string[ __( $k, 'undefined-wp-framework' ) ] );
            }
        }

        if ( !$full )
            $string = array_slice( $string, 0, 1 );

        return $string ? sprintf( __( '%s ago', 'undefined-wp-framework' ), implode( ', ', $string ) ) : __( 'just now', 'undefined-wp-framework' );
    }

    /**
     * Upload Files
     *
     * @param $name
     * @return array|bool
     */
    static function upload_file( $name )
    {
        if( empty( $_FILES[$name] ) ){
            return false;
        }

        if ( !function_exists( 'wp_handle_upload' ) ) {
            require_once( ABSPATH . 'wp-admin/includes/file.php' );
        }

        $uploadedfile       = $_FILES[ $name ];

        $upload_overrides   = [ 'test_form' => false ];

        $movefile           = wp_handle_upload( $uploadedfile, $upload_overrides );

        if ( $movefile && !isset( $movefile[ 'error' ] ) ) {
            return $movefile;
        } else {
            wp_send_json_error( $movefile );
        }

        return true;
    }

    /**
     * Create Attachment Post
     *
     * @param $post_id
     * @param $file
     * @return int|WP_Error
     */
    static function upload_media( $post_id, $file ){
        $attach_id = wp_insert_attachment(
            [
                'post_mime_type'    => $file[ 'type' ],
                'guid'              => $file[ 'url' ],
            ],
            $file[ 'file' ],
            $post_id
        );
        require_once( ABSPATH . 'wp-admin/includes/image.php' );

        $attach_data = wp_generate_attachment_metadata( $attach_id, $file[ 'file' ] );
        wp_update_attachment_metadata( $attach_id, $attach_data );

        return $attach_id;
    }
}
