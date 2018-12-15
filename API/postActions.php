<?php

namespace AnswerMe\Http\Controllers\API;

use Illuminate\Http\Request;
use AnswerMe\Http\Controllers\Controller;
use Carbon\Carbon;
use Validator;
use AnswerMe\Post;
use AnswerMe\User;
use AnswerMe\Comment;
use AnswerMe\PostLike;
use AnswerMe\PostShare;
use AnswerMe\Action;


class postActions extends Controller
{
  private $rules = [
    'apiToken'      => 'required|min:68|exists:users,apiToken,is_active,1',
    'postID'        => 'required|exists:posts,id',
    'action'        => 'required|in:like,share,comment',
    'isGhost'       => 'in:0,1',
    'comment'       => ''
    ];

  private $messages = [
    'apiToken.required'     => 403,
    'apiToken.min'          => 405,
    'apiToken.exists'       => 400,

    'postID.required'       => 403,
    'postID.exists'         => 403,

    'action.required'       => 403,
    'action.in'             => 406,

    'isGhost.in'            => 403
  ];

  public function postActions(Request $request){
    $validator = Validator::make($request->all(), $this->rules, $this->messages);
    if($validator->fails()) {return response()->json(['status'=>(int)$validator->errors()->first()]);}

    $user = User::where('apiToken', $request->apiToken)->first();

    if($request->action == 'like'){
      //PostLike::firstOrCreate(['users_id'=>$user->id, 'posts_id'=>$request->postID]);
    $postlike = PostLike::where('users_id',$user->id)->where('posts_id',$request->postID)->first();
    if($postlike) PostLike::where('users_id',$user->id)->where('posts_id',$request->postID)->delete();
    else PostLike::firstOrCreate(['users_id'=>$user->id, 'posts_id'=>$request->postID]);

    }elseif($request->action == 'share'){
      PostShare::firstOrCreate(['users_id'=>$user->id, 'posts_id'=>$request->postID]);

    }elseif($request->action == 'comment'){
      if(!$request->comment){ return response()->json(['status'=>403]); }
      if($request->isGhost == NULL){ return response()->json(['status'=>403]); }

      $postOwner = Post::find($request->postID)->users_id;
      $isBlocked = Action::where('target_users_id', $user->id)->where('users_id', $postOwner)->where('type', 'block')->first();
      if($isBlocked){ return response()->json(['status'=>402]); }

      $comment = new Comment;
      $comment->content   = $request->comment;
      $comment->is_ghost  = $request->isGhost;
      $comment->users_id  = $user->id;
      $comment->posts_id  = $request->postID;
      $comment->created_at  = Carbon::now();
      $comment->save();
      return response()->json(['status'=>200, 'commentID'=>$comment->id]);
    }
    return response()->json(['status'=>200]);
  }
}
