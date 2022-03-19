<?php

namespace Application\Model;

use Application\Helper\ObjectUtils;
use Application\Helper\StringUtils;
use Application\Core\MySqlDbRecord;
use Application\Core\Params;
use Application\Error\NotFoundError;

class ApplicationRecord
{
    private array $has_many;
    private array $has_one;
    private array $belongs_to;
    private array $through;
    private array $as;

    public function __construct()
    {
        $this->has_many = [];
        $this->belongs_to = [];
        $this->through = [];
        $this->as = [];
        $this->has_one = [];
    }

    public static function __callStatic(string $name, $arguments)
    {
        $execution_context = get_called_class();
        $my_sql_record = new MySqlDbRecord($execution_context);
        return call_user_func_array([$my_sql_record, $name], $arguments);
    }

    protected function has_many(string $model_name, string| null $through = null, string|null $as = null): void
    {
        $this->has_many = [...$this->has_many, $model_name];
        $this->through = [...$this->through, $through];
        $this->as = [...$this->as, $as];
    }

    protected function has_one(string $model_name): void
    {
        $this->has_one = [...$this->has_one, $model_name];
    }

    protected function belongs_to(string $model_name): void
    {
        $this->belongs_to = [...$this->belongs_to, $model_name];
    }

    public function __call(string $name, $arguments)
    {
        $model_class_name = "Application\\Model\\" . StringUtils::toModelName($name);
        $table_name =  StringUtils::toTableName($name);
        if (in_array($name, $this->belongs_to)) {
            $foreign_key = StringUtils::toForeignKey($name);
            return $model_class_name::select(['*'])
                ->from($table_name)
                ->where(['id' => $this->$foreign_key])
                ->exec()
                ->fetchAll($this->getApplicationRecordName($name))[0];
        } else if (in_array($name, $this->has_one)) {
            $foreign_key = StringUtils::toForeignKey(get_called_class());
            return $model_class_name::select(['*'])
                ->from($table_name)
                ->where([$foreign_key => $this->id])
                ->exec()
                ->fetchAll($this->getApplicationRecordName($name))[0];
        } else if (in_array($name, $this->has_many) && is_null($this->getJoinedAssoc($name))) {
            $foreign_key = StringUtils::toForeignKey(get_called_class());
            return $model_class_name::select(['*'])
                ->from($table_name)
                ->where([$foreign_key => $this->id])
                ->exec()
                ->fetchAll($this->getApplicationRecordName($name));
        } else if (in_array($name, $this->has_many) && !is_null($this->getJoinedAssoc($name))) {
            $joined_assoc = $this->getJoinedAssoc($name);
            $joined_assoc_model_name = StringUtils::toModelName($joined_assoc);
            $joined_assoc_table_name = StringUtils::toTableName($joined_assoc);
            $foreign_key = StringUtils::toForeignKey($joined_assoc_model_name);
            return $model_class_name::select(["$table_name.*"])
                ->from($table_name)
                ->join($joined_assoc_table_name, "$table_name.$foreign_key", "$joined_assoc_table_name.id")
                ->exec()
                ->fetchAll($this->getApplicationRecordName($name));
        }
    }

    protected function getJoinedAssoc(string $name): null|string
    {
        $looked_up_index = array_search($name, $this->has_many);
        return $this->through[$looked_up_index];
    }

    protected function getApplicationRecordName(string $name): string
    {
        $looked_up_index = array_search($name, $this->as);
        return is_null($this->as[$looked_up_index]) ? "Application\Model\\" . StringUtils::toModelName($name) : "Application\Model\\" . StringUtils::toModelName($this->as[$looked_up_index]);
    }

    public function __toString()
    {
        $public_vars = ObjectUtils::getPublicVars($this);
        return json_encode($public_vars);
    }

    protected static function handleRecordNotFound(Params $params)
    {
        $model_name = StringUtils::toModelName(get_called_class());
        $values = StringUtils::toQueryString(get_object_vars($params));
        $message = "$model_name not found using values $values";
        throw new NotFoundError([
            $message
        ]);
    }
}
