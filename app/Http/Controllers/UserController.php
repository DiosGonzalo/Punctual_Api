<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        return response()->json(User::with('horario')->get());
    }

    public function store(Request $request)
    {
        $payload = $request->all();

        if (!array_key_exists('id_horario', $payload)) {
            if (array_key_exists('horario_id', $payload)) {
                $payload['id_horario'] = $payload['horario_id'];
            } elseif (array_key_exists('idHorario', $payload)) {
                $payload['id_horario'] = $payload['idHorario'];
            }
        }

        $data = Validator::make($payload, [
            'nombre' => ['required', 'string', 'max:255'],
            'apellidos' => ['required', 'string', 'max:255'],
            'id_rol' => ['nullable', 'exists:roles,id'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'telefono' => ['required', 'string', 'max:255'],
            'foto_perfil' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'id_horario' => ['nullable', 'exists:horarios,id'],
            'id_workplace' => ['nullable', 'exists:workplaces,id'],
            'id_modalidad' => ['nullable', 'exists:modalidades,id'],
            'id_departamento' => ['nullable', 'exists:departamentos,id'],
            'is_presente' => ['boolean'],
            'password' => ['required', 'string', 'min:8'],
        ])->validate();

        if ($request->hasFile('foto_perfil')) {
            $data['foto_perfil'] = $request->file('foto_perfil')->store('avatars', 'public');
        }

        $data['password'] = Hash::make($data['password']);

        $user = User::create($data);
        return response()->json($user, 201);
    }

    public function show(User $user)
    {
        return response()->json($user->load('horario'));
    }

    public function update(Request $request, User $user)
    {
        $payload = $request->all();

        // Accept legacy/alternate keys used by different frontends.
        if (!array_key_exists('id_horario', $payload)) {
            if (array_key_exists('horario_id', $payload)) {
                $payload['id_horario'] = $payload['horario_id'];
            } elseif (array_key_exists('idHorario', $payload)) {
                $payload['id_horario'] = $payload['idHorario'];
            }
        }

        $data = Validator::make($payload, [
            'nombre' => ['sometimes', 'required', 'string', 'max:255'],
            'apellidos' => ['sometimes', 'required', 'string', 'max:255'],
            'id_rol' => ['sometimes', 'nullable', 'exists:roles,id'],
            'email' => ['sometimes', 'required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'telefono' => ['sometimes', 'required', 'string', 'max:255'],
            'foto_perfil' => ['sometimes', 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'id_horario' => ['sometimes', 'nullable', 'exists:horarios,id'],
            'id_workplace' => ['sometimes', 'nullable', 'exists:workplaces,id'],
            'id_modalidad' => ['sometimes', 'nullable', 'exists:modalidades,id'],
            'id_departamento' => ['sometimes', 'nullable', 'exists:departamentos,id'],
            'is_presente' => ['sometimes', 'boolean'],
            'password' => ['sometimes', 'required', 'string', 'min:8'],
        ])->validate();

        if ($request->hasFile('foto_perfil')) {
            if ($user->foto_perfil) {
                Storage::disk('public')->delete($user->foto_perfil);
            }

            $data['foto_perfil'] = $request->file('foto_perfil')->store('avatars', 'public');
        }

        if (array_key_exists('password', $data)) {
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);
        return response()->json($user->load('horario'));
    }

    public function destroy(User $user)
    {
        if ($user->foto_perfil) {
            Storage::disk('public')->delete($user->foto_perfil);
        }

        $user->delete();
        return response()->json(null, 204);
    }

    public function uploadAvatar(Request $request, User $user)
    {
        $request->validate([
            'foto_perfil' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        if ($user->foto_perfil) {
            Storage::disk('public')->delete($user->foto_perfil);
        }

        $path = $request->file('foto_perfil')->store('avatars', 'public');
        $user->update(['foto_perfil' => $path]);

        return response()->json($user->fresh()->load('horario'));
    }

    public function removeAvatar(User $user)
    {
        if ($user->foto_perfil) {
            Storage::disk('public')->delete($user->foto_perfil);
            $user->update(['foto_perfil' => null]);
        }

        return response()->json($user->fresh()->load('horario'));
    }
}
