<?php
/**
  * Copyright © 2025 Romain ITOFO. Tous droits réservés.
  *
  * @author Romain ITOFO
  * @license Propriétaire
 */

declare(strict_types=1);

namespace Romain\AdvancedWishList\Model\ResourceModel;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 *  WishListShare resource model
 */
class WishListShare extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('advanced_wishlist_share', 'share_id');
    }

    /**
     * Get shares by wishlist ID
     *
     * @param int $wishlistId
     *
     * @return array
     * @throws LocalizedException
     */
    public function getSharesByWishlistId(int $wishlistId): array
    {
        $connection = $this->getConnection();
        $select = $connection->select()
                             ->from($this->getMainTable())
                             ->where('wishlist_id = ?', $wishlistId)
                             ->order('shared_at DESC');

        return $connection->fetchAll($select);
    }

    /**
     * Get total view count for wishlist
     *
     * @param int $wishlistId
     *
     * @return int
     * @throws LocalizedException
     */
    public function getTotalViewCount(int $wishlistId): int
    {
        $connection = $this->getConnection();
        $select = $connection->select()
                             ->from($this->getMainTable(), 'SUM(view_count)')
                             ->where('wishlist_id = ?', $wishlistId);

        return (int)$connection->fetchOne($select);
    }

    /**
     * Get most recent share for wishlist
     *
     * @param int $wishlistId
     *
     * @return array|null
     * @throws LocalizedException
     */
    public function getLatestShare(int $wishlistId): ?array
    {
        $connection = $this->getConnection();
        $select = $connection->select()
                             ->from($this->getMainTable())
                             ->where('wishlist_id = ?', $wishlistId)
                             ->order('shared_at DESC')
                             ->limit(1);

        $result = $connection->fetchRow($select);

        return $result ?: null;
    }

    /**
     * Get share statistics
     *
     * @param int $wishlistId
     *
     * @return array
     * @throws LocalizedException
     */
    public function getShareStatistics(int $wishlistId): array
    {
        $connection = $this->getConnection();
        $select = $connection->select()
                             ->from($this->getMainTable(), [
                                 'total_shares' => 'COUNT(*)',
                                 'total_views'  => 'SUM(view_count)',
                                 'last_shared'  => 'MAX(shared_at)'
                             ])
                             ->where('wishlist_id = ?', $wishlistId);

        $result = $connection->fetchRow($select);

        return [
            'total_shares' => (int)($result['total_shares'] ?? 0),
            'total_views'  => (int)($result['total_views'] ?? 0),
            'last_shared'  => $result['last_shared'] ?? null
        ];
    }
}
