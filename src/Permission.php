<?php

declare(strict_types=1);

namespace GeneaLabs\LaravelGovernor;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Permission extends Model
{
    protected $rules = [
        'entity_name' => 'required',
        'action_name' => 'required',
        'ownership_name' => 'required',
    ];
    protected $fillable = [
        'role_name',
        'entity_name',
        'action_name',
        'ownership_name',
        "team_id",
    ];
    protected $table = "governor_permissions";

    protected static function syncPermissionsSingleton(): void
    {
        $permissionClass = config("genealabs-laravel-governor.models.permission");
        $permissions = (new $permissionClass)
            ->with("role", "team")
            ->toBase()
            ->get();
        app()->instance("governor-permissions", $permissions);
    }

    public static function boot(): void
    {
        parent::boot();

        static::saved(function () {
            self::syncPermissionsSingleton();
        });
        static::deleted(function () {
            self::syncPermissionsSingleton();
        });
    }

    public function entity(): BelongsTo
    {
        return $this->belongsTo(
            config('genealabs-laravel-governor.models.entity'),
            'entity_name'
        );
    }

    public function action(): BelongsTo
    {
        return $this->belongsTo(
            config('genealabs-laravel-governor.models.action'),
            'action_name'
        );
    }

    public function ownership(): BelongsTo
    {
        return $this->belongsTo(
            config('genealabs-laravel-governor.models.ownership'),
            'ownership_name'
        );
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(
            config('genealabs-laravel-governor.models.role'),
            'role_name'
        );
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(
            config('genealabs-laravel-governor.models.team')
        );
    }

    public function getFilteredBy(string $filter = null, string $value = null): Collection
    {
        return $this
            ->where(function ($query) use ($filter, $value) {
                if ($filter) {
                    $query->where($filter, $value);
                }
            })
            ->get();
    }
}
