<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }


    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function children()
    {
        return $this->hasMany(Permission::class, 'parent_id');
    }

    // رابطه معکوس (والد)
    public function parent()
    {
        return $this->belongsTo(Permission::class, 'parent_id');
    }
}