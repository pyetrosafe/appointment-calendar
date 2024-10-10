<?php

// ini_set('error_reporting', E_ERROR);

try {
    $loader = require_once('autoload.php');
    require_once('bootstrap.php');
} catch (Exception $execption) {
    printf('Um erro ocorreu: %s', $execption->getMessage());
}
