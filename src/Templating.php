<?php
namespace Undefined\Core;

/**
 * Templating
 *
 * @name Templating
 * @since 1.0.0
 * @package Undefined\Core
 */
class Templating
{
    public function __construct()
    {
        add_action( 'template_redirect', array(&$this, 'templateRedirect') );
        add_filter( 'body_class', array(&$this, 'customBodyClasses') );
    }

    /**
     * Load template from Controller method
     * TODO : Add Timber Support
     */
    public function templateRedirect()
    {
        $undfd_template = get_query_var('undfd_template');
        $undfd_section = get_query_var('undfd_section');
        
        if (!$undfd_template)
            return;

        $tpl = $undfd_template . (($undfd_section) ? '--' . str_replace('/', '--', $undfd_section) : '');
        
       if(file_exists(TEMPLATEPATH . "/template-$tpl.php")){
           include (TEMPLATEPATH . "/template-$tpl.php");

           exit;
       }
    }

    /**
     * Add default body classes
     * @param $classes
     * @return array
     */
    public function customBodyClasses( $classes ) {
        $undfd_template = get_query_var('undfd_template');
        $undfd_section = get_query_var('undfd_section');

        if ( !empty($undfd_template) ) {
            $classes[] = 'page-template-' . $undfd_template;
        }

        if ( !empty($undfd_section) ) {
            $classes[] = 'page-sub-template-' . str_replace('/', '--', $undfd_section);
        }

        return $classes;
    }
}

