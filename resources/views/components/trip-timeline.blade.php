{{--
    Frise des sorties, à gauche des cartes sur grand écran, sur un fond qui atténue la mer dessinée derrière elle.
    Elle occupe toute la hauteur de l'écran et montre toutes les années ; seule l'année en cours de lecture est dépliée
    sur ses sorties, espacées selon le temps qui les sépare. Une marque y suit la lecture des cartes : chaque carte,
    chaque année et le jour présent ont leur repère (data-timeline-anchor dans la liste, data-timeline-mark ici).
    Toucher une année ou une sortie y mène.
--}}

<nav aria-label="Frise des sorties" data-trip-timeline {{ $attributes }}>
    <div data-timeline-box class="sticky top-0 h-dvh bg-linear-to-r from-primary-50 from-75% to-primary-50/0 py-4">
        <div class="relative h-full">
            <span aria-hidden="true" class="absolute inset-y-0 left-3 w-px bg-gray-300"></span>

            {{-- Marque de lecture, sur l'axe : la frise y place le moment de la carte en haut de l'écran --}}
            <div data-timeline-reading aria-hidden="true"
                class="pointer-events-none absolute top-0 left-0 z-10 flex w-9 -translate-y-1/2 items-center">
                <span class="h-0.5 grow rounded-full bg-primary-700"></span>
                <span class="size-3 shrink-0 rounded-full bg-primary-700 ring-4 ring-primary-100"></span>
            </div>

            <ol class="flex h-full flex-col">
                @foreach ($years as $year)
                    @php($isFirst = $loop->first)
                    <li data-timeline-year="{{ $year['year'] }}" @if ($isFirst) data-current @endif @class([
                        'group/year flex min-h-0 basis-10 flex-col',
                        'grow-0 overflow-hidden transition-[flex-grow] duration-300 ease-out motion-reduce:transition-none data-current:grow' => $year['tripCount'] > 0,
                        'shrink-0' => $year['tripCount'] === 0,
                    ])>
                        <div data-timeline-mark="year-{{ $year['year'] }}" class="relative flex h-10 shrink-0 items-center gap-3 pl-10">
                            <span aria-hidden="true" data-timeline-point class="absolute top-1/2 left-3 h-px w-5 bg-gray-400"></span>
                            @if ($year['tripCount'] > 0)
                                <a href="#annee-{{ $year['year'] }}"
                                    class="font-merriweather text-xl font-bold text-gray-900 hover:text-primary-800 hover:underline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-700 group-data-current/year:text-2xl group-data-current/year:text-primary-900">
                                    {{ $year['year'] }}
                                </a>
                                <span class="text-sm text-gray-600 group-data-current/year:hidden">
                                    {{ $year['tripCount'] }} {{ $year['tripCount'] > 1 ? 'sorties' : 'sortie' }}
                                </span>
                            @else
                                <span class="font-merriweather text-xl font-bold text-gray-400">{{ $year['year'] }}</span>
                                <span class="text-sm text-gray-600">Aucune sortie</span>
                                @if ($year['hasToday'])
                                    <span data-timeline-mark="today" class="rounded-full bg-primary-700 px-3 py-0.5 text-sm font-bold text-white">Aujourd'hui</span>
                                @endif
                            @endif
                        </div>

                        @if ($year['tripCount'] > 0)
                            {{-- Les sorties de l'année, dépliées quand on la lit : les écarts se partagent la place selon les jours --}}
                            <ol data-timeline-trips @unless ($isFirst) inert @endunless
                                class="invisible flex min-h-0 grow flex-col overflow-y-auto pb-2 [scrollbar-width:thin] group-data-current/year:visible">
                                @foreach ($year['entries'] as $entry)
                                    @switch($entry['type'])
                                        @case('gap')
                                            <li aria-hidden="true" class="min-h-0 basis-0" style="flex-grow: {{ $entry['days'] }}"></li>
                                        @break

                                        @case('today')
                                            <li data-timeline-mark="today" class="relative flex shrink-0 items-center gap-3 py-1">
                                                <span aria-hidden="true" data-timeline-point class="ml-1.5 size-3 rounded-full bg-white ring-2 ring-primary-700"></span>
                                                <span class="rounded-full bg-primary-700 px-3 py-0.5 text-sm font-bold text-white">Aujourd'hui</span>
                                            </li>
                                        @break

                                        @case('trip')
                                            @php($trip = $entry['trip'])
                                            <li data-timeline-mark="trip-{{ $trip->id }}" class="relative shrink-0">
                                                <span aria-hidden="true" data-timeline-point @class([
                                                    'absolute top-3 left-3 size-2.5 -translate-1/2 rounded-full',
                                                    'bg-primary-700' => $trip->isFinished(),
                                                    'bg-white ring-2 ring-primary-700' => !$trip->isFinished(),
                                                ])></span>
                                                <a href="#trip-item-{{ $trip->id }}" data-timeline-trip="trip-item-{{ $trip->id }}"
                                                    class="group/mark block rounded-lg py-0.5 pr-2 pl-10 text-gray-700 transition-colors hover:text-gray-900 focus-visible:outline-2 focus-visible:-outline-offset-2 focus-visible:outline-primary-700 aria-[current=location]:font-bold aria-[current=location]:text-primary-900">
                                                    <span class="line-clamp-1 leading-snug group-hover/mark:underline">{{ $trip->title }}</span>
                                                    <span class="block text-sm font-normal text-gray-600">{{ $shortDates($trip) }}</span>
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
                const marks = new Map([...timeline.querySelectorAll('[data-timeline-mark]')].map((mark) => [mark.dataset.timelineMark, mark]));
                const anchors = [...document.querySelectorAll('[data-timeline-anchor]')].filter((anchor) => marks.has(anchor.dataset.timelineAnchor));
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
                    // Dans une année trop remplie pour la place, la liste défile jusqu'à la sortie lue
                    const list = link?.closest('[data-timeline-trips]');
                    if (list && list.scrollHeight > list.clientHeight) {
                        const [listBox, linkBox] = [list.getBoundingClientRect(), link.getBoundingClientRect()];
                        list.scrollTop += Math.min(0, linkBox.top - listBox.top) + Math.max(0, linkBox.bottom - listBox.bottom);
                    }
                }

                function update() {
                    frame = null;
                    if (anchors.length === 0 || !timeline.checkVisibility()) {
                        return;
                    }
                    // La frise tient dans la partie visible de sa colonne, en haut de page comme en fin de liste
                    const column = timeline.getBoundingClientRect();
                    box.style.height = `${Math.max(0, Math.min(innerHeight, column.bottom) - Math.max(0, column.top))}px`;

                    const line = innerHeight * readingLine;
                    const section = sections.findLast((element) => element.getBoundingClientRect().top <= line) ?? sections[0];
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

                    // La sortie lue : la dernière carte de l'année passée sous la ligne de lecture
                    const card = anchors.slice(0, index + 1).findLast((anchor) => anchor.id.startsWith('trip-item-') && section.contains(anchor));
                    setCurrentLink(card ? timeline.querySelector(`[data-timeline-trip="${card.id}"]`) : null);

                    if (performance.now() < animatedUntil) {
                        frame = requestAnimationFrame(update);
                    }
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
