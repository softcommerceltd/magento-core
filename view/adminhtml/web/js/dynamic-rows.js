/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

define([
    'Magento_Ui/js/dynamic-rows/dynamic-rows',
    'mageUtils',
    'underscore',
], function (dynamicRows, utils, _) {
    'use strict';

    return dynamicRows.extend({
        defaults: {
            initialised: false,
            template: 'SoftCommerce_Core/dynamic-rows/default',
            listens: {
                isUseDefault: 'toggleUseDefault'
            },
        },

        /**
         * @returns {Object} Chainable.
         */
        initialize: function () {
            this._super()
            this.setInitialScopeValue()
            return this;
        },

        /**
         * @returns {Object} Chainable.
         */
        initConfig: function () {
            this._super();
            this.configureScopeData();
            return this;
        },

        /**
         * Calls 'initObservable' of parent
         *
         * @returns {Object} Chainable.
         */
        initObservable: function () {
            this._super()
                .observe('isUseDefault');

            return this;
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
         * Configure scope data.
         */
        configureScopeData: function () {
            const scope = this.name.split('.');
            const name = scope.length > 3 ? scope.slice(-3) : scope;
            let inputNameUseDefault = [...name];
            inputNameUseDefault.unshift('use_default');

            _.extend(this, {
                inputNameUseDefault: utils.serializeName(inputNameUseDefault.join('.'))
            });
        },

        /**
         * Sets initial scope value of the element and subscribes.
         *
         * @returns {Abstract} Chainable.
         */
        setInitialScopeValue: function () {
            this.isUseDefault(this.disabled());
            return this;
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

        /**
         * @param {Boolean} state
         */
        toggleUseDefault: function (state) {
            this.disabled(state);

            if (this.source && this.service && this.service.template) {
                this.source.set('data.' + this.inputNameUseDefault, Number(state));
            }
        },
    });
});
