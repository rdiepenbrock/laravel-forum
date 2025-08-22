<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Database\Factories\CommentFactory;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $users = User::factory(10)->create();

        $posts = Post::factory(50)
            ->has(Comment::factory(15)->recycle($users))
            ->recycle($users)
            ->create();

         $user = User::factory()
             ->has(Post::factory(25))
             ->has(Comment::factory(50)->recycle($posts))
             ->create([
             'name' => 'Richard Diepenbrock',
             'email' => 'rjdwebsites@gmail.com',
             'password' => bcrypt('rjdWebs!tes2023'),
         ]);
    }
}
