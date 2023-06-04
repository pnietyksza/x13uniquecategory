<?php

/**
 * 2007-2023 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author    PrestaShop SA <contact@prestashop.com>
 *  @copyright 2007-2023 PrestaShop SA
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\PrestaShop\Adapter\SymfonyContainer;
use PrestaShop\PrestaShop\Core\Grid\Column\Type\Common\ToggleColumn;
use PrestaShop\PrestaShop\Core\Grid\Definition\GridDefinitionInterface;
use PrestaShopBundle\Form\Admin\Type\YesAndNoChoiceType;
use PrestaShop\PrestaShop\Core\Grid\Filter\Filter;
use PrestaShopBundle\Form\Admin\Type\SwitchType;

class X13uniquecategory extends Module
{
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'x13uniquecategory';
        $this->tab = 'administration';
        $this->version = '1.1.0';
        $this->author = 'Patryk Nietyksza';
        $this->need_instance = 0;

        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Unique category');
        $this->description = $this->l('Module written for x13 recruting process. Module adds new column in category view, thanks to this logged user have got access to this unique category.');

        $this->ps_versions_compliancy = ['min' => '1.7', 'max' => _PS_VERSION_];
    }

    public function install()
    {
        Configuration::updateValue('X13UNIQUECATEGORY_LIVE_MODE', false);

        include(dirname(__FILE__) . '/sql/install.php');

        return parent::install() &&
            $this->registerHook('actionCategoryGridQueryBuilderModifier') &&
            $this->registerHook('actionCategoryFormBuilderModifier') &&
            $this->registerHook('actionAfterUpdateCategoryFormHandler') &&
            $this->registerHook('actionAfterCreateCategoryFormHandler') &&
            $this->registerHook('actionCategoryGridDefinitionModifier');
    }

    public function uninstall()
    {
        Configuration::deleteByName('X13UNIQUECATEGORY_LIVE_MODE');

        return parent::uninstall();
    }

    protected function generateControllerURI()
    {
        $router = SymfonyContainer::getInstance()->get('router');

        return $router->generate('check_category_is_unique');
    }

    public function hookActionCategoryGridDefinitionModifier($params)
    {
        /** @var GridDefinitionInterface $definition */
        $definition = $params['definition'];
        $translator = $this->getTranslator();

        $definition
            ->getColumns()
            ->addAfter(
                'position',
                (new ToggleColumn('is_unique_category'))
                    ->setName($translator->trans('Special category', [], 'Modules.X13uniquecategory'))
                    ->setOptions([
                        'field' => 'is_unique_category',
                        'primary_field' => 'id_category',
                        'route' => 'check_category_is_unique',
                        'route_param_name' => 'categoryId',
                    ])
            );
        $definition->getFilters()->add(
            (new Filter('is_unique_category', YesAndNoChoiceType::class))
                ->setAssociatedColumn('is_unique_category')
        );
    }


    public function hookActionCategoryGridQueryBuilderModifier($params)
    {
        dump($params);

        /** @var QueryBuilder $searchQueryBuilder */
        $searchQueryBuilder = $params['search_query_builder'];

        /** @var CategoryFilters $searchCriteria */
        $searchCriteria = $params['search_criteria'];

        $searchQueryBuilder->addSelect(
            'IF(dcur.`is_unique_category` IS NULL,0,dcur.`is_unique_category`) AS `is_unique_category`'
        );
        $searchQueryBuilder->leftJoin(
            'c',
            '`' . pSQL(_DB_PREFIX_) . 'unique_category`',
            'dcur',
            'dcur.`id_category` = c.`id_category`'
        );
        if ('is_unique_category' === $searchCriteria->getOrderBy()) {
            $searchQueryBuilder->orderBy('dcur.`is_unique_category`', $searchCriteria->getOrderWay());
        }

        foreach ($searchCriteria->getFilters() as $filterName => $filterValue) {
            if ('is_unique_category' === $filterName) {
                $searchQueryBuilder->andWhere('dcur.`is_unique_category` = :is_unique_category');
                $searchQueryBuilder->setParameter('is_unique_category', $filterValue);

                if (!$filterValue) {
                    $searchQueryBuilder->orWhere('dcur.`is_unique_category` IS NULL');
                }
            }
        }
    }

    public function hookActionCategoryFormBuilderModifier($params)
    {
        /** @var FormBuilderInterface $formBuilder */
        $formBuilder = $params['form_builder'];
        $formBuilder->add('is_unique_category', SwitchType::class, [
            'label' => $this->getTranslator()->trans('Special category ', [], 'Modules.X13uniquecategory'),
            'required' => false,
        ]);

        $categoryId = $params['id'];

        $params['data']['is_unique_category'] = $this->getIsUniqueCategory($categoryId);

        $formBuilder->setData($params['data']);
    }

    private function getIsUniqueCategory($categoryId)
    {
        $sql = new DbQuery();
        $sql->select('is_unique_category');
        $sql->from('unique_category', 'c');
        $sql->where('c.id_category =' . pSQL((int)$categoryId));
        $result = Db::getInstance()->executeS($sql);

        if (!empty($result[0]['is_unique_category'])) {
            if ($result[0]['is_unique_category'] == "1") {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function hookActionAfterUpdateCategoryFormHandler(array $params)
    {
        $this->updateCategoryReviewStatus($params);
    }

    public function hookActionAfterCreateCategoryFormHandler(array $params)
    {
        $this->updateCategoryReviewStatus($params);
    }

    private function updateCategoryReviewStatus(array $params)
    {
        $categoryId = $params['id'];
        /** @var array $categoryFormData */
        $categoryFormData = $params['form_data'];
        $isUniqueCategory = (int) $categoryFormData['is_unique_category'];


        $db = \Db::getInstance();
        $request = 'SELECT `is_unique_category` FROM `' . _DB_PREFIX_ . 'unique_category` WHERE `id_category` =' . pSQL($categoryId);
        $result = $db->executeS($request);

        if (empty($result)) {
            $request = 'INSERT INTO `' . _DB_PREFIX_ . 'unique_category`(`is_unique_category`, `id_category`) VALUES (' . pSQL($isUniqueCategory) . ',' . pSQL($categoryId) . ')';
            $result = $db->executeS($request);
        } else {
            $request = ' UPDATE `' . _DB_PREFIX_ . 'unique_category` SET `is_unique_category`= ' . pSQL($isUniqueCategory) . ' WHERE `id_category`= ' . pSQL($categoryId) . '';
            $result = $db->execute($request);
        }
    }
}
