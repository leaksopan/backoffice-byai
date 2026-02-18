<?php

namespace Modules\MasterDataManagement\Filament\Resources\MdmAssetResource\Widgets;

use Filament\Widgets\Widget;
use Modules\MasterDataManagement\Services\AssetDepreciationService;
use Carbon\Carbon;

class AssetDepreciationWidget extends Widget
{
    protected static string $view = 'masterdatamanagement::filament.widgets.asset-depreciation';

    public $record;

    protected function getViewData(): array
    {
        if (!$this->record || !$this->record->isDepreciable()) {
            return [
                'isDepreciable' => false,
            ];
        }

        $service = new AssetDepreciationService();
        $asOfDate = Carbon::now();

        $monthlyDepreciation = $service->calculateMonthlyDepreciation($this->record);
        $accumulatedDepreciation = $service->calculateAccumulatedDepreciation($this->record, $asOfDate);
        $bookValue = $service->getBookValue($this->record, $asOfDate);

        // Generate depreciation schedule for next 12 months
        $schedule = [];
        for ($i = 0; $i < 12; $i++) {
            $date = Carbon::now()->addMonths($i);
            $schedule[] = [
                'month' => $date->format('M Y'),
                'accumulated' => $service->calculateAccumulatedDepreciation($this->record, $date),
                'book_value' => $service->getBookValue($this->record, $date),
            ];
        }

        return [
            'isDepreciable' => true,
            'monthlyDepreciation' => $monthlyDepreciation,
            'accumulatedDepreciation' => $accumulatedDepreciation,
            'bookValue' => $bookValue,
            'schedule' => $schedule,
            'depreciationMethod' => $this->record->depreciation_method,
            'usefulLifeYears' => $this->record->useful_life_years,
        ];
    }
}
