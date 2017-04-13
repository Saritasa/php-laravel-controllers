<?php

namespace Saritasa\Laravel\Controllers\Providers;

use Html;
use Illuminate\Support\ServiceProvider;

class MacroServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->registerScriptLinkMacro();
        $this->registerStyleLinkMacro();
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
    }

    /**
     * Html macro for generate html tag for include javascript file
     * example of usage: {!! HTML::scriptLink('/assets/js/bower.js') !!}
     */
    public function registerScriptLinkMacro()
    {
        HTML::macro('scriptLink', function ($src, $hasMinified = true, $hasVersioning = true) {
            $revisions = config('revision.files');

            if ($hasMinified && !config('app.debug')) {
                $src = str_replace('.js', '.min.js', $src);
            }
            if ($hasVersioning) {
                $src .= (isset($revisions[ltrim($src, '/')]) ? '?' . $revisions[ltrim($src, '/')] : '');
            }
            return "<script src=\"$src\"></script>";
        });
    }

    /**
     * Html macro for generate html tag for include css file
     * example of usage: {!! HTML::styleLink('/assets/css/bower.css') !!}
     */
    public function registerStyleLinkMacro()
    {
        HTML::macro('styleLink', function ($href, $hasMinified = true, $hasVersioning = true) {
            $revisions = config('revision.files');

            if ($hasMinified && !config('app.debug')) {
                $href = str_replace('.css', '.min.css', $href);
            }
            if ($hasVersioning) {
                $href .= (isset($revisions[ltrim($href, '/')]) ? '?' . $revisions[ltrim($href, '/')] : '');
            }
            return "<link rel=\"stylesheet\" href=\"$href\">";
        });
    }
}
