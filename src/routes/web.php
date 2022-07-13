<?php

use Illuminate\Support\Facades\Route;
use MAlsafadi\LaravelQueue\Facades\LaravelQueue;

if( !function_exists('getLQViewRows') ) {
    function getLQViewRows($rows, $title = "", $return_as_array = false)
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

        $args = [
            'title' => trim($title),
            'total' => count($rows),
            'rows' => $rows,
        ];

        return $return_as_array ? $args : view("laravel-queue::home", $args);
    }
}

Route::get(
    "/",
    $_LQGetPendingQueue = function() {
        $rows = LaravelQueue::get(null, null);

        return getLQViewRows($rows, "Pending List");
    }
);

Route::get(
    "success",
    $_LQGetSuccessQueue = function() {
        $rows = LaravelQueue::get(null, true);

        return getLQViewRows($rows, "Success List");
    }
)->name('.success');

Route::get(
    "failed",
    $_LQGetFailedQueue = function() {
        $rows = LaravelQueue::get(null, false);

        return getLQViewRows($rows, "Failed List");
    }
)->name('.failed');

Route::get("all", function() use ($_LQGetPendingQueue, $_LQGetSuccessQueue, $_LQGetFailedQueue) {
    $data = [];
    $data[] = getLQViewRows(LaravelQueue::get(null, null), "Pending List", true);
    $data[] = getLQViewRows(LaravelQueue::get(null, true), "Success List", true);
    $data[] = getLQViewRows(LaravelQueue::get(null, false), "Failed List", true);

    return view("laravel-queue::all", [
        'title' => "Laravel Queue",
        'total' => collect($data)->map(fn($a) => count(data_get($a, 'rows') ?: []))->sum(),
        'rows' => $data,
    ]);
})->name('.all');
