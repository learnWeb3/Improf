<?php

namespace Application\Model;

use Application\Core\Params;
use Application\Error\DatabaseError;

class ScheduleDetail extends ApplicationRecord
{
    public static function construct(array $fields)
    {
        $schedule_details = new self;
        foreach ($fields as $field_name => $field_value) {
            $schedule_details->$field_name = $field_value;
        }
        $schedule_details->belongs_to('schedule');
        return $schedule_details;
    }

    public static function register(Params $params): self
    {
        try {
            $schedule = Schedule::getOne(new Params([
                "id" => $params->id_schedule
            ]));
            return ScheduleDetail::create(get_object_vars($params))
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
            ScheduleDetail::update(get_object_vars($params))
                ->where(['id' => $params->id])
                ->exec();
            $schedule_details = ScheduleDetail::select(["*"])
                ->from('schedule_details')
                ->where(['id' => $params->id])
                ->exec()
                ->fetchAll()[0];
            return $schedule_details;
        } catch (\Throwable $th) {
            throw new DatabaseError([$th->getMessage()]);
        }
    }


    public static function remove(Params $params): void
    {
        try {
            ScheduleDetail::destroy()
                ->from('schedule_details')
                ->where(['id' => $params->id])
                ->exec();
        } catch (\Throwable $th) {
            throw new DatabaseError([$th->getMessage()]);
        }
    }
}
