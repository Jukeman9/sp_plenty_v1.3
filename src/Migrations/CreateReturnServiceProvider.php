<?php
/**
 * Created for plentymarkets-plugin.
 * User: jakim <pawel@jakimowski.info>
 * Date: 26/09/2019
 */

namespace SwiatPrzesylek\Migrations;


use Plenty\Modules\Order\Shipping\Returns\Contracts\ReturnsServiceProviderRepositoryContract;
use Plenty\Plugin\Log\Loggable;
use SwiatPrzesylek\Constants;

class CreateReturnServiceProvider
{
    use Loggable;

    private $returnsServiceProviderRepository;

    public function __construct(ReturnsServiceProviderRepositoryContract $returnsServiceProviderRepository)
    {
        $this->returnsServiceProviderRepository = $returnsServiceProviderRepository;
    }

    public function run()
    {
        try {
            $this->returnsServiceProviderRepository->saveReturnsServiceProvider(Constants::PLUGIN_NAME);
        } catch (\Exception $exception) {
            $this->getLogger(Constants::PLUGIN_NAME)
                ->critical(
                    "Could not migrate/create new shipping provider: " . $exception->getMessage(),
                    ['error' => $exception->getTrace()]
                );
        }
    }
}