<?php

namespace App\Models;

use App\Models\User;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    use Auditable;

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'model_has_roles',
            'role_id',
            'model_id'
        )->where('model_type', User::class);
    }
}
