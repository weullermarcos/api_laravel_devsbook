<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * AuthController constructor.
     */
    public function __construct()
    {
        //só descomentar após as rotas abaixo estarem criadas
        $this->middleware('auth:api', ['except' => 'login', 'create', 'unauthorized']);
    }

    public function unauthorized(){

        return response()->json(['error' => 'nao autorizado'], 401);
    }

    public function login(Request $request){

        $email = $request->input('email');
        $password = $request->input('password');

        //tenta o login
        $token = auth()->attempt([
            'email' => $email,
            'password' => $password
        ]);

        if($token)
            $array['token'] = $token;
        else
            $array = ['erro' => 'erro ao realizar login'];

        return $array;
    }

    public function logout(){

        auth()->logout();
        return ['error' => ''];
    }

    public function refresh(){

        $token = auth()->refresh();
        return ['error' => '', 'token' => $token];
    }

    public function create(Request $request){

        $name = $request->input('name');
        $password = $request->input('password');
        $email = $request->input('email');
        $birthdate = $request->input('birthdate');

        if($name && $password && $email && $birthdate){

            //validando formato da data
            if(strtotime($birthdate) === false){
                $array['error'] = 'data invalida';
                return $array;
            }

            //faz um select no banco para ver se existe usuario com esse e-mail
            $existe = User::where('email', $email)->count();

            //validando se o e-mail já está cadastrado
            if($existe > 0){
                $array['error'] = 'email ja cadastrado';
                return $array;
            }

            //cria um hash da senha
            $hash = password_hash($password, PASSWORD_DEFAULT);

            //cria e salva o novo usuário
            $newUser = new User();
            $newUser->name = $name;
            $newUser->email = $email;
            $newUser->password = $hash;
            $newUser->birthdate = $birthdate;
            $newUser->save();

            //tenta realizar o login
            $token = auth()->attempt([
                'email' => $email,
                'password' => $password
            ]);

            if(!$token){
                $array['error'] = 'erro ao logar';
                return $array;
            }

            $array['token'] = $token;
        }
        else{

            $array['error'] = 'Nao foram enviado todos os campos';
        }

        return $array;
    }
}
