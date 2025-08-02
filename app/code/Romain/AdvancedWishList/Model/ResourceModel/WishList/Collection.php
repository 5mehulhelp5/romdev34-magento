<?php
/**
 * Copyright © 2025 Romain ITOFO. Tous droits réservés.
 *
 * @author  Romain ITOFO
 * @license Propriétaire
 */

declare(strict_types=1);

namespace Romain\AdvancedWishList\Model\ResourceModel\WishList;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Romain\AdvancedWishList\Model\ResourceModel\WishList as WishListResource;
use Romain\AdvancedWishList\Model\WishList as WishListModel;

/**
 * Advanced WishList Collection
 */
class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'wishlist_id';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init(WishListModel::class, WishListResource::class);
    }

    /**
     * Filter by customer
     *
     * @param int $customerId
     *
     * @return $this
     */
    public function addCustomerFilter(int $customerId): self
    {
        $this->addFieldToFilter('customer_id', $customerId);

        return $this;
    }

    /**
     * Filter public wishlists only
     *
     * @return $this
     */
    public function addPublicFilter(): self
    {
        $this->addFieldToFilter('is_public', 1);

        return $this;
    }

    /**
     * Filter by default wishlist
     *
     * @param bool $isDefault
     *
     * @return $this
     */
    public function addDefaultFilter(bool $isDefault = true): self
    {
        $this->addFieldToFilter('is_default', $isDefault ? 1 : 0);

        return $this;
    }

    /**
     * Add items count to collection
     *
     * @return $this
     */
    public function addItemsCount(): self
    {
        $this->getSelect()->joinLeft(
            ['items' => $this->getTable('advanced_wishlist_item')],
            'main_table.wishlist_id = items.wishlist_id',
            ['items_count' => 'COUNT(items.item_id)']
        )->group('main_table.wishlist_id');

        return $this;
    }

    /**
     * Add customer data to collection
     *
     * @return $this
     */
    public function addCustomerData(): self
    {
        $this->getSelect()->joinLeft(
            ['customer' => $this->getTable('customer_entity')],
            'main_table.customer_id = customer.entity_id',
            [
                'customer_email' => 'customer.email',
                'customer_firstname' => 'customer.firstname',
                'customer_lastname' => 'customer.lastname'
            ]
        );

        return $this;
    }

    /**
     * Order by default first, then by creation date
     *
     * @return $this
     */
    public function addDefaultOrder(): self
    {
        $this->getSelect()->order(['is_default DESC', 'created_at ASC']);

        return $this;
    }
}
