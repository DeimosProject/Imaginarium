<?php

namespace Deimos\Imaginarium\Server;

class Db {

    protected $pdo;

    public function __construct()
    {
        $this->pdo = new \PDO('sqlite:file.db');
        $this->pdo->exec('CREATE TABLE IF NOT EXISTS files (id int NOT NULL AUTO_INCREMENT PRIMARY KEY,user varchar(255) NOT NULL,file varchar(16) NOT NULL);');
    }

    public function imageExist($user, $name)
    {
        $statement = $this->pdo->prepare('SELECT count(id) as c FROM files WHERE user=? AND file=?');

        $statement->execute([$user, $name]);

        return $statement->fetch()['c'];
    }

    public function imageSaveToDb($user, $name)
    {
        $this->pdo->prepare('INSERT INTO files SET user=?, file=?')->execute([$user, $name]);
    }

}
