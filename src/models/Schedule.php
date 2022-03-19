<?php

namespace Application\Model;

use Application\Core\Params;
use Application\Error\DatabaseError;
use Application\Error\ForbiddenError;
use Application\Error\NotFoundError;

class Schedule extends ApplicationRecord
{
    public bool $is_future;
    public bool $is_past;
    public bool $is_available;
    public int $duration;
    public int $available_capacity;

    public static function construct(array $fields)
    {
        // attributes initialization
        $schedule = new self;
        foreach ($fields as $field_name => $field_value) {
            $schedule->$field_name = $field_value;
        }

        // associations
        $schedule->belongs_to('formation');
        $schedule->has_many('scheduleDetails');
        $schedule->has_one("meeting");
        $schedule->has_many('appointments');
        $schedule->has_many('reviews', "appointments");

        // virtual attributes
        $schedule->setIs_future();
        $schedule->setIs_past();
        $schedule->setDuration();
        $schedule->setAvailable_capacity();
        $schedule->setIs_available();

        return $schedule;
    }


    public static function getOne(Params $params): self
    {
        try {
            $schedule = Schedule::select(["*"])
                ->from('schedules')
                ->where(['id' => $params->id])
                ->exec()
                ->fetchAll();
        } catch (NotFoundError $th) {
            throw $th;
        } catch (\Throwable $th) {
            throw new DatabaseError([$th->getMessage()]);
        }
        if (isset($schedule[0])) {
            $schedule =  $schedule[0];
            $schedule->meeting = $schedule->meeting();
            return  $schedule;
        } else {
            self::handleRecordNotFound($params);
        }
    }

    public static function register(Params $params,  User|null $current_user): self
    {
        // related objects 
        $formation = Formation::getOne(new Params(["id" => $params->id_formation]));

        // errors handling
        $errors = [];
        is_null($current_user) && $errors[] =  "user must be authenticated";
        !is_null($current_user) && $formation->id_user === $current_user->id || !is_null($current_user) && !$current_user->is_admin && $errors[] = "user must be formation owner or admin";
        count($errors) > 0 &&   throw new ForbiddenError($errors);

        try {
            $params->start = strftime("%F %T", $params->start);
            $params->end = strftime("%F %T", $params->end);
            $formation = Formation::getOne(new Params([
                "id" => $params->id_formation
            ]));
            return Schedule::create(get_object_vars($params))
                ->exec()
                ->lastInsert()
                ->exec()
                ->fetchAll()[0];
        } catch (\Throwable $th) {
            throw new DatabaseError([$th->getMessage()]);
        }
    }

    public static function amend(Params $params,  User|null $current_user): self
    {
        // related objects 
        $schedule = Schedule::getOne(new Params(["id" => $params->id]));
        $formation = $schedule->formation();

        // errors handling
        $errors = [];
        is_null($current_user) && $errors[] =  "user must be authenticated";
        !is_null($current_user) && $formation->id_user === $current_user->id || !is_null($current_user) && !$current_user->is_admin && $errors[] = "user must be formation owner or admin";
        count($errors) > 0 &&   throw new ForbiddenError($errors);

        try {
            isset($params->start) && $params->start = strftime("%F %T", $params->start);
            isset($params->end) && $params->end = strftime("%F %T", $params->end);
            Schedule::update(get_object_vars($params))
                ->where(['id' => $params->id])
                ->exec();
            $schedule = Schedule::select(["*"])
                ->from('schedules')
                ->where(['id' => $params->id])
                ->exec()
                ->fetchAll()[0];
            return $schedule;
        } catch (\Throwable $th) {
            throw new DatabaseError([$th->getMessage()]);
        }
    }


    public static function remove(Params $params,  User|null $current_user): void
    {
        // related objects 
        $schedule = Schedule::getOne(new Params(["id" => $params->id]));
        $formation = $schedule->formation();

        // errors handling
        $errors = [];
        is_null($current_user) && $errors[] =  "user must be authenticated";
        !is_null($current_user) && $formation->id_user === $current_user->id || !is_null($current_user) && !$current_user->is_admin && $errors[] = "user must be formation owner or admin";
        count($errors) > 0 &&   throw new ForbiddenError($errors);

        try {
            Schedule::destroy()
                ->from('schedules')
                ->where(['id' => $params->id])
                ->exec();
        } catch (\Throwable $th) {
            throw new DatabaseError([$th->getMessage()]);
        }
    }

    /**
     * Set the value of is_future
     *
     * @return  self
     */
    private function setIs_future()
    {
        $is_future = time() < strtotime($this->start);
        $this->is_future = $is_future;

        return $this;
    }

    /**
     * Set the value of is_past
     *
     * @return  self
     */
    private function setIs_past()
    {
        $is_past = time() > strtotime($this->end);
        $this->is_past = $is_past;

        return $this;
    }

    /**
     * Set the value of is_available
     *
     * @return  self
     */
    private function setIs_available()
    {
        $is_available = $this->available_capacity > 0;
        $this->is_available = $is_available;

        return $this;
    }

    /**
     * Set the value of duration
     *
     * @return  self
     */
    private function setDuration()
    {
        $duration = strtotime($this->end) - strtotime($this->start);
        $this->duration = $duration;
        return $this;
    }

    /**
     * Set the value of available_capacity
     *
     * @return  self
     */
    private function setAvailable_capacity()
    {
        $appointments = $this->appointments();
        $this->available_capacity = $this->total_capacity - count($appointments);
        return $this;
    }
}
