<?php

namespace App;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class RadioIdApi
{
    private HttpClientInterface $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient->withOptions([
            'base_uri' => 'https://database.radioid.net/',
        ]);
    }

    public function getDmrUsers(array $params = []): array
    {
        $validParameters = ['id', 'callsign', 'surname', 'city', 'state', 'country'];
        $invalidParameters = array_diff(array_keys($params), $validParameters);

        if (!empty($invalidParameters)) {
            throw new \Exception(sprintf(
                'Invalid parameter "%s". Expected one of "%s".',
                array_shift($invalidParameters),
                implode('", "', $validParameters)
            ));
        }

        return $this->httpClient->request(
            Request::METHOD_GET,
            'api/dmr/user/',
            ['query' => $params]
        )->toArray();
    }

    public function getDmrRepeaters(array $params = []): array
    {
        $validParameters = ['id', 'callsign', 'city', 'state', 'country', 'frequency', 'trustee'];
        $invalidParameters = array_diff(array_keys($params), $validParameters);

        if (!empty($invalidParameters)) {
            throw new \Exception(sprintf(
                'Invalid parameter "%s". Expected one of "%s".',
                array_shift($invalidParameters),
                implode('", "', $validParameters)
            ));
        }

        return $this->httpClient->request(
            Request::METHOD_GET,
            'api/dmr/repeater/',
            ['query' => $params]
        )->toArray();
    }

    public function getNxdnUsers(array $params = []): array
    {
        $validParameters = ['id', 'callsign', 'surname', 'city', 'state', 'country'];
        $invalidParameters = array_diff(array_keys($params), $validParameters);

        if (!empty($invalidParameters)) {
            throw new \Exception(sprintf(
                'Invalid parameter "%s". Expected one of "%s".',
                array_shift($invalidParameters),
                implode('", "', $validParameters)
            ));
        }

        return $this->httpClient->request(
            Request::METHOD_GET,
            'api/nxdn/user/',
            ['query' => $params]
        )->toArray();
    }

    public function getCplusUsers(array $params = []): array
    {
        $validParameters = ['id', 'callsign', 'surname', 'city', 'state', 'country'];
        $invalidParameters = array_diff(array_keys($params), $validParameters);

        if (!empty($invalidParameters)) {
            throw new \Exception(sprintf(
                'Invalid parameter "%s". Expected one of "%s".',
                array_shift($invalidParameters),
                implode('", "', $validParameters)
            ));
        }

        return $this->httpClient->request(
            Request::METHOD_GET,
            'api/cplus/user/',
            ['query' => $params]
        )->toArray();
    }
}
