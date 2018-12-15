<?php

namespace AnswerMe\Http\Controllers\API;

use Illuminate\Http\Request;
use AnswerMe\Http\Controllers\Controller;
use Validator;
use AnswerMe\User;
use AnswerMe\Post;

class userPosts extends Controller
{
  private $rules = [
    'apiToken'      => 'required|min:68|exists:users,apiToken,is_active,1',
    'userID'        => 'numeric|exists:users,id',
    'isGhost'       => 'in:0,1',
    'page'          => 'numeric|min:1'
    ];

  private $messages = [
    'apiToken.required'     => 403,
    'apiToken.min'          => 405,
    'apiToken.exists'       => 400,

    'userID.numeric'        => 402,
    'userID.exists'         => 402,

    'isGhost.in'            => 404,

    'page.numeric'          => 404,
    'page.min'              => 404,
  ];

  public function userPosts(Request $request){
    $validator = Validator::make($request->all(), $this->rules, $this->messages);
    if($validator->fails()) {return response()->json(['status'=>(int)$validator->errors()->first()]);}

    if($request->userID){
      $ownerID = $request->userID;
      $request->isGhost = 0;
    }else{ $ownerID = User::where('apiToken', $request->apiToken)->first()->id; }

    $isGhost = (string)$request->isGhost;

    $posts = Post::select('id', 'title', 'image', 'content')
                 ->where('users_id', $ownerID)->where('is_ghost', $isGhost)
                 ->paginate(20);
    foreach($posts as $post){
    $post['image'] = asset($post['image']);
    $post['photo'] = asset($post['photo']);
    }

    if($posts->items() == NULL) return response()->json(['status'=>300]);
    return response()->json(['status'=>200, 'posts'=>$posts->items()]);
  }
}
