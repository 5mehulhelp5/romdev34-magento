<?php
/**
 * Copyright © 2025 Romain ITOFO. Tous droits réservés.
 *
 * @author  Romain ITOFO
 * @license Propriétaire
 */

declare(strict_types=1);

namespace Romain\AdvancedWishList\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\Indexer\CacheContextFactory;
use Magento\Framework\Event\Manager as EventManager;
use Romain\AdvancedWishList\Model\WishListItem;
use Romain\AdvancedWishList\Api\WishListRepositoryInterface;

/**
 * Observer to invalidate the cache of the wish list items
 */
class InvalidateWishlistItemCache implements ObserverInterface
{
    /**
     * @param CacheInterface              $cache
     * @param TypeListInterface           $typeList
     * @param CacheContextFactory         $cacheContextFactory
     * @param EventManager                $eventManager
     * @param WishListRepositoryInterface $wishListRepository
     */
    public function __construct(
        private readonly CacheInterface              $cache,
        private readonly TypeListInterface           $typeList,
        private readonly CacheContextFactory         $cacheContextFactory,
        private readonly EventManager                $eventManager,
        private readonly WishListRepositoryInterface $wishListRepository
    ) {
    }

    /**
     *
     * @param Observer $observer
     *
     * @return void
     */
    public function execute(Observer $observer): void
    {
        /** @var WishListItem $wishlistItem */
        $wishlistItem = $observer->getEvent()->getData('object') ?? $observer->getEvent()->getData('wishlist_item');

        if (!$wishlistItem instanceof WishListItem) {
            return;
        }

        $this->invalidateWishlistItemCache($wishlistItem);
    }

    /**
     *
     * @param WishListItem $wishlistItem
     *
     * @return void
     */
    private function invalidateWishlistItemCache(WishListItem $wishlistItem): void
    {
        $wishlistId = $wishlistItem->getWishlistId();
        $itemId = $wishlistItem->getId();

        $customerId = null;
        try {
            $wishlist = $this->wishListRepository->getById($wishlistId);
            $customerId = $wishlist->getCustomerId();
        } catch (\Exception $e) {
            // Continue sans customer_id si erreur
        }

        // ⭐ Cache tags to invalidate - Exactly the ones of the bloc
        $cacheTags = [
            'advanced_wishlist_item_' . $itemId,
            'advanced_wishlist_items',
            'advanced_wishlist_' . $wishlistId,
        ];

        if ($customerId) {
            $cacheTags[] = 'customer_wishlist_items_' . $customerId;
            $cacheTags[] = 'customer_wishlists_' . $customerId;
        }

        $this->cache->clean($cacheTags);

        $cacheContext = $this->cacheContextFactory->create();

        $cacheContext->registerEntities(WishListItem::CACHE_TAG, [$itemId]);
        $cacheContext->registerEntities('advanced_wishlist', [$wishlistId]);

        $cacheContext->registerTags($cacheTags);

        $this->eventManager->dispatch('clean_cache_by_tags', ['object' => $cacheContext]);

        $this->typeList->invalidate(['full_page', 'block_html']);

        error_log("WishlistItem cache invalidated for item {$itemId}, wishlist {$wishlistId}");
        error_log("Tags invalidated: " . implode(', ', $cacheTags));
    }
}
