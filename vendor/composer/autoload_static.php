<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitaf74e3fcf98596027f125aaad9b98c69
{
    public static $prefixLengthsPsr4 = array (
        'F' => 
        array (
            'Frlnc\\Slack\\' => 12,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Frlnc\\Slack\\' => 
        array (
            0 => __DIR__ . '/..' . '/frlnc/php-slack/src',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitaf74e3fcf98596027f125aaad9b98c69::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitaf74e3fcf98596027f125aaad9b98c69::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
