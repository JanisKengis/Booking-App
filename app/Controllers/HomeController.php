<?php

namespace App\Controllers;

use App\View;

class HomeController
{
    public function home(): View
    {

        return new View('index', [
            'id' => $_SESSION['id'],
            'name' => $_SESSION['name'],
            'email' => $_SESSION['email']
        ]);
    }
}