<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Validator;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\BaseController as BaseController;
use App\User;
use App\UsuariosHasNiveles;
use App\UsuariosHasSubNiveles;
use App\Nivel;
use App\SubNivel;

class UserController extends BaseController {
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request) {
        $query = User::orderBy('created_at', 'desc');

        $nombre_completo = $request->query('nombre_completo');
        if ($nombre_completo) {
            $query->where('nombre_completo', 'LIKE', '%'.$nombre_completo.'%');
        }

        $email = $request->query('email');
        if ($email) {
            $query->where('email', 'LIKE', '%'.$email.'%');
        }

        $rol = $request->query('rol');
        if ($rol) {
            $query->where('rol', '=', $rol);
        }

        $activo = $request->query('activo');
        if ($activo) {
            $query->where('activo', '=', $activo);
        }


        $paginar = $request->query('paginar');
        $listar = (boolval($paginar)) ? 'paginate' : 'get';

        $data = $query->$listar();
        
        return $this->sendResponse(true, 'Listado obtenido exitosamente', $data, 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request) {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) {
        $nombre_completo = $request->input("nombre_completo");
        $email = $request->input("email");
        $password = hash('sha256', $request->input("password"));
        $rol = $request->input('rol');

        $validator = Validator::make($request->all(), [
            'nombre_completo'  => 'required',
            'email'  => 'required',
            'password'  => 'required',
            'rol'  => 'required'
        ]);

        if ($validator->fails()) {
            return $this->sendResponse(false, 'Error de validacion', $validator->errors(), 400);
        }

        $usuario = new User();
        $usuario->nombre_completo = $nombre_completo;
        $usuario->email = $email;
        $usuario->password = $password;
        $usuario->rol = $rol;
        $usuario->activo = 'S';

        if ($usuario->save()) {
            return $this->sendResponse(true, 'Usuario registrado', $usuario, 201);
        }
        
        return $this->sendResponse(false, 'Usuario no registrado', null, 400);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id) {
        $usuario = User::find($id);

        if (is_object($usuario)) {
            return $this->sendResponse(true, 'Listado obtenido exitosamente', $usuario, 200);
        }
        
        return $this->sendResponse(false,'El usuario no existe', null, 404);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id) {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id) {
        $nombre_completo = $request->input("nombre_completo");
        $email = $request->input("email");
        $password = hash('sha256', $request->input("password"));
        $rol = $request->input('rol');

        $validator = Validator::make($request->all(), [
            'nombre_completo'  => 'required',
            'email'  => 'required',
            'rol'  => 'required'
        ]);

        if ($validator->fails()) {
            return $this->sendResponse(false, 'Error de validacion', $validator->errors(), 400);
        }

        $usuario = User::find($id);
        if ($usuario) {
            $usuario->nombre_completo = $nombre_completo;
            $usuario->email = $email;
            $usuario->password = $password;
            $usuario->rol = $rol;
            $usuario->activo = 'S';
    
            if ($usuario->save()) {
                $jwtAuth = new \JwtAuth();
                $data = $jwtAuth->signIn($email, $usuario->password);
                $respuesta = ['token' => $data->original['data'], 'usuario' => $usuario];
                return $this->sendResponse(true, 'Usuario actualizado', $respuesta, 200);
            }

            return $this->sendResponse(false, 'Usuario no actualizado', null, 400);
        }
        
        return $this->sendResponse(false, 'No se encontro el Usuario', null, 404);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, Request $request) {
        $usuario = User::find($id);

        if ($usuario) {
            $usuario->activo = ($usuario->activo == 'S') ? 'N' : 'S';
            
            if ($usuario->update()) {
                return $this->sendResponse(true, 'Usuario actualizado', $usuario, 200);
            }
            
            return $this->sendResponse(false, 'Usuario no actualizado', $usuario, 400);
        }
        
        return $this->sendResponse(true, 'No se encontro el usuario', $usuario, 404);
    }


    public function signIn(Request $request) {
        $jwtAuth = new \JwtAuth();
        
        $email = $request->input("email");
        $password = hash('sha256', $request->input("password"));
        $getToken = $request->input("getToken");
            
        $validator = Validator::make($request->all(), [
            'email'     =>  'required|email',
            'password'  =>  'required'
        ]);

        if ($validator->fails()) {
            return $this->sendResponse(false, 'Error de validacion', $validator->errors(), 400);
        }else{
            if (!empty($getToken)) {
                $data = $jwtAuth->signIn($email, $password, true);
            }else{
                $data = $jwtAuth->signIn($email, $password);
            }
        }

        return $data;
    }

    public function checkToken(Request $request) {
        $token = $request->get('Authorization');
        $jwt = new \JwtAuth();
        $usuario = $jwt->checkToken($token);

        if ($usuario) {
            return $this->sendResponse(true, 'Login exitoso', $usuario, 200);
        }
        
        return $this->sendResponse(false, 'El usuario no existe', $usuario, 404);

    }

    public function validarEmail(Request $request) {
        $id = $request->input('id');
        $email = $request->input('eamil');

        $usuario = User::where([['email', '=', $email], ['identificador', '!=', $id]])->first();

        if ($usuario) return $this->sendResponse(false, 'Ya existe otro usuario con este email', null, 400);

        return $this->sendResponse(true, 'Email disponible', null, 200);
    }

    public function cambiarPassword(Request $request) {
        $id = $request->input("id");
        $email = hash('sha256', $request->input("email"));
        $password_actual = hash('sha256', $request->input("password_actual"));
        $password_nueva = hash('sha256', $request->input("password_nueva"));

        $validator = Validator::make($request->all(), [
            'id'  => 'required',
            'email'  => 'required',
            'password_actual'  => 'required',
            'password_nueva'  => 'required'
        ]);

        if ($validator->fails()) {
            return $this->sendResponse(false, 'Error de validacion', $validator->errors(), 400);
        }

        $usuario = User::find($id);
        if ($usuario) {
            if ($usuario->password != $password_actual) {
                return $this->sendResponse(false, 'Contraseña actual incorrecta', null, 400);
            }

            $usuario->password = $password_nueva;    
            if ($usuario->save()) {
                $jwtAuth = new \JwtAuth();
                $data = $jwtAuth->signIn($email, $password_nueva);
                $respuesta = ['token' => $data->original['data'], 'usuario' => $usuario];
                
                return $this->sendResponse(true, 'Contraseña actualizada', $respuesta, 200);
            }

            return $this->sendResponse(false, 'Contraseña no actualizada', null, 400);
        }
        
        return $this->sendResponse(false, 'No se encontro el Usuario', null, 404);
    }

    public function asignarNivel(Request $request){
        $email = $request->input('email');

        //obtener usuario
        $user = User::where([
            'email'  =>  $email,
            'rol'    =>  'US',
            'activo' =>  'S'
        ])->first();

        if (!is_object($user)) return $this->sendResponse(false, 'No se encontro el Usuario', null, 404);

        //obtener niveles asignados al usuario 
        $asignados = UsuariosHasNiveles::where([
            'usuarios_id'   =>  $user->id,
        ])->get();

        //si tiene niveles asignados, obtener ids de niveles asig y obtener los niveles no asignados de la tabla nivel
        //si no tiene ninguno, obtener todos los niveles
        if (count($asignados) > 0) {
            $idsAsignado = [];
            foreach ($asignados as $asignado) {
                array_push($idsAsignado, $asignado->niveles_id);
            }

            $niveles = Nivel::where(['activo' => 'S'])->whereNotIn('id', $idsAsignado)->get();
            //asignar nivel
            foreach ($niveles as $nivel) {
                 $asignacion = new UsuariosHasNiveles();
                 $asignacion->niveles_id = $nivel->id;
                 $asignacion->usuarios_id = $user->id;
                 $asignacion->bloqueado = ($nivel->numero == 1) ? 'N' : 'S';

                 if (!$asignacion->save()) return $this->sendResponse(false, 'Error al asignar nivel: '.$nivel->descripcion, null, 400);
            }
        }else{
            $niveles = Nivel::where(['activo' => 'S'])->get();
            if (!is_object($niveles)) return $this->sendResponse(false, 'No se encontraron niveles activos', $asignacion, 404);

            //asignar nivel
            foreach ($niveles as $nivel) {
                 $asignacion = new UsuariosHasNiveles();
                 $asignacion->niveles_id = $nivel->id;
                 $asignacion->usuarios_id = $user->id;
                 $asignacion->bloqueado = ($nivel->numero == 1) ? 'N' : 'S';

                 if (!$asignacion->save()) return $this->sendResponse(false, 'Error al asignar nivel: '.$nivel->descripcion, null, 400);
            }
        }

        return $this->sendResponse(true, 'Niveles asignados', null, 200);
    }

    public function asignarSubNivel(Request $request){
        $email = $request->input('email');
        $nivel_id = $request->input('nivel_id');

        //obtener usuario
        $user = User::where([
            'email'  =>  $email,
            'rol'    =>  'US',
            'activo' =>  'S'
        ])->first();

        if (!is_object($user)) return $this->sendResponse(false, 'No se encontro el Usuario', null, 404);

        //obtener sub niveles asignados al usuario 
        $asignados = UsuariosHasSubNiveles::where([
            'usuarios_id'   =>  $user->id,
        ])->get();

        //si tiene sub niveles asignados, obtener ids de sub niveles asig y obtener los sub niveles no asignados de la tabla sub_nivel
        //si no tiene ninguno, obtener todos los sub niveles
        if (count($asignados) > 0) {
            $idsAsignado = [];
            foreach ($asignados as $asignado) {
                array_push($idsAsignado, $asignado->sub_niveles_id);
            }

            $subNiveles = SubNivel::where(['niveles_id' => $nivel_id])->whereNotIn('id', $idsAsignado)->get();
            if (!is_object($subNiveles)) return $this->sendResponse(false, 'No se encontraron niveles activos', $asignacion, 404);

            //asignar sub nivel
            foreach ($subNiveles as $sub) {
                $asignacion = new UsuariosHasSubNiveles();
                $asignacion->niveles_id = $nivel_id;
                $asignacion->sub_niveles_id = $sub->id;
                $asignacion->usuarios_id = $user->id;
                $asignacion->bloqueado = ($sub->numero == 1) ? 'N' : 'S';
                $asignacion->sub_niveles_id = $sub->id;
                $asignacion->intentos = 3;
                $asignacion->puntos = 0;

                if (!$asignacion->save()) return $this->sendResponse(false, 'Error al asignar sub nivel: '.$sub->descripcion, null, 400);
            }
        }else{
            $subNiveles = SubNivel::where(['niveles_id' => $nivel_id])->get();
            if (!is_object($subNiveles)) return $this->sendResponse(false, 'No se encontraron niveles activos', $asignacion, 404);

            //asignar sub nivel
            foreach ($subNiveles as $sub) {
                 $asignacion = new UsuariosHasSubNiveles();
                 $asignacion->niveles_id = $nivel_id;
                 $asignacion->sub_niveles_id = $sub->id;
                 $asignacion->usuarios_id = $user->id;
                 $asignacion->bloqueado = ($sub->numero == 1) ? 'N' : 'S';
                 $asignacion->intentos = 3;
                 $asignacion->puntos = 0;

                 if (!$asignacion->save()) return $this->sendResponse(false, 'Error al asignar sub nivel: '.$sub->descripcion, null, 400);
            }
        }

        return $this->sendResponse(true, 'Sub niveles asignados', null, 200);


    }
}
