<?php

namespace Models;

use Services\Database;

abstract class Model {

    private Database $db;

    private static $instance;

    public function __construct()
    {
        //
        $this->db = new Database();
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
