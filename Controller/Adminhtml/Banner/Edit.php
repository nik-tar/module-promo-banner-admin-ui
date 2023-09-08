<?php

namespace Niktar\PromoBannerAdminUi\Controller\Adminhtml\Banner;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Page;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Backend\App\Action;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Niktar\PromoBanner\Api\BannerRepositoryInterface;

/**
 * Edit CMS page action.
 */
class Edit extends Action implements HttpGetActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Niktar_PromoBannerAdminUi::promo_banner_save';

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var BannerRepositoryInterface
     */
    private $bannerRepository;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param BannerRepositoryInterface $bannerRepository
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        BannerRepositoryInterface $bannerRepository
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->bannerRepository = $bannerRepository;
    }

    /**
     * Init actions
     *
     * @return Page
     */
    protected function _initAction()
    {
        // load layout, set active menu and breadcrumbs
        /** @var Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Niktar_PromoBannerAdminUi::promo_banner');
        return $resultPage;
    }

    /**
     * Edit CMS page
     *
     * @return Page|Redirect
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        // 1. Get ID and create model
        $bannerId = (int)$this->getRequest()->getParam('id');
        // 2. Initial checking
        try {
            $model = $this->bannerRepository->get($bannerId);
        } catch (NoSuchEntityException $e) {
            $this->messageManager->addErrorMessage(__('This page no longer exists.'));
            /** @var Redirect $resultRedirect */
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('*/*/');
        }

        // 5. Build edit form
        $resultPage = $this->_initAction();
        $resultPage->getConfig()->getTitle()->prepend(__('Banners'));
        $resultPage->getConfig()->getTitle()->prepend($model->getName());

        return $resultPage;
    }
}
