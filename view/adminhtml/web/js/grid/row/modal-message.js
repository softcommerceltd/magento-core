/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
define([
    'jquery',
    'Magento_Ui/js/modal/alert',
    'Magento_Ui/js/modal/modal'
], function($, alert) {
    'use strict';

    $.widget('plenty.gridRowModalMessage', {
        options: {
            html: null,
            url: null,
            gridPopupBtn: null,
            gridMassActionJsObj: null,
            gridJsObject: null
        },

        _create: function() {
            this._bind();
        },

        _bind: function() {
            let self = this;
            self.element.on('click', function() {
                self._execute();
            });
        },

        _execute: function() {
            let self = this;
            let gridRowModalMessageWidget = $('<div>').html(self.options.html).modal({
                modalClass: 'product-chooser-widget',
                responsive: true,
                innerScroll: true,
                title: $.mage.__('List of response messages.'),
                type: 'slide',
                buttons: [{
                    text: $.mage.__('Close'),
                    class: '',
                    click: function () {
                        this.closeModal();
                    }
                }]
            });

            gridRowModalMessageWidget.modal('openModal');
        },

        _getSelectedIds: function() {
            return this._getGridMassActionJsObj().getCheckedValues();
        },

        _getGridJsObject: function() {
            return window[this.options.gridJsObject];
        },

        _getGridMassActionJsObj: function() {
            return window[this.options.gridMassActionJsObj];
        },

        _buildMessage: function (response) {
            if (!response) {
                return false;
            }
            let self = this;
            let html = '<ul>';
            $.each(response, function (key, message) {
                self.options.title = $.mage.__(key.charAt(0).toUpperCase() + key.slice(1));
                html += '<li class="'+key+'">' + $.mage.__(message) + '</li>';
            });
            return html += '</ul>';
        },

    });

    return $.plenty.gridRowModalMessage;
});