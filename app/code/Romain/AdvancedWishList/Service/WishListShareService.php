<?php
/**
 * Améliorations suggérées pour votre WishListShareService
 */

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
     */
    public function shareWishlist(
        int     $wishlistId,
        string  $email,
        ?string $message,
        int     $customerId
    ): WishListShareInterface {
        try {
            // Verify wishlist exists and is accessible
            $wishlist = $this->wishListRepository->getById($wishlistId);

            // ⭐ Vérifications de sécurité améliorées
            if (!$this->canShareWishlist($wishlist, $customerId)) {
                throw new LocalizedException(__('You are not allowed to share this wishlist.'));
            }

            // Create share record avec données enrichies
            $share = $this->wishListShareFactory->create();
            $share->setWishlistId($wishlistId)
                  ->setSharedWithEmail($email)
                  ->setShareMessage($message)
                  ->setSharedAt(date('Y-m-d H:i:s'))
                  ->setViewCount(0);

            // ⭐ IMPORTANT: Utiliser save() pour déclencher les événements de cache
            $share->save(); // Ceci déclenche afterSave() et les observers !
            // Send email notification
            $this->sendShareEmail($wishlist, $email, $message);

            return $share;
        } catch (\Exception $e) {
            $this->logger->error('Error sharing wishlist: ' . $e->getMessage(), [
                'wishlist_id' => $wishlistId,
                'email'       => $email,
                'customer_id' => $customerId
            ]);
            throw new LocalizedException(__('Could not share wishlist: %1', $e->getMessage()));
        }
    }

    /**
     * ⭐ Vérifier les permissions de partage
     */
    private function canShareWishlist(WishListInterface $wishlist, int $customerId): bool
    {
        // Le propriétaire peut toujours partager
        if ($wishlist->getCustomerId() === $customerId) {
            return true;
        }

        // Les wishlists publiques peuvent être partagées par n'importe qui (optionnel)
        if ($wishlist->isPublic()) {
            return true; // ou false selon votre logique business
        }

        return false;
    }

    /**
     * Get wishlist by share code avec gestion du cache
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
            $this->logger->error('Error loading shared wishlist: ' . $e->getMessage(), [
                'share_code' => $shareCode
            ]);
            throw new LocalizedException(__('Could not load shared wishlist: %1', $e->getMessage()));
        }
    }

    /**
     * Increment view count avec gestion améliorée
     */
    public function incrementViewCount(string $shareCode): void
    {
        try {
            $wishlist = $this->getWishlistByShareCode($shareCode);

            $connection = $this->wishListShareResource->getConnection();
            $select = $connection->select()
                                 ->from($this->wishListShareResource->getMainTable())
                                 ->where('wishlist_id = ?', $wishlist->getWishlistId())
                                 ->order('shared_at DESC')
                                 ->limit(1);

            $shareData = $connection->fetchRow($select);

            if ($shareData) {
                // ⭐ Charger le modèle complet
                $share = $this->wishListShareFactory->create();
                $this->wishListShareResource->load($share, $shareData['share_id']);

                if ($share->getId()) {
                    $share->incrementViewCount();
                    $share->save(); // Déclenche les événements de cache !
                }
            }
        } catch (\Exception $e) {
            $this->logger->error('Error incrementing view count: ' . $e->getMessage(), [
                'share_code' => $shareCode
            ]);
            // Don't throw exception for view count errors
        }
    }

    /**
     * Send share email notification (votre code existant amélioré)
     */
    private function sendShareEmail(WishListInterface $wishlist, string $email, ?string $message): void
    {
        try {
            $this->inlineTranslation->suspend();

            $storeId = $this->storeManager->getStore()->getId();
            $shareUrl = $this->storeManager->getStore()->getBaseUrl() .
                'advancedwishlist/share/view/code/' . $wishlist->getShareCode();

            $templateVars = [
                'wishlist_name'        => $wishlist->getName(),
                'wishlist_description' => $wishlist->getDescription(),
                'share_url'            => $shareUrl,
                'share_message'        => $message,
                'store_name'           => $this->storeManager->getStore()->getName(),
                'sender_name'          => 'Customer' // Vous pouvez enrichir avec le nom du customer
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

            $this->logger->info('Wishlist share email sent successfully', [
                'wishlist_id' => $wishlist->getId(),
                'recipient'   => $email
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Error sending share email: ' . $e->getMessage(), [
                'wishlist_id' => $wishlist->getId(),
                'recipient'   => $email
            ]);
            // Ne pas faire échouer le partage si l'email échoue
        } finally {
            $this->inlineTranslation->resume();
        }
    }
}
