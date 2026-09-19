<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Promotion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
        if ($file = $request->file('offer_image')) $data['image_path'] = $file->store('offers', 'public');
        Promotion::create($data);
        return redirect()->route('admin.promotions.index')->with('success', 'Promotion created.');
    }

    public function edit(Promotion $promotion)
    {
        return view('admin.promotions.form', compact('promotion'));
    }

    public function update(Request $request, Promotion $promotion)
    {
        $data = $this->validated($request);
        $oldPath = $promotion->image_path;
        if ($file = $request->file('offer_image')) {
            $data['image_path'] = $file->store('offers', 'public');
        } elseif ($request->boolean('remove_offer_image')) {
            $data['image_path'] = null;
        }
        $promotion->update($data);
        if ($oldPath && $promotion->image_path !== $oldPath) Storage::disk('public')->delete($oldPath);
        return redirect()->route('admin.promotions.index')->with('success', 'Promotion updated.');
    }

    public function destroy(Promotion $promotion)
    {
        $path = $promotion->image_path;
        $promotion->delete();
        if ($path) Storage::disk('public')->delete($path);
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
