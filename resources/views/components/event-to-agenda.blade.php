@props(['event'])

{{-- Call component once in a div containing all the buttons to add the event to an agenda. --}}
{{-- The parent div must have the id 'event-item-{{ $event->id }}' --}}
{{-- The buttons must have the class 'add-to-agenda' --}}
{{-- Values are encoded with @js(): they can contain quotes or line breaks --}}

@push('scripts')
    <script type="text/javascript">
        document.getElementById("event-item-{{ $event->id }}").querySelectorAll('.add-to-agenda').forEach((button) => {
            button.addEventListener('click', () => atcb_action({
                    name: @js($event->name),
                    description: @js($event->description . '.[br]Voir sur le site → [url]' . route('events.show', $event) . '[/url]'),
                    startDate: "{{ $event->start_time->format('Y-m-d') }}",
                    endDate: "{{ $event->end_time->format('Y-m-d') }}",
                    location: @js($event->location ?? ''),
                    language: "fr",
                    options: [
                        "apple",
                        "google",
                        "ical",
                        "ms365",
                        "outlookcom",
                    ],
                    timeZone: "Europe/Paris",
                    trigger: "click",
                    iCalFileName: "Rappel-Akipaj",
                },
                button
            ));
        });
    </script>
@endpush
