<?php

namespace Niktar\PromoBannerAdminUi\Controller\Adminhtml\Banner;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Niktar\PromoBanner\Api\BannerRepositoryInterface;

class Delete extends Action implements HttpPostActionInterface
{
    /**
     * @var BannerRepositoryInterface
     */
    private $bannerRepository;

    /**
     * Delete constructor.
     * @param Context $context
     * @param BannerRepositoryInterface $bannerRepository
     */
    public function __construct(
        Context $context,
        BannerRepositoryInterface $bannerRepository
    ) {
        parent::__construct($context);

        $this->bannerRepository = $bannerRepository;
    }

    /**
     * @return Redirect|ResultInterface
     */
    public function execute()
    {
        $bannerId = $this->getRequest()->getParam('id', false);
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($bannerId) {
            try {
                $this->bannerRepository->deleteById($bannerId);
                $this->messageManager->addSuccessMessage(__('You deleted the banner.'));
                return $resultRedirect->setPath('*/*/');
            } catch (Exception $exception) {
                $this->messageManager->addErrorMessage($exception->getMessage());
                return $resultRedirect->setPath(
                    '*/*/edit',
                    ['id' => $bannerId]
                );
            }
        }
        $this->messageManager->addErrorMessage(__('We can\'t find a banner to delete.'));
        return $resultRedirect->setPath('*/*/');
    }
}
