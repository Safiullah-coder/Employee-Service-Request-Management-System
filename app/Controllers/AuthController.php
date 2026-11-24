<?php

namespace App\Controllers;

use App\Models\UserModel;

class AuthController extends BaseController
{
    public function loginForm()
    {
        if (session()->get('logged_in')) {
            return redirect()->to(site_url('dashboard'));
        }
        return view('auth/login');
    }

    public function login()
    {
        $email    = trim((string)$this->request->getPost('email'));
        $password = (string)$this->request->getPost('password');

        $userModel = new UserModel();
        $user = $userModel->findByEmail($email);

        if (!$user || !password_verify($password, $user['password'])) {
            return redirect()->back()->with('error', 'The email address or password is incorrect.');
        }

        session()->regenerate();
        session()->set([
            'logged_in' => true,
            'user_id'   => $user['id'],
            'name'      => $user['name'],
            'email'     => $user['email'],
            'role'      => $user['role'],
        ]);

        return redirect()->to(site_url('dashboard'));
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to(site_url('login'))->with('success', 'You have been signed out.');
    }
}
