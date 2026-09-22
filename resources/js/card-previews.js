import { deferPreviews } from './deferred-previews.js';

deferPreviews(
    document.querySelectorAll('[data-card-preview-src]'),
    'data-card-preview-src',
    '[data-card-preview-fallback]',
);
