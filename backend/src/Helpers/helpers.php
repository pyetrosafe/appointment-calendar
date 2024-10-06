<?php

use Symfony\Component\VarDumper\VarDumper;

if (!function_exists('dump')) {
    function dump() {
        $args = func_get_args();

        foreach ($args as $arg)
            return VarDumper::dump($arg);
    }
}

if (!function_exists('dd')) {
    function dd() {
        $args = func_get_args();

        foreach ($args as $arg)
            return VarDumper::dump($arg);

        die;
    }
}
