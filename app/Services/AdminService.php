<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;

class AdminService
{
    public function getFilteredUsers(Request $request, User $authUser)
    {
        $query = User::with(['roles', 'company']);

        if ($authUser->level !== 'creator') {
            $query->where('company_id', $authUser->company_id);
        } else {
            if ($request->filled('company_id')) {
                $query->where('company_id', $request->company_id);
            }
        }

        return $query->when($request->search, function ($query, $search) {
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();
    }

    public function updateUser(Request $request, User $user): void
    {
        $user->update([
            'level' => $request->level,
        ]);

        if ($request->level === 'admin') {
            $user->roles()->sync($request->roles ?? []);
        } else {
            $user->roles()->detach();
        }
    }

    public function deleteUser(User $user): void
    {
        $user->delete();
    }
}