<?php

namespace SwiatPrzesylek\Providers;


use Plenty\Modules\Order\Shipping\Returns\Services\ReturnsServiceProviderService;
use Plenty\Modules\Order\Shipping\ServiceProvider\Services\ShippingServiceProviderService;
use Plenty\Plugin\ServiceProvider;
use SwiatPrzesylek\Constants;
use SwiatPrzesylek\Controllers\ShippingController;

class SwiatPrzesylekServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     */
    public function register()
    {
        // add REST routes by registering a RouteServiceProvider if necessary
//	     $this->getApplication()->register(ShippingTutorialRouteServiceProvider::class);
    }

    public function boot(
        ShippingServiceProviderService $shippingServiceProviderService,
        ReturnsServiceProviderService $returnsServiceProviderService
    )
    {
        $shippingServiceProviderService->registerShippingProvider(
            Constants::PLUGIN_NAME,
            [
                'en' => 'Świat Przesyłek',
                'de' => 'Świat Przesyłek',
            ],
            [
                'SwiatPrzesylek\\Controllers\\ShippingController@registerShipments',
                'SwiatPrzesylek\\Controllers\\ShippingController@deleteShipments',
                'SwiatPrzesylek\\Controllers\\ShippingController@getLabels',
            ]
        );

        $returnsServiceProviderService->registerReturnsProvider(
            Constants::PLUGIN_NAME,
            'Świat Przesyłek',
            ShippingController::class
        );
    }
}