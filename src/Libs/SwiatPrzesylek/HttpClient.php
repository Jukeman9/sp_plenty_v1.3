<?php

namespace SwiatPrzesylek\Libs\SwiatPrzesylek;


use Plenty\Plugin\ConfigRepository;

class HttpClient
{
    const RESPONSE_FAIL = 'FAIL';
    const RESPONSE_OK = 'OK';

    public $username;
    public $apiToken;
    protected $baseUrl = 'https://api.swiatprzesylek.pl/V1';

    private $response;

    /**
     * For SP clients who have several API accesses.
     * Package type must contain prefix (SPA-, SPB-, SPC-), method must be called before hitting SP API.
     *
     * @param \Plenty\Plugin\ConfigRepository $configRepository
     * @param $packageType
     */
    public function setApiAccessByPackageType(ConfigRepository $configRepository, $packageType)
    {
        if (!is_string($packageType)) {
            $packageType = 'SPA-Standard';
        }

        $prefix = substr($packageType, 0, 4);
        switch ($prefix) {
            case 'SPC-':
                $this->username = $configRepository->get('SwiatPrzesylek.access.apiUsernameC');
                $this->apiToken = $configRepository->get('SwiatPrzesylek.access.apiTokenC');
                break;
            case 'SPB-':
                $this->username = $configRepository->get('SwiatPrzesylek.access.apiUsernameB');
                $this->apiToken = $configRepository->get('SwiatPrzesylek.access.apiTokenB');
                break;
            case 'SPA-':
            default:
                $this->username = $configRepository->get('SwiatPrzesylek.access.apiUsername');
                $this->apiToken = $configRepository->get('SwiatPrzesylek.access.apiToken');
                break;
        }
    }

    public function post($url, array $data)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "{$this->baseUrl}/{$url}");
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, $this->username . ':' . $this->apiToken);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-type: application/json"]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $response = curl_exec($ch);

        return $this->response = json_decode($response, true, 512, JSON_BIGINT_AS_STRING);
    }

    public function getFirstError()
    {
        $error = $this->findSpApiError();
        if (!$error) {
            $error = $this->findPackageError();
        }
        if ($error) {
            return $this->prepareErrorMessage($error['msg'], $error['details']);
        }

        return '';
    }

    public function getLastResponse()
    {
        return $this->response;
    }

    /**
     * Curl fetch content.
     *
     * @param string $fileUrl
     * @return bool|string
     */
    public function download(string $fileUrl)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $fileUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $output = curl_exec($ch);
        curl_close($ch);

        return $output;
    }

    private function prepareErrorMessage($msg, $details): string
    {
        return "<br><br>
=====<br>
Error message from operator: <br>
$msg <br> 
$details<br>
=====";
    }

    private function findSpApiError()
    {
        if ($this->response['result'] == self::RESPONSE_FAIL) {
            $code = $this->response['error']['error_code'] ?? '';
            $err = $this->response['error']['desc'] ?? 'SP API returned an error';
            $details = $this->response['error']['details'] ?? [];

            return [
                'msg' => "Code $code: $err",
                'details' => current(current($details)),
            ];
        }
    }

    private function findPackageError()
    {
        $packages = $this->response['response']['packages'] ?? [];
        foreach ($packages as $package) {
            if ($package['result'] == self::RESPONSE_FAIL) {
                return [
                    'msg' => "Package: {$package['package_id']}",
                    'details' => $package['log'],
                ];
            }
        }
    }
}