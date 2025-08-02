<?php
/**
 * Copyright © 2025 Romain ITOFO. Tous droits réservés.
 *
 * @author  Romain ITOFO
 * @license Propriétaire
 */

declare(strict_types=1);

namespace Romain\AdvancedWishList\Model\ResourceModel;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 *  WishListItem resource model
 */
class WishListItem extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('advanced_wishlist_item', 'item_id');
    }

    /**
     * Get items by wishlist ID
     *
     * @param int $wishlistId
     *
     * @return array
     * @throws LocalizedException
     */
    public function getItemsByWishlistId(int $wishlistId): array
    {
        $connection = $this->getConnection();
        $select = $connection->select()
                             ->from($this->getMainTable())
                             ->where('wishlist_id = ?', $wishlistId);

        return $connection->fetchAll($select);
    }

    /**
     * Check if product exists in wishlist
     *
     * @param int $wishlistId
     * @param int $productId
     *
     * @return bool
     * @throws LocalizedException
     */
    public function isProductInWishlist(int $wishlistId, int $productId): bool
    {
        $connection = $this->getConnection();
        $select = $connection->select()
                             ->from($this->getMainTable(), 'COUNT(*)')
                             ->where('wishlist_id = ?', $wishlistId)
                             ->where('product_id = ?', $productId);

        return (bool)$connection->fetchOne($select);
    }

    /**
     * Remove product from wishlist
     *
     * @param int $wishlistId
     * @param int $productId
     *
     * @return int
     * @throws LocalizedException
     */
    public function removeProduct(int $wishlistId, int $productId): int
    {
        $connection = $this->getConnection();

        return $connection->delete(
            $this->getMainTable(),
            [
                'wishlist_id = ?' => $wishlistId,
                'product_id = ?'  => $productId
            ]
        );
    }

    /**
     * Get items with price alerts
     *
     * @return array
     * @throws LocalizedException
     */
    public function getItemsWithPriceAlerts(): array
    {
        $connection = $this->getConnection();
        $select = $connection->select()
                             ->from($this->getMainTable())
                             ->where('price_alert = ?', 1)
                             ->where('target_price IS NOT NULL');

        return $connection->fetchAll($select);
    }
}
