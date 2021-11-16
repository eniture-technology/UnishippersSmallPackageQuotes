<?php
namespace Eniture\UnishippersSmallPackageQuotes\Block\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Backend\Block\Template\Context;
use Eniture\UnishippersSmallPackageQuotes\Helper\Data;
use Magento\Framework\Data\Form\Element\AbstractElement;

class TestConnection extends Field
{
    const BUTTON_TEMPLATE = 'system/config/testconnection.phtml';

    /**
     * @var Context
     */
    private $context;
    /**
     * @var Data
     */
    private $dataHelper;

    /**
     * @param Context $context
     * @param Data $dataHelper
     * @param array $data
     */
    public function __construct(Context $context, Data $dataHelper, $data = [])
    {
        $this->context = $context;
        $this->dataHelper = $dataHelper;
        parent::__construct($context, $data);
    }

    /**
     * @return $this
     */
    public function _prepareLayout()
    {
        parent::_prepareLayout();
        if (!$this->getTemplate()) {
            $this->setTemplate(static::BUTTON_TEMPLATE);
        }
        return $this;
    }

    /**
     * @param AbstractElement $element
     * @return element
     */
    public function render(AbstractElement $element)
    {
        // Remove scope label
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * @return url
     */
    public function getAjaxCheckUrl()
    {
        return $this->getbaseUrl().'unishipperssmallpackagequotes/Test/TestConnection/';
    }

    /**
     * @param AbstractElement $element
     * @return array
     */
    public function _getElementHtml(AbstractElement $element)
    {
        $this->addData(
            [
                'id'            => 'unishipperssm-test-conn',
                'button_label'  => 'Test Connection',
            ]
        );
        return $this->_toHtml();
    }

    /**
     * Show Unishippers Small Plan Notice
     * @return string
     */
    public function unishippersSmPlanNotice()
    {
        $planRefreshUrl = $this->getPlanRefreshUrl();
        return $this->dataHelper->unishippersSmallSetPlanNotice($planRefreshUrl);
    }

    public function unishippersSmConnMsg()
    {
        return '<div class="message message-notice notice unishipperssm-conn-setting-note">You must have a Unishippers (unishippers.com) account to use this application. If you don’t have one, contact Unishippers at 1-800-999-8721 and ask to be contacted by a sales person from the office serving your area or <a target="_blank" href="https://www.unishippers.com/request-new-account/">click here</a> to access the online new account request form</a>.</div>';
    }

    /**
     * @return url
     */
    public function getPlanRefreshUrl()
    {
        return $this->getbaseUrl().'unishipperssmallpackagequotes/Test/PlanRefresh/';
    }
}
