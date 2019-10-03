<?php
namespace Undefined\Core;

require_once 'src/Functions.php';

spl_autoload_register(__NAMESPACE__ . '\\undfd_core_autoload');
function undfd_core_autoload($class) {
    $class = ltrim($class, '\\');

    if(strpos($class, __NAMESPACE__) !== 0)
        return;

    $class = str_replace(__NAMESPACE__ . '\\', '', $class);

    if(!preg_match('#^Plugins\\\#', $class) || !preg_match('#^Gutenberg\\\#', $class)) {
        $path = 'src/' . str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';

        require_once($path);
    }
}