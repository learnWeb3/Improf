<?php

namespace Application\Model;

use Application\Core\Params;
use Application\Error\DatabaseError;
use Application\Error\NotFoundError;
use Application\Error\ForbiddenError;
use Application\Core\CollectionRecord;
use Application\Service\ZoomAPIMeeting;

class Meeting extends ApplicationRecord
{
    public static function construct(array $fields)
    {
        $meeting = new self;
        foreach ($fields as $field_name => $field_value) {
            $meeting->$field_name = $field_value;
        }
        $meeting->belongs_to('schedule');
        return $meeting;
    }

    public static function getAll(Params $params, int $limit, int $offset, string $previous_page, string  $next_page): CollectionRecord
    {
        try {
            $filters = get_object_vars($params);
            $meetings =  Meeting::select(["*"])
                ->from('meetings')
                ->where($filters)
                ->limit($limit)
                ->offset($offset)
                ->exec()
                ->fetchAll();
            $collection = new CollectionRecord([
                "meetings" => $meetings,
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
            $meeting = Meeting::select(["*"])
                ->from('meetings')
                ->where(['id' => $params->id])
                ->exec()
                ->fetchAll();
        } catch (NotFoundError $th) {
            throw $th;
        } catch (\Throwable $th) {
            throw new DatabaseError([$th->getMessage()]);
        }

        if (isset($meeting[0])) {
            $meeting = $meeting[0];
            $meeting->schedule = $meeting->schedule();
            return $meeting;
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
        $formation = Formation::getOne(new Params([
            "id" => $schedule->id_formation
        ]));
        $user = $formation->user();

        // errors handling
        $errors = [];
        is_null($current_user) && $errors[] = "user must be authenticated";
        !is_null($current_user) && $current_user->id !== $user->id || !is_null($current_user) && !$current_user->is_admin && $errors[] = "user must be formation owner or admin";
        count($errors) > 0 && throw new ForbiddenError($errors);

        // related object zoom credential

        $zoom_credential = ZoomCredential::getOne(new Params([
            "id" => $current_user->zoom_credential->id
        ]));

          // defining API call Params
          $zoom_meeting_params = new Params([
            "topic" => $formation->label,
            "start_time" => $schedule->start,
            "duration" => $schedule->duration / MINUTE,
            "schedule_for" => $user->email,
            "timezone" => "Europe/Paris",
            "password" => $params->password,
            "agenda" => $params->agenda,
            "type" => 2,
        ]);

        // refreshing API access token
        $zoom_credential = ZoomCredential::refreshToken(new Params([
            "id" => $zoom_credential->id,
            "refresh_token" => $zoom_credential->refresh_token
        ]));

        try {

            $access_token = $zoom_credential->access_token;
            
            // API call
            $meeting = ZoomAPIMeeting::createMeeting($access_token, $zoom_meeting_params);

            // Application record management
            $meeting_params = new Params([
                "id_schedule" => $params->id_schedule,
                "zoom_meeting_start_url" => $meeting["start_url"],
                "zoom_meeting_join_url" => $meeting["join_url"],
                "zoom_meeting_id" => $meeting["id"],
                "zoom_meeting_password" => $meeting['password']
            ]);
            $meeting = Meeting::create(get_object_vars($meeting_params))
                ->exec()
                ->lastInsert()
                ->exec()
                ->fetchAll()[0];

            return $meeting;
        } catch (\Throwable $th) {
            throw new DatabaseError([$th->getMessage()]);
        }
    }

    public static function remove(Params $params, User|null $current_user): void
    {
        // related objects
        $meeting = Meeting::getOne(new Params([
            'id' => $params->id
        ]));
        $schedule = Schedule::getOne(new Params([
            "id" => $meeting->id_schedule
        ]));
        $formation = Formation::getOne(new Params([
            "id" => $schedule->id_formation
        ]));
        $user = $formation->user();

        // errors handling
        $errors = [];
        is_null($current_user) && $errors[] = "user must be authenticated";
        !is_null($current_user) && $current_user->id !== $user->id || !is_null($current_user) && !$current_user->is_admin && $errors[] = "user must be formation owner or admin";
        count($errors) > 0 && throw new ForbiddenError($errors);

        // related object zoom credential 
        $zoom_credential = ZoomCredential::getOne(new Params([
            "id" => $current_user->zoom_credential->id
        ]));

        // refreshing API access token
        $zoom_credential = ZoomCredential::refreshToken(new Params([
            "id" => $zoom_credential->id,
            "refresh_token" => $zoom_credential->refresh_token
        ]));
        $access_token = $zoom_credential->access_token;

        // API call
        ZoomAPIMeeting::deleteMeeting($meeting->zoom_meeting_id, $access_token);

        // Application record management
        try {
            Meeting::destroy()
                ->from('meetings')
                ->where(['id' => $params->id])
                ->exec();
        } catch (\Throwable $th) {
            throw new DatabaseError([$th->getMessage()]);
        }
    }
}
