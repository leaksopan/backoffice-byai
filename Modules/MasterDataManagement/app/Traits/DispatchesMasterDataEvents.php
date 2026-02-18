<?php

namespace Modules\MasterDataManagement\Traits;

use Modules\MasterDataManagement\Events\MasterDataCreated;
use Modules\MasterDataManagement\Events\MasterDataDeleted;
use Modules\MasterDataManagement\Events\MasterDataUpdated;

trait DispatchesMasterDataEvents
{
    protected static function bootDispatchesMasterDataEvents(): void
    {
        static::created(function ($model) {
            MasterDataCreated::dispatch(
                $model->getEntityType(),
                $model->id,
                $model->toArray(),
                auth()->id()
            );
        });

        static::updated(function ($model) {
            $changedFields = array_keys($model->getDirty());
            $oldValues = [];
            $newValues = [];

            foreach ($changedFields as $field) {
                $oldValues[$field] = $model->getOriginal($field);
                $newValues[$field] = $model->$field;
            }

            if (!empty($changedFields)) {
                MasterDataUpdated::dispatch(
                    $model->getEntityType(),
                    $model->id,
                    $changedFields,
                    $oldValues,
                    $newValues,
                    auth()->id()
                );
            }
        });

        static::deleted(function ($model) {
            MasterDataDeleted::dispatch(
                $model->getEntityType(),
                $model->id,
                $model->toArray(),
                auth()->id()
            );
        });
    }

    abstract public function getEntityType(): string;
}
