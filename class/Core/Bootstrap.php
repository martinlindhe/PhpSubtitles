<?php
namespace Core;

require_once __DIR__.'/../Exceptions.php';

class Bootstrap
{
    private static function autoload($class)
    {
        $class = strtr($class, "\\", DIRECTORY_SEPARATOR);

        $fileName = realpath(__DIR__.'/../').'/'.$class.'.php';

        if (file_exists($fileName)) {
            include $fileName;
        }
    }

    /**
     * Activates the class autoloader
     * @codeCoverageIgnore
     */
    public static function registerAutoloader()
    {
        spl_autoload_register('Core\Bootstrap::autoload');
    }
}
