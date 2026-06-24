import Alpine from 'alpinejs';
import courtBrowser from './courts/court-browser';
import reviewsPanel from './reviews/reviews-panel';
import './notifications';

Alpine.data('courtBrowser', courtBrowser);
Alpine.data('reviewsPanel', reviewsPanel);

window.Alpine = Alpine;
Alpine.start();
