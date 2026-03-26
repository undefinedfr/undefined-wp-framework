<?php
namespace Undefined\Core;

use Undefined\Core\Loaders\Loader;

/**
 * Add Custom Post Types
 *
 * @name CustomPostTypes
 * @since 1.0.0
 * @update 2.0.2
 * @package Undefined\Core
 */
class CustomPostTypes {

	/**
	 * @var array
	 */
	protected $_posttypes = [];

	/**
	 * @return void
	 */
	public function __construct() {
		$this->_posttypes = new Loader();
		$this->_posttypes
			->setType( 'PostType' )
			->setAll( get_template_directory() . '/app/PostType/' );

		add_action( 'init', [ &$this, 'appCreatePosttypes' ], 0 );
	}

	/**
	 * Launch register on custom post type
	 *
	 * @return void
	 */
	public function appCreatePosttypes() {
		foreach ( $this->_posttypes->getAll() as $post_type => $file ) {
			if ( ! class_exists( $post_type )
				|| ! method_exists( $post_type, 'register' ) ) {
				continue;
			}

			call_user_func( [ $post_type, 'register' ] );

			add_filter(
				'timber/post/classmap',
				function ( $classmap ) use ( $post_type ) {
					$classmap[ call_user_func( [ $post_type, 'getPostType' ] ) ] = $post_type;

					return $classmap;
				}
			);

			$ACFexist = class_exists( 'ACF' );
			add_action(
				( $ACFexist ? 'acf/' : '' ) . 'save_post',
				function ( $post_id ) use ( $post_type ) {
					if ( empty( $post_type )
						|| call_user_func( [ $post_type, 'getPostType' ] ) != get_post_type( $post_id )
						|| ! method_exists( $post_type, 'onSavePost' ) ) {
						return;
					}

					call_user_func( [ $post_type, 'onSavePost' ], $post_id );
				},
				apply_filters( 'undfnd_cpt_save_post_priority', 10, $post_type::getPostType() ),
				apply_filters( 'undfnd_cpt_save_post_accepted_args', $ACFexist ? 1 : 3, $post_type::getPostType() )
			);
		}
	}

	/**
	 * Retrieve custom post_types
	 *
	 * @return array
	 */
	public function getPostTypes() {
		return $this->_posttypes;
	}
}
