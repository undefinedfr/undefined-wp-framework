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
            if(!isset($pt['pluriel'])) $pt['pluriel'] = $idpt;
            if(!isset($pt['singulier'])) $pt['singulier'] = $idpt;
            if(!isset($pt['feminin'])) $pt['feminin'] = 0;
            if(!isset($pt['supports'])) $pt['supports'] = ['title','editor','excerpt','trackbacks','custom-fields','comments','revisions','thumbnail','author','page-attributes'];
            if(!isset($pt['taxonomies'])) $pt['taxonomies'] = ['post_tag','category'];
            $rewrite = (isset($pt['rewrite'])) ? $pt['rewrite'] : $idpt;

            $fem_single = ($pt['feminin'] ? 'e':'');
            $article_single = 'un'.$fem_single;
            $new_single = 'nouve'.($pt['feminin'] ? 'lle':'au');
            $no_single = 'aucun'.$fem_single;

            $_PT_PARAMS = [
                'label' => ucfirst($pt['pluriel']),
                'singular_label' => ucfirst($pt['singulier']),
                'description' => '',
                'public' => true,
                'publicly_queryable' => true,
                'taxonomies' => $pt['taxonomies'],
                'menu_position' => 5,
                'show_ui' => true,
                'show_in_rest'       => true,
                '_builtin' => false,
                'show_in_menu' => true,
                'hierarchical' => !empty($pt['hierarchical']),
                'query_var' => true,
                'has_archive' => is_array($rewrite) ? $idpt : $rewrite,
                'rewrite' => is_array($rewrite) ? $rewrite : ['slug' => $rewrite],
                'supports' => $pt['supports'],
                'labels' => [
                    'name' => ucfirst($pt['pluriel']),
                    'name_admin_bar' =>  ucfirst($pt['singulier']),
                    'singular_name' => ucfirst($pt['singulier']),
                    'menu_name' => ucfirst($pt['pluriel']),
                    'add_new' => 'Ajouter '.$article_single.' '.$pt['singulier'],
                    'add_new_item' => 'Ajouter '.$article_single.' '.$new_single.' '.$pt['singulier'],
                    'edit' => 'Modifier',
                    'edit_item' => 'Modifier '.$article_single.' '.$pt['singulier'],
                    'new_item' => ucfirst($new_single).' '.$pt['singulier'],
                    'view' => 'Voir des '.$pt['pluriel'],
                    'view_item' => 'Voir '.$article_single.' '.$pt['singulier'],
                    'search_items' => 'Rechercher dans les '.$pt['pluriel'],
                    'not_found' => ucfirst($no_single).' '.$pt['singulier'].' trouv&eacute;'.$fem_single,
                    'not_found_in_trash' => ucfirst($no_single).' '.$pt['singulier'].' trouv&eacute;'.$fem_single.' dans la corbeille',
                    'parent' => ucfirst($pt['pluriel']).' parent'.$fem_single.'s',
                ],
                'capability_type'=>(isset($pt['capability_type'])) ? $pt['capability_type'] : 'post',
            ];

            if(isset($pt['capabilities']))
                $_PT_PARAMS['capabilities'] = $pt['capabilities'];
            if(isset($pt['menu_icon']))
                $_PT_PARAMS['menu_icon'] = $pt['menu_icon'];

            register_post_type($idpt,$_PT_PARAMS);
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


