<?php

namespace App\Observers;
use App\Models\Cover;

class CoverObserver
{
    /**
     * Ejecutar cuando se crea un nuevo Cover (DESPUÉS de insertarlo en BD).
     * Usamos 'created' en lugar de 'creating' porque Create() en Eloquent
     * dispara los hooks después de la inserción.
     */
    public function created(Cover $cover)
    {
        // Auto-asignar posición al crear si no la tiene
        if (!$cover->position || $cover->position === 0) {
            $cover->update([
                'position' => (Cover::max('position') ?? 0) + 1
            ]);
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
