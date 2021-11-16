<?php

namespace Eniture\UnishippersSmallPackageQuotes\Model\ResourceModel\Warehouse;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Eniture\UnishippersSmallPackageQuotes\Model\Warehouse', 'Eniture\UnishippersSmallPackageQuotes\Model\ResourceModel\Warehouse');
        $this->_map['fields']['page_id'] = 'main_table.page_id';
    }
}
