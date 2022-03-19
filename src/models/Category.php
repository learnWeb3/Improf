<?php

namespace Application\Model;

use Application\Core\Params;
use Application\Error\DatabaseError;
use Application\Error\NotFoundError;
use Application\Core\CollectionRecord;
use Application\Error\ForbiddenError;

class Category extends ApplicationRecord
{
    public static function construct(array $fields)
    {
        $category = new self;
        foreach ($fields as $field_name => $field_value) {
            $category->$field_name = $field_value;
        }
        $category->has_many('formations');
        return $category;
    }

    public static function getAll(Params $params, int $limit, int $offset, string $previous_page, string  $next_page): CollectionRecord
    {
        try {
            $filters = get_object_vars($params);
            $categories =  Category::select(["*"])
                ->from('categories')
                ->where($filters)
                ->limit($limit)
                ->offset($offset)
                ->exec()
                ->fetchAll();
            $collection = new CollectionRecord([
                "categories" => $categories,
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
            $category = Category::select(["*"])
                ->from('categories')
                ->where(['id' => $params->id])
                ->exec()
                ->fetchAll();
        } catch (NotFoundError $th) {
            throw $th;
        } catch (\Throwable $th) {
            throw new DatabaseError([$th->getMessage()]);
        }

        if (isset($category[0])) {
            $category = $category[0];
            $category->formations = $category->formations();
            return $category;
        } else {
            self::handleRecordNotFound($params);
        }
    }

    public static function register(Params $params, User|null $current_user): self
    {
        // error handling;
        $errors = [];
        is_null($current_user) && $errors[] =  "user must be authenticated";
        !is_null($current_user) && !$current_user->is_admin &&  $errors[] =  "user must be admin";
        count($errors) > 0 &&  throw new ForbiddenError($errors);

        try {
            $category = Category::create(get_object_vars($params))
                ->exec()
                ->lastInsert()
                ->exec()
                ->fetchAll()[0];
            return $category;
        } catch (\Throwable $th) {
            throw new DatabaseError([$th->getMessage()]);
        }
    }

    public static function amend(Params $params, User|null $current_user): self
    {
        // error handling;
        $errors = [];
        is_null($current_user) && $errors[] =  "user must be authenticated";
        !is_null($current_user) && !$current_user->is_admin &&  $errors[] =  "user must be admin";
        count($errors) > 0 &&  throw new ForbiddenError($errors);

        try {
            Category::update(get_object_vars($params))
                ->where(['id' => $params->id])
                ->exec();
            $category = Category::select(["*"])
                ->from('categories')
                ->where(['id' => $params->id])
                ->exec()
                ->fetchAll()[0];
            return $category;
        } catch (\Throwable $th) {
            throw new DatabaseError([$th->getMessage()]);
        }
    }
    public static function remove(Params $params, User|null $current_user): void
    {
        // error handling;
        $errors = [];
        is_null($current_user) && $errors[] =  "user must be authenticated";
        !is_null($current_user) && !$current_user->is_admin &&  $errors[] =  "user must be admin";
        count($errors) > 0 &&  throw new ForbiddenError($errors);

        try {
            Category::destroy()
                ->from('categories')
                ->where(['id' => $params->id])
                ->exec();
        } catch (\Throwable $th) {
            throw new DatabaseError([$th->getMessage()]);
        }
    }
}
