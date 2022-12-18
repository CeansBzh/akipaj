import './bootstrap';

import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse'

window.Alpine = Alpine;
Alpine.plugin(collapse)
Alpine.start();

import.meta.glob([
    '../images/**',
]);

import Editor from '@toast-ui/editor'
import '@toast-ui/editor/dist/toastui-editor.css';

window.editor = new Editor({
    el: document.querySelector('#editor'),
    height: '500px',
    initialEditType: 'markdown',
    previewStyle: 'vertical'
});
