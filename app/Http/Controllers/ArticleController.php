<?php

namespace App\Http\Controllers;

use App\Enums\AlertLevelEnum;
use App\Models\Article;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ArticleController extends Controller
{
    /**
     * Create the controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->authorizeResource(Article::class, 'article');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('article.index', [
            'articles' => Article::orderByDesc('published_at')
                ->when(! auth()->user()->isAdmin(), function ($query) {
                    return $query->where('online', true);
                })
                ->paginate(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('article.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $request['online'] = filter_var($request['online'], FILTER_VALIDATE_BOOLEAN);
        $request->validate([
            'title' => ['required', 'string', 'max:255', 'unique:articles'],
            'body' => ['required', 'string'],
            'online' => ['boolean'],
            'summary' => ['nullable', 'string', 'max:350'],
            'image' => ['required_if:online,true', 'mimes:png,jpg,jpeg,gif', 'max:10000', 'dimensions:max_width=2560,max_height=1600'],
        ]);

        $article = new Article;
        $article->title = $request->title;
        $article->body_md = $request->body;
        $article->summary = $request->summary;
        $article->online = $request->online;
        // Set published_at to now if online
        $article->published_at = $request->online ? now() : null;
        // Generate slug for urls
        $article->slug = Str::slug($article->title, '-');
        // Generate html from markdown
        $article->body_html = Str::markdown($article->body_md);
        // Save image
        if ($request->hasFile('image')) {
            $imageName = time().'.'.$request->image->extension();
            $path = $request->file('image')->storeAs('public/articles', $imageName);
            $article->imagePath = Storage::url($path);
        }
        $article->save();

        session()->flash('alert-'.AlertLevelEnum::SUCCESS->name, 'Article enregistré avec succès.');

        return redirect()->route('articles.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Article $article): View
    {
        return view('article.show')->with('article', $article);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Article $article): View
    {
        return view('article.edit')->with('article', $article);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Article $article): RedirectResponse
    {
        $request->request->add(['remove_image' => filter_var($request->remove_image, FILTER_VALIDATE_BOOLEAN)]);
        $request['online'] = filter_var($request['online'], FILTER_VALIDATE_BOOLEAN);
        $request->validateWithBag('updateArticle', [
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'online' => ['boolean'],
            'summary' => ['nullable', 'string', 'max:350'],
            'image' => ['nullable', 'mimes:png,jpg,jpeg,gif', 'max:10000', 'dimensions:max_width=2560,max_height=1600'],
            'remove_image' => ['boolean'],
        ]);
        // If the article is online and has no previous image or the current image is removed, the image is required
        if ($request->online && (! $article->imagePath || $request->remove_image)) {
            $request->validateWithBag('updateArticle', [
                'image' => ['required'],
            ]);
        }

        $article->title = $request->title;
        $article->body_md = $request->body;
        $article->summary = $request->summary;
        // Change published_at if online status changed
        if ($article->online != $request->online) {
            $article->published_at = $request->online ? now() : null;
            $article->online = $request->online;
        }
        // Generate slug for urls
        $article->slug = Str::slug($article->title, '-');
        // Generate html from markdown
        $article->body_html = Str::markdown($article->body_md);
        // Suppression de l'image actuelle si demandée, ou si une autre image a été uploadée
        if (($request->remove_image || $request->hasFile('image')) && $article->imagePath) {
            $filePath = 'public/articles/'.basename($article->imagePath);
            if (Storage::delete($filePath)) {
                $article->imagePath = null;
            }
        }
        // Si une nouvelle image a été uploadée, on la sauvegarde
        if ($request->hasFile('image')) {
            $imageName = time().'.'.$request->image->extension();
            $path = $request->file('image')->storeAs('public/articles', $imageName);
            $article->imagePath = Storage::url($path);
        }
        $article->save();

        session()->flash('alert-'.AlertLevelEnum::SUCCESS->name, 'Article enregistré avec succès.');

        return redirect()->route('articles.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Article $article): RedirectResponse
    {
        $article->delete();

        session()->flash('alert-'.AlertLevelEnum::SUCCESS->name, 'Article supprimé avec succès.');

        return redirect()->route('articles.index');
    }

    /**
     * Upload an image and return the url.
     */
    public function storeImage(Request $request): JsonResponse
    {
        $request->validate([
            'image' => ['required', 'mimes:png,jpg,jpeg,gif', 'max:10000', 'dimensions:max_width=800,max_height=800'],
        ]);
        $imageName = time().'.'.$request->image->extension();
        $path = $request->file('image')->storeAs('public/articles', $imageName);

        return response()->json([
            'success' => true,
            'data' => [
                'url' => Storage::url($path),
            ],
        ]);
    }
}
