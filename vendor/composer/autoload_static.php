<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInite2a274703a4dac328121b2d08f7fbe9d
{
    public static $prefixLengthsPsr4 = array (
        'P' => 
        array (
            'PiPHP\\GPIO\\' => 11,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'PiPHP\\GPIO\\' => 
        array (
            0 => __DIR__ . '/..' . '/piphp/gpio/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInite2a274703a4dac328121b2d08f7fbe9d::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInite2a274703a4dac328121b2d08f7fbe9d::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInite2a274703a4dac328121b2d08f7fbe9d::$classMap;

        }, null, ClassLoader::class);
    }
}