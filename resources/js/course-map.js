import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import '../css/course-map.css';

/*
 * Cartes du parcours d'une sortie, avec Leaflet : la carte de la page d'une sortie (composant x-course-map)
 * et celle du formulaire où l'on trace le parcours (composant x-course-input).
 *
 * Ce fichier est chargé après le démarrage d'Alpine. Les composants arrivent donc marqués x-ignore :
 * on déclare leurs données, puis on les initialise ici.
 * Les objets Leaflet restent hors des données d'Alpine, qui les envelopperait dans ses proxys et les casserait.
 */

const Alpine = window.Alpine;

// Bretagne sud et golfe de Gascogne, quand le parcours est vide
const DEFAULT_VIEW = { center: [47.3, -3.2], zoom: 7 };
const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

function createMap(element, { draggable }) {
    const map = L.map(element, { scrollWheelZoom: false, dragging: draggable, zoomSnap: 0.5 });

    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 18,
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
    }).addTo(map);
    // Balises, bouées et feux par-dessus le fond de carte
    L.tileLayer('https://tiles.openseamap.org/seamark/{z}/{x}/{y}.png', {
        maxZoom: 18,
        attribution: '&copy; <a href="https://www.openseamap.org">OpenSeaMap</a>',
    }).addTo(map);

    return map;
}

function fitPoints(map, points, animate = false) {
    if (points.length === 0) {
        map.setView(DEFAULT_VIEW.center, DEFAULT_VIEW.zoom);
    } else {
        map.fitBounds(L.latLngBounds(points.map(toLatLng)), {
            padding: [40, 40],
            maxZoom: 12,
            animate: animate && !prefersReducedMotion,
        });
    }
}

function toLatLng(point) {
    return [Number(point.latitude), Number(point.longitude)];
}

// Pastille numérotée : le même numéro que dans la liste des points
function numberedIcon(number) {
    return L.divIcon({
        className: 'course-marker',
        html: `<span>${number}</span>`,
        iconSize: [32, 32],
        iconAnchor: [16, 16],
        popupAnchor: [0, -18],
    });
}

function textPopup(...lines) {
    const popup = document.createElement('div');
    lines.forEach((line, index) => {
        const paragraph = document.createElement('p');
        paragraph.textContent = line;
        paragraph.className = index === 0 ? 'font-bold' : 'text-gray-700';
        popup.append(paragraph);
    });

    return popup;
}

/**
 * Carte de la page d'une sortie : le parcours jour par jour, et un jour mis en avant à la demande.
 *
 * @param {Array<{day: number, label: string, points: Array<{number: number, name: string, latitude: number, longitude: number}>}>} days
 */
Alpine.data('courseMap', (days) => {
    let map;
    const routes = new Map();
    const markers = [];

    return {
        selectedDay: null,

        init() {
            this.$nextTick(() => {
                // Sur téléphone, un doigt fait défiler la page : la carte se déplace avec ses boutons et en pinçant
                map = createMap(this.$refs.map, { draggable: !L.Browser.mobile });
                days.forEach(({ day, label, points }, index) => {
                    const route = L.polyline(points.map(toLatLng), {
                        className: 'course-route',
                        weight: 4,
                        dashArray: index % 2 === 1 ? '10 8' : null,
                    }).addTo(map);
                    routes.set(day, route);
                    points.forEach((point, pointIndex) => {
                        // Le premier point d'un jour est la fin du précédent : sa pastille est déjà posée
                        if (index > 0 && pointIndex === 0) {
                            return;
                        }
                        const marker = L.marker(toLatLng(point), { icon: numberedIcon(point.number), title: point.name })
                            .bindPopup(() => textPopup(point.name, label))
                            .addTo(map);
                        markers.push({ marker, number: point.number });
                    });
                });
                fitPoints(map, days.flatMap(({ points }) => points));
            });
        },

        showDay(day) {
            this.selectedDay = day;
            routes.forEach((route, routeDay) =>
                route.setStyle({ opacity: routeDay === day ? 1 : 0.35, weight: routeDay === day ? 6 : 4 }),
            );
            // Les points du jour, y compris son départ, qui est le dernier point de la veille
            const { points } = days.find((courseDay) => courseDay.day === day);
            const numbers = new Set(points.map((point) => point.number));
            markers.forEach(({ marker, number }) => marker.setOpacity(numbers.has(number) ? 1 : 0.55));
            fitPoints(map, points, true);
            this.$refs.map.scrollIntoView({ block: 'nearest', behavior: prefersReducedMotion ? 'auto' : 'smooth' });
        },

        showAll() {
            this.selectedDay = null;
            routes.forEach((route) => route.setStyle({ opacity: 1, weight: 4 }));
            markers.forEach(({ marker }) => marker.setOpacity(1));
            fitPoints(map, days.flatMap(({ points }) => points), true);
        },
    };
});

/**
 * Tracé du parcours dans le formulaire d'une sortie : on ajoute un point en touchant la carte ou en cherchant un lieu,
 * puis on le nomme, on choisit son jour et on le range. Les champs envoyés suivent l'ordre de la liste.
 *
 * @param {{points: Array<{name: string, day: number|string, latitude: number|string, longitude: number|string}>, startInput: string, endInput: string}} options
 */
Alpine.data('courseInput', ({ points, startInput, endInput }) => {
    let map;
    let layer;
    let nextKey = 1;
    const dateFormat = new Intl.DateTimeFormat('fr-FR', { weekday: 'long', day: 'numeric', month: 'long' });

    return {
        points: (points ?? []).map((point) => ({
            key: nextKey++,
            name: point.name ?? '',
            day: Number(point.day) || 1,
            latitude: Number(point.latitude),
            longitude: Number(point.longitude),
        })),
        startDate: null,
        dayCount: 1,
        relocatingKey: null,
        query: '',
        results: [],
        searching: false,
        searchMessage: '',

        init() {
            const start = document.getElementById(startInput);
            const end = document.getElementById(endInput);
            const readDates = () => {
                const [startDate, endDate] = [start?.value, end?.value].map((value) =>
                    value ? new Date(`${value}T12:00:00`) : null,
                );
                this.startDate = startDate;
                this.dayCount =
                    startDate && endDate && endDate >= startDate
                        ? Math.round((endDate - startDate) / 86400000) + 1
                        : 1;
            };
            readDates();
            start?.addEventListener('change', readDates);
            end?.addEventListener('change', readDates);

            this.$nextTick(() => {
                map = createMap(this.$refs.map, { draggable: true });
                layer = L.layerGroup().addTo(map);
                map.on('click', (event) => this.placeAt(event.latlng));
                fitPoints(map, this.points);
                Alpine.effect(() => this.draw(this.points.map((point) => [point.key, point.latitude, point.longitude])));
            });
        },

        draw() {
            layer.clearLayers();
            L.polyline(this.points.map(toLatLng), { className: 'course-route', weight: 3 }).addTo(layer);
            this.points.forEach((point, index) => {
                L.marker(toLatLng(point), {
                    icon: numberedIcon(index + 1),
                    draggable: true,
                    title: point.name,
                    keyboard: false,
                })
                    .on('dragend', (event) => {
                        const { lat, lng } = event.target.getLatLng();
                        Object.assign(this.find(point.key), { latitude: lat, longitude: lng });
                    })
                    .addTo(layer);
            });
        },

        get days() {
            const highestDay = Math.max(this.dayCount, ...this.points.map((point) => point.day));

            return Array.from({ length: highestDay }, (_, index) => index + 1);
        },

        dayLabel(day) {
            if (!this.startDate) {
                return `Jour ${day}`;
            }
            const date = new Date(this.startDate);
            date.setDate(date.getDate() + day - 1);

            return `Jour ${day} · ${dateFormat.format(date)}`;
        },

        startsDay(index) {
            return index === 0 || this.points[index - 1].day !== this.points[index].day;
        },

        find(key) {
            return this.points.find((point) => point.key === key);
        },

        // Un nouveau point va à la fin, le même jour que le dernier
        addPoint(latitude, longitude, name = '') {
            const lastDay = this.points.at(-1)?.day ?? 1;
            this.points.push({ key: nextKey++, name, day: lastDay, latitude, longitude });
        },

        placeAt({ lat, lng }) {
            if (this.relocatingKey !== null) {
                Object.assign(this.find(this.relocatingKey), { latitude: lat, longitude: lng });
                this.relocatingKey = null;
            } else {
                this.addPoint(lat, lng);
            }
        },

        relocatingName() {
            return this.find(this.relocatingKey)?.name || 'ce point';
        },

        setDay(key, day) {
            this.find(key).day = Number(day);
            // Tri stable : le point rejoint son jour, les autres gardent leur ordre
            this.points = this.points
                .map((point, index) => ({ point, index }))
                .sort((a, b) => a.point.day - b.point.day || a.index - b.index)
                .map(({ point }) => point);
        },

        // Monter le premier point d'un jour le fait passer à la fin du jour précédent, sans changer de place
        moveUp(index) {
            const [previous, point] = [this.points[index - 1], this.points[index]];
            if (previous.day !== point.day) {
                point.day = previous.day;
            } else {
                this.points.splice(index - 1, 2, point, previous);
            }
        },

        moveDown(index) {
            const [point, next] = [this.points[index], this.points[index + 1]];
            if (next.day !== point.day) {
                point.day = next.day;
            } else {
                this.points.splice(index, 2, next, point);
            }
        },

        remove(index) {
            const [removed] = this.points.splice(index, 1);
            if (removed.key === this.relocatingKey) {
                this.relocatingKey = null;
            }
        },

        // Recherche d'un lieu par son nom avec Nominatim (OpenStreetMap), seulement à la demande
        async search() {
            const query = this.query.trim();
            if (!query || this.searching) {
                return;
            }
            this.searching = true;
            this.searchMessage = '';
            this.results = [];
            try {
                const url = new URL('https://nominatim.openstreetmap.org/search');
                url.search = new URLSearchParams({ q: query, format: 'jsonv2', limit: '5', 'accept-language': 'fr' });
                const response = await fetch(url);
                if (!response.ok) {
                    throw new Error(response.statusText);
                }
                this.results = (await response.json()).map((place) => ({
                    id: place.place_id,
                    name: place.name || place.display_name.split(',')[0],
                    label: place.display_name,
                    latitude: Number(place.lat),
                    longitude: Number(place.lon),
                }));
                if (this.results.length === 0) {
                    this.searchMessage = 'Aucun lieu trouvé. Essayez un autre nom, ou touchez la carte.';
                }
            } catch {
                this.searchMessage = 'La recherche n\'a pas abouti. Réessayez, ou touchez la carte.';
            } finally {
                this.searching = false;
            }
        },

        addResult(result) {
            this.addPoint(result.latitude, result.longitude, result.name.slice(0, 100));
            this.results = [];
            this.query = '';
            map.setView([result.latitude, result.longitude], Math.max(map.getZoom(), 11), {
                animate: !prefersReducedMotion,
            });
        },
    };
});

for (const element of document.querySelectorAll('[data-course-component]')) {
    element.removeAttribute('x-ignore');
    delete element._x_ignore;
    Alpine.initTree(element);
}
