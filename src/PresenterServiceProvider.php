<?php

namespace Sands\Presenter;

use Illuminate\Support\ServiceProvider;

class PresenterServiceProvider extends ServiceProvider
{

    protected $presenters = [
        'blade' => [
            'presenter' => Presenters\Blade::class,
            'mimes' => [
                'text/html',
                'application/xhtml+xml',
            ],
        ],
        'json' => [
            'presenter' => Presenters\Json::class,
            'extensions' => [
                'json',
            ],
            'mimes' => [
                'application/json',
            ],
        ],
    ];

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {

    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        // register singleton
        $presenters = $this->presenters;
        app()->singleton('sands.presenter', function ($app) use ($presenters) {
            $presenter = new Presenter();
            foreach ($presenters as $type => $config) {
                $presenter->register($type, $config);
            }
            return $presenter;
        });
    }
}
