<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use App\Http\Resources\PostResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\PostDetailResource;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $posts = Post::all();
        // return response()->json(['data' => $posts]);
        return PostDetailResource::collection($posts->loadMissing(['writer:id,username,firstname,lastname','comments:id,post_id,comments_content,user_id']));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|max:255',
            'news_content' => 'required',
            'file' => 'mimes:jpg,jpeg,png|max:2048', 
        ]);

        $fileName = '';
        if ($request->file){
            $fileName = $this->generateRandomString().'.'.$request->file->extension();

            Storage::putFileAs(
                'public/images',
                $request->file,
                $fileName
            );
        }
        $request['image'] = $fileName;
        $request['author'] = Auth::user()->id;
        $post = Post::create($request->all());
        return new PostDetailResource($post->loadMissing('writer:id,username,firstname,lastname'));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $post = Post::with(['writer:id,username,firstname,lastname','comments:id,post_id,comments_content,user_id'])->findOrFail($id);

        return new PostDetailResource($post);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'title' => 'required|max:255',
            'news_content' => 'required',
            'file' => 'mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($request->file != null){
            $fileName = $this->generateRandomString().'.'.$request->file->extension();

            Storage::putFileAs(
                'public/images',
                $request->file,
                $fileName
            );
           
        }
        $request['image'] = $fileName;
        $request['author'] = Auth::user()->id;
        $post = Post::findOrFail($id);
        $post->update($request->all());

        return new PostDetailResource($post->loadMissing('writer:id,username,firstname,lastname'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $post = Post::findOrFail($id);
        $post->delete();

        return new PostDetailResource($post->loadMissing('writer:id,username,firstname,lastname'));
    }

    function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}
