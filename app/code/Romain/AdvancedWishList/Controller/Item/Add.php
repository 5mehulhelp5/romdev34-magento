<?php
/**
 * Copyright © 2025 Romain ITOFO. Tous droits réservés.
 *
 * @author  Romain ITOFO
 * @license Propriétaire
 */

declare(strict_types=1);

namespace Romain\AdvancedWishList\Controller\Item;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Cache\TypeListInterface as CacheTypeList;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Indexer\CacheContextFactory;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Romain\AdvancedWishList\Api\WishListItemRepositoryInterface;
use Romain\AdvancedWishList\Controller\AbstractWishList;
use Romain\AdvancedWishList\Model\WishList;
use Romain\AdvancedWishList\Model\WishListItem;
use Romain\AdvancedWishList\Api\WishListRepositoryInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Event\Manager as EventManager;

/**
 *  Controller to Add a product in the wishlist
 */
class Add extends AbstractWishList implements HttpPostActionInterface
{
    public function __construct(
        private readonly RequestInterface                $request,
        private readonly StoreManagerInterface           $storeManager,
        private readonly WishListItemRepositoryInterface $wishlistItemRepository,
        private readonly LoggerInterface                 $logger,
        private readonly EventManager                    $eventManager,
        private readonly CacheContextFactory             $cacheContextFactory,
        CacheTypeList                                    $cacheTypeList,
        CacheInterface                                   $cache,
        JsonFactory                                      $jsonFactory,
        WishListRepositoryInterface                      $wishListRepository,
        CustomerSession                                  $customerSession,
    ) {
        parent::__construct($cache, $cacheTypeList, $jsonFactory, $wishListRepository, $customerSession,$cacheContextFactory, $eventManager);
    }

    public function execute(): Json
    {
        $result = $this->jsonFactory->create();

        if (!$this->customerSession->isLoggedIn()) {
            return $result->setData([
                'success' => false,
                'message' => __('You must be logged in to add products to wishlist.')
            ]);
        }

        try {
            $wishlist_id = (int)$this->request->getParam('wishlist_id');
            $product_id = (int)$this->request->getParam('product_id');
            $qty = (float)$this->request->getParam('qty', 1);
            $description = $this->request->getParam('description');
            $price_alert = (bool)$this->request->getParam('price_alert', false);
            $target_price = $this->request->getParam('target_price');

            $this->checkWishList($wishlist_id, $product_id);

            $store_id = (int)$this->storeManager->getStore()->getId();

            $options = [
                'qty'          => $qty,
                'description'  => $description,
                'price_alert'  => $price_alert,
                'target_price' => $target_price ? (float)$target_price : null
            ];

              $this->wishlistItemRepository->addProductToWishlist($wishlist_id, $product_id, $store_id, $options);

//            $cache_context = $this->cacheContextFactory->create();
//            $cache_context->registerEntities(WishList::CACHE_TAG, [$wishlist_id]);
//            $this->eventManager->dispatch('clean_cache_by_tags', ['object' => $cache_context]);
//            $cache_context->registerEntities(WishListItem::CACHE_TAG, [$item->getItemId()]);
//            $this->eventManager->dispatch('clean_cache_by_tags', ['object' => $cache_context]);

            return $result->setData([
                'success' => true,
                'message' => __('Product has been added to your wishlist.')
            ]);
        } catch (LocalizedException $e) {
            return $result->setData([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Error adding product to wishlist: ' . $e->getMessage());

            return $result->setData([
                'success' => false,
                'message' => __('An error occurred while adding the product to your wishlist.' . $e->getMessage())
            ]);
        }
    }
}
