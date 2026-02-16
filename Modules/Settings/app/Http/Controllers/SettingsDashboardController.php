<?php

namespace Modules\Settings\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Modules\Settings\Http\Requests\UpdateBrandingRequest;
use Modules\Settings\Services\BrandingSettingsService;

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

    public function updateBranding(UpdateBrandingRequest $request, BrandingSettingsService $brandingSettingsService): RedirectResponse
    {
        $validated = $request->validated();
        $userId = $request->user()?->id;
        $brandingSettingsService->save($request, $validated, $userId);

        return redirect()
            ->route('settings.branding')
            ->with('status', 'Branding settings saved successfully.');
    }
}
