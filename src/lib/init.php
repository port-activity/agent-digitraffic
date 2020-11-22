<?php
namespace SMA\PAA\AGENT;

spl_autoload_register(
    function ($className) {
        $pathToFind = str_replace("\\", "/", $className);
        $dirs = ["/"];
        foreach ($dirs as $dir) {
            $file  = __DIR__ . $dir . $pathToFind . '.php';
            if (file_exists($file)) {
                include $file;
                return true;
            }
        }

        return false;
    }
);
