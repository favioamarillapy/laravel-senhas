<?php

namespace App\Helpers;

use Firebase\JWT\JWT;
use Illuminate\Support\Facades\DB;
use App\User;
use App\Http\Controllers\BaseController as BaseController;

class JwtAuth extends BaseController
{
    public $key;

    public function __construct()
    {
        $this->key = 'api-ecommerce-sy';
    }

    public function signIn($email, $password, $getToken = null)
    {
        $user = User::where([
            'email'     =>  $email,
            'password'  =>  $password,
            'activo'    =>  'S'
        ])->first();

        $signIn = is_object($user);
        if ($signIn) {
            $token = array(
                'sub'               => $user->id,
                'nombre_completo'   => $user->nombre_completo,
                'email'             => $user->email,
                'rol'               => $user->rol,
                'iat'               =>  time(),
                'exp'               =>  time() + (7 * 24 * 60 * 60)
            );

            $jwt = JWT::encode($token, $this->key, 'HS256');
            $decoded = JWT::decode($jwt, $this->key, ['HS256']);

            if (is_null($getToken)) {
                return $this->sendResponse(true, 'Autenticacion', $jwt, 200);
            }
            
            return $this->sendResponse(true, 'Autenticacion', $decoded, 200);
        }
        
        return $this->sendResponse(false, 'Acceso denegado', null, 400);
    }

    public function checkToken($jwt){
        $auth = false;

        if ($jwt) {
            try {
                $jwt = str_replace('"', '', $jwt);
                $decoded = JWT::decode($jwt, $this->key, ['HS256']);
            } catch (\UnexpectedValueException $e) {
                $auth = false;
            } catch (\DomainException $e) {
                $auth = false;
            }

            if (!empty($decoded) && is_object($decoded) && isset($decoded->sub)) {
                $auth = true;
            }else{
                $auth = false;
            }

            $auth = $decoded;
            return $auth;
        }
        
        return null;
    }

}
