<?php

use App\Http\Resources\CommentResource;
use App\Http\Resources\PostResource;
use App\Models\Post;
use App\Models\Comment;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\put;

it('it requires authentication', function() {
    put(route('comments.update', Comment::factory()->create()))
        ->assertRedirect(route('login'));
});

it('can update a comment', function () {
    $comment = Comment::factory()->create(['body' => 'This is the old comment']);
    $newComment = 'This is the new comment';

    actingAs($comment->user)
        ->put(route('comments.update', $comment), ['body' => $newComment]);

    $this->assertDatabaseHas('comments', [
        'id' => $comment->id,
        'body' => $newComment,
    ]);
});

it('redirects to the post show page', function () {
    $comment = Comment::factory()->create();

    actingAs($comment->user)
        ->put(route('comments.update', $comment), ['body' => 'This is the new comment'])
        ->assertRedirect($comment->post->showRoute());
});

it('it redirects to the correct page of comments', function () {
    $comment = Comment::factory()->create();

    actingAs($comment->user)
        ->put(route('comments.update', ['comment' => $comment, 'page' => 2]), ['body' => 'This is the new comment'])
        ->assertRedirect($comment->post->showRoute(['page' => 2]));
});

it('cannot update a comment from another user', function () {
    $comment = Comment::factory()->create();

    actingAs(User::factory()->create())
        ->put(route('comments.update', ['comment' => $comment]), ['body' => 'This is the new comment'])
        ->assertForbidden();
});

it('requires a valid body', function ($body) {
    $comment = Comment::factory()->create();

    actingAs($comment->user)
        ->put(route('comments.update', ['comment' => $comment]), ['body' => $body])
        ->assertInvalid('body');
})->with([
    null,
    true,
    1,
    1.5,
    str_repeat('a', 2501)
]);


