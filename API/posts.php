<?php

namespace AnswerMe\Http\Controllers\API;

use Illuminate\Http\Request;
use AnswerMe\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Validator;
use AnswerMe\Post;
use AnswerMe\User;
use AnswerMe\Action;
use AnswerMe\PostLike;
use AnswerMe\PostShare;

class posts extends Controller
{
  private $rules = [
    'apiToken'      => 'required|min:68|exists:users,apiToken,is_active,1',
    'filter'        => 'in:like,view,latest',
    'lang'          => 'required|in:ar,en',
    'page'          => 'numeric|min:1'
    ];

  private $messages = [
    'apiToken.required'     => 403,
    'apiToken.min'          => 405,
    'apiToken.exists'       => 400,

    'filter.required'       => 403,
    'filter.in'             => 407,

    'lang.required'         => 403,
    'lang.in'               => 406,

    'page.numeric'          => 404,
    'page.min'              => 404,
  ];

  public function posts(Request $request){
    $validator = Validator::make($request->all(), $this->rules, $this->messages);
    if($validator->fails()) {return response()->json(['status'=>(int)$validator->errors()->first()]);}

    $user = User::where('apiToken', $request->apiToken)->first();
//-------------------------------------------------------------------------
  $user = User::where('apiToken',$request->apiToken)->first();
    if($request->filter){
      if($request->filter == 'like'){ $filter = 'noLikes'; }
      elseif($request->filter == 'view'){ $filter = 'posts.no_views'; }
      elseif($request->filter == 'latest'){ $filter = 'posts.created_at'; }
      $posts = Post::select(
          'posts.id AS postID', 'posts.title', 'posts.image AS postImage', 'posts.content', 'posts.created_at AS time', 'posts.is_ghost',
          'users.id AS userID', 'users.name AS username', 'users.photo', 'ghostName',
          'ghost_images.imgUrl AS gImage',
          DB::raw('COUNT(posts_likes.users_id) AS noLikes')
        )
      ->leftJoin('users', 'posts.users_id', '=', 'users.id')
      ->leftjoin('ghost_images', 'users.ghost_images_id', '=', 'ghost_images.id')
      ->leftjoin('posts_likes', 'posts.id', '=', 'posts_likes.posts_id')
      ->groupBy('posts.id')
      ->orderBy($filter, 'desc')
      ->paginate(20);
	  
      for($i=0; $i<$posts->count(); $i++){
        $post = Post::find($posts[$i]['postID']);
        $posts[$i]['time'] = Carbon::parse($posts[$i]['time'])->timestamp;
        $posts[$i]['noShare'] = $post->Shares->count();
        $posts[$i]['noComments'] = $post->Comments->count();
        $posts[$i]['postImage'] = asset($posts[$i]['postImage']);
        if($posts[$i]['is_ghost'] == 1){
          $posts[$i]['username'] = $posts[$i]['ghostName'];
          $posts[$i]['photo'] = $posts[$i]['gImage'];
        }
      $posts[$i]['photo'] = asset($posts[$i]['photo']);
      $PL = PostLike::where('users_id',$user->id)->where('posts_id',$posts[$i]['postID'])->first();
      $PS = PostShare::where('users_id',$user->id)->where('posts_id',$posts[$i]['postID'])->first();
      $posts[$i]['isOwner'] = $user->id == $posts[$i]['userID'] ? 1 : 0;
      if($PL)
      $posts[$i]['isLiked'] = 1;
      else
      $posts[$i]['isLiked'] = 0;
      if($PS)
      $posts[$i]['isShared'] = 1;
      else
      $posts[$i]['isShared'] = 0;
        unset($posts[$i]['ghostName']);
        unset($posts[$i]['gImage']);
       // unset($posts[$i]['is_ghost']);
      }
    }
//-------------------------------------------------------------------------
    else{
      $followings = Action::select('target_users_id AS id')->where('users_id', $user->id)->where('type', 'follow')->get();
      $followingsArray = NULL;
      if($followings->count() ==0 ){ return response()->json(['status'=>300]);  }
      foreach ($followings as $element) { $followingsArray[] = $element->id;}
      $posts = Post::select(
          'posts.id AS postID', 'posts.title', 'posts.image AS postImage', 'posts.content', 'posts.created_at AS time', 'posts.is_ghost',
          'users.id AS userID', 'users.name AS username', 'users.photo', 'ghostName',
          'ghost_images.imgUrl AS gImage'
        )
      ->leftJoin('users', 'posts.users_id', '=', 'users.id')
      ->leftjoin('ghost_images', 'users.ghost_images_id', '=', 'ghost_images.id')
      ->whereIn('posts.users_id', $followingsArray)
      ->orderBy('posts.created_at', 'desc')
      ->paginate(20);

      for($i=0; $i<$posts->count(); $i++){
        $post = Post::find($posts[$i]['postID']);
        $posts[$i]['time'] = Carbon::parse($posts[$i]['time'])->timestamp;
        $posts[$i]['noShare'] = $post->Shares->count();
        $posts[$i]['noLikes'] = $post->Likes->count();
      $posts[$i]['postImage'] = asset($posts[$i]['postImage']);
        $posts[$i]['noComments'] = $post->Comments->count();
        if($posts[$i]['is_ghost'] == 1){
          $posts[$i]['username'] = $posts[$i]['ghostName'];
          $posts[$i]['photo'] = $posts[$i]['gImage'];
        }
      $posts[$i]['photo'] = asset($posts[$i]['photo']);
      $PL = PostLike::where('users_id',$user->id)->where('posts_id',$posts[$i]['postID'])->first();
      $PS = PostShare::where('users_id',$user->id)->where('posts_id',$posts[$i]['postID'])->first();
      $posts[$i]['isOwner'] = $user->id == $posts[$i]['userID'] ? 1 : 0;
      if($PL)
      $posts[$i]['isLiked'] = 1;
      else
      $posts[$i]['isLiked'] = 0;
      if($PS)
      $posts[$i]['isShared'] = 1;
      else
      $posts[$i]['isShared'] = 0;
        unset($posts[$i]['ghostName']);
        unset($posts[$i]['gImage']);
       // unset($posts[$i]['is_ghost']);
      }

    }
//-------------------------------------------------------------------------
    if(Count($posts->items()) == 0) return response()->json(['status'=>300]);
    return response()->json(['status'=>200, 'posts'=>$posts->items()]);
  }
}
