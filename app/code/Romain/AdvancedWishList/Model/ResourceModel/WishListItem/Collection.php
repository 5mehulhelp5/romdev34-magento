<?php
/**
 * Copyright © 2025 Romain ITOFO. Tous droits réservés.
 *
 * @author  Romain ITOFO
 * @license Propriétaire
 */

declare(strict_types=1);

namespace Romain\AdvancedWishList\Model\ResourceModel\WishListItem;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Romain\AdvancedWishList\Model\ResourceModel\WishListItem as WishListItemResource;
use Romain\AdvancedWishList\Model\WishListItem;

/**
 * WishListItem collection
 */
class Collection extends AbstractCollection
{
    protected $_idFieldName = 'item_id';

    protected function _construct(): void
    {
        $this->_init(WishListItem::class, WishListItemResource::class);
    }

    /**
     * Filter by wishlist ID
     *
     * @param int $wishlistId
     *
     * @return $this
     */
    public function addWishlistFilter(int $wishlistId): static
    {
        $this->addFieldToFilter('wishlist_id', $wishlistId);

        return $this;
    }

    /**
     * Filter by product ID
     *
     * @param int $productId
     *
     * @return $this
     */
    public function addProductFilter(int $productId): static
    {
        $this->addFieldToFilter('product_id', $productId);

        return $this;
    }

    /**
     * Join with product table to get product information
     *
     * @return $this
     */
    public function joinProductTable(): static
    {
        $this->getSelect()->join(
            ['product' => $this->getTable('catalog_product_entity')],
            'main_table.product_id = product.entity_id',
            ['sku', 'type_id']
        );

        return $this;
    }

    /**
     * Join with product varchar table to get product name
     *
     * @return $this
     */
    public function joinProductName(): static
    {
        $connection = $this->getConnection();

        // Get name attribute ID
        $name_attribute_id = $connection->fetchOne(
            $connection->select()
                       ->from($this->getTable('eav_attribute'), 'attribute_id')
                       ->where('entity_type_id = ?', 4) // Product entity type
                       ->where('attribute_code = ?', 'name')
        );

        if ($name_attribute_id) {
            $this->getSelect()->joinLeft(
                ['product_name' => $this->getTable('catalog_product_entity_varchar')],
                "main_table.product_id = product_name.entity_id AND product_name.attribute_id = {$name_attribute_id} AND product_name.store_id = 0",
                ['product_name' => 'value']
            );
        }

        return $this;
    }

    /**
     * Filter items with price alerts
     *
     * @return $this
     */
    public function addPriceAlertFilter(): static
    {
        $this->addFieldToFilter('price_alert', 1);
        $this->addFieldToFilter('target_price', ['notnull' => true]);

        return $this;
    }

    /**
     * Order by added date
     *
     * @param string $direction
     *
     * @return $this
     */
    public function orderByAddedDate(string $direction = 'DESC'): static
    {
        $this->setOrder('added_at', $direction);

        return $this;
    }
}
