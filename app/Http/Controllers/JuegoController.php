<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\BaseController as BaseController;
use Illuminate\Http\Response;
use Validator;
use Illuminate\Support\Facades\Storage;
use App\Juego;
use App\JuegoOpciones;

class JuegoController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Juego::with(['subNivel.nivel', 'opciones']);

        $descripcion = $request->query('descripcion');
        if ($descripcion) {
            $query->where('descripcion', 'LIKE', '%'.$descripcion.'%');
        }

        $sub_niveles_id = $request->query('sub_niveles_id');
        if ($sub_niveles_id) {
            $query->where('sub_niveles_id', '=', $sub_niveles_id);
        }

        $activo = $request->query('activo');
        if ($activo) {
            $query->where('activo', '=', $activo);
        }

        $paginar = $request->query('paginar');
        $listar = (boolval($paginar)) ? 'paginate' : 'get';
        $data = $query->orderBy('sub_niveles_id', 'asc')->orderBy('created_at', 'asc')->$listar();

        
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
        $nombre = $request->input("nombre");
        $imagen = $request->input("imagen");
        $sub_niveles_id = $request->input("sub_niveles_id");
        $opciones = $request->input("opciones");

        $validator = Validator::make($request->all(), [
            'nombre'  => 'required',
            'imagen'  => 'required',
            'sub_niveles_id' =>'required',
            'opciones' =>'required'
        ]);

        if ($validator->fails()) {
            return $this->sendResponse(false, 'Error de validacion', $validator->errors(), 400);
        }

        //validar que lleguen las opciones
        if (count($opciones) <= 0) {
            return $this->sendResponse(false, 'Debe agregar por lo menos una opcion', null, 400);
        }

        $juego = new Juego();
        $juego->nombre = $nombre;
        $juego->imagen = $imagen;
        $juego->sub_niveles_id = $sub_niveles_id;
        $juego->activo = 'S';

        if ($juego->save()) {

            foreach ($opciones as $opcion) {
                $juegoOpcion = new JuegoOpciones();
                $juegoOpcion->descripcion = $opcion['descripcion'];
                $juegoOpcion->correcto = $opcion['correcto'];
                $juegoOpcion->juegos_id = $juego->id;
                
                if (!$juegoOpcion->save()) {
                    return $this->sendResponse(false, 'Opcion no registrada, '.$opcion['descripcion'], $opcion, 400);
                    break;
                }
            }

            return $this->sendResponse(true, 'Juego registrado', $juego, 200);
        }
        
        return $this->sendResponse(false,'Juego no registrado', null, 400);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $juego = Juego::with(['subNivel.nivel', 'opciones'])->find($id);

        if (is_object($juego)) {
            return $this->sendResponse(true, 'Listado obtenido exitosamente', $juego, 200);
        }
        
        return $this->sendResponse(false,'El juego no existe', null, 404);
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
        $nombre = $request->input("nombre");
        $imagen = $request->input("imagen");
        $sub_niveles_id = $request->input("sub_niveles_id");
        $opciones = $request->input("opciones");

        $validator = Validator::make($request->all(), [
            'nombre'  => 'required',
            'imagen'  => 'required',
            'sub_niveles_id' =>'required',
            'opciones' =>'required'
        ]);

        if ($validator->fails()) {
            return $this->sendResponse(false, 'Error de validacion', $validator->errors(), 400);
        }

        //validar que lleguen las opciones
        if (count($opciones) <= 0) {
            return $this->sendResponse(false, 'Debe agregar por lo menos una opcion', null, 400);
        }

        $juego = Juego::find($id);
        if ($juego) {
            $juego->nombre = $nombre;
            $juego->imagen = $imagen;
            $juego->activo = 'S';
    
            if ($juego->save()) {
                $opcion = JuegoOpciones::where('juegos_id', $id)->delete();
                foreach ($opciones as $opcion) {
                    $juegoOpcion = new JuegoOpciones();
                    $juegoOpcion->descripcion = $opcion['descripcion'];
                    $juegoOpcion->correcto = $opcion['correcto'];
                    $juegoOpcion->juegos_id = $juego->id;
                    
                    if (!$juegoOpcion->save()) {
                        return $this->sendResponse(false, 'Opcion no registrada, '.$opcion['descripcion'], $opcion, 400);
                        break;
                    }
                }

                return $this->sendResponse(true, 'Juego actualizado', $juego, 200);
            }

            return $this->sendResponse(false, 'Juego no actualizado', null, 400);
        }
        
        return $this->sendResponse(false, 'No se encontro el juego', null, 404);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $juego = Juego::find($id);

        if ($juego) {
            $juego->activo = ($juego->activo == 'S') ? 'N' : 'S';
            
            if ($juego->update()) {
                return $this->sendResponse(true, 'Juego actualizado', $juego, 200);
            }
            
            return $this->sendResponse(false, 'Juego no actualizado', $juego, 400);
        }
        
        return $this->sendResponse(true, 'No se encontro el juego', $juego, 404);
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
            Storage::disk('juego')->put($image_name, \File::get($image));

            return $this->sendResponse(true, 'Imagen subida', $image_name, 200);
        }
        
        return $this->sendResponse(false, 'Error al subir imagen', null, 400);
    }

    public function getOpcionImg($filename){
        $isset = Storage::disk('juego')->exists($filename);
        if ($isset) {
            $file = Storage::disk('juego')->get($filename);
            return new Response($file);
        }
        
        return $this->sendResponse(false, 'La imagen no existe', null, 404);
    }
    

    public function juegoBySubNivel($sub_nivel_id)
    {
        $data = Juego::with(["subNivel", "opciones"])->where('sub_niveles_id', '=', $sub_nivel_id)->get();
        
        if (count($data) <= 0) return $this->sendResponse(true, 'No se encontraron juegos para el nivel', null, 404);

        return $this->sendResponse(true, 'Listado obtenido exitosamente', $data, 200);
    }
}
