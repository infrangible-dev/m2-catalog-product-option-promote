<?php

declare(strict_types=1);

namespace Infrangible\CatalogProductOptionPromote\Block\Product\View\Options;

use Magento\Catalog\Model\Product;
use Magento\Quote\Model\Quote\Item\AbstractItem;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2025 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
interface ItemOptionInterface
{
    public function getItem(): AbstractItem;

    public function setItem(AbstractItem $item): void;

    public function setProduct(Product $product = null);
}
