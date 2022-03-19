<?php

namespace Application\Service;

use Application\Core\CURLRequest;
use Application\Core\Params;
use Application\Helper\StringUtils;

class ZoomOAuth
{

    private const REDIRECT_URI = ZOOM_REDIRECT_URI;
    private const CLIENT_ID = ZOOM_CLIENT_ID;
    private const CLIENT_SECRET = ZOOM_CLIENT_SECRET;
    public function __construct(string $endpoint)
    {
        $this->base_url = "https://zoom.us";
        $this->endpoint = $endpoint;
        $this->url = $this->base_url . $this->endpoint;
    }

    public static function requestAuthorizeUrl()
    {
        $params = [
            "response_type" => "code",
            "redirect_uri" => self::REDIRECT_URI,
            "client_id" => self::CLIENT_ID,

        ];

        $params = StringUtils::toQueryString($params);

        return [
            "zoom_authorization_url" => "https://zoom.us/oauth/authorize?$params"
        ];
    }

    public static function requestAccessToken(string $authorization_code)

    {

        $credentials = self::CLIENT_ID . ":" . self::CLIENT_SECRET;
        $base64_credentials = base64_encode($credentials);

        $zoom_api_wrapper = new self('/oauth/token');

        $url = $zoom_api_wrapper->url;
        $headers = array(
            "Authorization: Basic $base64_credentials",
            "Content-Type: application/x-www-form-urlencoded",
        );
        $query_params = new Params([
            "code" => $authorization_code,
            "redirect_uri" => self::REDIRECT_URI,
            "grant_type" => "authorization_code",
        ]);

        $curl_request = new CURLRequest('POST', $url, $headers, $query_params);
        return $curl_request->exec();
    }


    public static function refreshToken(string $refresh_token)
    {
        $credentials = self::CLIENT_ID . ":" . self::CLIENT_SECRET;
        $base64_credentials = base64_encode($credentials);

        $zoom_api_wrapper = new self('/oauth/token');

        $url = $zoom_api_wrapper->url;
        $headers = array(
            "Authorization: Basic $base64_credentials",
            "Content-Type: application/x-www-form-urlencoded",
        );
        $query_params = new Params([
            "grant_type" => "refresh_token",
            "refresh_token" => $refresh_token
        ]);

        $curl_request = new CURLRequest('POST', $url, $headers, $query_params);
        return $curl_request->exec();
    }
}
