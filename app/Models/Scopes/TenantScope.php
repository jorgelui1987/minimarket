<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class TenantScope implements Scope
{
    /**
     * Evita recursión infinita: resolver Auth::user() consulta el modelo User,
     * lo que vuelve a disparar este scope. Mientras se resuelve el usuario,
     * no aplicamos el filtro (la búsqueda del usuario es por su id, sin tenant).
     */
    protected static bool $resolving = false;

    public function apply(Builder $builder, Model $model): void
    {
        if (static::$resolving) {
            return;
        }

        static::$resolving = true;

        try {
            $user = Auth::user();

            if ($user && $user->tenant_id && ! $user->is_super_admin) {
                $builder->where($model->getTable() . '.tenant_id', $user->tenant_id);
            }
        } finally {
            static::$resolving = false;
        }
    }
}
