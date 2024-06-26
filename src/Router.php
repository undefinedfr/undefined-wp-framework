<?php
namespace Undefined\Core;

/**
 * @name Router
 * @package Undefined\Core
 * @since 1.0.0
 * @update 2.0.0
 */
class Router
{
    /**
     * @var array
     */
    protected $_rules = [];

    public function __construct()
    {
        add_action( 'init', [&$this, 'customRewriteRule'], 10, 0 );
        add_filter( 'query_vars', [&$this, 'registerQueryVars'] );
        add_filter( 'template_include', [&$this, 'instanceController'], 99, 1);
    }

    /**
     * * Instance Controller
     *
     * @param $template
     * @return mixed|void
     */
    public function instanceController( $template )
    {
        if( is_admin() )
            return $template;

        global $undfdApp;

        $undfd_template = get_query_var('undfd_template');
        $undfd_section  = get_query_var('undfd_section');

        if ( !$undfd_template )
            return $template;

        // Init Controller
        $controllerName = $this->_getControllerName( $undfd_template );
        if( file_exists( __PROJECTDIR__ . 'Controllers/' . $controllerName . '.php' ) ) {
            require_once( __PROJECTDIR__ . 'Controllers/' . $controllerName . '.php' );
            $undfdApp->setController( new $controllerName( $undfd_section ) );
            return;
        }

        return $template;
    }

    /**
     * Helper toUpper
     *
     * @param $matches
     * @return string
     */
    public function toUpper( $matches )
    {
        return strtoupper( $matches[1] );
    }

    /**
     * Set custom rewrite rules
     */
    public function customRewriteRule()
    {
        foreach( $this->_rules as $rule ){
            add_rewrite_rule( $rule['regex'], $rule['redirect'], 'top' );
        }
    }

    /**
     * Add custom query vars
     *
     * @param $vars
     * @return array
     */
    public function registerQueryVars( $vars )
    {
        $vars[] = 'undfd_template';
        $vars[] = 'undfd_section';

        return $vars;
    }

    /**
     * Get rule by slug
     *
     * @param $rule
     * @return mixed|null
     */
    public function getRule( $rule )
    {
        return $this->getRules()[$rule] ?? null;
    }

    /**
     * Get All Rules
     *
     * @return array
     */
    public function getRules()
    {
        return $this->_rules;
    }

    /**
     * Add rule
     *
     * @param $slug
     * @param null $section
     */
    public function addRule( $slug, $section = null, $params = [] )
    {
        if (!function_exists('is_plugin_active')) {
            include_once(ABSPATH . 'wp-admin/includes/plugin.php');
        }

        $regex = '^' . $slug;
        $regex .= ( !empty( $section ) ? '/' . $section : '' );
        if( $params ) {
            foreach( $params as $param ){
                $regex .= '/' . $param;
            }
        }
        $regex .= '/?$';

        $redirect = self::add_query_arg('undfd_template', $slug, 'index.php');
        $redirect = self::add_query_arg('undfd_section', urlencode( !empty( $section ) ? $section : 'index' ), $redirect);

        if( $params ) {
            $i = 1;
            foreach ( $params as $key => $param ) {
                $redirect = self::add_query_arg( $key, '$matches[' . $i . ']', $redirect );
                $i++;
            }
        }

        // WPML Support
        if ( defined( 'ICL_LANGUAGE_CODE' )
            && !is_plugin_active( 'polylang/polylang.php' )
            && !is_plugin_active( 'polylang-pro/polylang.php' ) ) {

            $defaultLanguage    = apply_filters( 'wpml_default_language', null );
            $langs              = wpml_active_languages();
            if( $langs ) {
                foreach ( $langs as $lang ) {
                    if ( $lang['language_code'] != $defaultLanguage ) {
                        $this->_rules[$slug . ($section ? '-' . $section : '') . ( !empty( $params ) ? '-' . implode('-', array_keys( $params ) ) : '' ) . '-' . $lang['language_code']] = [
                            'regex'     => str_replace( '^', '^' . $lang['language_code'] . '/', $regex ),
                            'redirect'  => self::add_query_arg( 'lang', $lang['language_code'], $redirect ),
                        ];
                    }
                }
            }

            $redirect = self::add_query_arg( 'lang', $defaultLanguage, $redirect );
        }

        // Polylang Support
        if ( is_plugin_active( 'polylang/polylang.php' )
            || is_plugin_active( 'polylang-pro/polylang.php' ) ) {

            $defaultLanguage    = pll_default_language( 'slug' );
            $langs              = pll_languages_list( [ 'fields' => 'slug' ] );
            $polylang           = get_option( 'polylang' );
            foreach ( $langs as $lang ) {
                if ( $lang != $defaultLanguage || $polylang['hide_default'] == 0 ) {
                    $this->_rules[$slug . ($section ? '-' . $section : '') . ( !empty( $params ) ? '-' . implode('-', array_keys( $params ) ) : '' ) . '-' . $lang] = [
                        'regex'     => str_replace( '^', '^' . $lang . '/', $regex ),
                        'redirect'  => self::add_query_arg( 'lang', $lang, $redirect ),
                    ];
                }
            }
            $redirect = self::add_query_arg( 'lang', $defaultLanguage, $redirect );
        }

        $this->_rules[$slug . ( $section ? '-' . $section : '' ) . ( !empty( $params ) ? '-' . implode( '-', array_keys( $params ) ) : '' )] =  [
            'regex'     => $regex,
            'redirect'  => $redirect,
        ];
    }

    /**
     * Hard coded add_query_arg to remove urlencode
     * @param ...$args
     * @return array|string|string[]
     */
    protected static function add_query_arg( ...$args ) {
        if ( is_array( $args[0] ) ) {
            if ( count( $args ) < 2 || false === $args[1] ) {
                $uri = $_SERVER['REQUEST_URI'];
            } else {
                $uri = $args[1];
            }
        } else {
            if ( count( $args ) < 3 || false === $args[2] ) {
                $uri = $_SERVER['REQUEST_URI'];
            } else {
                $uri = $args[2];
            }
        }

        $frag = strstr( $uri, '#' );
        if ( $frag ) {
            $uri = substr( $uri, 0, -strlen( $frag ) );
        } else {
            $frag = '';
        }

        if ( 0 === stripos( $uri, 'http://' ) ) {
            $protocol = 'http://';
            $uri      = substr( $uri, 7 );
        } elseif ( 0 === stripos( $uri, 'https://' ) ) {
            $protocol = 'https://';
            $uri      = substr( $uri, 8 );
        } else {
            $protocol = '';
        }

        if ( str_contains( $uri, '?' ) ) {
            list( $base, $query ) = explode( '?', $uri, 2 );
            $base                .= '?';
        } elseif ( $protocol || ! str_contains( $uri, '=' ) ) {
            $base  = $uri . '?';
            $query = '';
        } else {
            $base  = '';
            $query = $uri;
        }

        wp_parse_str( $query, $qs );
        if ( is_array( $args[0] ) ) {
            foreach ( $args[0] as $k => $v ) {
                $qs[ $k ] = $v;
            }
        } else {
            $qs[ $args[0] ] = $args[1];
        }

        foreach ( $qs as $k => $v ) {
            if ( false === $v ) {
                unset( $qs[ $k ] );
            }
        }

        $ret = build_query( $qs );
        $ret = trim( $ret, '?' );
        $ret = preg_replace( '#=(&|$)#', '$1', $ret );
        $ret = $protocol . $base . $ret . $frag;
        $ret = rtrim( $ret, '?' );
        $ret = str_replace( '?#', '#', $ret );
        return $ret;
    }


    /**
     * Get Correct Controller Name
     *
     * @param $template
     * @return string
     */
    private function _getControllerName( $template )
    {
        return ucfirst( preg_replace_callback( '/[-_](.)/', [$this, 'toUpper'], $template ) ) . 'Controller';
    }
}

