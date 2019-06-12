<?php
namespace Undefined\Core\Helpers;

/**
 * User Helper
 *
 * @name UserHelper
 * @since 1.0.0
 * @package Undefined\Core\Helpers
 */
class UserHelper
{
    protected $_user;

    public function __construct($user = false)
    {
        $this->_setUser($user);
    }

    /**
     * Get current logged user
     * @return mixed
     */
    public function getCurrentUser(){
        return $this->_user;
    }

    /**
     * Retrieve user property
     * @param $property
     * @return null
     */
    public function get($property){
        return !empty($this->_user->{$property}) ? $this->_user->{$property} : null;
    }

    /**
     * Set current user
     * @param bool $user
     */
    protected function _setUser($user = false){
        $this->_user = !empty($user) && $user instanceof \WP_User ? $user : wp_get_current_user();
    }
}

