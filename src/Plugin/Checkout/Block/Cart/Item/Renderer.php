<?php

declare(strict_types=1);

namespace Infrangible\CatalogProductOptionPromote\Plugin\Checkout\Block\Cart\Item;

use Infrangible\CatalogProductOptionPromote\Block\Cart\Item\Options;
use Infrangible\Core\Helper\Block;
use Magento\Quote\Model\Quote\Item\AbstractItem;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2025 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Renderer
{
    /** @var Block */
    protected $blockHelper;

    public function __construct(Block $blockHelper)
    {
        $this->blockHelper = $blockHelper;
    }

    public function afterGetActions(
        \Magento\Checkout\Block\Cart\Item\Renderer $subject,
        string $result,
        AbstractItem $item
    ): string {
        return $this->blockHelper->renderChildBlock(
            $subject,
            Options::class,
            ['item_renderer' => $subject, 'item' => $item, 'action_html' => $result]
        );
    }
}
