<?php

namespace Application\Model;

use Application\Core\Params;
use Application\Error\DatabaseError;
use Application\Error\ForbiddenError;
use Application\Service\ZoomAPIMeeting;

class Appointment extends ApplicationRecord
{
    public static function construct(array $fields)
    {
        $appointment = new self;
        foreach ($fields as $field_name => $field_value) {
            $appointment->$field_name = $field_value;
        }
        $appointment->belongs_to('user');
        $appointment->belongs_to('schedule');
        $appointment->has_one('review');
        return $appointment;
    }

    public static function getOne(Params $params): self
    {
        try {
            $appointment = Appointment::select(["*"])
                ->from('appointments')
                ->where(['id' => $params->id])
                ->exec()
                ->fetchAll();
        } catch (\Throwable $th) {
            throw new DatabaseError([$th->getMessage()]);
        }

        if (isset($appointment[0])) {
            $appointment = $appointment[0];
            $appointment->user = $appointment->user();
            return $appointment;
        } else {
            self::handleRecordNotFound($params);
        }
    }

    public static function register(Params $params, User|null $current_user): self
    {
        // related objects
        $schedule = Schedule::getOne(new Params([
            "id" => $params->id_schedule
        ]));
        $user = User::getOne(new Params([
            "id" => $current_user->id
        ]));
        $user_wallet = $user->wallet;

        // errror handling
        $errors = [];
        is_null($current_user) && $errors[] = "user must be authenticated";
        !$schedule->is_available && $errors[] = "related schedule must be available";
        !$schedule->is_future && $errors[] = "related schedule must be future";
        count($errors) > 0 && throw new ForbiddenError($errors);

        try {
            Wallet::amend(new Params([
                "balance" => $user_wallet->balance - CREDIT_APPOINTMENT_COST,
                "id" => $user_wallet->id
            ]));

            $params->id_user = $current_user->id;

            $appointment = Appointment::create(get_object_vars($params))
                ->exec()
                ->lastInsert()
                ->exec()
                ->fetchAll()[0];

            return $appointment;
        } catch (\Throwable $th) {
            throw new DatabaseError([$th->getMessage()]);
        }
    }

    public static function amend(Params $params, User|null $current_user): self
    {
        // related objects 
        $appointment = Appointment::getOne(
            new Params([
                "id" => $params->id
            ])
        );
        $appointment_user = $appointment->user();
        // error handling
        $errors = [];
        is_null($current_user) && $errors[] = "user must be authenticated";
        !is_null($current_user) && $appointment_user->id !== $current_user->id || !is_null($current_user) && !$current_user->is_admin && $errors[] = "user must be appointment owner or admin";
        count($errors) > 0 && throw new ForbiddenError($errors);

        try {
            Appointment::update(get_object_vars($params))
                ->where(['id' => $params->id])
                ->exec();
            $appointment = Appointment::select(["*"])
                ->from('appointments')
                ->where(['id' => $params->id])
                ->exec()
                ->fetchAll()[0];
            return $appointment;
        } catch (\Throwable $th) {
            throw new DatabaseError([$th->getMessage()]);
        }
    }
    public static function remove(Params $params,  User|null $current_user): void
    {
        // related objects 
        $appointment = Appointment::getOne(
            new Params([
                "id" => $params->id
            ])
        );
        $appointment_user = $appointment->user();
        // error handling
        $errors = [];
        is_null($current_user) && $errors[] = "user must be authenticated";
        !is_null($current_user) && $appointment_user->id !== $current_user->id || !is_null($current_user) && !$current_user->is_admin && $errors[] = "user must be appointment owner or admin";
        count($errors) > 0 && throw new ForbiddenError($errors);

        try {
            Appointment::destroy()
                ->from('appointments')
                ->where(['id' => $params->id])
                ->exec();
        } catch (\Throwable $th) {
            throw new DatabaseError([$th->getMessage()]);
        }
    }
}
