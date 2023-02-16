<?php

namespace Megaads\EmailSubscriber\Providers;

use Illuminate\Support\ServiceProvider;
use Megaads\EmailSubscriber\Middlewares\SubscribeAuth;

class EmailSubscriberProvider extends ServiceProvider
{
    public function boot() {
        $this->publishConfig();
        if (!$this->app->routesAreCached()) {
            include dirname(__FILE__) . '/../routes.php';
        }
        $this->loadViewsFrom(dirname(__FILE__) . '/../Views/', 'email-subscriber');
        $this->registerMiddleware('Megaads\EmailSubscriber\Middlewares\HtmlInjectionMiddleware');

        $this->registerAuthMiddleware();
    }

    public function register()
    {
        // TODO: Implement register() method.
    }


    private function publishConfig()
    {
        if (function_exists('config_path')) {
            $path = $this->getConfigPath();
            $this->publishes([$path => config_path('subscriber.php')], 'config');
        }
    }

    private function getConfigPath() {
        return dirname(__FILE__) . '/../../config/subscriber.php';
    }

    protected function registerMiddleware($middleware) {
        $kernel = $this->app['Illuminate\Contracts\Http\Kernel'];
        $kernel->pushMiddleware($middleware);
    }

    protected function registerAuthMiddleware() {
        $appVersion = app()->version();
        preg_match('/\d+\.\d+/i', $appVersion, $matched);
        $matchVersion = isset($matched[0]) ? $matched[0] : 0;
        if ($appVersion <= 5.2) {
            app('router')->middleware('subs_auth', SubscribeAuth::class);
        } else if ($appVersion > 5.2 && $appVersion <= 5.8) {
            $this->app['router']->middleware('subs_auth', SubscribeAuth::class);
        }
    }

}