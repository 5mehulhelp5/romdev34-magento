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
use Romain\AdvancedWishList\Model\WishList;
use Romain\AdvancedWishList\Api\WishListRepositoryInterface;

/**
 * Observer to invalidate the cache of the wishlists
 */
class InvalidateWishlistCache implements ObserverInterface
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
        /** @var WishList $wishlist */
        $wishlist = $observer->getEvent()->getData('object') ?? $observer->getEvent()->getData('wishlist');

        if (!$wishlist instanceof WishList) {
            return;
        }

        $this->invalidateWishlistCache($wishlist);
    }

    /**
     *
     * @param WishList $wishlist
     *
     * @return void
     */
    private function invalidateWishlistCache(WishList $wishlist): void
    {
        $customerId = $wishlist->getCustomerId();
        $wishlistId = $wishlist->getId();

        $cacheTags = [
            WishList::CACHE_TAG . '_' . $wishlistId,           // Cache spécifique à cette wishlist
            'customer_wishlists_' . $customerId,               // Cache des listes du customer
            'advanced_wishlist_list',                          // Cache général des listes
            WishList::CACHE_TAG                                // Cache général du modèle
        ];

        $cacheContext = $this->cacheContextFactory->create();
        $allWishlistIds = $this->wishListRepository->getAllWishlistIds();
        $cacheContext->registerEntities(WishList::CACHE_TAG, $allWishlistIds);

        $cacheContext->registerTags($cacheTags);

        $this->eventManager->dispatch('clean_cache_by_tags', ['object' => $cacheContext]);

        $this->typeList->invalidate(['full_page', 'block_html']);
    }
}
