<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

/**
 * @implements CastsAttributes<string|null, string|null>
 */
class RichText implements CastsAttributes
{
    protected static ?HtmlSanitizer $sanitizer = null;

    /**
     * Convertit la valeur donnée.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        return $value === null ? null : static::sanitize($value);
    }

    /**
     * Prépare la valeur donnée pour l'enregistrement.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        return $value;
    }

    /**
     * Ne garde que la mise en forme que la barre d'outils de Trix peut produire, et les liens vers des pages web,
     * des adresses e-mail et des numéros de téléphone.
     */
    public static function sanitize(string $html): string
    {
        static::$sanitizer ??= new HtmlSanitizer(
            (new HtmlSanitizerConfig)
                ->allowElement('div')
                ->allowElement('br')
                ->allowElement('strong')
                ->allowElement('em')
                ->allowElement('del')
                ->allowElement('a', ['href'])
                ->allowElement('h2')
                ->allowElement('h3')
                ->allowElement('h4')
                ->allowElement('blockquote')
                ->allowElement('pre')
                ->allowElement('ul')
                ->allowElement('ol')
                ->allowElement('li')
                ->allowLinkSchemes(['http', 'https', 'mailto', 'tel'])
        );

        return static::$sanitizer->sanitize($html);
    }

    /**
     * Donne le texte que voit le lecteur, avec les paragraphes, les titres, les retours à la ligne et les éléments
     * de liste séparés par un espace.
     */
    public static function toPlainText(string $html): string
    {
        $separatedBlocks = preg_replace('/<br \/>|<\/(div|h[2-4]|blockquote|pre|li)>/', ' ', static::sanitize($html));

        return Str::squish(html_entity_decode(strip_tags((string) $separatedBlocks), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    }
}
