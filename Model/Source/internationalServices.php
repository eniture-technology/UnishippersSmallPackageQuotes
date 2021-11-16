<?php
namespace Eniture\UnishippersSmallPackageQuotes\Model\Source;

class internationalServices
{
    public function toOptionArray()
    {
        return [
            'internationalServices' =>
                ['value' => 'ZZ1',  'label'  => 'Worldwide Express'],

                ['value' => 'ZZ2',  'label'  => 'Worldwide Expedited'],

                ['value' => 'ZZ90',  'label'  => 'Worldwide Saver'],

                ['value' => 'ZZ11',  'label'  => 'Standard (Canada)']
            ];
    }
}
