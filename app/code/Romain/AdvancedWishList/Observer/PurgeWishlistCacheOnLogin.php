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
use Magento\Customer\Model\Session as CustomerSession;

class PurgeWishlistCacheOnLogin implements ObserverInterface
{
    public function __construct(
        private readonly PageCacheType   $pageCacheType,
        private readonly CustomerSession $customerSession
    ) {
    }

    public function execute(Observer $observer): void
    {
        try {
            $customer = $observer->getEvent()->getCustomer();
            $customerId = $customer ? $customer->getId() : $this->customerSession->getCustomerId();

            if ($customerId) {
                // Purger le cache spécifique à ce customer
                $this->purgeCustomerWishlistCache((int)$customerId);
            }
        } catch (\Exception $e) {
            error_log('Erreur lors de la purge du cache wishlist à la connexion: ' . $e->getMessage());
        }
    }

    private function purgeCustomerWishlistCache(int $customerId): void
    {
        $tags = [
            'advanced_wishlist',
            'advanced_wishlist_customer_' . $customerId,
            'BLOCK_TPL'
        ];

        $this->pageCacheType->clean(\Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG, $tags);
    }
}
