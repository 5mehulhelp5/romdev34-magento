<?php
/**
 * Copyright © 2025 Romain ITOFO. Tous droits réservés.
 *
 * @author  Romain ITOFO
 * @license Propriétaire
 */

declare(strict_types=1);

namespace Romain\AdvancedWishList\Model;

use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsFactory;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Romain\AdvancedWishList\Api\Data\WishListInterface;
use Romain\AdvancedWishList\Api\WishListRepositoryInterface;
use Romain\AdvancedWishList\Model\ResourceModel\WishList as WishListResource;
use Romain\AdvancedWishList\Model\ResourceModel\WishList\CollectionFactory as WishListCollectionFactory;

class WishListRepository implements WishListRepositoryInterface
{
    /**
     * @param WishListResource             $resource
     * @param WishListFactory              $wishlistFactory
     * @param WishListCollectionFactory    $collectionFactory
     * @param SearchResultsFactory         $searchResultsFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        private readonly WishListResource             $resource,
        private readonly WishListFactory              $wishlistFactory,
        private readonly WishListCollectionFactory    $collectionFactory,
        private readonly SearchResultsFactory         $searchResultsFactory,
        private readonly CollectionProcessorInterface $collectionProcessor
    ) {
    }

    /**
     * @throws CouldNotSaveException
     */
    public function save(WishListInterface $wishlist): WishListInterface
    {
        try {
            $this->resource->save($wishlist);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__('Unable to save wishlist: %1', $e->getMessage()));
        }

        return $wishlist;
    }

    /**
     * @param int $id
     *
     * @return WishListInterface
     * @throws NoSuchEntityException
     */
    public function getById(int $id): WishListInterface
    {
        $wishlist = $this->wishlistFactory->create();
        $this->resource->load($wishlist, $id);
        if (!$wishlist->getId()) {
            throw new NoSuchEntityException(__('WishList with id %1 does not exist.', $id));
        }

        return $wishlist;
    }

    /**
     * @param WishListInterface $wishlist
     *
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(WishListInterface $wishlist): bool
    {
        try {
            $this->resource->delete($wishlist);
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(__('Unable to delete wishlist: %1', $e->getMessage()));
        }

        return true;
    }

    /**
     * @param int $id
     *
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById(int $id): bool
    {
        $wishlist = $this->getById($id);

        return $this->delete($wishlist);
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     *
     * @return SearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria): SearchResultsInterface
    {
        $collection = $this->collectionFactory->create();

        $this->collectionProcessor->process($searchCriteria, $collection);

        $search_results = $this->searchResultsFactory->create();
        $search_results->setItems($collection->getItems());
        $search_results->setTotalCount($collection->getSize());
        $search_results->setSearchCriteria($searchCriteria);

        return $search_results;
    }

    /**
     * Get wishlist by customer ID.
     *
     * @param int $customerId
     *
     * @return WishListInterface[]
     */
    public function getByCustomerId(int $customerId): array
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('customer_id', $customerId);

        return $collection->getItems();
    }

    /**
     * Get all wishlists.
     *
     * @return WishListInterface[]
     */
    public function getAll(): array
    {
        $collection = $this->collectionFactory->create();

        return $collection->getItems();
    }

    /**
     * Set default wishlist for customer
     *
     * @param int $customerId
     * @param int $wishlistId
     *
     * @return void
     * @throws CouldNotSaveException
     */
    public function setDefaultWishList(int $customerId, int $wishlistId): void
    {
        try {
            // Vérifier que la wishlist existe et appartient au customer
            $wishlist = $this->getById($wishlistId);
            if ($wishlist->getCustomerId() !== $customerId) {
                throw new NoSuchEntityException(
                    __('Wishlist with id "%1" does not belong to customer "%2"', $wishlistId, $customerId)
                );
            }

            // Récupérer toutes les wishlists du customer
            $collection = $this->collectionFactory->create();
            $collection->addFieldToFilter('customer_id', $customerId);

            // Mettre à jour toutes les wishlists du customer
            foreach ($collection as $customer_wishlist) {
                /** @var WishListInterface $customer_wishlist */
                if ($customer_wishlist->getId() == $wishlistId) {
                    $customer_wishlist->setIsDefault(true);
                } else {
                    $customer_wishlist->setIsDefault(false);
                }
                $this->resource->save($customer_wishlist);
            }
        } catch (\Exception $e) {
            throw new CouldNotSaveException(
                __('Could not set default wishlist: %1', $e->getMessage())
            );
        }
    }

    /**
     * Get wishlist by share code
     *
     * @param string $shareCode
     *
     * @return WishListInterface|null
     * @throws NoSuchEntityException
     */
    public function getByShareCode(string $shareCode): ?WishListInterface
    {
        try {
            $collection = $this->collectionFactory->create();
            $collection->addFieldToFilter('share_code', $shareCode)
                       ->addFieldToFilter('is_public', 1)
                       ->setPageSize(1);

            $wishlist = $collection->getFirstItem();

            if (!$wishlist->getId()) {
                throw new NoSuchEntityException(
                    __('Wishlist with share code "%1" does not exist or is not public', $shareCode)
                );
            }

            return $wishlist;
        } catch (\Exception $e) {
            throw new NoSuchEntityException(
                __('Could not load wishlist with share code "%1": %2', $shareCode, $e->getMessage())
            );
        }
    }

    /**
     * Get all wishlist IDs
     *
     * @return array
     */
    public function getAllWishlistIds(): array
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToSelect('wishlist_id');

        return $collection->getColumnValues('wishlist_id');
    }

}
