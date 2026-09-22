<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Promotion;
use App\Services\MediaFiles;
use Illuminate\Http\Request;
use RuntimeException;

class PromotionController extends Controller
{
    public function index()
    {
        return view('admin.promotions.index', ['promotions' => Promotion::orderBy('sort_order')->orderBy('id')->get()]);
    }

    public function create()
    {
        return view('admin.promotions.form', ['promotion' => new Promotion]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        MediaFiles::transaction(function (MediaFiles $files) use ($request, $data) {
            if ($file = $request->file('offer_image')) {
                $data['image_path'] = $files->store($file, 'offers');
            }
            if (! (new Promotion($data))->save()) {
                throw new RuntimeException('The promotion could not be saved.');
            }
        });
        return redirect()->route('admin.promotions.index')->with('success', 'Promotion created.');
    }

    public function edit(Promotion $promotion)
    {
        return view('admin.promotions.form', compact('promotion'));
    }

    public function update(Request $request, Promotion $promotion)
    {
        $data = $this->validated($request);
        MediaFiles::transaction(function (MediaFiles $files) use ($request, $promotion, $data) {
            $promotion = Promotion::lockForUpdate()->findOrFail($promotion->id);
            if ($file = $request->file('offer_image')) {
                $data['image_path'] = $files->store($file, 'offers');
            } elseif ($request->boolean('remove_offer_image')) {
                $data['image_path'] = null;
            }
            if (array_key_exists('image_path', $data)) {
                $files->deleteAfterCommit($promotion->image_path);
            }
            if (! $promotion->update($data)) {
                throw new RuntimeException('The promotion could not be saved.');
            }
        });
        return redirect()->route('admin.promotions.index')->with('success', 'Promotion updated.');
    }

    public function destroy(Promotion $promotion)
    {
        MediaFiles::transaction(function (MediaFiles $files) use ($promotion) {
            $promotion = Promotion::lockForUpdate()->findOrFail($promotion->id);
            $files->deleteAfterCommit($promotion->image_path);
            if (! $promotion->delete()) {
                throw new RuntimeException('The promotion could not be deleted.');
            }
        });
        return back()->with('success', 'Promotion deleted.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => 'required|string|max:180',
            'label' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:1500',
            'button_text' => 'nullable|string|max:80',
            'button_url' => ['nullable', 'string', 'max:1000', function ($attribute, $value, $fail) {
                if (str_contains($value, '\\') || preg_match('/[\x00-\x20\x7f]/', $value)
                    || ! preg_match('~^(?:https?://[^\s]+|/(?!/)[^\s]*|\#[a-zA-Z][\w-]*)$~i', $value)) {
                    $fail('Use an https:// link, a site path such as /blogs, or a section such as #contact.');
                }
            }],
            'offer_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:6096',
            'image_alt' => 'nullable|string|max:255',
            'remove_offer_image' => 'sometimes|boolean',
            'sort_order' => 'required|integer|min:0|max:100000',
            'is_active' => 'required|boolean',
            'end_date' => 'nullable|date_format:Y-m-d',
        ]);
        unset($data['offer_image'], $data['remove_offer_image']);
        return $data;
    }
}
