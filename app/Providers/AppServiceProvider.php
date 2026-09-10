<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // @vasset('css/foo.css') -> asset URL with a ?v=<file mtime> cache-buster
        // so browsers and the CDN pick up changes without a hard refresh.
        Blade::directive('vasset', function ($expression) {
            return "<?php
                \$__vassetPath = {$expression};
                \$__vassetFile = public_path(\$__vassetPath);
                echo e(asset(\$__vassetPath) . (is_file(\$__vassetFile) ? '?v=' . filemtime(\$__vassetFile) : ''));
            ?>";
        });
    }
}
