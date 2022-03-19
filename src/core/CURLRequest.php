<?php

namespace Application\Core;

use Application\Core\Params;
use Application\Helper\StringUtils;

class CURLRequest
{

    private string $http_method;
    private string $url;
    private array|null $headers;
    private Params|null $query_params;
    private Params|null $body_params;
    private string $query_string;

    public function __construct(string $http_method, string $url, array|null $headers = null, Params|null $query_params = null, Params|null $body_params = null)
    {
        $this->http_method = $http_method;
        $this->url = $url;
        $this->headers = $headers;
        $this->query_params = $query_params;
        $this->body_params = $body_params;
        $this->query_string = is_null($query_params) ? "" : StringUtils::toQueryString(get_object_vars($this->query_params));;
    }

    public function exec()
    {
        $url = empty($this->query_string) ?  $this->url :  $this->url . "?" . $this->query_string;

        $session = curl_init($url);

        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $this->http_method
        ];
        $post_field_options = [
            CURLOPT_POST => true,
        ];
        $body_params_option = [
            CURLOPT_POSTFIELDS => $this->body_params,
        ];
        $headers_option = [
            CURLOPT_HTTPHEADER => $this->headers
        ];

        $http_methods_post_field_enabled = [
            "PUT",
            "POST",
            "PATCH"
        ];

        $options = !is_null($this->body_params) ? $body_params_option + $options : $options;
        $options = !is_null($this->headers) ? $headers_option + $options : $options;
        $options = in_array($this->http_method, $http_methods_post_field_enabled) ? $post_field_options + $options : $options;

        curl_setopt_array($session, $options);
        $results = curl_exec($session);
        curl_close($session);
        return json_decode($results, true);
    }
}
