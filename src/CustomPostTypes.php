<?php
namespace Undefined\Core;

/**
 * Add Custom Post Types
 *
 * @name CustomPostTypes
 * @since 1.0.0
 * @package Undefined\Core
 */
class CustomPostTypes
{
    protected $_posttypes = [
//        'website' => array(
//            'pluriel'       => 'Websites',
//            'singulier'     => 'Website',
//            'feminin'       => 0,
//            'supports'      => array('title'),
//            'taxonomies'    => array(),
//            'show_in_rest'  => true,
//            'query_var'     => true,
//            'menu_icon'     => 'dashicons-admin-site',
//        ),
    ];

    public function __construct()
    {
        add_action( 'init', array(&$this, 'app_create_posttypes'), 0 );
    }

    /**
     * Launch register
     */
    public function app_create_posttypes(){
        $this->_launch_create_posttypes( $this->_posttypes );
    }

    /**
     * Register custom posts_types
     * @param $post_types
     */
    private function _launch_create_posttypes($post_types)
    {
        foreach($post_types as $idpt => $pt){
            $pt = wp_parse_args($pt, [
                'pluriel'               => ucfirst($idpt) . (substr($idpt, -1) != 's' ?  's' : ''),
                'singulier'             => ucfirst($idpt),
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
                'supports'              => ['title','editor','excerpt','trackbacks','custom-fields','comments','revisions','thumbnail','author','page-attributes'],
                'taxonomies'            => ['post_tag','category'],
                'rewrite'               => $idpt,
            ]);

            $fem_single = ($pt['feminin'] ? 'e':'');
            $article_single = 'un'.$fem_single;
            $new_single = 'nouve'.($pt['feminin'] ? 'lle':'au');
            $no_single = 'aucun'.$fem_single;

            $_PT_PARAMS = [
                'label'                 => __(ucfirst($pt['pluriel'])),
                'singular_label'        => __(ucfirst($pt['singulier'])),
                'description'           => '',
                'public'                => $pt['public'],
                'publicly_queryable'    => $pt['publicly_queryable'],
                'taxonomies'            => $pt['taxonomies'],
                'menu_position'         => $pt['menu_position'],
                'show_ui'               => $pt['show_ui'],
                'show_in_rest'          => $pt['show_in_rest'],
                '_builtin'              => $pt['_builtin'],
                'show_in_menu'          => $pt['show_in_menu'],
                'hierarchical'          => !empty($pt['hierarchical']),
                'query_var'             => $pt['query_var'],
                'has_archive'           => is_array($pt['rewrite']) ? $pt['rewrite']['slug'] : ($pt['has_archive'] ?? true),
                'rewrite'               => is_array($pt['rewrite']) ? $pt['rewrite'] : ['slug' => $pt['rewrite']],
                'supports'              => $pt['supports'],
                'menu_icon'             => $pt['menu_icon'],
                'capability_type'       => $pt['capability_type'],
                'labels' => [
                    'name'                  => __(ucfirst($pt['pluriel'])),
                    'name_admin_bar'        => __(ucfirst($pt['singulier'])),
                    'singular_name'         => __(ucfirst($pt['singulier'])),
                    'menu_name'             => __(ucfirst($pt['pluriel'])),
                    'add_new'               => __('Ajouter '.$article_single.' '.$pt['singulier']),
                    'add_new_item'          => __('Ajouter '.$article_single.' '.$new_single.' '.$pt['singulier']),
                    'edit'                  => __('Modifier'),
                    'edit_item'             => __('Modifier '.$article_single.' '.$pt['singulier']),
                    'new_item'              => __(ucfirst($new_single).' '.$pt['singulier']),
                    'view'                  => __('Voir des '.$pt['pluriel']),
                    'view_item'             => __('Voir '.$article_single.' '.$pt['singulier']),
                    'search_items'          => __('Rechercher dans les '.$pt['pluriel']),
                    'not_found'             => __(ucfirst($no_single).' '.$pt['singulier'].' trouv&eacute;'.$fem_single),
                    'not_found_in_trash'    => __(ucfirst($no_single).' '.$pt['singulier'].' trouv&eacute;'.$fem_single.' dans la corbeille'),
                    'parent'                => __(ucfirst($pt['pluriel']).' parent'.$fem_single.'s'),
                ],
            ];

            if(!empty($pt['capabilities']))
                $_PT_PARAMS['capabilities'] = $pt['capabilities'];

            register_post_type($idpt, $_PT_PARAMS);
        }
    }

    /**
     * Retrieve custom post_types
     * @return array
     */
    public function getPostTypes(){
        return $this->_posttypes;
    }
}
