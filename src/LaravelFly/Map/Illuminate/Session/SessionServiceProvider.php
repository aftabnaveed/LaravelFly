<?php

namespace LaravelFly\Map\Illuminate\Session;


class SessionServiceProvider extends \Illuminate\Session\SessionServiceProvider
{
    static public function coroutineFriendlyServices():array
    {
        return ['session', 'session.store', \Illuminate\Session\Middleware\StartSession::class];
    }

    public function register()
    {
        $this->registerSessionManager();

        $this->registerSessionDriver();

        $this->app->singleton(\Illuminate\Session\Middleware\StartSession::class, function ($app) {
            return new StartSession($this->app->make('session'));
        });

    }
    protected function registerSessionManager()
    {
        $this->app->singleton('session', function ($app) {
            return new SessionManager($app);
        });
    }
}
