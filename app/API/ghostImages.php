<?php

namespace AnswerMe\Http\Controllers\API;

use Illuminate\Http\Request;
use AnswerMe\Http\Controllers\Controller;
use AnswerMe\GhostImage;

class ghostImages extends Controller
{
  public function ghostImages(Request $request){
    $images = GhostImage::all();
    return response()->json(['status'=>200, 'images'=>$images]);
  }
}
