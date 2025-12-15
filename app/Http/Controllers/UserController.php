<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        return response()->json(User::all());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'apellidos' => ['required', 'string', 'max:255'],
            'id_rol' => ['nullable', 'exists:roles,id'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'telefono' => ['required', 'string', 'max:255'],
            'id_horario' => ['nullable', 'exists:horarios,id'],
            'id_workplace' => ['nullable', 'exists:workplaces,id'],
            'id_modalidad' => ['nullable', 'exists:modalidades,id'],
            'id_departamento' => ['nullable', 'exists:departamentos,id'],
            'is_presente' => ['boolean'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $data['password'] = Hash::make($data['password']);

        $user = User::create($data);
        return response()->json($user, 201);
    }

    public function show(User $user)
    {
        return response()->json($user);
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'nombre' => ['sometimes', 'required', 'string', 'max:255'],
            'apellidos' => ['sometimes', 'required', 'string', 'max:255'],
            'id_rol' => ['sometimes', 'nullable', 'exists:roles,id'],
            'email' => ['sometimes', 'required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'telefono' => ['sometimes', 'required', 'string', 'max:255'],
            'id_horario' => ['sometimes', 'nullable', 'exists:horarios,id'],
            'id_workplace' => ['sometimes', 'nullable', 'exists:workplaces,id'],
            'id_modalidad' => ['sometimes', 'nullable', 'exists:modalidades,id'],
            'id_departamento' => ['sometimes', 'nullable', 'exists:departamentos,id'],
            'is_presente' => ['sometimes', 'boolean'],
            'password' => ['sometimes', 'required', 'string', 'min:8'],
        ]);

        if (array_key_exists('password', $data)) {
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);
        return response()->json($user);
    }

    public function destroy(User $user)
    {
        $user->delete();
        return response()->json(null, 204);
    }
}
