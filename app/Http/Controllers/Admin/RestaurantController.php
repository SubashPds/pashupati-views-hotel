<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class RestaurantController extends Controller
{
    public function index()
    {
        $restaurant = Restaurant::with('images')->find(1) ?? new Restaurant;

        return view('admin.restaurant.form', compact('restaurant'));
    }

    public function update(Request $request)
    {
        $imageRules = ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'];
        $rules = [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'overview' => 'nullable|string|max:10000',
            'cuisines_raw' => 'nullable|string|max:2000',
            'featured_dishes_raw' => 'nullable|string|max:5000',
            'is_active' => 'required|boolean',
            'menu_image' => $imageRules,
            'remove_menu_image' => 'nullable|boolean',
            'gallery_images' => 'nullable|array|max:20',
            'gallery_images.*' => ['required', ...array_slice($imageRules, 1)],
            'images' => 'nullable|array',
            'images.*' => 'array:id,caption,replacement,remove',
            'images.*.id' => ['required', 'integer', 'distinct', Rule::exists('restaurant_images', 'id')->where('restaurant_id', 1)],
            'images.*.caption' => 'nullable|string|max:255',
            'images.*.replacement' => $imageRules,
            'images.*.remove' => 'nullable|boolean',
        ];
        foreach (['opening' => 'closing', 'breakfast_start' => 'breakfast_end', 'lunch_start' => 'lunch_end', 'dinner_start' => 'dinner_end'] as $start => $end) {
            $rules[$start.'_time'] = "nullable|required_with:{$end}_time|date_format:H:i";
            $rules[$end.'_time'] = "nullable|required_with:{$start}_time|date_format:H:i";
        }
        $validated = $request->validate($rules);
        if ($request->boolean('remove_menu_image') && $request->hasFile('menu_image')) {
            throw ValidationException::withMessages(['menu_image' => 'Choose either a replacement menu or removal.']);
        }
        foreach ($validated['images'] ?? [] as $index => $image) {
            if (! empty($image['remove']) && ! empty($image['replacement'])) {
                throw ValidationException::withMessages(["images.$index.replacement" => 'Choose either replacement or removal for this image.']);
            }
        }

        $data = collect($validated)->except(['cuisines_raw', 'featured_dishes_raw', 'menu_image', 'remove_menu_image', 'gallery_images', 'images'])->all();
        $data['cuisines'] = $this->parseLines($request->input('cuisines_raw'));
        $data['featured_dishes'] = $this->parseLines($request->input('featured_dishes_raw'));
        $newPaths = [];
        $oldPaths = [];
        $store = function ($file, string $directory) use (&$newPaths): string {
            $path = $file->store($directory, 'public');
            if (! $path) {
                throw new \RuntimeException('The restaurant image could not be stored.');
            }
            $newPaths[] = $path;

            return $path;
        };

        try {
            DB::transaction(function () use ($request, $validated, $data, $store, &$oldPaths) {
                $restaurant = Restaurant::lockForUpdate()->find(1) ?? new Restaurant;
                $restaurant->id = 1;
                $restaurant->fill($data);
                if ($request->hasFile('menu_image') || $request->boolean('remove_menu_image')) {
                    if ($restaurant->menu_image) {
                        $oldPaths[] = $restaurant->menu_image;
                    }
                    $restaurant->menu_image = $request->hasFile('menu_image') ? $store($request->file('menu_image'), 'restaurant/menu') : null;
                }
                $restaurant->save();

                foreach ($validated['images'] ?? [] as $input) {
                    $image = $restaurant->images()->findOrFail($input['id']);
                    if (! empty($input['remove'])) {
                        $oldPaths[] = $image->image_path;
                        $image->delete();

                        continue;
                    }
                    if (! empty($input['replacement'])) {
                        $oldPaths[] = $image->image_path;
                        $image->image_path = $store($input['replacement'], 'restaurant/gallery');
                    }
                    $image->caption = $input['caption'] ?? null;
                    $image->save();
                }
                $order = $restaurant->images()->max('sort_order') ?? 0;
                foreach ($request->file('gallery_images', []) as $file) {
                    $restaurant->images()->create(['image_path' => $store($file, 'restaurant/gallery'), 'sort_order' => ++$order]);
                }
            });
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($newPaths);
            throw $exception;
        }
        Storage::disk('public')->delete($oldPaths);

        return redirect()->route('admin.restaurant.index')->with('success', 'Restaurant updated successfully.');
    }

    private function parseLines(?string $raw): array
    {
        return array_values(array_filter(array_map('trim', explode("\n", $raw ?? '')), fn ($line) => $line !== ''));
    }
}
