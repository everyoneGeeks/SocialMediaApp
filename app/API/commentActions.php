<?php

namespace AnswerMe\Http\Controllers\API;

use Illuminate\Http\Request;
use AnswerMe\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Validator;
use AnswerMe\User;
use AnswerMe\CommentLike;

class commentActions extends Controller
{
  private $rules = [
    'apiToken'      => 'required|min:68|exists:users,apiToken,is_active,1',
    'commentID'     => 'required|exists:comments,id',
    'action'        => 'required|in:like,dislike',
    ];

  private $messages = [
    'apiToken.required'     => 403,
    'apiToken.min'          => 405,
    'apiToken.exists'       => 400,

    'commentID.required'    => 403,
    'commentID.exists'      => 403,

    'action.required'       => 403,
    'action.in'             => 406,
  ];

  public function commentActions(Request $request){
    $validator = Validator::make($request->all(), $this->rules, $this->messages);
    if($validator->fails()) {return response()->json(['status'=>(int)$validator->errors()->first()]);}

    $user = User::where('apiToken', $request->apiToken)->first();

    $action = CommentLike::where('users_id', $user->id)
                         ->where('comments_id', $request->commentID)
                         ->first();

    if($action){
      CommentLike::where('users_id', $user->id)
                 ->where('comments_id', $request->commentID)
                 ->update(['type'=>$request->action]);
    }else{
      CommentLike::create(['users_id' => $user->id, 'comments_id'=>$request->commentID, 'type'=>$request->action]);
    }

    return response()->json(['status'=>200]);

  }
}
