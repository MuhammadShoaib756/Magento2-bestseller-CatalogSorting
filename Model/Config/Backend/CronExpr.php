<?php

declare(strict_types=1);

namespace Shoaib\CatalogSorting\Model\Config\Backend;

use Magento\Cron\Model\Config\Source\Frequency;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\App\Config\ValueFactory;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Psr\Log\LoggerInterface;

class CronExpr extends Value
{
    private const CRON_STRING_PATH = 'crontab/index/jobs/catalogsorting/schedule/cron_expr';
    private const CRON_MODEL_PATH = 'crontab/index/jobs/catalogsorting/run/model';

    /**
     * @param Context $context
     * @param Registry $registry
     * @param ScopeConfigInterface $config
     * @param TypeListInterface $cacheTypeList
     * @param ValueFactory $configValueFactory
     * @param LoggerInterface $logger
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param string $runModelPath
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        private readonly ValueFactory    $configValueFactory,
        private readonly LoggerInterface $logger,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        private string $runModelPath = '',
        array $data = []
    ) {
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * Save Cron Values
     *
     * @return CronExpr
     */
    public function afterSave()
    {
        try {
            $this->configValueFactory->create()->load(
                self::CRON_STRING_PATH,
                'path'
            )->setValue(
                $this->getCronExprString()
            )->setPath(
                self::CRON_STRING_PATH
            )->save();
            $this->configValueFactory->create()->load(
                self::CRON_MODEL_PATH,
                'path'
            )->setValue(
                $this->runModelPath
            )->setPath(
                self::CRON_MODEL_PATH
            )->save();
        } catch (\Exception $e) {
            $this->logger->error(__('We can\'t save the cron expression.'));
            $this->logger->error($e->getMessage());
        }

        return parent::afterSave();
    }

    /**
     * Get Cron Expression String
     *
     * @return string
     */
    private function getCronExprString(): string
    {
        $time = $this->getData('groups/cron/fields/time/value');
        $frequency = $this->getData('groups/cron/fields/frequency/value');

        $cronExprArray = [
            (int) $time[1],
            (int) $time[0],
            $frequency == Frequency::CRON_MONTHLY ? '1' : '*',
            '*',
            $frequency == Frequency::CRON_WEEKLY ? '1' : '*',
        ];

        return join(' ', $cronExprArray);
    }
}
