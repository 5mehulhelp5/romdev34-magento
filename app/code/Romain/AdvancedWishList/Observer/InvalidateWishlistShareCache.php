<?php
/**
 * Copyright © 2025 Romain ITOFO. Tous droits réservés.
 */

declare(strict_types=1);

namespace Romain\AdvancedWishList\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\Indexer\CacheContextFactory;
use Magento\Framework\Event\Manager as EventManager;
use Romain\AdvancedWishList\Model\WishListShare;

/**
 * Observer to invalidate the wishlist share cache
 */
class InvalidateWishlistShareCache implements ObserverInterface
{
    public function __construct(
        private readonly CacheInterface      $cache,
        private readonly TypeListInterface   $typeList,
        private readonly CacheContextFactory $cacheContextFactory,
        private readonly EventManager        $eventManager
    ) {
    }

    /**
     * Invalidate the cache
     */
    public function execute(Observer $observer): void
    {
        /** @var WishListShare $wishlistShare */
        $wishlistShare = $observer->getEvent()->getData('object') ?? $observer->getEvent()->getData('wishlist_share');

        if (!$wishlistShare instanceof WishListShare) {
            return;
        }

        $this->invalidateWishlistShareCache($wishlistShare);
    }

    /**
     * Invalidate the cache
     */
    private function invalidateWishlistShareCache(WishListShare $wishlistShare): void
    {
        $shareId = $wishlistShare->getId();
        $wishlistId = $wishlistShare->getWishlistId();

        // cache tags to invalidate
        $cacheTags = [
            'advanced_wishlist_share_' . $shareId,
            'wishlist_share_' . $wishlistId,
            'wishlist_shares',
            'advanced_wishlist_' . $wishlistId,
        ];

        // Cleaning of the tags
        $this->cache->clean($cacheTags);

        $cacheContext = $this->cacheContextFactory->create();
        $cacheContext->registerEntities(WishListShare::CACHE_TAG, [$shareId]);
        $cacheContext->registerTags($cacheTags);

        $this->eventManager->dispatch('clean_cache_by_tags', ['object' => $cacheContext]);

        $this->typeList->invalidate(['full_page', 'block_html']);

        error_log("WishlistShare cache invalidated for share {$shareId}, wishlist {$wishlistId}");
    }
}
