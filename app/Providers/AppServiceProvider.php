<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Constant;
use App\AppText;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        date_default_timezone_set('UTC');

        // Load default constants.
        $constants = Constant::all();

        if (!empty($constants) && !$constants->isEmpty()) {
            foreach ($constants as $constant) {
                if (empty($constant->key) || empty($constant->value)) {
                    continue;
                }

                if (!defined(strtoupper($constant->key))) {
                    define(strtoupper(trim($constant->key)), $constant->value);
                }
            }
        }
        
        $appTexts = AppText::all();
        if (!empty($appTexts) && !$appTexts->isEmpty()) {
            foreach ($appTexts as $appText) {
                if (empty($appText->key)) {
                    continue;
                }

                if (!defined(strtoupper($appText->key))) {
                    $value = empty($appText->show_text) ? $appText->english_text : $appText->show_text;

                    define(strtoupper(trim($appText->key)), $value);
                }
            }
        }
    }
}
