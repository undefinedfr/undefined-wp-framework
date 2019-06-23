<?php
namespace Undefined\Core;

use Undefined\Core\Helpers\HookHelper;

/**
 * Set Actions hook
 *
 * @name Actions
 * @since 1.0.0
 * @package Undefined\Core
 */
class Actions extends HookHelper
{
    protected $_hooks = [];

    public function __construct()
    {
        $this->setActionsList($this, $this->_hooks);
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

