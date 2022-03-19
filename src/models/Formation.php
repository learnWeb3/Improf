<?php

namespace Application\Model;

use Application\Core\Params;
use Application\Error\DatabaseError;
use Application\Error\NotFoundError;
use Application\Core\CollectionRecord;
use Application\Error\ForbiddenError;
use Application\Service\FacebookAPIDialog;

class Formation extends ApplicationRecord
{
    public static function construct(array $fields)
    {
        $formation = new self;
        foreach ($fields as $field_name => $field_value) {
            $formation->$field_name = $field_value;
        }
        $formation->belongs_to('user');
        $formation->belongs_to('category');
        $formation->has_many("schedules");
        $formation->has_many('appointments', 'schedules');
        $formation->has_many('scheduleDetails', 'schedules');
        $formation->has_many("meetings", "schedules");
        $formation->facebook_share_url = FacebookAPIDialog::requestShareDialogUrl(
            rawurlencode(ABSOLUTE_ROOT_PATH . "/formations/" . $formation->id),
            rawurlencode(ABSOLUTE_ROOT_PATH . "/formations/" . $formation->id)
        );
        return $formation;
    }
    public static function getAll(Params $params, int $limit, int $offset, string $previous_page, string  $next_page): CollectionRecord
    {
        try {
            $filters = get_object_vars($params);
            $formations =  Formation::select(["*"])
                ->from('formations')
                ->where($filters)
                ->limit($limit)
                ->offset($offset)
                ->exec()
                ->fetchAll();

            $formations = array_map(function ($formation) {
                $formation->schedules = array_map(function ($schedule) {
                    $schedule->schedule_details = $schedule->scheduleDetails();
                    $schedule->meeting = $schedule->meeting();
                    return $schedule;
                }, $formation->schedules());
                $formation->category = $formation->category();
                $formation->user = $formation->user();
                return $formation;
            }, $formations);

            $collection = new CollectionRecord([
                "formations" => $formations,
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
            $formation = Formation::select(["*"])
                ->from('formations')
                ->where(['id' => $params->id])
                ->exec()
                ->fetchAll();
        } catch (NotFoundError $th) {
            throw $th;
        } catch (\Throwable $th) {
            throw new DatabaseError([$th->getMessage()]);
        }
        if (isset($formation[0])) {
            $formation = $formation[0];
            //schedules, schedule details, appointments, reviews, category, user
            $formation->schedules = array_map(function ($schedule) {
                $schedule->schedule_details = $schedule->scheduleDetails();
                $schedule->meeting = $schedule->meeting();
                $schedule->appointments = array_map(function ($appointment) {
                    $appointment->review = $appointment->review()[0];
                    return $appointment;
                }, $schedule->appointments());
                return $schedule;
            },  $formation->schedules());

            $formation->category = $formation->category();
            $formation->user = $formation->user();
            return $formation;
        } else {
            self::handleRecordNotFound($params);
        }
    }

    public static function findOne(Params $params, User|null $current_user)
    {
        $formation = self::getOne($params);
        if (is_null($current_user) || !$current_user->is_admin && $current_user->id !== $formation->user->id) {
            $formation->schedules = array_map(function ($schedule) use ($current_user) {
                $schedule->appointments = Appointment::select(['*'])
                    ->from('appointments')
                    ->where([
                        'id_user' => $current_user->id,
                        'id_schedule' => $schedule->id
                    ])
                    ->exec()
                    ->fetchAll();
                $schedule->meeting = count($schedule->appointments) > 0 && Meeting::select(['*'])
                    ->from('meetings')
                    ->where([
                        'id_schedule' => $schedule->id
                    ])
                    ->exec()
                    ->fetchAll();
                return $schedule;
            }, $formation->schedules);
            unset($formation->user->password);
        }
        return $formation;
    }

    public static function register(Params $params, User|null $current_user): self
    {
        // error handling
        $errors = [];
        is_null($current_user) && $errors[] =  "user must be authenticated";
        count($errors) > 0 &&  throw new ForbiddenError($errors);

        try {
            $params->id_user = $current_user->id;
            $category = Category::getOne(new Params([
                "id" => $params->id_category
            ]));
            $formation = Formation::create(get_object_vars($params))
                ->exec()
                ->lastInsert()
                ->exec()
                ->fetchAll()[0];
            $user = $formation->user();
            $user_wallet = $user->wallet();
            Wallet::amend(new Params(
                [
                    "balance" => $user_wallet->balance + CREDIT_FORMATION_CREATION,
                    "id" => $user_wallet->id
                ]
            ));
            return $formation;
        } catch (\Throwable $th) {
            throw new DatabaseError([$th->getMessage()]);
        }
    }

    public static function amend(Params $params, User|null $current_user): self
    {

        // related objects
        $formation = Formation::getOne(new Params(["id" => $params->id]));

        // error handling
        $errors = [];
        is_null($current_user) && $errors[] =   "user must be authenticated";
        !is_null($current_user) && $formation->id_user !== $current_user->id || !is_null($current_user) && !$current_user->is_admin && $errors[] =  "user must be formation owner or admin";
        count($errors) > 0 &&  throw new ForbiddenError($errors);

        try {
            Formation::update(get_object_vars($params))
                ->where(['id' => $params->id])
                ->exec();
            $formation = Formation::select(["*"])
                ->from('formations')
                ->where(['id' => $params->id])
                ->exec()
                ->fetchAll()[0];
            return $formation;
        } catch (\Throwable $th) {
            throw new DatabaseError([$th->getMessage()]);
        }
    }
    public static function remove(Params $params, User|null $current_user): void
    {
        // related objects
        $formation = Formation::getOne(new Params(["id" => $params->id]));

        // error handling
        $errors = [];
        is_null($current_user) && $errors[] =   "user must be authenticated";
        !is_null($current_user) && $formation->id_user !== $current_user->id || !is_null($current_user) && !$current_user->is_admin && $errors[] =  "user must be formation owner or admin";
        count($errors) > 0 &&  throw new ForbiddenError($errors);

        try {
            Formation::destroy()
                ->from('formations')
                ->where(['id' => $params->id])
                ->exec();
        } catch (\Throwable $th) {
            throw new DatabaseError([$th->getMessage()]);
        }
    }
}
