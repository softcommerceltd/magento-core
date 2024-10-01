/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

/**
 * @api
 */
define([
    'Magento_Ui/js/grid/columns/column',
    'uiRegistry',
    'underscore'
], function (Column, uiRegistry, _) {
    'use strict';

    return Column.extend({
        defaults: {
            bodyTmpl: 'SoftCommerce_Core/grid/cells/html-status'
        },

        /**
         * @param {Object} record
         * @returns {*}
         */
        getCellContent: function (record) {
            let elementClass = this.getElementClass(record),
                elementStatus = this.getLabel(record);

            if (elementStatus) {
                elementClass += ' status-' + elementStatus;
            }

            return '<i class="' + elementClass + '"></i>';
        },

        /**
         * @param {Object} record
         * @returns {string}
         */
        getElementClass: function (record) {
            let attribute = '';
            if (record['cell_attribute']) {
                attribute = record['cell_attribute'];
            }
            return attribute;
        },

        /**
         * @param {Object} record
         * @returns {string}
         */
        getElementStatus: function (record) {
            let attribute = '';
            if (record['cell_attribute']) {
                attribute = record['cell_attribute'];
            }
            return attribute;
        }
    });
});
