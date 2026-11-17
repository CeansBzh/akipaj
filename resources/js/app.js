import './bootstrap';

// Livewire bundles Alpine (with the collapse plugin) and exposes window.Livewire and window.Alpine
import { Livewire, Alpine } from '../../vendor/livewire/livewire/dist/livewire.esm';
import mainNavigation from './navigation';
import { albumCombobox, uploaderSelect } from './gallery-filters';

Alpine.data('mainNavigation', mainNavigation);
Alpine.data('albumCombobox', albumCombobox);
Alpine.data('uploaderSelect', uploaderSelect);

Livewire.start();

// Static images referenced in Blade with Vite::asset(): eager URL imports so Vite emits them in the manifest
import.meta.glob(['../images/**', '../views/**/images/**'], { eager: true, query: '?url', import: 'default' });
