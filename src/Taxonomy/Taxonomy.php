<?php
namespace Undefined\Core\Taxonomy;

use Timber\Term as TimberTerm;

/**
 * Taxonomy Class
 * @since 2.0.0
 * @package Undefined\Core\Taxonomy
 */
class Taxonomy extends TimberTerm
{
    /**
     * Return the key used to register the taxonomy with WordPress
     * First parameter of the `register_taxonomy` function:
     * https://codex.wordpress.org/Function_Reference/register_taxonomy
     *
     * @return string
     */
    public static function getTaxonomy()
    {
        return 'category';
    }

    /**
     * Return the config to use to register the taxonomy type with WordPress
     * Second parameter of the `register_taxonomy` function:
     * https://codex.wordpress.org/Function_Reference/register_taxonomy
     *
     * @return array|null
     */
    protected static function getTaxonomyConfig()
    {
        return [];
    }

    /**
     * Register this PostType with WordPress
     *
     * @return void
     */
    public static function register()
    {
        $taxonomy   = static::getTaxonomy();
        $config     = static::getTaxonomyConfig();

        if ( empty( $taxonomy ) || ( $taxonomy === 'category' || $taxonomy === 'post_tag' ) ) {
            throw new \Exception( 'Taxonomy not set (please add getTaxonomy method)' );
        }

        $config = wp_parse_args( $config, [
            'hierarchical'      => true,
            'show_ui'           => true,
            'show_in_rest'      => true,
            'query_var'         => true,
            'feminin'           => false,
            'show_admin_column' => true,
            'pluriel'           => ucfirst( $taxonomy ) . ( substr( $taxonomy, -1 ) != 's' ?  's' : '' ),
            'name'              => ucfirst( $taxonomy ),
            'post_types'        => [ 'post','page' ],
            'rewrite'           => [ 'slug' => $taxonomy ],
        ]);

        $un_fem = 'un' . ( $config['feminin'] ? 'e' : '' ) . ' ' . $config['name'];

        register_taxonomy( $taxonomy, $config['post_types'], [
            'hierarchical'      => $config['hierarchical'],
            'labels' => [
                'name'              => __( $config['pluriel'] ),
                'singular_name'     => __( $config['name'] ),
                'search_items'      => __( 'Search ' . $config['pluriel'] ),
                'all_items'         => __( 'All ' . $config['pluriel'] ),
                'parent_item'       => __( 'Parent ' . $config['name'] ),
                'parent_item_colon' => __( 'Parent ' . $config['name'] . ':' ),
                'edit_item'         => __( 'Modifier ' . $un_fem ),
                'update_item'       => __( 'Mettre &agrave; jour ' . $un_fem ),
                'add_new_item'      => __( 'Ajouter ' . $un_fem ),
                'new_item_name'     => __( 'Nouve' . ( $config['feminin'] ? 'lle' : 'au' ) . ' nom de ' . $config['name'] . ' ' ),
                'menu_name'         => __( $config['pluriel'] ),
            ],
            'show_ui'           => $config['show_ui'],
            'show_admin_column' => $config['show_admin_column'],
            'show_in_rest'      => $config['hierarchical'],
            'query_var'         => $config['query_var'],
            'rewrite'           => $config['rewrite'],
        ]);
    }
}
