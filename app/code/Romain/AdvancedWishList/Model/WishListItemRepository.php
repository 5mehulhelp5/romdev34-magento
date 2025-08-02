<?php

declare(strict_types=1);

namespace Romain\AdvancedWishList\Model;

use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Romain\AdvancedWishList\Api\Data\WishListItemInterface;
use Romain\AdvancedWishList\Api\WishListItemRepositoryInterface;
use Romain\AdvancedWishList\Model\ResourceModel\WishListItem as WishListItemResource;
use Romain\AdvancedWishList\Model\ResourceModel\WishListItem\CollectionFactory;

class WishListItemRepository implements WishListItemRepositoryInterface
{
    public function __construct(
        private readonly WishListItemResource $resource,
        private readonly WishListItemFactory  $wishListItemFactory,
        private readonly CollectionFactory    $collectionFactory,
    ) {
    }

    /**
     * @param WishListItemInterface $wishListItem
     *
     * @return WishListItemInterface
     * @throws CouldNotSaveException
     */
    public function save(WishListItemInterface $wishListItem): WishListItemInterface
    {
        try {
            $this->resource->save($wishListItem);

        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__('Could not save the wishlist item: %1', $exception->getMessage()));
        }
        return $wishListItem;
    }

    /**
     * @param int $itemId
     *
     * @return WishListItemInterface
     * @throws NoSuchEntityException
     */
    public function getById(int $itemId): WishListItemInterface
    {
        $wish_list_item = $this->wishListItemFactory->create();
        $this->resource->load($wish_list_item, $itemId);
        if (!$wish_list_item->getItemId()) {
            throw new NoSuchEntityException(__('Wishlist item with id "%1" does not exist.', $itemId));
        }
        return $wish_list_item;
    }

    /**
     * @param WishListItemInterface $wishListItem
     *
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(WishListItemInterface $wishListItem): bool
    {
        try {
            $this->resource->delete($wishListItem);

        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__('Could not delete the wishlist item: %1', $exception->getMessage()));
        }
        return true;
    }

    /**
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById(int $itemId): bool
    {
        return $this->delete($this->getById($itemId));
    }

    /**
     * @param int $wishlistId
     *
     * @return array|WishListItemInterface[]
     */
    public function getByWishlistId(int $wishlistId): array
    {
        $collection = $this->collectionFactory->create();
        $collection->addWishlistFilter($wishlistId)
            ->joinProductTable()
            ->joinProductName()
            ->orderByAddedDate();

        return $collection->getItems();
    }

    /**
     * @param int   $wishlistId
     * @param int   $productId
     * @param int   $storeId
     * @param array $options
     *
     * @return WishListItemInterface
     * @throws CouldNotSaveException
     * @throws LocalizedException
     */
    public function addProductToWishlist(int $wishlistId, int $productId, int $storeId, array $options = []): WishListItemInterface
    {
        // Check if product already exists in wishlist
        if ($this->resource->isProductInWishlist($wishlistId, $productId)) {
            throw new CouldNotSaveException(__('Product is already in the wishlist.'));
        }

        $wish_list_item = $this->wishListItemFactory->create();
        $wish_list_item->setWishlistId($wishlistId)
            ->setProductId($productId)
            ->setStoreId($storeId)
            ->setQty($options['qty'] ?? 1.0)
            ->setDescription($options['description'] ?? null)
            ->setPriceAlert($options['price_alert'] ?? false)
            ->setTargetPrice($options['target_price'] ?? null);

        return $this->save($wish_list_item);
    }

    /**
     * @param int $wishlistId
     * @param int $productId
     *
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function removeProductFromWishlist(int $wishlistId, int $productId): bool
    {
        try {
            $collection = $this->collectionFactory->create();
            $item = $collection->addWishlistFilter($wishlistId)
                              ->addProductFilter($productId)
                              ->getFirstItem();
            $rows_affected = $this->resource->removeProduct($wishlistId, $productId);

            return $rows_affected > 0;
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__('Could not remove product from wishlist: %1', $exception->getMessage()));
        }
    }

    /**
     * @param int $wishlistId
     * @param int $productId
     *
     * @return bool
     * @throws LocalizedException
     */
    public function isProductInWishlist(int $wishlistId, int $productId): bool
    {
        return $this->resource->isProductInWishlist($wishlistId, $productId);
    }

    /**
     * @return array|WishListItemInterface[]
     */
    public function getItemsWithPriceAlerts(): array
    {
        $collection = $this->collectionFactory->create();
        $collection->addPriceAlertFilter()
            ->joinProductTable();

        return $collection->getItems();
    }
}
