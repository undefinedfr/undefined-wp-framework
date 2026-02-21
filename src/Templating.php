<?php
namespace Undefined\Core;

/**
 * Templating
 *
 * @name Templating
 * @since 1.0.0
 * @update 2.1.0
 * @package Undefined\Core
 */
class Templating
{
    /**
     * @return void
     */
    public function __construct()
    {
        add_action('template_redirect', [$this, 'templateRedirect']);
        add_filter('body_class', [$this, 'customBodyClasses']);
        add_filter('timber/loader/loader', [$this, 'registerTwigNamespaces']);
    }

    /**
     * Register custom Twig namespaces for Timber
     *
     * @param \Twig\Loader\FilesystemLoader $loader
     * @return \Twig\Loader\FilesystemLoader
     */
    public function registerTwigNamespaces($loader)
    {
        // Add @blocks namespace for Timber-style blocks
        $blocksPath = get_template_directory() . '/app/blocks';
        if (is_dir($blocksPath)) {
            $loader->addPath($blocksPath, 'blocks');
        }

        return $loader;
    }

    /**
     * Load template from Controller method
     *
     * @return void
     */
    public function templateRedirect()
    {
        $undfd_template = get_query_var('undfd_template');
        $undfd_section  = get_query_var('undfd_section');

        if (!$undfd_template)
            return;

        $tpl = $undfd_template . ( ( $undfd_section ) ? '--' . str_replace( '/', '--', $undfd_section ) : '' );

       if( file_exists( get_template_directory() . '/template-' . $tpl . '.php' ) ){
           include ( get_template_directory() . '/template-' . $tpl . '.php' );

           exit;
       }
    }

    /**
     * Add default body classes
     * @param $classes
     * @return array
     */
    public function customBodyClasses( $classes )
    {
        $undfd_template = get_query_var('undfd_template');
        $undfd_section  = get_query_var('undfd_section');

        if ( !empty( $undfd_template ) ) {
            $classes[] = 'page-template-' . $undfd_template;
        }

        if ( !empty( $undfd_section ) ) {
            $classes[] = 'page-sub-template-' . str_replace('/', '--', $undfd_section);
        }

        return $classes;
    }
}

