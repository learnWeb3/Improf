<?php

namespace Application\Service;

use Application\Core\CURLRequest;
use Application\Core\Params;

class ZoomAPIMeeting
{
    public function __construct(string $endpoint, string $access_token)
    {
        $this->base_url = "https://api.zoom.us";
        $this->version = "/v2";
        $this->endpoint = $endpoint;
        $this->url = $this->base_url . $this->version . $this->endpoint;
        $this->access_token = $access_token;
    }

    public static function getMeeting(int|string $meeeting_id, string $access_token)
    {
        $endpoint = "/meetings/$meeeting_id";
        $zoom_api_wrapper = new self($endpoint, $access_token);
        $access_token = $zoom_api_wrapper->access_token;

        $url = $zoom_api_wrapper->url;
        $headers = [
            "Authorization: Bearer $access_token"
        ];

        $curl_request = new CURLRequest('GET', $url, $headers);
        return $curl_request->exec();
    }


    public static function getMeetings(string $access_token, Params $params)
    {
        $endpoint = "/users/me/meetings";
        $zoom_api_wrapper = new self($endpoint, $access_token);
        $access_token = $zoom_api_wrapper->access_token;

        $url = $zoom_api_wrapper->url;
        $headers = [
            "Authorization: Bearer $access_token"
        ];

        $page_size = [
            "page_size" => $params->page_size
        ];

        $next_page = [
            "next_page_token" => $params->next_page_token
        ];

        $query_params = [];
        $query_params = isset($params->next_page_token) ? $query_params + $next_page : $query_params;
        $query_params = isset($params->page_size) ? $query_params + $page_size : $query_params;

        $curl_request = new CURLRequest('GET', $url, $headers, new Params(
            $query_params
        ));
        return $curl_request->exec();
    }

    public static function createMeeting(string $access_token, Params $body_params)
    {
        $endpoint = "/users/me/meetings";
        $zoom_api_wrapper = new self($endpoint, $access_token);
        $access_token = $zoom_api_wrapper->access_token;

        $url = $zoom_api_wrapper->url;

        $headers = [
            "Authorization: Bearer $access_token",
            "Content-type: application/json"
        ];

        $body_params->start_time = gmdate("Y-m-d\TH:i:s\Z", strtotime($body_params->start_time));

        $body_params = new Params([
            "topic" => $body_params->topic,
            "type" => 2,
            "start_time" => $body_params->start_time,
            "duration" => $body_params->duration,
            "timezone" => $body_params->timezone,
            "password" => $body_params->password,
            "agenda" => $body_params->agenda,
            "settings" => [
                "host_video" => true,
                "participant_video" => true,
                "join_before_host" => true,
                "jbh_time" => 10,
                "mute_upon_entry" => true,
                "registrants_email_notification" => true,
                "registrants_confirmation_email" => true,
                "meeting_authentication" => false,
            ]
        ]);

        $curl_request = new CURLRequest('POST', $url, $headers, null, $body_params);
        return $curl_request->exec();
    }

    public static function updateMeeting(int|string $meeting_id, string $access_token, Params $body_params)
    {

        $endpoint = "/meetings/$meeting_id";
        $zoom_api_wrapper = new self($endpoint, $access_token);
        $access_token = $zoom_api_wrapper->access_token;

        $url = $zoom_api_wrapper->url;
        $headers = [
            "Authorization: Bearer $access_token"
        ];
        $body_params = new Params([
            "topic" => $body_params->topic,
            "type" => 2,
            "start_time" => $body_params->start_time,
            "duration" => $body_params->duration,
            "schedule_for" => $body_params->schedule_for,
            "timezone" => $body_params->timezone,
            "password" => $body_params->password,
            "agenda" => $body_params->agenda,
            "settings" => [
                "host_video" => true,
                "participant_video" => true,
                "join_before_host" => true,
                "jbh_time" => 10,
                "mute_upon_entry" => true,
                "registrants_email_notification" => true,
                "registrants_confirmation_email" => true,
                "meeting_authentication" => false,
            ]
        ]);

        $curl_request = new CURLRequest('PATCH', $url, $headers, null, $body_params);
        return $curl_request->exec();
    }

    public static function deleteMeeting(int|string $meeting_id, string $access_token,)
    {
        $endpoint = "/meetings/$meeting_id";
        $zoom_api_wrapper = new self($endpoint, $access_token);
        $access_token = $zoom_api_wrapper->access_token;

        $url = $zoom_api_wrapper->url;
        $headers = [
            "Authorization: Bearer $access_token"
        ];

        $query_params = new Params([
            "schedule_for_reminder" => true,
            "cancel_meeting_reminder" => true
        ]);

        $curl_request = new CURLRequest('DELETE', $url, $headers, $query_params, null);
        return $curl_request->exec();
    }

    public static function registerParticipant(int|string $meeting_id, string $access_token, Params $body_params)
    {
        $endpoint = "/meetings/$meeting_id/registrants";
        $zoom_api_wrapper = new self($endpoint, $access_token);
        $access_token = $zoom_api_wrapper->access_token;

        $url = $zoom_api_wrapper->url;
        $headers = [
            "Authorization: Bearer $access_token"
        ];

        $body_params = new Params([
            "email" => $body_params->email,
            "first_name" => $body_params->first_name,
            //"last_name" => $body_params->last_name,
            // "address" => $body_params->address,
            // "city" => $body_params->city,
            // "country" => $body_params->country,
            // "zip" => $body_params->zip,
            // "state" => $body_params->state,
            // "phone" => $body_params->phone
        ]);

        $curl_request = new CURLRequest('POST', $url, $headers, null, $body_params);
        return $curl_request->exec();
    }

    public static function removeParticipant(int|string $meeting_id, int|string $registrant_id, string $access_token, Params $body_params)
    {
        $endpoint = "/meetings/$meeting_id/registrants/$registrant_id";
        $zoom_api_wrapper = new self($endpoint, $access_token);
        $access_token = $zoom_api_wrapper->access_token;

        $url = $zoom_api_wrapper->url;
        $headers = [
            "Authorization: Bearer $access_token"
        ];
        $curl_request = new CURLRequest('DELETE', $url, $headers);
        return $curl_request->exec();
    }

    public static function listParticipants(int|string $meeting_id, string $access_token)
    {
        $endpoint =  "/meetings/$meeting_id}/registrants";
        $zoom_api_wrapper = new self($endpoint, $access_token);
        $access_token = $zoom_api_wrapper->access_token;

        $url = $zoom_api_wrapper->url;
        $headers = [
            "Authorization: Bearer $access_token"
        ];

        $curl_request = new CURLRequest('DELETE', $url, $headers);
        return $curl_request->exec();
    }
}
