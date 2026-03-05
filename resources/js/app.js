import './bootstrap';

import Alpine from 'alpinejs';
import * as bootstrap from 'bootstrap';
import galleryComponent from './gallery';

window.bootstrap = bootstrap;
window.Alpine = Alpine;

Alpine.data('galleryComponent', galleryComponent);

Alpine.start();
