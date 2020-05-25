<?php

namespace App\Http\Controllers\API;

use App\Post;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Repositories\PostRepository;
use App\Http\Resources\CommentResource;

class PostController extends Controller
{
    protected PostRepository $posts;

    public function __construct(PostRepository $posts)
    {
        $this->posts = $posts;
        $this->posts->warmCache();
    }

    /**
     * Display a listing of the posts.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $title = $request->input('title');

        if ($title !== null) {
            $posts = $this->posts->searchByTitle($title);
        } else {
            $posts = $this->posts->all();
        }

        return PostResource::collection($posts->paginate(10));
    }

    /**
     * Display the specified post.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Post $post)
    {
        return PostResource::make($post);
    }

    /**
     * Display a listing of the post's comments.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function comments(Post $post)
    {
        return CommentResource::make($post->comments()->paginate(10));
    }
}
