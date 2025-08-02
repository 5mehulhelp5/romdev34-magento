<?php
/**
 * Copyright © 2025 Romain ITOFO. Tous droits réservés.
 *
 * @author  Romain ITOFO
 * @license Propriétaire
 */

declare(strict_types=1);

namespace Romain\AdvancedWishList\Block\WishList;

use Magento\Framework\View\Element\Template\Context;
use Romain\AdvancedWishList\Block\AbsctractWishList;
use Romain\AdvancedWishList\Model\ResourceModel\WishList\CollectionFactory;
use Romain\AdvancedWishList\Model\ResourceModel\WishListItem\CollectionFactory as WishListItemCollectionFactory;
use Romain\AdvancedWishList\Model\ResourceModel\WishList as WishListResource;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\DataObject\IdentityInterface;
use Romain\AdvancedWishList\Api\WishListRepositoryInterface;
use Romain\AdvancedWishList\Api\WishListItemRepositoryInterface;
use Romain\AdvancedWishList\Api\Data\WishListInterface;

/**
 * Block to handle the view of the wishlist page advancedwishlist
 */
class Index extends AbsctractWishList implements IdentityInterface
{
    private WishListInterface $wishlist;

    public function __construct(
        Context                                          $context,
        CustomerSession                                  $customerSession,
        HttpContext                                      $httpContext,
        private readonly CollectionFactory               $wishListCollectionFactory,
        private readonly WishListResource                $wishListResource,
        private readonly WishListItemCollectionFactory   $wishListItemCollectionFactory,
        private readonly WishListRepositoryInterface     $wishListRepository,
        private readonly WishListItemRepositoryInterface $wishListItemRepository,
        array                                            $data = []
    ) {
        parent::__construct($context, $customerSession, $httpContext, $data);
    }

    /**
     * Get cache tags - avec tags spécifiques pour la purge
     *
     * @return array
     */
    protected function getCacheTags(): array
    {
        $tags = parent::getCacheTags();

        // Ajouter des tags génériques
        $tags[] = 'advanced_wishlist';

        if ($this->isCustomerLoggedIn()) {
            $customerId = $this->getCustomerId();
            $tags[] = 'advanced_wishlist_customer_' . $customerId;
        } else {
            $tags[] = 'advanced_wishlist_guest';
        }

        return $tags;
    }


    /**
     * Get identities from wishlist model
     *
     * @return array|string[]
     */
    public function getIdentities(): array
    {
        $identities = [];
        foreach ($this->getCustomerWishlistsWithCounts() as $wishlist) {
            $identities = array_merge($identities, $wishlist['wishlist']->getIdentities());
        };
        return array_unique($identities);
    }

    /**
     * Get customer wishlists with item counts
     *
     * @return array
     */
    public function getCustomerWishlistsWithCounts(): array
    {
        if (!$this->customerSession->isLoggedIn()) {
            return [];
        }

        try {
            $customer_id = $this->getCustomerId();
            $wishlists = $this->wishListRepository->getByCustomerId($customer_id);

            $wishlists_with_counts = [];
            foreach ($wishlists as $wishlist) {
                $items = $this->wishListItemRepository->getByWishlistId((int)$wishlist->getId());
                $wishlists_with_counts[] = [
                    'wishlist'   => $wishlist,
                    'item_count' => count($items)
                ];
            }

            return $wishlists_with_counts;
        } catch (\Exception) {
            return [];
        }
    }

    /**
     * @param $wishlistId
     *
     * @return void
     */
    public function setWishList($wishlistId): void
    {
        $this->wishlist = $this->wishListRepository->getById($wishlistId);
    }

    /**
     * Retrieves the wishlist for the current user.
     *
     * @return WishListInterface
     */
    public function getWishList(): WishListInterface
    {
        return $this->wishlist;
    }

    /**
     * Get customer wishlists
     */
    public function getCustomerWishLists(): array
    {
        $customer_id = $this->getCustomerId();
        if (!$customer_id) {
            return [];
        }

        try {
            return $this->wishListResource->getCustomerWishLists($customer_id);
        } catch (\Exception $e) {
            $this->_logger->error('Error getting wishlists: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Get wishlist collection for current customer
     */
    public function getWishListCollection(): ?WishListResource\Collection
    {
        $customer_id = $this->getCustomerId();
        if (!$customer_id) {
            return null;
        }

        $collection = $this->wishListCollectionFactory->create();
        $collection->addFieldToFilter(WishListInterface::CUSTOMER_ID, $customer_id)
                   ->setOrder(WishListInterface::IS_DEFAULT, 'DESC')
                   ->setOrder(WishListInterface::CREATED_AT, 'ASC');

        return $collection;
    }

    /**
     * Get wishlist view URL
     */
    public function getWishListViewUrl(int $wishlistId): string
    {
        return $this->getUrl('advancedwishlist/wishlist/view', ['id' => $wishlistId]);
    }

    /**
     * Get wishlist edit URL
     */
    public function getWishListEditUrl(int $wishlistId): string
    {
        return $this->getUrl('advancedwishlist/index/edit', ['id' => $wishlistId]);
    }

    /**
     * Get wishlist delete URL
     */
    public function getWishListDeleteUrl(int $wishlistId): string
    {
        return $this->getUrl('advancedwishlist/index/delete', ['id' => $wishlistId]);
    }

    /**
     * Get wishlist share URL
     */
    public function getWishListShareUrl(string $shareCode): string
    {
        return $this->getUrl('advancedwishlist/index/share', [WishListInterface::SHARE_CODE => $shareCode]);
    }

    /**
     * Format wishlist date
     */
    public function formatWishListDate(string $date): string
    {
        return $this->formatDate($date, \IntlDateFormatter::MEDIUM);
    }



    /**
     * Get create new wishlist URL
     */
    public function getCreateWishListUrl(): string
    {
        return $this->getUrl('advancedwishlist/wishlist/create');
    }

    public function getItemsCount(int $wishlistId): int
    {
        $collection = $this->wishListItemCollectionFactory->create();
        $collection->addFieldToFilter(WishListInterface::WISHLIST_ID, $wishlistId);

        return $collection->getSize();
    }
}
