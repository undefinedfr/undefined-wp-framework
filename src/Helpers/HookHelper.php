<?php
namespace Undefined\Core\Helpers;

/**
 * Hooks Helper
 *
 * @name HookHelper
 * @since 1.0.3
 * @package Undefined\Core\Helpers
 */
class HookHelper
{
    /**
     * Set Filters list
     * @param $class
     * @param $hooks
     */
    protected function setFiltersList($class, $hooks){
        foreach($hooks as $functionName => $hook){
            if(empty($hook['remove_on_admin']) || !is_admin()){
                if(is_array($hook)){
                    add_filter($hook['hook'], [&$class, 'theme_' . $functionName], $hook['priority'], $hook['accepted_args']);
                } else {
                    add_filter($hook, [&$class, 'theme_' . $functionName]);
                }
            }
        }
    }
    /**
     * Set Actions list
     * @param $class
     * @param $hooks
     */
    protected function setActionsList($class, $hooks){
        foreach($hooks as $functionName => $hook){
            if(empty($hook['remove_on_admin']) || !is_admin()){
                if(is_array($hook)){
                    add_action($hook['hook'], [&$class, 'theme_' . $functionName], $hook['priority'], $hook['accepted_args']);
                } else {
                    add_action($hook, [&$class, 'theme_' . $functionName]);
                }
            }
        }
    }
}