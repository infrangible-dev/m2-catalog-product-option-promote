<?php /** @noinspection PhpDeprecationInspection */

declare(strict_types=1);

namespace Infrangible\CatalogProductOptionPromote\Plugin\Checkout\Model;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2025 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Cart
{
    /** @var \Infrangible\Core\Helper\Cart */
    protected $cartHelper;

    public function __construct(\Infrangible\Core\Helper\Cart $cartHelper)
    {
        $this->cartHelper = $cartHelper;
    }

    /**
     * @throws \Exception
     * @noinspection PhpUnusedParameterInspection
     */
    public function afterUpdateItems(
        \Magento\Checkout\Model\Cart $subject,
        \Magento\Checkout\Model\Cart $result,
        array $data
    ): \Magento\Checkout\Model\Cart {
        return $this->cartHelper->addItemCustomOptions(
            $result,
            $data
        );
    }
}
