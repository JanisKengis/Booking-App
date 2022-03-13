<?php

namespace App\Controllers;

use App\Database;
use App\Redirect;
use App\View;
use PDO;

class UsersController
{
    public function index(): View
    {
        return new View('Users/index');
    }

    public function show(array $data): View
    {
        return new View('Users/show', ['id' => $data['id']]);
    }

    public function signUp(): View
    {
        return new View('Users/signup');
    }

    public function register(): Redirect
    {
        $hashedPwd = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $registerQuery = Database::connect()
            ->prepare('INSERT INTO users(name, last_name, email, password) VALUE (?, ?, ?, ?)')
            ->execute([$_POST['name'], $_POST['lastname'], $_POST['email'], $hashedPwd]);

        return new Redirect('/');
    }

    public function signIn(): View
    {
        return new View('Users/signin');
    }

    public function enter(): Redirect
    {
        $query = Database::connect();
        $signInQuery = $query->prepare('SELECT * FROM users WHERE email=?');
        $signInQuery->execute([$_POST['login_email']]);
        $signInData = $signInQuery->fetchAll(PDO::FETCH_ASSOC);

        $passwordVerified = password_verify($_POST['login_password'], $signInData[0]['password']);
        if (!$passwordVerified) {
            return new Redirect('/errors');
        }

        session_start();
        $_SESSION['id'] = $signInData[0]['id'];
        $_SESSION['name'] = $signInData[0]['name'];
        $_SESSION['email'] = $signInData[0]['email'];

        return new Redirect('/apartments');
    }

    public function error(): View
    {
        return new View('Users/error');
    }

    public function logout(): Redirect
    {
        session_start();
        session_unset();
        session_destroy();
        return new Redirect('/');
    }
}