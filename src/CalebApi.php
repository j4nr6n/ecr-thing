<?php

namespace App;

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class CalebApi
{
    private string $p25url;
    private string $dmrUrl;
    private string $allstarUrl;
    private string $allstarPort;
    private string $irlpUrl;
    private string $irlpToken;
    private CacheInterface $cache;
    private HttpClientInterface $httpClient;

    public function __construct(
        string $p25url,
        string $dmrUrl,
        string $allstarUrl,
        string $allstarPort,
        string $irlpUrl,
        string $irlpToken,
        CacheInterface $cache,
        HttpClientInterface $httpClient
    ) {
        $this->p25url = $p25url;
        $this->dmrUrl = $dmrUrl;
        $this->allstarUrl = $allstarUrl;
        $this->allstarPort = $allstarPort;
        $this->irlpUrl = $irlpUrl;
        $this->irlpToken = $irlpToken;

        $this->cache = $cache;
        $this->httpClient = $httpClient;
    }

    public function getP25Nodes(): array
    {
        return $this->parseCallsigns($this->p25url);
    }

    public function getDMRNodes(): array
    {
        return $this->parseCallsigns($this->dmrUrl);
    }

    public function getHHNodes(): array
    {
        $nodes = $this->getAllstarNodes();

        $result = array_filter($nodes, static function ($nodeId) {
            return str_starts_with($nodeId, 'HH');
        });

        return array_values($result);
    }

    public function getHPNodes(): array
    {
        $nodes = $this->getAllstarNodes();

        $result = array_filter($nodes, static function ($nodeId) {
            return str_starts_with($nodeId, 'HP');
        });

        return array_values($result);
    }

    public function getAllstarNodes(): array
    {
        $httpClient = $this->httpClient;
        $url = $this->allstarUrl;
        $port = $this->allstarPort;

        /** @var array $result */
        $result = $this->cache->get(
            'ALLSTAR_NODES',
            static function (ItemInterface $item) use ($httpClient, $url, $port): array {
                $item->expiresAfter(3600);

                $result = $httpClient->request('GET', $url, [
                    'query' => [
                        'node' => $port,
                    ],
                ])->toArray();

                return $result['nodes'] ?? [];
            }
        );

        return $result;
    }

    public function getIRLPNodes(): array
    {
        $httpClient = $this->httpClient;
        $url = $this->irlpUrl;
        $token = $this->irlpToken;

        /** @var array $result */
        $result = $this->cache->get(
            'IRLP_NODES',
            static function (ItemInterface $item) use ($httpClient, $url, $token): array {
                $item->expiresAfter(3600);

                $result = $httpClient->request('GET', $url, [
                    'headers' => [
                        'authorizationToken' => $token,
                    ],
                    'verify_host' => false,
                    'verify_peer' => false,
                ])->toArray();

                return array_map(static function ($node) {
                    return $node['client'];
                }, $result['IRLP'] ?? []);
            }
        );

        return $result;
    }

    private function parseCallsigns(string $url): array
    {
        $httpClient = $this->httpClient;

        /** @var array $result */
        $result = $this->cache->get(
            'PARSE_CALLSIGNS_' . base64_encode($url),
            static function (ItemInterface $item) use ($httpClient, $url): array {
                $item->expiresAfter(3600);

                $response = $httpClient->request('GET', $url);
                $crawler = new Crawler($response->getContent());

                $callsigns = [];
                foreach ($crawler->filter('b') as $element) {
                    if (in_array(trim($element->textContent), ['N0CALL', 'MMDVM'])) {
                        continue;
                    }

                    $callsigns[] = trim($element->textContent);
                }

                return $callsigns;
            }
        );

        return $result;
    }
}
