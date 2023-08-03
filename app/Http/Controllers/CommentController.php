<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Illuminate\Http\Request;
use App\Http\Resources\CommentResource;

class CommentController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'post_id' => 'required|exists:posts,id',
            'comments_content' => 'required',
        ]);
        $request['user_id'] = auth()->user()->id;

        $comment = Comment::create($request->all());

        return CommentResource::make($comment->loadMissing(['commentator:id,username,firstname,lastname']));
    }

    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'comments_content' => 'required',
        ]);

        $comment = Comment::findOrFail($id);
        $comment->update($request->all());

        return CommentResource::make($comment->loadMissing(['commentator:id,username,firstname,lastname']));
    }   

    public function destroy(string $id)
    {
        $comment = Comment::findOrFail($id);
        $comment->delete();

        return response()->json(['message' => 'Success'], 200);
    }
}
