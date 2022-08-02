<?php

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use MAlsafadi\LaravelQueue\Facades\LaravelQueue;

defined('AUTO_REFRESH_CACHE_KEY') || define('AUTO_REFRESH_CACHE_KEY', 'laravel-queue-web.auto-refresh');
defined('AUTO_REFRESH_CACHE_TTL') || define('AUTO_REFRESH_CACHE_TTL', 60 * 60 * 1);
defined('FORMAT_DATETIME') || define('FORMAT_DATETIME', false);

if( !function_exists('getLQViewRows') ) {
    /**
     * @param      $rows
     * @param      $title
     * @param      $return_as_array
     * @param bool $is_history
     *
     * @return array|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    function getLQViewRows($rows, $title = "", $return_as_array = false, bool $is_history = false)
    {
        $date_columns = [
            'date',
            'created_at',
            'result_at',
            'history_at',
        ];
        foreach( $rows as &$row ) {
            if( FORMAT_DATETIME && count($date_columns) ) {
                foreach( $date_columns as $date_column ) {
                    if( $row[ $date_column ] ?? null ) {
                        $row[ $date_column ] = Carbon::parse($row[ $date_column ])->format(config('app.datetime_format') ?: 'Y-m-d h:i:s a');
                    }
                }
            }

            foreach( $row as $key => &$value ) {
                if( is_array($value) ) {
                    $value = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                }
            }
            unset($value);
        }
        unset($row);

        $rows = collect($rows);

        if( $order = request('order') ) {
            $rows = $rows->sortBy(fn($r) => $r[ $order ] ?? $r[ 'created_at' ] ?? $r[ 'name' ], SORT_REGULAR, request('dir'));
        } else {
            $rows = $rows->sortByDesc(fn($r) => $r[ 'history_at' ] ?? $r[ 'created_at' ] ?? $r[ 'name' ], SORT_REGULAR);
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
            'history' => $is_history,
            'auto_refresh' => Cache::remember(AUTO_REFRESH_CACHE_KEY, AUTO_REFRESH_CACHE_TTL, fn() => false),
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

Route::get(
    "history",
    $_LQGetFailedQueue = function() {
        $rows = LaravelQueue::getHistory(null);

        return getLQViewRows($rows, "History List", false, true);
    }
)->name('.history');

Route::get("all", function() use ($_LQGetPendingQueue, $_LQGetSuccessQueue, $_LQGetFailedQueue) {
    $data = [];
    $data[] = getLQViewRows(LaravelQueue::get(null, null), "Pending List", true);
    $data[] = getLQViewRows(LaravelQueue::get(null, true), "Success List", true);
    $data[] = getLQViewRows(LaravelQueue::get(null, false), "Failed List", true);
    $data[] = getLQViewRows(LaravelQueue::getHistory(null), "History List", true, true);

    return view("laravel-queue::all", [
        'title' => "Laravel Queue",
        'total' => collect($data)->map(fn($a) => count(data_get($a, 'rows') ?: []))->sum(),
        'rows' => $data,
        'auto_refresh' => Cache::remember(AUTO_REFRESH_CACHE_KEY, AUTO_REFRESH_CACHE_TTL, fn() => false),
    ]);
})->name('.all');

Route::get("auto-refresh", function() {
    $status = Cache::remember(AUTO_REFRESH_CACHE_KEY, AUTO_REFRESH_CACHE_TTL, fn() => false);
    $status = !$status;
    Cache::put(AUTO_REFRESH_CACHE_KEY, $status, AUTO_REFRESH_CACHE_TTL);

    return back();
})->name('.toggle_auto_refresh');
