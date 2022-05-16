/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
define([
    'jquery',
    'Magento_Ui/js/modal/alert',
    'mage/translate',
], function ($, alert) {
    'use strict';

    $.widget('plenty.gridMassActionApi', {
        options: {
            url: '',
            gridObject: '',
            title: ''
        },

        _create: function () {
            this._bind();
        },

        _bind: function () {
            let self = this;
            self.element.on('click', function () {
                self._ajaxSubmit();
            });
        },

        _ajaxSubmit: function () {
            let self = this;
            $.ajax({
                url: self.options.url,
                method: 'GET',
                dataType: 'json',
                isAjax: true,
                showLoader: true,
                data: {
                    isAjax: 1
                },
            }).done(function (response) {
                let message = self._buildMessage(response);
                alert({
                    title: self.options.title,
                    content: message,
                    modalClass: 'alert',
                    actions: {
                        always: function () {
                            if (self.options.gridObject) {
                                self._gridJsObject().resetFilter();
                            }
                        }
                    }
                });
            }).fail(function (jqXHR, textStatus) {
                console.log(textStatus);
            });
        },

        _buildMessage: function (response) {
            if (!response) {
                return false;
            }
            let self = this;
            let html = '<ul>';
            $.each(response, function (status, messages) {
                $.each(messages, function ($key, message) {
                    self.options.title = $.mage.__(status.charAt(0).toUpperCase() + status.slice(1));
                    html += '<li class="'+status+'">' + $.mage.__(message) + '</li>';
                });
            });
            return html += '</ul>';
        },

        _gridJsObject: function () {
            return window[this.options.gridObject];
        },
    });

    return $.plenty.gridMassActionApi;
});
