<?php
namespace extas\components\bv\jsonrpc;

use extas\components\jsonrpc\operations\OperationDispatcher;
use extas\components\SystemContainer;
use extas\interfaces\bv\ISubcategory;
use extas\interfaces\bv\ISubcategoryRepository;
use extas\interfaces\jsonrpc\IRequest;
use extas\interfaces\jsonrpc\IResponse;

/**
 * Class CalculationDo
 *
 * @package extas\components\bv\jsonrpc
 * @author jeyroik@gmail.com
 */
class CalculationDo extends OperationDispatcher
{
    /**
     * @param IRequest $request
     * @param IResponse $response
     */
    protected function dispatch(IRequest $request, IResponse &$response)
    {
        $items = $request->getData()['items'] ?? [];
        /**
         * @var $subCatRepo ISubcategoryRepository
         * @var $subCats ISubcategory[]
         */
        $subCatRepo = SystemContainer::getItem(ISubcategoryRepository::class);
        $subCatNames = array_column($items, 'subcategory_name');
        $subCats = $subCatRepo->all([ISubcategory::FIELD__NAME => $subCatNames]);

        $sum = 0;
        $hash = '';

        foreach ($subCats as $subCat) {
            $sum += $subCat->getWeight();
            $hash && ($hash .= '.');
            $hash .= substr($subCat->getName(), 0, 3) . $subCat->getWeight();
        }

        $response->success([
            'sum' => $sum,
            'hash' => $hash
        ]);
    }
}
