<?php
/**
 * Copyright © 2025 Romain ITOFO. Tous droits réservés.
 *
 * @author  Romain ITOFO
 * @license Propriétaire
 */

declare(strict_types=1);

namespace Romain\AdvancedWishList\Controller\Index;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Indexer\CacheContextFactory;
use Magento\Framework\Message\ManagerInterface;
use Random\RandomException;
use Romain\AdvancedWishList\Api\Data\WishListInterface;
use Romain\AdvancedWishList\Controller\AbstractWishList;
use Romain\AdvancedWishList\Model\WishListFactory;
use Magento\Framework\Event\Manager as EventManager;
use Magento\Framework\App\CacheInterface;
use Romain\AdvancedWishList\Api\WishListRepositoryInterface;
use Magento\Customer\Model\Session as CustomerSession;

/**
 * Create WishList Controller
 */
class Create extends AbstractWishList implements HttpPostActionInterface, CsrfAwareActionInterface
{
    /**
     * Constructor
     *
     * @param JsonFactory                 $resultJsonFactory
     * @param RedirectFactory             $resultRedirectFactory
     * @param RequestInterface            $request
     * @param ManagerInterface            $messageManager
     * @param WishListFactory             $wishListFactory
     * @param Validator                   $formKeyValidator
     * @param CacheContextFactory         $cacheContextFactory
     * @param EventManager                $eventManager
     * @param CacheInterface              $cache
     * @param TypeListInterface           $typeList
     * @param WishListRepositoryInterface $wishListRepository
     * @param CustomerSession             $customerSession
     */
    public function __construct(
        private readonly JsonFactory      $resultJsonFactory,
        private readonly RedirectFactory  $resultRedirectFactory,
        private readonly RequestInterface $request,
        private readonly ManagerInterface $messageManager,
        private readonly WishListFactory  $wishListFactory,
        private readonly Validator        $formKeyValidator,
        CacheContextFactory               $cacheContextFactory,
        EventManager                      $eventManager,
        CacheInterface                    $cache,
        TypeListInterface                 $typeList,
        WishListRepositoryInterface       $wishListRepository,
        CustomerSession                   $customerSession,
    ) {
        parent::__construct(
            $cache,
            $typeList,
            $resultJsonFactory,
            $wishListRepository,
            $customerSession,
            $cacheContextFactory,
            $eventManager
        );
    }

    /**
     * Execute action
     *
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        $result_json = $this->resultJsonFactory->create();
        $result_redirect = $this->resultRedirectFactory->create();
        // Validate form key if necessary
        if (!$this->formKeyValidator->validate($this->request)) {
            $this->messageManager->addErrorMessage(__('Invalid form key. Please refresh the page.'));

            return $result_redirect->setRefererUrl();
        }
        // Check if customer is logged in
        if (!$this->customerSession->isLoggedIn()) {
            return $result_json->setData([
                'success' => false,
                'message' => __('You must be logged in to create a wishlist.')
            ]);
        }
        try {
            $name = $this->request->getParam(WishListInterface::NAME, '');
            $description = $this->request->getParam(WishListInterface::DESCRIPTION, '');
            $is_public = !$this->request->getParam(WishListInterface::IS_PUBLIC, false);
            $is_default = (bool) $this->request->getParam(WishListInterface::IS_DEFAULT, false);
            // Validate required fields
            if (empty($name)) {
                return $result_json->setData([
                    'success' => false,
                    'message' => __('WishList name is required.')
                ]);
            }

            $customer_id = (int)$this->customerSession->getCustomerId();
            try {
                $wish_list = $this->wishListFactory->create();
            } catch (\Throwable $e) {
                echo 'Exception during wishlist creation: ' . $e->getMessage();
                exit;
            }
            // Create new wishlist
            $wish_list->setCustomerId($customer_id)
                      ->setName($name)
                      ->setDescription($description)
                      ->setIsPublic($is_public)
                      ->setIsDefault($is_default);

            $wish_list->beforeSave();
            $this->wishListRepository->save($wish_list);

            if ($is_default) {
                $this->wishListRepository->setDefaultWishList($customer_id, (int)$wish_list->getId());
            }

            $this->messageManager->addSuccess(__('WishList "%1" has been created successfully.', $name));

            return $result_redirect->setPath('advancedwishlist');
        } catch (\Exception $e) {
            $this->messageManager->addError(__('An error occurred while creating the wishlist. ' . $e->getMessage()));

            return $result_json->setData([
                'success' => false,
                'message' => __(
                    'An error occurred while creating the wishlist. ' . $e->getMessage()
                )
            ]);
        }
    }

    /**
     * Create csrf validation exception
     *
     * @param RequestInterface $request
     *
     * @return InvalidRequestException|null
     */
    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    /**
     * Perform custom request validation
     *
     * @param RequestInterface $request
     *
     * @return bool|null
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }

    /**
     * Generate a unique share code for the wishlist
     *
     * @return string
     * @throws RandomException
     */
    private function generateShareCode(): string
    {
        return bin2hex(random_bytes(16)); // 32 caractères hex
    }
}
