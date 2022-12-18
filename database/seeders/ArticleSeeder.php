<?php

namespace Database\Seeders;

use App\Models\Article;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ArticleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $body_md = "# Bienvenue sur le site !

        Ce site servira à communiquer sur l'actualité d'Akipaj et à partager ses photos aux autres membres de l'association.

        Plusieurs pages sont à retrouver, avec chacune leur fonction bien particulière :

        * Actualités, où vous pouvez retrouver des articles comme celui-là
        * Sorties, où est listé l'historique des sorties de l'association comme les régates ou les croisières
        * Programme, comme son nom l'indique cette page liste les événement à venir de l'association";

        $body_html = "<h1>Bienvenue sur le site !</h1>
        <p>Ce site servira à communiquer sur l'actualité d'Akipaj et à partager ses photos aux autres membres de l'association.</p>
        <p>Plusieurs pages sont à retrouver, avec chacune leur fonction bien particulière :</p>
        <ul>
        <li>Actualités, où vous pouvez retrouver des articles comme celui-là</li>
        <li>Sorties, où est listé l'historique des sorties de l'association comme les régates ou les croisières</li>
        <li>Programme, comme son nom l'indique cette page liste les événement à venir de l'association</li>
        </ul>";

        Article::create([
            'title' => 'Premier article du site !',
            'slug' => 'premier-article-du-site-00-00',
            'summary' => 'Premier article du site, nous revenons sur son fonctionnement et la liste de ses pages.',
            'body_md' => $body_md,
            'body_html' => $body_html,
            'online' => true,
            'published_at' => now(),
            'imagePath' => fake()->imageUrl(640, 480, 'cats'),
        ]);
    }
}
