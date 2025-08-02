<?php

declare(strict_types=1);
/**
 * Copyright © 2025 Romain ITOFO. Tous droits réservés.
 *
 * @author  Romain ITOFO
 * @license Propriétaire
 */

namespace Romain\AdvancedWishList\Controller\Ajax;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\LayoutFactory;
use Romain\AdvancedWishList\Api\Data\WishListItemInterface;
use Romain\AdvancedWishList\Block\Product\AddToWishList;
use Magento\Customer\Model\Session as CustomerSession;

/**
 *
 */
class Render extends Action
{
    public function __construct(
        Context                                     $context,
        private readonly JsonFactory                $jsonFactory,
        private readonly LayoutFactory              $layoutFactory,
        private readonly ProductRepositoryInterface $productRepository,
        private readonly CustomerSession            $customerSession
    ) {
        parent::__construct($context);
    }

    public function execute(): Json|ResultInterface|ResponseInterface
    {
        $customer_id = (int)$this->customerSession->getCustomerId();

        $product_id = (int)$this->getRequest()->getParam(WishListItemInterface::PRODUCT_ID);
        try {
            $product = $this->productRepository->getById($product_id);

            $layout = $this->layoutFactory->create();
            $block = $layout->createBlock(
                AddToWishList::class,
                '',
                ['data' => ['product' => $product, 'customer_id' => $customer_id]]
            );
            $block->setTemplate('Romain_AdvancedWishList::product/add_to_wishlist.phtml');

            $html = $block->toHtml();

            return $this->jsonFactory->create()->setData([
                'success'   => true,
                'html'      => $html,
                'reinit_js' => true
            ]);
        } catch (\Exception $e) {
            return $this->jsonFactory->create()->setData([
                'success' => false,
                'message' => __('Unable to load wishlist block.')
            ]);
        }
    }
}
