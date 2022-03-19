<?php

namespace Application\Service;

use Application\Helper\StringUtils;

class FacebookAPIDialog
{
    private const CLIENT_ID = FACEBOOK_CLIENT_ID;

    public static function requestFeedDialogUrl(string $ressource_link, string $redirect_uri)
    {
        $url =  "https://www.facebook.com/dialog/feed";
        $params = [
            "app_id" => self::CLIENT_ID,
            "display" => "popup",
            "link" => $ressource_link,
            "redirect_uri" => $redirect_uri
        ];
        $query_string = StringUtils::toQueryString($params);
        return $url . "?" . $query_string;
    }

    public static function requestShareDialogUrl(string $ressource_link, string $redirect_uri)
    {
        $url =  "https://www.facebook.com/dialog/share";
        $params = [
            "app_id" => self::CLIENT_ID,
            "display" => "popup",
            "link" => $ressource_link,
            "redirect_uri" => $redirect_uri
        ];
        $query_string = StringUtils::toQueryString($params);
        return $url . "?" . $query_string;
    }
}
