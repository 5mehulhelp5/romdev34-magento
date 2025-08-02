<?php
/**
  * Copyright © 2025 Romain ITOFO. Tous droits réservés.
  *
  * @author Romain ITOFO
  * @license Propriétaire
 */

declare(strict_types=1);

namespace Romain\AdvancedWishList\Model;

use Magento\Framework\ObjectManagerInterface;
use Romain\AdvancedWishList\Api\Data\WishListItemInterface;

/**
 *
 */
class WishListItemFactory
{
    /**
     * @var ObjectManagerInterface
     */
    private ObjectManagerInterface $objectManager;

    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Create new wishlist item instance
     *
     * @param array $data
     *
     * @return WishListItemInterface
     */
    public function create(array $data = []): WishListItemInterface
    {
        return $this->objectManager->create(WishListItemInterface::class, $data);
    }
}
