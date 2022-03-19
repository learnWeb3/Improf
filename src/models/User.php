<?php

namespace Application\Model;

use Application\Core\Params;
use Application\Error\DatabaseError;
use Application\Error\NotFoundError;
use Application\Core\CollectionRecord;
use Application\Core\JWTWrapper;
use Application\Core\Role;
use Application\Error\ForbiddenError;

class User extends ApplicationRecord
{

    public static function construct(array $fields)
    {
        $user = new self;
        foreach ($fields as $field_name => $field_value) {
            $user->$field_name = $field_value;
        }

        $user->has_one('wallet');
        $user->has_many('formations');
        $user->has_many("schedules", "formation");
        $user->has_one('zoom_credential');
        $user->has_many('reviews');
        $user->has_many('appointments');

        // virtual attributes
        $user->is_admin = Role::getRoleName($user->role) === "admin";

        return $user;
    }


    public static function authenticate(Params $params): self|null
    {
        try {
            $user = User::select(["*"])
                ->from('users')
                ->where(['email' => $params->email])
                ->exec()
                ->fetchAll();
            $password_check = password_verify($params->password, $user[0]->password);
        } catch (\Throwable $th) {
            throw new DatabaseError([$th->getMessage()]);
        }

        if ($password_check && isset($user[0])) {
            $user = $user[0];
            return $user;
        } else {
            throw new NotFoundError();
        }
    }

    public static function getUserFromToken(string $jwt_token): self
    {
        $jwt_token = JWTWrapper::decode($jwt_token);
        $jwt_token->verifyIssClaim();

        $user = User::getOne(new Params([
            "id" => $jwt_token->getSub()
        ]));

        return $user;
    }


    public static function getAll(Params $params, int $limit, int $offset, string $previous_page, string  $next_page): CollectionRecord
    {
        try {
            $filters = get_object_vars($params);
            $users =  User::select(["*"])
                ->from('users')
                ->where($filters)
                ->limit($limit)
                ->offset($offset)
                ->exec()
                ->fetchAll();

            $collection = new CollectionRecord([
                "users" => $users,
                "previous" => $previous_page,
                "next" => $next_page
            ]);
            return $collection;
        } catch (\Throwable $th) {
            throw new DatabaseError([$th->getMessage()]);
        }
    }

    public static function getOne(Params $params): self
    {
        try {
            $user = User::select(["*"])
                ->from('users')
                ->where(['id' => $params->id])
                ->exec()
                ->fetchAll();
        } catch (NotFoundError $th) {
            throw $th;
        } catch (\Throwable $th) {
            throw new DatabaseError([$th->getMessage()]);
        }

        if (isset($user[0])) {
            $user = $user[0];
            $user->wallet = $user->wallet();
            $user->formations = $user->formations();
            $user->reviews = $user->reviews();
            $user->schedules = $user->schedules();

            $user->appointments = $user->appointments();
            $user->zoom_credential = $user->zoom_credential();
            return $user;
        } else {
            self::handleRecordNotFound($params);
        }
    }

    public static function findOne(Params $params, User|null $current_user)
    {
        $user = self::getOne($params);
        if (is_null($current_user) || !$current_user->is_admin && $current_user->id !== $params->id) {
            unset($user->appointments);
            unset($user->zoom_credential);
            unset($user->password);
        }
        return $user;
    }


    public static function register(Params $params): self
    {

        try {
            $params->password = password_hash($params->password, PASSWORD_BCRYPT);
            $user = User::create(get_object_vars($params))
                ->exec()
                ->lastInsert()
                ->exec()
                ->fetchAll()[0];

            $wallet_params = new Params([
                "balance" => DEFAULT_USER_CREDIT_BALANCE,
                "id_user" => $user->id
            ]);

            Wallet::register($wallet_params);

            $user->wallet = $user->wallet();
            return $user;
        } catch (\Throwable $th) {
            throw new DatabaseError([$th->getMessage()]);
        }
    }

    public static function amend(Params $params, User|null $current_user): self
    {
        // related objects 
        $user = User::getOne(new Params(["id" => $params->id]));

        // errors handling
        $errors = [];
        is_null($current_user) && $errors[] =  "user must be authenticated";
        !is_null($current_user) && $user->id_user === $current_user->id || !is_null($current_user) && !$current_user->is_admin && $errors[] = "user must be current authenticated user owner or admin";
        count($errors) > 0 &&   throw new ForbiddenError($errors);

        try {
            User::update(get_object_vars($params))
                ->where(['id' => $params->id])
                ->exec();
            $user = User::select(["*"])
                ->from('users')
                ->where(['id' => $params->id])
                ->exec()
                ->fetchAll()[0];
            return $user;
        } catch (\Throwable $th) {
            throw new DatabaseError([$th->getMessage()]);
        }
    }

    public static function remove(Params $params, User|null $current_user): void
    {
        // related objects 
        $user = User::getOne(new Params(["id" => $params->id]));

        // errors handling
        $errors = [];
        is_null($current_user) && $errors[] =  "user must be authenticated";
        !is_null($current_user) && $user->id_user === $current_user->id || !is_null($current_user) && !$current_user->is_admin && $errors[] = "user must be current authenticated user or admin";
        count($errors) > 0 &&   throw new ForbiddenError($errors);

        try {
            User::destroy()
                ->from('users')
                ->where(['id' => $params->id])
                ->exec();
        } catch (\Throwable $th) {
            throw new DatabaseError([$th->getMessage()]);
        }
    }
}
