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
use Magento\PageCache\Model\Cache\Type as PageCacheType;

class PurgeWishlistCacheOnLogin implements ObserverInterface
{
    public function __construct(
        private readonly PageCacheType   $pageCacheType,
    ) {
    }

    public function execute(Observer $observer): void
    {
        try {
            // Purger le cache spécifique à ce customer
            $this->purgeCustomerWishlistCache();
        } catch (\Exception $e) {
            error_log('Erreur lors de la purge du cache wishlist à la connexion: ' . $e->getMessage());
        }
    }

    private function purgeCustomerWishlistCache(): void
    {
        $tags = [
            'advanced_wishlist_list',
            'advanced_wishlist_items',
        ];

        $this->pageCacheType->clean(\Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG, $tags);
    }
}
