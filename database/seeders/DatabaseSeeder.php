<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Comment;
use App\Models\Like;
use App\Models\Post;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(TopicSeeder::class);
        $topics = Topic::all();

        $users = User::factory(15)->create();

        $posts = Post::factory(75)
            ->withFixture()
            ->has(Comment::factory(20)->recycle($users))
            ->recycle([$users, $topics])
            ->create();

        $user = User::factory()
            ->has(Post::factory(30)->recycle($topics)->withFixture())
            ->has(Comment::factory(50)->recycle($posts))
            ->has(Like::factory()->forEachSequence(
                ...$posts->random(25)->map(fn (Post $post) => ['likeable_id' => $post]),
            ))
            ->create([
                'name' => 'Richard Diepenbrock',
                'email' => 'rjdwebsites@gmail.com',
                'password' => bcrypt('rjdWebs!tes2023'),
            ]);
    }
}
