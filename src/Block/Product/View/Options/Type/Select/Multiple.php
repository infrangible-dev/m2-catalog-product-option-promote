<?php

declare(strict_types=1);

namespace Infrangible\CatalogProductOptionPromote\Block\Product\View\Options\Type\Select;

use Infrangible\CatalogProductOptionPromote\Block\Product\View\Options\ItemOptionInterface;
use Infrangible\CatalogProductOptionPromote\Traits\ItemOption;
use Magento\Catalog\Api\Data\ProductCustomOptionInterface;
use Magento\Catalog\Model\Product\Option;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Html\Select;
use Magento\Quote\Model\Quote\Item\AbstractItem;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2025 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Multiple extends \Magento\Catalog\Block\Product\View\Options\Type\Select\Multiple implements ItemOptionInterface
{
    use ItemOption;

    /**
     * @throws LocalizedException
     */
    protected function _toHtml(): string
    {
        $item = $this->getItem();
        $option = $this->getOption();
        $optionType = $option->getType();
        $configValue = $this->getProduct()->getPreconfiguredValues()->getData('options/' . $option->getId());
        $require = $option->getIsRequire() ? ' required' : '';
        $extraParams = '';

        /** @var Select $select */
        $select = $this->getLayout()->createBlock(Select::class);

        $select->setData(
            [
                'id'    => sprintf(
                    'cart_%s_select_%s',
                    $item->getId(),
                    $option->getId()
                ),
                'class' => $require . ' product-custom-option admin__control-select'
            ]
        );

        $select = $this->insertSelectOption(
            $select,
            $item,
            $option
        );

        $select = $this->processSelectOption(
            $select,
            $option
        );

        if ($optionType === ProductCustomOptionInterface::OPTION_TYPE_MULTIPLE) {
            $extraParams = ' multiple="multiple"';
        }

        if (! $this->getDataUsingMethod('skip_js_reload_price')) {
            $extraParams .= ' onchange="opConfig.reloadPrice()"';
        }

        $extraParams .= sprintf(
            ' data-selector="%s"',
            $select->getDataUsingMethod('name')
        );

        $select->setDataUsingMethod(
            'extra_params',
            $extraParams
        );

        if ($configValue) {
            $select->setDataUsingMethod(
                'value',
                $configValue
            );
        }

        return $select->getHtml();
    }

    private function insertSelectOption(Select $select, AbstractItem $item, Option $option): Select
    {
        $require = $option->getIsRequire() ? ' required' : '';

        if ($option->getType() === ProductCustomOptionInterface::OPTION_TYPE_DROP_DOWN) {
            $select->setDataUsingMethod(
                'name',
                sprintf(
                    'cart[%s][options][%s]',
                    $item->getId(),
                    $option->getId()
                )
            );
            $select->addOption(
                '',
                __('-- Please Select --')
            );
        } else {
            $select->setDataUsingMethod(
                'name',
                sprintf(
                    'cart[%s][options][%s][]',
                    $item->getId(),
                    $option->getId()
                )
            );

            $select->setClass('multiselect admin__control-multiselect' . $require . ' product-custom-option');
        }

        return $select;
    }

    private function processSelectOption(Select $select, Option $option): Select
    {
        $store = $this->getProduct()->getStore();

        foreach ($option->getValues() as $_value) {
            $isPercentPriceType = $_value->getPriceType() === 'percent';

            $priceStr = $this->_formatPrice(
                [
                    'is_percent'    => $isPercentPriceType,
                    'pricing_value' => $_value->getPrice($isPercentPriceType)
                ],
                false
            );

            $select->addOption(
                $_value->getOptionTypeId(),
                $_value->getTitle() . ' ' . strip_tags($priceStr),
                [
                    'price' => $this->pricingHelper->currencyByStore(
                        $_value->getPrice(true),
                        $store,
                        false
                    )
                ]
            );
        }

        return $select;
    }
}
