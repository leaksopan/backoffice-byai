<?php

namespace Modules\Settings\Http\Controllers;

use App\Models\AppSetting;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingsDashboardController
{
    public function dashboard(): View
    {
        return view('settings::dashboard');
    }

    public function branding(): View
    {
        return view('settings::branding', [
            'form' => [
                'app_name' => setting('app.name', config('app.name')),
                'tagline' => setting('app.tagline'),
                'logo_light' => setting('branding.logo_light'),
                'logo_dark' => setting('branding.logo_dark'),
                'favicon' => setting('branding.favicon'),
            ],
        ]);
    }

    public function updateBranding(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'app_name' => ['required', 'string', 'max:255'],
            'tagline' => ['nullable', 'string', 'max:255'],
            'logo_light' => ['nullable', 'file', 'mimes:jpg,jpeg,png,svg,webp', 'max:2048'],
            'logo_dark' => ['nullable', 'file', 'mimes:jpg,jpeg,png,svg,webp', 'max:2048'],
            'favicon' => ['nullable', 'file', 'mimes:png,ico,svg', 'max:1024'],
        ]);

        $userId = $request->user()?->id;

        AppSetting::putValue('app.name', $validated['app_name'], 'string', 'app', $userId);
        AppSetting::putValue('app.tagline', $validated['tagline'] ?? null, 'string', 'app', $userId);

        $this->syncFileSetting($request, 'logo_light', 'branding.logo_light', $userId);
        $this->syncFileSetting($request, 'logo_dark', 'branding.logo_dark', $userId);
        $this->syncFileSetting($request, 'favicon', 'branding.favicon', $userId);

        return redirect()
            ->route('settings.branding')
            ->with('status', 'Branding settings saved successfully.');
    }

    private function syncFileSetting(Request $request, string $inputName, string $settingKey, ?int $userId): void
    {
        if (! $request->hasFile($inputName)) {
            return;
        }

        $oldPath = setting($settingKey);

        if ($oldPath && Storage::disk('public')->exists($oldPath)) {
            Storage::disk('public')->delete($oldPath);
        }

        $path = $request->file($inputName)->store('branding', 'public');

        AppSetting::putValue($settingKey, $path, 'string', 'branding', $userId);
    }
}
