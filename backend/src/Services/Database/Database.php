<?php

namespace Services;

use PDO;
use Exception;

class Database {

    private $connection = null;

    public function __construct() {
        try {
            // TODO: GET FROM ENV
            $drive      = 'mysql';
            $host       = 'db';
            $port       = '3306';
            $dbname     = 'appointmentdb';
            $charset    = 'utf8';
            $user       = 'user';
            $pass       = 'u53rP5WD';

            $dns = "$drive:host=$host;port=$port;dbname=$dbname;charset=$charset";

            $this->connection = new PDO($dns, $user, $pass);

            $sth = $this->connection->query('SELECT now() as date');
            $rows = $sth->fetchAll(PDO::FETCH_ASSOC);
            foreach($rows as $row) {
                dump($row['date']);
            }

        } catch(Exception $execption) {
            var_dump("Conexão não estabelecida: " . $execption->getMessage());
        }
    }
}
