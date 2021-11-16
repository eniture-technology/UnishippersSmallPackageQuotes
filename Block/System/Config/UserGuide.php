<?php
namespace Eniture\UnishippersSmallPackageQuotes\Block\System\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;

class UserGuide extends \Magento\Config\Block\System\Config\Form\Field
{
    const GUIDE_TEMPLATE = 'system/config/userguide.phtml';

    private $dataHelper;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Eniture\UnishippersSmallPackageQuotes\Helper\Data $dataHelper,
        array $data = []
    ) {
        $this->dataHelper      = $dataHelper;
        parent::__construct($context, $data);
    }
    /**
     * @return $this
     */
    public function _prepareLayout()
    {
        parent::_prepareLayout();
        if (!$this->getTemplate()) {
            $this->setTemplate(static::GUIDE_TEMPLATE);
        }
        return $this;
    }

    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return html
     */
    public function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        return $this->_toHtml();
    }

    /**
     * Show Unishippers Small Plan Notice
     * @return string
     */
    public function unishippersSmallPlanNotice()
    {
        $planMsg = $this->dataHelper->unishippersSmallSetPlanNotice();
        return $planMsg;
    }
}
