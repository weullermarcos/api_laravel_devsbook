<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    private $loggedUser;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->middleware('auth:api');

        //pega o usuário logado
        $loggedUser = auth()->user();
    }
}
