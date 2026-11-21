import './bootstrap';

// Livewire bundles Alpine (with the collapse plugin) and exposes window.Livewire and window.Alpine
import { Livewire, Alpine } from '../../vendor/livewire/livewire/dist/livewire.esm';
import mainNavigation from './navigation';
import { albumCombobox, uploaderSelect } from './gallery-filters';

Alpine.data('mainNavigation', mainNavigation);
Alpine.data('albumCombobox', albumCombobox);
Alpine.data('uploaderSelect', uploaderSelect);

Livewire.start();

// Images et polices nommées dans Blade par Vite::asset() : importées en adresses pour que Vite les inscrive au manifeste
import.meta.glob(['../images/**', '../views/**/images/**', '../fonts/*.woff2'], { eager: true, query: '?url', import: 'default' });
