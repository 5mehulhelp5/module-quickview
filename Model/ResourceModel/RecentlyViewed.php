<?php
/**
 * Copyright © Panth Infotech. All rights reserved.
 * Recently Viewed Resource Model
 */
declare(strict_types=1);

namespace Panth\QuickView\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class RecentlyViewed extends AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('panth_recently_viewed', 'view_id');
    }
}
