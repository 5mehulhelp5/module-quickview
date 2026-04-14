<?php
/**
 * Copyright © Panth Infotech. All rights reserved.
 * QuickView Helper
 */
declare(strict_types=1);

namespace Panth\QuickView\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    /**
     * Configuration XML path prefix
     */
    private const XML_PATH_GENERAL = 'panth_quickview/general/';
    private const XML_PATH_DISPLAY = 'panth_quickview/display/';

    /**
     * Check if Quick View is enabled
     */
    public function isEnabled(?int $storeId = null): bool
    {
        return (bool) $this->scopeConfig->getValue(
            self::XML_PATH_GENERAL . 'enabled',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check if product image gallery should be shown
     */
    public function showImageGallery(?int $storeId = null): bool
    {
        return (bool) $this->scopeConfig->getValue(
            self::XML_PATH_DISPLAY . 'show_image_gallery',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check if short description should be shown
     */
    public function showShortDescription(?int $storeId = null): bool
    {
        return (bool) $this->scopeConfig->getValue(
            self::XML_PATH_DISPLAY . 'show_short_description',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check if SKU should be shown
     */
    public function showSku(?int $storeId = null): bool
    {
        return (bool) $this->scopeConfig->getValue(
            self::XML_PATH_DISPLAY . 'show_sku',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check if stock status should be shown
     */
    public function showStockStatus(?int $storeId = null): bool
    {
        return (bool) $this->scopeConfig->getValue(
            self::XML_PATH_DISPLAY . 'show_stock_status',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check if Add to Cart button should be shown
     */
    public function showAddToCart(?int $storeId = null): bool
    {
        return (bool) $this->scopeConfig->getValue(
            self::XML_PATH_DISPLAY . 'show_add_to_cart',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
