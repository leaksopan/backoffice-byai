<?php

namespace Modules\Settings\Services;

use App\Models\AppSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BrandingSettingsService
{
    public function save(Request $request, array $validated, ?int $userId): void
    {
        AppSetting::putValue('app.name', $validated['app_name'], 'string', 'app', $userId);
        AppSetting::putValue('app.tagline', $validated['tagline'] ?? null, 'string', 'app', $userId);

        $this->syncFileSetting($request, 'logo_light', 'branding.logo_light', $userId);
        $this->syncFileSetting($request, 'logo_dark', 'branding.logo_dark', $userId);
        $this->syncFileSetting($request, 'favicon', 'branding.favicon', $userId);
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
