<?php

namespace Niktar\PromoBannerAdminUi\Ui\DataProvider\Banner\Form;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Ui\DataProvider\Modifier\PoolInterface;
use Niktar\PromoBanner\Api\Data\BannerInterface;
use Niktar\PromoBanner\Model\Banner;
use Niktar\PromoBanner\Model\ResourceModel\Banner\Collection;
use Niktar\PromoBanner\Model\ResourceModel\Banner\CollectionFactory;

/**
 * Class DataProvider
 */
class DataProvider extends \Magento\Ui\DataProvider\ModifierPoolDataProvider
{
    /**
     * @var Collection
     */
    protected $collection;

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var array
     */
    protected $loadedData;

    /**
     * @var AuthorizationInterface
     */
    private $auth;
    /**
     * @var DataObjectProcessor
     */
    private $dataObjectProcessor;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param DataPersistorInterface $dataPersistor
     * @param DataObjectProcessor $dataObjectProcessor
     * @param array $meta
     * @param array $data
     * @param PoolInterface|null $pool
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        DataPersistorInterface $dataPersistor,
        DataObjectProcessor $dataObjectProcessor,
        array $meta = [],
        array $data = [],
        PoolInterface $pool = null
    ) {
        $this->collection = $collectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data, $pool);
        $this->dataObjectProcessor = $dataObjectProcessor;
    }

    /**
     * Get data
     *
     * @return array|null
     */
    public function getData(): ?array
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }

        $items = $this->collection->getItems();
        /** @var Banner $banner */
        foreach ($items as $banner) {
            $this->modifyTargetUrl($banner);
            $this->loadedData[$banner->getId()] = $this->dataObjectProcessor->buildOutputDataArray(
                $banner,
                BannerInterface::class
            );
        }

        $data = $this->dataPersistor->get('niktar_banner_form');
        if (!empty($data)) {
            $banner = $this->collection->getNewEmptyItem();
            $banner->setData($data);
            $this->loadedData[$banner->getId()] = $banner->getData();
            $this->dataPersistor->clear('niktar_banner_form');
        }

        return $this->loadedData;
    }

    /**
     * @param BannerInterface $banner
     *
     * @return void
     */
    private function modifyTargetUrl(BannerInterface $banner): void
    {
        $targetUrl = $banner->getTargetUrl();

        if (!$targetUrl->getSetting()
            || !is_string($targetUrl->getSetting())
        ) {
            return;
        }

        $targetUrl->setSetting(
            $targetUrl->getSetting() === 'true'
        );
    }
}
