/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

define([
    'SoftCommerce_Core/js/grid/columns/column/modal-content',
    'jquery',
    'underscore'
], function (ModalContent, $, _) {
    'use strict';

    return ModalContent.extend({
        defaults: {
            bodyTmpl: 'SoftCommerce_Core/grid/cells/hybrid-message',
            tooltipDelay: 10, // Minimal delay for immediate response
            tooltipTimer: null,
            modalType: 'slide', // Can be 'slide' or 'popup'
            modalClass: 'hybrid-message-modal-wrapper'
        },

        /**
         * Initialize component
         */
        initialize: function () {
            this._super();

            // Bind event handlers
            this.onMouseEnter = this.onMouseEnter.bind(this);
            this.onMouseLeave = this.onMouseLeave.bind(this);

            return this;
        },

        /**
         * Get icon class based on message presence and status
         *
         * @param {Object} record
         * @returns {String}
         */
        getIconClass: function (record) {
            if (!record) {
                return 'far fa-comment empty';
            }
            
            var hasMessages = record[this.index + '_has_messages'],
                status = record[this.index + '_status'] || 'info',
                iconClass;

            if (hasMessages) {
                iconClass = 'far fa-comment-dots';
            } else {
                iconClass = 'far fa-comment empty';
            }

            // Add status-based color class
            iconClass += ' message-icon-' + status;

            return iconClass;
        },

        /**
         * Override getCellLabel to use our icon logic
         *
         * @param {Object} record
         * @returns {String}
         */
        getCellLabel: function (record) {
            return '<i class="' + this.getIconClass(record) + '"></i>';
        },

        /**
         * Get tooltip content
         *
         * @param {Object} record
         * @returns {String}
         */
        getTooltipContent: function (record) {
            if (!record) {
                return '';
            }
            return record[this.index + '_tooltip'] || '';
        },

        /**
         * Get modal content
         *
         * @param {Object} record
         * @returns {String}
         */
        getModalContent: function (record) {
            if (!record) {
                return '<div class="message-modal-empty">No data available</div>';
            }
            return record[this.index + '_modal'] || '';
        },

        /**
         * Override getCellContent to use modal content
         *
         * @param {Object} record
         * @returns {String}
         */
        getCellContent: function (record) {
            return this.getModalContent(record);
        },

        /**
         * Check if record has messages
         *
         * @param {Object} record
         * @returns {Boolean}
         */
        hasMessages: function (record) {
            if (!record) {
                return false;
            }
            return record[this.index + '_has_messages'] || false;
        },

        /**
         * Handle mouse enter event
         *
         * @param {Object} record
         * @param {Event} event
         */
        onMouseEnter: function (record, event) {
            var self = this,
                $target = $(event.currentTarget),
                tooltipContent = this.getTooltipContent(record);

            if (!tooltipContent || !this.hasMessages(record)) {
                return;
            }

            // Clear any existing timer
            if (this.tooltipTimer) {
                clearTimeout(this.tooltipTimer);
            }

            // Delay tooltip display
            this.tooltipTimer = setTimeout(function () {
                self.showTooltip($target, tooltipContent);
            }, this.tooltipDelay);
        },

        /**
         * Handle mouse leave event
         *
         * @param {Object} record
         * @param {Event} event
         */
        onMouseLeave: function (record, event) {
            // Clear timer
            if (this.tooltipTimer) {
                clearTimeout(this.tooltipTimer);
                this.tooltipTimer = null;
            }

            this.hideTooltip();
        },

        /**
         * Show tooltip
         *
         * @param {jQuery} $target
         * @param {String} content
         */
        showTooltip: function ($target, content) {
            var tooltip = $('#hybrid-message-tooltip');

            // Create tooltip if it doesn't exist
            if (tooltip.length === 0) {
                tooltip = $('<div id="hybrid-message-tooltip" class="hybrid-message-tooltip">')
                    .appendTo('body');
            }

            // Set content
            tooltip.html(content);

            // Position tooltip
            var offset = $target.offset(),
                targetHeight = $target.outerHeight(),
                targetWidth = $target.outerWidth(),
                tooltipWidth = tooltip.outerWidth(),
                tooltipHeight = tooltip.outerHeight(),
                windowWidth = $(window).width(),
                windowHeight = $(window).height(),
                scrollTop = $(window).scrollTop(),
                left = offset.left + (targetWidth / 2) - (tooltipWidth / 2),
                top = offset.top - tooltipHeight - 8;

            // Adjust horizontal position if tooltip goes off screen
            if (left < 10) {
                left = 10;
            } else if (left + tooltipWidth > windowWidth - 10) {
                left = windowWidth - tooltipWidth - 10;
            }

            // If tooltip would go above viewport, show below instead
            if (top < scrollTop + 10) {
                top = offset.top + targetHeight + 8;
                tooltip.addClass('tooltip-below');
            } else {
                tooltip.removeClass('tooltip-below');
            }

            tooltip.css({
                left: left + 'px',
                top: top + 'px',
                opacity: 0,
                visibility: 'visible'
            }).animate({
                opacity: 1
            }, 200);
        },

        /**
         * Hide tooltip
         */
        hideTooltip: function () {
            var tooltip = $('#hybrid-message-tooltip');

            if (tooltip.length) {
                tooltip.css({
                    opacity: 0,
                    visibility: 'hidden'
                });
            }
        },

        /**
         * Get field classes
         *
         * @param {Object} record
         * @returns {String}
         */
        getFieldClass: function (record) {
            var classes = [this._super()];

            if (record && this.hasMessages(record)) {
                classes.push('has-messages');
                classes.push('clickable');
            }

            return classes.join(' ');
        },

        /**
         * Check if cell has content (override parent)
         *
         * @param {Object} record
         * @returns {Boolean}
         */
        hasCellContent: function (record) {
            return this.hasMessages(record);
        }
    });
});
