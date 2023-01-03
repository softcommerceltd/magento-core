<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Core\Model\Source;

/**
 * @inheritDoc
 */
class Status implements StatusInterface
{
    public const CRITICAL = 'critical';
    public const ERROR = 'error';
    public const FAILED = 'failed';
    public const MISSED = 'missed';
    public const PENDING = 'pending';
    public const PENDING_COLLECT = 'pending_collect';
    public const COMPLETE = 'complete';
    public const RUNNING = 'running';
    public const PROCESSING = 'processing';
    public const SUCCESS = 'success';
    public const NOTICE = 'notice';
    public const SKIPPED = 'skipped';
    public const STOPPED = 'stopped';
    public const CREATED = 'created';
    public const UPDATED = 'updated';
    public const UNKNOWN = 'unknown';
    public const WARNING = 'warning';
    public const NEW = 'new';

    /**
     * @var array
     */
    private $options;

    /**
     * @inheritDoc
     */
    public function getOptions(): array
    {
        if (null === $this->options) {
            $this->options = [
                self::CRITICAL => __('Critical'),
                self::ERROR => __('Error'),
                self::FAILED => __('Failed'),
                self::NOTICE => __('Notice'),
                self::MISSED => __('Missed'),
                self::PENDING => __('Pending'),
                self::PENDING_COLLECT => __('Pending Collect'),
                self::COMPLETE => __('Complete'),
                self::RUNNING => __('Running'),
                self::PROCESSING => __('Processing'),
                self::SUCCESS => __('Success'),
                self::SKIPPED => __('Skipped'),
                self::STOPPED => __('Stopped'),
                self::CREATED => __('Created'),
                self::UPDATED => __('Updated'),
                self::UNKNOWN => __('Unknown'),
                self::WARNING => __('Warning'),
                self::NEW => __('New')
            ];
        }
        return $this->options;
    }

    /**
     * @inheritDoc
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => self::CRITICAL, 'label' => __('Critical')],
            ['value' => self::ERROR, 'label' => __('Error')],
            ['value' => self::WARNING, 'label' => __('Warning')],
            ['value' => self::PENDING, 'label' => __('Pending')],
            ['value' => self::COMPLETE, 'label' => __('Complete')],
            ['value' => self::PROCESSING, 'label' => __('Processing')],
            ['value' => self::SKIPPED, 'label' => __('Skipped')],
            ['value' => self::STOPPED, 'label' => __('Stopped')]
        ];
    }

    /**
     * @return array
     */
    public function toOptionArrayScheduleStatus(): array
    {
        return [
            ['value' => self::ERROR, 'label' => __('Error')],
            ['value' => self::MISSED, 'label' => __('Missed')],
            ['value' => self::PENDING, 'label' => __('Pending')],
            ['value' => self::SUCCESS, 'label' => __('Success')],
            ['value' => self::RUNNING, 'label' => __('Running')]
        ];
    }

    /**
     * @return array
     */
    public function toOptionArrayScheduleHistoryStatus(): array
    {
        return [
            ['value' => self::ERROR, 'label' => __('Error')],
            ['value' => self::PENDING, 'label' => __('Pending')],
            ['value' => self::COMPLETE, 'label' => __('Complete')],
            ['value' => self::PROCESSING, 'label' => __('Processing')]
        ];
    }

    /**
     * @return array
     */
    public function toOptionHashScheduleHistoryStatuses(): array
    {
        $options =[];
        foreach ($this->toOptionArrayScheduleHistoryStatus() as $index => $item) {
            $options[$item['value']] = $item['label'];
        }

        return $options;
    }

    /**
     * @return array
     */
    public function toOptionArrayImportExportStatus(): array
    {
        return [
            ['value' => self::ERROR, 'label' => __('Error')],
            ['value' => self::WARNING, 'label' => __('Warning')],
            ['value' => self::NOTICE, 'label' => __('Notice')],
            ['value' => self::SKIPPED, 'label' => __('Skipped')],
            ['value' => self::PENDING, 'label' => __('Pending')],
            ['value' => self::PROCESSING, 'label' => __('Processing')],
            ['value' => self::COMPLETE, 'label' => __('Complete')]
        ];
    }

    /**
     * @return array
     */
    public function getImportExportStatusOptionsArray(): array
    {
        $options = [];
        foreach ($this->toOptionArrayImportExportStatus() as $item) {
            $options[$item['value']] = $item['label'];
        }

        return $options;
    }

    /**
     * @return array
     */
    public function toOptionArrayExportStatus(): array
    {
        return [
            ['value' => self::FAILED, 'label' => __('Failed')],
            ['value' => self::PENDING, 'label' => __('Pending')],
            ['value' => self::PROCESSING, 'label' => __('Processing')],
            ['value' => self::CREATED, 'label' => __('Exported')],
            ['value' => self::UPDATED, 'label' => __('Updated')]
        ];
    }

    /**
     * @return array
     */
    public function toOptionHashImportExportStatus(): array
    {
        $options = [];
        foreach ($this->toOptionArrayImportExportStatus() as $item) {
            $options[$item['value']] = $item['label'];
        }

        return $options;
    }
}
