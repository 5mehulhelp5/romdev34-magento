<?php

declare(strict_types=1);
/**
 * Copyright © 2025 Romain ITOFO. Tous droits réservés.
 *
 * @author  Romain ITOFO
 * @license Propriétaire
 */

/**
 * "My Wish List" link
 */

namespace Romain\AdvancedWishList\Block\Menu;

use Magento\Customer\Block\Account\SortLinkInterface;
use Magento\Framework\Phrase;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class Link
 *
 * @api
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 * @since 100.0.2
 */
class Link extends \Magento\Framework\View\Element\Html\Link implements SortLinkInterface
{
    /**
     * Template name
     *
     * @var string
     */
    protected $_template = 'Romain_AdvancedWishList::menu/link.phtml';

    /**
     * @param Context $context
     * @param array   $data
     */
    public function __construct(
        Context $context,
        array   $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    public function getHref(): string
    {
        return $this->getUrl('advancedwishlist');
    }

    /**
     * @return Phrase
     */
    public function getLabel(): Phrase
    {
        return __('Démo wish list');
    }

    /**
     * {@inheritdoc}
     * @since 101.0.0
     */
    public function getSortOrder()
    {
        return $this->getData(self::SORT_ORDER);
    }
}
