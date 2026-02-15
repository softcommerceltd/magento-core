<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Core\Model;

use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Store\Model\Store;
use SoftCommerce\Core\Api\EmailNotificationInterface;

/**
 * @inheritDoc
 *
 * Email notification service using global email log configuration.
 * Sends emails using the configured template from Core Configuration.
 */
class EmailNotification implements EmailNotificationInterface
{
    private const XML_PATH_IS_ACTIVE = 'softcommerce_core/dev/is_active_mail_log';
    private const XML_PATH_RECIPIENT = 'softcommerce_core/dev/mail_log_email';
    private const XML_PATH_EMAIL_IDENTITY = 'softcommerce_core/dev/mail_log_email_identity';
    private const XML_PATH_EMAIL_TEMPLATE = 'softcommerce_core/dev/mail_log_email_template';

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param StateInterface $inlineTranslation
     * @param TransportBuilder $transportBuilder
     */
    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly StateInterface $inlineTranslation,
        private readonly TransportBuilder $transportBuilder
    ) {
    }

    /**
     * @inheritDoc
     */
    public function isEnabled(): bool
    {
        return (bool) $this->scopeConfig->getValue(self::XML_PATH_IS_ACTIVE);
    }

    /**
     * @inheritDoc
     */
    public function send(string $subject, string $content, array $additionalData = []): void
    {
        if (!$this->isEnabled()) {
            return;
        }

        $recipient = $this->getRecipient();
        if (!$recipient) {
            throw new LocalizedException(__('Email notification recipient is not configured.'));
        }

        $templateId = $this->scopeConfig->getValue(self::XML_PATH_EMAIL_TEMPLATE);
        if (!$templateId) {
            throw new LocalizedException(__('Email notification template is not configured.'));
        }

        $emailIdentity = $this->scopeConfig->getValue(self::XML_PATH_EMAIL_IDENTITY);
        if (!$emailIdentity) {
            throw new LocalizedException(__('Email notification sender identity is not configured.'));
        }

        $this->inlineTranslation->suspend();

        try {
            $templateVars = array_merge([
                'subject' => $subject,
                'content' => $content,
                'items' => []
            ], $additionalData);

            $transport = $this->transportBuilder
                ->setTemplateIdentifier($templateId)
                ->setTemplateOptions([
                    'area' => Area::AREA_ADMINHTML,
                    'store' => Store::DEFAULT_STORE_ID
                ])
                ->setTemplateVars($templateVars)
                ->setFromByScope($emailIdentity, Store::DEFAULT_STORE_ID)
                ->addTo($recipient)
                ->getTransport();

            $transport->sendMessage();
        } finally {
            $this->inlineTranslation->resume();
        }
    }

    /**
     * @inheritDoc
     */
    public function getRecipient(): ?string
    {
        return $this->scopeConfig->getValue(self::XML_PATH_RECIPIENT);
    }
}
