<?php

namespace Niktar\PromoBannerAdminUi\Controller\Adminhtml\Banner;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Niktar\PromoBanner\Api\BannerRepositoryInterface;
use Niktar\PromoBanner\Api\Data\BannerInterface;
use Niktar\PromoBanner\Model\Banner;
use Niktar\PromoBanner\Model\BannerFactory;

/**
 * Save CMS banner action.
 */
class Save extends Action implements HttpPostActionInterface
{
    /**
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Niktar_PromoBannerAdminUi::promo_banner_save';

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var BannerFactory
     */
    private $bannerFactory;

    /**
     * @var BannerRepositoryInterface
     */
    private $bannerRepository;
    /**
     * @var DataObjectProcessor
     */
    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @param Context $context
     * @param DataPersistorInterface $dataPersistor
     * @param BannerFactory $bannerFactory
     * @param BannerRepositoryInterface $bannerRepository
     * @param DataObjectHelper $dataObjectHelper
     */
    public function __construct(
        Context $context,
        DataPersistorInterface $dataPersistor,
        BannerFactory $bannerFactory,
        BannerRepositoryInterface $bannerRepository,
        DataObjectHelper $dataObjectHelper
    ) {
        parent::__construct($context);
        $this->dataPersistor = $dataPersistor;
        $this->bannerFactory = $bannerFactory;
        $this->bannerRepository = $bannerRepository;
        $this->dataObjectHelper = $dataObjectHelper;
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @return ResultInterface
     */
    public function execute()
    {
        /** @var Http $request */
        $request = $this->getRequest();
        $data = $request->getPostValue();
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            foreach (BannerInterface::BOOLEAN_FIELDS as $field) {
                if (isset($data[$field]) && $data[$field] === 'true') {
                    $data[$field] = true;
                } else {
                    $data[$field] = false;
                }
            }

            if (empty($data[BannerInterface::BANNER_ID])) {
                $data[BannerInterface::BANNER_ID] = null;
            }

            /** @var Banner $model */
            $model = $this->bannerFactory->create();

            $bannerId = $this->getRequest()->getParam('id');
            if ($bannerId) {
                try {
                    $model = $this->bannerRepository->get($bannerId);
                } catch (LocalizedException $e) {
                    $this->messageManager->addErrorMessage(__('This banner no longer exists.'));
                    return $resultRedirect->setPath('*/*/');
                }
            }
            $data = array_map(function ($value) {
                if (
                    is_array($value)
                    && isset($value[0])
                    && is_array($value[0])
                    && isset($value[0]['previewType'])
                ) {
                    $value[0]['preview_type'] = $value[0]['previewType'];
                    unset($value[0]['previewType']);
                }
                return $value;
            }, $data);

            $this->dataObjectHelper->populateWithArray(
                $model,
                $data,
                BannerInterface::class
            );

            try {
                $this->bannerRepository->save($model);
                $this->messageManager->addSuccessMessage(__('You saved the banner.'));
                return $this->processResultRedirect($model, $resultRedirect);
            } catch (LocalizedException $e) {
                $this->messageManager->addExceptionMessage($e->getPrevious() ?: $e);
            } catch (Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the banner.'));
            }

            $this->dataPersistor->set('niktar_banner_form', $data);
            return $resultRedirect->setRefererOrBaseUrl();
        }
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * @param BannerInterface $model
     * @param Redirect $resultRedirect
     * @return Redirect
     */
    private function processResultRedirect(BannerInterface $model, Redirect $resultRedirect): Redirect
    {
        $this->dataPersistor->clear('niktar_banner_form');
        if ($this->getRequest()->getParam('back')) {
            return $resultRedirect->setPath('*/*/edit', ['banner_id' => $model->getBannerId(), '_current' => true]);
        }
        return $resultRedirect->setPath('*/*/');
    }
}
