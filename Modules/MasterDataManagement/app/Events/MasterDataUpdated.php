<?php

namespace Modules\MasterDataManagement\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MasterDataUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public string $entityType,
        public int $entityId,
        public array $changedFields,
        public array $oldValues,
        public array $newValues,
        public ?int $userId = null
    ) {}

    public function toArray(): array
    {
        return [
            'entity_type' => $this->entityType,
            'entity_id' => $this->entityId,
            'action' => 'updated',
            'changed_fields' => $this->changedFields,
            'old_values' => $this->oldValues,
            'new_values' => $this->newValues,
            'user_id' => $this->userId,
            'timestamp' => now()->toIso8601String(),
        ];
    }
}
