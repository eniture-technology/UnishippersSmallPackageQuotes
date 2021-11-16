<?php
namespace Eniture\UnishippersSmallPackageQuotes\Model\Source;

class domesticServices
{
    public function toOptionArray()
    {
        return [
            'domesticServices' =>
                ['value' => 'ND',  'label'  => 'UPS Next Day Air'],

                ['value' => 'ND4',  'label'  => 'UPS Next Day Air Saver'],

                ['value' => 'ND5',  'label'  => 'UPS Next Day Air Early A.M.'],

                ['value' => 'SC',  'label'  => 'UPS 2nd Day Air'],

                ['value' => 'SC25',  'label'  => 'UPS 2nd Day Air A.M.'],

                ['value' => 'SC3',  'label'  => 'UPS 3 Day Select'],

                ['value' => 'SG',  'label'  => 'UPS Ground'],

                ['value' => 'SGR',  'label'  => 'UPS Ground (Residential Delivery)'],

                ['value' => 'SND',  'label'  => 'Saturday - UPS Next Day Air'],

                ['value' => 'SND5',  'label'  => 'Saturday - UPS Next Day Air Early A.M.'],

                ['value' => 'SSC',  'label'  => 'Saturday - UPS 2nd Day Air']
            ];
    }
}
