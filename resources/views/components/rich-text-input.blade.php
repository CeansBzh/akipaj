@props(['id', 'name', 'value' => null, 'maxlength' => null])

{{-- Trix fills the hidden input with HTML, sanitized when it is read (App\Casts\RichText) --}}
{{-- The length is counted like App\Rules\RichTextLength: the paragraphs and line breaks are one space --}}
<div {{ $attributes->only('class') }} x-data="{
    length: 0,
    countText(editor) {
        this.length = Array.from(editor.getDocument().toString().replace(/\s+/g, ' ').trim()).length;
    },
}" x-on:trix-initialize="countText($event.target.editor)"
    x-on:trix-change="countText($event.target.editor)">
    <input type="hidden" id="{{ $id }}_value" name="{{ $name }}" value="{{ $value }}">
    <trix-editor id="{{ $id }}" input="{{ $id }}_value" class="rich-text rich-text-input"
        {{ $attributes->except('class') }}></trix-editor>
    @if ($maxlength)
        <p class="mt-1 text-right text-sm" :class="length > {{ $maxlength }} ? 'text-red-600' : 'text-gray-500'">
            <span x-text="length">0</span> / {{ $maxlength }} caractères
        </p>
    @endif
</div>

@once
    @push('scripts')
        @vite('resources/js/rich-text-editor.js')
    @endpush
@endonce
