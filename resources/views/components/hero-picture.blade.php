<picture>
    <source srcset="{{ Vite::asset('resources/images/hero/hero-lg.jpg') }}" media="(min-width: 992px)" width="1200"
        height="500">
    <source
        srcset="
    {{ Vite::asset('resources/images/hero/hero-md-w_767.jpg') }} 767w,
    {{ Vite::asset('resources/images/hero/hero-md-w_984.jpg') }} 984w,
    {{ Vite::asset('resources/images/hero/hero-md-w_991.jpg') }} 991w,
    "
        media="(min-width: 768px)" width="991" height="432">
    <img class="h-full w-full object-cover" src="{{ Vite::asset('resources/images/hero/hero.jpg') }}"
        alt="Photo de la corse, prise depuis la mer, montrant 3 bateaux naviguants devant de belles falaises."
        width="767" height="320" loading="lazy" decoding="async">
</picture>
