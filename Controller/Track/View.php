<?php
/**
 * Copyright © Panth Infotech. All rights reserved.
 * Track Product View — AJAX endpoint called from product pages
 */
declare(strict_types=1);

namespace Panth\QuickView\Controller\Track;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Store\Model\StoreManagerInterface;
use Panth\QuickView\Model\RecentlyViewedFactory;
use Panth\QuickView\Model\ResourceModel\RecentlyViewed as RecentlyViewedResource;
use Panth\QuickView\Model\ResourceModel\RecentlyViewed\CollectionFactory;
use Psr\Log\LoggerInterface;

class View implements HttpPostActionInterface, CsrfAwareActionInterface
{
    private RequestInterface $request;
    private JsonFactory $jsonFactory;
    private RecentlyViewedFactory $recentlyViewedFactory;
    private RecentlyViewedResource $recentlyViewedResource;
    private CollectionFactory $collectionFactory;
    private CustomerSession $customerSession;
    private StoreManagerInterface $storeManager;
    private LoggerInterface $logger;

    public function __construct(
        RequestInterface $request,
        JsonFactory $jsonFactory,
        RecentlyViewedFactory $recentlyViewedFactory,
        RecentlyViewedResource $recentlyViewedResource,
        CollectionFactory $collectionFactory,
        CustomerSession $customerSession,
        StoreManagerInterface $storeManager,
        LoggerInterface $logger
    ) {
        $this->request = $request;
        $this->jsonFactory = $jsonFactory;
        $this->recentlyViewedFactory = $recentlyViewedFactory;
        $this->recentlyViewedResource = $recentlyViewedResource;
        $this->collectionFactory = $collectionFactory;
        $this->customerSession = $customerSession;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
    }

    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }

    public function execute()
    {
        $result = $this->jsonFactory->create();

        try {
            $body = $this->request->getContent();
            $data = json_decode($body, true);

            $productId = (int)($data['product_id'] ?? 0);
            if (!$productId) {
                return $result->setData(['success' => false, 'message' => 'Product ID required.']);
            }

            $storeId = (int)$this->storeManager->getStore()->getId();
            $customerId = $this->customerSession->isLoggedIn() ? (int)$this->customerSession->getCustomerId() : null;
            $visitorId = $this->getVisitorId();

            // Check if already tracked recently (within last 5 minutes) to avoid duplicates
            $collection = $this->collectionFactory->create();
            $collection->addFieldToFilter('product_id', $productId);
            $collection->addFieldToFilter('store_id', $storeId);

            if ($customerId) {
                $collection->addFieldToFilter('customer_id', $customerId);
            } else {
                $collection->addFieldToFilter('visitor_id', $visitorId);
            }

            $collection->addFieldToFilter('viewed_at', ['gteq' => date('Y-m-d H:i:s', strtotime('-5 minutes'))]);
            $collection->setPageSize(1);

            if ($collection->getSize() > 0) {
                // Update existing record's timestamp
                $existing = $collection->getFirstItem();
                $existing->setData('viewed_at', date('Y-m-d H:i:s'));
                $this->recentlyViewedResource->save($existing);
                return $result->setData(['success' => true, 'action' => 'updated']);
            }

            // Create new view record
            $view = $this->recentlyViewedFactory->create();
            $view->setData([
                'product_id' => $productId,
                'customer_id' => $customerId,
                'visitor_id' => $visitorId,
                'store_id' => $storeId,
                'viewed_at' => date('Y-m-d H:i:s'),
            ]);
            $this->recentlyViewedResource->save($view);

            return $result->setData(['success' => true, 'action' => 'created']);
        } catch (\Exception $e) {
            $this->logger->error('QuickView Track error: ' . $e->getMessage());
            return $result->setData(['success' => false, 'message' => 'Tracking failed.']);
        }
    }

    private function getVisitorId(): string
    {
        $ip = $this->request->getServer('REMOTE_ADDR') ?? '0.0.0.0';
        $ua = $this->request->getServer('HTTP_USER_AGENT') ?? '';
        return hash('sha256', $ip . '|' . $ua);
    }
}
