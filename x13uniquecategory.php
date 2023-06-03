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

class X13uniquecategory extends Module
{
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'x13uniquecategory';
        $this->tab = 'administration';
        $this->version = '1.1.0';
        $this->author = 'Patryk Nietykszaa';
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

        return parent::install() &&
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
}
