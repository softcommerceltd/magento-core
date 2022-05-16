<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Core\Logger\Handler;

use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Store\Model\Store;
use Monolog\Handler\MailHandler;
use Monolog\Logger;

/**
 * @inheritDoc
 */
class MailStreamHandler extends MailHandler
{
    private const XML_PATH_IS_ACTIVE = 'softcommerce_core/dev/is_active_mail_log';
    private const XML_PATH_RECIPIENT = 'softcommerce_core/dev/mail_log_email';
    private const XML_PATH_EMAIL_IDENTITY = 'softcommerce_core/dev/mail_log_email_identity';
    private const XML_PATH_EMAIL_TEMPLATE = 'softcommerce_core/dev/mail_log_email_template';

    /**
     * @var StateInterface
     */
    protected $inlineTranslation;

    /**
     * @var TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param StateInterface $inlineTranslation
     * @param TransportBuilder $transportBuilder
     * @param int $level
     * @param bool $bubble
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StateInterface $inlineTranslation,
        TransportBuilder $transportBuilder,
        int $level = Logger::ALERT,
        bool $bubble = true
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->inlineTranslation = $inlineTranslation;
        $this->transportBuilder = $transportBuilder;
        parent::__construct($level, $bubble);
    }

    /**
     * @param string $content
     * @param array $records
     * @param bool $forceSend
     * @throws LocalizedException
     * @throws MailException
     */
    protected function send($content, array $records, bool $forceSend = false)
    {
        if (false === $forceSend && !$this->scopeConfig->getValue(self::XML_PATH_IS_ACTIVE)) {
            return;
        }

        $this->inlineTranslation->suspend();
        $recipient = $this->scopeConfig->getValue(self::XML_PATH_RECIPIENT);
        $transport = $this->transportBuilder
            ->setTemplateIdentifier($this->scopeConfig->getValue(self::XML_PATH_EMAIL_TEMPLATE))
            ->setTemplateOptions(['area' => Area::AREA_ADMINHTML, 'store' => Store::DEFAULT_STORE_ID])
            ->setTemplateVars(['content' => $content, 'items' => $records])
            ->setFromByScope($this->scopeConfig->getValue(self::XML_PATH_EMAIL_IDENTITY), Store::DEFAULT_STORE_ID)
            ->addTo($recipient)
            ->getTransport();

        $transport->sendMessage();
        $this->inlineTranslation->resume();
    }
}
