<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\UserRelation;
use App\Models\Post;
use App\Models\PostComment;
use App\Models\PostLike;
use Image;

class FeedController extends Controller
{

    private $loggedUser;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function create(Request $request){

        //tipos de imagens permitadas
        $imageTypes = ['image/jpeg', 'image/jpg', 'image/png'];

        $type = $request->input('type');
        $body = $request->input('body');
        $photo = $request->file('photo');

        if(!$type){
            $array['error'] = 'dados incorretos';
            return $array;
        }

        switch ($type){
            case 'photo':
                if(!$photo){
                    $array['error'] = 'arquivo nao enviado';
                    return $array;
                }

                //verifica se é um tipo de imagem permitido
                if(!in_array($photo->getClientMimeType(), $imageTypes)){
                    $array['error'] = 'tipo de imagem nao permitido';
                    return $array;
                }

                //cria um nome aleatorio para o arquivo
                $filename = md5(time().rand(0,9999)) . '.jpg';

                //caminho da pasta de destino
                $destPath = public_path('/media/uploads');

                //criando e salvando a imagem
                $img = Image::make($photo->path())
                    ->resize(800, null, function ($constraint){
                        $constraint->aspectRatio(); //faz com que a imagem manteha a proporçao de altura x largura
                    })
                    ->save($destPath . '/' . $filename);

                $body = $filename;


                break;

            case 'text':
                if(!$body){
                    $array['error'] = 'texto nao enviado';
                    return $array;
                }
            break;

            default:
                $array['error'] = 'tipo incorreto';
                return $array;
        }

        if($body){

            //criando e salvando um novo post
            $newPost = new Post();
            $newPost->id_user = Auth::user()->id;
            $newPost->type = $type;
            $newPost->created_at = date('Y-m-d H:i:s');
            $newPost->body = $body;
            $newPost->save();
        }

        $array['error'] = '';
        return $array;
    }

    public function read(Request $request){

        //se vier algo será 1 se não será 0
        $page = intval($request->input('page'));

        //configurando 2 itens por página de feed
        $perPage = 2;

        //1 - Recuperar a lista de usuários que eu sigo
        //array de usuarios
        $users = [];

        //recupera a lista de usuários que eu sigo incluindo EU
        $userList = UserRelation::where('user_from', Auth::user()->id)->get();

        //adiciona os usuários que eu sigo ao array
        foreach ($userList as $item) {
            $users[] = $item['user_to'];
        }

        //me incluindo no array de usuarios
        $users[] = Auth::user()->id;

        //2 - Recuperar a lista de post dos usuários que eu sigo
        $postList = Post::whereIn('id_user', $users)
            ->orderBy('created_at', 'desc')
            ->offset($page * $perPage)
            ->limit($perPage)
            ->get();

        //contando a quantidade de posts
        $total = Post::whereIn('id_user', $users)->count();
        //contando a quantidade de paginas
        $pageCount = ceil($total / $perPage);

        //3 - Recuperar as demais informações

        $posts = $this->_postListToObject($postList, Auth::user()->id);

        $array['posts'] = $posts;
        $array['pageCount'] = $pageCount;
        $array['currentPage'] = $page;

        $array['error'] = '';
        return $array;
    }

    public function _postListToObject($postList, $idUser){

        foreach ($postList as $postKey => $postItem){

            //verifico se o post é meu
            if($postItem['id_user'] == $idUser){
                $postList[$postKey]['mine'] = true;
            }
            else{
                $postList[$postKey]['mine'] = false;
            }

            //recuperar informações do usuario
            $userInfo = User::find($postItem['id_user']);
            $userInfo['avatar'] = url('media/avatars/' . $userInfo['avatar']);
            $userInfo['cover'] = url('media/covers/' . $userInfo['cover']);
            $postList[$postKey]['user'] = $userInfo;

            //recupera as informaçòes de likes
            $likes = PostLike::where('id_post', $postItem['id'])->get()->count();
            $postList[$postKey]['likeCount'] = $likes;

            $isLiked = PostLike::where('id_post', $postItem['id'])
                ->where('id_user', $idUser)
                ->count();

            $postList[$postKey]['liked'] = $isLiked > 0;

            //recupera as informaçòes de comentarios
            $comments = PostComment::where('id_post', $postItem['id'])->get();

            foreach ($comments as $commentKey => $comment){

                $user = User::find($comment['id_user']);
                $user['avatar'] = url('media/avatars/' . $user['avatar']);
                $user['cover'] = url('media/covers/' . $user['cover']);
                $comments[$commentKey]['user'] = $user;
            }

            $postList[$postKey]['comments'] = $comments;

        }

        return $postList;
    }

}
