<?php

namespace DigitalNode\Larafields\Actions;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GetFormAction
{
    /**
     * Execute the action to get form data.
     *
     * @param Request $request
     * @return array|null
     */
    public function execute(Request $request)
    {
        $data = $request->validate([
            'object_id'   => 'nullable|required_without_all:object_name,field_key',
            'object_name' => 'nullable|required_without_all:object_id,field_key',
            'field_key'  => 'nullable|required_without_all:object_id,object_name',
        ]);

        $query = DB::table('larafields')
            ->when(isset($data['field_key']), function ($query) use ($data) {
                return $query->where('field_key', $data['field_key']);
            })
            ->when(isset($data['object_name']), function ($query) use ($data) {
                return $query->where('object_name', $data['object_name']);
            })
            ->when(isset($data['object_id']), function ($query) use ($data) {
                return $query->where('object_id', $data['object_id']);
            });

        return $query
            ->get()
            ->when(
                isset($data['field_key']),
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
