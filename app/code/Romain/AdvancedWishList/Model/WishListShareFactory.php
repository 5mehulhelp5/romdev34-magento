<?php

declare(strict_types=1);

namespace Romain\AdvancedWishList\Model;

use Magento\Framework\ObjectManagerInterface;
use Romain\AdvancedWishList\Api\Data\WishListShareInterface;

/**
 *  Mode WishListShare factory
 */
class WishListShareFactory
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
     * Create new wishlist share instance
     *
     * @param array $data
     *
     * @return WishListShareInterface
     */
    public function create(array $data = []): WishListShareInterface
    {
        return $this->objectManager->create(WishListShareInterface::class, $data);
    }
}
