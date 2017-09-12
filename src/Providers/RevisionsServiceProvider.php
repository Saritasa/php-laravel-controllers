<?php

namespace Saritasa\Laravel\Controllers\Providers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;

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
