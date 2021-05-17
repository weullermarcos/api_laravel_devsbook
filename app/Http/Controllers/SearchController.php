<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SearchController extends Controller
{
    private $loggedUser;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function search(Request $request){

        $array['error'] = '';

        $txt = $request->input('txt');

        if(trim($txt) == ''){
            $array['error'] = "busca vazia";
            return $array;
        }

        //Busca de usuarios
        $userList = User::where('name', 'like', '%'.$txt.'%')->get();

        foreach ($userList as $item){

            $array['users'][] = [

                'id' => $item['id'],
                'name' => $item['name'],
                'avatar' => url('/media/avatars/' . $item['avatar'])
            ];
        }

        return $array;
    }
}
