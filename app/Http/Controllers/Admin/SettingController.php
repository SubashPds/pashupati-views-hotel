<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use App\Support\CurrencySettings;
use App\Support\EmailAddresses;
use App\Support\StaySettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SettingController extends Controller
{
    private const MAP_SETTING = [
        'key' => 'contact_map_location',
        'type' => 'text',
        'group' => 'contact',
        'label' => 'Map Location (full address or latitude, longitude)',
        'sort_order' => 5,
    ];

    private const EMAIL_SETTING = [
        'key' => 'contact_email', 'type' => 'textarea', 'group' => 'contact',
        'label' => 'Email addresses (enquiry notifications)', 'sort_order' => 3,
    ];

    public function index()
    {
        $settings = SiteSetting::orderBy('group')->orderBy('sort_order')->get()->groupBy('group');
        $contactSettings = $settings->get('contact', collect());
        if (! $contactSettings->contains('key', 'contact_map_location')) {
            $contactSettings->push(new SiteSetting(self::MAP_SETTING));
        }
        if (!$contactSettings->contains('key', 'contact_email')) $contactSettings->push(new SiteSetting(self::EMAIL_SETTING));
        foreach ($contactSettings as $setting) {
            if ($setting->key === 'contact_email') $setting->fill(['label' => self::EMAIL_SETTING['label'], 'type' => 'textarea']);
        }
        $settings->put('contact', $contactSettings);
        $settings->forget('offers');
        $currencySettings = $settings->get('currency', collect());
        foreach (CurrencySettings::definitions() as $definition) {
            if (!$currencySettings->contains('key', $definition['key'])) $currencySettings->push(new SiteSetting($definition));
        }
        $settings->put('currency', $currencySettings);
        $staySettings = $settings->get('stay', collect());
        foreach (StaySettings::definitions() as $definition) {
            if (!$staySettings->contains('key', $definition['key'])) $staySettings->push(new SiteSetting($definition));
        }
        $settings->put('stay', $staySettings);

        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $stayRules = [];
        foreach (StaySettings::definitions() as $definition) {
            $stayRules[$definition['key']] = $definition['type'] === 'boolean'
                ? 'sometimes|required|boolean'
                : 'sometimes|required|string|max:'.($definition['type'] === 'textarea' ? '3000' : '255');
        }
        $request->validate([
            ...$stayRules,
            'contact_map_location' => 'nullable|string|max:500',
            'contact_email' => ['nullable', 'string', 'max:3000', function ($attribute, $value, $fail) {
                if (!is_string($value)) return;
                if (Validator::make(['emails' => EmailAddresses::parse($value)], ['emails' => 'array|max:20', 'emails.*' => 'required|email:rfc|max:254'])->fails()) {
                    $fail('Enter up to 20 valid email addresses, separated by commas or new lines.');
                }
            }],
            'currency_npr_per_inr' => 'sometimes|required|numeric|between:0.0001,1000000',
            'currency_npr_per_usd' => 'sometimes|required|numeric|between:0.0001,1000000',
        ]);

        if ($request->exists('contact_email')) {
            SiteSetting::updateOrCreate(['key' => 'contact_email'], array_merge(self::EMAIL_SETTING, [
                'value' => implode(', ', EmailAddresses::parse($request->input('contact_email'))),
            ]));
        }

        foreach (array_merge(CurrencySettings::definitions(), StaySettings::definitions()) as $definition) {
            if ($request->exists($definition['key'])) {
                SiteSetting::updateOrCreate(['key' => $definition['key']], array_merge($definition, ['value' => $request->input($definition['key'])]));
            }
        }

        if ($request->exists('contact_map_location')) {
            SiteSetting::updateOrCreate(
                ['key' => 'contact_map_location'],
                array_merge(self::MAP_SETTING, ['value' => $request->input('contact_map_location')]),
            );
        }

        $data = $request->except(array_merge(['_token', '_method', '_settings_tab', 'contact_map_location', 'contact_email'], array_keys(CurrencySettings::defaults()), array_keys(StaySettings::defaults())));

        foreach ($data as $key => $value) {
            SiteSetting::where('group', '!=', 'offers')->where('key', $key)->update(['value' => $value]);
        }

        return back()->with('success', 'Settings saved successfully.')
            ->with('settings_tab', $request->input('_settings_tab'));
    }
}
