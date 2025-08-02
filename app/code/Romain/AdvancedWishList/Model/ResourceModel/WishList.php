<?php
/**
  * Copyright © 2025 Romain ITOFO. Tous droits réservés.
  *
  * @author Romain ITOFO
  * @license Propriétaire
 */

declare(strict_types=1);

namespace Romain\AdvancedWishList\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Exception\LocalizedException;

/**
 * Advanced WishList Resource Model
 */
class WishList extends AbstractDb
{
    public const TABLE_NAME  = 'advanced_wishlist';
    public const PRIMARY_KEY = 'wishlist_id';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init(self::TABLE_NAME, self::PRIMARY_KEY);
    }

    /**
     * Get wishlist by share code
     *
     * @param string $shareCode
     *
     * @return array
     * @throws LocalizedException
     */
    public function getByShareCode(string $shareCode): array
    {
        $connection = $this->getConnection();
        $select = $connection->select()
                             ->from($this->getMainTable())
                             ->where('share_code = ?', $shareCode)
                             ->where('is_public = ?', 1);

        $result = $connection->fetchRow($select);

        return $result ?: [];
    }

    /**
     * Get customer wishlists
     *
     * @param int $customerId
     *
     * @return array
     * @throws LocalizedException
     */
    public function getCustomerWishLists(int $customerId): array
    {
        $connection = $this->getConnection();
        $select = $connection->select()
                             ->from($this->getMainTable())
                             ->where('customer_id = :customer_id')  // Utiliser un placeholder nommé
                             ->order(['is_default DESC', 'created_at ASC']);  // Tableau pour ORDER BY

        // Bind des paramètres de manière sécurisée
        $bind = ['customer_id' => $customerId];

        return $connection->fetchAll($select, $bind);
    }

    /**
     * Get default wishlist for customer
     *
     * @param int $customerId
     *
     * @return array
     * @throws LocalizedException
     */
    public function getDefaultWishList(int $customerId): array
    {
        $connection = $this->getConnection();
        $select = $connection->select()
                             ->from($this->getMainTable())
                             ->where('customer_id = ?', $customerId)
                             ->where('is_default = ?', 1)
                             ->limit(1);

        $result = $connection->fetchRow($select);

        return $result ?: [];
    }

    /**
     * Check if share code exists
     *
     * @param string   $shareCode
     * @param int|null $excludeId
     *
     * @return bool
     * @throws LocalizedException
     */
    public function shareCodeExists(string $shareCode, ?int $excludeId = null): bool
    {
        $connection = $this->getConnection();
        $select = $connection->select()
                             ->from($this->getMainTable(), 'COUNT(*)')
                             ->where('share_code = ?', $shareCode);

        if ($excludeId) {
            $select->where('wishlist_id != ?', $excludeId);
        }

        return (bool)$connection->fetchOne($select);
    }
}
