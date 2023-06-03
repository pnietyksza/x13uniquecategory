<?php

namespace PrestaShop\Module\X13uniquecategory\Controller;

use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController as AdminController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\Common\Cache\CacheProvider;

class UniqueCategoryController extends AdminController
{
    public function __construct()
    {

    }
    public function checkCategoryIsUnique(Request $request) : Response
    {
        return (new Response);
    }
}
