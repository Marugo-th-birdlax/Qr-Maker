<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (session('user')) {
            return redirect()->route('home');
        }
        return view('auth.login');
    }

    public function login(Request $req)
    {
        $data = $req->validate([
            'employee_id' => ['required','string'],
            'password'    => ['required','string'],
        ],[],[
            'employee_id' => 'รหัสพนักงาน',
            'password'    => 'รหัสผ่าน',
        ]);

        $user = User::where('employee_id', $data['employee_id'])->first();

        if (!$user || !Hash::check($data['password'], $user->password) || !$user->is_active) {
            return back()->withErrors(['employee_id' => 'รหัสพนักงานหรือรหัสผ่านไม่ถูกต้อง หรือผู้ใช้งานถูกปิดใช้งาน'])
                         ->withInput();
        }

        // เก็บข้อมูลที่ Topbar ใช้
        session([
            'user' => [
                'id'          => $user->id,
                'employee_id' => $user->employee_id,
                'first_name'  => $user->first_name,
                'last_name'   => $user->last_name,
                'email'       => $user->email,
                'role'        => $user->role,
            ],
        ]);

        return redirect()->intended(route('home'));
    }

    public function logout(Request $req)
    {
        $req->session()->forget('user');
        $req->session()->invalidate();
        $req->session()->regenerateToken();
        return redirect()->route('login');
    }
}
