<?php

declare(strict_types=1);

namespace Infrangible\CatalogProductOptionPromote\Block\Product\View\Options\Type\Select;

use Infrangible\CatalogProductOptionPromote\Block\Product\View\Options\ItemOptionInterface;
use Infrangible\CatalogProductOptionPromote\Traits\ItemOption;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2025 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Checkable extends \Magento\Catalog\Block\Product\View\Options\Type\Select\Checkable implements ItemOptionInterface
{
    use ItemOption;

    /** @var string */
    protected $_template = 'Infrangible_CatalogProductOptionPromote::product/view/options/type/select/checkable.phtml';

    public function getSkipJsReloadPrice(): bool
    {
        return $this->getData('skip_js_reload_price') == 1;
    }
}
