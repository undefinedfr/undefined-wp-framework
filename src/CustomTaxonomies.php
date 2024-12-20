<?php
namespace Undefined\Core;

use Undefined\Core\Loaders\Loader;

/**
 * Set Custom Taxonomies
 *
 * @name CustomTaxonomies
 * @since 1.0.0
 * @package Undefined\Core
 */
class CustomTaxonomies
{
    /**
     * @var array
     */
    protected $_taxonomies = [];

    public function __construct()
    {
        $this->_taxonomies = new Loader();
        $this->_taxonomies
            ->setType( 'Taxonomy' )
            ->setAll( get_template_directory() . '/app/Taxonomy/' );

        add_action( 'init', [ &$this, 'appCreateTaxonomies' ], 0 );
    }

    /**
     * Launch register method on custom taxonomies
     *
     * @return void
     */
    public function appCreateTaxonomies()
    {
        foreach( $this->_taxonomies->getAll() as $taxonomy => $file ) {
            if ( !class_exists( $taxonomy )
                || !method_exists( $taxonomy, 'register' ) )
                continue;

            call_user_func( [ $taxonomy, 'register' ] );

            if( method_exists( $taxonomy, 'onSaveTerm') ) {
                add_action( 'saved_' . $taxonomy::getTaxonomy(), function( $term_id, $tt_id, $update, $args ) use ( $taxonomy ) {
                    call_user_func( [ $taxonomy, 'onSaveTerm' ], $term_id, $tt_id, $update, $args );
                }, 10, 4 );
            }
        }
    }

    /**
     * Retrieve custom taxonomies
     *
     * @return array
     */
    public function getTaxonomies()
    {
        return $this->_taxonomies;
    }
}
