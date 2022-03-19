<?php

namespace Application\Model;

use Application\Core\Params;
use Application\Error\DatabaseError;
use Application\Error\ForbiddenError;
use Application\Error\NotFoundError;

class Review extends ApplicationRecord
{
    public static function construct(array $fields)
    {
        $review = new self;
        foreach ($fields as $field_name => $field_value) {
            $review->$field_name = $field_value;
        }
        $review->belongs_to('user');
        $review->belongs_to('appointment');
        return $review;
    }

    public static function getOne(Params $params): self
    {
        try {
            $review = Review::select(["*"])
                ->from('reviews')
                ->where(['id' => $params->id])
                ->exec()
                ->fetchAll();
        } catch (NotFoundError $th) {
            throw $th;
        } catch (\Throwable $th) {
            throw new DatabaseError([$th->getMessage()]);
        }

        if (isset($review[0])) {
            $review = $review[0];
            $review->user = $review->user();
            return $review;
        } else {
            self::handleRecordNotFound($params);
        }
    }

    public static function register(Params $params, User|null $current_user): self
    {
        // related objects
        $appointment = Appointment::getOne(new Params([
            "id" => $params->id_appointment
        ]));

        // errors handling
        $errors = [];
        is_null($current_user) && $errors[] =  "user must be authenticated";

        try {
            $params->id_user = $current_user->id;
            return Review::create(get_object_vars($params))
                ->exec()
                ->lastInsert()
                ->exec()
                ->fetchAll()[0];
        } catch (\Throwable $th) {
            throw new DatabaseError([$th->getMessage()]);
        }
    }

    public static function amend(Params $params, User|null $current_user): self
    {
        // related objects 
        $review = Review::getOne(new Params(["id" => $params->id]));

        // errors handling
        $errors = [];
        is_null($current_user) && $errors[] =  "user must be authenticated";
        !is_null($current_user) && $review->id_user === $current_user->id || !is_null($current_user) && !$current_user->is_admin && $errors[] = "user must be review owner or admin";
        count($errors) > 0 &&   throw new ForbiddenError($errors);

        try {
            Review::update(get_object_vars($params))
                ->where(['id' => $params->id])
                ->exec();
            $review = Review::select(["*"])
                ->from('reviews')
                ->where(['id' => $params->id])
                ->exec()
                ->fetchAll()[0];
            return $review;
        } catch (\Throwable $th) {
            throw new DatabaseError([$th->getMessage()]);
        }
    }


    public static function remove(Params $params, User|null $current_user): void
    {
        // related objects 
        $review = Review::getOne(new Params(["id" => $params->id]));

        // errors handling
        $errors = [];
        is_null($current_user) && $errors[] =  "user must be authenticated";
        !is_null($current_user) && $review->id_user === $current_user->id || !is_null($current_user) && !$current_user->is_admin && $errors[] = "user must be review owner or admin";
        count($errors) > 0 &&   throw new ForbiddenError($errors);

        try {
            Review::destroy()
                ->from('reviews')
                ->where(['id' => $params->id])
                ->exec();
        } catch (\Throwable $th) {
            throw new DatabaseError([$th->getMessage()]);
        }
    }
}
