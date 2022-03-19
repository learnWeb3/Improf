<?php

namespace Application\Service;

use Application\Core\CURLRequest;
use Application\Core\Params;
use Application\Helper\StringUtils;

class FacebookOAuth
{
    private const REDIRECT_URI = FACEBOOK_REDIRECT_URI;
    private const CLIENT_ID = FACEBOOK_CLIENT_ID;
    private const CLIENT_SECRET = FACEBOOK_CLIENT_SECRET;
    public function __construct(string $endpoint)
    {
        $this->base_url = "https://graph.facebook.com/";
        $this->version = "v12.0";
        $this->endpoint = $endpoint;
        $this->url = $this->base_url . $this->version . $this->endpoint;
    }

    public static function requestAuthorizeUrl()
    {
        $params = [
            "state" => "",
            "redirect_uri" => self::REDIRECT_URI,
            "client_id" => self::CLIENT_ID,
        ];
        $params = StringUtils::toQueryString($params);
        return [
            "facebook_authorization_url" => "https://www.facebook.com/v12.0/dialog/oauth?$params"
        ];
    }

    public static function requestAccessToken(string $authorization_code)

    {

        $zoom_api_wrapper = new self('/oauth/access_token');

        $url = $zoom_api_wrapper->url;

        $query_params = new Params([
            "client_id" => self::CLIENT_ID,
            "redirect_uri" => self::REDIRECT_URI,
            "client_secret" => self::CLIENT_SECRET,
            "code" => $authorization_code
        ]);

        $curl_request = new CURLRequest('GET', $url, null, $query_params);
        return $curl_request->exec();
    }
}
