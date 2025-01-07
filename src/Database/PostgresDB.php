<?php

namespace App\Database;
use Exception;

class PostgresDB {
    public $db;

    public function __construct()
    {
        $this->db = pg_connect('host=postgres dbname=tg-bot-db user=admin password=1111');

        if (!$this->db) {
            throw new Exception('Возникла проблема с подключением к БД');
        }
    }

    public function executeQuery(string $query)
    {
        $query = pg_query($this->db, $query);
        return $query;
    }
}


