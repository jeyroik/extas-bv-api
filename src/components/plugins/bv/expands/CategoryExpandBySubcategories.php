<?php
namespace extas\components\plugins\bv\expands;

use extas\components\plugins\expands\PluginExpandAbstract;
use extas\components\SystemContainer;
use extas\interfaces\bv\ICategory;
use extas\interfaces\bv\ISubcategory;
use extas\interfaces\bv\ISubcategoryRepository;
use extas\interfaces\expands\IExpandingBox;
use extas\interfaces\servers\requests\IServerRequest;
use extas\interfaces\servers\responses\IServerResponse;

/**
 * Class CategoryExpandBySubcategories
 *
 * @stage expand.index.category
 * @package extas\components\plugins\expands\schemas
 * @author jeyroik@gmail.com
 */
class CategoryExpandBySubcategories extends PluginExpandAbstract
{
    /**
     * @param IExpandingBox $parent
     * @param IServerRequest $request
     * @param IServerResponse $response
     */
    protected function dispatch(IExpandingBox &$parent, IServerRequest &$request, IServerResponse &$response)
    {
        /**
         * @var $categories array
         * @var $subRepo ISubcategoryRepository
         * @var $subs ISubcategory[]
         */
        $value = $parent->getValue([]);
        if (empty($value)) {
            $categoryIndex = $parent->getData();
            $categories = $categoryIndex['category_list'] ?? [];
        } else {
            $categories = $value['category_list'];
        }
        $categoryNames = array_column($categories, ICategory::FIELD__NAME);
        $categoriesByNames = array_column($categories, null, ICategory::FIELD__NAME);

        $subRepo = SystemContainer::getItem(ISubcategoryRepository::class);
        $subs = $subRepo->all([ISubcategory::FIELD__CATEGORY_NAME => $categoryNames]);

        foreach ($subs as $sub) {
            $cat = $sub->getCategoryName();
            if (isset($categoriesByNames[$cat])) {
                if (!isset($categoriesByNames[$cat]['subcategories'])) {
                    $categoriesByNames[$cat]['subcategories'] = [];
                }
                $categoriesByNames[$cat]['subcategories'][] = $sub->__toArray();
            }
        }

        $parent->addToValue('category_list', array_values($categoriesByNames));
    }
    /**
     * @return bool
     */
    protected function isAllowed()
    {
        return true;
    }
    /**
     * @return string
     */
    protected function getExpandName(): string
    {
        return 'subcategories';
    }
}