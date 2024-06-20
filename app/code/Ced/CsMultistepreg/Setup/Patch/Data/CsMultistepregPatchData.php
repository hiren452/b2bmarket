<?php

declare(strict_types=1);

namespace Ced\CsMultistepreg\Setup\Patch\Data;

use Ced\CsMarketplace\Setup\CsMarketplaceSetupFactory;
use Ced\CsMultistepreg\Model\StepsFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class CsMultistepregPatchData implements DataPatchInterface
{
    /**
     * CsMultistepregPatchData constructor.
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param CsMarketplaceSetupFactory $csmarketplaceSetupFactory
     * @param StepsFactory $stepsFactory
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        CsMarketplaceSetupFactory $csmarketplaceSetupFactory,
        StepsFactory $stepsFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->csmarketplaceSetupFactory = $csmarketplaceSetupFactory;
        $this->stepsFactory = $stepsFactory;
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * Do Upgrade
     *
     * @return void
     */
    public function apply()
    {
        $setup = $this->moduleDataSetup;
        $csmarketplaceSetup = $this->csmarketplaceSetupFactory->create(['setup' => $setup]);
        $setup->startSetup();
        $csmarketplaceSetup->installEntities();
        $csmarketplaceSetup->addAttribute('csmarketplace_vendor', 'multistep_done', [
            'group' => 'General Information',
            'visible' => false,
            'position' => 6,
            'type' => 'int',
            'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
            'label' => 'Multistep Done',
            'input' => 'text',
            'required' => false,
            'default' => 0,
            'user_defined' => false,
        ]);

        /* inserting steps info */
        $steps = ['Personal Info', 'Business Info'];
        $stepCount = 1;
        foreach ($steps as $key => $value) {
            $this->stepsFactory
                ->create()
                ->setStepNumber($stepCount++)
                ->setStepLabel($value)
                ->save();
        }
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }
}
