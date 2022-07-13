<?php

use Illuminate\Support\Facades\Route;
use MAlsafadi\LaravelQueue\Facades\LaravelQueue;

if( !function_exists('getLQViewRows') ) {
    function getLQViewRows($rows)
    {
        $rows = collect($rows);

        if( $order = request('order') ) {
            $rows = $rows->sortBy(fn($r) => $r[ $order ] ?? $r[ 'created_at' ] ?? $r[ 'name' ], SORT_REGULAR, request('dir'));
        }

        if( request()->has('search') && ($s = trim(request('search'))) ) {
            $rows = $rows->filter(function($r) use ($s) {
                foreach( array_values($r) as $_r ) {
                    if( str_contains($_r, $s) ) {
                        return true;
                    }
                }

                return false;
            });
        }

        $rows = $rows->all();

        return view("laravel-queue::home", [
            'rows' => $rows,
        ]);
    }
}

Route::get("/", function() {
    $rows = LaravelQueue::get(null, null);

    return getLQViewRows($rows);
});

Route::get("success", function() {
    $rows = LaravelQueue::get(null, false);

    return getLQViewRows($rows);
})->name('.success');

Route::get("failed", function() {
    $rows = LaravelQueue::get(null, true);

    return getLQViewRows($rows);
})->name('.failed');
