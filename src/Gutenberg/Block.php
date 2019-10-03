<?php
namespace Undefined\Core\Gutenberg;

/**
 * Block
 *
 * @name Block
 * @since 1.0.3
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

    public function __construct()
    {
        $this->title = __($this->title, DOMAIN_LANG);
        add_action( 'acf/init', [$this, 'registerBlock'] );
    }

    public function registerBlock(){
       if ( function_exists( 'acf_register_block' ) ) {
           acf_register_block(array(
               'name'            => $this->name,
               'title'           => $this->title,
               'description'     => $this->description,
               'render_template' => !empty($this->render_template) ? $this->render_template : (get_template_directory() . '/templates/gutenberg/' . $this->name . '.twig'),
               'category'        => $this->category,
               'icon'            => $this->icon,
               'mode'            => $this->mode,
               'keywords'        => array_merge([$this->name], $this->keywords)
           ));
       }
    }
}