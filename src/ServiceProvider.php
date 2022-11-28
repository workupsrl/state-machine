<?php

namespace Workup\StateMachine;

use Illuminate\Foundation\Application as LaravelApplication;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Laravel\Lumen\Application as LumenApplication;
use Workup\StateMachine\Callback\ContainerAwareCallback;
use Workup\StateMachine\Callback\ContainerAwareCallbackFactory;
use Workup\StateMachine\Commands\Debug;
use Workup\StateMachine\Commands\Visualize;
use Workup\StateMachine\Event\Dispatcher;
use Workup\StateMachine\Factory\Factory;
use SM\Callback\CallbackFactoryInterface;
use SM\Callback\CascadeTransitionCallback;
use SM\Factory\FactoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            if ($this->app instanceof LaravelApplication) {
                $this->publishes([
                    __DIR__.'/../config/state-machine.php' => config_path('state-machine.php'),
                ], 'config');
            } elseif ($this->app instanceof LumenApplication) {
                $this->app->configure('state-machine');
            }
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->registerCallbackFactory();
        $this->registerEventDispatcher();
        $this->registerFactory();
        $this->registerCascadeTransitionCallback();
        $this->registerCommands();
    }

    protected function registerCallbackFactory()
    {
        $this->app->bind('sm.callback.factory', function ($app) {
            return new ContainerAwareCallbackFactory(ContainerAwareCallback::class, $app);
        });

        $this->app->alias('sm.callback.factory', CallbackFactoryInterface::class);
    }

    protected function registerEventDispatcher()
    {
        $this->app->bind('sm.event.dispatcher', function ($app) {
            return new Dispatcher($app->make('events'));
        });

        $this->app->alias('sm.event.dispatcher', EventDispatcherInterface::class);
    }

    protected function registerFactory()
    {
        $this->app->singleton('sm.factory', function ($app) {
            return new Factory(
                $app->make('config')->get('state-machine', []),
                $app->make('sm.event.dispatcher'),
                $app->make('sm.callback.factory')
            );
        });

        $this->app->alias('sm.factory', FactoryInterface::class);
    }

    protected function registerCascadeTransitionCallback()
    {
        $this->app->bind(CascadeTransitionCallback::class, function ($app) {
            return new CascadeTransitionCallback($app->make('sm.factory'));
        });
    }

    protected function registerCommands()
    {
        $this->app->bind(Debug::class, function ($app) {
            return new Debug($app->make('config')->get('state-machine', []));
        });

        $this->app->bind(Visualize::class, function ($app) {
            return new Visualize($app->make('config')->get('state-machine', []));
        });

        $this->commands([
            Debug::class,
            Visualize::class,
        ]);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            'sm.callback.factory',
            'sm.event.dispatcher',
            'sm.factory',
            Debug::class,
        ];
    }
}
