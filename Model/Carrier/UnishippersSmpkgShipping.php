<?php

namespace Eniture\UnishippersSmallPackageQuotes\Model\Carrier;

use Magento\Quote\Model\Quote\Address\RateRequest;
use Eniture\UnishippersSmallPackageQuotes\Helper\UnishippersSmConstants;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * @category   Shipping
 * @package    Eniture_UnishippersSmallPackageQuotes
 * @author     eniture.com
 * @website    https://eniture.com
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UnishippersSmpkgShipping extends \Magento\Shipping\Model\Carrier\AbstractCarrier implements
    \Magento\Shipping\Model\Carrier\CarrierInterface
{
    protected $_code = 'ENUnishippersSmpkg';

    public $isFixed = true;

    public $rateResultFactory;

    public $rateMethodFactory;

    public $scopeConfig;

    public $dataHelper;

    public $registry;

    public $moduleManager;

    public $qty;

    public $session;

    public $productloader;

    public $mageVersion;

    public $objectManager;
    /**
     * @var TimezoneInterface
     */
    private $timezone;

    /**
     * UnishippersSmpkgShipping constructor.
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory
     * @param \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory
     * @param \Magento\Checkout\Model\Cart $cart
     * @param \Eniture\UnishippersSmallPackageQuotes\Helper\Data $dataHelper
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Magento\Framework\UrlInterface $urlInterface
     * @param \Magento\Framework\Session\SessionManagerInterface $session
     * @param \Magento\Catalog\Model\ProductFactory $productloader
     * @param \Magento\Framework\ObjectManagerInterface $objectmanager
     * @param UnishippersSmpkgAdminConfiguration $unishippersAdminConfig
     * @param UnishippersSmpkgShipmentPackage $unishippersShipPkg
     * @param UnishippersSmpkgGenerateRequestData $unishippersReqData
     * @param UnishippersSmallSetCarriersGlobaly $unishippersSetGlobalCarrier
     * @param UnishippersSmpkgManageAllQuotes $unishippersMangQuotes
     * @param \Magento\Framework\App\RequestInterface $httpRequest
     * @param TimezoneInterface $httpRequest
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        \Magento\Checkout\Model\Cart $cart,
        \Eniture\UnishippersSmallPackageQuotes\Helper\Data $dataHelper,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Framework\UrlInterface $urlInterface,
        \Magento\Framework\Session\SessionManagerInterface $session,
        \Magento\Catalog\Model\ProductFactory $productloader,
        \Magento\Framework\ObjectManagerInterface $objectmanager,
        \Eniture\UnishippersSmallPackageQuotes\Model\Carrier\UnishippersSmpkgAdminConfiguration $unishippersAdminConfig,
        \Eniture\UnishippersSmallPackageQuotes\Model\Carrier\UnishippersSmpkgShipmentPackage $unishippersShipPkg,
        \Eniture\UnishippersSmallPackageQuotes\Model\Carrier\UnishippersSmpkgGenerateRequestData $unishippersReqData,
        \Eniture\UnishippersSmallPackageQuotes\Model\Carrier\UnishippersSmallSetCarriersGlobaly $unishippersSetGlobalCarrier,
        \Eniture\UnishippersSmallPackageQuotes\Model\Carrier\UnishippersSmpkgManageAllQuotes $unishippersMangQuotes,
        \Magento\Framework\App\RequestInterface $httpRequest,
        TimezoneInterface $timezone,
        array $data = []
    ) {
        $this->rateResultFactory = $rateResultFactory;
        $this->rateMethodFactory = $rateMethodFactory;
        $this->scopeConfig       = $scopeConfig;
        $this->cart              = $cart;
        $this->dataHelper        = $dataHelper;
        $this->registry          = $registry;
        $this->moduleManager     = $moduleManager;
        $this->urlInterface      = $urlInterface;
        $this->session            = $session;
        $this->productloader     = $productloader;
        $this->objectManager       = $objectmanager;
        $this->unishippersAdminConfig       = $unishippersAdminConfig;
        $this->unishippersShipPkg       = $unishippersShipPkg;
        $this->unishippersReqData       = $unishippersReqData;
        $this->unishippersSetGlobalCarrier       = $unishippersSetGlobalCarrier;
        $this->unishippersMangQuotes       = $unishippersMangQuotes;
        $this->httpRequest = $httpRequest;
        $this->timezone = $timezone;
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }

    public function collectRates(RateRequest $request)
    {
        if (!$this->scopeConfig->getValue(
            'carriers/ENUnishippersSmpkg/active',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        )
        ) {
            return false;
        }

        if (empty($request->getDestPostcode()) || empty($request->getDestCountryId()) ||
            empty($request->getDestCity()) || empty($request->getDestRegionId())) {
            return false;
        }

        $this->getAllowedMethods();

        // Admin Configuration Class call
        $this->unishippersAdminConfig->_init($this->scopeConfig, $this->registry);

        $ItemsList          = $request->getAllItems();
        $receiverZipCode    = $request->getDestPostcode();

        $package            = $this->GetUnishippersSmpkgShipmentPackage($ItemsList, $receiverZipCode, $request);

        $this->unishippersReqData->_init($this->scopeConfig, $this->registry, $this->moduleManager, $this->dataHelper, $this->httpRequest, $this->timezone);
        $unishippersSmpkgArr        = $this->unishippersReqData->generateUnishippersSmpkgArray();

        $unishippersSmpkgArr['originAddress'] = $package['origin'];

        $this->unishippersSetGlobalCarrier->_init($this->dataHelper);
        $resp = $this->unishippersSetGlobalCarrier->manageCarriersGlobaly($unishippersSmpkgArr, $this->registry);

        $getQuotesFromSession = $this->quotesFromSession();
        if (null !== $getQuotesFromSession) {
            return $getQuotesFromSession;
        }

        if (!$resp) {
            return false;
        }

        $requestArr = $this->unishippersReqData->generateRequestArray(
            $request,
            $unishippersSmpkgArr,
            $package['items'],
            $this->cart
        );

        if (empty($requestArr)) {
            return false;
        }

        $quotes = $this->dataHelper->unishippersSmSendCurlRequest(UnishippersSmConstants::QUOTES_URL, $requestArr);

        $this->unishippersMangQuotes->_init($quotes, $this->dataHelper, $this->scopeConfig, $this->registry, $this->session, $this->moduleManager, $this->objectManager);
        $quotesResult = $this->unishippersMangQuotes->getQuotesResultArr($request);
        $this->session->setEnShippingQuotes($quotesResult);

        $unishippersSmpkgQuotes = (!empty($quotesResult))?$this->setCarrierRates($quotesResult):'';
        return $unishippersSmpkgQuotes;
    }

    /**
     * This fuction returns quotes from session
     * @return type
     */
    public function quotesFromSession()
    {
        $currentAction = $this->urlInterface->getCurrentUrl();
        $currentAction = strtolower($currentAction);
        if (strpos($currentAction, 'shipping-information') !== false || strpos($currentAction, 'payment-information') !== false) {
            $availableSessionQuotes = $this->session->getEnShippingQuotes(); // FROM SESSSION
            $availableQuotes = (!empty($availableSessionQuotes))?$this->setCarrierRates($availableSessionQuotes):null;
        } else {
            $availableQuotes = null;
        }
        return $availableQuotes;
    }

    /**
     * @return array
     */
    public function getAllowedMethods()
    {
        $allowed = explode(',', $this->getConfigData('allowed_methods'));
        $arr = [];
        foreach ($allowed as $k) {
            $arr[$k] = $this->getCode('method', $k);
        }

        return $arr;
    }
    /**
     * Get configuration data of carrier
     * @param string $type
     * @param string $code
     * @return array|false
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function getCode($type, $code = '')
    {
        $codes = [
            'method' => [
                'ND' => __('UPS Next Day Air'),
                'ND4' => __('UPS Next Day Air Saver'),
                'ND5' => __('UPS Next Day Air Early A.M.'),
                'SC' => __('UPS 2nd Day Air'),
                'SC25' => __('UPS 2nd Day Air A.M.'),
                'SC3' => __('UPS 3 Day Select'),
                'SG' => __('UPS Ground'),
                'SGR' => __('UPS Ground (Residential Delivery)'),
                'SND' => __('Saturday - UPS Next Day Air'),
                'SND5' => __('Saturday - UPS Next Day Air Early A.M.'),
                'SSC' => __('Saturday - UPS 2nd Day Air'),
                'ZZ1' => __('Worldwide Express'),
                'ZZ2' => __('Worldwide Expedited'),
                'ZZ90' => __('Worldwide Saver'),
                'ZZ11' => __('Standard (Canada)')

            ],
        ];

        if (!isset($codes[$type])) {
            return false;
        } elseif ('' === $code) {
            return $codes[$type];
        }

        if (!isset($codes[$type][$code])) {
            return false;
        } else {
            return $codes[$type][$code];
        }
    }
    /**
     * This function returns package array
     * @param $items
     * @param $receiverZipCode
     * @param $request
     * @return array
     */
    public function GetUnishippersSmpkgShipmentPackage($items, $receiverZipCode, $request)
    {

        $this->unishippersShipPkg->_init($request, $this->scopeConfig, $this->dataHelper, $this->productloader, $this->httpRequest);

        $weightConfigExeedOpt = $this->scopeConfig->getValue('UnishippersSmQuoteSetting/third/weightExeeds', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $insuranceData = $hazordousData = [];
        foreach ($items as $key => $item) {
            if ($item->getRealProductType() == 'configurable') {
                $this->qty= $item->getQty();
            }
            if ($item->getRealProductType() == 'simple') {

                $productQty = ( $this->qty > 0 ) ? $this->qty: $item->getQty();
                $this->qty = 0;

                $_product       = $this->productloader->create()->load($item->getProductId());

                $isEnableLtl    = $_product->getData('en_ltl_check');

                $lineItemClass  = $_product->getData('en_freight_class');

                if (($isEnableLtl) || ( $_product->getWeight() > 150 && $weightConfigExeedOpt)) {
                    $freightClass = 'ltl';
                } else {
                    $freightClass = '';
                }

                $originAddress  = $this->unishippersShipPkg->unishippersSmpkgOriginAddress($_product, $receiverZipCode);

                //Checking if plan is at least Standard
                $plan = $this->dataHelper->unishippersSmallPlanInfo("ENUnishippersSmpkg");
                if ($plan['planNumber'] < 2) {
                    $insurance =  0;
                    $hazmat = 'N';
                } else {
                    $hazmat = ($_product->getData('en_hazmat'))?'Y':'N';
                    $insurance = $_product->getData('en_insurance');
                    if ($insurance) {
                        if ($this->registry->registry('en_insurance') === null) {
                            $this->registry->register('en_insurance', $insurance);
                        }

                        if (!isset($insuranceData[$originAddress['senderZip']])) {
                            $insuranceData[$originAddress['senderZip']] = '1';
                        }
                    }
                }

                switch ($lineItemClass) {
                    case 77:
                        $lineItemClass = 77.5;
                        break;
                    case 92:
                        $lineItemClass = 92.5;
                        break;
                    default:
                        break;
                }

                $hazordousData[][$originAddress['senderZip']] = $this->setHazmatArray($_product, $hazmat);
                $package['origin'][$_product->getId()] = $originAddress;
                $orderWidget[$originAddress['senderZip']]['origin'] = $originAddress;

                $length = ( $_product->getData('en_length') != null ) ? $_product->getData('en_length') : $_product->getData('ts_dimensions_length');
                $width = ( $_product->getData('en_width') != null ) ? $_product->getData('en_width') : $_product->getData('ts_dimensions_width');
                $height = ( $_product->getData('en_height') != null ) ? $_product->getData('en_height') : $_product->getData('ts_dimensions_height');

                $lineItems = [
                    'lineItemClass'          => ($lineItemClass == 'No Freight Class'
                        || $lineItemClass == 'No') ?
                        0 : $lineItemClass,
                    'freightClass'              => $freightClass,
                    'lineItemId'                => $_product->getId(),
                    'lineItemName'              => $_product->getName(),
                    'piecesOfLineItem'          => $productQty,
                    'lineItemPrice'             => $_product->getPrice(),
                    'lineItemWeight'            => number_format($_product->getWeight(), 2, '.', ''),
                    'lineItemLength'            => number_format($length, 2, '.', ''),
                    'lineItemWidth'             => number_format($width, 2, '.', ''),
                    'lineItemHeight'            => number_format($height, 2, '.', ''),
                    'isHazmatLineItem'          => $hazmat,
                    'product_insurance_active'  => $insurance,
                    'shipBinAlone'              => $_product->getData('en_own_package'),
                    'vertical_rotation'         => $_product->getData('en_vertical_rotation'),
                ];

                $package['items'][$_product->getId()] = array_merge($lineItems);
                $orderWidget[$originAddress['senderZip']]['item'][] = $package['items'][$_product->getId()];
            }
        }

        $this->setDataInRegistry($package['origin'], $hazordousData, $orderWidget, $insuranceData);

        return $package;
    }

    /**
     * @param type $_product
     * @return type
     */
    public function setHazmatArray($_product, $hazmat)
    {
        return [
            'lineItemId'    => $_product->getId(),
            'isHazordous'   => $hazmat== 'Y' ? '1' : '0' ,
        ];
    }

    /**
     * @param type $origin
     * @param type $hazordousData
     * @param type $setPackageDataForOrderDetail
     */
    public function setDataInRegistry($origin, $hazordousData, $orderWidget, $insuranceData)
    {
        // set order detail widget data
        if ($this->registry->registry('setPackageDataForOrderDetail') === null) {
            $this->registry->register('setPackageDataForOrderDetail', $orderWidget);
        }

        // set hazardous data globally
        if ($this->registry->registry('hazardousShipment') === null) {
            $this->registry->register('hazardousShipment', $hazordousData);
        }
        // set shipment origin globally for instore pickup and local delivery
        if ($this->registry->registry('shipmentOrigin') === null) {
            $this->registry->register('shipmentOrigin', $origin);
        }

        // set Insurance data globally
        if ($this->registry->registry('insureShipmentIdentifier') === null) {
            $this->registry->register('insureShipmentIdentifier', $insuranceData);
        }
    }

    public function setCarrierRates($quotes)
    {
        $carrersArray   = $this->registry->registry('enitureCarrierCodes');
        $carrersTitle   = $this->registry->registry('enitureCarrierTitle');

        $result = $this->rateResultFactory->create();

        foreach ($quotes as $carrierkey => $quote) {
            foreach ($quote as $key => $carreir) {
                if (isset($carreir['code']) && isset($carreir['title']) && isset($carreir['rate'])) {
                    $method = $this->rateMethodFactory->create();
                    $carrierCode    = (isset($carrersTitle[$carrierkey]))? $carrersTitle[$carrierkey] : $this->_code;
                    $carrierTitle   = (isset($carrersArray[$carrierkey]))? $carrersArray[$carrierkey] : $this->getConfigData('title');
                    $method->setCarrierTitle($carrierCode);
                    $method->setCarrier($carrierTitle);
                    $method->setMethod($carreir['code']);
                    $method->setMethodTitle($carreir['title']);
                    $method->setPrice($carreir['rate']);

                    $result->append($method);
                }

            }
        }

        return $result;
    }
}
