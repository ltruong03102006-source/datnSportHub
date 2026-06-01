import Alpine from 'alpinejs';
import courtBrowser from './courts/court-browser';

Alpine.data('courtBrowser', courtBrowser);

window.Alpine = Alpine;
Alpine.start();
