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

class PurgeWishlistCacheOnLogout implements ObserverInterface
{
    public function __construct(
        private readonly PageCacheType     $pageCacheType,
    ) {
    }

    public function execute(Observer $observer): void
    {
        try {
            // Purger le cache spécifique aux wishlists
            $this->purgeWishlistCache();
        } catch (\Exception $e) {
            // Logger l'erreur mais ne pas bloquer la déconnexion
            error_log('Erreur lors de la purge du cache wishlist: ' . $e->getMessage());
        }
    }

    private function purgeWishlistCache(): void
    {
        // Purger spécifiquement les pages de wishlist
        $wishlistTags = [
            'advanced_wishlist_list',
            'advanced_wishlist_items',
        ];

        $this->pageCacheType->clean(\Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG, $wishlistTags);
    }

    private function getUrlPattern(string $url): string
    {
        // Extraire le pattern de l'URL pour purger toutes les variantes
        $parsed = parse_url($url);

        return $parsed['path'] ?? '/';
    }
}
