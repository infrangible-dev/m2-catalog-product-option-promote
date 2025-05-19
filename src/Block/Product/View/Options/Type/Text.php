<?php

declare(strict_types=1);

namespace Infrangible\CatalogProductOptionPromote\Block\Product\View\Options\Type;

use Infrangible\CatalogProductOptionPromote\Block\Product\View\Options\ItemOptionInterface;
use Infrangible\CatalogProductOptionPromote\Traits\ItemOption;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2025 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Text extends \Magento\Catalog\Block\Product\View\Options\Type\Text implements ItemOptionInterface
{
    use ItemOption;
}
