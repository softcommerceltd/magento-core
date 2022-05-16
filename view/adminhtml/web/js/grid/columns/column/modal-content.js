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
            bodyTmpl: 'SoftCommerce_Core/grid/cells/html-modal'
        },

        /**
         * @param {Object} record
         * @returns {*}
         */
        getCellLabel: function (record) {
            let elementClass = '',
                elementStatus = this.getCellStatus(record);

            if (this.hasCellContent(record)) {
                elementClass = 'far fa-comment-dots';
            } else {
                elementClass = 'far fa-comment empty';
            }

            if (elementStatus) {
                elementClass += ' status-' + this.getCellStatus(record);
            }

            return '<i class="' + elementClass + '"></i>';
        },

        /**
         * @param {Object} record
         * @returns {*}
         */
        getCellContent: function (record) {
            if (_.isUndefined(record)) {
                return '';
            }

            return record[this.index];
        },

        /**
         * @param {Object} record
         * @returns {string}
         */
        getCellStatus: function (record) {
            let status = '';
            if (record['cell_status']) {
                status = record['cell_status'];
            }
            return status;
        },

        /**
         * @returns {string}
         */
        getModalTitle: function () {
            return this.label;
        },

        /**
         * @param {Object} record
         * @returns {boolean}
         */
        hasCellContent: function (record) {
            if (_.isUndefined(record)) {
                return false;
            }

            return !_.isNull(this.getLabel(record)) && this.getLabel(record).length;
        }
    });
});
