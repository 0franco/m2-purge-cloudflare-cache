<?php

declare(strict_types=1);

namespace OH\Cloudflare\Model;

use Magento\Framework\HTTP\ZendClientFactory;
use Magento\Framework\Serialize\SerializerInterface;

class Client
{
    /**
     * @var string
     */
    const CLOUDFLARE_BASE_API_URL = 'https://api.cloudflare.com/client/v4';

    /**
     * @var array
     */
    const API_ENDPOINTS = [
        'zones' => '/zones',
        'purge_cache' => '/zones/{zone_id}/purge_cache'
    ];

    /**
     * @var ZendClientFactory
     */
    private $httpClientFactory;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    public function __construct(
        SerializerInterface $serializer,
        ZendClientFactory $httpClientFactory,
        ConfigProvider $configProvider
    ) {
        $this->serializer = $serializer;
        $this->configProvider = $configProvider;
        $this->httpClientFactory = $httpClientFactory;
    }

    /**
     * @param $headers
     * @return array
     */
    public function appendBaseHeader($headers): array
    {
        return array_merge($headers, ['Content-Type: application/json']);
    }

    /**
     * @param $headers
     * @return array
     */
    public function appendAuthKeyHeader($headers): array
    {
        return array_merge($headers, [sprintf('X-Auth-Key: %s', $this->configProvider->getAuthKey())]);
    }

    /**
     * @param $headers
     * @return array
     */
    public function appendAuthEmailHeader(&$headers): array
    {
        return array_merge($headers, [sprintf('X-Auth-Email: %s', $this->configProvider->getEmail())]);
    }

    /**
     * Purge everything
     */
    public function purgeAllCache(): array
    {
        $result = [];
        $url = self::CLOUDFLARE_BASE_API_URL . self::API_ENDPOINTS['purge_cache'];

        foreach ($this->configProvider->getZoneIds() as $zoneId) {
            $curl = $this->httpClientFactory->create();
            $url = str_replace('{zone_id}', $zoneId, $url);

            $headers = [];
            $headers = $this->appendBaseHeader($headers);
            $headers = $this->appendAuthEmailHeader($headers);
            $headers = $this->appendAuthKeyHeader($headers);

            $curl->setUri($url);
            $curl->setMethod(\Zend_Http_Client::POST);
            $curl->setHeaders($headers);
            $curl->setRawData('{"purge_everything":true}');

            $responseBody = $curl->request()->getBody();
            $result[] = $this->serializer->unserialize($responseBody);
        }

        return $result;
    }

    /**
     * Get available zones
     *
     * @return array
     */
    public function getZones(): array
    {
        try {
            $url = self::CLOUDFLARE_BASE_API_URL . self::API_ENDPOINTS['zones'];
            $curl = $this->httpClientFactory->create();
            $curl->setConfig(['maxredirects' => 5, 'timeout' => 30]);

            $headers = [];
            $headers = $this->appendBaseHeader($headers);
            $headers = $this->appendAuthEmailHeader($headers);
            $headers = $this->appendAuthKeyHeader($headers);

            $curl->setUri($url);
            $curl->setMethod(\Zend_Http_Client::GET);
            $curl->setHeaders($headers);

            $responseBody = $curl->request()->getBody();
            $response = $this->serializer->unserialize($responseBody);
        } catch (\Exception $e) {
            return [
                'error' => ['Response error: %1', $e->getMessage()],
                'response' => $e->getTraceAsString()
            ];
        }

        return $response && $response['success'] ? $response['result'] : [];
    }
}