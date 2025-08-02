<?php

declare(strict_types=1);

namespace Romain\AdvancedWishList\Service;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Romain\AdvancedWishList\Api\Data\WishListInterface;
use Romain\AdvancedWishList\Api\Data\WishListShareInterface;
use Romain\AdvancedWishList\Api\WishListRepositoryInterface;
use Romain\AdvancedWishList\Model\ResourceModel\WishListShare as WishListShareResource;
use Romain\AdvancedWishList\Model\WishListShareFactory;

class WishListShareService
{
    public function __construct(
        private readonly WishListShareFactory        $wishListShareFactory,
        private readonly WishListShareResource       $wishListShareResource,
        private readonly WishListRepositoryInterface $wishListRepository,
        private readonly TransportBuilder            $transportBuilder,
        private readonly StateInterface              $inlineTranslation,
        private readonly StoreManagerInterface       $storeManager,
        private readonly ScopeConfigInterface        $scopeConfig,
        private readonly LoggerInterface             $logger
    ) {
    }

    /**
     * Share wishlist via email
     *
     * @param int         $wishlistId
     * @param string      $email
     * @param string|null $message
     * @param int         $customerId
     *
     * @return WishListShareInterface
     * @throws LocalizedException
     */
    public function shareWishlist(
        int     $wishlistId,
        string  $email,
        ?string $message,
        int     $customerId
    ): WishListShareInterface {
        try {
            // Verify wishlist exists and is public or belongs to customer
            $wishlist = $this->wishListRepository->getById($wishlistId);

            if (!$wishlist->isPublic() && $wishlist->getCustomerId() !== $customerId) {
                throw new LocalizedException(__('You can only share public wishlists or your own wishlists.'));
            }

            // Create share record
            $share = $this->wishListShareFactory->create();
            $share->setWishlistId($wishlistId)
                  ->setSharedWithEmail($email)
                  ->setShareMessage($message);

            $this->wishListShareResource->save($share);

            // Send email notification
            $this->sendShareEmail($wishlist, $email, $message);

            return $share;
        } catch (\Exception $e) {
            $this->logger->error('Error sharing wishlist: ' . $e->getMessage());
            throw new LocalizedException(__('Could not share wishlist: %1', $e->getMessage()));
        }
    }

    /**
     * Get wishlist by share code
     *
     * @param string $shareCode
     *
     * @return WishListInterface
     * @throws LocalizedException
     */
    public function getWishlistByShareCode(string $shareCode): WishListInterface
    {
        try {
            $wishlist = $this->wishListRepository->getByShareCode($shareCode);

            if (!$wishlist->isPublic()) {
                throw new LocalizedException(__('This wishlist is not public.'));
            }

            return $wishlist;
        } catch (\Exception $e) {
            throw new LocalizedException(__('Could not load shared wishlist: %1', $e->getMessage()));
        }
    }

    /**
     * Increment view count for shared wishlist
     *
     * @param string $shareCode
     *
     * @return void
     * @throws LocalizedException
     */
    public function incrementViewCount(string $shareCode): void
    {
        try {
            $wishlist = $this->getWishlistByShareCode($shareCode);

            // Find the most recent share record for this wishlist
            $connection = $this->wishListShareResource->getConnection();
            $select = $connection->select()
                                 ->from($this->wishListShareResource->getMainTable())
                                 ->where('wishlist_id = ?', $wishlist->getWishlistId())
                                 ->order('shared_at DESC')
                                 ->limit(1);

            $shareData = $connection->fetchRow($select);

            if ($shareData) {
                $share = $this->wishListShareFactory->create();
                $share->setData($shareData);
                $share->incrementViewCount();
                $this->wishListShareResource->save($share);
            }
        } catch (\Exception $e) {
            $this->logger->error('Error incrementing view count: ' . $e->getMessage());
            // Don't throw exception for view count errors
        }
    }

    /**
     * Send share email notification
     *
     * @param WishListInterface $wishlist
     * @param string                                              $email
     * @param string|null                                         $message
     *
     * @return void
     */
    private function sendShareEmail($wishlist, string $email, ?string $message): void
    {
        try {
            $this->inlineTranslation->suspend();

            $storeId = $this->storeManager->getStore()->getId();
            $shareUrl = $this->storeManager->getStore()->getBaseUrl(
                ) . 'advancedwishlist/share/view/code/' . $wishlist->getShareCode();

            $templateVars = [
                'wishlist_name' => $wishlist->getName(),
                'share_url'     => $shareUrl,
                'share_message' => $message,
                'store_name'    => $this->storeManager->getStore()->getName()
            ];

            $transport = $this->transportBuilder
                ->setTemplateIdentifier('advanced_wishlist_share_email')
                ->setTemplateOptions([
                    'area'  => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => $storeId
                ])
                ->setTemplateVars($templateVars)
                ->setFromByScope([
                    'name'  => $this->scopeConfig->getValue('trans_email/ident_general/name'),
                    'email' => $this->scopeConfig->getValue('trans_email/ident_general/email')
                ])
                ->addTo($email)
                ->getTransport();

            $transport->sendMessage();
        } catch (\Exception $e) {
            $this->logger->error('Error sending share email: ' . $e->getMessage());
        } finally {
            $this->inlineTranslation->resume();
        }
    }
}
