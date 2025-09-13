<?php

namespace SimpleMVC\Database;

use SimpleMVC\Database\Driver\DatabaseInterface;

class EntityManager
{
    private DatabaseInterface $driver;

    public function __construct(DatabaseInterface $driver)
    {
        $this->driver = $driver;
    }

    public function getRepository(string $entityClass): Repository
    {
        return new Repository($this->driver, $entityClass);
    }

    public function persist(object $entity): void
    {
        // For now, delegate to repository
        $repo = $this->getRepository($entity::class);
        $repo->save($entity);
    }

    public function remove(object $entity): void
    {
        $repo = $this->getRepository($entity::class);
        $repo->delete($entity);
    }

    public function getQueryBuilder(string $entityClass): QueryBuilder
    {
        return new QueryBuilder($this->driver, $entityClass);
    }

}