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
    /**
     * @var string
     */
    protected $_distPath;

    /**
     * @var array[]
     */
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

    /**
     * @var array[]
     */
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

    /**
     * @retrun void
     */
    public function __construct()
    {
        $this->_distPath    = get_stylesheet_directory_uri() . '/public/assets/';

        $this->_scripts['scripts']['args'] = [
            'site_url'      => get_site_url(),
            'ajax_url'      => admin_url( 'admin-ajax.php' ),
            'template_url'  => get_template_directory_uri() . 'tpl',
            'ip'            => $_SERVER['REMOTE_ADDR'],
            'admin_user'    => current_user_can( 'administrator' ),
            'ajax_nonce'    => wp_create_nonce( 'undefined_ajax_nonce' ),
        ];

        add_action( 'wp_enqueue_scripts', [ &$this, 'appScriptsInit' ] );
        add_action( 'wp_enqueue_scripts', [ &$this, 'appStylesInit' ] );
    }

    /**
     * Enqueue scripts
     *
     * @return void
     */
    public function appScriptsInit()
    {
        if ( !is_admin() ) {
            wp_enqueue_script( 'jquery' );

            foreach ( $this->_scripts as $scripts ) {
                $assetHash = $this->_getPathAssetHash( $scripts['filename'] );

                wp_enqueue_script( $scripts['handle'], $this->_distPath . $assetHash, $scripts['deps'], $scripts['version'], !empty( $scripts['infooter'] ) );

                if( !empty( $scripts['args'] ) ) {
                    wp_localize_script( $scripts['handle'], 'args', $scripts['args'] );
                }
            }
        }
    }

    /**
     * Enqueue styles
     *
     * @return void
     */
    public function appStylesInit()
    {
        foreach ( $this->_styles as $styles ) {
            $assetHash = $this->_getPathAssetHash( $styles['filename'] );
            wp_enqueue_style( $styles['handle'], $this->_distPath . $assetHash,  $styles['deps'], $styles['version'] );
        }
    }

    /**
     * Get styles
     *
     * @return array[]
     */
    public function getStyles()
    {
        return $this->_styles;
    }

    /**
     * Get scripts
     *
     * @return array[]
     */
    public function getScripts()
    {
        return $this->_scripts;
    }

    /**
     * Get path assets hash
     *
     * @param $asset
     * @return mixed|string
     */
    private function _getPathAssetHash( $asset )
    {
        $map = get_stylesheet_directory() . '/public/assets/hash.json';

        $hash = file_exists( $map ) ? json_decode( file_get_contents( $map ), true ) : [];

        if ( array_key_exists( $asset, $hash ) ) {
            return $hash[$asset];
        }

        $extension = explode( '.', $asset );
        $extension = end( $extension );

        return $extension . '/' . $asset;
    }
}
