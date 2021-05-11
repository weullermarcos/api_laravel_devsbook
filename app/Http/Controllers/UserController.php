<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class UserController extends Controller
{

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function update(Request $request){

        $name = $request->input('name');
        $email = $request->input('email');
        $birthdate = $request->input('birthdate');
        $city = $request->input('city');
        $work = $request->input('work');
        $password = $request->input('password');
        $password_confirm = $request->input('password_confirm');

        //recupera as informações do usuário logado
        $user = User::find(Auth::user()->id);

        if(!$user){
            $array['error'] = 'usuario nao logado';
            return $array;
        }

        //name
        if($name)
            $user->name = $name;

        //email
        if($email){

            //verifica se o e-mail é diferente
            if($email != $user->email){

                //verifica se o e-mail já existe
                $emailExiste = User::where('email', $email)->count();
                if($emailExiste > 0){
                    $array['error'] = 'email ja existe';
                    return $array;
                }
            }

            $user->email = $email;
        }

        //birthdate
        if($birthdate){

            if(strtotime($birthdate) === false){
                $array['error'] = 'data de nascimento invalida';
                return $array;
            }

            $user->birthdate = $birthdate;
        }

        //city
        if($city)
            $user->city = $city;

        //work
        if($work)
            $user->work = $work;

        if($password && $password_confirm){

            if($password != $password_confirm){
                $array['error'] = 'as senhas nao conferem';
                return $array;
            }

            //gera o hash da nova senha
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $user->password = $hash;
        }

        $user->save();

        $array['sucesso'] = 'usuario alterado com sucesso';
        return $array;
    }

}
