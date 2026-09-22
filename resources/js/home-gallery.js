import { deferPreviews } from './deferred-previews.js';

deferPreviews(
    document.querySelectorAll('[data-home-gallery] [data-home-gallery-src]'),
    'data-home-gallery-src',
    '[data-gallery-fallback]',
);
