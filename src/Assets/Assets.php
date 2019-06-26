<?php
namespace Undefined\Core\Assets;

/**
 * Add Scripts and Styles
 *
 * @name Assets
 * @since 1.0.0
 * @package Undefined\Core\Assets
 */
class Assets
{
    private $_assetsPath;
    private $_jsPath;
    protected $_scripts = [
        'libs' => [
            'handle' => 'lib',
            'filename' => 'lib.js',
            'deps' => 'jquery',
            'version' => '1.0',
            'infooter' =>  false
        ],
        'scripts' => [
            'handle' => 'scripts',
            'filename' => 'scripts.js',
            'deps' => 'jquery',
            'version' => '1.0',
            'infooter' =>  true
        ]
    ];
    protected $_styles = [
        'bootstrap' => [
            'handle' => 'bootstrap',
            'filename' => 'bootstrap.css',
            'deps' => [],
            'version' => '1.0',
        ],
        'theme' => [
            'handle' => 'theme',
            'filename' => 'theme.css',
            'deps' => [],
            'version' => '1.0',
        ],
    ];

    public function __construct()
    {
        $this->_assetsPath  = get_stylesheet_directory_uri() . '/assets/css/';
        $this->_jsPath      = get_stylesheet_directory_uri() . '/assets/dist/';

        $this->_scripts['scripts']['args'] = array(
            'site_url' => get_site_url(),
            'ajax_url' => admin_url('admin-ajax.php'),
            'template_url' => get_template_directory_uri() . 'tpl',
            'ip' => $_SERVER['REMOTE_ADDR'],
            'admin_user' => current_user_can('administrator'),
            'ajax_nonce' => wp_create_nonce('undefined_ajax_nonce'),
        );

        add_action( 'wp_enqueue_scripts', array(&$this, 'app_scripts_init') );
        add_action( 'wp_enqueue_scripts', array(&$this, 'app_styles_init') );
    }

    public function app_scripts_init()
    {
        if ( !is_admin() ) {
            wp_enqueue_script( 'jquery' );

            foreach ($this->_scripts as $scripts) {
                wp_enqueue_script( $scripts['handle'], $this->_jsPath . $scripts['filename'], $scripts['deps'], $scripts['version'], !empty($scripts['infooter']) );

                if(!empty($scripts['args'])){
                    wp_localize_script( $scripts['handle'], 'args', $scripts['args']);
                }
            }
        }
    }

    public function app_styles_init()
    {
        foreach ($this->_styles as $styles) {
            wp_enqueue_style( $styles['handle'], $this->_assetsPath . $styles['filename'],  $styles['deps'], $styles['version'] );
        }
    }

    public function getStyles(){
        return $this->_styles;
    }

    public function getScripts(){
        return $this->_scripts;
    }
}