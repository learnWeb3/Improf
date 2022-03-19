<?php

namespace Application\Core\Middleware;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

use Application\Core\Uploader;

class ImgUploader implements IMiddleware
{
    public function __invoke(RequestInterface $request, ResponseInterface $response, callable $next)
    {
        if (in_array("Content-type", $request->getHeader("Content-type"))) {
            $content_type_header = $request->getHeader("Content-type");
            if ($content_type_header === "multipart/form-data") {
                if (empty($_FILES) === false) {
                    $request->uploaded_files = Uploader::upload($_FILES);
                }
            }
        }
        return $next($request, $response, $next);
    }
}
