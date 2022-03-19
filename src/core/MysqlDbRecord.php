<?php

namespace Application\Core;

use Application\Helper\StringUtils;

class MySqlDbRecord implements IDatabaseCaller
{
    public array $values;
    public string $statement;
    public string $execution_context;

    private \PDO $connection;
    private \PDOStatement $executed_statement;

    public function __construct(string $execution_context)
    {
        $this->values = [];
        $this->statement = "";
        $this->execution_context = $execution_context;
        $this->connection = $this->connect();
        $this->table = $this->getTableName($execution_context);
    }

    // select(['id', 'firstname', 'lastname'])
    public function select(array $fields): self
    {
        $columns = implode(", ", $fields);

        $this->statement = "SELECT $columns";

        return $this;
    }

    // from($table_name)
    public function from(string $table_name): self
    {
        $this->table_name = $table_name;

        $this->statement = $this->statement . " FROM $table_name";

        return $this;
    }


    // where(['id'=>3, 'firtsname'=>'toto'])
    public function where(array $params): self
    {
        if (count($params) > 0) {
            $filters = [];
            foreach ($params as $column => $value) {
                $filters[] = "$column=?";
            }
            $filters = implode(" AND ", $filters);
            $statement = " WHERE $filters";
            $this->statement = $this->statement . $statement;
            $this->params = $params;
            $this->values = [...$this->values, ...array_values($params)];
        }
        return $this;
    }

    // whereIn('id', ['id', 'firstname', 'lastname'])
    public function whereIn(string $column, array $values): self
    {

        $filters = implode(",", array_map(function ($el) {
            return "?";
        }, $values));

        $statement = " WHERE $column IN ($filters)";

        $this->statement =  $this->statement . $statement;
        $this->values = [...$this->values, ...array_values($values)];
        return $this;
    }

    // create(['lastname'=>'test', firstname=>'test'])
    public function create(array $params): self
    {
        $params = $this->sanitizeParameters($params);

        $table_name = $this->table;
        $columns = implode(',', array_keys($params));
        $values = implode(',', array_map(function ($value) {
            return "?";
        }, array_values($params)));

        $statement = "INSERT INTO $table_name ($columns) VALUES ($values)";

        $this->statement = $statement;
        $this->values = [...$this->values, ...array_values($params)];
        return $this;
    }
    // update(['lastname'=>'test', firstname=>'test'])
    public function update(array $params): self
    {
        $params = $this->sanitizeParameters($params);

        $table_name = $this->table;
        $columns = [];

        foreach ($params as $key => $value) {
            $columns[] = "$key=?";
        }
        $columns_string = implode(",", $columns);

        $statement = "UPDATE $table_name SET $columns_string";

        $this->statement = $statement;
        $this->values = [...$this->values, ...array_values($params)];

        return $this;
    }

    public function destroy(): self
    {
        $statement = "DELETE ";

        $this->statement = $statement;

        return $this;
    }

    public function limit(int $limit): self
    {
        $statement = " LIMIT $limit";

        $this->statement .= $statement;

        return $this;
    }
    public function offset(int $offset): self
    {
        $statement = " OFFSET $offset";

        $this->statement .= $statement;

        return $this;
    }

    //join('posts', 'id', 'id_user')
    public function join(string $joined_table, string $primary_key, string $foreign_key): self
    {
        $statement = " JOIN $joined_table ON $primary_key=$foreign_key";

        $this->statement = $this->statement . $statement;

        return $this;
    }

    public function  groupBy(array $groups): self
    {
        $groups = implode(',', $groups);
        $statement = " GROUP BY $groups";

        $this->statement = $this->statement . $statement;

        return $this;
    }
    // lastInsert()
    public function lastInsert(): self
    {
        $table_name = $this->table;

        $statement = "SELECT * FROM $table_name WHERE id=LAST_INSERT_ID()";

        $this->statement = $statement;

        return $this;
    }

    public function fetchAll($model_name = null, $fetch_mode = \PDO::FETCH_ASSOC): array
    {
        $final_result = [];

        while ($row = $this->executed_statement->fetch($fetch_mode)) {
            $application_record = is_null($model_name) ? $this->execution_context::construct($row) : $model_name::construct($row);
            $final_result = [...$final_result, $application_record];
        }

        return $final_result;
    }

    public function exec(): self
    {
        $pdo_stmt = $this->connection->prepare($this->statement);

        count($this->values) > 0 ? $pdo_stmt->execute($this->values) : $pdo_stmt->execute();

        $this->executed_statement = $pdo_stmt;
        $this->values = [];
        $this->statement = "";

        return $this;
    }

    private function connect(): \PDO
    {
        $host = DB_HOST;
        $db_name = DB_NAME;
        $charset = DB_CHARSET;
        $dsn = "mysql:host=$host;dbname=$db_name;charset=$charset";
        $username = DB_USERNAME;
        $password = DB_PASSWORD;

        $options = [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_CASE => \PDO::CASE_NATURAL,
            \PDO::ATTR_ORACLE_NULLS => \PDO::NULL_EMPTY_STRING,
            \PDO::ATTR_EMULATE_PREPARES => false,
            \PDO::ATTR_STRINGIFY_FETCHES => false
        ];

        return new \PDO($dsn, $username, $password, $options);
    }

    private function sanitizeParameters(array $params): array
    {
        $excluded_keys = ['id'];

        return array_filter($params, function ($param) use ($excluded_keys) {
            return !in_array($param, $excluded_keys);
        });
    }

    private function getTableName($execution_context): string
    {
        return StringUtils::toTableName($execution_context);
    }
}
