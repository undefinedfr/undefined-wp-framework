<?php
namespace Undefined\Core;

use Undefined\Core\Helpers\HookHelper;

/**
 * Set Filters hook
 *
 * @name Filters
 * @since 1.0.0
 * @package Undefined\Core
 */
class Filters extends HookHelper
{
    /**
     * @var array
     */
    protected $_hooks = [];

    /**
     * @return void
     */
    public function __construct()
    {
        $this->setFiltersList( $this, $this->_hooks );
    }

    /**
     * Retrieve filters hooks
     *
     * @return array
     */
    public function getHooks()
    {
        return $this->_hooks;
    }

}

