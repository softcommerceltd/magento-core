/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

define([
    'underscore',
    'jquery',
    'Magento_Ui/js/form/components/group',
], function (_, $, Group) {
    'use strict';

    return Group.extend({
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
            return false;
        },
    });
});
