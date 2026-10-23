<?php

namespace Tests\Feature;

use App\Models\Article;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ArticleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
    }

    public function test_members_only_see_online_articles(): void
    {
        $this->actingAsMember();
        $online = Article::factory()->create();
        $draft = Article::factory()->draft()->create();

        $articles = $this->get(route('articles.index'))->assertOk()->viewData('articles');

        $this->assertTrue($articles->contains($online));
        $this->assertFalse($articles->contains($draft));
    }

    public function test_admins_see_all_articles(): void
    {
        $this->actingAsAdmin();
        $online = Article::factory()->create();
        $draft = Article::factory()->draft()->create();

        $articles = $this->get(route('articles.index'))->assertOk()->viewData('articles');

        $this->assertTrue($articles->contains($online));
        $this->assertTrue($articles->contains($draft));
    }

    public function test_articles_are_resolved_by_slug(): void
    {
        $this->actingAsMember();
        $article = Article::factory()->create(['slug' => 'mon-article']);

        $this->get('/articles/mon-article')->assertOk()->assertViewIs('article.show')->assertSee($article->title);
    }

    public function test_members_cannot_read_drafts(): void
    {
        $this->actingAsMember();
        $draft = Article::factory()->draft()->create();

        $this->get(route('articles.show', $draft))->assertForbidden();
    }

    public function test_admins_can_read_drafts(): void
    {
        $this->actingAsAdmin();
        $draft = Article::factory()->draft()->create();

        $this->get(route('articles.show', $draft))->assertOk();
    }

    public function test_members_cannot_write_articles(): void
    {
        $this->actingAsMember();
        $article = Article::factory()->create();

        $this->get(route('articles.create'))->assertForbidden();
        $this->post(route('articles.store'), ['title' => 'Titre', 'body' => 'Texte'])->assertForbidden();
        $this->get(route('articles.edit', $article))->assertForbidden();
        $this->put(route('articles.update', $article), ['title' => 'Titre', 'body' => 'Texte'])->assertForbidden();
        $this->delete(route('articles.destroy', $article))->assertForbidden();
    }

    public function test_admin_can_create_a_draft_without_image(): void
    {
        $this->actingAsAdmin();
        $this->get(route('articles.create'))->assertOk();

        $this->post(route('articles.store'), [
            'title' => 'Mon Super Article',
            'body' => "# Titre\n\nDu **gras**.",
            'summary' => 'Résumé',
            'online' => 'false',
        ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('articles.index'));

        $article = Article::where('slug', 'mon-super-article')->firstOrFail();
        $this->assertFalse((bool) $article->online);
        $this->assertNull($article->published_at);
        $this->assertStringContainsString('<strong>gras</strong>', $article->body_html);
        $this->assertNull($article->imagePath);
    }

    public function test_publishing_an_article_requires_an_image(): void
    {
        $this->actingAsAdmin();

        $this->post(route('articles.store'), [
            'title' => 'Article publié',
            'body' => 'Texte',
            'online' => 'true',
        ])->assertSessionHasErrors('image');

        $this->assertDatabaseCount('articles', 0);
    }

    public function test_admin_can_publish_an_article_with_an_image(): void
    {
        $this->actingAsAdmin();

        $this->post(route('articles.store'), [
            'title' => 'Article publié',
            'body' => 'Texte',
            'online' => 'true',
            'image' => UploadedFile::fake()->image('cover.jpg', 1280, 720),
        ])->assertSessionHasNoErrors();

        $article = Article::where('slug', 'article-publie')->firstOrFail();
        $this->assertTrue((bool) $article->online);
        $this->assertNotNull($article->published_at);
        $this->assertNotNull($article->imagePath);
        Storage::disk('local')->assertExists('public/articles/'.basename($article->imagePath));
    }

    public function test_article_titles_must_be_unique(): void
    {
        $this->actingAsAdmin();
        Article::factory()->create(['title' => 'Doublon']);

        $this->post(route('articles.store'), ['title' => 'Doublon', 'body' => 'Texte'])->assertSessionHasErrors('title');
    }

    public function test_admin_can_update_an_article_and_regenerate_its_slug(): void
    {
        $this->actingAsAdmin();
        $article = Article::factory()->draft()->create(['imagePath' => null]);
        $this->get(route('articles.edit', $article))->assertOk();

        $this->put(route('articles.update', $article), [
            'title' => 'Nouveau titre',
            'body' => 'Nouveau *texte*',
            'online' => 'false',
            'remove_image' => 'false',
        ])->assertSessionHasNoErrors()->assertRedirect(route('articles.index'));

        $article->refresh();
        $this->assertSame('nouveau-titre', $article->slug);
        $this->assertStringContainsString('<em>texte</em>', $article->body_html);
    }

    public function test_publishing_an_existing_article_sets_the_publication_date(): void
    {
        $this->actingAsAdmin();
        $article = Article::factory()->draft()->create(['imagePath' => '/storage/articles/cover.jpg']);

        $this->put(route('articles.update', $article), [
            'title' => $article->title,
            'body' => 'Texte',
            'online' => 'true',
            'remove_image' => 'false',
        ])->assertSessionHasNoErrors();

        $this->assertNotNull($article->refresh()->published_at);

        $this->put(route('articles.update', $article), [
            'title' => $article->title,
            'body' => 'Texte',
            'online' => 'false',
            'remove_image' => 'false',
        ])->assertSessionHasNoErrors();

        $this->assertNull($article->refresh()->published_at);
    }

    public function test_an_online_article_cannot_lose_its_image(): void
    {
        $this->actingAsAdmin();
        $article = Article::factory()->create(['imagePath' => '/storage/articles/cover.jpg']);

        $this->put(route('articles.update', $article), [
            'title' => $article->title,
            'body' => 'Texte',
            'online' => 'true',
            'remove_image' => 'true',
        ])->assertSessionHasErrorsIn('updateArticle', 'image');
    }

    public function test_the_image_of_a_draft_can_be_removed(): void
    {
        $this->actingAsAdmin();
        Storage::disk('local')->put('public/articles/cover.jpg', 'image');
        $article = Article::factory()->draft()->create(['imagePath' => '/storage/articles/cover.jpg']);

        $this->put(route('articles.update', $article), [
            'title' => $article->title,
            'body' => 'Texte',
            'online' => 'false',
            'remove_image' => 'true',
        ])->assertSessionHasNoErrors();

        $this->assertNull($article->refresh()->imagePath);
        Storage::disk('local')->assertMissing('public/articles/cover.jpg');
    }

    public function test_admin_can_delete_an_article(): void
    {
        $this->actingAsAdmin();
        $article = Article::factory()->create();

        $this->delete(route('articles.destroy', $article))->assertRedirect(route('articles.index'));

        $this->assertModelMissing($article);
    }

    public function test_admin_can_upload_an_image_from_the_editor(): void
    {
        $this->actingAsAdmin();

        $response = $this->post('/articles/admin/storeImage', [
            'image' => UploadedFile::fake()->image('inline.png', 400, 300),
        ])->assertOk()->assertJson(['success' => true]);

        $url = $response->json('data.url');
        $this->assertIsString($url);
        Storage::disk('local')->assertExists('public/articles/'.basename($url));
    }

    public function test_editor_images_are_limited_in_size(): void
    {
        $this->actingAsAdmin();

        $this->postJson('/articles/admin/storeImage', [
            'image' => UploadedFile::fake()->image('inline.png', 1200, 300),
        ])->assertUnprocessable()->assertJsonValidationErrors('image');
    }

    public function test_members_cannot_upload_editor_images(): void
    {
        $this->actingAsMember();

        $this->post('/articles/admin/storeImage', [
            'image' => UploadedFile::fake()->image('inline.png', 400, 300),
        ])->assertForbidden();
    }
}
