<?php
/**
 * Copyright © Panth Infotech. All rights reserved.
 * Recently Viewed Model
 */
declare(strict_types=1);

namespace Panth\QuickView\Model;

use Magento\Framework\Model\AbstractModel;

class RecentlyViewed extends AbstractModel
{
    /**
     * Cache tag
     */
    const CACHE_TAG = 'panth_recently_viewed';

    /**
     * @var string
     */
    protected $_cacheTag = 'panth_recently_viewed';

    /**
     * @var string
     */
    protected $_eventPrefix = 'panth_recently_viewed';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Panth\QuickView\Model\ResourceModel\RecentlyViewed::class);
    }

    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
}
