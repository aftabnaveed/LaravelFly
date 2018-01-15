<?php

namespace LaravelFly\Coroutine\Bootstrap;

use LaravelFly\Coroutine\Application;

class SetProvidersInRequest
{

    public function bootstrap(Application $app)
    {

        $appConfig = $app->make('config');

        if ($ps = $appConfig['laravelfly.providers_in_request']) {

            $app->setProvidersInRequest($ps);

            $appConfig['app.providers'] = array_diff($appConfig['app.providers'], $ps);

            if ($appConfig['app.debug']) {
                echo '[NOTICE] Providers in request ( they are removed from config["app.providers"] )',
                implode(', ', $ps), '.From:', __CLASS__, PHP_EOL;
            }

        }

    }
}