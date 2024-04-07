<?php

namespace Hnqca\Database;

use PDO;

class Database
{
    private ?PDO    $pdo;               // The PDO instance for database manipulation
    private ?array  $placeholders = []; // Array to store placeholders and their values

    protected string $table   = "";
    protected string $where   = "";
    protected string $groupBy = "";
    protected string $orderBy = "";
    protected string $limit   = "";
    protected string $offset  = "";

    /**
     * Constructor of the Database class.
     *
     * @param Connection $connection Connection object to establish the database connection.
     */
    public function __construct(Connection $connection)
    {
        $this->pdo = $connection->getConnection();
    }

    public static function cleanParam(string|int|float|bool $param)
    {
        return is_string($param) ? htmlspecialchars(strip_tags($param)) : $param;
    }

    public function from(string $table)
    {
        $this->placeholders = [];

        $this->table = self::cleanParam($table);

        return $this;
    }

    public function where(string $condition)
    {
        $pattern = '/(\w+)\s*(!=|>=|<=|=|>|<|<>)([^,]+)/';
        preg_match_all($pattern, $condition, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $column   = self::cleanParam(trim($match[1]));
            $operator = trim($match[2]);
            $value    = trim($match[3]);

            $clauses[]            = "`{$column}` {$operator} :{$column}_where";
            $this->placeholders[] = [":{$column}_where", $value];
        }

        $this->where = "WHERE " . implode(' AND ', $clauses);

        return $this;
    }

    public function groupBy(array $data)
    {
        $columns = implode(', ',  array_map('self::cleanParam', $data));

        $this->groupBy = "GROUP BY {$columns}";

        return $this;
    }

    public function orderBy(array $data)
    {
        foreach ($data as $column => $value) {

            $column = self::cleanParam($column);
            $value  = self::cleanParam($value);

            $clauses[] = "{$column} {$value}";
        }

        $this->orderBy = 'ORDER BY ' . implode(', ', $clauses);

        return $this;
    }

    public function limit(int $limit, int|null $page = null)
    {
        $this->limit = "LIMIT {$limit}";

        if ($page and $page > 1) {
            $this->offset(($page - 1) * $limit);
        }

        return $this;
    }

    public function offset(int $rows)
    {
        $this->offset = "OFFSET {$rows}";

        return $this;
    }

    public function select(bool $all = false, array $columns = [])
    {
        if (empty($columns)) {
            $columns = "*";
        } else {
            $columns = array_map('self::cleanParam', $columns);
            $columns = implode(", ", $columns);
        }

        $sql = "SELECT {$columns} FROM {$this->table} {$this->where} {$this->groupBy} {$this->orderBy} {$this->limit} {$this->offset}";

        $stmt = $this->pdo->prepare($sql);
        $stmt = $this->setBindValues($stmt, $this->placeholders);
        $stmt->execute();

        $typeFetch = [
            true  => "fetchAll",
            false => "fetch"
        ];

        $data = $stmt->{$typeFetch[$all]}();

        if (!$data) {
            return ($all ? [] : null);
        }

        return $data;
    }


    public function insert(array $data)
    {
        $columns      = implode(', ',  array_map('self::cleanParam', array_keys($data)));
        $placeholders = ':' . str_replace(" ", ":", $columns);

        foreach ($data as $column => $value) {
            $this->placeholders[] = [":{$column}", $value];
        }

        $sql  = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";
        $stmt = $this->pdo->prepare($sql);

        $stmt = $this->setBindValues($stmt, $this->placeholders);
        $stmt->execute();

        return $this->pdo->lastInsertId();
    }

    public function delete()
    {
        $sql  = "DELETE FROM {$this->table} {$this->where} {$this->groupBy} {$this->orderBy} {$this->limit}";
        $stmt = $this->pdo->prepare($sql);

        $stmt = $this->setBindValues($stmt, $this->placeholders);

        return $stmt->execute();
    }

    public function update(array $data)
    {
        $sets = implode(', ', array_map(function ($column, $value) {

            $column = self::cleanParam($column);
            $value  = self::cleanParam($value);

            $this->placeholders[] = [":{$column}", $value];

            return "{$column} = :{$column}";
        }, array_keys($data), $data));

        $sql  = "UPDATE {$this->table} SET {$sets} {$this->where} {$this->groupBy} {$this->orderBy} {$this->limit}";
        $stmt = $this->pdo->prepare($sql);

        $stmt = $this->setBindValues($stmt, $this->placeholders);

        return $stmt->execute();
    }

    public function count(string $column = "*")
    {
        return $this->aggregation($column, "COUNT");
    }

    public function sum(string $column)
    {
        return $this->aggregation($column, "SUM");
    }

    public function avg(string $column)
    {
        return $this->aggregation($column, "AVG");
    }

    public function min(string $column)
    {
        return $this->aggregation($column, "MIN");
    }

    public function max(string $column)
    {
        return $this->aggregation($column, "MAX");
    }

    private function aggregation(string $column, string $operation)
    {
        $column = self::cleanParam($column);

        $sql  = "SELECT {$operation}({$column}) AS total FROM {$this->table} {$this->where} {$this->groupBy}";
        $stmt = $this->pdo->prepare($sql);

        $stmt = $this->setBindValues($stmt, $this->placeholders);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_OBJ)->total;
    }

    private function setBindValues($stmt, array $data = [])
    {
        if (empty($data)) {
            return $stmt;
        }

        foreach ($data as $value) {
            $stmt->bindValue($value[0], self::cleanParam($value[1]));
        }

        return $stmt;
    }
}