<?php

namespace App;

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class CalebApi
{
    private string $p25url;
    private string $dmrUrl;
    private string $allstarUrl;
    private string $irlpUrl;
    private CacheInterface $cache;
    private HttpClientInterface $httpClient;

    public function __construct(
        string $p25url,
        string $dmrUrl,
        string $allstarUrl,
        string $irlpUrl,
        CacheInterface $cache,
        HttpClientInterface $httpClient
    ) {
        $this->p25url = $p25url;
        $this->dmrUrl = $dmrUrl;
        $this->allstarUrl = $allstarUrl;
        $this->irlpUrl = $irlpUrl;

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
        $nodes = $this->getVoipNodes();

        $result = array_filter($nodes, static function (string $nodeId) {
            return str_starts_with($nodeId, 'HH');
        });

        return array_values($result);
    }

    public function getHPNodes(): array
    {
        $nodes = $this->getVoipNodes();

        $result = array_filter($nodes, static function (string $nodeId) {
            return str_starts_with($nodeId, 'HP');
        });

        return array_values($result);
    }

    public function getAllstarNodes(): array
    {
        $nodes = $this->getVoipNodes();

        $result = array_filter($nodes, static function (string $nodeId) {
            return !in_array(substr($nodeId, 0, 2), ['HH', 'HP']);
        });

        return array_values($result);
    }

    public function getIRLPNodes(): array
    {
        $httpClient = $this->httpClient;
        $url = $this->irlpUrl;

        /** @var array $result */
        $result = $this->cache->get(
            'IRLP_NODES',
            static function (ItemInterface $item) use ($httpClient, $url): array {
                $item->expiresAfter(60);

                try {
                    $response = $httpClient->request(Request::METHOD_GET, $url);
                    $crawler = new Crawler($response->getContent());
                } catch (ExceptionInterface $exception) {
                    return [];
                }

                $nodes = [];
                foreach ($crawler->filter('body > center:nth-child(2) > table > tr > td:nth-child(2)') as $td) {
                    $nodes[] = $td->textContent;
                }

                return $nodes;
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
                $item->expiresAfter(60);

                try {
                    $response = $httpClient->request(Request::METHOD_GET, $url);
                    $crawler = new Crawler($response->getContent());
                } catch (ExceptionInterface $exception) {
                    return [];
                }

                $callsigns = [];
                foreach ($crawler->filter('b') as $element) {
                    // Strip unicode white space from the callsign
                    $callsign = preg_replace('/^[\pZ\pC]+|[\pZ\pC]+$/u', '', $element->textContent);

                    if (in_array($callsign, ['N0CALL', 'MMDVM'])) {
                        continue;
                    }

                    $callsigns[] = trim($element->textContent);
                }

                return $callsigns;
            }
        );

        return $result;
    }

    private function getVoipNodes(): array
    {
        $httpClient = $this->httpClient;
        $url = $this->allstarUrl;

        /** @var array $result */
        $result = $this->cache->get(
            'ALLSTAR_NODES',
            static function (ItemInterface $item) use ($httpClient, $url): array {
                $item->expiresAfter(60);

                try {
                    /** @var array[] $result */
                    $result = $httpClient->request(Request::METHOD_GET, $url)->toArray();
                } catch (ExceptionInterface $exception) {
                    return [];
                }

                return $result['nodes'] ?? [];
            }
        );

        return $result;
    }

    public function getAllstarNodesByCallsign(string $callsign): array
    {
        $httpClient = $this->httpClient;
        $url = 'https://stats.allstarlink.org/stats/byCallsign';

        /** @var array $result */
        $result = $this->cache->get(
            'ALLSTAR_NODES_BY_CALL_SIGN_' . $callsign,
            static function (ItemInterface $item) use ($httpClient, $url, $callsign): array {
                $item->expiresAfter(60);

                try {
                    $response = $httpClient->request(Request::METHOD_GET, $url, [
                        'query' => ['callsign' => $callsign],
                    ]);
                    $crawler = new Crawler($response->getContent());
                } catch (ExceptionInterface $exception) {
                    return [];
                }

                $nodes = [];
                foreach ($crawler->filter('body > main > div:nth-child(2) > table > tr > td:nth-child(1)') as $td) {
                    $nodes[] = $td->textContent;
                }

                return $nodes;
            }
        );

        return $result;
    }

    public function getEchoLinkNodesByCallsign(string $callsign): array
    {
        $httpClient = $this->httpClient;
        $url = 'https://www.echolink.org/validation/node_lookup.jsp';

        /** @var array $result */
        $result = $this->cache->get(
            'ECHOLINK_NODES_BY_CALL_SIGN_' . $callsign,
            static function (ItemInterface $item) use ($httpClient, $url, $callsign): array {
                $item->expiresAfter(60);

                try {
                    $response = $httpClient->request(Request::METHOD_POST, $url, [
                        'body' => ['call' => $callsign],
                    ]);
                    $crawler = new Crawler($response->getContent());
                } catch (ExceptionInterface $exception) {
                    return [];
                }

                $nodes = [];
                foreach (
                    $crawler->filter('body > table:nth-child(4) > tr:not(:first-child) > td:nth-child(2)') as $td
                ) {
                    $nodes[] = $td->textContent;
                }

                return $nodes;
            }
        );

        return $result;
    }
}
