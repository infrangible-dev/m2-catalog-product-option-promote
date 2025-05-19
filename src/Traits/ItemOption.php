<?php

declare(strict_types=1);

namespace Infrangible\CatalogProductOptionPromote\Traits;

use Magento\Quote\Model\Quote\Item\AbstractItem;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2025 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
trait ItemOption
{
    /** @var AbstractItem */
    private $item;

    public function getItem(): AbstractItem
    {
        return $this->item;
    }

    public function setItem(AbstractItem $item): void
    {
        $this->item = $item;
    }
}
