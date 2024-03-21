/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

define([
    'Magento_Ui/js/form/element/abstract',
    'mageUtils',
    'underscore',
], function (Abstract, utils, _) {
    'use strict';

    return Abstract.extend({

        initConfig: function () {
            this._super();
            this.configureScopeData();
            return this;
        },

        /**
         * Configure scope data.
         */
        configureScopeData: function () {
            const scope = this.dataScope.split('.');
            const name = scope.length > 1 ? scope.slice(1) : scope;

            let inputNameUseDefault = [...name];
            inputNameUseDefault.unshift('use_default');

            _.extend(this, {
                inputNameUseDefault: utils.serializeName(inputNameUseDefault.join('.'))
            });
        },

        /**
         * @param {Boolean} state
         */
        toggleUseDefault: function (state) {
            this.disabled(state);

            if (this.source && this.hasService()) {
                this.source.set('data.' + this.inputNameUseDefault, Number(state));
            }
        }
    });
});

