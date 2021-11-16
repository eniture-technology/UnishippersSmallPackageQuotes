    require(["jquery", "domReady!"], function ($) {
        /* Test Connection Validation */
        unishippersSmAddTestConnTitle($);
        $('#unishipperssm-test-conn').click(function () {
            if ($('#config-edit-form').valid()) {
                let ajaxURL = $(this).attr('connAjaxUrl');
                unishippersSmTestConnAjaxCall($, ajaxURL);
            }
        return false;
        });

    });

    /**
     * Assign Title to inputs
     */
    function unishippersSmAddTestConnTitle($)
    {
        let sectionId = '#UnishippersSmConnSetting_first_';
        let data = {'upsAccountNumber' : 'UPS Account Number',
                    'username' : 'Username',
                    'password' : 'Password',
                    'unishippersCustomerNumber' : 'Unishippers Customer Number',
                    'unishippersRequestKey' : 'Request Key',
                    'licenseKey' : 'Plugin License Key'
                    };

        for (let id in data) {
            let title = data[id];
            $(sectionId+id).attr('title', title);
        }
    }

    /**
     * Test connection ajax call
     * @param {object} $
     * @param {string} ajaxURL
     * @returns {function}
     */
    function unishippersSmTestConnAjaxCall($, ajaxURL){
        let sectionId = '#UnishippersSmConnSetting_first_';
        let credentials = {
            upsAccountNumber    : $(sectionId+'upsAccountNumber').val(),
            username            : $(sectionId+'username').val(),
            password            : $(sectionId+'password').val(),
            unishippersCustomerNumber   : $(sectionId+'unishippersCustomerNumber').val(),
            unishippersRequestKey   : $(sectionId+'unishippersRequestKey').val(),
            pluginLicenceKey    : $(sectionId+'licenseKey').val()
        };

        unishippersSmAjaxRequest(credentials, ajaxURL, unishippersSmTestConnResponse);

    }

    /**
     * Handel response
     * @param {object} data
     * @returns {void}
     */
    function unishippersSmTestConnResponse(data){
        let elemId = '#unishipperssm-conn-response';
        let msgClass, msgText =  '';
        if (data.Error) {
            msgClass = 'error';
            msgText = data.Error;
        } else {
            msgClass = 'success';
            msgText = data.Success;
        }
        unishippersSmResponseMessage(elemId, msgClass, msgText);
    }

    /**
     * Test connection ajax call
     * @param {object} $
     * @param {string} ajaxURL
     * @returns {function}
     */
    function unishippersSmPlanRefresh(e){
        let ajaxURL = e.getAttribute('planRefAjaxUrl');
        let parameters = {};
        unishippersSmAjaxRequest(parameters, ajaxURL, unishippersSmPlanRefreshResponse);
    }

    /**
     * Handel response
     * @param {object} data
     * @returns {void}
     */
    function unishippersSmPlanRefreshResponse(data){}
