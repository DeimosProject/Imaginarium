<?php

namespace Deimos\Imaginarium\Server;

use Deimos\Imaginarium\Builder;

class Database
{

    /**
     * @var Builder
     */
    protected $builder;

    public function __construct(Builder $builder)
    {
        $q = 'CREATE TABLE IF NOT EXISTS files (id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,user TEXT NOT NULL,name TEXT NOT NULL);';
        $this->builder = $builder;

        $this->builder->database()->exec($q);
    }

    public function imageExist($user, $name)
    {
        return $this->builder->orm()
            ->repository('file')
            ->where('user', $user)
            ->where('name', $name)
            ->count();
    }

    public function imageSaveToDb($user, $name)
    {
        $this->builder->orm()
            ->create('file', ['user' => $user, 'name' => $name]);
    }

}
