<?php

namespace JasperPHP;

use Illuminate\Support\ServiceProvider;
use JasperPHP\core\Instructions;
use JasperPHP\database\TTransaction;

class JasperPHPServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        // No registration needed for simple static library
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // Publish configuration
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/jasperphp.php' => config_path('jasperphp.php'),
            ], 'jasperphp-config');
        }

        // Apply configuration to standalone static properties
        $config = $this->app['config']->get('jasperphp', []);
        if (!empty($config)) {
            \JasperPHP\elements\Report::$defaultFolder = $config['default_folder'] ?? \JasperPHP\elements\Report::$defaultFolder;
            \JasperPHP\elements\Report::$locale = $config['locale'] ?? \JasperPHP\elements\Report::$locale;
            \JasperPHP\elements\Report::$dec_point = $config['dec_point'] ?? \JasperPHP\elements\Report::$dec_point;
            \JasperPHP\elements\Report::$thousands_sep = $config['thousands_sep'] ?? \JasperPHP\elements\Report::$thousands_sep;
        }

        // Octane compatibility: listen to RequestTerminated event
        if ($this->app->bound('events') && class_exists('\Laravel\Octane\Events\RequestTerminated')) {
            $this->app['events']->listen(\Laravel\Octane\Events\RequestTerminated::class, function () {
                Instructions::reset();
                TTransaction::reset();
            });
        }
    }
}
