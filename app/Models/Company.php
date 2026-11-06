<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Company extends Model
{
    use HasFactory;

    protected $guarded = [
       'id'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }


    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'parent_id');
    }

    
    public function children(): HasMany
    {
        return $this->hasMany(Company::class, 'parent_id');
    }

    
    public function targetTickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'target_company_id');
    }

    public function getRootCompany(): Company
    {
        $company = $this;
        while ($company->parent_id && $company->parent) {
            $company = $company->parent;
        }
        return $company;
    }

    
    public function canInteractWith(Company $targetCompany): bool
    {
        return $this->getRootCompany()->id === $targetCompany->getRootCompany()->id;
    }

    
    public function getGroupCompanies()
    {
        $root = $this->getRootCompany();
        
        $ids = [$root->id];
        $collectChildren = function ($company) use (&$ids, &$collectChildren) {
            foreach ($company->children as $child) {
                $ids[] = $child->id;
                $collectChildren($child);
            }
        };
        $collectChildren($root);

        return Company::whereIn('id', $ids)->get();
    }

}