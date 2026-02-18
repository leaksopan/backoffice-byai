<?php

return [
    'name' => 'CostCenterManagement',
    
    /**
     * Budget threshold percentage untuk warning notification
     * Default: 80% dari budget amount
     */
    'budget_threshold' => env('BUDGET_THRESHOLD', 80.0),
];
