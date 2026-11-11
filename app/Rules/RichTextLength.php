<?php

namespace App\Rules;

use App\Casts\RichText;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Str;
use Illuminate\Translation\PotentiallyTranslatedString;

/**
 * Limits the length of the text written with the Trix editor, without counting its formatting.
 */
class RichTextLength implements ValidationRule
{
    public function __construct(public int $max) {}

    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            return;
        }

        $length = Str::length(RichText::toPlainText($value));

        // The editor submits markup even when nothing is written, such as "<div><br></div>"
        if ($length === 0) {
            $fail('validation.required')->translate();
        } elseif ($length > $this->max) {
            $fail('validation.max.string')->translate(['max' => (string) $this->max]);
        }
    }
}
