<?php declare(strict_types=1);
/**
* Copyright © PH2M SARL. All rights reserved.
* See COPYING.txt for license details.
*/

namespace Ph2m\MetabaseDashboard\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Encryption\EncryptorInterface;

class Data extends AbstractHelper
{
    private const XML_PATH_ENABLED           = 'metabase_dashboard/general/enabled';
    private const XML_PATH_SITE_URL          = 'metabase_dashboard/general/site_url';
    private const XML_PATH_SECRET_KEY        = 'metabase_dashboard/general/secret_key';
    private const XML_PATH_DASHBOARD_ID      = 'metabase_dashboard/general/dashboard_id';
    private const XML_PATH_EXPIRATION        = 'metabase_dashboard/general/expiration_minutes';
    private const XML_PATH_BORDERED          = 'metabase_dashboard/general/bordered';
    private const XML_PATH_TITLED            = 'metabase_dashboard/general/titled';

    public function __construct(
        Context $context,
        private readonly EncryptorInterface $encryptor
    ) {
        parent::__construct($context);
    }

    public function isEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_ENABLED);
    }

    public function getIframeUrl(): string
    {
        $siteUrl   = rtrim((string) $this->scopeConfig->getValue(self::XML_PATH_SITE_URL), '/');
        $secretKey = $this->encryptor->decrypt(
            (string) $this->scopeConfig->getValue(self::XML_PATH_SECRET_KEY)
        );
        $dashboardId = (int) $this->scopeConfig->getValue(self::XML_PATH_DASHBOARD_ID);
        $expiration  = (int) $this->scopeConfig->getValue(self::XML_PATH_EXPIRATION) ?: 10;
        $bordered    = $this->scopeConfig->isSetFlag(self::XML_PATH_BORDERED) ? 'true' : 'false';
        $titled      = $this->scopeConfig->isSetFlag(self::XML_PATH_TITLED) ? 'true' : 'false';

        $payload = [
            'resource' => ['dashboard' => $dashboardId],
            'params'   => new \stdClass(),
            'exp'      => time() + ($expiration * 60),
        ];

        $token = $this->encodeJwt($payload, $secretKey);

        return $siteUrl . '/embed/dashboard/' . $token . '#bordered=' . $bordered . '&titled=' . $titled;
    }

    private function encodeJwt(array $payload, string $secretKey): string
    {
        $header    = $this->base64UrlEncode((string) json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
        $payload   = $this->base64UrlEncode((string) json_encode($payload));
        $signature = $this->base64UrlEncode(
            hash_hmac('sha256', $header . '.' . $payload, $secretKey, true)
        );

        return $header . '.' . $payload . '.' . $signature;
    }

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
