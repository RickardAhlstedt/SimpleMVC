<?php

namespace SimpleMVC\Database;

class DB
{
    public static function getQueryBuilder(string $entityClass): QueryBuilder
    {
        $em = \SimpleMVC\Core\Container::getInstance()->get(EntityManager::class);
        return $em->getQueryBuilder($entityClass);
    }

}
