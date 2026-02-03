<?php

namespace App\Observers;
use App\Models\Cover;

class CoverObserver
{
    public function creating(Cover $cover)
    {
        // Auto-asignar posición al crear
        $cover->position = (Cover::max('position') ?? 0) + 1;
    }
}
