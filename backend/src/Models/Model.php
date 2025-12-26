<?php

namespace Models;

use Services\Database;

abstract class Model {

    protected ?\PDO $pdo;
    private static $instance;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
    }

    public abstract function get();

    public static function __callStatic($name, $arguments)
    {

        if (self::$instance == null) {
            self::$instance = new self();
        }

        if ($name == 'get') {
            self::$instance->get();
        }
    }

}
