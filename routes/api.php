<?php

use DigitalNode\Larafields\Http\Middleware\ApplicationPasswordAuth;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::middleware(ApplicationPasswordAuth::class)
    ->group(function () {
        // TODO: fix this.
        Route::get('/forms', function (Request $request) {
            $data = $request->validate([
                'object_id'   => 'nullable|required_without_all:object_name,field_key',
                'object_name' => 'nullable|required_without_all:object_id,field_key',
                'field_key'  => 'nullable|required_without_all:object_id,object_name',
            ]);

            $query = DB::table('larafields');

            if (isset($data['field_key'])) {
                $query->where('field_key', $data['field_key']);
            }

            if (isset($data['object_name'])) {
                $query->where('object_name', $data['object_name']);
            }

            if (isset($data['object_id'])) {
                $query->where('object_id', $data['object_id']);
            }

            $result = $query
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

            return response()->json($result);
        })->prefix('larafields');

        Route::post('/forms', function (Request $request) {
            $data = $request->validate([
                'field_key' => 'required',
                'field_value' => 'required',
                'object_id' => 'required',
                'object_name' => 'required',
            ]);

            DB::table('larafields')
                ->where('field_key', $data['field_key'])
                ->where('object_id', $data['object_id'])
                ->where('object_name', $data['object_name'])
                ->update([
                    'field_value' => json_encode($data['field_value']),
                ]);

            return response()->json(['status' => 'ok']);
        })->prefix('larafields');
    });
