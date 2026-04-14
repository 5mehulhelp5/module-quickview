<?php
/**
 * Copyright © Panth Infotech. All rights reserved.
 * QuickView Block
 */
declare(strict_types=1);

namespace Panth\QuickView\Block;

use Magento\Framework\View\Element\Template;
use Panth\QuickView\Helper\Data as QuickViewHelper;

class QuickView extends Template
{
    /**
     * @var QuickViewHelper
     */
    private QuickViewHelper $quickViewHelper;

    /**
     * @param Template\Context $context
     * @param QuickViewHelper $quickViewHelper
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        QuickViewHelper $quickViewHelper,
        array $data = []
    ) {
        $this->quickViewHelper = $quickViewHelper;
        parent::__construct($context, $data);
    }

    /**
     * Get helper instance
     *
     * @return QuickViewHelper
     */
    public function getHelper(): QuickViewHelper
    {
        return $this->quickViewHelper;
    }

    /**
     * Check if module is enabled
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->quickViewHelper->isEnabled();
    }

    /**
     * Get quick view URL
     *
     * @param string $productId
     * @return string
     */
    public function getQuickViewUrl(string $productId = ''): string
    {
        return $this->getUrl('quickview/product/view');
    }
}
