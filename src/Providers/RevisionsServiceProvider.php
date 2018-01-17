<?php

namespace Saritasa\Laravel\Controllers\Providers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;

/*
 * @deprecated 2.0.5 Use Laravel Mix recommended https://laravel.com/docs/mix
 */
class RevisionsServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $filename = public_path() . '/assets/sum.md5';
        $revisions = is_file($filename) ? file($filename) : [];
        $files = [];
        foreach ($revisions as $file) {
            $file = explode('  ', $file);
            $files[str_replace(public_path() . '/', '', trim($file[1]))] = trim($file[0]);
        }
        Config::set('revision.files', $files);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
    }
}
