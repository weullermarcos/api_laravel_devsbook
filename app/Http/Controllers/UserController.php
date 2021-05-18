<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\UserRelation;
use Faker\Provider\DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Image;

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

    public function updateAvatar(Request $request){

        //tipos de imagens permitadas
        $imageTypes = ['image/jpeg', 'image/jpg', 'image/png'];

        $image = $request->file('avatar');

        //se a imagem não foi enviada
        if(! $image){

            $array['error'] = 'imagem nao enviada';
            return $array;
        }

        //se o tipo de imagem não é permitido
        if(! in_array($image->getClientMimeType(), $imageTypes)){

            $array['error'] = 'tipo de imagem nao suportado';
            return $array;
        }

        //gerando  um nome aleatório para a imagem
        $fileName = md5(time().rand(0,99999)) . '.jpg';

        //caminho de onde ficarao os arquivos
        $destPath = public_path('/media/avatars');

        //gera e salva a imagem
        $img = Image::make($image->path())
            ->fit(200,200)
            ->save($destPath . '/' . $fileName);

        //recuper o usuário logado para salvar o caminho da imagem
        $user = User::find(Auth::user()->id);

        $user->avatar = $fileName;
        $user->save();

        //retorna a url do arquivo
        $array['url'] = url('/media/avatars/' . $fileName);
        return $array;
    }

    public function updateCover(Request $request){

        //tipos de imagens permitadas
        $imageTypes = ['image/jpeg', 'image/jpg', 'image/png'];

        $image = $request->file('cover');

        //se a imagem não foi enviada
        if(! $image){

            $array['error'] = 'imagem nao enviada';
            return $array;
        }

        //se o tipo de imagem não é permitido
        if(! in_array($image->getClientMimeType(), $imageTypes)){

            $array['error'] = 'tipo de imagem nao suportado';
            return $array;
        }

        //gerando  um nome aleatório para a imagem
        $fileName = md5(time().rand(0,99999)) . '.jpg';

        //caminho de onde ficarao os arquivos
        $destPath = public_path('/media/covers');

        //gera e salva a imagem
        $img = Image::make($image->path())
            ->fit(850,310)
            ->save($destPath . '/' . $fileName);

        //recuper o usuário logado para salvar o caminho da imagem
        $user = User::find(Auth::user()->id);

        $user->cover = $fileName;
        $user->save();

        //retorna a url do arquivo
        $array['url'] = url('/media/covers/' . $fileName);
        return $array;

    }

    //faz com que a informação do parametro seja opcional
    public function read($id = false){

        $me = true;

        //caso não seja informado id o id do meu usuário
        if(!$id){
            $id = Auth::user()->id;
            $me = false;
        }

        $user = User::find($id);

        //se o usuário nao existir
        if(!$user){
            $array['error'] = 'usuario inexistente';
            return $array;
        }

        $user['avatar'] = url('media/avatars/' . $user['avatar']);
        $user['cover'] = url('media/covers/' . $user['cover']);

        //recebe a informação se o usuário sou eu ou não
        $user['me'] = $me;

        //calculando a idade
        $dateFrom = new \DateTime($user['birthdate']);
        $dateTo = new \DateTime('today');
        $user['age'] = $dateFrom->diff($dateTo)->y;

        //buscando a quantidade de seguidores
        $user['followers'] = UserRelation::where('user_to', $user['id'])->count();

        //buscando a quantidade de usuarios que eu sigo
        $user['following'] = UserRelation::where('user_from', $user['id'])->count();

        //buscando a quantidade de fotos
        $user['photoCount'] = Post::where('id_user', $user['id'])
            ->where('type', 'photo')
            ->count();

        //veriica se eu sigo o usuario
        $hasRelation = UserRelation::where('user_from', Auth::user()->id)
            ->where('user_to', $user['id'])
            ->count();

        $user['isFollowing'] = $hasRelation > 0;

        //preencho o array com as informações do usuario
        $array['data'] = $user;
        return $array;
    }

    public function follow($id){

        if($id == Auth::user()->id){

            $array['error'] = 'usuario nao pode serguir a si mesmo';
            return $array;
        }

        $userExists = User::find($id);

        if(!$userExists){

            $array['error'] = 'usuario inexistente';
            return $array;
        }

        $relation = UserRelation::where('user_from', Auth::user()->id)
            ->where('user_to', $id)
            ->first();

        if($relation){

            //se eu já sigo o usuário eu vou parar de seguir
            $relation->delete();
        }
        else{

            //se não eu começo a seguir:
            $new = new UserRelation();
            $new->user_from = Auth::user()->id;
            $new->user_to = $id;
            $new->save();
        }

        $array['error'] = '';
        return $array;
    }

    public function followers($id){

        $user = User::find($id);

        //se o usuário nao existir
        if(!$user){
            $array['error'] = 'usuario inexistente';
            return $array;
        }

        $followers = UserRelation::where('user_to', $id)->get();
        $following = UserRelation::where('user_from', $id)->get();

        $array['followers'] = [];
        $array['following'] = [];

        foreach ($followers as $item){

            $newUser = User::find($item['user_from']);
            $array['followers'][] = [
                'id'=> $newUser['id'],
                'name' => $newUser['name'],
                'avatar' => url('/media/avatars/'. $newUser['avatar'])
            ];
        }

        foreach ($following as $item){

            $newUser = User::find($item['user_to']);
            $array['following'][] = [
                'id'=> $newUser['id'],
                'name' => $newUser['name'],
                'avatar' => url('/media/avatars/'. $newUser['avatar'])
            ];
        }

        return $array;
    }
}
