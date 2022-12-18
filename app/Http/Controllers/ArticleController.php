<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Support\Str;
use App\Enum\AlertLevelEnum;
use Illuminate\Http\Request;
use App\Http\Requests\StoreArticleRequest;
use App\Http\Requests\UpdateArticleRequest;

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
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('article.index', [
            'articles' => Article::orderByDesc('updated_at')->where('online', true)->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('article.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request['online'] = filter_var($request['online'], FILTER_VALIDATE_BOOLEAN);
        $request->validate([
            'title' => ['required', 'string', 'max:255', 'unique:articles'],
            'body' => ['required', 'string'],
            'online' => ['boolean'],
            'summary' => ['nullable', 'string', 'max:350'],
        ]);

        $article = new Article();
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
        $article->save();

        session()->flash('alert-' . AlertLevelEnum::SUCCESS->name, 'Article enregistré avec succès.');

        return redirect()->route('articles.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Article  $article
     * @return \Illuminate\Http\Response
     */
    public function show(Article $article)
    {
        return view('article.show')->with('article', $article);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Article  $article
     * @return \Illuminate\Http\Response
     */
    public function edit(Article $article)
    {
        return view('article.edit')->with('article', $article);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\Request  $request
     * @param  \App\Models\Article  $article
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Article $article)
    {
        $request['online'] = filter_var($request['online'], FILTER_VALIDATE_BOOLEAN);
        $request->validateWithBag('updateArticle', [
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'online' => ['boolean'],
            'summary' => ['nullable', 'string', 'max:350'],
        ]);

        $article->title = $request->title;
        $article->body_md = $request->body;
        $article->summary = $request->summary;
        $article->online = $request->online;
        // Set published_at to now if previously offline and now online
        $article->published_at = !isset($article->published_at) && $request->online ? now() : $article->published_at;
        // Generate slug for urls
        $article->slug = Str::slug($article->title, '-');
        // Generate html from markdown
        $article->body_html = Str::markdown($article->body_md);
        $article->save();

        session()->flash('alert-' . AlertLevelEnum::SUCCESS->name, 'Article enregistré avec succès.');

        return redirect()->route('articles.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Article  $article
     * @return \Illuminate\Http\Response
     */
    public function destroy(Article $article)
    {
        $article->delete();

        session()->flash('alert-' . AlertLevelEnum::SUCCESS->name, 'Article supprimé avec succès.');

        return redirect()->route('articles.index');
    }
}
