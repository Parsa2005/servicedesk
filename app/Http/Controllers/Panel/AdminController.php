<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\Company;
use App\Http\Requests\UpdateUserRequest;
use App\Services\AdminService;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class AdminController extends Controller
{
    use AuthorizesRequests;

    protected $adminService;

    public function __construct(AdminService $adminService)
    {
        $this->adminService = $adminService;
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', User::class);

        $users = $this->adminService->getFilteredUsers($request, auth()->user());
        $companies = Company::orderBy('name')->get();

        return view('panel.users.index', compact('users', 'companies'));
    }

    public function edit(User $user)
    {
        $this->authorize('update', $user);

        $user->load('roles');
        $roles = Role::with('permissions')->get();

        return view('panel.users.edit', compact('user', 'roles'));
    }

    public function update(UpdateUserRequest $request, User $user)
    {
      
        
        $this->adminService->updateUser($request, $user);

        return redirect()->route('panel.users.index')
            ->with('success', 'دسترسی‌ها و نقش‌های کاربر با موفقیت بروزرسانی شد.');
    }

    public function destroy(User $user)
    {
        $this->authorize('delete', $user);

        if ($user->id === auth()->id()) {
            return back()->with('error', 'امکان حذف حساب کاربری خودتان وجود ندارد.');
        }

        $this->adminService->deleteUser($user);

        return back()->with('success', 'کاربر با موفقیت حذف شد.');
    }
}