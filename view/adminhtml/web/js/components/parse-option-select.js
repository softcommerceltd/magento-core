/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

define([
    'Magento_Ui/js/form/element/select',
    'underscore',
], function (Select, _) {
    'use strict';

    return Select.extend({

        /**
         * @param {Object} response - Response data object.
         * @returns {Object}
         */
        setParsed: function (response) {
            if (response.error) {
                return this;
            }

            let option = this.parseOption(response);
            this.updateSelect(option);
        },

        /**
         * @param {Object} response - Option object.
         * @returns {Object}
         */
        parseOption: function (response) {
            return {
                value: response.params.id,
                label: response.params.label,
                labeltitle: response.params.label,
            };
        },

        /**
         * Updates select with new option value.
         * @param option
         */
        updateSelect: function (option) {
            let existingOptions = this.options(),
                optionIndex = this.getExistingOptionIndex(option);

            if (_.isUndefined(optionIndex)) {
                existingOptions.splice(optionIndex, 1, option);
                this.value(null);
            } else {
                existingOptions.push(option);
            }

            this.options(existingOptions);
            this.value(option.value);
        },

        /**
         * @param {Object} option
         * @returns {Number|0}
         */
        getExistingOptionIndex: function (option) {
            _.each(this.options(), function (opt, id) {
                if (option.value === parseInt(opt.value)) {
                    return id;
                }
            });
        }
    });
});

