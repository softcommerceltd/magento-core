/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

define([
    'Magento_Ui/js/dynamic-rows/dynamic-rows',
    'underscore',
], function (dynamicRows, _) {
    'use strict';

    return dynamicRows.extend({
        defaults: {
            initialised: false,
        },

        /**
         * @inheritDoc
         */
        initHeader: function () {
            this._super();

            if (false === this.initialised) {
                this.initialised = true;
                return;
            }

            _.each(this.childTemplate.children, function (cell) {
                cell.config.labelVisible = false;
            }, this);

            this.reload();
            this.checkSpinner();
        },

        /**
         * Hides component
         * @returns {*}
         */
        hide: function () {
            this.visible(false);
            return this;
        },

        /**
         * Shows component
         * @returns {*}
         */
        show: function () {
            this.visible(true);
            return this;
        },

        /**
         * Enable component.
         */
        enable: function () {
            this.disabled(false);
            return this;
        },

        /**
         * Disable component.
         */
        disable: function () {
            this.disabled(true);
            return this;
        },
    });
});
