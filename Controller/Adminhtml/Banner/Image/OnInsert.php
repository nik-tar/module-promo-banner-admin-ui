<?php

namespace Niktar\PromoBannerAdminUi\Controller\Adminhtml\Banner\Image;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Catalog\Helper\Data;
use Magento\Cms\Helper\Wysiwyg\Images as ImagesHelper;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * We need this controller because we also want save relative path to the database
 * and not only absolute one
 */
class OnInsert extends Action implements HttpPostActionInterface
{
    const ADMIN_RESOURCE = 'Niktar_PromoBannerAdminUi::promo_banner_save';

    /**
     * @var Filesystem\Directory\WriteInterface
     */
    private $directory;
    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @param Context $context
     * @param Filesystem $filesystem
     * @param SerializerInterface $serializer
     * @throws FileSystemException
     */
    public function __construct(
        Context $context,
        Filesystem $filesystem,
        SerializerInterface $serializer
    ) {
        parent::__construct($context);
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->serializer = $serializer;
    }

    /**
     * Fire when select image
     *
     * @return ResultInterface
     * @throws LocalizedException
     */
    public function execute()
    {
        /** @var ImagesHelper $imagesHelper */
        $imagesHelper = $this->_objectManager->get(ImagesHelper::class);
        $request = $this->getRequest();

        $storeId = $request->getParam('store');

        $filename = $request->getParam('filename');
        $filename = $imagesHelper->idDecode($filename);

        $asIs = $request->getParam('as_is');

        $forceStaticPath = $request->getParam('force_static_path');

        $this->_objectManager->get(Data::class)->setStoreId($storeId);
        $imagesHelper->setStoreId($storeId);

        if ($forceStaticPath) {
            $url = $imagesHelper->getCurrentUrl() . $filename;
        } else {
            $url = $imagesHelper->getImageHtmlDeclaration($filename, $asIs);
        }

        $relativePath = $imagesHelper->getCurrentPath();
        $relativePath = trim($this->directory->getRelativePath($relativePath), '/') . '/' . $filename;

        $result = [
            'relative_path' => $relativePath,
            'url' => $url
        ];

        /** @var Raw $resultRaw */
        $resultRaw = $this->resultFactory->create(ResultFactory::TYPE_RAW);
        return $resultRaw->setContents(
            'base64,' . base64_encode($this->serializer->serialize($result))
        );
    }
}
