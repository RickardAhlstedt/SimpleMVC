<?php

namespace SimpleMVC\Database;

class QueryBuilder
{
    private DatabaseInterface $driver;
    private string $entityClass;
    private string $table;
    private array $criteria = [];
    private ?int $limit = null;
    private ?int $offset = null;
    private array $orderBy = [];
    private array $select = ['*'];
    private ?string $groupBy = null;
    private array $joins = [];
    private ?string $having = null;
    private ?array $customConditions = null;
    private ?string $locale = null;

    public function __construct(DatabaseInterface $driver, string $entityClass)
    {
        $this->driver = $driver;
        $this->entityClass = $entityClass;
        $table = strtolower((new \ReflectionClass($entityClass))->getShortName()) . 's';
        $this->table = $table;
    }

    public function setColumns(array $columns): self
    {
        $this->select = $columns;
        return $this;
    }

    public function addConditionWhere(array $criteria): self
    {
        $this->criteria = array_merge($this->criteria, $criteria);
        return $this;
    }

    public function addConditionParam(string $condition, array $params): self
    {
        // Store custom condition and params for later SQL generation
        if (!isset($this->customConditions)) {
            $this->customConditions = [];
        }
        $this->customConditions[] = ['condition' => $condition, 'params' => $params];
        return $this;
    }

    public function setLimit(int $limit): self
    {
        $this->limit = $limit;
        return $this;
    }

    public function setOffset(int $offset): self
    {
        $this->offset = $offset;
        return $this;
    }

    public function setOrderBy(array $orderBy): self
    {
        $this->orderBy = array_merge($this->orderBy, $orderBy);
        return $this;
    }

    public function setGroupBy(string $groupBy): self
    {
        $this->groupBy = $groupBy;
        return $this;
    }

    public function addJoin(string $table, string $on, string $type = 'INNER'): self
    {
        $this->joins[] = [$type, $table, $on];
        return $this;
    }

    public function setHaving(string $having): self
    {
        $this->having = $having;
        return $this;
    }

    public function setLocale(string $locale): self
    {
        $this->locale = $locale;
        return $this;
    }

    public function load(): array
    {
        $baseTable = $this->table;
        $sql = "SELECT ". implode(', ', $this->select) ." FROM {$baseTable}";

        // Handle localization
        if ($this->locale && in_array(\SimpleMVC\Model\Localized::class, class_implements($this->entityClass))) {
            $localeTable = $this->entityClass::getLocaleTable($this->locale);
            $sql .= " LEFT JOIN {$localeTable} ON {$baseTable}.id = {$localeTable}.entity_id";
        }

        $params = [];
        $where = [];
        if ($this->criteria) {
            $conditions = [];
            foreach ($this->criteria as $column => $value) {
                $conditions[] = "$column = :$column";
                $params[$column] = $value;
            }
        }

        if ($this->customConditions) {
            foreach ($this->customConditions as $cond) {
                $where[] = $cond['condition'];
                $params = array_merge($params, $cond['params']);
            }
        }

        if ($where) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }
        if ($this->orderBy) {
            $orders = array_map(fn ($o) => "{$o[0]} {$o[1]}", $this->orderBy);
            $sql .= " ORDER BY " . implode(', ', $orders);
        }
        if ($this->limit !== null) {
            $sql .= " LIMIT " . $this->limit;
        }
        if ($this->offset !== null) {
            $sql .= " OFFSET " . $this->offset;
        }
        foreach ($this->joins as [$type, $table, $on]) {
            $sql .= " $type JOIN $table ON $on";
        }
        if ($this->groupBy) {
            $sql .= " GROUP BY " . $this->groupBy;
        }
        if ($this->having) {
            $sql .= " HAVING " . $this->having;
        }
        $stmt = $this->driver->query($sql, $params);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Map to entities
        return array_map(fn ($row) => $this->mapToEntity($row), $rows);
    }

    public function current(): ?object
    {
        $results = $this->load();
        return $results[0] ?? null;
    }

    public function first(): ?object
    {
        $this->setLimit(1);
        $results = $this->load();
        return $results[0] ?? null;
    }

    private function mapToEntity(array $data): object
    {
        $entity = new $this->entityClass();
        foreach ($data as $column => $value) {
            if (property_exists($entity, $column)) {
                $entity->$column = $value;
            }
        }
        return $entity;
    }

}
