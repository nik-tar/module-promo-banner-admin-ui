<?php

namespace Niktar\PromoBannerAdminUi\Ui\Component\Banner\Listing\Column;

use InvalidArgumentException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\Component\Listing\Columns\Column;

class Image extends Column
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var string
     */
    private $mediaUrl;
    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * Image constructor.
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param StoreManagerInterface $storeManager
     * @param SerializerInterface $serializer
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        StoreManagerInterface $storeManager,
        SerializerInterface $serializer,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->storeManager = $storeManager;
        $this->serializer = $serializer;
    }

    /**
     * @inheritdoc
     */
    public function prepareDataSource(array $dataSource): array
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $colName = $this->getData('name');

                if (!isset($item[$colName])) {
                    continue;
                }

                try {
                    $data = is_array($item[$colName])
                        ? $item[$colName]
                        : $this->serializer->unserialize($item[$colName]);
                } catch (InvalidArgumentException $exception) {
                    $item[$colName] = [];
                    continue;
                }

                foreach ($data as &$record) {
                    if (!isset($record['relative_path'])) {
                        continue;
                    }
                    $record['direct_url'] = $this->getMediaUrl() . $record['relative_path'];
                }

                $item[$colName] = $data;
            }
        }

        return $dataSource;
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    private function getMediaUrl(): string
    {
        if ($this->mediaUrl !== null) {
            return $this->mediaUrl;
        }

        $this->mediaUrl = rtrim($this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA), '/') . '/';

        return $this->mediaUrl;
    }
}
