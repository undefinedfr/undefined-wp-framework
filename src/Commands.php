<?php
namespace Undefined\Core;

use Undefined\Core\Loaders\Loader;

/**
 * Add Custom WP CLI Commands
 *
 * @name Commands
 * @since 1.0.0
 * @package Undefined\Core
 */
class Commands
{
    /**
     * @var array
     */
    protected $_commands = [];

    /**
     * @return void
     */
    public function __construct()
    {
        if( !defined('WP_CLI') )
            return;

        $this->_commands = new Loader();
        $this->_commands
            ->setType( 'Command' )
            ->setAll( get_template_directory() . '/app/Command/' )
            ->loadAll();

        foreach ( $this->_commands->getAll() as $className => $class ) {
            \WP_CLI::add_command( $class->name, 'App\Command\\' . $className );
        }
    }

    /**
     * Retrieve custom commands
     * @return array
     */
    public function getCommands()
    {
        return $this->_commands;
    }
}
