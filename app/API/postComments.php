<?php

namespace AnswerMe\Http\Controllers\API;

use Illuminate\Http\Request;
use AnswerMe\Http\Controllers\Controller;
use Carbon\Carbon;
use Validator;
use AnswerMe\Comment;
use AnswerMe\User;
use AnswerMe\CommentLike;

class postComments extends Controller
{
  private $rules = [
    'apiToken'      => 'required|min:68|exists:users,apiToken,is_active,1',
    'postID'        => 'required|numeric|exists:posts,id',
    'page'          => 'numeric|min:1'
    ];

  private $messages = [
    'apiToken.required'     => 403,
    'apiToken.min'          => 405,
    'apiToken.exists'       => 400,

    'postID.required'       => 403,
    'postID.numeric'        => 402,
    'postID.exists'         => 402,

    'page.numeric'          => 404,
    'page.min'              => 404,
  ];

  public function postComments(Request $request){
    $validator = Validator::make($request->all(), $this->rules, $this->messages);
    if($validator->fails()) {return response()->json(['status'=>(int)$validator->errors()->first()]);}

    $comments = Comment::select('comments.id AS commentID',
                                'comments.content',
                                'comments.created_at AS time',
                                'comments.is_ghost AS ghost',

                                'users.id AS userID',
                                'users.name AS userName',
                                'users.photo AS userImg',
                                'users.ghostName',
                                'ghost_images.imgUrl AS ghostImag')
                       ->leftjoin('users', 'users_id', '=', 'users.id')
                       ->leftjoin('ghost_images', 'ghost_images_id', '=', 'ghost_images.id')
                       ->where('posts_id', $request->postID)->get()->toArray();

  	$user = User::where('apiToken',$request->apiToken)->first();
    for($i = 0; $i < count($comments); $i++){
      $comments[$i]['time'] = Carbon::parse($comments[$i]['time'])->timestamp;
      $comment = Comment::find($comments[$i]['commentID']);
      $comments[$i]['noLikes'] = $comment->Likes->count();
      $comments[$i]['noUnlike'] = $comment->Dislikes->count();
      $comments[$i]['userImg'] = asset($comments[$i]['userImg']);
      $ss = CommentLike::select('type')->where('users_id',$user->id)->where('comments_id',$comments[$i]['commentID'])->first();
      $comments[$i]['isOwner'] = $user->id == $comments[$i]['userID'] ? 1 : 0;
      if($ss['type'] === 'like')
      $comments[$i]['likeStatus'] = 1;
      elseif($ss['type'] == 'dislike')
      $comments[$i]['likeStatus'] = 2;
      else
      $comments[$i]['likeStatus'] = 0;

      if($comments[$i]['ghost'] == 0){
        unset($comments[$i]['ghostName']);
        unset($comments[$i]['ghostImag']);
      }else{
        $comments[$i]['userName'] = $comments[$i]['ghostName'];
        $comments[$i]['userImg'] = $comments[$i]['ghostImag'];
        unset($comments[$i]['userID']);
        unset($comments[$i]['ghostImag']);
        unset($comments[$i]['ghostName']);
      }
    }//EOfor
	
    $commentsCl = collect($comments)->forPage($request->page, 20); 

    if(Count($commentsCl) == 0) return response()->json(['status'=>300]);
    return response()->json(['status'=>200, 'comments'=>$commentsCl]);
  }
}
/*
commentID
content
time

noLikes
noUnlike

userID
userName
userImg
ghostImagID
*/
