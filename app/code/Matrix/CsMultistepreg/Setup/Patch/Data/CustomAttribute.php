<?php
declare(strict_types=1);

namespace Matrix\CsMultistepreg\Setup\Patch\Data;

use B2bmarkets\Custom\Helper\Data;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Class AddressAttribute
 */
class CustomAttribute implements DataPatchInterface
{
    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;
    /**
     * @var Data
     */
    private $helperData;

    /**
     * AddressAttribute constructor.
     * * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        EavSetupFactory $eavSetupFactory
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {

        $eavSetup = $this->eavSetupFactory->create();
        $eavSetup->updateAttribute(
            'csmarketplace_vendor',
            'industry',
            [
                'source_model' => 'Matrix\CsMultistepreg\Model\Config\Source\Options'
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases(): array
    {
        return [];
    }
}
