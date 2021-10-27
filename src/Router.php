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
        add_action( 'parse_query', array(&$this, 'instanceController') );
    }

    /**
     * Instance Controller
     */
    public function instanceController()
    {
        if(is_admin())
            return;

        global $undfdApp;
        $undfd_template = get_query_var('undfd_template');
        $undfd_section = get_query_var('undfd_section');

        if (!$undfd_template)
            return;

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

    /**
     * Add rule
     *
     * @param $slug
     * @param null $section
     */
    protected function _addRule($slug, $section = null, $params = []){
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
        $redirect .= ((defined('ICL_LANGUAGE_CODE') && !is_plugin_active('polylang/polylang.php') && !is_plugin_active('polylang-pro/polylang.php')) ? '&lang=' . ICL_LANGUAGE_CODE : '');

        $this->_rules[] =  [
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

