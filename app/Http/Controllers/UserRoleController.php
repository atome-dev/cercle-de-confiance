<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserRoleController extends Controller
{
    public function update(Request $request, User $user)
    {
        $this->authorize('assignRole', $user);

        $validated = $request->validate([
            'roles' => ['array'],
            'roles.*' => ['string', 'in:administrateur,membre'],
        ]);

        $user->syncRoles($validated['roles'] ?? []);

        return back()->with('success', 'Rôles mis à jour avec succès.');
    }
}
