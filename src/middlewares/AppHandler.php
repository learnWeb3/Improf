<?php

namespace Application\Core\Middleware;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

use Application\Error\ForbiddenParameterError;
use Application\Error\ParameterMismatchError;
use Application\Error\NotFoundError;
use Application\Error\DatabaseError;
use Application\Error\RouterError;
use Application\Error\ForbiddenError;

use Application\Core\Params;
use Application\Core\Router;

class AppHandler implements IMiddleware
{
    public function __invoke(RequestInterface $request, ResponseInterface $response, callable $next)
    {
        try {
            $router = new \AltoRouter(ROUTES, ROOT_PATH);
            $match = $router->match();
            $all_parameters = isset($request->params) ? array_merge($match['params'], get_object_vars($request->params)) : $match['params'];
            $request->target = $match['target'];
            $params = new Params($all_parameters);
            $data = Router::route($request, $params);
            $response = $response->withStatus(200);
            $response->getBody()->write(
                (string) $data
            );
        } catch (ForbiddenParameterError $th) {
            $response = $response->withStatus(422);
            $response->getBody()->write(
                (string) $th
            );
        } catch (ParameterMismatchError $th) {
            $response = $response->withStatus(422);
            $response->getBody()->write(
                (string) $th
            );
        } catch (ForbiddenError $th) {
            $response = $response->withStatus(403);
            $response->getBody()->write(
                (string) $th
            );
        } catch (NotFoundError $th) {
            $response = $response->withStatus(404);
            $response->getBody()->write(
                (string) $th
            );
        } catch (DatabaseError $th) {
            $response = $response->withStatus(500);
            $response->getBody()->write(
                (string) $th
            );
        } catch (RouterError $th) {
            $response = $response->withStatus(500);
            $response->getBody()->write(
                (string) $th
            );
        } catch (\Throwable $th) {
            $response = $response->withStatus(500);
            $response->getBody()->write(
                //(string) $th
                'Internal server error'
            );
        } finally {
            $response = $response->withAddedHeader('Content-type', 'application/json');
            return $response;
        }
    }
}
