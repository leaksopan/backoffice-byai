<?php

namespace Modules\CostCenterManagement\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AllocationCompleted
{
    use Dispatchable, SerializesModels;

    public string $batchId;
    public int $totalJournals;
    public float $totalAmount;
    public array $summary;

    public function __construct(string $batchId, int $totalJournals, float $totalAmount, array $summary = [])
    {
        $this->batchId = $batchId;
        $this->totalJournals = $totalJournals;
        $this->totalAmount = $totalAmount;
        $this->summary = $summary;
    }
}
