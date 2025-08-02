<?php
/**
 * Copyright © 2025 Romain ITOFO. Tous droits réservés.
 *
 * @author  Romain ITOFO
 * @license Propriétaire
 */

declare(strict_types=1);

namespace Romain\AdvancedWishList\Controller\Index;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Theme\Block\Html\Breadcrumbs;
use Romain\AdvancedWishList\Model\ResourceModel\WishList as WishListResource;

/**
 * Share WishList Controller
 */
class Share implements HttpGetActionInterface
{
    /**
     * Constructor
     *
     * @param PageFactory      $resultPageFactory
     * @param RequestInterface $request
     * @param ManagerInterface $messageManager
     * @param RedirectFactory  $resultRedirectFactory
     * @param WishListResource $wishlistResource
     */
    public function __construct(
        private readonly PageFactory      $resultPageFactory,
        private readonly RequestInterface $request,
        private readonly ManagerInterface $messageManager,
        private readonly RedirectFactory  $resultRedirectFactory,
        private readonly WishListResource $wishlistResource
    ) {
    }

    /**
     * Execute action
     *
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        $share_code = $this->request->getParam('code');

        if (!$share_code) {
            $this->messageManager->addError(__('Invalid share link.'));
            $result_redirect = $this->resultRedirectFactory->create();

            return $result_redirect->setPath('/');
        }

        try {
            $wishlist_data = $this->wishlistResource->getByShareCode($share_code);

            if (empty($wishlist_data)) {
                $this->messageManager->addError(__('WishList not found or not public.'));
                $result_redirect = $this->resultRedirectFactory->create();

                return $result_redirect->setPath('/');
            }

            $result_page = $this->resultPageFactory->create();
            $result_page->getConfig()->getTitle()->set(__('Shared WishList: %1', $wishlist_data['name']));

            // Add breadcrumbs
            /** @var Breadcrumbs $breadcrumbs */
            $breadcrumbs = $result_page->getLayout()->getBlock('breadcrumbs');
            $breadcrumbs->addCrumb(
                'home',
                [
                    'label' => __('Home'),
                    'title' => __('Home'),
                    'link'  => '/'
                ]
            );
            $breadcrumbs->addCrumb(
                'shared_wishlist',
                [
                    'label' => __('Shared WishList'),
                    'title' => __('Shared WishList')
                ]
            );

            return $result_page;
        } catch (\Exception $e) {
            $this->messageManager->addError(__('An error occurred while loading the wishlist.'));
            $result_redirect = $this->resultRedirectFactory->create();

            return $result_redirect->setPath('/');
        }
    }
}
