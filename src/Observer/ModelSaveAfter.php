<?php /** @noinspection PhpDeprecationInspection */

declare(strict_types=1);

namespace Infrangible\CatalogProductOptionPromote\Observer;

use FeWeDev\Base\Arrays;
use FeWeDev\Base\Variables;
use Infrangible\Core\Helper\Database;
use Magento\Catalog\Model\Product\Option;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\Store;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2025 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class ModelSaveAfter implements ObserverInterface
{
    /** @var Database */
    protected $databaseHelper;

    /** @var Arrays */
    protected $arrays;

    /** @var Variables */
    protected $variables;

    public function __construct(Database $databaseHelper, Arrays $arrays, Variables $variables)
    {
        $this->databaseHelper = $databaseHelper;
        $this->arrays = $arrays;
        $this->variables = $variables;
    }

    /**
     * @throws \Exception
     */
    public function execute(Observer $observer): void
    {
        $object = $observer->getData('object');

        if ($object instanceof Option) {
            $optionId = $this->variables->intValue($object->getData('option_id'));
            $storeId = $this->variables->intValue($object->getData('store_id'));
            $promote = $this->variables->intValue($object->getData('promote'));

            $dbAdapter = $object->getResource()->getConnection();

            $tableName = $dbAdapter->getTableName('catalog_product_option_promote');

            $storeQuery = $this->databaseHelper->select(
                $tableName,
                ['id', 'promote']
            );

            $storeQuery->where(
                'option_id = ?',
                $optionId
            );

            $storeQuery->where(
                'store_id  = ?',
                $storeId
            );

            $storeQueryResult = $this->databaseHelper->fetchRow(
                $storeQuery,
                $dbAdapter
            );

            if ($storeId == Store::DEFAULT_STORE_ID) {
                $this->updateStorePromote(
                    $dbAdapter,
                    $tableName,
                    $optionId,
                    $storeId,
                    $promote,
                    $storeQueryResult
                );
            } else {
                $defaultQuery = $this->databaseHelper->select(
                    $tableName,
                    ['id', 'promote']
                );

                $defaultQuery->where(
                    'option_id = ?',
                    $optionId
                );

                $defaultQuery->where(
                    'store_id  = ?',
                    0
                );

                $defaultQueryResult = $this->databaseHelper->fetchRow(
                    $defaultQuery,
                    $dbAdapter
                );

                if ($defaultQueryResult === null) {
                    $this->updateStorePromote(
                        $dbAdapter,
                        $tableName,
                        $optionId,
                        $storeId,
                        $promote,
                        $storeQueryResult
                    );
                } else {
                    $currentDefaultPromote = $this->arrays->getValue(
                        $defaultQueryResult,
                        'promote'
                    );

                    if ($currentDefaultPromote == $promote) {
                        if ($storeQueryResult !== null) {
                            $this->databaseHelper->deleteTableData(
                                $dbAdapter,
                                $tableName,
                                sprintf(
                                    'option_id = %d AND store_id = %d',
                                    $optionId,
                                    $storeId
                                )
                            );
                        }
                    } else {
                        $this->updateStorePromote(
                            $dbAdapter,
                            $tableName,
                            $optionId,
                            $storeId,
                            $promote,
                            $storeQueryResult
                        );
                    }
                }
            }
        }
    }

    /**
     * @throws \Exception
     */
    private function updateStorePromote(
        AdapterInterface $dbAdapter,
        string $tableName,
        int $optionId,
        int $storeId,
        int $promote,
        ?array $storeQueryResult
    ) {
        if ($storeQueryResult === null) {
            $this->databaseHelper->createTableData(
                $dbAdapter,
                $tableName,
                ['option_id' => $optionId, 'store_id' => $storeId, 'promote' => $promote]
            );
        } else {
            $currentStorePromote = $this->arrays->getValue(
                $storeQueryResult,
                'promote'
            );

            if ($currentStorePromote != $promote) {
                $id = $this->arrays->getValue(
                    $storeQueryResult,
                    'id'
                );

                $this->databaseHelper->updateTableData(
                    $dbAdapter,
                    $tableName,
                    ['promote' => $promote],
                    sprintf(
                        'id = %d',
                        $id
                    )
                );
            }
        }
    }
}
