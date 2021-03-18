<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\BaseController as BaseController;
use Illuminate\Http\Response;
use Validator;
use Illuminate\Support\Facades\Storage;
use App\Glosario;

class GlosarioController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Glosario::orderBy('created_at', 'desc');

        $descripcion = $request->query('descripcion');
        if ($descripcion) {
            $query->where('descripcion', 'LIKE', '%'.$descripcion.'%');
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
        $descripcion = $request->input("descripcion");
        $imagen = $request->input("imagen");

        $validator = Validator::make($request->all(), [
            'descripcion'  => 'required',
            'imagen'  => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendResponse(false, 'Error de validacion', $validator->errors(), 400);
        }

        $glosario = new Glosario();
        $glosario->descripcion = $descripcion;
        $glosario->imagen = $imagen;
        $glosario->activo = 'S';

        if ($glosario->save()) {

            return $this->sendResponse(true, 'Glosario registrado', $glosario, 200);
        }
        
        return $this->sendResponse(false,'Glosario no registrado', null, 400);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $glosario = Glosario::find($id);

        if (is_object($glosario)) {
            return $this->sendResponse(true, 'Listado obtenido exitosamente', $glosario, 200);
        }
        
        return $this->sendResponse(false,'El glosario no existe', null, 404);
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
        $descripcion = $request->input("descripcion");
        $imagen = $request->input("imagen");

        $validator = Validator::make($request->all(), [
            'descripcion'  => 'required',
            'imagen'  => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendResponse(false, 'Error de validacion', $validator->errors(), 400);
        }

        $glosario = Glosario::find($id);
        if ($glosario) {
            $glosario->descripcion = $descripcion;
            $glosario->imagen = $imagen;
            $glosario->activo = 'S';
          
    
            if ($glosario->save()) {

                return $this->sendResponse(true, 'Glosario actualizado', $glosario, 200);
            }

            return $this->sendResponse(false, 'Glosario no actualizado', null, 400);
        }
        
        return $this->sendResponse(false, 'No se encontro el glosario', null, 404);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $glosario = Glosario::find($id);

        if ($glosario) {
            $glosario->activo = ($glosario->activo == 'S') ? 'N' : 'S';
            
            if ($glosario->update()) {
                return $this->sendResponse(true, 'Glosario actualizado', $glosario, 200);
            }
            
            return $this->sendResponse(false, 'Glosario no actualizado', $glosario, 400);
        }
        
        return $this->sendResponse(true, 'No se encontro el glosario', $glosario, 404);
    }

    public function uploadOpcionImg(Request $request){
        $image = $request->file('file0');

        $validator = Validator::make($request->all(), [
            'file0'      =>  'required|image|mimes:jpeg,jpg,png,gif',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Error de validacion', $validator->errors(), 400);
        }

        if ($image) {
            $image_name = time().'.'.$image->getClientOriginalExtension();
            Storage::disk('glosario')->put($image_name, \File::get($image));

            return $this->sendResponse(true, 'Imagen subida', $image_name, 200);
        }
        
        return $this->sendResponse(false, 'Error al subir imagen', null, 400);
    }

    public function getOpcionImg($filename){
        $isset = Storage::disk('glosario')->exists($filename);
        if ($isset) {
            $file = Storage::disk('glosario')->get($filename);
            return new Response($file);
        }
        
        return $this->sendResponse(false, 'La imagen no existe', null, 404);
    }
}
