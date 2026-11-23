<?php

namespace App\Policies;

use App\Models\Sla;
use App\Models\User;

class SlaPolicy
{
    
    protected function isCompanyManager(User $user): bool
    {
        return $user->roles()->where('slug', 'company-manager')->exists()
            || $user->hasPermission('slas.manage');
    }

    public function viewAny(User $user): bool
    {
        return $user->isCreator() || $this->isCompanyManager($user);
    }

    public function manageCompany(User $user, int $companyId): bool
    {
        // کریتور به همه شرکت‌ها دسترسی دارد
        if ($user->isCreator()) {
            return true;
        }

        // مدیر شرکت فقط به شرکت خودش دسترسی دارد و باید حتماً نقش مدیر شرکت را داشته باشد
        return $this->isCompanyManager($user) && (int) $user->company_id === $companyId;
    }
}