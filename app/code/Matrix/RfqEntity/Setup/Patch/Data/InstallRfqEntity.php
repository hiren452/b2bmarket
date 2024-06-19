<?php
namespace Matrix\RfqEntity\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Matrix\RfqEntity\Setup\RfqEntitySetupFactory;

class InstallRfqEntity implements DataPatchInterface, PatchVersionInterface
{
    /**
     * RfqEntity setup factory
     *
     * @var RfqEntitySetupFactory
     */
    protected $rfqentitySetupFactory;

    /**
     * Module data setup instance
     *
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * Init
     *
     * @param RfqEntitySetupFactory $rfqentitySetupFactory
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        RfqEntitySetupFactory $rfqentitySetupFactory,
        ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->rfqentitySetupFactory = $rfqentitySetupFactory;
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        /** @var RfqEntitySetup $rfqentitysetup */
        $rfqentitySetup = $this->rfqentitySetupFactory->create(['setup' => $this->moduleDataSetup]);

        $this->moduleDataSetup->getConnection()->startSetup();

        $rfqentitySetup->installEntities();
        $entities = $rfqentitySetup->getDefaultEntities();
        foreach ($entities as $entityName => $entity) {
            $rfqentitySetup->addEntityType($entityName, $entity);
        }

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getVersion()
    {
        return '1.0.0';
    }
}
