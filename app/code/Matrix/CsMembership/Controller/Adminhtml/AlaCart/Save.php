<?php

namespace Matrix\CsMembership\Controller\Adminhtml\AlaCart;

class Save extends \Magento\Backend\App\Action
{

    /**
     * @var \Magento\Framework\Filesystem
     */
    public $filesystem;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    protected $alaCartPriceFactory;

    public function __construct(
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Matrix\CsMembership\Model\AlaCartPriceFactory $alaCartPriceFactory,
        \Magento\Backend\App\Action\Context $context
    ) {
        parent::__construct($context);

        $this->filesystem = $filesystem;

        $this->storeManager = $storeManager;
        $this->alaCartPriceFactory = $alaCartPriceFactory;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $post_data = $this->getRequest()->getPostValue();

        if ($this->getRequest()->isPost()) {
            $redirectBack = $this->getRequest()->getParam('back', false);

            /* Calculate Commission */
            $arr = [];
            foreach ($post_data['qty_from'] as $key => $qty_from) {
                $qty_from   = trim($post_data['qty_from'][$key]);
                $qty_to     = trim($post_data['qty_to'][$key]);
                $price      = trim($post_data['price'][$key]);
                //$percentage = trim($post_data['percentage'][$key]);

                if ($qty_from != '' && $qty_to != '' && $price != '') {
                    $arr[$key]['qty_from']   = $qty_from;
                    $arr[$key]['qty_to']     = $qty_to;
                    $arr[$key]['price']      = $price;
                    //$arr[$key]['percentage'] = $percentage;
                }
            }

            $qty_above   = trim($post_data['qty_above'][0]);
            $above_price = trim($post_data['above_price'][0]);
            //$above_percentage = trim($post_data['above_percentage'][0]);

            if ($qty_above != '' && $above_price != '') {
                $arr[$key+1]['qty_above']        = $qty_above;
                $arr[$key+1]['above_price']      = $above_price;
                //$arr[$key+1]['above_percentage'] = $above_percentage;
            }

            //if( !empty($arr) ){
            $post_data['commission'] = json_encode($arr);
            //}
            /* Calculate Commission */

            try {
                $model = $this->alaCartPriceFactory->create()
                        ->addData($post_data)
                        ->setId($this->getRequest()->getParam("id"));

                $model->save();
                $this->messageManager->addSuccessMessage(__('Saved Successfully.'));

            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__($e->getMessage()));
                $this->_redirect("*/*/");
                return;
            }

            if ($redirectBack) {
                $this->_redirect('*/*/edit', [
                    'id' => $model->getId(),
                    '_current' => true
                ]);
                return;
            }
            $this->_redirect("*/*/");
        } else {
            $this->_redirect("*/*/");
        }
    }
}
