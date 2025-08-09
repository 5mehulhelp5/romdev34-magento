<?php

declare(strict_types=1);

namespace Romain\AdvancedWishList\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Romain\AdvancedWishList\Api\WishListRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Psr\Log\LoggerInterface;

class AdvancedWishList implements SectionSourceInterface
{
    /**
     * @var CurrentCustomer
     */
    private CurrentCustomer $currentCustomer;

    /**
     * @var WishListRepositoryInterface
     */
    private WishListRepositoryInterface $wishListRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private SearchCriteriaBuilder $searchCriteriaBuilder;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @param CurrentCustomer             $currentCustomer
     * @param WishListRepositoryInterface $wishListRepository
     * @param SearchCriteriaBuilder       $searchCriteriaBuilder
     * @param LoggerInterface             $logger
     */
    public function __construct(
        CurrentCustomer             $currentCustomer,
        WishListRepositoryInterface $wishListRepository,
        SearchCriteriaBuilder       $searchCriteriaBuilder,
        LoggerInterface             $logger
    ) {
        $this->currentCustomer = $currentCustomer;
        $this->wishListRepository = $wishListRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function getSectionData(): array
    {
        $customerId = $this->currentCustomer->getCustomerId();

        if (!$customerId) {
            return [
                'counter' => 0,
                'items'   => []
            ];
        }

        try {
            $searchCriteria = $this->searchCriteriaBuilder
                ->addFilter('customer_id', $customerId)
                ->create();

            $wishListSearchResults = $this->wishListRepository->getList($searchCriteria);
            $wishLists = $wishListSearchResults->getItems();

            return [
                'wishlist_count' => count($wishLists),
            ];
        } catch (\Exception $e) {
            $this->logger->error('Error getting wishlist data: ' . $e->getMessage());

            return [
                'counter' => 0,
                'items'   => []
            ];
        }
    }
}
