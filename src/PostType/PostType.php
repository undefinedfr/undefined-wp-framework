<?php
namespace Undefined\Core\PostType;

use Timber\Post as TimberPost;
use Timber\Timber;

/**
 * PostType Class
 * @since 2.0.0
 * @package Undefined\Core\PostType
 */
class PostType extends TimberPost
{
    /**
     * Return the key used to register the post type with WordPress
     * First parameter of the `register_post_type` function:
     * https://codex.wordpress.org/Function_Reference/register_post_type
     *
     * @return string
     */
    public static function getPostType()
    {
        return 'post';
    }

    /**
     * Return the config to use to register the post type with WordPress
     * Second parameter of the `register_post_type` function:
     * https://codex.wordpress.org/Function_Reference/register_post_type
     *
     * @return array|null
     */
    protected static function getPostTypeConfig()
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
        $postType   = static::getPostType();
        $config     = static::getPostTypeConfig();

        if ( empty( $postType ) || $postType === 'post' ) {
            throw new \Exception( 'Post type not set (please add getPostType method)' );
        }

        $config = wp_parse_args( $config, [
            'pluriel'               => ucfirst( $postType ) . ( substr( $postType, -1 ) != 's' ?  's' : '' ),
            'singulier'             => ucfirst( $postType ),
            'feminin'               => false,
            'public'                => true,
            'publicly_queryable'    => true,
            'show_ui'               => true,
            'show_in_rest'          => true,
            '_builtin'              => false,
            'show_in_menu'          => true,
            'query_var'             => true,
            'capability_type'       => 'post',
            'menu_icon'             => null,
            'menu_position'         => 5,
            'supports'              => ['title', 'editor', 'excerpt', 'trackbacks', 'custom-fields', 'comments', 'revisions', 'thumbnail', 'author', 'page-attributes'],
            'taxonomies'            => ['post_tag', 'category'],
            'rewrite'               => $postType,
        ]);

        $fem_single = ( $config['feminin'] ? 'e':'' );
        $article_single = 'un' . $fem_single;
        $new_single = 'nouve' . ( $config['feminin'] ? 'lle' : 'au' );
        $no_single = 'aucun' . $fem_single;

        $config = [
            'label'                 => __( ucfirst ($config['pluriel'] ) ),
            'singular_label'        => __( ucfirst($config['singulier'] ) ),
            'description'           => '',
            'public'                => $config['public'],
            'publicly_queryable'    => $config['publicly_queryable'],
            'taxonomies'            => $config['taxonomies'],
            'menu_position'         => $config['menu_position'],
            'show_ui'               => $config['show_ui'],
            'show_in_rest'          => $config['show_in_rest'],
            '_builtin'              => $config['_builtin'],
            'show_in_menu'          => $config['show_in_menu'],
            'hierarchical'          => !empty( $config['hierarchical'] ),
            'query_var'             => $config['query_var'],
            'has_archive'           => is_array( $config['rewrite'] ) ? $config['rewrite']['slug'] : ( $config['has_archive'] ?? true ),
            'rewrite'               => is_array( $config['rewrite'] ) ? $config['rewrite'] : ['slug' => $config['rewrite']],
            'supports'              => $config['supports'],
            'menu_icon'             => $config['menu_icon'],
            'capability_type'       => $config['capability_type'],
            'labels' => [
                'name'                  => __( ucfirst( $config['pluriel'] ) ),
                'name_admin_bar'        => __( ucfirst( $config['singulier'] ) ),
                'singular_name'         => __( ucfirst( $config['singulier'] ) ),
                'menu_name'             => __( ucfirst( $config['pluriel'] ) ),
                'add_new'               => __( 'Ajouter ' . $article_single . ' ' . $config['singulier'] ),
                'add_new_item'          => __( 'Ajouter ' . $article_single . ' ' . $new_single . ' ' . $config['singulier'] ),
                'edit'                  => __( 'Modifier'),
                'edit_item'             => __( 'Modifier ' . $article_single . ' ' . $config['singulier'] ),
                'new_item'              => __( ucfirst( $new_single ) . ' ' . $config['singulier'] ),
                'view'                  => __( 'Voir des ' . $config['pluriel'] ),
                'view_item'             => __( 'Voir ' . $article_single . ' ' . $config['singulier'] ),
                'search_items'          => __( 'Rechercher dans les ' . $config['pluriel']),
                'not_found'             => __( ucfirst( $no_single ) . ' ' . $config['singulier'] . ' trouv&eacute;' . $fem_single),
                'not_found_in_trash'    => __( ucfirst( $no_single ) . ' ' . $config['singulier'] . ' trouv&eacute;' . $fem_single . ' dans la corbeille'),
                'parent'                => __( ucfirst( $config['pluriel'] ).' parent' . $fem_single . 's'),
            ],
        ];

        register_post_type( $postType, $config );
    }
}
