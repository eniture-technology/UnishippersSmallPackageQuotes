<?php
namespace Eniture\UnishippersSmallPackageQuotes\Model;

class Warehouse extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Eniture\UnishippersSmallPackageQuotes\Model\ResourceModel\Warehouse');
    }
}
