<?php

declare(strict_types=1);

namespace OH\Cloudflare\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Store\Model\ScopeInterface;

class ConfigProvider
{
    /**
     * @var string
     */
    const XML_CONFIG_PATH_ENABLE_PURGE_CACHE = 'oh_cloudflare/settings/enable_purge_cache';

    /**
     * @var string
     */
    const XML_CONFIG_PATH_EMAIL = 'oh_cloudflare/settings/email';

    /**
     * @var string
     */
    const XML_CONFIG_PATH_AUTH_KEY = 'oh_cloudflare/settings/auth_key';

    /**
     * @var string
     */
    const XML_CONFIG_PATH_ZONES = 'oh_cloudflare/settings/zones';

    /**
     * @var ScopeInterface
     */
    private $scopeInterface;

    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    public function __construct(
        EncryptorInterface $encryptor,
        ScopeConfigInterface $scopeInterface
    ) {
        $this->scopeInterface = $scopeInterface;
        $this->encryptor = $encryptor;
    }

    /**
     * Check if can purge cache
     *
     * @return bool
     */
    public function isEnabledPurgeCache(): bool
    {
        return $this->scopeInterface->isSetFlag(self::XML_CONFIG_PATH_ENABLE_PURGE_CACHE) ?: false;
    }

    /**
     * Get auth key
     *
     * @return string
     */
    public function getAuthKey(): string
    {
        return $this->scopeInterface->getValue(self::XML_CONFIG_PATH_AUTH_KEY) ? $this->encryptor->decrypt($this->scopeInterface->getValue(self::XML_CONFIG_PATH_AUTH_KEY)) : '';
    }

    /**
     * Get email associated
     *
     * @return string
     */
    public function getEmail(): string
    {
        return $this->scopeInterface->getValue(self::XML_CONFIG_PATH_EMAIL) ?: '';
    }

    /**
     * Get zone ids
     *
     * @return array
     */
    public function getZoneIds(): array
    {
        return $this->scopeInterface->getValue(self::XML_CONFIG_PATH_ZONES) ? explode(',', $this->scopeInterface->getValue(self::XML_CONFIG_PATH_ZONES)) : [];
    }
}