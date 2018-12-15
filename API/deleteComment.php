<?php

namespace AnswerMe\Http\Controllers\API;

use Illuminate\Http\Request;
use AnswerMe\Http\Controllers\Controller;
use Validator;
use AnswerMe\User;

class deleteComment extends Controller
{
  private $rules = [
    'apiToken'      => 'required|min:68|exists:users,apiToken,is_active,1',
    'commentID'     => 'required|numeric|exists:posts,id',
    ];

  private $messages = [
    'apiToken.required'     => 403,
    'apiToken.min'          => 405,
    'apiToken.exists'       => 400,

    'commentID.required'    => 403,
    'commentID.numeric'     => 401,
    'commentID.exists'      => 401,
  ];

  public function deleteComment(Request $request){
    $validator = Validator::make($request->all(), $this->rules, $this->messages);
    if($validator->fails()) {return response()->json(['status'=>(int)$validator->errors()->first()]);}

    $user = User::where('apiToken', $request->apiToken)->first();
    $comment = $user->Comments()->where('id', $request->commentID)->delete();

    if(!$comment){ return response()->json(['status'=>402]); }

    return response()->json(['status'=>200]);
  }
}
