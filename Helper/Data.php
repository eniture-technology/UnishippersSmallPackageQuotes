<?php
/**
 * @category   Shipping
 * @package    Eniture_UnishippersSmallPackageQuotes
 * @author     Eniture Technology : <sales@eniture.com>
 * @website    http://eniture.com
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Eniture\UnishippersSmallPackageQuotes\Helper;

use \Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper
{
    public $connection;
    public $WHtableName;
    public $shippingConfig;
    public $storeManager;
    public $currencyFactory;
    public $priceCurrency;
    public $registry;
    public $coreSession;
    public $originZip;
    public $residentialDelivery;
    public $curl;
    public $canAddWh = 1;
    public $cacheManager;
    public $objectManager;
    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Shipping\Model\Config $shippingConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param \Magento\Directory\Model\Currency $currencyModel
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param \Magento\Directory\Helper\Data $directoryHelper
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Session\SessionManagerInterface $coreSession
     * @param \Eniture\UnishippersSmallPackageQuotes\Model\WarehouseFactory $warehouseFactory
     * @param \Magento\Framework\HTTP\Client\Curl $curl
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Shipping\Model\Config $shippingConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Directory\Model\Currency $currencyModel,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Directory\Helper\Data $directoryHelper,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Session\SessionManagerInterface $coreSession,
        \Eniture\UnishippersSmallPackageQuotes\Model\WarehouseFactory $warehouseFactory,
        \Eniture\UnishippersSmallPackageQuotes\Model\EnituremodulesFactory $enituremodulesFactory,
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Magento\Framework\App\Cache\Manager $cacheManager,
        \Magento\Framework\ObjectManagerInterface $objectmanager
    ) {
        $this->resource            = $resource;
        $this->shippingConfig      = $shippingConfig;
        $this->storeManager        = $storeManager;
        $this->currencyFactory     = $currencyFactory;
        $this->currenciesModel      = $currencyModel;
        $this->priceCurrency       = $priceCurrency;
        $this->directoryHelper      = $directoryHelper;
        $this->registry            = $registry;
        $this->coreSession         = $coreSession;
        $this->warehouseFactory    = $warehouseFactory;
        $this->enituremodulesFactory    = $enituremodulesFactory;
        $this->context       = $context;
        $this->curl = $curl;
        $this->cacheManager = $cacheManager;
        $this->objectManager = $objectmanager;
        parent::__construct($context);
    }

    /**
     * @param $location
     * @return array
     */
    public function fetchWarehouseSecData($location)
    {
        $whCollection       = $this->warehouseFactory->create()
            ->getCollection()->addFilter('location', ['eq' => $location]);
        $warehouseSecData   = $this->purifyCollectionData($whCollection);

        return $warehouseSecData;
    }

    /**
     * @param $location
     * @param $locationId
     * @return array
     */
    public function fetchWarehouseWithID($location, $locationId)
    {
        $whFactory = $this->warehouseFactory->create();
        $collection  = $whFactory->getCollection()
            ->addFilter('location', ['eq' => $location])
            ->addFilter('warehouse_id', ['eq' => $locationId]);

        $data   = $this->purifyCollectionData($collection);

        return $data;
    }

    /**
     * @param $whCollection
     * @return array
     */
    public function purifyCollectionData($whCollection)
    {
        $warehouseSecData = [];
        foreach ($whCollection as $wh) {
            $warehouseSecData[] = $wh->getData();
        }
        return $warehouseSecData;
    }

    /**
     * @param $data
     * @param $whereClause
     * @return mixed
     */
    function updateWarehousData($data, $whereClause)
    {
        $defualtConn = \Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION;
        $whTableName = $this->resource->getTableName('warehouse');
        return $this->resource->getConnection($defualtConn)->update("$whTableName", $data, "$whereClause");
    }

    /**
     * @param $data
     * @param $id
     * @return array
     */
    function insertWarehouseData($data, $id)
    {
        $defualtConn    = \Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION;
        $connection     =  $this->resource->getConnection($defualtConn);
        $whTableName    = $this->resource->getTableName('warehouse');
        $insertQry = $connection->insert("$whTableName", $data);
        if ($insertQry == 0) {
            $lastid = $id;
        } else {
            $lastid = $connection->lastInsertId();
        }
        return ['insertId' => $insertQry, 'lastId' => $lastid];
    }

    /**
     * @param $data
     * @return mixed
     */
    function deleteWarehouseSecData($data)
    {
        $defualtConn    = \Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION;
        $whTableName    = $this->resource->getTableName('warehouse');
        return $this->resource->getConnection($defualtConn)->delete("$whTableName", $data);
    }


    /**
     * Data Array
     * @param $inputData
     * @return array
     */

    public function unishippersSmOriginArray($inputData)
    {
        $dataArr = [
            'city'       => $inputData['city'],
            'state'      => $inputData['state'],
            'zip'        => $inputData['zip'],
            'country'    => $inputData['country'],
            'location'   => $inputData['location'],
            'nickname'   => $inputData['nickname'] ?? '',
        ];
        $pickupArr = [
            'enable_store_pickup'           => ($inputData['instore-enable'] === 'on') ? 1 : 0,
            'miles_store_pickup'            => $inputData['is-within-miles'],
            'match_postal_store_pickup'     => $inputData['is-postcode-match'],
            'checkout_desc_store_pickup'    => $inputData['is-checkout-descp'],
            'suppress_other'                => ($inputData['ld-sup-rates'] === 'on') ? 1 : 0,
        ];
        $dataArr['in_store'] = json_encode($pickupArr);

        $localDeliveryArr = [
            'enable_local_delivery'         => ($inputData['ld-enable'] === 'on') ? 1 : 0,
            'miles_local_delivery'          => $inputData['ld-within-miles'],
            'match_postal_local_delivery'   => $inputData['ld-postcode-match'],
            'checkout_desc_local_delivery'  => $inputData['ld-checkout-descp'],
            'fee_local_delivery'            => $inputData['ld-fee'],
            'suppress_other'                => ($inputData['ld-sup-rates'] === 'on')?1:0,
        ];
        $dataArr['local_delivery'] = json_encode($localDeliveryArr);

        return $dataArr;
    }

    /**
     * @param $scopeConfig
     */
    function quoteSettingsData($scopeConfig)
    {
        $fields = [
            'residentialDlvry'  => 'residentialDlvry',
            'deliveryEstimateOptions'        => 'deliveryEstimateOptions',
            'enableCuttOff'    => 'enableCuttOff',
            'cutOffTime'     => 'cutOffTime',
            'offsetDays'     => 'offsetDays',
            'shipDays'     => 'shipDays',
            'onlyGndService'     => 'onlyGndService',
            'gndHzrdousFee'     => 'gndHzrdousFee',
            'airHzrdousFee'     => 'airHzrdousFee'
        ];
        foreach ($fields as $key => $field) {
            $this->$key = $this->adminConfigData($field, $scopeConfig);
        }

        // Get origin zipcode array for onerate settings
        $this->getOriginZipCodeArr();
    }

    /**
     * getOriginZipCodeArr
     */
    public function getOriginZipCodeArr()
    {
        if ($this->registry->registry('shipmentOrigin') !== null) {
            $originArr = $this->registry->registry('shipmentOrigin');
        }

        foreach ($originArr as $key => $origin) {
            $this->originZip[$key] = $origin['senderZip'];
        }
    }

    /**
     * validate Input Post
     * @param $sPostData
     * @return mixed
     */
    public function unishippersSmValidatedPostData($sPostData)
    {
        $dataArray = ['city', 'state', 'zip', 'country'];
        foreach ($sPostData as $key => $tag) {
            $preg = '/[#$%@^&_*!()+=\-\[\]\';,.\/{}|":<>?~\\\\]/';
            $check_characters = (in_array($key, $dataArray)) ? preg_match($preg, $tag) : '';

            if ($check_characters != 1) {
                if ($key === 'city' || $key === 'nickname' || $key === 'in_store' || $key === 'local_delivery') {
                    $data[$key] = $tag;
                } else {
                    $data[$key] = preg_replace('/\s+/', '', $tag);
                }
            } else {
                $data[$key] = 'Error';
            }
        }

        return $data;
    }

    /**
     * @param array $getWarehouse
     * @param array $validateData
     * @return string
     */
    public function checkUpdatePickupDelivery($getWarehouse, array $validateData)
    {
        $update = false;
        $newData = $oldData = [];

        if (empty($getWarehouse)) {
            return $update;
        }

        $getWarehouse = reset($getWarehouse);
        unset($getWarehouse['warehouse_id']);
        unset($getWarehouse['nickname']);
        unset($validateData['nickname']);

        foreach ($getWarehouse as $key => $value) {
            if (empty($value) || is_null($value)) {
                $newData[$key] = 'empty';
            } else {
                $oldData[$key] = trim($value);
            }
        }

        $whData = array_merge($newData, $oldData);
        $diff1 = array_diff($whData, $validateData);
        $diff2 = array_diff($validateData, $whData);

        if ((is_array($diff1) && !empty($diff1)) || (is_array($diff2) && !empty($diff2))) {
            $update = true;
        }

        return $update;
    }
    /**
     * This function send request and return response
     * $isAssocArray Parameter When TRUE, then returned objects will
     * be converted into associative arrays, otherwise its an object
     * @param $url
     * @param $postData
     * @param $isAssocArray
     * @return string
     */
    public function unishippersSmSendCurlRequest($url, $postData, $isAssocArray = false)
    {
        $fieldString = http_build_query($postData);
        try {
            $this->curl->post($url, $fieldString);
            $output = $this->curl->getBody();
            $result = json_decode($output, $isAssocArray);
        } catch (\Throwable $e) {
            $result = [];
        }
        return $result;
    }

    /**
     * @param type $key
     * @return string|empty
     */
    public function getZipcode($key)
    {
        $key = explode("_", $key);
        return (isset($key[0])) ? $key[0] : "";
    }

    /**
     * Method Quotes
     * @param $quotes
     * @param $getMinimum
     * @return array
     */
    public function getQuotesResults($quotes, $getMinimum, $isMultishipmentQuantity, $scopeConfig)
    {
        $binPackagingArr = $filteredQ = [];
        $allConfigServices = $this->getAllConfigServicesArray($scopeConfig);
        $this->quoteSettingsData($scopeConfig);

        if ($isMultishipmentQuantity) {
            return $this->getOriginsMinimumQuotes($quotes, $allConfigServices, $scopeConfig);
        }

        $multiShipment = (count($quotes)>1 ? true : false);
        $this->verifyInsureShipmentArr = [];
        foreach ($quotes as $key => $quote) {

            $instPkpLclDlvry = isset($quote->InstorPickupLocalDelivery) ? $quote->InstorPickupLocalDelivery : "";
            if (!isset($quote->q)) {
                if (!empty($instPkpLclDlvry) && !$multiShipment) {
                    $filteredQ[$key] = $this->instoreLocalDeliveryQuotes([], $instPkpLclDlvry);
                }
                continue;
            }

            $binPackaging = $this->setBinPackagingData($quote, $key);

            $binPackagingArr[] = $binPackaging;
            $binPackThisShip = (count($binPackaging) > 0 && isset($binPackaging[$key])) ? $binPackaging[$key] : [];
            $filQuotes = $this->parseQuotes($quote, $scopeConfig, $allConfigServices, $binPackThisShip, $instPkpLclDlvry, $key);
            if (is_array($filQuotes) && count($filQuotes) > 0) {
                $filteredQ[$key] = $filQuotes;
            }
        }

        $this->coreSession->start();
        $this->coreSession->setUnishippersBinPackaging($binPackagingArr);

        if (!$multiShipment) {
            $this->setOrderDetailWidgetData([], $scopeConfig);
            return reset($filteredQ);
        } else {
            $multiShipQuotes = $this->getMultishipmentQuotes($filteredQ);
            $this->setOrderDetailWidgetData($multiShipQuotes['orderWidgetQ'], $scopeConfig);
            return $multiShipQuotes['multiShipQ'];
        }
    }

    /**
     * @param $availableServ
     * @param $scopeConfig
     * @param $allConfigServices
     * @return array
     */
    public function parseQuotes($availableServ, $scopeConfig, $allConfigServices, $binPackaging, $instPkpLclDlvry, $originKey)
    {
        $services = [];
        $boxFee = isset($binPackaging['unishippersServices']->boxesFee) ? $binPackaging['unishippersServices']->boxesFee : 0;
        $autoResidentialsStatus = isset($availableServ->autoResidentialsStatus)
            ? $availableServ->autoResidentialsStatus : '';
        foreach ($availableServ->q as $servkey => $availableServ) {

            if (in_array($availableServ->serviceType, $allConfigServices)) {

                if (!(isset($this->verifyInsureShipmentArr[$originKey]['shipmentInsured']) && $this->verifyInsureShipmentArr[$originKey]['shipmentInsured'] == 1)) {
                    $this->verifyInsureShipmentArr[$originKey]['shipmentInsured'] = $this->checkForShipmentInsurance($availableServ);
                }

                if ($availableServ->serviceType == 'SG' || $availableServ->serviceType == 'SGR') {
                    // check for Ground restriction field
                    $restrictGndSer = $this->transitTimeRestriction($availableServ);
                    if ($restrictGndSer) {
                        continue;
                    }
                }

                $cost = isset($availableServ->totalNetCharge->Amount) ? $availableServ->totalNetCharge->Amount : 0;
                $cost += $boxFee;    //adding BoxFee if any
                $serviceType = isset($availableServ->serviceType) ? $availableServ->serviceType : '';
                $autoResTitle = $this->getAutoResidentialTitle($autoResidentialsStatus);
                $serviceName = $this->getServiceNameWithEstimate($serviceType, $availableServ, true);
                $serviceTitle = $serviceName.' '.$autoResTitle;
                $addedHandlingCost = $this->calculateHandlingFee($cost, $scopeConfig);
                $addedHzrdousCost = $this->calculateHazardousFee($serviceType, $addedHandlingCost, $originKey);

                if ($addedHzrdousCost > 0) {
                    $services[] = [
                        'code'          => $serviceType.'UNS',
                        'rate'          => $addedHzrdousCost,
                        'title'         => $serviceTitle
                    ];
                }
            }
        }

        if (count($services) > 0) {
            $priceSortedKey = [];

            foreach ($services as $key => $costCarrier) {
                $priceSortedKey[$key] = $costCarrier['rate'];
            }
            array_multisort($priceSortedKey, SORT_ASC, $services);
        }

        if (!empty($instPkpLclDlvry)) {
            $services = $this->instoreLocalDeliveryQuotes(
                $services,
                $instPkpLclDlvry
            );
        }
        return $services;
    }

    /**
     * @param type $quote
     * @param type $key
     * @return array
     */
    public function setBinPackagingData($quote, $key)
    {
        $binPackaging = [];
        if (isset($quote->binPackagingData)) {
            $binPackaging[$key]['unishippersServices'] = $quote->binPackagingData ;
            $binPackaging[$key]['unishippersServices']->boxesFee = isset($quote->binPackagingData->response) ?
                $this->calculateBoxesFee($quote->binPackagingData->response)
                : 0;
        }
        return $binPackaging;
    }

    public function calculateBoxesFee($response)
    {
        $totalBoxesFee = 0;
        $boxesFee = $boxIDs = [];
        foreach ($response->bins_packed as $binDetails) {
            if (isset($binDetails->bin_data->type) && $binDetails->bin_data->type="item") { // If user boxes are not used
                $boxIDs = null;
            } else {
                $boxIDs[] = $binDetails->bin_data->id;
            }

        }
        if (!is_null($boxIDs) && count($boxIDs) > 0) {
            $boxFactory = $this->getBoxHelper('boxFactory');
            foreach ($boxIDs as $boxID) {
                if (!array_key_exists($boxID, $boxesFee)) {
                    $boxCollection = $boxFactory->getCollection()->addFilter('box_id', ['eq' => $boxID])->addFieldToSelect('boxfee');
                    foreach ($boxCollection as $box) {
                        $boxFee = $box->getData();
                    }
                    $boxesFee[$boxID]= !empty($boxFee['boxfee']) ? $boxFee['boxfee'] : 0;
                }

                $totalBoxesFee +=$boxesFee[$boxID];
            }
        }

        return $totalBoxesFee;
    }


    public function getBoxHelper($objectName)
    {
        if ($objectName == 'helper') {
            return $this->objectManager->get("Eniture\StandardBoxSizes\Helper\Data");
        }
        if ($objectName == 'boxFactory') {
            $boxHelper =  $this->objectManager->get("Eniture\StandardBoxSizes\Helper\Data");
            return $boxHelper->getBoxFactory();
        }
    }



    /**
     * @param type $filteredQuotes
     * @return array
     */
    public function getMultishipmentQuotes($filteredQuotes)
    {
        $totalRate = 0;
        $multiship = [];
        foreach ($filteredQuotes as $key => $multiQuotes) {
            if (isset($multiQuotes[0])) {
                $totalRate += $multiQuotes[0]['rate'];
                $multiship[$key]['quotes'] = $multiQuotes[0];
            }
        }

        $response['multiShipQ']['unishippersSmall'] = $this->getFinalQuoteArray(
            $totalRate,
            'UnishippersSPMS',
            'Shipping '.$this->residentialDelivery
        );
        $response['orderWidgetQ'] = $multiship;

        return $response;
    }

    /**
     * @param $response
     * @return mixed
     */
    public function transitTimeRestriction($service)
    {
        $daysToRestrict = $this->getConfigData('UnishippersSmQuoteSetting/third/transitDaysNumber');
        $transitDayType = $this->getConfigData('UnishippersSmQuoteSetting/third/transitDaysRestrictionBy');
        $plan = $this->unishippersSmallPlanInfo('ENUnishippersSmpkg');
        if ($plan['planNumber'] == 3 && strlen($daysToRestrict) > 0 && strlen($transitDayType) > 0) {
            if (isset($service->$transitDayType) &&
                ($service->$transitDayType > $daysToRestrict)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param string $service
     * @return string
     */
    public function getAutoResidentialTitle($service)
    {
        $append = '';
        $moduleManager = $this->context->getModuleManager();

        if ($moduleManager->isEnabled('Eniture_ResidentialAddressDetection')) {
            $isRadSuspend = $this->getConfigData('resaddressdetection/suspend/value');
            if ($this->residentialDlvry == "1") {
                $this->residentialDlvry = $isRadSuspend == 'no' ? null : $isRadSuspend;
            } else {
                $this->residentialDlvry = $isRadSuspend == "no" ? null : $this->residentialDlvry;
            }

            if ($this->residentialDlvry == null
            || $this->residentialDlvry == '0') {
                if ($service == 'r') {
                    $append = ' with residential delivery';
                }
            }
            $this->residentialDelivery = $append;
        }

        return $append;
    }


    /**
     * This function returns minimum array index from array
     * @param $servicesArr
     * @return array
     */
    public function findArrayMininum($servicesArr)
    {
        $counter = 1;
        $minIndex = [];
        foreach ($servicesArr as $key => $value) {
            if ($counter == 1) {
                $minimum =  $value['rate'];
                $minIndex = $value;
                $counter = 0;
            } else {
                if ($value['rate'] < $minimum) {
                    $minimum =  $value['rate'];
                    $minIndex = $value;
                }
            }
        }
        return $minIndex;
    }

    /**
     * @param array $quotes
     * @param array $allConfigServices
     * @param object $scopeConfig
     * @return array
     */
    public function getOriginsMinimumQuotes($quotes, $allConfigServices, $scopeConfig)
    {
        $minIndexArr = $binPackagingArr = [];
        $resiArr = ['residential' => false, 'label' => ''];
        foreach ($quotes as $key => $quote) {
            $minInQ = [];
            $counter = 0;

            $binPackaging = $this->setBinPackagingData($quote, $key);
            $binPackagingArr[] = $binPackaging;

            $isRad = $quote->autoResidentialsStatus ?? '';
            $resi = $this->getAutoResidentialTitle($isRad);

            if ($this->residentialDlvry == "1" || $resi != '') {
                $resiArr = ['residential' => true, 'label' => $resi];
            }

            if (isset($quote->q)) {
                foreach ($quote->q as $servkey => $availableServ) {
                    if (isset($availableServ->serviceType)
                        && in_array($availableServ->serviceType, $allConfigServices)) {
                        $totalAmount = isset($availableServ->totalNetCharge->Amount) ? $availableServ->totalNetCharge->Amount : 0;
                        $boxFee = isset($binPackaging['unishippersServices']->boxesFee) ? $binPackaging['unishippersServices']->boxesFee : 0;
                        $totalAmount += $boxFee;
                        $addedHandling = $this->calculateHandlingFee($totalAmount, $scopeConfig);
                        $addedHazardous = $this->calculateHazardousFee($availableServ->serviceType, $addedHandling, $key);
                        $currentService = $this->getServiceNameWithEstimate($availableServ->serviceType, $availableServ, false);
                        if ((isset($currentService) && !empty($currentService)) && $addedHazardous > 0) {
                            $currentArray = ['code' => $availableServ->serviceType . 'UNS',
                                'rate' => $addedHazardous,
                                'title' => $currentService . ' ' . $resi,
                                'resi' => $resiArr];
                            if ($counter == 0) {
                                $minInQ = $currentArray;
                            } else {
                                $minInQ = ($currentArray['rate'] < $minInQ['rate'] ? $currentArray : $minInQ);
                            }
                        }
                        $counter ++;
                    }
                }
                if ($minInQ['rate'] > 0) {
                    $minIndexArr[$key] = $minInQ;
                }
            }
        }

        $this->coreSession->start();
        $this->coreSession->setSemiBinPackaging($binPackagingArr);
        return $minIndexArr;
    }

    /**
     * This Function returns all active services array from configurations
     * @return array
     */
    public function getAllConfigServicesArray($scopeConfig)
    {

        $domesticServicesOptions   = $scopeConfig->getValue('UnishippersSmQuoteSetting/third/domesticServices', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $internatinalServicesOptions   = $scopeConfig->getValue('UnishippersSmQuoteSetting/third/internationalServices', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $servicesDomestic   = explode(',', $domesticServicesOptions);
        $servicesInternational     = explode(',', $internatinalServicesOptions);
        return array_merge($servicesDomestic, $servicesInternational);
    }

    /**
     * Final quotes array
     * @param $grandTotal
     * @param $code
     * @param $title
     * @return array
     */
    public function getFinalQuoteArray($grandTotal, $code, $title)
    {
        $allowed = [];
        if ($grandTotal > 0) {
            $allowed = [
                'code'  => $code,// or carrier name
                'title' => $title,
                'rate'  => $grandTotal
            ];
        }

        return $allowed;
    }

    /**
     * @param type $serviceType
     * @param type $addedHandling
     * @return type
     */
    public function calculateHazardousFee($serviceType, $addedHandling, $originKey)
    {
        $hazourdous = $this->checkHazardousShipment();

        if (!empty($hazourdous) && in_array($originKey, $hazourdous)) {
            $ground = ($serviceType == 'SG' || $serviceType == 'SGR') ? true : false;
            $addedHazardous = 0 ;
            if ($this->onlyGndService == '1') {
                if ($ground) {
                    if (!empty($this->gndHzrdousFee)) {
                        $addedHazardous = $this->gndHzrdousFee + $addedHandling;
                    } else {
                        $addedHazardous = $addedHandling;
                    }
                } elseif (!$ground && $this->airHzrdousFee !== '') {
                    $addedHazardous = 0 ;
                }
            } else {
                if ($ground && !empty($this->gndHzrdousFee)) {
                    $addedHazardous = $this->gndHzrdousFee + $addedHandling;
                } elseif (!$ground && !empty($this->airHzrdousFee)) {
                    $addedHazardous = $this->airHzrdousFee + $addedHandling;
                } else {
                    $addedHazardous = $addedHandling;
                }
            }
        } else {
            $addedHazardous = $addedHandling;
        }
        return $addedHazardous;
    }

    /**
     * @return type
     */
    public function checkHazardousShipment()
    {
        $hazourdous = [];
        $checkHazordous = $this->registry->registry('hazardousShipment');
        if (isset($checkHazordous)) {
            foreach ($checkHazordous as $key => $data) {
                foreach ($data as $k => $d) {
                    if ($d['isHazordous'] == '1') {
                        $hazourdous[] =  $k;
                    }
                }
            }
        }
        return $hazourdous;
    }


    /**
     * Calculate Handling Fee
     * @param $totalPrice
     * @param $scopeConfig
     * @return int | float
     */
    public function calculateHandlingFee($totalPrice, $scopeConfig)
    {
        $grpSec = 'UnishippersSmQuoteSetting/third';
        $hndlngFeeMarkup = $scopeConfig->getValue(
            $grpSec.'/hndlngFee',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $symbolicHndlngFee = $scopeConfig->getValue(
            $grpSec.'/symbolicHndlngFee',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        if ($hndlngFeeMarkup !== '') {
            if ($symbolicHndlngFee == '%') {
                $prcntVal = $hndlngFeeMarkup / 100 * $totalPrice;
                $grandTotal = $prcntVal + $totalPrice;
            } else {
                $grandTotal = $hndlngFeeMarkup + $totalPrice;
            }
        } else {
            $grandTotal = $totalPrice;
        }
        return $grandTotal;
    }

    /**
     * @param type $servicesArr
     * @param type $QCount
     */
    public function setOrderDetailWidgetData(array $servicesArr, $scopeConfig)
    {
        $orderDetail['residentialDelivery'] = ($this->residentialDelivery != '' || $this->residentialDlvry == '1' || $this->residentialDlvry == 'yes') ?
            'Residential Delivery' : '';

        $setPkgForOrderDetailReg = null !== $this->registry->registry('setPackageDataForOrderDetail') ?
            $this->registry->registry('setPackageDataForOrderDetail') : [];
        $orderDetail['insuranceInResp'] = $this->verifyInsureShipmentArr;
        $orderDetail['shipmentData'] = array_replace_recursive($setPkgForOrderDetailReg, $servicesArr);

        // set order detail widget data
        $this->coreSession->start();
        $this->coreSession->setUnishippersSmpkgOrderDetailSession($orderDetail);
    }

    /**
     *
     * @param type $fieldId
     * @param type $scopeConfig
     * @return type
     */
    function adminConfigData($fieldId, $scopeConfig)
    {
        return $scopeConfig->getValue("UnishippersSmQuoteSetting/third/$fieldId", \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     *
     * @return type
     */
    function getActiveCarriersForENCount()
    {
        return $this->shippingConfig->getActiveCarriers();
    }

    /**
     * @return array
     */
    public function quoteSettingFieldsToRestrict()
    {
        $restriction = [];
        $currentPlanArr = $this->unishippersSmallPlanInfo('ENUnishippersSmpkg');
        $transitFields = [
            'transitDaysNumber','transitDaysRestrictionByTransitTimeInDays','transitDaysRestrictionByCalenderDaysInTransit'
        ];
        $hazmatFields = [
            'onlyGndService','gndHzrdousFee','airHzrdousFee','enableCuttOff'
        ];
        switch ($currentPlanArr['planNumber']) {
            case 0:
                $restriction = [
                    'advance' => $transitFields,
                    'standard' => $hazmatFields
                ];
                break;
            case 1:
                $restriction = [
                    'advance' => $transitFields,
                    'standard' => $hazmatFields
                ];
                break;
            case 2:
                $restriction = [
                    'advance' => $transitFields
                ];
                break;
            default:
                break;
        }
        return $restriction;
    }

    /**
     * @return string
     */
    public function unishippersSmallSetPlanNotice($planRefreshUrl = '')
    {
        $planPackage = $this->unishippersSmallPlanInfo('ENUnishippersSmpkg');
        if (is_null($planPackage['storeType'])) {
            $planPackage = [];
        }
        $planMsg = $this->displayPlanMessages($planPackage, $planRefreshUrl);
        return $planMsg;
    }

    /**
     * @param type $planPackage
     * @return type
     */
    public function displayPlanMessages($planPackage, $planRefreshUrl = '')
    {

        $planRefreshLink = '';
        if (!empty($planRefreshUrl)) {
            $planRefreshLink = ' <a href="javascript:void(0)" id="plan-refresh-link" planRefAjaxUrl = '.$planRefreshUrl.' onclick="unishippersSmPlanRefresh(this)" >Click here</a> to refresh the plan (please sign-in again after this action).';
        }
        $planMsg = __('Eniture - Unishippers Small Package Quotes plan subscription is inactive.'.$planRefreshLink.'  If the subscription status remains inactive, log into eniture.com and update your license.');
        if (isset($planPackage) && !empty($planPackage)) {
            if (!is_null($planPackage['planNumber']) && $planPackage['planNumber'] != '-1') {
                $planMsg = __('Eniture - Unishippers Small Package Quotes is currently on the '.$planPackage['planName'].'. Your plan will expire within '.$planPackage['expireDays'].' days and plan renews on '.$planPackage['expiryDate'].'.'.$planRefreshLink);
            }
        }
        return $planMsg;
    }

    /**
     * Get Unishippers Small Plan
     * @param string $carrierId
     * @return array
     */
    public function unishippersSmallPlanInfo($carrierId = 'ENUnishippersSmpkg')
    {
        $plan = $this->getConfigData("eniture/$carrierId/plan");
        $storeType = $this->getConfigData("eniture/$carrierId/storetype");
        $expireDays = $this->getConfigData("eniture/$carrierId/expireday");
        $expiryDate = $this->getConfigData("eniture/$carrierId/expiredate");
        $planName = "";

        switch ($plan) {
            case 3:
                $planName = "Advanced Plan";
                break;
            case 2:
                $planName = "Standard Plan";
                break;
            case 1:
                $planName = "Basic Plan";
                break;
            case 0:
                $planName = "Trial Plan";
                break;
        }
        return [
            'planNumber' => $plan,
            'planName' => $planName,
            'expireDays' => $expireDays,
            'expiryDate' => $expiryDate,
            'storeType' => $storeType
        ];
    }

    public function getConfigData($confPath)
    {
        $scopeConfig = $this->context->getScopeConfig();
        return $scopeConfig->getValue($confPath, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function whPlanRestriction()
    {
        $planArr = $this->unishippersSmallPlanInfo('ENUnishippersSmpkg');

        $planNumber = isset($planArr['planNumber']) ? $planArr['planNumber'] : '';

        if ($planNumber < 2) {
            $warehouses = $this->fetchWarehouseSecData('warehouse');
            count($warehouses) > 0 ? $this->canAddWh = 0 : '';
        }
        return $this->canAddWh;
    }

    /**
     * @return int
     */
    public function checkAdvancePlan()
    {
        $advncPlan = 1;
        $planArr = $this->unishippersSmallPlanInfo('ENUnishippersSmpkg');
        $planNumber = isset($planArr['planNumber']) ? $planArr['planNumber'] : '';

        if ($planNumber != 3) {
            $advncPlan = 0;
        }
        return $advncPlan;
    }

    /**
     * @param type $quotesarray
     * @param type $instoreLd
     * @return type
     */
    public function instoreLocalDeliveryQuotes($quotesarray, $instoreLd)
    {
        $data = $this->registry->registry('shipmentOrigin');
        if (count($data) > 1) {
            return $quotesarray;
        }

        foreach ($data as $array) {

            $warehouseData = $this->getWarehouseData($array);


            /* Quotes array only to be made empty if Suppress other rates is ON and Instore Pickup or Local Delivery also carries some quotes. Else if Instore Pickup or Local Delivery does not have any quotes i.e Postal code or within miles does not match then the Quotes Array should be returned as it is. */
            if ($warehouseData['suppress_other']) {

                if ((isset($instoreLd->inStorePickup->status) && $instoreLd->inStorePickup->status == 1)
                    || (isset($instoreLd->localDelivery->status) && $instoreLd->localDelivery->status == 1)) {
                    $quotesarray=[];
                }
            }
            if (isset($instoreLd->inStorePickup->status) && $instoreLd->inStorePickup->status == 1) {
                $quotesarray[] = [
                    'serviceType' => 'IN_STORE_PICKUP',
                    'code' => 'INSP',
                    'rate' => 0,
                    'transitTime' => '',
                    'title' => $warehouseData['inStoreTitle'],
                    'serviceName' => 'UnishippersSmallservice'
                ];
            }

            if (isset($instoreLd->localDelivery->status) && $instoreLd->localDelivery->status == 1) {
                $quotesarray[] = [
                    'serviceType' => 'LOCAL_DELIVERY',
                    'code' => 'LOCDEL',
                    'rate' => $warehouseData['fee_local_delivery'],
                    'transitTime' => '',
                    'title' => $warehouseData['locDelTitle'],
                    'serviceName' => 'UnishippersSmallservice'
                ];
            }
        }
        return $quotesarray;
    }

    public function clearCache()
    {
        $this->cacheManager->flush($this->cacheManager->getAvailableTypes());
        // or this
        $this->cacheManager->clean($this->cacheManager->getAvailableTypes());
    }

    /**
     * @param $data
     * @return array
     */
    public function getWarehouseData($data)
    {
        $return = [];
        $whCollection = $this->warehouseFactory->create()->getCollection()
            ->addFilter('location', ['eq' => $data['location']])
            ->addFilter('warehouse_id', ['eq' => $data['locationId']]);

        $whCollection = $this->purifyCollectionData($whCollection);
        $instore = json_decode($whCollection[0]['in_store'], true);
        $locDel  = json_decode($whCollection[0]['local_delivery'], true);

        if ($instore) {
            $inStoreTitle = $instore['checkout_desc_store_pickup'];
            if (empty($inStoreTitle)) {
                $inStoreTitle = "Instore Pick Up";
            }
            $return['inStoreTitle'] = $inStoreTitle;
            $return['suppress_other'] = $instore['suppress_other']=='1' ? true : false;
        }

        if ($locDel) {
            $locDelTitle = $locDel['checkout_desc_local_delivery'];
            if (empty($locDelTitle)) {
                $locDelTitle = "Local Delivery";
            }
            $return['locDelTitle'] = $locDelTitle;
            $return['fee_local_delivery'] = $locDel['fee_local_delivery'];
            $return['suppress_other'] = $locDel['suppress_other']=='1' ? true : false;
        }
        return $return;
    }

    /**
     * Reruns service name
     * @param $serviceType
     * @param $serviceDesc
     */
    protected function getServiceNameWithEstimate($serviceType, $serviceData, $addEstimate)
    {

        $servsArr = [
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
        ];

        if (isset($servsArr[$serviceType])) {

            if ($addEstimate && !empty($this->deliveryEstimateOptions) && ($this->deliveryEstimateOptions == 'DAYS' || $this->deliveryEstimateOptions == 'DATE')) {
                return $this->attachTransitTimeWithTitle($servsArr[$serviceType], $serviceData);
            } else {
                return $servsArr[$serviceType];
            }
        } else {
            return $serviceData->serviceDesc;
        }
    }

    /**
     * return either shipment insured or not
     * @param $availableServ
     */
    protected function checkForShipmentInsurance($serviceQuote)
    {
        $surchargesArr = $serviceQuote->surcharges;
        if (is_array($surchargesArr->fee) && count($surchargesArr->fee) > 0) {
            foreach ($surchargesArr->fee as $surchargeObj) {
                if (isset($surchargeObj->code) && strpos(strtolower($surchargeObj->code), 'evc') !== false) {
                    return 1;
                }
            }
        }
        return 0;
    }

    /**
     * @param $serviceName
     * @param $serviceData
     * @return mixed|string
     */
    protected function attachTransitTimeWithTitle($serviceName, $serviceData)
    {

        if ($this->deliveryEstimateOptions == 'DAYS') {
            if (isset($serviceData->totalTransitTimeInDays) && is_numeric($serviceData->totalTransitTimeInDays)) {
                $verb = ($serviceData->totalTransitTimeInDays == '1') ? 'is' : 'are';
                $serviceName .= ' ( Estimated number of days until delivery ' . $verb . ' ' . $serviceData->totalTransitTimeInDays . ' )';
            }
        } elseif ($this->deliveryEstimateOptions == 'DATE') {
            if (!empty($serviceData->deliveryDate)) {
                $serviceName .= ' ( Expected delivery by ' . date('Y-m-d', strtotime($serviceData->deliveryDate)) . ' )';
            }
        }

        return $serviceName;
    }
}
