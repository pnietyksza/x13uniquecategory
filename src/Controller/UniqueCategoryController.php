<?php

namespace PrestaShop\Module\X13uniquecategory\Controller;

use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController as AdminController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use PrestaShop\Module\X13uniquecategory\Entity\UniqueCategory;
use PrestaShop\PrestaShop\Core\Search\Filters\CategoryFilters;


class UniqueCategoryController extends AdminController
{
    public function changeStatus(Request $request, $categoryId, CategoryFilters $filters): Response
    {
        $entityManager = $this->container->get('doctrine.orm.entity_manager');
        $uniqueCategoryRepository = $entityManager->getRepository(UniqueCategory::class);

        $uniqueCategory = $uniqueCategoryRepository->findByIdCategory($categoryId);

        if (empty($uniqueCategory)) {
            $createUniqueCategory = new UniqueCategory();
            $createUniqueCategory->setIdCategory($categoryId);
            $createUniqueCategory->setIsUnique(1);
            $entityManager->persist($createUniqueCategory);
            $entityManager->flush();
        } else {
            $uniqueCategoryObject = $uniqueCategory[0];
            $isUnique = $uniqueCategoryObject->getIsUnique();
            if ($isUnique) {
                $uniqueCategoryObject->setIsUnique(0);
                $entityManager->persist($uniqueCategoryObject);
                $entityManager->flush();
            } else {
                $uniqueCategoryObject->setIsUnique(1);
                $entityManager->persist($uniqueCategoryObject);
                $entityManager->flush();
            }
        }
        $response = new Response();
        $response->setContent(json_encode([]));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
}
