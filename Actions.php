<?php
namespace Undefined\Core;

/**
 * Set Actions hook
 *
 * @name Actions
 * @since 1.0.0
 * @package Undefined\Core
 */
class Actions
{
    protected $_hooks = [];

    public function __construct()
    {
        foreach($this->_hooks as $functionName => $hook){
            if(is_array($hook)){
                add_action($hook['hook'], array(&$this, 'theme_' . $functionName), $hook['priority'], $hook['accepted_args']);
            } else {
                add_action($hook, array(&$this, 'theme_' . $functionName));
            }
        }
    }

    /**
     * Retrieve actions hooks
     * @return array
     */
    public function getHooks()
    {
        return $this->_hooks;
    }

}

