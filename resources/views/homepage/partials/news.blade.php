@php
$news = collect([
collect([
'title' => 'Noteworthy technology acquisitions 2020',
'date' => '20 avril 2012',
'image' => 'hero.bak.jpg',
'excerpt' => 'Here are the biggest enterprise technology acquisitions of 2021 so far, in reverse chronological order.',
'link' => '#',
]),
collect([
'title' => 'Noteworthy technology acquisitions 2021',
'date' => '20 avril 2012',
'image' => 'hero.jpg',
'excerpt' => 'Here are the biggest enterprise technology acquisitions of 2021 so far, in reverse chronological order.',
'link' => '#',
]),
collect([
'title' => 'Noteworthy technology acquisitions 2022',
'date' => '20 avril 2012',
'image' => 'hero.bak.jpg',
'excerpt' => 'Here are the biggest enterprise technology acquisitions of 2021 so far, in reverse chronological order.',
'link' => '#',
]),
]);
@endphp

<div
    class="flex flex-col space-y-3 max-w-md mx-auto md:grid md:grid-cols-[1.3fr_repeat(2,1fr)] md:gap-3 md:space-y-0 md:max-w-none">
    {{-- first article --}}
    <div class="relative shadow-sm overflow-hidden group/card">
        <img src="{{ Vite::asset('resources/images/hero.jpg') }}" alt="Image de couverture de la sortie text"
            class="absolute object-cover w-full h-full select-none overflow-hidden transform duration-500 ease-in-out group-hover/card:scale-105">
        <div class="absolute inset-0 bg-gradient-to-tr from-neutral-800/75">
        </div>
        <div class="relative top-0 p-5 max-w-sm w-full h-full flex flex-col justify-end">
            <p class="font-semibold text-sky-300 mb-2">20 avril 2012</p>
            <a href="#">
                <h5 class="mb-4 text-2xl font-bold tracking-tight text-white">test</h5>
            </a>
            <p class="mb-3 font-normal text-white">Here are the biggest enterprise technology
                acquisitions of 2021 so far, in reverse chronological order.</p>
            <a href="#" class="inline-flex items-center text-sm font-medium text-center text-white group/read-more">
                Lire la suite
                <svg aria-hidden="true"
                    class="w-4 h-4 ml-2 transform transition duration-300 ease-in-out group-hover/read-more:translate-x-1"
                    fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd"
                        d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z"
                        clip-rule="evenodd"></path>
                </svg>
            </a>
        </div>
    </div>

    {{-- second and third articles --}}
    @foreach($news->slice(1) as $article)
    <div class="block bg-white shadow-md overflow-hidden group/card">
        <div class="relative pb-48 overflow-hidden">
            <a href="#">
                <img class="absolute inset-0 h-full w-full object-cover select-none transform duration-500 ease-in-out group-hover/card:scale-105"
                    src="{{ Vite::asset('resources/images/'.$article['image']) }}"
                    alt="Image de couverture de article 1" />
            </a>
        </div>
        <div class="p-5">
            <p class="font-semibold text-sky-600 mb-2">20 avril 2012</p>
            <a href="#">
                <h5 class="mb-4 text-2xl font-bold tracking-tight text-gray-900">{{ $article['title'] }}</h5>
            </a>
            <p class="mb-3 font-normal text-gray-700">Here are the biggest enterprise technology
                acquisitions of 2021 so far, in reverse chronological order.</p>
            <a href="#" class="inline-flex items-center text-sm font-medium text-center text-gray-600 group/read-more">
                Lire la suite
                <svg aria-hidden="true"
                    class="w-4 h-4 ml-2 transform transition duration-300 ease-in-out group-hover/read-more:translate-x-1"
                    fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd"
                        d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z"
                        clip-rule="evenodd"></path>
                </svg>
            </a>
        </div>
    </div>
    @endforeach
</div>
