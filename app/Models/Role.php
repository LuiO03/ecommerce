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

    public function isProtected(): bool
    {
        return in_array($this->name, [
            'Administrador',
            'Superadministrador',
            'Cliente'
        ]);
    }

    public function canBeEditedBy(User $user): bool
    {
        if (!$user->can('roles.edit')) {
            return false;
        }

        // Superadmin puede editar todo
        if ($user->hasRole('Superadministrador')) {
            return true;
        }

        // Usuarios normales NO pueden editar roles protegidos
        return !$this->isProtected();
    }

    public function canBeAssignPermissions(User $user): bool
    {
        if (!$user->can('roles.assign-permissions')) {
            return false;
        }

        // Superadmin puede asignar todo
        if ($user->hasRole('Superadministrador')) {
            return true;
        }

        // Usuarios normales NO pueden asignar roles protegidos
        return !$this->isProtected();
    }
}
