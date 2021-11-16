<?php
namespace Eniture\UnishippersSmallPackageQuotes\Model\Carrier;

/**
 * class that generated request data
 */
class UnishippersSmpkgGenerateRequestData
{
    public $registry;
    public $scopeConfig;
    public $moduleManager;
    public $dataHelper;
    public $request;
    public $timezone;

    public function _init(
        $scopeConfig,
        $registry,
        $moduleManager,
        $dataHelper,
        $request,
        $timezone
    ) {
        $this->registry        = $registry;
        $this->scopeConfig     = $scopeConfig;
        $this->moduleManager   = $moduleManager;
        $this->dataHelper      = $dataHelper;
        $this->request         = $request;
        $this->timezone = $timezone;
    }

    /**
     * function that generates Unishippers array
     * @return array
     */
    public function generateUnishippersSmpkgArray()
    {
        $unishippersSmpkgArr = [
            'licenseKey'    => $this->getConfigData('licenseKey'),
            'serverName'    => $this->request->getServer('SERVER_NAME'),
            'carrierMode'   => 'pro', // use test / pro
            'quotestType'   => 'small',
            'version'       => '1.0.0',
            'api'           => $this->getApiInfoArr(),
        ];

        return  $unishippersSmpkgArr;
    }

    /**
     * @param $request
     * @param $unishippersSmpkgArr
     * @param $itemsArr
     * @param $dataHelper
     * @return array
     */
    public function generateRequestArray($request, $unishippersSmpkgArr, $itemsArr, $cart)
    {
        if (count($unishippersSmpkgArr['originAddress']) > 1) {
            foreach ($unishippersSmpkgArr['originAddress'] as $wh) {
                $whIDs[] = $wh['locationId'];
            }
            if (count(array_unique($whIDs)) > 1) {
                foreach ($unishippersSmpkgArr['originAddress'] as $id => $wh) {
                    if (isset($wh['InstorPickupLocalDelivery'])) {
                        $unishippersSmpkgArr['originAddress'][$id]['InstorPickupLocalDelivery'] = [];
                    }
                }
            }
        }
        $carriers = $this->registry->registry('enitureCarriers');

        $carriers['unishippersSmall'] = $unishippersSmpkgArr;
        $receiverAddress = $this->getReceiverData($request);
        $smartPost = $this->getFedExSmartPost('FedExSmartPost');
        if ($this->registry->registry('fedexSmartPost') === null) {
            $this->registry->register('fedexSmartPost', $smartPost);
        }

        $requestArr = [
            'apiVersion'                    => '2.0',
            'platform'                      => 'magento2',
            'binPackagingMultiCarrier'      => $this->binPackSuspend(),
            'autoResidentials'              => $this->autoResidentialDelivery(),
            'liftGateWithAutoResidentials'  => $this->registry->registry('radForLiftgate'),
            'FedexOneRatePricing'           => ($smartPost) ? '0' : $this->checkFedExOnerate(),
            'FedexSmartPostPricing'         => $smartPost,

            'requestKey'        => $cart->getQuote()->getId(),
            'carriers'          => $carriers,
            'receiverAddress'   => $receiverAddress,
            'commdityDetails'   => $itemsArr
        ];

        if ($this->moduleManager->isEnabled('Eniture_StandardBoxSizes')) {
            $binsData = $this->getSavedBins();
            $requestArr = array_merge($requestArr, isset($binsData) ? $binsData : []);
        }

        return  $requestArr;
    }

    /**
     * @return int
     */
    public function checkFedExOnerate()
    {
        $onerate = 0;
        if ($this->registry->registry('FedexOneRatePricing')) {
            $onerate = $this->registry->registry('FedexOneRatePricing');
        }
        return $onerate;
    }

    /**
     * @param $postId
     * @return string
     */
    public function getFedExSmartPost($postId)
    {
        $return = "0";
        if ($this->moduleManager->isEnabled('Eniture_FedExSmallPackageQuotes')) {
            $isActive = $this->scopeConfig->getValue(
                "carriers/ENFedExSmpkg/active",
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );

            if ($isActive == 1) {
                $return = $this->scopeConfig->getValue(
                    "fedexQuoteSetting/third/$postId",
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                ) == "no" ? "1" : "0";
            }
        }
        return $return;
    }

    /**
     * @return string
     */
    public function binPackSuspend()
    {
        $return = "0";
        if ($this->moduleManager->isEnabled('Eniture_StandardBoxSizes')) {
            $return = $this->scopeConfig->getValue("binPackaging/suspend/value", \Magento\Store\Model\ScopeInterface::SCOPE_STORE) == "no" ? "1" : "0";
        }
        return $return;
    }
    /**
     * @return int
     */
    public function autoResidentialDelivery()
    {
        $autoDetectResidential = 0;
        if ($this->moduleManager->isEnabled('Eniture_ResidentialAddressDetection')) {
            $suspndPath = "resaddressdetection/suspend/value";
            $autoResidentialSuspend = $this->scopeConfig->getValue($suspndPath, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            if ($autoResidentialSuspend != null && $autoResidentialSuspend == 'no') {
                $autoDetectResidential = 1;
            }
        }
        if ($this->registry->registry('autoDetectResidential') === null) {
            $this->registry->register('autoDetectResidential', $autoDetectResidential);
        }

        return $autoDetectResidential ;
    }


    public function getSavedBins()
    {
        $savedBins = [];
        if ($this->moduleManager->isEnabled('Eniture_StandardBoxSizes')) {
            $boxSizeHelper = $this->dataHelper->getBoxHelper('helper');
            $savedBins = $boxSizeHelper->fillBoxingData();
        }
        return $savedBins;
    }

    /**
     * This function returns carriers array if have not empty origin address
     * @return array
     */
    public function getCarriersArray()
    {
        $carriersArr = $this->registry->registry('enitureCarriers');
        $newCarriersArr = [];
        foreach ($carriersArr as $carrkey => $carrArr) {
            $notHaveEmptyOrigin = true;
            foreach ($carrArr['originAddress'] as $key => $value) {
                if (empty($value['senderZip'])) {
                    $notHaveEmptyOrigin = false;
                }
            }
            if ($notHaveEmptyOrigin) {
                $newCarriersArr[$carrkey] = $carrArr;
            }
        }

        return $newCarriersArr;
    }

    /**
     * function that returns API array
     * @return array
     */
    public function getApiInfoArr()
    {
        $accessorialsArr = [];
        if (!$this->autoResidentialDelivery()) {
            if ($this->getConfigData('residentialDlvry')) {
                $accessorialsArr[] = 'REP';
            }
        }

        $apiArray = [
            'username'               => $this->getConfigData('username'),
            'password'               => $this->getConfigData('password'),
            'unishipperscustomernumber' => $this->getConfigData('unishippersCustomerNumber'),
            'upsaccountnumber'       => $this->getConfigData('upsAccountNumber'),
            'requestkey'             => $this->getConfigData('unishippersRequestKey'),
            'service'                => 'ALL',
            'packagetype'            => 'P',
            'accessorial'            => $accessorialsArr,
            'prefferedCurrency'      => 'USD',
            'includeDeclaredValue'   => $this->registry->registry('en_insurance'),
        ];

        $cutOffData = $this->getCutoffData();
        return array_merge($apiArray, $cutOffData);
    }

    /**
     * function return service data
     * @param $fieldId
     * @return string
     */
    public function getConfigData($fieldId)
    {
        $secThreeIds = ['residentialDlvry', 'weightExeeds', 'enableCuttOff', 'cutOffTime', 'offsetDays', 'shipDays'];
        if (in_array($fieldId, $secThreeIds)) {
            $sectionId = 'UnishippersSmQuoteSetting';
            $groupId = 'third';
        } else {
            $sectionId = 'UnishippersSmConnSetting';
            $groupId = 'first';
        }

        return $this->scopeConfig->getValue("$sectionId/$groupId/$fieldId", \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * This function returns Reveiver Data Array
     * @param $request
     * @return array
     */
    public function getReceiverData($request)
    {
        $addressType = $this->scopeConfig->getValue("resaddressdetection/addressType/value", \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $receiverDataArr = [
            'addressLine'           => $request->getDestStreet(),
            'receiverCity'          => $request->getDestCity(),
            'receiverState'         => $request->getDestRegionCode(),
            'receiverZip'           => preg_replace('/\s+/', '', $request->getDestPostcode()),
            'receiverCountryCode'   => $request->getDestCountryId(),
            'defaultRADAddressType' => $addressType ?? 'residential', //get value from RAD
        ];

        return  $receiverDataArr;
    }

    /**
     * Return offset days and cutoff time array
     * @return array
     */
    public function getCutoffData()
    {
        $return = [];
        $plan = $this->dataHelper->unishippersSmallPlanInfo("ENUnishippersSmpkg");
        $isEligible = $plan['planNumber'] > 1 && $this->getConfigData('enableCuttOff');
        if ($isEligible) {
            $cutOffTime = str_replace(',', ':', $this->getConfigData('cutOffTime'));
            $shipDays   = explode(',', $this->getConfigData('shipDays'));

            $return = [ 'modifyShipmentDateTime' => '1',
                'OrderCutoffTime'        => $cutOffTime,
                'shipmentOffsetDays'     => $this->getConfigData('offsetDays'),
                'storeDateTime'          => $this->timezone->date()->format('Y-m-d H:i:s'),
                'shipmentWeekDays'       => $shipDays
            ];
        }
        return $return;
    }
}
