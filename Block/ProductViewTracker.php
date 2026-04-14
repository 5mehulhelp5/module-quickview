<?php
/**
 * Copyright © Panth Infotech. All rights reserved.
 * Product View Tracker Block
 */
declare(strict_types=1);

namespace Panth\QuickView\Block;

use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class ProductViewTracker extends Template
{
    /**
     * @var Registry
     */
    private Registry $registry;

    /**
     * @var ImageHelper
     */
    private ImageHelper $imageHelper;

    /**
     * @var PriceHelper
     */
    private PriceHelper $priceHelper;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param ImageHelper $imageHelper
     * @param PriceHelper $priceHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ImageHelper $imageHelper,
        PriceHelper $priceHelper,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->imageHelper = $imageHelper;
        $this->priceHelper = $priceHelper;
        parent::__construct($context, $data);
    }

    /**
     * Get current product
     *
     * @return \Magento\Catalog\Model\Product|null
     */
    public function getCurrentProduct()
    {
        return $this->registry->registry('current_product');
    }

    /**
     * Get formatted price
     *
     * @param float $price
     * @return string
     */
    public function getFormattedPrice(float $price): string
    {
        return (string)$this->priceHelper->currency($price, true, false);
    }

    /**
     * Get product image URL
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    public function getProductImageUrl($product): string
    {
        return (string)$this->imageHelper
            ->init($product, 'product_page_image_small')
            ->getUrl();
    }
}
