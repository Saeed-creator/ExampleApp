<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AccountDeletionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/** Public pages required by the App Store and Google Play. */
class PageController extends Controller
{
    public function privacy()
    {
        return view('pages.privacy');
    }

    public function terms()
    {
        return view('pages.terms');
    }

    public function support()
    {
        return view('pages.support');
    }

    public function deleteAccountInfo()
    {
        return view('pages.delete-account');
    }

    /**
     * Lets people delete their account without installing the app: they prove
     * ownership with email + password, then everything is removed immediately.
     */
    public function deleteAccount(Request $request, AccountDeletionService $deletion)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', strtolower(trim($data['email'])))->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => __('app.delete_invalid_credentials')]);
        }

        $deletion->delete($user);

        return view('pages.delete-account-done');
    }
}
