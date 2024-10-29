<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitdaad3a9f52f9e3b38e213e3d9d866290
{
    public static $files = array (
        '6e3fae29631ef280660b3cdad06f25a8' => __DIR__ . '/..' . '/symfony/deprecation-contracts/function.php',
        '320cde22f66dd4f5d3fd621d3e88b98f' => __DIR__ . '/..' . '/symfony/polyfill-ctype/bootstrap.php',
        '0e6d7bf4a5811bfa5cf40c5ccd6fae6a' => __DIR__ . '/..' . '/symfony/polyfill-mbstring/bootstrap.php',
        '23c18046f52bef3eea034657bafda50f' => __DIR__ . '/..' . '/symfony/polyfill-php81/bootstrap.php',
        '89efb1254ef2d1c5d80096acd12c4098' => __DIR__ . '/..' . '/twig/twig/src/Resources/core.php',
        'ffecb95d45175fd40f75be8a23b34f90' => __DIR__ . '/..' . '/twig/twig/src/Resources/debug.php',
        'c7baa00073ee9c61edf148c51917cfb4' => __DIR__ . '/..' . '/twig/twig/src/Resources/escaper.php',
        'f844ccf1d25df8663951193c3fc307c8' => __DIR__ . '/..' . '/twig/twig/src/Resources/string_loader.php',
        'f67691bbf3e56eb0b63e9061f037180b' => __DIR__ . '/..' . '/vinkla/extended-acf/src/helpers.php',
        '685c0c9b0aae55957cbb597018fbca5e' => __DIR__ . '/../..' . '/src/Functions.php',
    );

    public static $prefixLengthsPsr4 = array (
        'U' => 
        array (
            'Undefined\\Core\\' => 15,
        ),
        'T' => 
        array (
            'Twig\\' => 5,
            'Timber\\' => 7,
        ),
        'S' => 
        array (
            'Symfony\\Polyfill\\Php81\\' => 23,
            'Symfony\\Polyfill\\Mbstring\\' => 26,
            'Symfony\\Polyfill\\Ctype\\' => 23,
        ),
        'E' => 
        array (
            'Extended\\ACF\\' => 13,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Undefined\\Core\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
        'Twig\\' => 
        array (
            0 => __DIR__ . '/..' . '/twig/twig/src',
        ),
        'Timber\\' => 
        array (
            0 => __DIR__ . '/..' . '/timber/timber/src',
        ),
        'Symfony\\Polyfill\\Php81\\' => 
        array (
            0 => __DIR__ . '/..' . '/symfony/polyfill-php81',
        ),
        'Symfony\\Polyfill\\Mbstring\\' => 
        array (
            0 => __DIR__ . '/..' . '/symfony/polyfill-mbstring',
        ),
        'Symfony\\Polyfill\\Ctype\\' => 
        array (
            0 => __DIR__ . '/..' . '/symfony/polyfill-ctype',
        ),
        'Extended\\ACF\\' => 
        array (
            0 => __DIR__ . '/..' . '/vinkla/extended-acf/src',
        ),
    );

    public static $classMap = array (
        'CURLStringFile' => __DIR__ . '/..' . '/symfony/polyfill-php81/Resources/stubs/CURLStringFile.php',
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
        'ReturnTypeWillChange' => __DIR__ . '/..' . '/symfony/polyfill-php81/Resources/stubs/ReturnTypeWillChange.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitdaad3a9f52f9e3b38e213e3d9d866290::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitdaad3a9f52f9e3b38e213e3d9d866290::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInitdaad3a9f52f9e3b38e213e3d9d866290::$classMap;

        }, null, ClassLoader::class);
    }
}
