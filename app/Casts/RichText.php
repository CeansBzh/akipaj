<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

/**
 * HTML written with the Trix editor.
 *
 * The HTML is sanitized when it is read, not when it is saved: the sanitizer encodes apostrophes as entities,
 * which the search could no longer match, and the descriptions written as plain text before the editor are
 * then rendered safely too.
 *
 * @implements CastsAttributes<string|null, string|null>
 */
class RichText implements CastsAttributes
{
    protected static ?HtmlSanitizer $sanitizer = null;

    /**
     * Cast the given value.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        return $value === null ? null : static::sanitize($value);
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        return $value;
    }

    /**
     * Keep only the formatting the Trix toolbar can produce, and links to web pages, emails and phone numbers.
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
                ->allowElement('h1')
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
     * Get the text a reader sees, with the paragraphs, line breaks and list items separated by a space.
     */
    public static function toPlainText(string $html): string
    {
        $separatedBlocks = preg_replace('/<br \/>|<\/(div|h1|blockquote|pre|li)>/', ' ', static::sanitize($html));

        return Str::squish(html_entity_decode(strip_tags((string) $separatedBlocks), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    }
}
