<?php

declare(strict_types=1);

namespace Infrangible\CatalogProductOptionPromote\Block\Product\View\Options\Type;

use Infrangible\CatalogProductOptionPromote\Block\Product\View\Options\ItemOptionInterface;
use Infrangible\CatalogProductOptionPromote\Block\Product\View\Options\Type\Select\CheckableFactory;
use Infrangible\CatalogProductOptionPromote\Block\Product\View\Options\Type\Select\MultipleFactory;
use Infrangible\CatalogProductOptionPromote\Traits\ItemOption;
use Magento\Catalog\Api\Data\ProductCustomOptionInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Pricing\Helper\Data;
use Magento\Framework\View\Element\Template\Context;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2025 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Select extends \Magento\Catalog\Block\Product\View\Options\Type\Select implements ItemOptionInterface
{
    use ItemOption;

    /** @var CheckableFactory */
    protected $checkableFactory;

    /** @var MultipleFactory */
    protected $multipleFactory;

    public function __construct(
        Context $context,
        Data $pricingHelper,
        \Magento\Catalog\Helper\Data $catalogData,
        CheckableFactory $checkableFactory,
        MultipleFactory $multipleFactory,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $pricingHelper,
            $catalogData,
            $data
        );

        $this->checkableFactory = $checkableFactory;
        $this->multipleFactory = $multipleFactory;
    }

    public function getValuesHtml(): string
    {
        $option = $this->getOption();
        $optionType = $option->getType();

        if ($optionType === ProductCustomOptionInterface::OPTION_TYPE_DROP_DOWN ||
            $optionType === ProductCustomOptionInterface::OPTION_TYPE_MULTIPLE) {

            $optionBlock = $this->multipleFactory->create();
        } elseif ($optionType === ProductCustomOptionInterface::OPTION_TYPE_RADIO ||
            $optionType === ProductCustomOptionInterface::OPTION_TYPE_CHECKBOX) {

            $optionBlock = $this->checkableFactory->create();
        } else {
            $optionBlock = null;
        }

        if ($optionBlock) {
            $optionBlock->setItem($this->getItem());
            $optionBlock->setOption($option);
            $optionBlock->setProduct($this->getProduct());
            $optionBlock->setDataUsingMethod(
                'skip_js_reload_price',
                1
            );

            try {
                return $optionBlock->_toHtml();
            } catch (LocalizedException $exception) {
            }
        }

        return '';
    }
}
