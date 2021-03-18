<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController as BaseController;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Validator;
use Illuminate\Support\Facades\Storage;
use App\Nivel;
use App\UsuariosHasNiveles;

class NivelController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request) {
        $query = Nivel::orderBy('numero', 'asc');

        $numero = $request->query('numero');
        if ($numero) {
            $query->where('numero', '=', $numero);
        }

        $descripcion = $request->query('descripcion');
        if ($descripcion) {
            $query->where('descripcion', 'LIKE', '%'.$descripcion.'%');
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
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $numero = $request->input("numero");
        $descripcion = $request->input("descripcion");

        $validator = Validator::make($request->all(), [
            'numero'  => 'required',
            'descripcion'  => 'required' 
        ]);

        if ($validator->fails()) {
            return $this->sendResponse(false, 'Error de validacion', $validator->errors(), 400);
        }

        $nivel = new Nivel();
        $nivel->numero = $numero;
        $nivel->descripcion = $descripcion;
        $nivel->activo = 'S';

        if ($nivel->save()) {
            return $this->sendResponse(true, 'Nivel registrado', $nivel, 201);
        }
        
        return $this->sendResponse(false, 'Nivel no registrado', null, 400);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $nivel = Nivel::find($id);

        if (is_object($nivel)) {
            return $this->sendResponse(true, 'Listado obtenido exitosamente', $nivel, 200);
        }
        
        return $this->sendResponse(false,'El nivel no existe', null, 404);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $numero = $request->input("numero");
        $descripcion = $request->input("descripcion");

        $validator = Validator::make($request->all(), [
            'numero'  => 'required',
            'descripcion'  => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendResponse(false, 'Error de validacion', $validator->errors(), 400);
        }

        $nivel = Nivel::find($id);
        if ($nivel) {
            $nivel->numero = $numero;
            $nivel->descripcion = $descripcion;
            $nivel->activo = 'S';
    
            if ($nivel->save()) {
                return $this->sendResponse(true, 'Nivel actualizado', $nivel, 200);
            }

            return $this->sendResponse(false, 'Nivel no actualizado', null, 400);
        }
        
        return $this->sendResponse(false, 'No se encontro el Nivel', null, 404);
    }

    public function validarNivel(Request $request){
        $id = $request->input('id');
        $numero = $request->input('numero');
        $nivel = null;

        $nivel = Nivel::where([['numero', '=', $numero], ['id', '!=', $id]])->first();

        //obtener el maximo nivel en caso de que ya exista
        $maximo = 0;
        if($nivel) $maximo = Nivel::all()->last();

        //obtener el numero sugerido
        $sugerido = 1;
        if($nivel)  $sugerido += $maximo->numero;


        if($nivel) return $this->sendResponse(false, 'Ya existe un nivel con este numero, numero sugerido '.$sugerido  , null, 200);

        return $this->sendResponse(true, 'Listado obtenido exitosamente', null, 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $nivel = Nivel::find($id);

        if ($nivel) {
            $nivel->activo = ($nivel->activo == 'S') ? 'N' : 'S';
            
            if ($nivel->update()) {
                return $this->sendResponse(true, 'Nivel actualizado', $nivel, 200);
            }
            
            return $this->sendResponse(false, 'Nivel no actualizado', $nivel, 400);
        }
        
        return $this->sendResponse(true, 'No se encontro el nivel', $nivel, 404);
    }

    public function nivelByUsuario($user_id) {
        $data = UsuariosHasNiveles::with(["nivel"])->where('usuarios_id', '=', $user_id)->get();
        
        if (count($data) <= 0) return $this->sendResponse(true, 'No se encontraron niveles para el usuario', null, 404);

        return $this->sendResponse(true, 'Listado obtenido exitosamente', $data, 200);
    }
}
