<?php

namespace DigitalNode\Larafields\Actions;

use DigitalNode\Larafields\DTOs\GetFormDTO;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GetFormAction
{
    public function execute(GetFormDTO $dto)
    {
        if (! $dto->isValid()) {
            return null;
        }

        $query = DB::table('larafields')
            ->when($dto->fieldKey, function ($query) use ($dto) {
                return $query->where('field_key', $dto->fieldKey);
            })
            ->when($dto->objectName, function ($query) use ($dto) {
                return $query->where('object_name', $dto->objectName);
            })
            ->when($dto->objectId, function ($query) use ($dto) {
                return $query->where('object_id', $dto->objectId);
            });

        return $query
            ->get()
            ->when(
                $dto->fieldKey,
                function (Collection $collection) {
                    return $collection
                        ->map(fn ($entry) => (array) $entry)
                        ->map(fn ($entry) => array_merge(
                            Arr::except($entry, ['id', 'field_value', 'field_key']),
                            ['data' => json_decode($entry['field_value'], true)]
                        ))->first();
                },
                function (Collection $collection) {
                    return $collection
                        ->map(fn ($entry) => (array) $entry)
                        ->map(fn ($entry) => array_merge(
                            Arr::except($entry, ['id', 'field_value']),
                            ['data' => json_decode($entry['field_value'], true)]
                        ))->all();
                });
    }
}
