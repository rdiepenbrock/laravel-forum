<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\CommentResource;
use App\Http\Resources\PostResource;
use App\Http\Resources\TopicResource;
use App\Models\Post;
use App\Models\Topic;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Inertia\Response;
use Inertia\ResponseFactory;

class PostController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Post::class);

    }

    /**
     * Display a listing of the resource.
     */
    public function index(Topic $topic = null)
    {
        $posts = Post::with(['user', 'topic'])
            ->when($topic, fn (Builder $query) => $query->whereBelongsTo($topic))
            ->latest()
            ->latest('id')
            ->paginate();

        return inertia('Posts/Index', [
            'posts' => PostResource::collection($posts),
            'topics' => fn () => TopicResource::collection(Topic::all()),
            'selectedTopic' => fn () => $topic ? TopicResource::make($topic) : null,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return inertia('Posts/Create', [
            'topics' => fn () => TopicResource::collection(Topic::all()),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'min:10', 'max:120'],
            'topic_id' => ['required', 'exists:topics,id'],
            'body' => ['required', 'string', 'min:100', 'max:10000'],
        ]);

        $post = Post::create([
            ...$data,
            'user_id' => request()->user()->id,
        ]);

        return redirect($post->showRoute());
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Post $post)
    {
        if (!Str::contains($post->showRoute(), $request->path())) {
            return redirect($post->showRoute($request->query()), status: 301);
        }

        $post->load('user', 'topic');

        return inertia('Posts/Show', [
            'post' => fn() => PostResource::make($post),
            'comments' => fn() => CommentResource::collection($post->comments()
                ->with('user')
                ->latest()
                ->latest('id')
                ->paginate(10)),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Post $post): View
    {
        return view('posts.edit', compact('post'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Post $post): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
        ]);

        return redirect()
            ->route('posts.show', $post)
            ->with('status', 'Post updated successfully.');
    }
}
