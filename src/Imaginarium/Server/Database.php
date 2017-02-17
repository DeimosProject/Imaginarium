<?php

namespace Deimos\Imaginarium\Server;

use Deimos\Imaginarium\Builder;

class Database
{

    /**
     * @var Builder
     */
    protected $builder;

    /**
     * Database constructor.
     *
     * @param Builder $builder
     */
    public function __construct(Builder $builder)
    {
        $database     = $builder->database();
        $queryBuilder = $database->queryBuilder();

        if ($queryBuilder->adapter()->name() === 'sqlite')
        {
            $q = 'CREATE TABLE IF NOT EXISTS files (
                id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
                user TEXT NOT NULL,
                name TEXT NOT NULL
            );';

            $database->exec($q);
        }

        $this->builder = $builder;
    }

    /**
     * @return \Deimos\ORM\ORM
     */
    protected function orm()
    {
        return $this->builder->orm();
    }

    /**
     * @param $user
     * @param $name
     *
     * @return int
     */
    public function imageExist($user, $name)
    {
        return $this->orm()->repository('file')
            ->where('user', $user)
            ->where('name', $name)
            ->count();
    }

    /**
     * @param $user
     * @param $name
     *
     * @return \Deimos\ORM\Entity
     *
     * @throws \Deimos\ORM\Exceptions\ModelNotLoad
     * @throws \Deimos\ORM\Exceptions\ModelNotModify
     */
    public function imageSaveToDb($user, $name)
    {
        return $this->orm()->create('file', [
            'user' => $user,
            'name' => $name
        ]);
    }

}
