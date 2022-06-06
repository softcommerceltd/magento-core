/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

define([
    'Magento_Ui/js/form/element/ui-select',
    'underscore',
], function (Select, _) {
    'use strict';

    return Select.extend({

        /**
         * @param {Object} response - Response data object.
         * @returns {Object}
         */
        setParsed: function (response) {
            if (response.error || !response.params) {
                return this;
            }

            if (!_.isArray(response.params)) {
                response.params = [response.params];
            }

            let self = this;
            response.params.each(function (item) {
                let option = self.parseOption(item);
                self.updateSelect(option);
            });
        },

        /**
         * @param {Object} response - Option object.
         * @returns {Object}
         */
        parseOption: function (response) {
            return {
                value: response.id,
                label: response.label
            };
        },

        /**
         * Updates select with new option value.
         * @param option
         */
        updateSelect: function (option) {
            let existingOptions = this.options(),
                optionIndex = this.getExistingOptionIndex(option);

            if (!_.isUndefined(optionIndex)) {
                existingOptions.splice(optionIndex, 1, option);
                // this.value(null);
            } else {
                existingOptions.push(option);
            }

            this.cacheOptions.plain = existingOptions;
            this.options(existingOptions);
            this.toggleOptionSelected(option);
        },

        /**
         * @param {Object} data
         * @returns {boolean}
         */
        getExistingOptionIndex: function (data) {
            let index;
            _.each(this.cacheOptions.plain, function (opt, id) {
                if (data.value === parseInt(opt.value)) {
                    index = id;
                }
            });

            return index;
        },

        /**
         * @param {Object} response
         */
        triggerReloadItem: function (response) {
            if (!response.error && response.params.client_id) {
                this.value(response.params.client_id);
            }
        },
    });
});

