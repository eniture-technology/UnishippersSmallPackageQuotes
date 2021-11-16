<?php
namespace Eniture\UnishippersSmallPackageQuotes\Model\Source;

class deliveryEstimateOptions
{
    public function toOptionArray()
    {
        return [
            'deliveryEstimateOptions' =>
                ['value' => 'NO',  'label'  => 'Don\'t display delivery estimates.'],

                ['value' => 'DAYS',  'label'  => 'Display estimated number of days until delivery.'],

                ['value' => 'DATE',  'label'  => 'Display estimated delivery date.']
            ];
    }
}
