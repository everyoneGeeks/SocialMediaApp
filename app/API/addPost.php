<?php

namespace AnswerMe\Http\Controllers\API;

use Illuminate\Http\Request;
use AnswerMe\Http\Controllers\Controller;
use Carbon\Carbon;
use Validator;
use AnswerMe\User;
use AnswerMe\Post;

class addPost extends Controller
{
  private $rules = [
    'apiToken'      => 'required|min:68|exists:users,apiToken,is_active,1',
    'isGhost'       => 'required|in:0,1',
    'categoryID'    => 'required|exists:categories,id',
    'title'         => 'required',
    'content'       => '',
    'image'         => 'image',
    ];

  private $messages = [
    'apiToken.required'     => 403,
    'apiToken.min'          => 405,
    'apiToken.exists'       => 400,

    'isGhost.required'      => 403,
    'isGhost.in'            => 406,

    'categoryID.required'   => 403,
    'categoryID.exists'     => 403,

    'title.required'        => 403,
    'image.image'           => 406,
  ];

  public function addPost(Request $request){
    $validator = Validator::make($request->all(), $this->rules, $this->messages);
    if($validator->fails()) {return response()->json(['status'=>(int)$validator->errors()->first()]);}
    if(!$request->content && !$request->image){ return response()->json(['status'=>403]); }

    $user = User::where('apiToken', $request->apiToken)->first();

    $post = new Post;
    $post->users_id = $user->id;
    $post->is_ghost = $request->isGhost;
    $post->title = $request->title;
    $post->content = $request->content;
    $post->categories_id = $request->categoryID;
    $post->created_at = Carbon::now();
    if($request->image) $this->SaveFile($post, 'image', 'image', 'images/post');
    $post->save();

    return response()->json(['status'=>200, 'postID'=>$post->id]);
  }
}
