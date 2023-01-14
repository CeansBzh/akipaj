import './bootstrap';

import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse'

window.Alpine = Alpine;
Alpine.plugin(collapse)
Alpine.start();

import.meta.glob([
    '../images/**',
    '../views/**/images/**',
]);

import './article-editor';
