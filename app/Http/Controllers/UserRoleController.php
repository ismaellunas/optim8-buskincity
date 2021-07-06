<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
//use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;
//use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Auth;

class UserRoleController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $roles = Role::all();
        return Inertia::render('UserRoles', [
            'user' => $user,
            'roles' => $roles,
            'assignedRoles' => $user->roles->pluck('id'),
            'can' => [
                'blog_create' => $user->hasPermissionTo('blog.create'),
                'blog_update' => $user->hasPermissionTo('blog.update'),
                'blog_delete' => $user->hasPermissionTo('blog.delete'),
                'profile_view' => $user->hasPermissionTo('profile.view'),
            ]
        ]);
    }

    public function update(Request $request/*, $id*/)
    {
        if ($request->has('id')) {
            Role::find($request->input('id'))->update($request->all());
            return redirect()
                ->back()
                ->with('message', 'Role Updated Successfully.');
        }
    }
}
