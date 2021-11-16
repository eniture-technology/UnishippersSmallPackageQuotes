var unishippersSmDsFormId = "#unishipperssm-ds-form";
var unishippersSmDsEditFormData = '';

require([
        'jquery',
        'Magento_Ui/js/modal/modal',
        'Magento_Ui/js/modal/confirm',
        'domReady!',
    ],
    function($, modal, confirmation) {

        var addDsModal = $('#unishipperssm-ds-modal');
        var options = {
            type: 'popup',
            modalClass: 'unishipperssm-add-ds-modal',
            responsive: true,
            innerScroll: true,
            title: 'Drop Ship',
            closeText: 'Close',
            focus : unishippersSmDsFormId + ' #unishipperssm-ds-nickname',
            buttons: [{
                text: $.mage.__('Save'),
                class: 'en-btn save-ds-ds',
                click: function (data) {
                    var $this = this;
                    var form_data = unishippersSmGetFormData($, unishippersSmDsFormId);
                    var ajaxUrl = unishippersSmDsAjaxUrl + 'SaveDropship/';

                    if ($(unishippersSmDsFormId).valid() && unishippersSmDsZipMilesValid()) {
                        //If form data is unchanged then close the modal and show updated message
                        if (unishippersSmDsEditFormData !== '' && unishippersSmDsEditFormData === form_data) {
                            jQuery('.unishipperssm-ds-msg').text('Drop ship updated successfully.').show('slow');
                            unishippersSmScrollHideMsg(1, 'html,body', '.ds', '.unishipperssm-ds-msg');
                            addDsModal.modal('closeModal');
                        } else {
                            $.ajax({
                                url: ajaxUrl,
                                type: 'POST',
                                data: form_data,
                                showLoader: true,
                                success: function (data) {
                                    if (unishippersSmDropshipSaveResSettings(data)) {
                                        addDsModal.modal('closeModal');
                                    }
                                },
                                error: function (result) {
                                    console.log('no response !');
                                }
                            });
                        }
                    }
                }
            }],
            keyEventHandlers: {
                tabKey: function () {
                    return;
                },
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
                unishippersSmModalClose(unishippersSmDsFormId, '#ds-', $);
            }
        };


        $('body').on('click', '.unishipperssm-del-ds', function (event) {
            event.preventDefault();
            confirmation({
                title: 'Unishippers Small Package Quotes',
                content: 'Warning! If you delete this location, Drop ship location settings will be disabled against products.',
                actions: {
                    always: function () {},
                    confirm: function () {
                        var dataset = event.currentTarget.dataset;
                        unishippersSmDeleteDropship(dataset.id, unishippersSmDsAjaxUrl);
                    },
                    cancel: function () {}
                }
            });
            return false;
        });


        //Add DS
        $('#unishipperssm-add-ds-btn').on('click', function () {
            var popup = modal(options, addDsModal);
            addDsModal.modal('openModal');
        });

        //Edit DS
        $('body').on('click', '.unishipperssm-edit-ds', function () {
            var dsId = $(this).data("id");
            if (typeof dsId !== 'undefined') {
                unishippersSmEditDropship(dsId, unishippersSmDsAjaxUrl);
                setTimeout(function () {
                    var popup = modal(options, addDsModal);
                    addDsModal.modal('openModal');
                }, 500);
            }
        });

        //Add required to Local Delivery Fee if Local Delivery is enabled
        $(unishippersSmDsFormId + ' #ds-enable-local-delivery').on('change', function () {
            if ($(this).is(':checked')) {
                $(unishippersSmDsFormId + ' #ds-ld-fee').addClass('required');
            } else {
                $(unishippersSmDsFormId + ' #ds-ld-fee').removeClass('required');
            }
        });

        //Get data of Zip Code
        $(unishippersSmDsFormId + ' #unishipperssm-ds-zip').on('change', function () {
            var ajaxUrl = unishippersSmAjaxUrl + 'UnishippersSmallOriginAddress/';
            $(unishippersSmDsFormId + ' #ds-city').val('');
            $(unishippersSmDsFormId + ' #ds-state').val('');
            $(unishippersSmDsFormId + ' #ds-country').val('');
            unishippersSmGetAddressFromZip(ajaxUrl, this, unishippersSmGetDsAddressResSettings);
            $(unishippersSmDsFormId).validation('clearError');
        });
    });

    /**
     * Set Address from zipCode
     * @param {type} data
     * @returns {Boolean}
     */
    function unishippersSmGetDsAddressResSettings(data){
        let id = unishippersSmDsFormId;
        if( data.country === 'US' || data.country === 'CA'){
            var oldNick = jQuery( '#unishipperssm-ds-nickname' ).val();
            var newNick = '';
            var zip     = jQuery( '#unishipperssm-ds-zip' ).val();
            if (data.postcode_localities === 1) {
                jQuery(id + ' .city-select' ).show();
                jQuery(id + ' #ds-actname' ).replaceWith( data.city_option );
                jQuery(id + ' .city-multiselect' ).replaceWith( data.city_option );
                jQuery(id).on('change', '.city-multiselect', function(){
                    var city = jQuery(this).val();
                    jQuery(id + ' #ds-city').val(city);
                    jQuery(id + ' #unishipperssm-ds-nickname' ).val(unishippersSmSetDsNickname(oldNick, zip, city));
                });
                jQuery(id + " #ds-city" ).val( data.first_city );
                jQuery(id + ' #ds-state' ).val( data.state );
                jQuery(id + ' #ds-country' ).val( data.country );
                jQuery(id + ' .city-input' ).hide();
                newNick = unishippersSmSetDsNickname(oldNick, zip, data.first_city);
            }else{
                jQuery(id + ' .city-input' ).show();
                jQuery(id + ' #wh-multi-city' ).removeAttr('value');
                jQuery(id + ' .city-select' ).hide();
                jQuery(id + ' #ds-city' ).val( data.city );
                jQuery(id + ' #ds-state' ).val( data.state );
                jQuery(id + ' #ds-country' ).val( data.country );
                newNick = unishippersSmSetDsNickname(oldNick, zip, data.city);
            }
            jQuery(id + ' #unishipperssm-ds-nickname' ).val(newNick);
        }else if (data.msg){
            jQuery('.unishipperssm-ds-er-msg').text(data.msg).show('slow');
            unishippersSmScrollHideMsg(2, '', '.unishipperssm-ds-er-msg', '.unishipperssm-ds-er-msg');
        }
        return true;
    }


    function unishippersSmDsZipMilesValid() {
        let  id = unishippersSmDsFormId;
        var enable_instore_pickup = jQuery(id + " #ds-enable-instore-pickup").is(':checked');
        var enable_local_delivery = jQuery(id + " #ds-enable-local-delivery").is(':checked');
        if (enable_instore_pickup || enable_local_delivery) {
            var instore_within_miles = jQuery(id + " #ds-within-miles").val();
            var instore_postal_code  = jQuery(id + " #ds-postcode-match").val();
            var ld_within_miles      = jQuery(id + " #ds-ld-within-miles").val();
            var ld_postal_code       = jQuery(id + " #ds-ld-postcode-match").val();

            switch(true){
                case (enable_instore_pickup && (instore_within_miles.length == 0 && instore_postal_code.length == 0)):
                    jQuery(id + ' .ds-instore-miles-postal-err').show('slow');
                    unishippersSmScrollHideMsg(2, '', id + ' #ds-is-heading-left', '.ds-instore-miles-postal-err');
                    return false;

                case (enable_local_delivery && (ld_within_miles.length == 0 && ld_postal_code.length == 0)):
                    jQuery(id + ' .ds-local-miles-postals-err').show('slow');
                    unishippersSmScrollHideMsg(2, '', id + ' #ds-ld-heading-left', '.ds-local-miles-postals-err');
                    return false;

            }
        }
        return true;
    }


    function unishippersSmDropshipSaveResSettings(data){
        if (data.insert_qry == 1) {
            jQuery('.unishipperssm-ds-msg').text(data.msg).show('slow');

            jQuery('#append-dropship tr:last').after(
                '<tr id="row_' + data.id + '" data-id="' + data.id+ '">' +
                '<td>'+data.nickname+'</td>' +
                unishippersSmGetRowData(data, 'ds') + '</tr>');

            unishippersSmScrollHideMsg(1, 'html,body', '.ds', '.unishipperssm-ds-msg');

        } else if(data.update_qry == 1) {
            jQuery('.unishipperssm-ds-msg').text(data.msg).show('slow');

            jQuery('tr[id=row_' + data.id + ']').html('<td>'+data.nickname+'</td>' + unishippersSmGetRowData(data, 'ds'));

            unishippersSmScrollHideMsg(1, 'html,body', '.ds', '.unishipperssm-ds-msg');

        }else{
            jQuery('.unishipperssm-ds-er-msg').text(data.msg).show('slow');
            unishippersSmScrollHideMsg(2, '', '.unishipperssm-ds-er-msg', '.unishipperssm-ds-er-msg');
            return false;
        }

        return true;
    }

    function unishippersSmEditDropship(dataId, ajaxUrl)
    {
        ajaxUrl = ajaxUrl + 'EditDropship/';
        var parameters = {
            'action'    : 'edit_dropship',
            'edit_id'   : dataId
        };

        unishippersSmAjaxRequest(parameters, ajaxUrl, unishippersSmDropshipEditResSettings);
        return false;
    }

    function unishippersSmDropshipEditResSettings(data){
        let id = unishippersSmDsFormId;
        if (data[0]) {
            jQuery(id + ' #ds-edit-form-id' ).val( data[0].warehouse_id );
            jQuery(id + ' #unishipperssm-ds-zip' ).val( data[0].zip );
            jQuery(id + ' #unishipperssm-ds-nickname' ).val( data[0].nickname );
            jQuery(id + ' .city-select' ).hide();
            jQuery(id + ' .city-input' ).show();
            jQuery(id + ' #ds-city' ).val( data[0].city );
            jQuery(id + ' #ds-state' ).val( data[0].state );
            jQuery(id + ' #ds-country' ).val( data[0].country );

            if (unishippersSmAdvancePlan) {
                // Load instore pickup and local delivery data
                if((data[0].in_store != null && data[0].in_store != 'null')
                    || (data[0].local_delivery != null && data[0].local_delivery != 'null')){
                    unishippersSmSetInspAndLdData(data[0], '#ds-');
                }
            }

            unishippersSmDsEditFormData = unishippersSmGetFormData(jQuery, unishippersSmDsFormId);
        }
        return true;
    }

    function unishippersSmDeleteDropship(deleteid, ajaxUrl)
    {
        ajaxUrl = ajaxUrl + 'DeleteDropship/';
        var parameters = {
            'action'    : 'delete_dropship',
            'delete_id' : deleteid
        };
        unishippersSmAjaxRequest(parameters, ajaxUrl, unishippersSmDropshipDeleteResSettings);

        return false;
    }

    function unishippersSmDropshipDeleteResSettings(data){
        if (data.qryResp == 1) {
            jQuery('#row_'+data.deleteID).remove();
            jQuery('.unishipperssm-ds-msg').text(data.msg).show('slow');
            unishippersSmScrollHideMsg(1, 'html,body', '.ds', '.unishipperssm-ds-msg');
        }
        return true;
    }

    function unishippersSmSetDsNickname(oldNick, zip, city) {
        var nickName = '';
        var curNick = 'DS_'+zip+'_'+city;
        var pattern = /DS_[0-9 a-z A-Z]+_[a-z A-Z]*/;
        var regex = new RegExp(pattern, 'g');
        if(oldNick !== ''){
            nickName =  regex.test(oldNick) ? curNick : oldNick;
        }
        return nickName;
    }
