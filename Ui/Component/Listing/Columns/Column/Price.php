<?php

namespace JustBetter\ProductGridExport\Ui\Component\Listing\Columns\Column;

use Magento\Catalog\Ui\Component\Listing\Columns\Price as MagentoColumn;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;

class Price extends MagentoColumn
{
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Magento\Framework\Locale\CurrencyInterface $localeCurrency,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        protected \Magento\Framework\App\Request\Http $request,
        array $components = [],
        array $data = [])
    {
        parent::__construct($context, $uiComponentFactory, $localeCurrency, $storeManager, $components, $data);
    }

    public function prepareDataSource(array $dataSource): array
    {
        if ($this->request->getModuleName() != 'productgridexport') {
            return parent::prepareDataSource($dataSource);
        }
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item[$fieldName])) {
                    $item[$fieldName] = number_format($item[$fieldName], 2, '.', '');
                }
            }
        }
        return $dataSource;
    }
}
