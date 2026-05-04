<?php declare(strict_types=1);
/**
* Copyright © PH2M SARL. All rights reserved.
* See COPYING.txt for license details.
*/

namespace Ph2m\MetabaseDashboard\Block\Adminhtml;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Ph2m\MetabaseDashboard\Helper\Data;

class Dashboard extends Template
{
    public function __construct(
        Context $context,
        private readonly Data $helper,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    public function isEnabled(): bool
    {
        return $this->helper->isEnabled();
    }

    public function getIframeUrl(): string
    {
        return $this->helper->getIframeUrl();
    }

    protected function _toHtml(): string
    {
        if (!$this->helper->isEnabled()) {
            return '';
        }

        return parent::_toHtml();
    }
}
