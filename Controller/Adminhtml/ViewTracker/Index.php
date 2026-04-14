<?php
/**
 * Copyright © Panth Infotech. All rights reserved.
 * View Tracker Admin Controller
 */
declare(strict_types=1);

namespace Panth\QuickView\Controller\Adminhtml\ViewTracker;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends Action
{
    /**
     * Authorization level
     */
    const ADMIN_RESOURCE = 'Panth_QuickView::view_tracker';

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Execute action
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Panth_QuickView::view_tracker');
        $resultPage->getConfig()->getTitle()->prepend(__('Product View Tracker'));
        return $resultPage;
    }
}
