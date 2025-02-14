<?php

use DigitalNode\FormMaker\Http\Middleware\ApplicationPasswordAuthMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::middleware(ApplicationPasswordAuthMiddleware::class)
    ->group(function(){
        Route::get('/forms', function(Request $request){
            $data = $request->validate([
                'field_name' => 'required',
                'location' => 'required',
                'taxonomy' => 'required_if:location,term_option,taxonomy',
                'id' => 'required_if:location,term_option,taxonomy,page'
            ]);

            $form_key = match($data['location']) {
                'term_option' => sprintf("%s_term_option_%s_%s", $data['field_name'], $data['taxonomy'], $data['id']),
                'page' => sprintf("%s_page_%s", $data['field_name'], $data['id']),
                'taxonomy' => sprintf("%s_term_%s", $data['field_name'], $data['id']),
                'postType' => sprintf("%s_%s", $data['field_name'], $data['id']),
                default => $data['field_name']
            };

            $form = DB::table('form_submissions')
              ->where('form_key', $form_key)
              ->get()
              ->map(fn($entry) => json_decode($entry->form_content, true))
              ->first();

            return response()->json($form);
    })->prefix('larafields');
});
