<?php
namespace Eniture\UnishippersSmallPackageQuotes\Cron;

use Eniture\UnishippersSmallPackageQuotes\Helper\UnishippersSmConstants;
use Magento\Framework\App\Config\ConfigResource\ConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class PlanUpgrade
{
    /**
     * @var StoreManagerInterface
     */
    public $storeManager;
    /**
     * @var Curl
     */
    public $curl;
    /**
     * @var ConfigInterface
     */
    public $resourceConfig;
    /**
     * @var ConfigInterface
     */
    public $scopeConfig;
    /**
     * @var LoggerInterface
     */
    public $logger;

    /**
     * PlanUpgrade constructor.
     * @param StoreManagerInterface $storeManager
     * @param Curl $curl
     * @param ConfigInterface $resourceConfig
     * @param ScopeConfigInterface scopeConfig
     * @param LoggerInterface $logger
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        Curl $curl,
        ConfigInterface $resourceConfig,
        ScopeConfigInterface $scopeConfig,
        LoggerInterface $logger
    ) {
        $this->storeManager     = $storeManager;
        $this->curl             = $curl;
        $this->resourceConfig   = $resourceConfig;
        $this->scopeConfig   = $scopeConfig;
        $this->logger           = $logger;
    }

  /**
   *
   */
    public function execute()
    {
        $domain = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);
        $licenseKey = $this->scopeConfig->getValue(
            'UnishippersSmConnSetting/first/licenseKey',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $webhookUrl = $domain.'unishipperssmallpackagequotes';
        $postData = http_build_query([
                'platform'      => 'magento2',
                'carrier'       => '91',
                'store_url'     => $domain,
                'webhook_url'   => $webhookUrl,
                'license_key'   => ($licenseKey) ?? '',
            ]);

        $this->curl->post(UnishippersSmConstants::PLAN_URL, $postData);
        $output = $this->curl->getBody();
        $result = json_decode($output, true);

        $plan       = !empty($result['pakg_group']) ? $result['pakg_group'] : 0;
        $expireDay  = $result['pakg_duration'] ?? '';
        $expiryDate = $result['expiry_date'] ?? '';
        $planType   = $result['plan_type'] ?? '';
        $pakgPrice  = $result['pakg_price'] ?? '';

        if ($pakgPrice == 0) {
            $plan = 0;
        }

        $today =  date('F d, Y');
        if (!empty($expiryDate) && strtotime($today) > strtotime($expiryDate)) {
            $plan ='-1';
        }
        $configData = ['plan' => $plan,
                        'expireday' => $expireDay,
                        'expiredate' => $expiryDate,
                        'storetype' => $planType,
                        'pakgprice' => $pakgPrice,
                        'label' => 'Eniture - Unishippers Small Package Quotes'
                    ];
        $this->saveConfigurations($configData);
        $this->logger->info($output);
    }


    /**
     * @param array $configData
     */
    public function saveConfigurations($configData)
    {
        $path = 'eniture/ENUnishippersSmpkg/';
        foreach ($configData as $field => $value) {
            $this->resourceConfig->saveConfig(
                $path.$field,
                $value,
                ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                Store::DEFAULT_STORE_ID
            );
        }
    }
}
