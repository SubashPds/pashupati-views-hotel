# Restaurant management

The public restaurant page is `/restaurant`. Desktop navigation, mobile navigation, and the footer link to it. Staff manage the hotel's single restaurant at `/admin/restaurant`.

## Setup

Apply migrations with `php artisan migrate` and build assets with `npm run build`. Uploaded images use the existing `public` storage disk; ensure `/storage` is served as it is for room and package images. No restaurant content is seeded.

In **Admin → Restaurant Management**, enter the restaurant details, upload images, select **Publish restaurant content**, and save. Unpublished or unconfigured restaurants show a neutral public availability message without exposing saved content.

## Content and images

- Name, short description, overview, cuisine types, featured dishes, opening hours, and meal timings come from the database.
- Enter cuisines and featured dishes one per line. Paragraphs in the overview are preserved, and all text is escaped on the public page.
- Complete both times in each pair or leave both blank. Earlier end times mean the following day; identical start and end times mean 24 hours. Times use local hotel time.
- The food menu is one uploaded image, displayed without cropping and linked to the original for zooming. There are no menu category, item, or price records.
- Menu and gallery uploads accept JPEG, PNG, and WebP images up to 4 MB each. Add up to 20 new gallery images per save. Gallery images support captions, replacement, and removal.
- Changes, including image removals, take effect on save. Replacement and removal of the same image in one submission are rejected. Validation failures require selecting uploads again.
- Database updates run in a transaction. Replaced files are deleted after successful persistence; failed saves remove newly uploaded files.

The `restaurant` permission controls every Restaurant Management request and sidebar visibility. The migration grants it to existing admin and superadmin roles; superadmins can change role access in Roles & Permissions. Restaurant records and gallery images use the existing creator/updater audit fields.

## Verification

Run `php artisan test --filter=RestaurantTest` in the project's PHP environment with SQLite support. Tests use an isolated in-memory database and fake image storage. Browser checks cover upload previews, gallery replacement, captions, menu access, keyboard navigation, and layouts at 375, 768, 1280, and 1440 pixels.
