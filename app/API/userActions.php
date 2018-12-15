<?php

namespace AnswerMe\Http\Controllers\API;

use Illuminate\Http\Request;
use AnswerMe\Http\Controllers\Controller;
use Carbon\Carbon;
use Validator;
use AnswerMe\User;
use AnswerMe\Action;
use AnswerMe\Post;
use AnswerMe\Comment;

class userActions extends Controller
{
  private $rules = [
    'apiToken'      => 'required|min:68|exists:users,apiToken,is_active,1',
    'userID'        => 'numeric|exists:users,id',
    'postID'        => 'numeric|exists:posts,id',
    'commentID'     => 'numeric|exists:comments,id',
    'action'        => 'required|in:follow,unfollow,hide_post,hide_suggest,block,unblock',
    ];

  private $messages = [
    'apiToken.required'     => 403,
    'apiToken.min'          => 405,
    'apiToken.exists'       => 400,

    'userID.required'       => 403,
    'userID.numeric'        => 403,
    'userID.exists'         => 403,

    'postID.required'       => 403,
    'postID.numeric'        => 403,
    'postID.exists'         => 403,

    'commentID.required'    => 403,
    'commentID.numeric'     => 403,
    'commentID.exists'      => 403,

    'action.required'       => 403,
    'action.in'             => 406,
  ];

  public function userActions(Request $request){
    $validator = Validator::make($request->all(), $this->rules, $this->messages);
    if($validator->fails()) {return response()->json(['status'=>(int)$validator->errors()->first()]);}

    if(!$request->userID && !$request->postID && !$request->commentID){ return response()->json(['status'=>403]); }

    $user = User::where('apiToken', $request->apiToken)->first();
//--------------------------------------------------------------
    if($request->action == 'follow'){
      Action::firstOrCreate([
        'type' => 'follow',
        'users_id' => $user->id,
        'target_users_id' => $request->userID
      ]);
    }
//--------------------------------------------------------------
    elseif($request->action == 'unfollow'){
      Action::where('type', 'follow')->where('users_id', $user->id)->where('target_users_id', $request->userID)->delete();
    }
//--------------------------------------------------------------
    elseif($request->action == 'hide_post'){
      if($request->userID){ $targetUser = $request->userID; }
      else{ $targetUser = Post::find($request->postID)->users_id; }
      $isGhost = Post::find($request->postID)->is_ghost;
      Action::firstOrCreate([
        'type' => 'hide_post',
        'is_ghost' => $isGhost,
        'users_id' => $user->id,
        'target_users_id' => $targetUser
      ]);
    }
//--------------------------------------------------------------
    elseif($request->action == 'hide_suggest'){
      Action::firstOrCreate([
        'type' => 'hide_suggest',
        'users_id' => $user->id,
        'target_users_id' => $request->userID
      ]);
    }
//--------------------------------------------------------------
    elseif($request->action == 'block'){
      if($request->userID){ $targetUser = $request->userID; }
      else{ $targetUser = Post::find($request->commentID)->users_id; }
      Action::firstOrCreate([
        'type' => 'block',
        'users_id' => $user->id,
        'target_users_id' => $request->userID
      ]);
    }
//--------------------------------------------------------------
    elseif($request->action == 'unblock'){
      Action::where('type', 'block')->where('users_id', $user->id)->where('target_users_id', $request->userID)->delete();
    }
//--------------------------------------------------------------
    return response()->json(['status'=>200]);
  }
}
