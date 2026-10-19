import './bootstrap';

// Livewire bundles Alpine (with the collapse plugin) and exposes window.Livewire and window.Alpine
import { Livewire } from '../../vendor/livewire/livewire/dist/livewire.esm';

Livewire.start();

// Static images referenced in Blade with Vite::asset(): eager URL imports so Vite emits them in the manifest
import.meta.glob(['../images/**', '../views/**/images/**'], { eager: true, query: '?url', import: 'default' });

import './article-editor';
