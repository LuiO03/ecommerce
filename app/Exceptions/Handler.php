<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Auth\Access\AuthorizationException;
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
        return parent::render($request, $exception);
    }
}
