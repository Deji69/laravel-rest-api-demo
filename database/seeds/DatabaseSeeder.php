<?php

use App\Post;
use App\User;
use App\Comment;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // $this->call(UserSeeder::class);

        $ids = [];
        $userMinId = User::max('id') ?: 1;
        $postMinId = Post::max('id') ?: 1;
        $commentMinId = Comment::max('id') ?: 1;

        for ($i = 0; $i < 20; ++$i) {
            $ids[] = ['id' => $i + $userMinId];
        }

        factory(User::class)->createMany($ids)->each(function (User $user) use (&$postMinId, &$commentMinId) {
            factory(Post::class, 5)->state('nouser')->make()->each(
                function (Post $post, int $idx) use ($user, &$postMinId, &$commentMinId) {
                    $post->id = $postMinId + $idx;
                    $post->user_id = $user->id;
                    $post->save();

                    factory(Comment::class, 5)->state('nopost')->make()->each(
                        function (Comment $comment, int $idx) use ($post, &$commentMinId) {
                            $comment->id = $commentMinId + $idx;
                            $comment->post_id = $post->id;
                            $comment->save();
                        }
                    );

                    $commentMinId += 5;
                }
            );

            $postMinId += 5;
        });
    }
}
