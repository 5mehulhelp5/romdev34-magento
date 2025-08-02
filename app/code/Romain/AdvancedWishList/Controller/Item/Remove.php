<?php

declare(strict_types=1);

namespace Romain\AdvancedWishList\Controller\Item;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Customer\Model\Session as CustomerSession;
use Romain\AdvancedWishList\Api\WishListItemRepositoryInterface;
use Romain\AdvancedWishList\Api\WishListRepositoryInterface;
use Psr\Log\LoggerInterface;
use Romain\AdvancedWishList\Controller\AbstractWishList;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\Cache\TypeListInterface;
use Romain\AdvancedWishList\Model\WishListItem;
use Romain\AdvancedWishList\Model\WishList;
use Magento\Framework\Event\Manager as EventManager;
use Magento\Framework\Indexer\CacheContextFactory;

/**
 * Controller to remove an item from the wishlist
 */
class Remove extends AbstractWishList implements HttpPostActionInterface
{
    public function __construct(
        private readonly RequestInterface                $request,
        private readonly WishListItemRepositoryInterface $wishlistItemRepository,
        private readonly LoggerInterface                 $logger,
        private readonly EventManager                    $eventManager,
        private readonly CacheContextFactory             $cacheContextFactory,
        TypeListInterface                                $cacheTypeList,
        CacheInterface                                   $cache,
        JsonFactory                                      $jsonFactory,
        WishListRepositoryInterface                      $wishListRepository,
        CustomerSession                                  $customerSession,
    ) {
        parent::__construct($cache, $cacheTypeList, $jsonFactory, $wishListRepository, $customerSession, $cacheContextFactory, $eventManager);
    }

    public function execute(): Json
    {
        $result = $this->jsonFactory->create();

        if (!$this->customerSession->isLoggedIn()) {
            return $result->setData([
                'success' => false,
                'message' => __('You must be logged in to remove products from wishlist.')
            ]);
        }

        try {
            $wishlist_id = (int)$this->request->getParam('wishlist_id');
            $product_id = (int)$this->request->getParam('product_id');
            $item_id = (int)$this->request->getParam('item_id');
            $this->checkWishList($wishlist_id, $product_id);

            $removed = $this->wishlistItemRepository->removeProductFromWishlist($wishlist_id, $product_id);

            $cache_context = $this->cacheContextFactory->create();
            $cache_context->registerEntities(WishList::CACHE_TAG, [$wishlist_id]);
            $this->eventManager->dispatch('clean_cache_by_tags', ['object' => $cache_context]);
            $cache_context->registerEntities(WishListItem::CACHE_TAG, [$item_id]);
            $this->eventManager->dispatch('clean_cache_by_tags', ['object' => $cache_context]);
            $this->cache->clean($cache_context->getIdentities());

            if ($removed) {
                return $result->setData([
                    'success' => true,
                    'message' => __('Product has been removed from your wishlist.')
                ]);
            } else {
                return $result->setData([
                    'success' => false,
                    'message' => __('Product was not found in your wishlist.')
                ]);
            }
        } catch (LocalizedException $e) {
            return $result->setData([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Error removing product from wishlist: ' . $e->getMessage());

            return $result->setData([
                'success' => false,
                'message' => __('An error occurred while removing the product from your wishlist.' . $e->getMessage())
            ]);
        }
    }
}
