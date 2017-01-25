<?php

namespace Deimos\Imaginarium\Server;

class Db {

    protected $pdo;

    public function __construct()
    {
        $this->pdo = new \PDO('sqlite:file.db');
        $this->pdo->exec('CREATE TABLE IF NOT EXISTS files (id int NOT NULL AUTO_INCREMENT PRIMARY KEY,user varchar(255) NOT NULL,file varchar(16) NOT NULL);');
    }

    public function imageExist($user, $name) {

        $pdo = $this->pdo->prepare('SELECT count(id) as c WHERE user=? AND file=?', [$user, $name]);

        $pdo->execute();

        return $pdo->fetch()['c'];

    }

}
