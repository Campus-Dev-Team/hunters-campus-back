<?php

namespace App\Http\Controllers\Desafios;

use App\Http\Controllers\Controller;
use App\Models\Retos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RetosController extends Controller {

    public function ver($idReto) {
        try {
            $reto = Retos::find($idReto);

            if (!$reto) return $this->errorResponse(['cod' => 'rne']);

            return $this->successResponse(['reto' => $reto]);
        } catch (\Throwable $th) {
            return $this->capturar($th);
        }
    }

    public function editar($idReto, Request $request) {
        try {

            $request->validate([
                'titulo' => 'required|max:60',
                'hora' => 'required',
                'lugar' => 'required|max:40',
                'descripcion' => 'required|max:1200',
                'puntos' => 'required|numeric',
                'fecha' => 'required|date',
            ]);

            return DB::transaction(function () use ($idReto, $request) {

                $model = Retos::find($idReto);

                if (!$model) return $this->errorResponse(['cod' => 'rne']);

                $model->fecha = $request->fecha;
                $model->titulo = $request->titulo;
                $model->hora = $request->hora;
                $model->lugar = $request->lugar;
                $model->descripcion = $request->descripcion;
                $model->puntos = $request->puntos;
                $model->save();

                return $this->successResponse(['model' => $model]);
            });
        } catch (\Throwable $th) {
            return $this->capturar($th);
        }
    }

    public function crear(Request $request) {
        try {
            $request->validate([
                'titulo' => 'required|max:60',
                'descripcion' => 'required|max:1200',
                'cantidad' => 'required|numeric|between:2,4',
                'hora' => 'required',
                'lugar' => 'required|max:40',
                'puntos' => 'required|numeric',
                'fecha' => 'required|date',
                'created_by' => 'required|numeric',
            ]);


            return DB::transaction(function () use ($request) {

                $model = new Retos();
                $model->fecha = $request->fecha;
                $model->titulo = $request->titulo;
                $model->descripcion = $request->descripcion;
                $model->hora = $request->hora;
                $model->lugar = $request->lugar;
                $model->cantidad = $request->cantidad;
                $model->puntos = $request->puntos;
                $model->created_by = $request->created_by;
                $model->save();

                return $this->successResponse(['model' => $model]);
            });
        } catch (\Throwable $th) {
            return $this->capturar($th);
        }
    }
}
