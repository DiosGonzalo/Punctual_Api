<?php

namespace App\Http\Controllers;

use App\Models\Horario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class HorarioController extends Controller
{
    public function index()
    {
        return response()->json(Horario::all());
    }

    public function store(Request $request)
    {
        $payload = $this->normalizeHorarioPayload($request->all());

        $data = Validator::make($payload, [
            'nombre' => ['required', 'string', 'max:255'],
            'horas' => ['required', 'string', 'max:255'],
            'dias' => ['required', 'string', 'max:255'],
        ])->validate();

        $horario = Horario::create($data);
        return response()->json($horario, 201);
    }

    public function show(Horario $horario)
    {
        return response()->json($horario);
    }

    public function update(Request $request, Horario $horario)
    {
        $payload = $this->normalizeHorarioPayload($request->all());

        $data = Validator::make($payload, [
            'nombre' => ['sometimes', 'required', 'string', 'max:255'],
            'horas' => ['sometimes', 'required', 'string', 'max:255'],
            'dias' => ['sometimes', 'required', 'string', 'max:255'],
        ])->validate();

        $horario->update($data);
        return response()->json($horario);
    }

    private function normalizeHorarioPayload(array $payload): array
    {
        $aliases = [
            'nombre' => ['name', 'title', 'nombre_horario'],
            'horas' => ['hours', 'time_range', 'rango_horas', 'hora_inicio_hora_fin'],
            'dias' => ['days', 'dias_laborales', 'working_days'],
        ];

        foreach ($aliases as $target => $keys) {
            if (array_key_exists($target, $payload)) {
                continue;
            }

            foreach ($keys as $key) {
                if (array_key_exists($key, $payload)) {
                    $payload[$target] = $payload[$key];
                    break;
                }
            }
        }

        return $payload;
    }

    public function destroy(Horario $horario)
    {
        $horario->delete();
        return response()->json(null, 204);
    }
}
