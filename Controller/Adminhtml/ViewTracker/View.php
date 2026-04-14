<?php
/**
 * Copyright © Panth Infotech. All rights reserved.
 * View Tracker Detail Controller
 */
declare(strict_types=1);

namespace Panth\QuickView\Controller\Adminhtml\ViewTracker;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Panth\QuickView\Model\RecentlyViewedFactory;

class View extends Action
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
     * @var RecentlyViewedFactory
     */
    protected $viewedFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param RecentlyViewedFactory $viewedFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        RecentlyViewedFactory $viewedFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->viewedFactory = $viewedFactory;
    }

    /**
     * Execute action
     *
     * @return \Magento\Framework\View\Result\Page|\Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');

        if (!$id) {
            $this->messageManager->addErrorMessage(__('Invalid view record ID.'));
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('*/*/index');
        }

        // Load the view record
        $viewRecord = $this->viewedFactory->create()->load($id);

        if (!$viewRecord->getId()) {
            $this->messageManager->addErrorMessage(__('View record not found.'));
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('*/*/index');
        }

        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Panth_QuickView::view_tracker');
        $resultPage->getConfig()->getTitle()->prepend(__('View Details #%1', $id));

        return $resultPage;
    }
}
