<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\PostComment;
use App\Models\PostLike;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{
    private $loggedUser;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function like($id){

        //verificar se o post existe

        $postExists = Post::find($id);

        //se o post não existe
        if(!$postExists){
            $array['error'] = "post inexistente";
            return $array;
        }

        //verifico se eu já curti o post
        $isLiked = PostLike::where('id_post', $id)
            ->where('id_user', Auth::user()->id)
            ->count();

        //se eu dei like no post
        if($isLiked > 0){

            //removo o like
            $pl = PostLike::where('id_post', $id)
                ->where('id_user', Auth::user()->id)
                ->first();

            $pl->delete();

            $array['liked'] = false;
        }
        else{

            //se não, eu dou um like
            $postLike = new PostLike();
            $postLike->id_post = $id;
            $postLike->id_user = Auth::user()->id;
            $postLike->created_at = date('Y-m-d H:i:s');
            $postLike->save();

            $array['liked'] = true;
        }

        //verifico a quantidade de likes do post
        $likeCount = PostLike::where('id_post', $id)->count();
        $array['likeCount'] = $likeCount;

        return $array;
    }

    public function comment(Request $request, $id){

        $txt = $request->input('txt');

        if(trim($txt) == ''){
            $array['error'] = "comentario vazio";
            return $array;
        }

        $postExists = Post::find($id);

        if(!$postExists){
            $array['error'] = "post inexistente";
            return $array;
        }

        $newComment = new PostComment();
        $newComment->id_post = $id;
        $newComment->id_user = Auth::user()->id;
        $newComment->created_at = date('Y-m-d H:i:s');
        $newComment->body = $txt;
        $newComment->save();

        $array['error'] = "";
        return $array;
    }
}
