<?php

namespace Application\Core;

// Must implement IDatabaseCaller if you need to use database type which is not supported by PDO aka (MongoDb)

interface IDatabaseCaller
{
    public function __construct(string $execution_context);
    // select(['id', 'firstname', 'lastname'])
    public function select(array $fields): self;
    // from($table_name)
    public function from(string $table_name): self;
    // where(['id'=>3, 'firtsname'=>'toto'])
    public function where(array $params): self;
    // whereIn('id', ['id', 'firstname', 'lastname'])
    public function whereIn(string $column, array $values): self;
    // create(['lastname'=>'test', firstname=>'test'])
    public function create(array $params): self;
    // update(['lastname'=>'test', firstname=>'test'])
    public function update(array $params): self;
    public function destroy(): self;
    public function limit(int $limit): self;
    public function offset(int $offset): self;
    //join('posts', 'id', 'id_user')
    public function join(string $joined_table, string $primary_key, string $foreign_key): self;
    public function  groupBy(array $groups): self;
    public function lastInsert(): self;
    public function fetchAll($model_name = null): array;
    public function exec(): self;
}
