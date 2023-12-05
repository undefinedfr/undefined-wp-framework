<?php
namespace Undefined\Core;

use Undefined\Core\Loaders\Loader;

/**
 * Add Custom Gutenberg Blocks
 *
 * @name Blocks
 * @since 1.0.0
 * @package Undefined\Core
 */
class Blocks
{
    /**
     * @var array
     */
    protected $_blocks = [];

    /**
     * @return void
     */
    public function __construct()
    {
        $this->_blocks = new Loader();
        $this->_blocks
            ->setType( 'Block' )
            ->setAll( get_template_directory() . '/app/Block/' )
            ->loadAll();
    }

    /**
     * Retrieve custom post_types
     * @return array
     */
    public function getBlocks()
    {
        return $this->_blocks;
    }
}
