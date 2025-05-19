<?php

declare(strict_types=1);

namespace Infrangible\CatalogProductOptionPromote\Helper;

use Magento\Catalog\Model\ResourceModel\Product\Option\Collection;
use Magento\Store\Model\Store;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2025 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Data
{
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
}
