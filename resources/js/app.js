import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

import { registerSW } from 'virtual:pwa-register'

registerSW({
    immediate: true,
})