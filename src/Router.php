<?php
namespace Undefined\Core;

/**
 * @name Router
 * @package Undefined\Core
 * @since 1.0.0
 */
class Router
{
    protected $_rules = [];

    public function __construct()
    {
        add_action( 'init', [&$this, 'customRewriteRule'], 10, 0 );
        add_filter( 'query_vars', [&$this, 'registerQueryVars'] );
        add_filter( 'template_include', array(&$this, 'instanceController'), 99, 1);
    }

    /**
     * Instance Controller
     * @param $template
     */
    public function instanceController( $template )
    {
        if(is_admin())
            return $template;

        global $undfdApp;
        $undfd_template = get_query_var('undfd_template');
        $undfd_section = get_query_var('undfd_section');

        if (!$undfd_template)
            return $template;

        // Init Controller
        $controllerName = $this->_getControllerName($undfd_template);
        if(file_exists(__PROJECTDIR__ . 'Controllers/' . $controllerName . '.php')){
            require_once(__PROJECTDIR__ . 'Controllers/' . $controllerName . '.php');
            $undfdApp->setController(new $controllerName($undfd_section));
        }
    }

    /**
     * Helper toUpper
     *
     * @param $matches
     * @return string
     */
    public function toUpper($matches) {
        return strtoupper($matches[1]);
    }

    /**
     * Set custom rewrite rules
     */
    public function customRewriteRule()
    {
        foreach($this->_rules as $rule){
            add_rewrite_rule($rule['regex'], $rule['redirect'], 'top');
        }
    }

    /**
     * Add custom query vars
     *
     * @param $vars
     * @return array
     */
    public function registerQueryVars($vars)
    {
        $vars[] = 'undfd_template';
        $vars[] = 'undfd_section';

        return $vars;
    }

    public function getRule( $rule ) {
        return $this->getRules()[$rule];
    }

    public function getRules() {
        return $this->_rules;
    }

    /**
     * Add rule
     *
     * @param $slug
     * @param null $section
     */
    protected function _addRule($slug, $section = null, $params = []) {
        if (!function_exists('is_plugin_active')) {
            include_once(ABSPATH . 'wp-admin/includes/plugin.php');
        }

        $regex = '^' . $slug;
        $regex .= (!empty($section) ? '/' . $section : '');
        foreach($params as $param){
            $regex .= '/' . $param;
        }
        $regex .= '/?$';

        $redirect = 'index.php?undfd_template=' . $slug;
        $redirect .= '&undfd_section=' . urlencode(!empty($section) ? $section : 'index');
        $i = 1;
        foreach($params as $key => $param){
            $redirect .= '&' . $key . '=$matches[' . $i . ']';
            $i++;
        }

        // WPML Support
        if (defined('ICL_LANGUAGE_CODE') && !is_plugin_active('polylang/polylang.php') && !is_plugin_active('polylang-pro/polylang.php')) {
            $defaultLanguage = apply_filters( 'wpml_default_language' ,null );
            $langs = wpml_active_languages();
            foreach ($langs as $lang) {
                if ($lang['language_code'] != $defaultLanguage) {
                    $this->_rules[$slug . ($section ? '-' . $section : '') . (!empty($params) ? '-' . implode('-', array_keys($params)) : '') . '-' . $lang['language_code']] = [
                        'regex' => str_replace('^', '^' . $lang['language_code'] . '/', $regex),
                        'redirect' => $redirect . '&lang=' . $lang['language_code'],
                    ];
                }
            }
            $redirect .=  '&lang=' . $defaultLanguage;
        }

        // Polylang Support
        if (is_plugin_active('polylang/polylang.php') || is_plugin_active('polylang-pro/polylang.php')) {
            $defaultLanguage = pll_default_language('slug');
            $langs = pll_languages_list(['fields' => 'slug']);
            $polylang = get_option('polylang');
            foreach ($langs as $lang) {
                if ($lang != $defaultLanguage || $polylang['hide_default'] == 0) {
                    $this->_rules[$slug . ($section ? '-' . $section : '') . (!empty($params) ? '-' . implode('-', array_keys($params)) : '') . '-' . $lang] = [
                        'regex' => str_replace('^', '^' . $lang . '/', $regex),
                        'redirect' => $redirect . '&lang=' . $lang,
                    ];
                }
            }
            $redirect .=  '&lang=' . $defaultLanguage;
        }

        $this->_rules[$slug . ($section ? '-' . $section : '') . (!empty($params) ? '-' . implode('-', array_keys($params)) : '')] =  [
            'regex' => $regex,
            'redirect' => $redirect,
        ];
    }

    /**
     * Get Correct Controller Name
     *
     * @param $template
     * @return string
     */
    private function _getControllerName($template){
        return ucfirst(preg_replace_callback('/[-_](.)/', [$this, 'toUpper'], $template)) . 'Controller';
    }
}

