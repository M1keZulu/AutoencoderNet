<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    public function store(Request $request, Image $image)
    {
        $request->validate([
            'id' => 'required|numeric',
            'content' => 'required',
        ]);

        $comment = new Comment;
        $comment->content = $request->content;
        $comment->user_id = Auth::user()->id;
        $comment->image_id = $request->id;
        $comment->save();

        return redirect()->route('comment.show', ['id' => $request->id])->with('success', 'Comment added successfully');
    }
}
