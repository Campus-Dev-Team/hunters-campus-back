<?php

namespace App\Http\Middleware;

use JWTAuth;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;
use Exception;

class JwtMiddleware extends BaseMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        try
        {
            JWTAuth::parseToken()->authenticate();
            return $next($request);
        }
        catch (\Tymon\JWTAuth\Exceptions\TokenBlacklistedException $e)
        {
            return response(['status' => 'Token inválido'], Response::HTTP_UNAUTHORIZED);
        }
        catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e)
        {
            return response(['status' => 'Token inválido'], Response::HTTP_UNAUTHORIZED);
        }
        catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e)
        {
            return response(['status' => 'El token ha expirado'], Response::HTTP_UNAUTHORIZED);
        }
        catch (\Tymon\JWTAuth\Exceptions\JWTException $e)
        {
            return response(['status' => 'El token no ha sido encontrado'], Response::HTTP_UNAUTHORIZED);
        }
        catch (Exception $e)
        {
            return response(['status' => 'El token no ha sido encontrado'], Response::HTTP_UNAUTHORIZED);
        }
    }
}
