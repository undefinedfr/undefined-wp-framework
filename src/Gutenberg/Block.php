<?php
namespace Undefined\Core\Gutenberg;

use Timber;

/**
 * Block
 *
 * @name Block
 * @since 1.0.9
 * @package Undefined\Core\Gutenberg
 */
class Block
{
    /**
     * @var $name
     */
    public $name;

    /**
     * @var $title
     */
    public $title;

    /**
     * @var $description
     */
    public $description;

    /**
     * @var $render_template
     */
    public $render_template;

    /**
     * @var $category
     */
    public $category = 'layout';

    /**
     * @var $icon
     */
    public $icon ='slides';

    /**
     * @var $mode
     */
    public $mode = 'preview';

    /**
     * @var $keywords
     */
    public $keywords = [];

    /**
     * @var $styleDependencies
     */
    public $styleDependencies = [];

    /**
     * @var $scriptDependencies
     */
    public $scriptDependencies = [];

    /**
     * Block constructor.
     */
    public function __construct($stylesheet = null)
    {
        $this->title = __($this->title);
        add_action( 'acf/init', [$this, 'registerBlock'] );
        add_action( 'enqueue_block_editor_assets', [$this, 'loadAssets'] );
    }

    /**
     * Register block
     */
    public function registerBlock()
    {
        if ( function_exists( 'acf_register_block' ) ) {

            $this->render_template = apply_filters('undfnd_gutenberg_bloc_template', (get_template_directory() . '/templates/partial/block/' . $this->name . '.twig'), $this->name);
            $this->render_callback = apply_filters('undfnd_gutenberg_bloc_callback', [$this, 'render'], $this->name);

            acf_register_block(array(
                'name'            => $this->name,
                'title'           => $this->title,
                'description'     => $this->description,
                'render_template' => $this->render_template,
                'render_callback' => $this->render_callback,
                'category'        => $this->category,
                'icon'            => $this->icon,
                'mode'            => $this->mode,
                'keywords'        => array_merge([$this->name], $this->keywords)
            ));
        }
    }

    /**
     * Render block with twig
     */
    public function render( $block )
    {
        Timber::render( $this->render_template, ['block' => $block] );
    }

    public function loadAssets()
    {
        $stylesheet = '/assets/admin/css/gutenberg/' . $this->name . '.css';
        if (file_exists(get_template_directory() . $stylesheet)) {
            wp_enqueue_style( $this->name, get_template_directory_uri() . $stylesheet, $this->styleDependencies );
        }

        $script = '/assets/admin/js/gutenberg/' . $this->name . '.js';
        if (file_exists(get_template_directory() . $script)) {
            wp_enqueue_script( $this->name, get_template_directory_uri() . $script, $this->scriptDependencies );
        }
    }
}
