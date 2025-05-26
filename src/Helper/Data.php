<?php

declare(strict_types=1);

namespace Infrangible\CatalogProductOptionPromote\Helper;

use Magento\Catalog\Api\Data\ProductCustomOptionInterface;
use Magento\Catalog\Api\Data\ProductCustomOptionValuesInterface;
use Magento\Catalog\Model\ResourceModel\Product\Option\Collection;
use Magento\Framework\DataObject;
use Magento\Framework\EntityManager\EventManager;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\Store\Model\Store;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2025 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Data
{
    /** @var EventManager */
    protected $eventManager;

    public function __construct(EventManager $eventManager)
    {
        $this->eventManager = $eventManager;
    }

    public function addPromoteToResult(Collection $collection, $storeId): void
    {
        if ($collection->isLoaded() || $collection->hasFlag('promote')) {
            return;
        }

        $dbAdapter = $collection->getConnection();

        $tableName = $dbAdapter->getTableName('catalog_product_option_promote');

        $promoteExpr = $dbAdapter->getCheckSql(
            'store_option_promote.promote IS NULL',
            'default_option_promote.promote',
            'store_option_promote.promote'
        );

        $select = $collection->getSelect();

        $select->joinLeft(
            ['default_option_promote' => $tableName],
            sprintf(
                'default_option_promote.option_id = main_table.option_id AND %s',
                $dbAdapter->quoteInto(
                    'default_option_promote.store_id = ?',
                    Store::DEFAULT_STORE_ID
                )
            ),
            ['default_promote' => 'promote']
        );

        $select->joinLeft(
            ['store_option_promote' => $tableName],
            sprintf(
                'store_option_promote.option_id = main_table.option_id AND %s',
                $dbAdapter->quoteInto(
                    'store_option_promote.store_id = ?',
                    $storeId
                )
            ),
            ['store_promote' => 'promote', 'promote' => $promoteExpr]
        );

        $collection->setFlag(
            'promote',
            true
        );
    }

    public function isItemOptionAvailable(AbstractItem $item, ProductCustomOptionInterface $productOption): bool
    {
        $isAvailable = true;

        if (! $productOption->getData('promote')) {
            $isAvailable = false;
        }

        $checkResult = new DataObject();
        $checkResult->setData(
            'is_available',
            $isAvailable
        );

        $eventData = ['item' => $item, 'product_option' => $productOption, 'result' => $checkResult];

        $this->eventManager->dispatch(
            'catalog_product_option_promote_item_option_available',
            $eventData
        );

        return $checkResult->getData('is_available');
    }

    public function isItemOptionValueAvailable(
        AbstractItem $item,
        ProductCustomOptionValuesInterface $productOptionValue
    ): bool {
        $checkResult = new DataObject();
        $checkResult->setData(
            'is_available',
            true
        );

        $eventData = ['item' => $item, 'product_option_value' => $productOptionValue, 'result' => $checkResult];

        $this->eventManager->dispatch(
            'catalog_product_option_promote_item_option_value_available',
            $eventData
        );

        return $checkResult->getData('is_available');
    }
}
