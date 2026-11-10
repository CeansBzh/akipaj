@props(['user'])

{{--
    Photo de profil ronde d'un matelot. Ses initiales sont dessous : elles se voient quand il n'a pas de photo,
    ou quand elle ne se charge pas. Elles prennent une teinte de bleu tirée de son nom, toujours la même pour lui.
    Purement décorative : le nom du matelot est toujours écrit à côté.
    Sa taille se donne avec une classe (size-20 par exemple) ; les initiales s'y adaptent.
    Un contenu passé au composant remplace la photo enregistrée, par exemple l'aperçu d'une nouvelle photo choisie.
--}}

@php
    $tones = [
        'bg-primary-100 text-primary-800',
        'bg-primary-200 text-primary-900',
        'bg-primary-700 text-white',
        'bg-primary-900 text-primary-100',
    ];
    $tone = $tones[crc32($user->name) % count($tones)];
@endphp

<span aria-hidden="true"
    {{ $attributes->class(['@container relative inline-flex shrink-0 items-center justify-center overflow-hidden rounded-full select-none', $tone]) }}>
    <span class="text-[38cqi] font-bold leading-none">{{ $user->initials }}</span>
    @if ($slot->isNotEmpty())
        {{ $slot }}
    @elseif ($user->profile_picture_path)
        <img src="{{ $user->profile_picture_path }}" alt="" width="96" height="96" loading="lazy"
            decoding="async" class="absolute inset-0 size-full object-cover">
    @endif
</span>
