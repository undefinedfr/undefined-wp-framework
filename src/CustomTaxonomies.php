<?php
namespace Undefined\Core;

/**
 * Set Custom Taxonomies
 *
 * @name CustomTaxonomies
 * @since 1.0.0
 * @package Undefined\Core
 */
class CustomTaxonomies
{
    protected $_taxonomies = [
//        'category' => array(
//            'name' => 'Catégorie',
//            'pluriel' => 'Catégories',
//            'feminin' => 1,
//            'hierarchical' => true,
//            'post_types' => array( 'creation','post' ),
//        ),
    ];

    public function __construct()
    {
        add_action( 'init', array(&$this, 'app_create_taxonomies'), 0 );
    }

    /**
     * Launch register
     */
    public function app_create_taxonomies()
    {
        $this->_launch_create_taxonomies( $this->_taxonomies );
    }

    /**
     * Save extra fields
     * @param $term_id
     */
    public function save_extra_taxonomy_fields( $term_id )
    {
        if ( isset( $_POST['term_meta'] ) ) {
            $t_id = $term_id;
            $term_meta = get_option( "app_meta_taxonomy_$t_id" );
            $cat_keys = array_keys( $_POST['term_meta'] );
            foreach ( $cat_keys as $key ) {
                if ( isset ( $_POST['term_meta'][$key] ) ) {
                    $term_meta[$key] = $_POST['term_meta'][$key];
                }
            }
            // Save the option array.
            update_option( "app_meta_taxonomy_$t_id", $term_meta );
        }
    }

    /**
     * Edit extra fields
     * @param $tag
     */
    public function extra_edit_tax_fields($tag)
    {
        // Check for existing taxonomy meta for term ID.
        $t_id = $tag->term_id;
        $term_meta = get_option( "app_meta_taxonomy_$t_id" );
        global $taxonomies_extra_fields;
        if(!empty($taxonomies_extra_fields)){
            foreach($taxonomies_extra_fields as $id_field => $field_values) {
                if(!isset($field_values['taxonomies'])) $field_values['taxonomies'] = array();
                if(!isset($field_values['type'])) $field_values['type'] = 'text';
                if(in_array($tag->taxonomy,$field_values['taxonomies'])){ ?>
                    <tr class="form-field">
                        <th scope="row" valign="top">
                            <label for="app_field_<?php echo $id_field; ?>"><?php echo $field_values['name']; ?></label>
                        </th>
                        <td>
                            <?php switch ($field_values['type']) {
                                case 'textarea':
                                    ?><textarea name="term_meta[<?php echo $id_field; ?>]" id="app_field_<?php echo $id_field; ?>"><?php echo esc_attr( $term_meta[$id_field] ) ? esc_attr( $term_meta[$id_field] ) : ''; ?></textarea><?php
                                    break;
                                default:
                                    ?><input type="text" name="term_meta[<?php echo $id_field; ?>]" id="app_field_<?php echo $id_field; ?>" value="<?php echo esc_attr( $term_meta[$id_field] ) ? esc_attr( $term_meta[$id_field] ) : ''; ?>" /><?php
                                    break;
                            } ?>
                            <?php echo (isset($field_values['description']) ? '<p class="description">'.$field_values['description'].'</p>' : ''); ?>
                        </td>
                    </tr>
                <?php }
            }
        }
    }

    /**
     * Add extra fields
     * @param $tag
     */
    public function extra_add_tax_fields( $tag )
    {
        // Check for existing taxonomy meta for term ID.
        global $taxonomies_extra_fields;
        if(!empty($taxonomies_extra_fields)){
            foreach($taxonomies_extra_fields as $id_field => $field_values) {
                if (!isset($field_values['taxonomies'])) $field_values['taxonomies'] = array();
                if (!isset($field_values['type'])) $field_values['type'] = 'text';
                if (in_array($tag, $field_values['taxonomies'])) { ?>
                    <div class="form-field">
                        <label for="app_field_<?php echo $id_field; ?>"><?php echo $field_values['name']; ?></label>
                        <?php switch ($field_values['type']) {
                            case 'textarea':
                                ?><textarea name="term_meta[<?php echo $id_field; ?>]"
                                            id="app_field_<?php echo $id_field; ?>"></textarea><?php
                                break;

                            default:
                                ?><input type="text" name="term_meta[<?php echo $id_field; ?>]"
                                         id="app_field_<?php echo $id_field; ?>" value="" /><?php
                                break;
                        } ?>
                        <?php echo(isset($field_values['description']) ? '<p class="description">' . $field_values['description'] . '</p>' : ''); ?>
                    </div>
                <?php }
            }
        }
    }

    /**
     * Register custom taxonomies
     * @param $taxonomies
     */
    private function _launch_create_taxonomies($taxonomies)
    {
        if(empty($taxonomies))
            return;

        foreach($taxonomies as $slug => $args){
            if(!isset($args['hierarchical']) || !is_bool($args['hierarchical']))
                $args['hierarchical'] = true;
            if(!isset($args['feminin']) || !is_bool($args['hierarchical']))
                $args['feminin'] = true;
            if(empty($args['post_types']))
                $args['post_types'] = array('post','page');
            if(is_string($args['post_types']))
                $args['post_types'] = array($args['post_types']);

            $un_fem = 'un'.($args['feminin'] ? 'e':'').' '.$args['name'];

            register_taxonomy($slug,$args['post_types'], array(
                'hierarchical' => $args['hierarchical'],
                'labels' => array(
                    'name' => _x( $args['pluriel'], 'taxonomy general name' ),
                    'singular_name' => _x( $args['name'], 'taxonomy singular name' ),
                    'search_items' =>  ( 'Search '.$args['pluriel'] ),
                    'all_items' => ( 'All '.$args['pluriel'] ),
                    'parent_item' => ( 'Parent '.$args['name'] ),
                    'parent_item_colon' => ( 'Parent '.$args['name'].':' ),
                    'edit_item' => ( 'Modifier '.$un_fem ),
                    'update_item' => ( 'Mettre &agrave; jour '.$un_fem ),
                    'add_new_item' => ( 'Ajouter '.$un_fem ),
                    'new_item_name' => ( 'Nouve'.($args['feminin'] ? 'lle':'au').' nom de '.$args['name'].' ' ),
                    'menu_name' => ( $args['pluriel'] ),
                ),
                'show_ui' => true,
                'query_var' => true,
                'rewrite' => array( 'slug' => $slug ),
            ));
        }

        $default_taxonomies = array( 'category' => array(), 'post_tag' => array() );
        $all_taxonomies = array_merge( $taxonomies, $default_taxonomies );

        foreach ($all_taxonomies as $slug => $args) {
            add_action($slug . '_edit_form_fields', array(&$this, 'extra_edit_tax_fields'), 10, 2);
            add_action($slug . '_add_form_fields', array(&$this, 'extra_add_tax_fields'), 10, 2);
            add_action('edited_' . $slug, array(&$this, 'save_extra_taxonomy_fields'), 10, 2);
            add_action('create_' . $slug, array(&$this, 'save_extra_taxonomy_fields'), 10, 2);
        }
    }

    /**
     * Retrieve custom taxonomies
     * @return array
     */
    public function getTaxonomies(){
        return $this->_taxonomies;
    }
}

