<?php
namespace Undefined\Core;

/**
 * Set Filters hook
 *
 * @name Filters
 * @since 1.0.0
 * @package Undefined\Core
 */
class Filters
{
    protected $_hooks = [];

    public function __construct()
    {
        foreach($this->_hooks as $functionName => $hook){
            if(is_array($hook)){
                add_filter($hook['hook'], array(&$this, 'theme_' . $functionName), $hook['priority'], $hook['accepted_args']);
            } else {
                add_filter($hook, array(&$this, 'theme_' . $functionName));
            }
        }
    }

    /**
     * Retrieve filters hooks
     * @return array
     */
    public function getHooks(){
        return $this->_hooks;
    }

}

