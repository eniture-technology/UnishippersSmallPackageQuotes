<?php


namespace Eniture\UnishippersSmallPackageQuotes\Model\Source;

class orderShipDays
{
    public function toOptionArray()
    {
        return ['orderShipDays' =>
            ['value' => '1', 'label' => 'Monday'],
            ['value' => '2', 'label' => 'Tuesday'],
            ['value' => '3', 'label' => 'Wednesday'],
            ['value' => '4', 'label' => 'Thursday'],
            ['value' => '5', 'label' => 'Friday']
        ];
    }
}
