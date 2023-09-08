<?php

namespace Niktar\PromoBannerAdminUi\Ui\Component\Banner\Listing\Column;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Niktar\PromoBanner\Api\Data\BannerInterface;

class Actions extends Column
{
    private const URL_PATH_EDIT = 'niktar_promo_banner/banner/edit';
    private const URL_PATH_DELETE = 'niktar_promo_banner/banner/delete';

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array|null
     */
    public function prepareDataSource(array $dataSource): ?array
    {
        if (!isset($dataSource['data']['items'])) {
            return $dataSource;
        }

        foreach ($dataSource['data']['items'] as & $item) {
            if (isset($item[BannerInterface::BANNER_ID])) {
                $item[$this->getData('name')] = $this->createMenuForListItem($item);
            }
        }

        return $dataSource;
    }

    /**
     * Create menu for list item
     *
     * @param array $item
     * @return array|array[]
     */
    private function createMenuForListItem(array $item): array
    {
        return [
            'edit' => [
                'href' => $this->crateActionLink(static::URL_PATH_EDIT, $item),
                'label' => __('Edit'),
            ],
            'delete' => [
                'href' => $this->crateActionLink(static::URL_PATH_DELETE, $item),
                'label' => __('Delete'),
                'confirm' => [
                    'title' => __('Delete "${ $.$data.code }"'),
                    'message' => __('Are you sure you wan\'t to delete a "${ $.$data.code }" record?'),
                ],
                'post' => true,
            ],
        ];
    }

    /**
     * Create link for action.
     *
     * @param string $routePath
     * @param array $item
     * @return string
     */
    private function crateActionLink(string $routePath, array $item): string
    {
        return $this->urlBuilder->getUrl(
            $routePath,
            [
                'id' => $item[BannerInterface::BANNER_ID],
            ]
        );
    }
}
