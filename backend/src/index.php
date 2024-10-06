<?php
require_once('autoload.php');

use Services\Database;

echo "HELLO WORLD!";

try {

    $db = new Database();

} catch(Exception $execption) {
    dump("Conexão não estabelecida: " . $execption->getMessage());
}

dump("OI");
