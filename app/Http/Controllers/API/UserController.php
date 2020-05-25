<?php

namespace App\Http\Controllers\API;

use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Http\Resources\UserResource;
use App\Repositories\UserRepository;

class UserController extends Controller
{
    protected UserRepository $users;

    public function __construct(UserRepository $users)
    {
        $this->users = $users;
        $this->users->warmCache();
    }

    /**
     * Display a listing of the users.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($email = $request->input('email')) {
            $user = $this->users->findByEmail($email);
            return UserResource::make($user);
        } else {
            $users = $this->users->all();
        }

        return UserResource::collection($users->paginate(10));
    }

    /**
     * Display the specified user.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        return UserResource::make($user);
    }

    /**
     * Display a listing of the user's posts.
     *
     * @param User $user
     * @return \Illuminate\Http\Response
     */
    public function posts(User $user)
    {
        return PostResource::collection($user->posts()->paginate(10));
    }
}
