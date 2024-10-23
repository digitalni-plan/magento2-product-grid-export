<?php

namespace JustBetter\ProductGridExport\Model\Export;

use JustBetter\ProductGridExport\Model\LazySearchResultIterator;
use Magento\Framework\Exception\LocalizedException;

class ConvertToCsv extends \Magento\Ui\Model\Export\ConvertToCsv
{
    /**
     * Returns CSV file
     *
     * @return array
     * @throws LocalizedException
     */
    public function getCsvFile()
    {
        $component = $this->filter->getComponent();

        $name = md5(microtime());
        $file = 'export/'. $component->getName() . $name . '.csv';

        $this->filter->prepareComponent($component);
        $this->filter->applySelectionOnTargetProvider();
        $dataProvider = $component->getContext()->getDataProvider();
        $fields = $this->metadataProvider->getFields($component);
//        $options = $this->metadataProvider->getOptions();

        $this->directory->create('export');
        $stream = $this->directory->openFile($file, 'w+');
        $stream->lock();
        $stream->writeCsv($this->metadataProvider->getHeaders($component));
        $columnsWithType= $this->metadataProvider->getColumnsWithDataType($component);
        $page = 1;

        $component->prepareDataSource($component->getDataSourceData());

        $searchResult = $dataProvider->getSearchResult()
            ->setCurPage($page)
            ->setPageSize($this?->pageSize ?? 200);

        $items = LazySearchResultIterator::getGenerator($searchResult);
        foreach ($items as $item) {
            $this->metadataProvider->convertDate($item, $component->getName());

//            die( get_class($searchResult) . ' ' . get_class($items) . ' ' . get_class($item) );

            $stream->writeCsv($this->metadataProvider->getRowDataBasedOnColumnType($item, $fields, $columnsWithType, []));
            $stream->writeCsv($this->metadataProvider->getRowData($item, $fields, []));
        }

        $stream->unlock();
        $stream->close();

        return [
            'type' => 'filename',
            'value' => $file,
            'rm' => true  // can delete file after use
        ];
    }
}
