{{--
    Frise des sorties, à gauche des cartes sur grand écran, sur un fond qui atténue la mer dessinée derrière elle.
    Elle reste à l'écran et glisse sous la ligne de lecture au rythme des cartes : chaque carte, chaque année
    et le jour présent ont leur repère (data-timeline-anchor dans la liste, data-timeline-mark ici).
    Un repère touché mène à sa carte ou à son année.
--}}

<nav aria-label="Frise des sorties" data-trip-timeline {{ $attributes }}>
    <div data-timeline-viewport
        class="sticky top-0 h-dvh overflow-hidden bg-linear-to-r from-primary-50 from-75% to-primary-50/0 mask-[linear-gradient(to_bottom,#000_85%,transparent)]">
        {{-- Ligne de lecture, sur l'axe : la frise y place le moment de la carte en haut de l'écran --}}
        <div data-timeline-reading aria-hidden="true"
            class="pointer-events-none absolute top-[30%] left-0 z-20 flex w-9 -translate-y-1/2 items-center">
            <span class="h-0.5 grow rounded-full bg-primary-700"></span>
            <span class="size-3 shrink-0 rounded-full bg-primary-700 ring-4 ring-primary-100"></span>
        </div>

        <div data-timeline-track class="relative will-change-transform" style="height: {{ $height }}rem">
            <span aria-hidden="true" class="absolute inset-y-0 left-3 w-px bg-gray-300"></span>
            @foreach ($ticks as $tick)
                <span aria-hidden="true" style="top: {{ $tick['top'] }}rem" @class([
                    'absolute left-3 h-px',
                    'w-5 bg-gray-400' => $tick['isNewYear'],
                    'w-2 bg-gray-300' => !$tick['isNewYear'],
                ])></span>
            @endforeach

            <ol>
                @foreach ($marks as $mark)
                    <li data-timeline-mark="{{ $mark['key'] }}" style="top: {{ $mark['top'] }}rem" @class([
                        'absolute inset-x-0',
                        // L'année en cours de lecture reste épinglée en haut de la frise, sur fond blanc
                        'group/year z-10' => $mark['type'] === 'year',
                    ])>
                        @switch($mark['type'])
                            @case('year')
                                <div class="-translate-y-1/2 py-2 pl-10 group-data-pinned/year:bg-primary-50 group-data-pinned/year:shadow-[0_0.5rem_0.75rem_0.25rem_var(--color-primary-50)]">
                                    @if ($mark['hasTrips'])
                                        <a href="#annee-{{ $mark['year'] }}"
                                            class="font-merriweather text-2xl font-bold text-gray-900 hover:text-primary-800 hover:underline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-700">
                                            {{ $mark['year'] }}
                                        </a>
                                    @else
                                        <p class="font-merriweather text-2xl font-bold text-gray-400">{{ $mark['year'] }}</p>
                                        <p class="text-sm text-gray-600">Aucune sortie</p>
                                    @endif
                                </div>
                            @break

                            @case('today')
                                <p class="flex -translate-y-1/2 items-center gap-3">
                                    <span aria-hidden="true" class="ml-1.5 size-3 rounded-full bg-white ring-2 ring-primary-700"></span>
                                    <span class="rounded-full bg-primary-700 px-3 py-0.5 text-sm font-bold text-white">Aujourd'hui</span>
                                </p>
                            @break

                            @case('trip')
                                @php($trip = $mark['trip'])
                                <span aria-hidden="true" @class([
                                    'absolute top-0 left-3 size-2.5 -translate-1/2 rounded-full',
                                    'bg-primary-700' => $trip->isFinished(),
                                    'bg-white ring-2 ring-primary-700' => !$trip->isFinished(),
                                ])></span>
                                <a href="#trip-item-{{ $trip->id }}" data-timeline-trip="trip-item-{{ $trip->id }}"
                                    class="group/mark -mt-3 block rounded-lg py-0.5 pr-2 pl-10 text-gray-700 transition-colors hover:text-gray-900 focus-visible:outline-2 focus-visible:outline-primary-700 aria-[current=location]:font-bold aria-[current=location]:text-primary-900">
                                    <span class="line-clamp-2 leading-snug group-hover/mark:underline">{{ $trip->title }}</span>
                                    <span class="block text-sm font-normal text-gray-600">{{ $shortDates($trip) }}</span>
                                </a>
                            @break
                        @endswitch
                    </li>
                @endforeach
            </ol>
        </div>
    </div>
</nav>

@once
    @push('scripts')
        <script>
            // Fait glisser la frise au rythme des cartes : la position de la ligne de lecture entre deux repères
            // de la liste (une année, une carte, aujourd'hui) donne la même position entre leurs repères sur la frise.
            (() => {
                const timeline = document.querySelector('[data-trip-timeline]');
                if (!timeline) {
                    return;
                }
                const viewport = timeline.querySelector('[data-timeline-viewport]');
                const reading = timeline.querySelector('[data-timeline-reading]');
                const track = timeline.querySelector('[data-timeline-track]');
                const marks = new Map([...timeline.querySelectorAll('[data-timeline-mark]')].map((mark) => [mark.dataset.timelineMark, mark]));
                const anchors = [...document.querySelectorAll('[data-timeline-anchor]')].filter((anchor) => marks.has(anchor.dataset.timelineAnchor));
                const years = [...marks.values()].filter((mark) => mark.dataset.timelineMark.startsWith('year-'));
                const readingLine = 0.3;
                let current = null;
                let frame = null;

                function markTop(anchor) {
                    return marks.get(anchor.dataset.timelineAnchor).offsetTop;
                }

                function setCurrent(link) {
                    if (link === current) {
                        return;
                    }
                    current?.removeAttribute('aria-current');
                    link?.setAttribute('aria-current', 'location');
                    current = link;
                }

                function update() {
                    frame = null;
                    if (anchors.length === 0 || !timeline.checkVisibility()) {
                        return;
                    }
                    // La ligne de lecture se place dans la partie visible de la frise, qui ne l'est pas en entier
                    // avant d'arriver en haut de l'écran ni à la fin de la liste
                    const box = viewport.getBoundingClientRect();
                    const visibleTop = Math.max(box.top, 0) - box.top;
                    const visibleBottom = Math.min(box.bottom, innerHeight) - box.top;
                    const lineInBox = visibleTop + (visibleBottom - visibleTop) * readingLine;
                    const line = box.top + lineInBox;
                    reading.style.top = `${lineInBox}px`;
                    const tops = anchors.map((anchor) => anchor.getBoundingClientRect().top);
                    const index = tops.findLastIndex((top) => top <= line);

                    let position;
                    if (index === -1) {
                        position = markTop(anchors[0]);
                    } else if (index === anchors.length - 1) {
                        position = markTop(anchors[index]);
                    } else {
                        const progress = (line - tops[index]) / (tops[index + 1] - tops[index]);
                        position = markTop(anchors[index]) + progress * (markTop(anchors[index + 1]) - markTop(anchors[index]));
                    }
                    const shift = lineInBox - position;
                    track.style.transform = `translateY(${shift}px)`;
                    pinYear(shift, visibleTop + 28);

                    // La sortie en cours de lecture : la dernière carte passée sous la ligne, sinon la première
                    const cards = anchors.filter((anchor, anchorIndex) => anchor.id.startsWith('trip-item-') && anchorIndex <= Math.max(index, 0));
                    const card = cards.at(-1) ?? anchors.find((anchor) => anchor.id.startsWith('trip-item-'));
                    setCurrent(card ? timeline.querySelector(`[data-timeline-trip="${card.id}"]`) : null);
                }

                // L'année dont on lit les sorties reste en haut de la frise, jusqu'à ce que la suivante la pousse
                function pinYear(shift, pinned) {
                    const gap = 52;
                    const centers = years.map((mark) => mark.offsetTop + shift);
                    const index = centers.findLastIndex((center) => center < pinned);
                    years.forEach((mark, yearIndex) => {
                        const isPinned = yearIndex === index;
                        const center = isPinned ? Math.min(pinned, (centers[yearIndex + 1] ?? Infinity) - gap) : centers[yearIndex];
                        mark.style.translate = `0 ${center - centers[yearIndex]}px`;
                        mark.toggleAttribute('data-pinned', isPinned);
                    });
                }

                function schedule() {
                    frame ??= requestAnimationFrame(update);
                }

                addEventListener('scroll', schedule, { passive: true });
                addEventListener('resize', schedule);
                update();
            })();
        </script>
    @endpush
@endonce
