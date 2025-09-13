<?php

namespace SimpleMVC\Database;

use SimpleMVC\Database\Driver\DatabaseInterface;
use SimpleMVC\Database\Attributes\Table;
use SimpleMVC\Database\Attributes\Column;
use ReflectionClass;
use ReflectionProperty;

class Repository
{
    private DatabaseInterface $driver;
    private string $entityClass;
    private string $table;
    private array $propertyToColumnMap = [];
    private array $columnToPropertyMap = [];
    private ?string $primaryKeyColumn = null;
    private ?string $primaryKeyProperty = null;

    public function __construct(DatabaseInterface $driver, string $entityClass)
    {
        $this->driver = $driver;
        $this->entityClass = $entityClass;

        $reflectionClass = new ReflectionClass($entityClass);

        // Check for Table attribute
        $tableAttributes = $reflectionClass->getAttributes(Table::class);
        if (count($tableAttributes) > 0) {
            /** @var Table $tableAttrInstance */
            $tableAttrInstance = $tableAttributes[0]->newInstance();
            $this->table = $tableAttrInstance->name;
        } else {
            // Convention: table name = lowercase class name + "s"
            $this->table = strtolower($reflectionClass->getShortName()) . 's';
        }

        // Map properties to columns using Column attributes
        foreach ($reflectionClass->getProperties() as $property) {
            $columnAttributes = $property->getAttributes(Column::class);
            if (count($columnAttributes) > 0) {
                /** @var Column $columnAttrInstance */
                $columnAttrInstance = $columnAttributes[0]->newInstance();
                $columnName = $columnAttrInstance->name;
                $this->propertyToColumnMap[$property->getName()] = $columnName;
                $this->columnToPropertyMap[$columnName] = $property->getName();

                if ($columnAttrInstance->primaryKey) {
                    $this->primaryKeyColumn = $columnName;
                    $this->primaryKeyProperty = $property->getName();
                }
            } else {
                // Fallback: property name = column name
                $this->propertyToColumnMap[$property->getName()] = $property->getName();
                $this->columnToPropertyMap[$property->getName()] = $property->getName();
            }
        }

        if ($this->primaryKeyColumn === null) {
            // Default primary key column and property
            $this->primaryKeyColumn = 'id';
            $this->primaryKeyProperty = 'id';
        }
    }

    public function find(int $id): ?object
    {
        $stmt = $this->driver->query("SELECT * FROM {$this->table} WHERE {$this->primaryKeyColumn} = :id", ['id' => $id]);
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $data ? $this->mapToEntity($data) : null;
    }

    public function findAll(): array
    {
        $stmt = $this->driver->query("SELECT * FROM {$this->table}");
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return array_map(fn ($row) => $this->mapToEntity($row), $rows);
    }

    public function save(object $entity): void
    {
        $data = [];
        $primaryKeyValue = null;

        foreach ($this->propertyToColumnMap as $property => $column) {
            if (property_exists($entity, $property)) {
                $value = $entity->$property;
                $data[$column] = $value;
                if ($column === $this->primaryKeyColumn) {
                    $primaryKeyValue = $value;
                }
            }
        }

        if ($primaryKeyValue) {
            // update
            $fields = array_keys($data);
            $updates = implode(', ', array_map(fn ($f) => "$f = :$f", $fields));
            $sql = "UPDATE {$this->table} SET $updates WHERE {$this->primaryKeyColumn} = :{$this->primaryKeyColumn}";
            $this->driver->query($sql, $data);
        } else {
            // insert
            $fields = array_keys($data);
            $columns = implode(', ', $fields);
            $placeholders = implode(', ', array_map(fn ($f) => ":$f", $fields));
            $sql = "INSERT INTO {$this->table} ($columns) VALUES ($placeholders)";
            $this->driver->query($sql, $data);

            if ($this->primaryKeyProperty !== null && property_exists($entity, $this->primaryKeyProperty)) {
                $entity->{$this->primaryKeyProperty} = $this->driver->lastInsertId();
            }
        }
    }

    public function delete(object $entity): void
    {
        if ($this->primaryKeyProperty === null || !property_exists($entity, $this->primaryKeyProperty)) {
            throw new \InvalidArgumentException("Entity must have an id to delete.");
        }

        $this->driver->query(
            "DELETE FROM {$this->table} WHERE {$this->primaryKeyColumn} = :id",
            ['id' => $entity->{$this->primaryKeyProperty}]
        );
    }

    private function mapToEntity(array $data): object
    {
        $entity = new $this->entityClass();
        foreach ($data as $column => $value) {
            if (isset($this->columnToPropertyMap[$column])) {
                $property = $this->columnToPropertyMap[$column];
                if (property_exists($entity, $property)) {
                    $entity->$property = $value;
                }
            }
        }
        return $entity;
    }

    public function findOneBy(array $criteria): ?object
    {
        $conditions = [];
        $params = [];
        foreach ($criteria as $property => $value) {
            $column = $this->propertyToColumnMap[$property] ?? $property;
            $conditions[] = "$column = :$property";
            $params[$property] = $value;
        }
        $where = implode(' AND ', $conditions);
        $sql = "SELECT * FROM {$this->table} WHERE $where LIMIT 1";
        $stmt = $this->driver->query($sql, $params);
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $data ? $this->mapToEntity($data) : null;
    }
}
