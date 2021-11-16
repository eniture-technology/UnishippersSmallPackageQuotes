<?php
namespace Eniture\UnishippersSmallPackageQuotes\Model\Carrier;

/**
 * class for admin configuration that runs first
 */
class UnishippersSmpkgAdminConfiguration
{
    /**
     * @var \Magento\Framework\Registry
     */

    public $registry;
    public $scopeConfig;

    /**
     * @param type $scopeConfig
     * @param type $registry
     */
    public function _init($scopeConfig, $registry)
    {
        $this->registry = $registry;
        $this->scopeConfig = $scopeConfig;
        $this->setCarriersAndHelpersCodesGlobaly();
    }

    /**
     * This function is for set carriers codes and helpers code globaly
     */
    public function setCarriersAndHelpersCodesGlobaly()
    {
        $this->setCodesGlobaly('enitureCarrierCodes', 'ENUnishippersSmpkg');
        $this->setCodesGlobaly('enitureCarrierTitle', 'Unishippers Small Package Quotes');
        $this->setCodesGlobaly('enitureHelpersCodes', '\Eniture\UnishippersSmallPackageQuotes');
        $this->setCodesGlobaly('enitureActiveModules', $this->checkModuleIsEnabled());
        $this->setCodesGlobaly('enitureModuleTypes', 'small');
    }

    /**
     * return if this module is enable or not
     * @return boolean
     */
    public function checkModuleIsEnabled()
    {
        $grpSecPath = "carriers/ENUnishippersSmpkg/active";
        return $this->scopeConfig->getValue($grpSecPath, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * This function sets Codes Globaly e.g carrier code or helper code
     * @param $globArrayName
     * @param $arrValue
     */
    public function setCodesGlobaly($globArrayName, $arrValue)
    {
        if (is_null($this->registry->registry($globArrayName))) {
            $codesArray = [];
            $codesArray['unishippersSmall'] = $arrValue;
            $this->registry->register($globArrayName, $codesArray);
        } else {
            $codesArray = $this->registry->registry($globArrayName);
            $codesArray['unishippersSmall'] = $arrValue;
            $this->registry->unregister($globArrayName);
            $this->registry->register($globArrayName, $codesArray);
        }
    }
}
