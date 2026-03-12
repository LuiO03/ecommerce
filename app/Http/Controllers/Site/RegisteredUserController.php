<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Mail\UserRegistered;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;

class RegisteredUserController extends Controller
{
    /**
     * Mostrar formulario de registro público.
     */
    public function create()
    {
        return view('auth.admin-register');
    }

    /**
     * Registrar un nuevo usuario del sitio.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|min:3|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'required|email:rfc,dns|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'address' => 'nullable|string|max:255',
            'document_type' => 'nullable|string|in:DNI,RUC,CE,PASAPORTE',
            'document_number' => 'nullable|string|max:30',
        ]);

        $name = ucwords(mb_strtolower($validated['name']));
        $lastName = !empty($validated['last_name'])
            ? ucwords(mb_strtolower($validated['last_name']))
            : null;

        $user = User::create([
            'name' => $name,
            'last_name' => $lastName,
            'email' => $validated['email'],
            // El cast "hashed" en el modelo se encarga de encriptar
            'password' => $validated['password'],
            'address' => $validated['address'] ?? null,
            'document_type' => $validated['document_type'] ?? null,
            'document_number' => $validated['document_number'] ?? null,
            'status' => true,
        ]);

        // Asignar rol Cliente usando Spatie Permissions
        $user->assignRole('Cliente');

        // Enviar correo de bienvenida / confirmación de registro
        Mail::to($user->email)->send(new UserRegistered($user));

        /*
        Session::flash('info', [
            'type' => 'success',
            'title' => 'Cuenta creada',
            'message' => "La cuenta de <strong>{$user->name}</strong> se creó correctamente. Ahora puedes iniciar sesión.",
            ]);
        */
        Session::flash('toast', [
            'type' => 'success',
            'title' => 'Cuenta creada',
            'message' => "La cuenta de <strong>{$user->name}</strong> se creó correctamente. Ahora puedes iniciar sesión.",
        ]);


        // Mensaje para el banner de estado en el login
        Session::flash('status', '
        Tu cuenta ha sido creada correctamente. Revisa tu correo para verificar tu dirección de email y activar tu cuenta.
        ');

        return redirect()->route('login');
    }
}
