<?php

namespace Eniture\UnishippersSmallPackageQuotes\Controller\Test;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Eniture\UnishippersSmallPackageQuotes\Helper\Data;
use Eniture\UnishippersSmallPackageQuotes\Helper\UnishippersSmConstants;

class TestConnection extends Action
{
    /**
     * @var Data
     */
    private $dataHelper;

    /**
     * TestConnection constructor.
     * @param Context $context
     * @param Data $dataHelper
     */
    public function __construct(
        Context $context,
        Data $dataHelper
    ) {
        $this->dataHelper   = $dataHelper;
        parent::__construct($context);
    }

    public function execute()
    {
        $credentials = [];
        foreach ($this->getRequest()->getPostValue() as $key => $data) {
            $credentials[$key] = filter_var($data, FILTER_SANITIZE_STRING);
        }

        $postData = [
            'platform'            => 'magento2',
            'username'            => $credentials['username'] ?? '',
            'password'            => $credentials['password'] ?? '',
            'unishipperscustomernumber' => $credentials['unishippersCustomerNumber'] ?? '',
            'upsaccountnumber'    => $credentials['upsAccountNumber'] ?? '',
            'requestkey'          => $credentials['unishippersRequestKey'] ?? '',
            'carrierName'         => 'unisheppers',
            'carrier_mode'        => 'test',
            'licence_key'         => $credentials['pluginLicenceKey'] ?? '',
            'server_name'         => $this->getStoreUrl(),
        ];

        $response = $this->dataHelper->unishippersSmSendCurlRequest(UnishippersSmConstants::TEST_CONN_URL, $postData);

        $result = $this->unishippersSmTestConnResponse($response);

        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody($result);
    }

    /**
     * @param object $response
     * @return object
     */
    public function unishippersSmTestConnResponse($response)
    {

        $return = [];
        $successMsg = 'The test resulted in a successful connection.';
        $erMsg = 'The credentials entered did not result in a successful test. Confirm your credentials and try again.';

        if (isset($response->severity) && $response->severity == 'SUCCESS') {
            $return['Success'] =  $successMsg;
        } elseif (isset($response->severity) && $response->severity == 'ERROR') {
            $return['Error'] =  $response->Message;
        } elseif (isset($response->error)) {
            $return['Error'] =  $erMsg;
        }
        return json_encode($return);
    }

    /**
     * This function returns the Current Store Url
     * @return string
     */
    public function getStoreUrl()
    {
        // It will be written to return Current Store Url in multi-store view
        return $this->getRequest()->getServer('SERVER_NAME');
    }
}
