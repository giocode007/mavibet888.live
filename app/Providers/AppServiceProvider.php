<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

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
        Model::unguard();
        Schema::defaultStringLength(191);
        Blade::directive('money', function ($amount) {
            return "<?php echo '$' . number_format($amount); ?>";
        });
        Blade::directive('comma', function ($amount) {
            return "<?php echo number_format($amount); ?>";
        });
        Blade::directive('commission', function ($amount) {
            return "<?php echo number_format($amount, 2); ?>";
        });
        Blade::directive('payout', function ($amount) {
            return "<?php echo number_format($amount, 1); ?>";
        });
    }
}
