<?php

namespace AnswerMe\Http\Controllers\API;

use Illuminate\Http\Request;
use AnswerMe\Http\Controllers\Controller;
use Validator;
use Carbon\Carbon;
use AnswerMe\Post;

class postInfo extends Controller
{
  private $rules = [
    'apiToken'      => 'required|min:68|exists:users,apiToken,is_active,1',
    'postID'        => 'required|numeric|exists:posts,id',
    'lang'          => 'required|in:ar,en,ur',
    ];

  private $messages = [
    'apiToken.required'     => 403,
    'apiToken.min'          => 405,
    'apiToken.exists'       => 400,

    'postID.required'       => 403,
    'postID.numeric'        => 402,
    'postID.exists'         => 402,

    'lang.required'         => 403,
    'lang.in'               => 406,
  ];

  public function postInfo(Request $request){
    $validator = Validator::make($request->all(), $this->rules, $this->messages);
    if($validator->fails()) {return response()->json(['status'=>(int)$validator->errors()->first()]);}

    $name = 'name_' .$request->lang;
    $post = Post::select('title', 'image', 'content', 'created_at AS time', $name.' AS category')
                ->leftJoin('categories', 'posts.categories_id', '=', 'categories.id')
                ->where('posts.id', $request->postID)
                ->first();
  $post['image'] = asset($post['image']);

    $postObject = Post::find($request->postID);
    $post->time = Carbon::parse($post->time)->timestamp;
    $post->noShares = $postObject->Shares->Count();
    $post->noLikes = $postObject->Likes->Count();
    $post->noComments = $postObject->Comments->Count();

    return response()->json(['status'=>200, 'post'=>$post]);
  }
}
