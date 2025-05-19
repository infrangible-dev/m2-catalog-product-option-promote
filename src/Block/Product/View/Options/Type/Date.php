<?php

declare(strict_types=1);

namespace Infrangible\CatalogProductOptionPromote\Block\Product\View\Options\Type;

use Infrangible\CatalogProductOptionPromote\Block\Product\View\Options\ItemOptionInterface;
use Infrangible\CatalogProductOptionPromote\Traits\ItemOption;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Form\FilterFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Pricing\Helper\Data;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\View\Element\Template\Context;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2025 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Date extends \Magento\Catalog\Block\Product\View\Options\Type\Date implements ItemOptionInterface
{
    use ItemOption;

    /** @var FilterFactory */
    private $filterFactory;

    public function __construct(
        Context $context,
        Data $pricingHelper,
        \Magento\Catalog\Helper\Data $catalogData,
        \Magento\Catalog\Model\Product\Option\Type\Date $catalogProductOptionTypeDate,
        array $data = [],
        ?FilterFactory $filterFactory = null
    ) {
        parent::__construct(
            $context,
            $pricingHelper,
            $catalogData,
            $catalogProductOptionTypeDate,
            $data,
            $filterFactory
        );

        $this->filterFactory = $filterFactory ?? ObjectManager::getInstance()->get(FilterFactory::class);
    }

    /**
     * @throws LocalizedException
     * @throws \DateInvalidTimeZoneException
     */
    public function getCalendarDateHtml(): string
    {
        $option = $this->getOption();
        $values = $this->getProduct()->getPreconfiguredValues()->getData('options/' . $option->getId());

        $yearStart = $this->_catalogProductOptionTypeDate->getYearStart();
        $yearEnd = $this->_catalogProductOptionTypeDate->getYearEnd();

        $dateFormat = $this->_localeDate->getDateFormatWithLongYear();
        /** Escape RTL characters which are present in some locales and corrupt formatting */
        $escapedDateFormat = preg_replace(
            '/[^MmDdYy\/\.\-]/',
            '',
            $dateFormat
        );

        $value = null;

        if (is_array($values)) {
            $date = $this->getInternalDateString($values);

            if ($date !== null) {
                $dateFilter = $this->filterFactory->create(
                    'date',
                    ['format' => $escapedDateFormat]
                );
                $value = $dateFilter->outputFilter($date);
            } elseif (isset($values[ 'date' ])) {
                $value = $values[ 'date' ];
            }
        }

        $item = $this->getItem();

        /** @var \Magento\Framework\View\Element\Html\Date $calendar */
        $calendar = $this->getLayout()->createBlock(\Magento\Framework\View\Element\Html\Date::class);

        $calendar->setDataUsingMethod(
            'id',
            'cart_' . $item->getId() . '_options_' . $this->getOption()->getId() . '_date'
        );
        $calendar->setDataUsingMethod(
            'name',
            'cart[' . $item->getId() . '][options][' . $this->getOption()->getId() . '][date]'
        );
        $calendar->setDataUsingMethod(
            'class',
            'product-custom-option datetime-picker input-text'
        );
        $calendar->setDataUsingMethod(
            'image',
            $this->getViewFileUrl('Magento_Theme::calendar.png')
        );
        $calendar->setDataUsingMethod(
            'date_format',
            $escapedDateFormat
        );
        $calendar->setDataUsingMethod(
            'value',
            $value
        );
        $calendar->setDataUsingMethod(
            'years_range',
            $yearStart . ':' . $yearEnd
        );

        return $calendar->getHtml();
    }

    /**
     * @throws \DateInvalidTimeZoneException
     */
    private function getInternalDateString(array $value): ?string
    {
        $result = null;

        if (! empty($value[ 'date' ]) && ! empty($value[ 'date_internal' ])) {
            $dateTimeZone = new \DateTimeZone($this->_localeDate->getConfigTimezone());

            $dateTimeObject = date_create_from_format(
                DateTime::DATETIME_PHP_FORMAT,
                $value[ 'date_internal' ],
                $dateTimeZone
            );

            if ($dateTimeObject !== false) {
                $result = $dateTimeObject->format(DateTime::DATE_PHP_FORMAT);
            }
        } elseif (! empty($value[ 'day' ]) && ! empty($value[ 'month' ]) && ! empty($value[ 'year' ])) {
            $dateTimeObject = $this->_localeDate->date();

            $dateTimeObject->setDate(
                (int)$value[ 'year' ],
                (int)$value[ 'month' ],
                (int)$value[ 'day' ]
            );

            $result = $dateTimeObject->format(DateTime::DATE_PHP_FORMAT);
        }

        return $result;
    }

    /**
     * @param string   $name
     * @param int|null $value
     *
     * @throws LocalizedException
     */
    protected function _getHtmlSelect($name, $value = null): \Magento\Framework\View\Element\Html\Select
    {
        $option = $this->getOption();

        $this->setData(
            'skip_js_reload_price',
            1
        );

        $require = '';

        $item = $this->getItem();

        /** @var \Magento\Framework\View\Element\Html\Select $select */
        $select = $this->getLayout()->createBlock(\Magento\Framework\View\Element\Html\Select::class);

        $select->setId('cart_' . $item->getId() . '_options_' . $this->getOption()->getId() . '_' . $name);
        $select->setClass('product-custom-option admin__control-select datetime-picker' . $require);
        $select->setData('extra_params');
        $select->setData(
            'name',
            'cart[' . $item->getId() . '][options][' . $option->getId() . '][' . $name . ']'
        );

        $extraParams = 'style="width:auto"';
        if (! $this->getData('skip_js_reload_price')) {
            $extraParams .= ' onchange="opConfig.reloadPrice()"';
        }
        $extraParams .= ' data-role="calendar-dropdown" data-calendar-role="' . $name . '"';
        $extraParams .= ' data-selector="' . $select->getData('name') . '"';
        if ($this->getOption()->getIsRequire()) {
            $extraParams .= ' data-validate=\'{"datetime-validation": true}\'';
        }
        $select->setData(
            'extra_params',
            $extraParams
        );

        if ($value === null) {
            $values = $this->getProduct()->getPreconfiguredValues()->getData('options/' . $option->getId());
            $value = is_array($values) ? $this->parseDate(
                $values,
                $name
            ) : null;
        }

        if ($value !== null) {
            $select->setData(
                'value',
                $value
            );
        }

        return $select;
    }

    private function parseDate(array $value, string $part): ?string
    {
        $result = null;

        if (! empty($value[ 'date' ]) && ! empty($value[ 'date_internal' ])) {
            $formatDate = explode(
                ' ',
                $value[ 'date_internal' ]
            );

            $date = explode(
                '-',
                $formatDate[ 0 ]
            );

            $value[ 'year' ] = $date[ 0 ];
            $value[ 'month' ] = $date[ 1 ];
            $value[ 'day' ] = $date[ 2 ];
        }

        if (isset($value[ $part ])) {
            $result = (string)$value[ $part ];
        }

        return $result;
    }
}
