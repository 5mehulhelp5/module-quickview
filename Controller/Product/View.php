<?php
declare(strict_types=1);

namespace Panth\QuickView\Controller\Product;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Checkout\Helper\Cart as CartHelper;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\StoreManagerInterface;
use Panth\QuickView\Helper\Data as QuickViewHelper;
use Psr\Log\LoggerInterface;

class View implements HttpGetActionInterface
{
    private const IMAGE_WIDTH  = 700;
    private const IMAGE_HEIGHT = 700;
    private const THUMB_WIDTH  = 100;
    private const THUMB_HEIGHT = 100;

    private const SHORT_DESC_MAX_LENGTH = 300;

    private JsonFactory $resultJsonFactory;
    private ProductRepositoryInterface $productRepository;
    private StoreManagerInterface $storeManager;
    private PriceHelper $priceHelper;
    private StockRegistryInterface $stockRegistry;
    private QuickViewHelper $helper;
    private RequestInterface $request;
    private ImageHelper $imageHelper;
    private PageFactory $resultPageFactory;
    private CartHelper $cartHelper;
    private FormKey $formKey;
    private Registry $registry;
    private LoggerInterface $logger;

    public function __construct(
        JsonFactory $resultJsonFactory,
        ProductRepositoryInterface $productRepository,
        StoreManagerInterface $storeManager,
        PriceHelper $priceHelper,
        StockRegistryInterface $stockRegistry,
        QuickViewHelper $helper,
        RequestInterface $request,
        ImageHelper $imageHelper,
        PageFactory $resultPageFactory,
        CartHelper $cartHelper,
        FormKey $formKey,
        Registry $registry,
        LoggerInterface $logger
    ) {
        $this->resultJsonFactory  = $resultJsonFactory;
        $this->productRepository  = $productRepository;
        $this->storeManager       = $storeManager;
        $this->priceHelper        = $priceHelper;
        $this->stockRegistry      = $stockRegistry;
        $this->helper             = $helper;
        $this->request            = $request;
        $this->imageHelper        = $imageHelper;
        $this->resultPageFactory  = $resultPageFactory;
        $this->cartHelper         = $cartHelper;
        $this->formKey            = $formKey;
        $this->registry           = $registry;
        $this->logger             = $logger;
    }

    public function execute(): Json
    {
        $result = $this->resultJsonFactory->create();

        if (!$this->helper->isEnabled()) {
            return $result->setData([
                'success' => false,
                'message' => __('Quick View is disabled.')
            ]);
        }

        $productId = (int) $this->request->getParam('id');

        if (!$productId) {
            return $result->setData([
                'success' => false,
                'message' => __('Product ID is required.')
            ]);
        }

        try {
            $product = $this->productRepository->getById($productId);

            $typeId = $product->getTypeId();
            $inStock = true;
            try {
                if ($typeId === 'simple' || $typeId === 'virtual') {
                    $stockItem = $this->stockRegistry->getStockItem($productId);
                    $inStock = (bool)$stockItem->getIsInStock();
                } else {
                    $inStock = $product->isAvailable();
                }
            } catch (\Exception $e) {
                $inStock = true;
            }

            $finalPrice = $product->getFinalPrice();
            $regularPrice = $product->getPrice();

            $pricePrefix = '';
            if ($typeId === 'configurable') {
                $pricePrefix = '<span style="font-size:0.8rem;color:var(--color-fg-secondary,#525252);font-weight:400;">As low as </span>';

                $children = $product->getTypeInstance()->getUsedProducts($product);
                $minPrice = PHP_FLOAT_MAX;
                foreach ($children as $child) {
                    if ($child->getFinalPrice() < $minPrice) {
                        $minPrice = $child->getFinalPrice();
                    }
                }
                $finalPrice = $minPrice < PHP_FLOAT_MAX ? $minPrice : $finalPrice;
            }

            $priceHtml = $pricePrefix . '<span class="price" style="font-size:1.5rem;font-weight:700;color:var(--color-fg,#171717);">'
                . $this->priceHelper->currency($finalPrice, true, false)
                . '</span>';

            if ($regularPrice > $finalPrice && $finalPrice > 0 && $typeId !== 'configurable') {
                $priceHtml = '<span style="text-decoration:line-through;color:var(--color-fg-muted,#a3a3a3);font-size:0.9rem;margin-right:8px;">'
                    . $this->priceHelper->currency($regularPrice, true, false)
                    . '</span>' . $priceHtml;
            }

            $data = [
                'success' => true,
                'product' => [
                    'id'                => (int)$product->getId(),
                    'name'              => $product->getName(),
                    'sku'               => $product->getSku(),
                    'type_id'           => $typeId,
                    'price_html'        => $priceHtml,
                    'short_description' => $this->getTruncatedShortDescription($product),
                    'images'            => $this->getProductImages($product),
                    'in_stock'          => $inStock,
                    'stock_qty'         => 0,
                    'url'               => $product->getProductUrl(),
                    'form_key'          => $this->formKey->getFormKey(),
                ],
                'options_html' => '',
            ];

            return $result->setData($data);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return $result->setData([
                'success' => false,
                'message' => __('Product not found.')
            ]);
        } catch (\Exception $e) {
            $this->logger->error('QuickView controller error: ' . $e->getMessage(), [
                'product_id' => $productId,
                'trace'      => $e->getTraceAsString(),
            ]);
            return $result->setData([
                'success' => false,
                'message' => __('Unable to load product data. Please try again.')
            ]);
        }
    }

    private function registerProduct($product): void
    {
        $this->registry->unregister('current_product');
        $this->registry->unregister('product');

        $this->registry->register('current_product', $product);
        $this->registry->register('product', $product);
    }

    private function renderPriceHtml($resultPage, $product): string
    {
        $layout = $resultPage->getLayout();

        $priceRender = $layout->getBlock('product.price.render.default');

        if (!$priceRender) {
            $priceRender = $layout->createBlock(
                \Magento\Framework\Pricing\Render::class,
                'product.price.render.default.quickview',
                [
                    'data' => [
                        'price_render_handle' => 'catalog_product_prices',
                        'use_link_for_as_low_as' => true,
                    ],
                ]
            );
        }

        if ($priceRender) {
            $priceHtml = $priceRender->render(
                \Magento\Catalog\Pricing\Price\FinalPrice::class,
                $product,
                [
                    'include_container'     => true,
                    'display_minimal_price' => true,
                    'zone'                  => \Magento\Framework\Pricing\Render::ZONE_ITEM_LIST,
                    'list_category_page'    => true,
                ]
            );

            return $priceHtml;
        }

        return '<span class="price">'
            . $this->priceHelper->currency($product->getFinalPrice(), true, false)
            . '</span>';
    }

    private function renderOptionsHtml($resultPage, $product): string
    {
        $typeId = $product->getTypeId();

        if ($typeId === \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE
            && !$product->getHasOptions()
        ) {
            return '';
        }

        $layout = $resultPage->getLayout();
        $html   = '';

        if ($typeId === \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
            $html .= $this->renderConfigurableOptions($layout, $product);
        }

        if ($typeId === \Magento\Bundle\Model\Product\Type::TYPE_CODE) {
            $html .= $this->renderBundleOptions($layout, $product);
        }

        if ($typeId === \Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE) {
            $html .= $this->renderGroupedOptions($layout, $product);
        }

        if ($product->getHasOptions()) {
            $html .= $this->renderCustomOptions($layout, $product);
        }

        return $html;
    }

    private function renderConfigurableOptions($layout, $product): string
    {
        try {
            $block = $layout->createBlock(
                \Magento\Swatches\Block\Product\Renderer\Configurable::class,
                'quickview.product.configurable.options.' . $product->getId(),
                [
                    'data' => [
                        'product' => $product,
                    ],
                ]
            );

            if ($block) {
                $block->setProduct($product);
                $block->setTemplate('Magento_Swatches::product/view/renderer.phtml');
                return $block->toHtml();
            }
        } catch (\Exception $e) {
            $this->logger->warning('QuickView: could not render configurable options', [
                'product_id' => $product->getId(),
                'error'      => $e->getMessage(),
            ]);
        }

        try {
            $block = $layout->createBlock(
                \Magento\ConfigurableProduct\Block\Product\View\Type\Configurable::class,
                'quickview.product.configurable.fallback.' . $product->getId()
            );

            if ($block) {
                $block->setProduct($product);
                $block->setTemplate('Magento_ConfigurableProduct::product/view/type/options/configurable.phtml');
                return $block->toHtml();
            }
        } catch (\Exception $e) {
            $this->logger->warning('QuickView: configurable fallback failed', [
                'product_id' => $product->getId(),
                'error'      => $e->getMessage(),
            ]);
        }

        return '';
    }

    private function renderBundleOptions($layout, $product): string
    {
        try {
            $block = $layout->createBlock(
                \Magento\Bundle\Block\Catalog\Product\View\Type\Bundle::class,
                'quickview.product.bundle.options.' . $product->getId()
            );

            if ($block) {
                $block->setProduct($product);
                $block->setTemplate('Magento_Bundle::catalog/product/view/type/bundle/options.phtml');
                return $block->toHtml();
            }
        } catch (\Exception $e) {
            $this->logger->warning('QuickView: could not render bundle options', [
                'product_id' => $product->getId(),
                'error'      => $e->getMessage(),
            ]);
        }

        return '';
    }

    private function renderGroupedOptions($layout, $product): string
    {
        try {
            $block = $layout->createBlock(
                \Magento\GroupedProduct\Block\Product\View\Type\Grouped::class,
                'quickview.product.grouped.options.' . $product->getId()
            );

            if ($block) {
                $block->setProduct($product);
                $block->setTemplate('Magento_GroupedProduct::product/view/type/grouped.phtml');
                return $block->toHtml();
            }
        } catch (\Exception $e) {
            $this->logger->warning('QuickView: could not render grouped options', [
                'product_id' => $product->getId(),
                'error'      => $e->getMessage(),
            ]);
        }

        return '';
    }

    private function renderCustomOptions($layout, $product): string
    {
        try {
            $block = $layout->createBlock(
                \Magento\Catalog\Block\Product\View\Options::class,
                'quickview.product.custom.options.' . $product->getId()
            );

            if ($block) {
                $block->setProduct($product);
                $block->setTemplate('Magento_Catalog::product/view/options.phtml');

                $typeBlocks = [
                    'default' => [
                        'class'    => \Magento\Catalog\Block\Product\View\Options\Type\DefaultType::class,
                        'template' => 'Magento_Catalog::product/view/options/type/default.phtml',
                    ],
                    'text' => [
                        'class'    => \Magento\Catalog\Block\Product\View\Options\Type\Text::class,
                        'template' => 'Magento_Catalog::product/view/options/type/text.phtml',
                    ],
                    'select' => [
                        'class'    => \Magento\Catalog\Block\Product\View\Options\Type\Select::class,
                        'template' => 'Magento_Catalog::product/view/options/type/select.phtml',
                    ],
                    'file' => [
                        'class'    => \Magento\Catalog\Block\Product\View\Options\Type\File::class,
                        'template' => 'Magento_Catalog::product/view/options/type/file.phtml',
                    ],
                    'date' => [
                        'class'    => \Magento\Catalog\Block\Product\View\Options\Type\Date::class,
                        'template' => 'Magento_Catalog::product/view/options/type/date.phtml',
                    ],
                ];

                foreach ($typeBlocks as $alias => $info) {
                    $childBlock = $layout->createBlock(
                        $info['class'],
                        'quickview.product.options.type.' . $alias . '.' . $product->getId()
                    );
                    if ($childBlock) {
                        $childBlock->setTemplate($info['template']);
                        $block->setChild($alias, $childBlock);
                    }
                }

                return $block->toHtml();
            }
        } catch (\Exception $e) {
            $this->logger->warning('QuickView: could not render custom options', [
                'product_id' => $product->getId(),
                'error'      => $e->getMessage(),
            ]);
        }

        return '';
    }

    private function getProductImages($product): array
    {
        $images = [];

        $mediaGallery = $product->getMediaGalleryImages();

        if ($mediaGallery && $mediaGallery->getSize()) {
            foreach ($mediaGallery as $image) {
                $mainUrl = $this->imageHelper
                    ->init($product, 'product_page_image_large')
                    ->setImageFile($image->getFile())
                    ->resize(self::IMAGE_WIDTH, self::IMAGE_HEIGHT)
                    ->getUrl();

                $thumbUrl = $this->imageHelper
                    ->init($product, 'product_page_image_small')
                    ->setImageFile($image->getFile())
                    ->resize(self::THUMB_WIDTH, self::THUMB_HEIGHT)
                    ->getUrl();

                $images[] = [
                    'url'       => $mainUrl,
                    'thumb_url' => $thumbUrl,
                    'label'     => $image->getLabel() ?: $product->getName(),
                    'position'  => (int) $image->getPosition(),
                ];
            }
        }

        if (empty($images)) {
            $placeholderUrl = $this->imageHelper
                ->init($product, 'product_page_image_large')
                ->resize(self::IMAGE_WIDTH, self::IMAGE_HEIGHT)
                ->getUrl();

            $images[] = [
                'url'       => $placeholderUrl,
                'thumb_url' => $placeholderUrl,
                'label'     => $product->getName(),
                'position'  => 0,
            ];
        }

        return $images;
    }

    private function getTruncatedShortDescription($product): string
    {
        $shortDesc = $product->getShortDescription()
            ?: $product->getData('short_description')
            ?: $product->getDescription()
            ?: $product->getData('description')
            ?: '';

        if (!$shortDesc) {
            return '';
        }

        $plain = strip_tags((string) $shortDesc);
        $plain = trim(preg_replace('/\s+/', ' ', $plain));

        if (mb_strlen($plain) > self::SHORT_DESC_MAX_LENGTH) {
            $plain = mb_substr($plain, 0, self::SHORT_DESC_MAX_LENGTH);

            $lastSpace = mb_strrpos($plain, ' ');
            if ($lastSpace !== false) {
                $plain = mb_substr($plain, 0, $lastSpace);
            }
            $plain .= '...';
        }

        return $plain;
    }

    private function getAddToCartUrl($product): string
    {
        return $this->cartHelper->getAddUrl($product, ['useUencPlaceholders' => false]);
    }
}
