<?php

namespace Application\Model;

use Application\Core\Params;
use Application\Error\DatabaseError;

class Wallet extends ApplicationRecord
{
    public bool $is_active;

    public static function construct(array $fields)
    {
        // attibutes initialization
        $wallet = new self;
        foreach ($fields as $field_name => $field_value) {
            $wallet->$field_name = $field_value;
        }
        // associations
        $wallet->belongs_to('user');
        // virtual attributes
        $wallet->setIs_active($wallet->balance > 0);
        return $wallet;
    }


    /**
     * Set the value of is_active
     *
     * @return  self
     */
    public function setIs_active(bool $is_active)
    {
        $this->is_active = $is_active;

        return $this;
    }

    public static function register(Params $params): self
    {
        // related objects
        $user = User::getOne(new Params([
            "id" => $params->id_user
        ]));
        try {
            return Wallet::create(get_object_vars($params))
                ->exec()
                ->lastInsert()
                ->exec()
                ->fetchAll()[0];
        } catch (\Throwable $th) {
            throw new DatabaseError([$th->getMessage()]);
        }
    }

    public static function amend(Params $params): self
    {
        try {
            Wallet::update(get_object_vars($params))
                ->where(['id' => $params->id])
                ->exec();
            $wallet = Wallet::select(["*"])
                ->from('wallets')
                ->where(['id' => $params->id])
                ->exec()
                ->fetchAll()[0];
            return $wallet;
        } catch (\Throwable $th) {
            throw new DatabaseError([$th->getMessage()]);
        }
    }


    public static function remove(Params $params): void
    {
        try {
            Wallet::destroy()
                ->from('wallets')
                ->where(['id' => $params->id])
                ->exec();
        } catch (\Throwable $th) {
            throw new DatabaseError([$th->getMessage()]);
        }
    }
}
