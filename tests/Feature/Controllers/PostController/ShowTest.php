<?php

use App\Http\Resources\CommentResource;
use App\Http\Resources\PostResource;
use App\Models\Post;
use App\Models\Comment;

use function Pest\Laravel\get;

it('can show a post', function() {
    $post = Post::factory()->create();

    get($post->showRoute())
        ->assertComponent('Posts/Show');
});

it('passes a post to the view', function() {
    $post = Post::factory()->create();

    $post->load('user', 'topic');

    get($post->showRoute())
        ->assertHasResource('post', PostResource::make($post));
});

it('passes comments to the view', function() {
    $post = Post::factory()->create();
    $comments = Comment::factory(3)->for($post)->create();

    $comments->load('user');

    get($post->showRoute())
        ->assertHasPaginatedResource('comments', CommentResource::collection($comments->reverse()));
});

it('will redirect if the slug is incorrect', function() {
    $post = Post::factory()->create(['title' => 'This is a good title']);

    get(route('posts.show', [$post, 'foo-bar', 'page' => 2]))
        ->assertRedirect($post->showRoute(['page' => 2]));
});
