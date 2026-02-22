<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Owner;
use App\Models\Animal;
use App\Http\Resources\OwnerResource;
use App\Http\Resources\AnimalResource;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/owners', function () {
    return OwnerResource::collection(Owner::all());
});

Route::get('/owner/{id}', function ($id) {
    $owner = Owner::find($id);

    if (!$owner) {
        return response()->json([
            'mensaje' => 'No se ha encontrado ningún dueño con el identificador ' . $id
        ], 404);
    } else {
        return new OwnerResource($owner);
    }
});

Route::post('/owner/{nombre}/{apellido}', function ($nombre, $apellido) {
    if (!$nombre || !$apellido) {
        return response()->json([
            'mensaje' => 'Nombre o apellido vacío, rellene los 2 campos'
        ], 400);
    }

    $owner = Owner::create([
        'name' => $nombre,
        'surname' => $apellido,
    ]);

    return response()->json([
        'mensaje' => 'Dueño añadido correctamente',
        'datos' => new OwnerResource($owner)
    ], 201);
});

Route::put('/owner/{id}', function (Request $request, $id) {
    $owner = Owner::find($id);

    if (!$owner) {
        return response()->json([
            'mensaje' => 'No se ha encontrado ningún Owner con el identificador ' . $id
        ], 404);
    }

    $nombre = $request->input('nombre');
    $apellido = $request->input('apellido');

    if ($nombre) $owner->name = $nombre;
    if ($apellido) $owner->surname = $apellido;

    $owner->save();

    return response()->json([
        'mensaje' => 'Owner con el identificador ' . $id . ' actualizado correctamente',
        'datos_actualizados' => new OwnerResource($owner),
    ]);
});

Route::delete('/owner/{id}', function ($id) {
    $owner = Owner::find($id);

    if (!$owner) {
        return response()->json([
            'mensaje' => 'No se ha encontrado ningún dueño con el identificador ' . $id
        ], 404);
    }

    $owner->delete();

    return response()->json([
        'mensaje' => 'Se ha eliminado correctamente el Owner con identificador ' . $id
    ]);
});

Route::get('/animals', function () {
    $animals = Animal::all();
    if ($animals->isEmpty()) {
        return response()->json([
            'mensaje' => 'No hay ningún animal registrado'
        ], 404);
    }
    return AnimalResource::collection($animals);
});

Route::get('/animal/{id}', function ($id) {
    $animal = Animal::find($id);

    if (!$animal) {
        return response()->json([
            'mensaje' => 'No se ha encontrado ningún animal con el identificador ' . $id
        ], 404);
    } else {
        return new AnimalResource($animal);
    }
});

Route::post('/animal/{owner_id}/{tipo}/{nombre}/{peso}', function (Request $request, $owner_id, $tipo, $nombre, $peso) {
    $owner = Owner::find($owner_id);

    if (!$owner) {
        return response()->json([
            'mensaje' => 'No se ha podido registrar el animal, no se ha encontrado ningún dueño con el identificador ' . $owner_id
        ], 403);
    }

    $validTypes = ['perro', 'gato', 'conejo', 'hámster', 'hamster'];

    if (!$tipo) {
        return response()->json(['mensaje' => 'El campo tipo no puede estar vacío'], 400);
    }

    if (!$nombre) {
        return response()->json(['mensaje' => 'El campo nombre no puede estar vacío'], 400);
    }

    if (!is_numeric($peso)) {
        return response()->json(['mensaje' => 'El peso debe ser un número decimal'], 400);
    }

    if (!in_array(strtolower($tipo), $validTypes)) {
        return response()->json(['mensaje' => 'El tipo de animal seleccionado no está disponible'], 400);
    }

    $animal = Animal::create([
        'nombre' => $nombre,
        'tipo' => $tipo,
        'peso' => $peso,
        'enfermedad' => $request->input('enfermedad'),
        'comentarios' => $request->input('comentarios'),
        'owner_id' => $owner_id,
    ]);

    return response()->json([
        'mensaje' => 'Animal añadido correctamente, el identificador del dueño es ' . $owner_id,
        'datos' => new AnimalResource($animal)
    ], 201);
});

Route::put('/animal/{id}', function (Request $request, $id) {
    $animal = Animal::find($id);

    if (!$animal) {
        return response()->json([
            'mensaje' => 'No se ha encontrado ningún animal con el identificador ' . $id
        ], 404);
    }

    $nombre = $request->input('nombre');
    $tipo = $request->input('tipo');
    $peso = $request->input('peso');
    $enfermedad = $request->input('enfermedad');
    $comentarios = $request->input('comentarios');

    $validTypes = ['perro', 'gato', 'conejo', 'hámster', 'hamster'];

    if ($tipo && !in_array(strtolower($tipo), $validTypes)) {
        return response()->json(['mensaje' => 'El tipo de animal seleccionado no está disponible'], 400);
    }

    if ($peso && !is_numeric($peso)) {
         return response()->json(['mensaje' => 'El peso debe ser un número decimal'], 400);
    }

    if ($nombre) $animal->nombre = $nombre;
    if ($tipo) $animal->tipo = $tipo;
    if ($peso) $animal->peso = $peso;
    if ($enfermedad !== null) $animal->enfermedad = $enfermedad;
    if ($comentarios !== null) $animal->comentarios = $comentarios;

    $animal->save();

    return response()->json([
        'mensaje' => 'Animal con el identificador ' . $id . ' actualizado correctamente',
        'datos_actualizados' => new AnimalResource($animal)
    ], 200);
});

Route::delete('/animal/{id}', function ($id) {
    $animal = Animal::find($id);

    if (!$animal) {
        return response()->json([
            'mensaje' => 'No se ha encontrado ningún animal con el identificador ' . $id
        ], 404);
    }

    $animal->delete();

    return response()->json([
        'mensaje' => 'Se ha eliminado correctamente el animal con identificador ' . $id
    ]);
});