<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    private const MAP_SETTING = [
        'key' => 'contact_map_location',
        'type' => 'text',
        'group' => 'contact',
        'label' => 'Map Location (full address or latitude, longitude)',
        'sort_order' => 5,
    ];

    public function index()
    {
        $settings = SiteSetting::orderBy('group')->orderBy('sort_order')->get()->groupBy('group');
        $contactSettings = $settings->get('contact', collect());
        if (! $contactSettings->contains('key', 'contact_map_location')) {
            $contactSettings->push(new SiteSetting(self::MAP_SETTING));
        }
        $settings->put('contact', $contactSettings);
        $settings->forget('offers');

        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate(['contact_map_location' => 'nullable|string|max:500']);

        if ($request->exists('contact_map_location')) {
            SiteSetting::updateOrCreate(
                ['key' => 'contact_map_location'],
                array_merge(self::MAP_SETTING, ['value' => $request->input('contact_map_location')]),
            );
        }

        $data = $request->except(['_token', '_method', '_settings_tab', 'contact_map_location']);

        foreach ($data as $key => $value) {
            SiteSetting::where('group', '!=', 'offers')->where('key', $key)->update(['value' => $value]);
        }

        return back()->with('success', 'Settings saved successfully.')
            ->with('settings_tab', $request->input('_settings_tab'));
    }
}
