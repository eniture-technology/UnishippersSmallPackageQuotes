    var unishippersSmWhFormId = "#unishipperssm-wh-form";
    var unishippersSmWhEditFormData = '';
    require(
        [
            'jquery',
            'Magento_Ui/js/modal/modal',
            'domReady!'
        ],
        function($, modal) {

            let addWhModal = $('#unishipperssm-wh-modal');
            let formId = unishippersSmWhFormId;
            let options = {
                type: 'popup',
                modalClass: 'unishipperssm-add-wh-modal',
                responsive: true,
                innerScroll: true,
                title: 'Warehouse',
                closeText: 'Close',
                focus : formId + ' #unishipperssm-wh-zip',
                buttons: [{
                    text: $.mage.__('Save'),
                    class: 'en-btn save-wh-ds',
                    click: function (data) {
                        var $this = this;
                        var formData = unishippersSmGetFormData($, formId);
                        var ajaxUrl = unishippersSmAjaxUrl + 'SaveWarehouse/';
                        console.log('ajaxUrl');
                        console.log(ajaxUrl);
                        if ($(formId).valid() && unishippersSmZipMilesValid()) {
                            //If form data is unchanged then close the modal and show updated message
                            if (unishippersSmWhEditFormData !== '' && unishippersSmWhEditFormData === formData) {
                                jQuery('.unishipperssm-wh-msg').text('Warehouse updated successfully.').show('slow');
                                unishippersSmScrollHideMsg(1, 'html,body', '.wh-text', '.unishipperssm-wh-msg');
                                addWhModal.modal('closeModal');
                            } else {
                                $.ajax({
                                    url: ajaxUrl,
                                    type: 'POST',
                                    data: formData,
                                    showLoader: true,
                                    success: function (data) {
                                        if (unishippersSmWarehouseSaveResSettings(data)) {
                                            addWhModal.modal('closeModal');
                                        }
                                    },
                                    error: function (result) {
                                        console.log(result);
                                        console.log('no response !');
                                    }
                                });
                            }
                        }
                    }
                }],
                keyEventHandlers: {
                    tabKey: function () { },
                    /**
                     * Escape key press handler,
                     * close modal window
                     */
                    escapeKey: function () {
                        if (this.options.isOpen && this.modal.find(document.activeElement).length ||
                            this.options.isOpen && this.modal[0] === document.activeElement) {
                            this.closeModal();
                        }
                    }
                },
                closed: function () {
                    unishippersSmModalClose(formId, '#', $);
                }
            };

            //Add WH
            $('#unishipperssm-add-wh-btn').on('click', function () {
                var popup = modal(options, addWhModal);
                addWhModal.modal('openModal');
            });

            //Edit WH
            $('body').on('click', '.unishipperssm-edit-wh', function () {
                var whId = $(this).data("id");
                if (typeof whId !== 'undefined') {
                    unishippersSmEditWarehouse(whId, unishippersSmAjaxUrl);
                    setTimeout(function () {
                        var popup = modal(options, addWhModal);
                        addWhModal.modal('openModal');
                    }, 500);
                }
            });

            //Delete WH
            $('body').on('click', '.unishipperssm-del-wh', function () {
                var whId = $(this).data("id");
                if (typeof whId !== 'undefined') {
                    unishippersSmDeleteWarehouse(whId, unishippersSmAjaxUrl);
                }
            });

            //Add required to Local Delivery Fee if Local Delivery is enabled
            $(formId + ' #enable-local-delivery').on('change', function () {
                if ($(this).is(':checked')) {
                    $(formId + ' #ld-fee').addClass('required');
                } else {
                    $(formId + ' #ld-fee').removeClass('required');
                }
            });

            //Get data of Zip Code
            $(formId + ' #unishipperssm-wh-zip').on('change', function () {
                let ajaxUrl = unishippersSmAjaxUrl + 'UnishippersSmallOriginAddress/';
                $(formId + ' #wh-origin-city').val('');
                $(formId + ' #wh-origin-state').val('');
                $(formId + ' #wh-origin-country').val('');
                unishippersSmGetAddressFromZip(ajaxUrl, this, unishippersSmGetAddressResSettings);
                $(formId).validation('clearError');
            });
        }
    );


    function unishippersSmGetAddressResSettings(data){
        let id = unishippersSmWhFormId;
        if( data.country === 'US' || data.country === 'CA') {
            if (data.postcode_localities === 1) {
                jQuery(id+' .city-select').show();
                jQuery(id+' #actname').replaceWith(data.city_option);
                jQuery(id+' .city-multiselect').replaceWith(data.city_option);
                jQuery(id).on('change', '.city-multiselect',function () {
                    var city = jQuery(this).val();
                    jQuery(id+' #wh-origin-city').val(city);
                });
                jQuery(id+" #wh-origin-city").val(data.first_city);
                jQuery(id+" #wh-origin-state").val(data.state);
                jQuery(id+" #wh-origin-country").val(data.country);
                jQuery(id+' .city-input').hide();
            } else {
                jQuery(id+' .city-input').show();
                jQuery(id+' #wh-multi-city').removeAttr('value');
                jQuery(id+' .city-select').hide();
                jQuery(id+" #wh-origin-city").val(data.city);
                jQuery(id+" #wh-origin-state").val(data.state);
                jQuery(id+" #wh-origin-country").val(data.country);
            }
        }else if (data.msg){
            jQuery(id+' .unishipperssm-wh-er-msg').text(data.msg).show('slow');
            unishippersSmScrollHideMsg(2, '', '.unishipperssm-wh-er-msg', '.unishipperssm-wh-er-msg');
        }
        return true;
    }


    function unishippersSmZipMilesValid() {
        let id = unishippersSmWhFormId;
        var enable_instore_pickup = jQuery(id + " #enable-instore-pickup").is(':checked');
        var enable_local_delivery = jQuery(id + " #enable-local-delivery").is(':checked');
        if (enable_instore_pickup || enable_local_delivery) {
            var instore_within_miles = jQuery(id + " #within-miles").val();
            var instore_postal_code  = jQuery(id + " #postcode-match").val();
            var ld_within_miles      = jQuery(id + " #ld-within-miles").val();
            var ld_postal_code       = jQuery(id + " #ld-postcode-match").val();

            switch (true) {
                case (enable_instore_pickup && (instore_within_miles.length == 0 && instore_postal_code.length == 0)):
                    jQuery(id +' .wh-instore-miles-postal-err').show('slow');
                    unishippersSmScrollHideMsg(2, '', id + ' #wh-is-heading-left', '.wh-instore-miles-postal-err');
                    return false;

                case (enable_local_delivery && (ld_within_miles.length == 0 && ld_postal_code.length == 0)):
                    jQuery(id + ' .wh-local-miles-postals-err').show('slow');
                    unishippersSmScrollHideMsg(2, '', id + ' #wh-ld-heading-left', '.wh-local-miles-postals-err');
                    return false;
            }
        }
        return true;
    }

    function unishippersSmWarehouseSaveResSettings(data) {
        unishippersSmAddWarehouseRestriction(data.canAddWh);

        if (data.insert_qry == 1) {
            jQuery('.unishipperssm-wh-msg').text(data.msg).show('slow');

            jQuery('#append-warehouse tr:last').after(
                '<tr id="row_' + data.id + '" data-id="' + data.id+ '">' + unishippersSmGetRowData(data, 'wh') + '</tr>');

            unishippersSmScrollHideMsg(1, 'html,body', '.wh-text', '.unishipperssm-wh-msg');

        } else if (data.update_qry == 1) {
            jQuery('.unishipperssm-wh-msg').text(data.msg).show('slow');

            jQuery('tr[id=row_' + data.id + ']').html(unishippersSmGetRowData(data, 'wh'));

            unishippersSmScrollHideMsg(1, 'html,body', '.wh-text', '.unishipperssm-wh-msg');
        } else {
            jQuery('.unishipperssm-wh-er-msg').text(data.msg).show('slow');
            //to be changed
            unishippersSmScrollHideMsg(2, '', '.unishipperssm-wh-er-msg', '.unishipperssm-wh-er-msg');
            return false;
        }
        return true;
    }

    /**
     * Edit warehouse
     * @param {type} dataId
     * @param {type} ajaxUrl
     * @returns {Boolean}
     */
    function unishippersSmEditWarehouse(dataId, ajaxUrl) {
        ajaxUrl = ajaxUrl + 'EditWarehouse/';
        var parameters = {
            'action': 'edit_warehouse',
            'edit_id': dataId
        };

        unishippersSmAjaxRequest(parameters, ajaxUrl, unishippersSmWarehouseEditResSettings);
        return false;
    }

    function unishippersSmWarehouseEditResSettings(data) {
        let id = unishippersSmWhFormId;
        if (data[0]) {
            jQuery(id+' #edit-form-id').val(data[0].warehouse_id);
            jQuery(id+' #unishipperssm-wh-zip').val(data[0].zip);
            jQuery(id+' .city-select').hide();
            jQuery(id+' .city-input').show();
            jQuery(id+' #wh-origin-city').val(data[0].city);
            jQuery(id+' #wh-origin-state').val(data[0].state);
            jQuery(id+' #wh-origin-country').val(data[0].country);

            if (unishippersSmAdvancePlan) {
                // Load instorepikup and local delivery data
                if ((data[0].in_store != null && data[0].in_store != 'null')
                    || (data[0].local_delivery != null && data[0].local_delivery != 'null')) {
                    unishippersSmSetInspAndLdData(data[0], '#');
                }
            }
            unishippersSmWhEditFormData = unishippersSmGetFormData(jQuery, unishippersSmWhFormId);
        }
        return true;
    }

    /**
     * Delete selected Warehouse
     * @param {int} dataId
     * @param {string} ajaxUrl
     * @returns {boolean}
     */
    function unishippersSmDeleteWarehouse(dataId, ajaxUrl) {
        ajaxUrl = ajaxUrl + 'DeleteWarehouse/';
        var parameters = {
            'action': 'delete_warehouse',
            'delete_id': dataId
        };
        unishippersSmAjaxRequest(parameters, ajaxUrl, unishippersSmWarehouseDeleteResSettings);
        return false;
    }

    function unishippersSmWarehouseDeleteResSettings(data) {

        if (data.qryResp == 1) {
            jQuery('#row_' + data.deleteID).remove();
            unishippersSmAddWarehouseRestriction(data.canAddWh);
            jQuery('.unishipperssm-wh-msg').text(data.msg).show('slow');
            unishippersSmScrollHideMsg(1, 'html,body', '.wh-text', '.unishipperssm-wh-msg');
        }
        return true;
    }
