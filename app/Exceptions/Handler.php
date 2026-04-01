<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Routing\Exceptions\InvalidSignatureException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $exception)
    {
        if ($exception instanceof AuthorizationException) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'No tienes permisos para realizar esta acción.'
                ], 403);
            }
            return redirect()->intended(route('admin.dashboard'))->with('info', [
                'type' => 'danger',
                'header' => 'Acceso denegado',
                'title' => 'Permiso insuficiente',
                'message' => 'No tienes permisos para realizar esta acción.'
            ]);
        }

        if ($exception instanceof TokenMismatchException) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Tu sesión expiró. Actualiza la página e inténtalo de nuevo.'
                ], 419);
            }

            return redirect()->route('welcome.index');
        }

        // Enlaces firmados inválidos o expirados (por ejemplo, verificación de correo)
        if ($exception instanceof InvalidSignatureException) {
            // Para enlaces de verificación de email del flujo público mostramos una pantalla amigable
            if ($request->routeIs('site.verification.verify')) {
                return response()->view('auth.admin-confirm-email-failure', [
                    'title' => 'Enlace de verificación expirado',
                    'message' => 'El enlace de verificación ha expirado o ya fue utilizado.
                        Si aún no has verificado tu cuenta, inicia sesión con tu correo y contraseña para solicitar un nuevo
                        correo de verificación.',
                ], 403);
            }
        }
        return parent::render($request, $exception);
    }
}
