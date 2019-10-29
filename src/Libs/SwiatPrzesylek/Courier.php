<?php

namespace SwiatPrzesylek\Libs\SwiatPrzesylek;


use Plenty\Plugin\ConfigRepository;
use Plenty\Plugin\Log\Loggable;
use SwiatPrzesylek\Constants;

class Courier
{
    use Loggable;

    /**
     * @var \SwiatPrzesylek\Libs\SwiatPrzesylek\HttpClient
     */
    public $client;

    private $config;

    public function __construct(HttpClient $client, ConfigRepository $configRepository)
    {
        $this->client = $client;
        $this->config = $configRepository;
        $this->client->setApiAccessByPackageType($configRepository, null);
    }

    public function cancel($packageType, $id)
    {
        $env = $this->config->get('SwiatPrzesylek.env.type', Constants::ENV_DEV);

        $this->client->setApiAccessByPackageType($this->config, $packageType);

        if ($env == Constants::ENV_DEV) {
            return 'ok';
        }

        return $this->client->post('courier/cancel', [
            'id' => [$id],
        ]);
    }

    public function createReturn($packageType, array $package, array $sender, array $receiver)
    {
        $env = $this->config->get('SwiatPrzesylek.env.type', Constants::ENV_DEV);

        $this->client->setApiAccessByPackageType($this->config, $packageType);

        if ($env == Constants::ENV_DEV) {
            return $this->dummyCreatePreRouting();
        }

        return $this->client->post('courier/create-return', [
            'package' => $package,
            'sender' => $sender,
            'receiver' => $receiver,
            'options2' => [
                'label_type' => 'PDF',
            ],
        ]);
    }

    public function createPreRouting($packageType, array $package, array $sender, array $receiver, array $options = [])
    {
        $env = $this->config->get('SwiatPrzesylek.env.type', Constants::ENV_DEV);

        $this->client->setApiAccessByPackageType($this->config, $packageType);

        if ($env == Constants::ENV_DEV) {
            return $this->dummyCreatePreRouting();
        }

        return $this->client->post('courier/create-pre-routing', [
            'package' => $package,
            'sender' => $sender,
            'receiver' => $receiver,
            'options' => $options,
            'options2' => [
                'label_type' => 'PDF',
            ],
        ]);
    }

    protected function dummyCreatePreRouting()
    {
        return [
            'result' => 'OK',
            'response' => [
                'number' => 1,
                'packages' => [
                    [
                        'package_id' => rand(100, 1000000) . uniqid('', true),
                        'result' => 'OK',
                        'log' => '',
                        'labels_no' => 1,
                        'labels' => [
                            base64_encode($this->client->download('https://www.dhl.com/content/dam/downloads/g0/express/customs_regulations_china/waybill_sample.pdf')),
                        ],
                        'labels_file_ext' => 'pdf',
                        'external_id' => rand(100, 1000000) . uniqid('', true),
                    ],
                ],
            ],
        ];
    }
}