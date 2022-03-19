<?php

namespace Application\Service;

use Application\Core\CURLRequest;

class ZoomAPIMe
{
    public function __construct(string $endpoint, string $access_token)
    {
        $this->base_url = "https://api.zoom.us/";
        $this->version = "/v2";
        $this->endpoint = $endpoint;
        $this->url = $this->base_url . $this->version . $this->endpoint;
        $this->access_token = $access_token;
    }

    public static function getMe(string $access_token)
    {
        $zoom_api_wrapper = new self('/users/me', $access_token);
        $access_token = $zoom_api_wrapper->access_token;

        $url = $zoom_api_wrapper->url;

        $headers = [
            "Authorization: Bearer $access_token",
        ];

        $curl_request = new CURLRequest('GET', $url, $headers);
        return $curl_request->exec();
    }
}
