{{--
    Frise des sorties, à gauche des cartes sur grand écran, sur un fond qui atténue la mer dessinée derrière elle.
    Elle occupe toute la hauteur de l'écran et montre toutes les années, quitte à défiler quand elles n'y tiennent pas ;
    seule l'année en cours de lecture est dépliée sur ses sorties, espacées selon le temps qui les sépare. Une marque y suit la lecture des cartes : chaque carte,
    chaque année et le jour présent ont leur repère (data-timeline-anchor dans la liste, data-timeline-mark ici).
    Toucher une année ou une sortie y mène.
--}}

<nav aria-label="Frise des sorties" data-trip-timeline {{ $attributes }}>
    <div data-timeline-box
        class="top-below-nav-0 bg-linear-to-r bg-radial from-primary-50 sticky h-dvh overflow-y-auto overscroll-contain from-20% to-transparent py-4 [scrollbar-width:thin]">
        <div class="relative min-h-full">
            <span aria-hidden="true" class="absolute inset-y-0 left-3 w-px bg-gray-300"></span>

            {{-- Marque de lecture, sur l'axe : la frise y place le moment de la carte en haut de l'écran --}}
            <div data-timeline-reading aria-hidden="true"
                class="pointer-events-none absolute left-0 top-0 z-10 flex w-9 -translate-y-1/2 items-center">
                <span class="bg-primary-700 h-0.5 grow rounded-full"></span>
                <span class="bg-primary-700 ring-primary-100 size-3 shrink-0 rounded-full ring-4"></span>
            </div>

            <ol>
                @foreach ($years as $year)
                    @php($isFirst = $loop->first)
                    <li data-timeline-year="{{ $year['year'] }}" @if ($isFirst) data-current @endif
                        @class([
                            'group/year',
                            'grid grid-rows-[2.5rem_0fr] transition-[grid-template-rows] duration-300 ease-out motion-reduce:transition-none data-current:grid-rows-[2.5rem_1fr]' =>
                                $year['tripCount'] > 0,
                        ])>
                        <div data-timeline-mark="year-{{ $year['year'] }}"
                            class="relative flex h-10 shrink-0 items-center gap-3 pl-10">
                            <span aria-hidden="true" data-timeline-point
                                class="absolute left-3 top-1/2 h-px w-5 bg-gray-400"></span>
                            @if ($year['tripCount'] > 0)
                                <a href="#annee-{{ $year['year'] }}"
                                    class="font-merriweather hover:text-primary-800 focus-visible:outline-primary-700 group-data-current/year:text-2xl group-data-current/year:text-primary-900 text-xl font-bold text-gray-900 hover:underline focus-visible:outline-2 focus-visible:outline-offset-2">
                                    {{ $year['year'] }}
                                </a>
                                <span class="group-data-current/year:hidden text-sm text-gray-600">
                                    {{ $year['tripCount'] }} {{ $year['tripCount'] > 1 ? 'sorties' : 'sortie' }}
                                </span>
                            @else
                                <span
                                    class="font-merriweather text-xl font-bold text-gray-400">{{ $year['year'] }}</span>
                                <span class="text-sm text-gray-600">Aucune sortie</span>
                                @if ($year['hasToday'])
                                    <span data-timeline-mark="today"
                                        class="bg-primary-700 rounded-full px-3 py-0.5 text-sm font-bold text-white">Aujourd'hui</span>
                                @endif
                            @endif
                        </div>

                        @if ($year['tripCount'] > 0)
                            {{-- Les sorties de l'année, dépliées quand on la lit, séparées d'écarts à proportion des jours --}}
                            <ol data-timeline-trips @unless ($isFirst) inert @endunless
                                class="group-data-current/year:visible invisible min-h-0 overflow-hidden">
                                @foreach ($year['entries'] as $entry)
                                    @switch($entry['type'])
                                        @case('gap')
                                            <li aria-hidden="true" style="height: {{ $gapHeight($entry['days']) }}"></li>
                                        @break

                                        @case('today')
                                            <li data-timeline-mark="today"
                                                class="relative flex items-center gap-3 py-1">
                                                <span aria-hidden="true" data-timeline-point
                                                    class="ring-primary-700 ml-1.5 size-3 rounded-full bg-white ring-2"></span>
                                                <span
                                                    class="bg-primary-700 rounded-full px-3 py-0.5 text-sm font-bold text-white">Aujourd'hui</span>
                                            </li>
                                        @break

                                        @case('trip')
                                            @php($trip = $entry['trip'])
                                            <li data-timeline-mark="trip-{{ $trip->id }}" class="relative">
                                                <span aria-hidden="true" data-timeline-point
                                                    @class([
                                                        'absolute top-3 left-3 size-2.5 -translate-1/2 rounded-full',
                                                        'bg-primary-700' => $trip->isFinished(),
                                                        'bg-white ring-2 ring-primary-700' => !$trip->isFinished(),
                                                    ])></span>
                                                <a href="#trip-item-{{ $trip->id }}"
                                                    data-timeline-trip="trip-item-{{ $trip->id }}"
                                                    class="group/mark focus-visible:outline-primary-700 aria-[current=location]:text-primary-900 block rounded-lg py-0.5 pl-10 pr-2 text-gray-700 transition-colors hover:text-gray-900 focus-visible:outline-2 focus-visible:-outline-offset-2 aria-[current=location]:font-bold">
                                                    <span
                                                        class="line-clamp-1 leading-snug group-hover/mark:underline">{{ $trip->title }}</span>
                                                    <span
                                                        class="block text-sm font-normal text-gray-600">{{ $shortDates($trip) }}</span>
                                                </a>
                                            </li>
                                        @break
                                    @endswitch
                                @endforeach
                            </ol>
                        @endif
                    </li>
                @endforeach
            </ol>
        </div>
    </div>
</nav>

@once
    @push('scripts')
        <script>
            // Suit la lecture des cartes : déplie l'année lue et place la marque de lecture entre les repères des deux
            // éléments de la liste (une année, une carte, aujourd'hui) qui entourent la ligne de lecture.
            (() => {
                const timeline = document.querySelector('[data-trip-timeline]');
                if (!timeline) {
                    return;
                }
                const box = timeline.querySelector('[data-timeline-box]');
                const reading = timeline.querySelector('[data-timeline-reading]');
                const years = [...timeline.querySelectorAll('[data-timeline-year]')];
                const marks = new Map([...timeline.querySelectorAll('[data-timeline-mark]')].map((mark) => [mark.dataset
                    .timelineMark, mark
                ]));
                const anchors = [...document.querySelectorAll('[data-timeline-anchor]')].filter((anchor) => marks.has(anchor
                    .dataset.timelineAnchor));
                const sections = anchors.filter((anchor) => anchor.dataset.timelineAnchor.startsWith('year-'));
                const readingLine = 0.3;
                let currentYear = null;
                let currentLink = null;
                let frame = null;
                let animatedUntil = 0;

                function openYear(year) {
                    if (year === currentYear) {
                        return;
                    }
                    currentYear = year;
                    years.forEach((element) => {
                        const isCurrent = element.dataset.timelineYear === year;
                        element.toggleAttribute('data-current', isCurrent);
                        element.querySelector('[data-timeline-trips]')?.toggleAttribute('inert', !isCurrent);
                    });
                    // La marque suit l'année qui se déplie
                    animatedUntil = performance.now() + 400;
                }

                // Hauteur d'un repère à l'écran ; une sortie d'une année repliée est représentée par son année
                function markY(anchor) {
                    let mark = marks.get(anchor.dataset.timelineAnchor);
                    const year = mark.closest('[data-timeline-trips]')?.closest('[data-timeline-year]');
                    if (year && !year.hasAttribute('data-current')) {
                        mark = year.querySelector('[data-timeline-mark]');
                    }
                    const point = (mark.querySelector('[data-timeline-point]') ?? mark).getBoundingClientRect();

                    return point.top + point.height / 2;
                }

                function setCurrentLink(link) {
                    if (link === currentLink) {
                        return;
                    }
                    currentLink?.removeAttribute('aria-current');
                    link?.setAttribute('aria-current', 'location');
                    currentLink = link;
                }

                // Quand la frise ne tient pas à l'écran, elle défile pour garder la marque de lecture en vue
                function keepInView(y) {
                    const view = box.getBoundingClientRect();
                    const [top, bottom] = [view.top + view.height * 0.2, view.top + view.height * 0.6];
                    box.scrollTop += Math.min(0, y - top) + Math.max(0, y - bottom);
                }

                function update() {
                    frame = null;
                    if (anchors.length === 0 || !timeline.checkVisibility()) {
                        return;
                    }
                    // La frise tient dans la partie visible de sa colonne, sous la barre de navigation quand elle est là,
                    // en haut de page comme en fin de liste
                    const column = timeline.getBoundingClientRect();
                    const boxTop = Math.max(parseFloat(getComputedStyle(box).top) || 0, column.top);
                    box.style.height = `${Math.max(0, Math.min(innerHeight, column.bottom) - boxTop)}px`;

                    const line = innerHeight * readingLine;
                    const section = sections.findLast((element) => element.getBoundingClientRect().top <= line) ?? sections[
                        0];
                    openYear(section.dataset.timelineAnchor.replace('year-', ''));

                    const tops = anchors.map((anchor) => anchor.getBoundingClientRect().top);
                    const index = tops.findLastIndex((top) => top <= line);
                    let y;
                    if (index === -1 || index === anchors.length - 1) {
                        y = markY(anchors[Math.max(index, 0)]);
                    } else {
                        const progress = Math.min(1, (line - tops[index]) / (tops[index + 1] - tops[index]));
                        y = markY(anchors[index]) + progress * (markY(anchors[index + 1]) - markY(anchors[index]));
                    }
                    reading.style.top = `${y - box.firstElementChild.getBoundingClientRect().top}px`;
                    keepInView(y);

                    // La sortie lue : la dernière carte de l'année passée sous la ligne de lecture
                    const card = anchors.slice(0, index + 1).findLast((anchor) => anchor.id.startsWith('trip-item-') &&
                        section.contains(anchor));
                    setCurrentLink(card ? timeline.querySelector(`[data-timeline-trip="${card.id}"]`) : null);

                    if (performance.now() < animatedUntil) {
                        frame = requestAnimationFrame(update);
                    }
                }

                function schedule() {
                    frame ??= requestAnimationFrame(update);
                }

                addEventListener('scroll', schedule, {
                    passive: true
                });
                addEventListener('resize', schedule);
                // La frise glisse avec la barre de navigation qui se range ou revient : sa hauteur la suit
                box.addEventListener('transitionrun', (event) => {
                    if (event.target === box) {
                        animatedUntil = performance.now() + 400;
                        schedule();
                    }
                });
                update();
            })
            ();
        </script>
    @endpush
@endonce
