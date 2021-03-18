<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\BaseController as BaseController;
use Illuminate\Http\Response;
use Validator;
use Illuminate\Support\Facades\Storage;
use App\SubNivel;
use App\UsuariosHasSubNiveles;

class SubNivelController extends BaseController
{
  
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request) {
        $query = SubNivel::with(["nivel"]);

        $numero = $request->query('numero');
        if ($numero) {
            $query->where('numero', '=', $numero);
        }

        $descripcion = $request->query('descripcion');
        if ($descripcion) {
            $query->where('descripcion', 'LIKE', '%'.$descripcion.'%');
        }

        $activo = $request->query('activo');
        if ($activo) {
            $query->where('activo', '=', $activo);
        }

        $niveles_id = $request->query('niveles_id');
        if ($niveles_id) {
            $query->where('niveles_id', '=', $niveles_id);
        }

        $paginar = $request->query('paginar');
        $listar = (boolval($paginar)) ? 'paginate' : 'get';
        $data = $query->orderBy('niveles_id', 'asc')->orderBy('numero', 'asc')->$listar();

        
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
        $niveles_id = $request->input("niveles_id");

        $validator = Validator::make($request->all(), [
            'numero'  => 'required',
            'descripcion'  => 'required',
            'niveles_id' =>'required',
        ]);

        if ($validator->fails()) {
            return $this->sendResponse(false, 'Error de validacion', $validator->errors(), 400);
        }

        $subnivel = new SubNivel();
        $subnivel->numero = $numero;
        $subnivel->descripcion = $descripcion;
        $subnivel->activo = 'S';
        $subnivel->niveles_id = $niveles_id;

        if ($subnivel->save()) {
            return $this->sendResponse(true, ' Sub Nivel registrado', $subnivel, 201);
        }
        
        return $this->sendResponse(false,'Sub Nivel no registrado', null, 400);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $subnivel = SubNivel::with(['nivel'])->find($id);

        if (is_object($subnivel)) {
            return $this->sendResponse(true, 'Listado obtenido exitosamente', $subnivel, 200);
        }
        
        return $this->sendResponse(false,'El sub nivel no existe', null, 404);
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
        $niveles_id = $request->input("niveles_id");

        $validator = Validator::make($request->all(), [
            'numero'  => 'required',
            'descripcion'  => 'required',
            'niveles_id' =>'required',
        ]);

        if ($validator->fails()) {
            return $this->sendResponse(false, 'Error de validacion', $validator->errors(), 400);
        }

        $subnivel = SubNivel::find($id);
        if ($subnivel) {
            $subnivel->numero = $numero;
            $subnivel->descripcion = $descripcion;
            $subnivel->activo = 'S';
            $subnivel->niveles_id = $niveles_id;
    
            if ($subnivel->save()) {
                return $this->sendResponse(true, 'Sub Nivel actualizado', $subnivel, 200);
            }

            return $this->sendResponse(false, 'Sub Nivel no actualizado', null, 400);
        }
        
        return $this->sendResponse(false, 'No se encontro el Sub Nivel', null, 404);
    }

    public function validarSubNivel(Request $request){
        $id = $request->input('id');
        $numero = $request->input('numero');
        $niveles_id = $request->input('niveles_id');

        $subnivel = SubNivel::where([['numero', '=', $numero], ['id', '!=', $id], ['niveles_id','=', $niveles_id]])->first();

        //obtener el maximo nivel en caso de que ya exista
        $maximo = 0;
        if($subnivel) $maximo = SubNivel::where([['numero', '=', $numero], ['id', '!=', $id], ['niveles_id','=', $niveles_id]])->orderBy('numero', 'desc')->first();

        //obtener el numero sugerido
        $sugerido = 1;
        if($subnivel)  $sugerido += $maximo->numero;
        

        if($subnivel) return $this->sendResponse(false, 'Ya existe un sub nivel con este numero, numero sugerido '.$sugerido  , null, 200);

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
        $subnivel = SubNivel::find($id);

        if ($subnivel) {
            $subnivel->activo = ($subnivel->activo == 'S') ? 'N' : 'S';
            
            if ($subnivel->update()) {
                return $this->sendResponse(true, 'Sub Nivel actualizado', $subnivel, 200);
            }
            
            return $this->sendResponse(false, 'Sub Nivel no actualizado', $subnivel, 400);
        }
        
        return $this->sendResponse(true, 'No se encontro el sub nivel', $subnivel, 404);
    }

    public function subNivelByUsuario($nivel_id, $user_id) {
        $data = UsuariosHasSubNiveles::with(["subNivel"])->where('niveles_id', '=', $nivel_id)->get();
        
        if (count($data) <= 0) return $this->sendResponse(true, 'No se encontraron sub niveles para el usuario', null, 404);

        return $this->sendResponse(true, 'Listado obtenido exitosamente', $data, 200);
    }
}
