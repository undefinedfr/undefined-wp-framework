<?php
/**
 * Global helper functions
 *
 * @name Helpers
 * @since 2.1.0
 * @package Undefined\Core
 */

if ( ! function_exists( 'app' ) ) {
	/**
	 * Get the application instance
	 *
	 * @return \Undefined\Core\App
	 */
	function app(): \Undefined\Core\App {
		return \App\App::getInstance();
	}
}
