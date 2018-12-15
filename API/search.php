<?php

namespace AnswerMe\Http\Controllers\API;

use Illuminate\Http\Request;
use AnswerMe\Http\Controllers\Controller;
use Carbon\Carbon;
use Validator;
use AnswerMe\User;
use AnswerMe\Post;
use AnswerMe\Category;

class search extends Controller
{
  private $rules = [
    'apiToken'      => 'required|min:68|exists:users,apiToken,is_active,1',
    'keyword'       => 'required',
    'type'          => 'required|in:posts,sections',
    'page'          => 'numeric|min:1'
  ];

  private $messages = [
    'apiToken.required'     => 403,
    'apiToken.min'          => 405,
    'apiToken.exists'       => 400,

    'keyword.required'      => 403,
	
  	'type.required'         => 403,
    'type.type'             => 403,
    'type.in'               => 406,

    'page.numeric'          => 404,
    'page.min'              => 404,
  ];

  public function search(Request $request){
    $validator = Validator::make($request->all(), $this->rules, $this->messages);
    if($validator->fails()) {return response()->json(['status'=>(int)$validator->errors()->first()]);}

    if($request->type == 'sections'){
      $sections = Category::select('id AS categoryID', 'name_en', 'name_ar', 'hex_color AS HexColor')
                          ->where('name_en', 'LIKE', '%'.$request->keyword.'%')
                          ->orWhere('name_ar', 'LIKE', '%'.$request->keyword.'%')
                          ->paginate(20);
      if($sections->items() == NULL) return response()->json(['status'=>300]);
      return response()->json(['status'=>200, 'sections'=>$sections->items()]);

    }else{
      $posts = Post::select(
          'posts.id AS postID', 'posts.title', 'posts.image AS postImage', 'posts.content', 'posts.created_at AS time', 'posts.is_ghost',
          'users.id AS userID', 'users.name AS username', 'users.photo', 'ghostName',
          'ghost_images.imgUrl AS gImage'
        )
      ->leftJoin('users', 'posts.users_id', '=', 'users.id')
      ->leftjoin('ghost_images', 'users.ghost_images_id', '=', 'ghost_images.id')
      ->where('posts.title', 'LIKE', '%'.$request->keyword.'%')
      ->orWhere('posts.content', 'LIKE', '%'.$request->keyword.'%')
      ->orderBy('posts.created_at', 'desc')
      ->paginate(20);

      for($i=0; $i<$posts->count(); $i++){
        $posts[$i]['time'] = Carbon::parse($posts[$i]['time'])->timestamp;
        if($posts[$i]['is_ghost'] == 1){
          
          $posts[$i]['username'] = $posts[$i]['ghostName'];
          $posts[$i]['photo'] = asset($posts[$i]['gImage']);

        }
        unset($posts[$i]['ghostName']);
        unset($posts[$i]['gImage']);
        unset($posts[$i]['is_ghost']);
      }
    foreach ($posts as $post) {
    $post->postImage = asset($post->postImage);
    $post->photo = asset($post->photo);
    }

      if($posts->items() == NULL) return response()->json(['status'=>300]);
      return response()->json(['status'=>200, 'posts'=>$posts->items()]);
    }

  }

}
