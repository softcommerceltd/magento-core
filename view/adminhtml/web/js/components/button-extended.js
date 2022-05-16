/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

define([
    'Magento_Ui/js/form/components/button',
    'underscore'
], function (Button, _) {
    'use strict';

    return Button.extend({

        /**
         * Show component.
         */
        show: function () {
            this.visible(true);
            return this;
        },

        /**
         * Hide component.
         */
        hide: function () {
            this.visible(false);
            return this;
        },

        /**
         * Toggles visibility
         * @param boolean
         * @returns {*}
         */
        toggleVisible: function (boolean) {
            this.visible(boolean);
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
