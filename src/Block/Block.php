<?php
namespace Undefined\Core\Block;

use Extended\ACF\Location;
use Timber;

/**
 * Block
 *
 * @name Block
 * @since 1.0.9
 * @update 2.0.2
 * @package Undefined\Core\Block
 */
class Block
{
    /**
     * @var string $name
     */
    public $name;

    /**
     * @var string $title
     */
    public $title;

    /**
     * @var string $description
     */
    public $description;

    /**
     * @var string $render_template
     */
    public $render_template;

    /**
     * @var string $category
     */
    public $category = 'layout';

    /**
     * @var string $icon
     */
    public $icon ='slides';

    /**
     * @var string $mode
     */
    public $mode = 'preview';

    /**
     * @var array $keywords
     */
    public $keywords = [];

    /**
     * @var array $styleDependencies
     */
    public $styleDependencies = [];

    /**
     * @var array $scriptDependencies
     */
    public $scriptDependencies = [];

    /**
     * @var string $groupField
     */
    public $groupField = [];

    /**
     * Block constructor.
     *
     * @return void
     */
    public function __construct()
    {
        $this->title = __( $this->title );

        $iconPath = apply_filters( 'undfnd_gutenberg_bloc_icon_path', ( get_template_directory() . '/public/assets/images/icons/gutenberg-icons/' . $this->name . '.svg' ), $this );

        // Load icon if exists
        if( file_exists( $iconPath ) ) {
            $this->icon = file_get_contents( $iconPath );
        }

        // Create ACF group field if exists
        $this->_setGroupField();

        if( !empty( $this->groupField['fields'] ) ) {
            add_action('acf/init', [$this, 'registerGroupField'] );
        }

        add_action( 'init', [$this, 'registerBlock'] );
        add_action( 'enqueue_block_editor_assets', [$this, 'loadAssets'] );
    }

    /**
     * Register block
     *
     * @return void
     */
    public function registerBlock()
    {
        if ( function_exists( 'acf_register_block' ) ) {

            $this->render_template = apply_filters( 'undfnd_gutenberg_bloc_template' , ( get_template_directory() . '/templates/partial/block/' . $this->name . '.twig' ), $this->name );

            acf_register_block( [
                'name'            => $this->name,
                'title'           => $this->title,
                'description'     => $this->description,
                'render_template' => $this->render_template,
                'render_callback' => [$this, 'render'],
                'category'        => $this->category,
                'icon'            => $this->icon,
                'mode'            => $this->mode,
                'keywords'        => array_merge( [$this->name], $this->keywords ),
                'example'  => [
                    'attributes' => [
                        'mode' => 'preview',
                    ]
                ]
            ] );
        }
    }

    /**
     * Render block with twig
     *
     * @param $block
     * @param $content
     * @param $is_preview
     * @param $post_id
     * @return void
     */
    public function render( $block, $content = '', $is_preview = false, $post_id = 0 )
    {
        $block = $this->_prepareBlock( $block, $post_id );

        $this->_render( $block );
    }

    /**
     * Load assets with block
     *
     * @return void
     */
    public function loadAssets()
    {
        $stylesheet = '/assets/admin/css/gutenberg/' . $this->name . '.css';
        if ( file_exists( get_template_directory() . $stylesheet ) ) {
            wp_enqueue_style( $this->name, get_template_directory_uri() . $stylesheet, $this->styleDependencies );
        }

        $script = '/assets/admin/js/gutenberg/' . $this->name . '.js';
        if ( file_exists( get_template_directory() . $script ) ) {
            wp_enqueue_script( $this->name, get_template_directory_uri() . $script, $this->scriptDependencies );
        }
    }

    /**
     * Register ACF Group Field
     *
     * @return void
     */
    public function registerGroupField()
    {
        register_extended_field_group( $this->groupField ?: [] );
    }

    /**
     * Set ACF Group Field
     * @return void
     */
    protected function _setGroupField()
    {
        if( empty( $this->groupField['title'] ) ) {
            $this->groupField['title'] = '[Bloc] ' . $this->title;
        }

        if( empty( $this->groupField['location'] ) ) {
            $this->groupField['location'] = [
                Location::where( 'block', 'acf/' . $this->name )
            ];
        }
    }

    /**
     * On admin update, block data is send to template, not all block
     *
     * @param $block
     * @return mixed
     */
    protected function _prepareBlock( $block, $post_id = 0 )
    {
        $keys = array_keys( $block['data'] );

        if( strpos( reset( $keys ), 'field_' ) == false ) {
            $data           = $block['data'];
            $block['data']  = [];

            foreach( $data as $key => $field ) {
                $acfObj = get_field_object( $key );

                if ( !empty( $acfObj ) ) {
                    if( $acfObj['type'] == 'clone' ) {
                        $block['data'] = array_merge( $block['data'], $acfObj['value'] );
                    } else {
                        $block['data'][$acfObj['name']] = $acfObj['value'];
                    }
                }
            }
        }

        return $block;
    }

    /**
     * Render block with Timber
     *
     * @param $block
     * @return mixed
     */
    protected function _render( $block )
    {
        if( empty( array_filter( $block['data'] ) )
            && is_admin()
            && file_exists( apply_filters( 'undfnd_gutenberg_bloc_empty_template', ( get_template_directory() . '/templates/layout/gutenberg-preview.twig' ), $block, $this ) ) ) {

            Timber::render( 'layout/gutenberg-preview.twig', [ 'image' => $this->name ] );
        } else {
            Timber::render( $this->render_template, [ 'block' => $block ] );
        }
    }
}
