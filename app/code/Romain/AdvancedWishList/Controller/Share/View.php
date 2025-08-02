<?php
/**
 * Copyright © 2025 Romain ITOFO. Tous droits réservés.
 *
 * @author  Romain ITOFO
 * @license Propriétaire
 */

declare(strict_types=1);

namespace Romain\AdvancedWishList\Controller\Share;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\View\Result\PageFactory;
use Psr\Log\LoggerInterface;
use Romain\AdvancedWishList\Service\WishListShareService;

/**
 * Class view Share list
 */
class View implements HttpGetActionInterface
{
    public function __construct(
        private readonly RequestInterface     $request,
        private readonly PageFactory          $pageFactory,
        private readonly RedirectFactory      $redirectFactory,
        private readonly ManagerInterface     $messageManager,
        private readonly WishListShareService $wishlistShareService,
        private readonly LoggerInterface      $logger
    ) {
    }

    public function execute(): ResultInterface
    {
        $share_code = $this->request->getParam('code');

        if (!$share_code) {
            $this->messageManager->addErrorMessage(__('Invalid share code.'));
            $redirect = $this->redirectFactory->create();

            return $redirect->setPath('/');
        }

        try {
            $wishlist = $this->wishlistShareService->getWishlistByShareCode($share_code);

            // Increment view count
            $this->wishlistShareService->incrementViewCount($share_code);

            // Create page and set wishlist data
            $page = $this->pageFactory->create();
            $page->getConfig()->getTitle()->set(__('Shared Wishlist: %1', $wishlist->getName()));

            // Pass wishlist to block
            $block = $page->getLayout()->getBlock('shared.wishlist.view');
            if ($block) {
                $block->setWishlist($wishlist);
            }

            return $page;
        } catch (\Exception $e) {
            $this->logger->error('Error viewing shared wishlist: ' . $e->getMessage());
            $this->messageManager->addErrorMessage(__('Could not load shared wishlist.'));

            $redirect = $this->redirectFactory->create();

            return $redirect->setPath('/');
        }
    }
}
