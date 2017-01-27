<?php

namespace Deimos\Imaginarium\Server;

class Database
{

    protected $pdo;

    public function __construct($rootDir)
    {
        $this->pdo = new \PDO('sqlite:' . $rootDir . 'file.db');
        $this->pdo->exec('CREATE TABLE IF NOT EXISTS files (id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,user TEXT NOT NULL,file TEXT NOT NULL);');
    }

    public function imageExist($user, $name)
    {
        $statement = $this->pdo->prepare('SELECT count(id) as c FROM files WHERE user=? AND file=?');

        $statement->execute([$user, $name]);

        return $statement->fetch()['c'];
    }

    public function imageSaveToDb($user, $name)
    {
        $this->pdo->prepare('INSERT INTO files (user, file) VALUES (?, ?)')->execute([$user, $name]);
    }

}
