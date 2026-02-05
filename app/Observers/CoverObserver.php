<?php

namespace App\Observers;
use App\Models\Cover;

class CoverObserver
{
    /**
     * Ejecutar cuando se va a crear un nuevo Cover (ANTES de insertarlo en BD).
     * Usamos 'creating' para asignar la posición antes de la inserción.
     */
    public function creating(Cover $cover)
    {
        // Auto-asignar orden al crear si no la tiene
        if (!$cover->order || $cover->order === 0) {
            $cover->order = (Cover::max('order') ?? 0) + 1;
        }
    }

    /**
     * Ejecutar cuando se actualiza un Cover.
     */
    public function updated(Cover $cover)
    {
        // Aquí puedes agregar lógica adicional si es necesario
    }

    /**
     * Ejecutar cuando se elimina un Cover.
     */
    public function deleted(Cover $cover)
    {
        // Aquí puedes agregar lógica de limpieza si es necesario
    }
}
