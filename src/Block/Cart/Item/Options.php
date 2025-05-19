<?php

declare(strict_types=1);

namespace Infrangible\CatalogProductOptionPromote\Block\Cart\Item;

use Infrangible\CatalogProductOptionPromote\Block\Product\View\Options\ItemOptionInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Option;
use Magento\Checkout\Block\Cart\Item\Renderer;
use Magento\Framework\View\Element\Template;
use Magento\Quote\Model\Quote\Item\AbstractItem;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2025 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Options extends Template
{
    protected function _construct()
    {
        $this->setData(
            'template',
            $this->getTemplateName()
        );

        parent::_construct();
    }

    public function getTemplateName(): string
    {
        return 'Infrangible_CatalogProductOptionPromote::cart/item/options.phtml';
    }

    public function getItemRenderer(): Renderer
    {
        return $this->getData('item_renderer');
    }

    public function getItem(): AbstractItem
    {
        return $this->getData('item');
    }

    public function getProduct(): Product
    {
        return $this->getItem()->getProduct();
    }

    public function getActionHtml(): string
    {
        return $this->getData('action_html');
    }

    /**
     * @return Option[]
     */
    public function getOptionList(): array
    {
        $item = $this->getItem();

        $itemOptionIds = $item->getOptionByCode('option_ids');

        $itemOptionIds = $itemOptionIds && $itemOptionIds->getValue() ? explode(
            ',',
            $itemOptionIds->getValue()
        ) : [];

        $product = $this->getProduct();

        $productOptions = $product->getOptions();

        $options = [];

        foreach ($productOptions as $productOption) {
            if (in_array(
                $productOption->getId(),
                $itemOptionIds
            )) {
                continue;
            }

            if (! $productOption->getData('promote')) {
                continue;
            }

            $options[] = $productOption;
        }

        return $options;
    }

    public function getOptionHtml(Option $option): string
    {
        $itemRenderer = $this->getItemRenderer();

        $type = $option->getType();

        $group = $option->getGroupByType($type);

        $group = $group == '' ? 'default' : $group;

        $rendererBlockId = sprintf(
            '%s.option.%s',
            $itemRenderer->getNameInLayout(),
            $group
        );

        /** @var ItemOptionInterface $renderer */
        $renderer = $itemRenderer->getChildBlock($rendererBlockId);

        if ($renderer) {
            $renderer->setProduct($this->getProduct());
            $renderer->setOption($option);
            $renderer->setItem($this->getItem());

            return $itemRenderer->getChildHtml(
                $rendererBlockId,
                false
            );
        }

        return '';
    }
}
