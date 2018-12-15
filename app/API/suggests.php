<?php

namespace AnswerMe\Http\Controllers\API;

use Illuminate\Http\Request;
use AnswerMe\Http\Controllers\Controller;
use Validator;
use AnswerMe\Action;
use AnswerMe\User;

class suggests extends Controller
{
  private $rules = [
    'apiToken'      => 'required|min:68|exists:users,apiToken',
    'page'          => 'numeric|min:1'
    ];

  private $messages = [
    'apiToken.required'     => 403,
    'apiToken.min'          => 405,
    'apiToken.exists'       => 400,

    'page.numeric'          => 403,
    'page.min'              => 403,
  ];

  public function suggests(Request $request){
    $validator = Validator::make($request->all(), $this->rules, $this->messages);
    if($validator->fails()) {return response()->json(['status'=>(int)$validator->errors()->first()]);}

    $user = User::where('apiToken', $request->apiToken)->first();
  ////////////////////////////////////////////////////////////////////////
  if(isset($request->keyword) && !empty($request->keyword)){
  if($user->is_active == 0){ return response()->json(['status'=>401]); }

    $users = User::select('id AS userID', 'name', 'photo')
                 ->where('name', 'LIKE', '%'.$request->keyword.'%')
                 ->orderBy('name')
                 ->paginate(20);
  
  foreach($users as $user){
    $user['photo'] = asset($user['photo']);}

    if($users->items() == NULL) return response()->json(['status'=>300]);
    return response()->json(['status'=>200, 'users'=>$users->items() ]);
  }
  ///////////////////////////////////////////////////////////////////////
    $usernotfollowings = Action::select('target_users_id AS id')->where('users_id', $user->id)->get();
    if($usernotfollowings->count() == 0 )
    {
    $usernotfollowingsI = Action::select('target_users_id AS id')->where('type', 'hide_suggest')->orWhere('type','block')->distinct()->get();
    
    $userfollowingsArrayI = NULL;
    foreach ($usernotfollowingsI as $element) { $userfollowingsArrayI[] = $element->id;}
    
    $users = User::select('id AS userID', 'name', 'photo')->whereNotIn('id', $userfollowingsArrayI)->paginate(20);
    
    //$users = $users->toArray();
    foreach($users as $user){
    $user['photo'] = asset($user['photo']);}
    //$users = collect($users);
    return response()->json(['status'=>200, 'users'=>$users->items()]);
    }
    // Get user followings
    $userfollowings = Action::select('target_users_id AS id')->where('users_id', $user->id)->where('type', 'follow')->get();
    $userfollowingsArray = NULL;
    if($userfollowings->count() ==0 ){return response()->json(['status'=>300]);  }
    foreach ($userfollowings as $element) { $userfollowingsArray[] = $element->id;}
    // Get friends followings
    $friendsfollowings = Action::select('target_users_id AS id')->whereIn('users_id', $userfollowingsArray)->where('type', 'follow')->distinct()->get();
    $friendsfollowingsArray = NULL;
    if($friendsfollowings->count() == 0 ){ return response()->json(['status'=>300]);  }
    foreach ($friendsfollowings as $element) { $friendsfollowingsArray[] = $element->id;}
    //Get Hidden Suggestions
    $hiddenSuggestions = Action::select('target_users_id AS id')->where('users_id', $user->id)->where('type', 'hide_suggest')->get();
    $hiddenSuggestionsArray []= $user->id;
    foreach ($hiddenSuggestions as $element) { $hiddenSuggestionsArray[] = $element->id;}
    //Get Suggestions
    $users = User::select('id AS userID', 'name', 'photo')->whereIn('id', $friendsfollowingsArray)->whereNotIn('id', $hiddenSuggestionsArray)->paginate(20);
    //$users = $users->toArray();
    foreach($users as $user){$user['photo'] = asset($user['photo']);}
    //$users = collect($users);
    if(Count($users->items()) == 0) {return response()->json(['status'=>300]);}
    return response()->json(['status'=>200, 'users'=>$users->items()]);
  }
}
