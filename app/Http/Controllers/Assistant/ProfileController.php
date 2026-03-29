<?php

namespace App\Http\Controllers\Assistant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function show()
    {
        $user = auth()->user()->load(['department', 'course', 'organization', 'otherOrganizations']);
        return view('assistant.profile', compact('user'));
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $user = auth()->user();
        
        if (!\Illuminate\Support\Facades\Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        $user->password = \Illuminate\Support\Facades\Hash::make($request->new_password);
        $user->save();

        return back()->with('success', 'Password updated successfully.');
    }

    public function updateAboutMe(Request $request)
    {
        $request->validate([
            'about_me' => 'nullable|string|max:5000',
        ]);

        $user = auth()->user();
        $user->about_me = $request->about_me;
        $user->save();

        return back()->with('success', 'About Me information updated successfully.');
    }
}
