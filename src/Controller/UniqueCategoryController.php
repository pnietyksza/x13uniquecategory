<?php

declare (strict_types=1);

namespace PrestaShop\Module\X13uniquecategory\Controller;

use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController as AdminController;

class UniqueCategoryController extends AdminController
{
    public function __construct()
    {
        dump('This is contruictor of UniqueCategoryController');
        exit;
    }
    public function checkCategoryIsUnique() : int
    {
        return 0;
    }
}
