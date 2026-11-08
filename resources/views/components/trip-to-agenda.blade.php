@props(['trip'])

{{--
    Branche les boutons « Ajouter à mon agenda » d'une sortie. À appeler une seule fois dans l'élément
    d'identifiant « trip-item-{id} » qui contient ces boutons, chacun portant la classe « add-to-agenda ».
    Les valeurs sont encodées avec @js() : elles peuvent contenir des guillemets ou des retours à la ligne.
--}}

@push('scripts')
    <script type="text/javascript">
        document.getElementById("trip-item-{{ $trip->id }}").querySelectorAll('.add-to-agenda').forEach((button) => {
            button.addEventListener('click', () => addToAgenda({
                    name: @js($trip->title),
                    description: @js($trip->description_text . '.[br]Voir sur le site → [url]' . route('trips.show', $trip) . '[/url]'),
                    startDate: "{{ $trip->start_date->format('Y-m-d') }}",
                    endDate: "{{ $trip->end_date->format('Y-m-d') }}",
                    location: @js($trip->location ?? ''),
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
