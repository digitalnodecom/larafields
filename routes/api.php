<?php

use DigitalNode\Larafields\Http\Middleware\ApplicationPasswordAuthMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::middleware(ApplicationPasswordAuthMiddleware::class)
    ->group(function () {
        // TODO: fix this.
        Route::get('/forms', function (Request $request) {
            $data = $request->validate([
                'form_name' => 'required',
                'location' => 'sometimes',
                'taxonomy' => 'required_if:location,term_option,taxonomy',
                'id' => 'required_if:location,term_option,taxonomy,page',
            ]);

            $query = DB::table('larafields')
                ->where('form_key', $data['form_name']);

            if (isset($data['location'])) {
                $location_meta = match ($data['location']) {
                    'term_option' => sprintf('term_option_%s_%s', $data['taxonomy'], $data['id']),
                    'page' => sprintf('page_%s', $data['id']),
                    'taxonomy' => sprintf('term_%s', $data['id']),
                    'postType' => sprintf('%s', $data['id'])
                };

                $query->where('form_location_meta', $location_meta);
            }

            $result = $query
                ->get()
                ->when(
                    isset($data['location']),
                    function (Collection $collection) {
                        return $collection
                            ->map(fn ($entry) => json_decode($entry->form_content, true))
                            ->first();
                    },
                    function (Collection $collection) {
                        return $collection
                            ->mapWithKeys(fn ($entry) => [$entry->form_location_meta => json_decode($entry->form_content, true)])
                            ->all();
                    });

            return response()->json($result);
        })->prefix('larafields');
    });
