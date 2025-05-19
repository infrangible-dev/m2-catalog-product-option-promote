<?php

declare(strict_types=1);

namespace Infrangible\CatalogProductOptionPromote\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2025 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * @throws \Exception
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context): void
    {
        $setup->startSetup();

        $connection = $setup->getConnection();

        $promoteTableName = $setup->getTable('catalog_product_option_promote');

        if (! $setup->tableExists($promoteTableName)) {
            $optionTableName = $setup->getTable('catalog_product_option');
            $storeTableName = $setup->getTable('store');

            $promoteTable = $connection->newTable($promoteTableName);

            $promoteTable->addColumn(
                'id',
                Table::TYPE_INTEGER,
                10,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true]
            );
            $promoteTable->addColumn(
                'option_id',
                Table::TYPE_INTEGER,
                10,
                ['unsigned' => true, 'nullable' => false]
            );
            $promoteTable->addColumn(
                'store_id',
                Table::TYPE_SMALLINT,
                5,
                ['unsigned' => true, 'nullable' => false]
            );
            $promoteTable->addColumn(
                'promote',
                Table::TYPE_SMALLINT,
                5,
                ['unsigned' => true, 'nullable' => false]
            );

            $promoteTable->addForeignKey(
                $setup->getFkName(
                    $promoteTableName,
                    'option_id',
                    $optionTableName,
                    'option_id'
                ),
                'option_id',
                $optionTableName,
                'option_id',
                Table::ACTION_CASCADE
            );

            $promoteTable->addForeignKey(
                $setup->getFkName(
                    $promoteTableName,
                    'store_id',
                    $storeTableName,
                    'store_id'
                ),
                'store_id',
                $storeTableName,
                'store_id',
                Table::ACTION_CASCADE
            );

            $connection->createTable($promoteTable);
        }

        $setup->endSetup();
    }
}
