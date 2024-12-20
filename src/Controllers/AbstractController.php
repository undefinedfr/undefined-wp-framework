<?php
namespace Undefined\Core\Controllers;

use Timber;

/**
 * Global Controller
 *
 * @name AbstractController
 * @since 1.0.0
 * @package Undefined\Core\Controllers
 */
class AbstractController
{
    /**
     * @var string
     */
    public      $context;

    /**
     * @var array
     */
    public      $data       = [];

    /**
     * @var array
     */
    protected   $_options   = ['home', 'siteurl', 'posts_per_page', 'page_on_front'];

    /**
     * @var string
     */
    protected   $_action;

    /**
     * @var string
     */
    protected   $_section;

    /**
     * @var string
     */
    protected   $_title;

    /**
     * @var string
     */
    protected   $_controllerName;

    /**
     * @var object
     */
    protected   $_queriedObject;

    /**
     * @return void
     */
    public function __construct( $section = null )
    {
        if( empty( $_SESSION['app'] ) ) {
            $_SESSION['app'] = [
                'notices' => []
            ];
        }

        $this->_setName();
        $this->_setSection( $section );
        $this->_setAction();

        $this->_queriedObject = get_queried_object();

        $actionName = $this->_action;
        if( !empty( $_POST ) && method_exists( $this, $actionName . 'PostAction' ) ) {
            $actionName .= 'Post';
        }
        $actionName .= 'Action';

        if( method_exists( $this, $actionName ) )
            $this->$actionName();

        add_action( apply_filters( 'undfnd_controller_wp_title_action', 'wp_title' ), [$this, 'setCustomWpTitle']);
        add_action( 'shutdown', [$this, 'unsetNotices']);
    }

    /**
     * Detect if param route is current route
     *
     * @param $route
     * @return bool
     */
    public function isCurrentRoute( $route = null )
    {
        return $route == ( $this->getName() . '/' . $this->getSection() );
    }

    /**
     * Get Controller Name
     *
     * @return mixed
     */
    public function getName()
    {
        return $this->_controllerName;
    }

    /**
     * Get Section Name
     *
     * @return mixed
     */
    public function getSection()
    {
        return $this->_section;
    }

    /**
     * Get Action Name
     *
     * @return mixed
     */
    public function getAction()
    {
        return $this->_action;
    }

    /**
     * Get Session Notices
     *
     * @return array
     */
    public function getNotices()
    {
        return !empty( $_SESSION['app']['notices'] ) ? $_SESSION['app']['notices'] : [];
    }

    /**
     * Unset Session Notices
     *
     * @return void
     */
    public function unsetNotices()
    {
        unset( $_SESSION['app']['notices'] );
    }

    /**
     * Set Wp Title
     *
     * @return mixed
     */
    public function setCustomWpTitle()
    {
        return $this->_title;
    }

    /**
     * Get url
     *
     * @param $url
     */
    public function getUrl( $url = null )
    {
        return !preg_match( '#^(http|https):#', $url ) ? get_site_url( null, $url ) : $url;
    }

    /**
     * Render Timber views
     *
     * @param $data
     * @return void
     */
    public function render($data = [])
    {
        $this->context              = Timber::context();
        $this->context['menu']      = Timber::get_menu();
        $this->context['options']   = $this->_getOptions( apply_filters( 'timber_default_options', $this->_options ) );

        $this->_setContext( $data );

        $this->_render();
    }

    /**
     * Set section
     *
     * @param (string|null) $section
     * @return void
     */
    protected function _setSection( $section = null )
    {
        $this->_section = $section;
    }

    /**
     * Set Action
     *
     * @param $action
     */
    protected function _setAction()
    {
        $formatedSection = empty( $this->_section ) ? '' : str_replace( '-', '', $this->_section );
        $lastSection = explode( '/', $formatedSection );
        $this->_action = !empty( $this->_section ) ? array_pop( $lastSection ) : 'index';
    }

    /**
     * Set Controller Name
     *
     * @param $controllerName
     */
    protected function _setName()
    {
        $this->_controllerName = strtolower( preg_replace( '#([a-zA-Z0-9]{1,})Controller$#', '$1' , get_class( $this ) ) );
    }

    /**
     * Set Controller Title
     *
     * @param null $controllerName
     */
    protected function _setTitle( $title )
    {
        $this->_title = __( $title );
    }

    /**
     * Redirect to specific url
     *
     * @param $url
     */
    protected function _redirect( $url = null )
    {
        wp_redirect( $this->getUrl( $url ) );
        exit;
    }

    /**
     * Add Notice
     *
     * @param string $type
     * @param null $message
     * @param bool $dismissible
     */
    protected function _addNotice( $type = 'success', $message = null, $dismissible = false, $auto_dismissible = false )
    {
        if( empty( $_SESSION['app']['notices'] ) )
            $_SESSION['app']['notices'] = [];

        $_SESSION['app']['notices'][] = [
            'type'              => $type,
            'message'           => __( $message ),
            'dismissible'       => $dismissible,
            'auto_dismissible'  => $auto_dismissible,
        ];
    }

    /**
     * Set Timber context
     *
     * @param $data
     * @return void
     */
    protected function _setContext( $data = [] )
    {
        $this->context[ 'id' ] = $this->_controllerName . '/' . $this->_action;

        if( is_single() ) {
            $this->context[ 'post' ]    = Timber::get_posts();
        }
        else if( is_archive() ) {
            $this->context[ 'posts' ]   = Timber::get_posts();
        }
        else if( is_page() ) {
            $this->context[ 'page' ]    = Timber::get_posts();
        }

        $this->context = array_merge( $this->context, apply_filters( 'timber_global_context_data', $this->data ) );

        $this->context = array_merge( $data, $this->context );

    }

    /**
     * Set data to Timber context
     *
     * @param array $data
     */
    protected function _setData( $data = [], $merge = true )
    {
        $this->data = !$merge ? $data : array_merge( $this->data, $data );
    }

    /**
     * Render Timber views
     *
     * @return void
     */
    protected function _render()
    {
        $templates = [];
        $template_name = $this->_controllerName;

        // Action
        if( ( !empty( $this->_action ) && $this->_action != 'index' ) ) {
            $template_name .= '-' . $this->_action;
        }

        // Sub section
        if( !empty($this->_section )
            && $this->_section != 'index'
            && str_replace( '-', '', $this->_section ) != $this->_action ) {
            $template_name .= '-' . $this->_section;
        }

        $post = !empty( $this->context['post'] ) ? $this->context['post'] : false;

        // Custom post_type
        if( !empty( $post ) && ( $post instanceof Timber\Post ) && $this->_controllerName == 'single' ) {
            $this->context['post_type'] = $post->post_type;
            $templates[] = $template_name . '-' . $post->ID . '.twig';
            $templates[] = $template_name . '-' . $post->post_type . '.twig';
        }

        // Search
        if( is_search() ) {
            // Is paged page
            if( is_paged() ){
                $templates[] = 'search-paged.twig';
            }

            $templates[] = 'search.twig';
        }

        // Custom post_type list
        if( is_archive() && !is_tax() && !is_category() && !is_author() ) {
            $this->context['post_type'] = $this->_queriedObject->slug;

            // Is paged page
            if( is_paged() ){
                $templates[] = 'archive-paged.twig';
                $templates[] = 'archive-' . $this->_queriedObject->slug . '-paged.twig';
            }

            $templates[] = 'archive-' . $this->_queriedObject->slug . '.twig';
            $templates[] = $template_name . '-' . $this->_queriedObject->slug . '.twig';
            $templates[] = 'archive.twig';
        }

        // Taxonomy template
        if( is_tax() || is_category() || is_tag() ) {
            $this->context['taxonomy'] = $this->_queriedObject->taxonomy;

            // Is category
            if( is_category() ) {

                // Is paged page
                if( is_paged() ) {
                    $templates[] = 'category-paged.twig';
                    $templates[] = 'category-' . $this->_queriedObject->slug . '-paged.twig';
                    $templates[] = 'category-' . $this->_queriedObject->taxonomy . '-' . $this->_queriedObject->slug . '-paged.twig';
                }

                $templates[] = 'category-' . $this->_queriedObject->taxonomy . '.twig';
                $templates[] = 'category-' . $this->_queriedObject->taxonomy . '-' . $this->_queriedObject->slug . '.twig';
                $templates[] = $template_name . '-' . $this->_queriedObject->slug . '.twig';
                $templates[] = 'category.twig';
            }

            // Is paged page
            if( is_paged() ){
                $templates[] = 'taxonomy-paged.twig';
                $templates[] = 'taxonomy-' . $this->_queriedObject->slug . '-paged.twig';
                $templates[] = 'taxonomy-' . $this->_queriedObject->taxonomy . '-' . $this->_queriedObject->slug . '-paged.twig';
            }

            $templates[] = 'taxonomy-' . $this->_queriedObject->taxonomy . '.twig';
            $templates[] = 'taxonomy-' . $this->_queriedObject->taxonomy . '-' . $this->_queriedObject->slug . '.twig';
            $templates[] = $template_name . '-' . $this->_queriedObject->slug . '.twig';
            $templates[] = 'taxonomy.twig';
        }

        // Page template
        if( !empty( $post ) && ( $post instanceof Timber\Post ) && $post->post_type == 'page' && get_page_template_slug() ) {
            $template = rtrim( get_page_template_slug(), '.php' );
            $templates[] = $template_name . '-' . $template . '.twig';
            $templates[] = $template . '.twig';
        }

        // Author
        if( is_author() ) {

            // Is paged page
            if( is_paged() ) {
                $templates[] = 'author-paged.twig';
                $templates[] = 'author-' . $this->_queriedObject->user_login . '-paged.twig';
                $templates[] = 'author-' . $this->_queriedObject->ID . '-paged.twig';
            }

            $templates[] = 'author-' . $this->_queriedObject->user_login . '.twig';
            $templates[] = 'author-' . $this->_queriedObject->ID . '.twig';

            $templates[] = 'author.twig';
        }

        $templates[] = $template_name . '.twig';

        // Protected posts
        if( !empty( $post ) && ( $post instanceof Timber\Post ) && post_password_required( $post->ID ) ) {
            foreach($templates as &$template) {
                $template = str_replace( '.twig', '-password.twig', $template );
            }
        }

        Timber::render( $templates, $this->context );
    }

    /**
     * Get options from wp_options table
     *
     * @param array $keys
     * @return array|mixed
     */
    private function _getOptions($keys = [])
    {
        $prefixLang = '';
        if(defined('ICL_LANGUAGE_CODE')
            && !is_plugin_active('polylang/polylang.php')
            && !is_plugin_active('polylang-pro/polylang.php')) {

            global $sitepress;
            $prefixLang = ( ICL_LANGUAGE_CODE != $sitepress->get_default_language() ? ICL_LANGUAGE_CODE . '_' : '' );
        }

        if( false === ( $meta = get_transient( 'value' ) ) ) {

            // Globals.
            global $wpdb;

            // Vars.
            $meta = [];
            $query = "SELECT * FROM $wpdb->options WHERE 1 = 1 AND (";
            foreach ( $keys as $index => $key ) {
                $meta[$key] = false;
                $query .= $index > 0 ? ' OR ' : '';
                $query .= $wpdb->prepare( "option_name = %s", $key );
                $query .= $wpdb->prepare( "OR option_name LIKE '%s'", 'options_' . $prefixLang . '%' . $key );
            }
            $query .= ")";


            // Query database for results.
            $rows = $wpdb->get_results( $query, ARRAY_A );

            foreach ( $rows as $row ) {
                if ( empty( $meta[ $row[ 'option_name' ] ] ) )
                    $meta[ preg_replace( '#options_' . $prefixLang . '(.?)#', '$1', $row['option_name'] ) ] = $row['option_value'];
            }

            set_transient( $prefixLang . 'options_cache', $meta, HOUR_IN_SECONDS );
        }

        return $meta;
    }

}
