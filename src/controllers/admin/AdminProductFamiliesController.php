<?php
/**
 * 2007-2016 PhenyxShop
 *
 * ephenyx is an extension to the PhenyxShop e-commerce software developed by PhenyxShop SA
 * Copyright (C) 2017-2018 ephenyx
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@ephenyx.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PhenyxShop to newer
 * versions in the future. If you wish to customize PhenyxShop for your
 * needs please refer to https://www.ephenyx.com for more information.
 *
 *  @author    ephenyx <contact@ephenyx.com>
 *  @author    PhenyxShop SA <contact@PhenyxShop.com>
 *  @copyright 2017-2019 ephenyx
 *  @copyright 2007-2016 PhenyxShop SA
 *  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *  PhenyxShop is an internationally registered trademark & property of PhenyxShop SA
 */

/**
 * @property ProductFamily $object
 */
class AdminProductFamiliesControllerCore extends AdminController {

    public $familySelector;

    public $specificPriceFields;

    public function __construct() {

        $this->bootstrap = true;
        $this->table = 'product_family';
        $this->className = 'ProductFamily';
        $this->publicName = $this->l('Product families');
        $this->lang = true;

        $this->context = Context::getContext();

        parent::__construct();
		EmployeeConfiguration::updateValue('EXPERT_PRODUCTFAMILIES_SCRIPT', $this->generateParaGridScript(true));
		$this->paragridScript = EmployeeConfiguration::get('EXPERT_PRODUCTFAMILIES_SCRIPT');
		if (empty($this->paragridScript)) {
            EmployeeConfiguration::updateValue('EXPERT_PRODUCTFAMILIES_SCRIPT', $this->generateParaGridScript(true));
            $this->paragridScript = EmployeeConfiguration::get('EXPERT_PRODUCTFAMILIES_SCRIPT');
        }		
		
        $this->configurationField = Tools::jsonDecode(EmployeeConfiguration::get('EXPERT_PRODUCTFAMILIES_FIELDS'), true);

        if (empty($this->configurationField)) {
            EmployeeConfiguration::updateValue('EXPERT_PRODUCTFAMILIES_FIELDS', Tools::jsonEncode($this->getProductFamilyFields()));
            $this->configurationField = Tools::jsonDecode(EmployeeConfiguration::get('EXPERT_PRODUCTFAMILIES_FIELDS'), true);
        }

        EmployeeConfiguration::updateValue('EXPERT_PRICESFAMILIES_FIELDS', Tools::jsonEncode($this->getSpecificPriceProductFamilyFields()));
        $this->specificPriceFields = Tools::jsonDecode(EmployeeConfiguration::get('EXPERT_PRICESFAMILIES_FIELDS'), true);

        if (empty($this->specificPriceFields)) {
            EmployeeConfiguration::updateValue('EXPERT_PRICESFAMILIES_FIELDS', Tools::jsonEncode($this->getSpecificPriceProductFamilyFields()));
            $this->specificPriceFields = Tools::jsonDecode(EmployeeConfiguration::get('EXPERT_PRICESFAMILIES_FIELDS'), true);
        }

        $this->familySelector = '<div class="pq-theme"><select id="familySelect"><option value="">' . $this->l('--Select--') . '</option><option value="0">' . $this->l('No family') . '</option>';

        foreach (ProductFamily::getProductFamilies() as $family) {
            $this->familySelector .= '<option value="' . $family->id . '">' . $family->name . '</option>';
        }

        $this->familySelector .= '</select></div>';

    }

    public function setMedia() {

        parent::setMedia();

        $this->addJS(__PS_BASE_URI__ . $this->admin_webpath . '/js/families.js');
        MediaAdmin::addJsDefL('addSpecificPrice', $this->l('Add a specific price'));
        MediaAdmin::addJsDefL('saveSpecificPrice', $this->l('Update specific price'));
		MediaAdmin::addJsDefL('valAlert1', $this->l('The value quantity '));
		MediaAdmin::addJsDefL('valAlert2', $this->l('is unique and already exist'));
		MediaAdmin::addJsDef([
			 'AjaxLink' . $this->controller_name => $this->context->link->getAdminLink($this->controller_name),
        ]);
		
		$this->addJS([
            __PS_BASE_URI__ . $this->admin_webpath . '/js/pgrid/pqSelect/pqselect.min.js',
            __PS_BASE_URI__ . $this->admin_webpath . '/js/pgrid/pqgrid.min.js',
            __PS_BASE_URI__ . $this->admin_webpath . '/js/pgrid/localize/pq-localize-fr.js',
            __PS_BASE_URI__ . $this->admin_webpath . '/js/pgrid/pqTouch/pqtouch.min.js',
            __PS_BASE_URI__ . $this->admin_webpath . '/js/pgrid/jsZip-2.5.0/jszip.min.js',
            __PS_BASE_URI__ . $this->admin_webpath . '/js/pgrid/FileSaver.js',
            __PS_BASE_URI__ . $this->admin_webpath . '/js/pgrid/javascript-detect-element-resize/jquery.resize.js',

        ]);

    }

    public function ajaxProcessinitController() {

        return $this->initGridController();
    }

    public function initGridController() {

        $this->paramGridObj = 'obj' . $this->className;
        $this->paramGridVar = 'grid' . $this->className;
        $this->paramGridId = 'grid_' . $this->controller_name;
        $ajaxlink = $this->context->link->getAdminLink($this->controller_name);
        $this->TitleBar = $this->l('Product List with Family');

        return parent::initGridController();

    }
	
	public function initContent()  {
        
		$this->displayGrid = true;
		$this->paramGridObj = 'obj' . $this->className;
        $this->paramGridVar = 'grid' . $this->className;
        $this->paramGridId = 'grid_' . $this->controller_name;
        $ajaxlink = $this->context->link->getAdminLink($this->controller_name);

        $this->TitleBar = $this->l('Product List with Family');
		
		$this->context->smarty->assign([
			'controller'         => Tools::getValue('controller'),
            'tabScript'          => $this->generateTabScript(Tools::getValue('controller')),
            'gridId'             => 'grid_AdminProductFamilies',
            'tableName'          => $this->table,
            'className'          => $this->className,
            'linkController'     => $this->context->link->getAdminLink($this->controller_name),
            'AjaxLink'           => $this->context->link->getAdminLink($this->controller_name),
            'paragridScript'     => $this->generateParaGridScript(),
			 'titleBar'  		=> $this->TitleBar,
            'bo_imgdir' 		=> __PS_BASE_URI__ . $this->admin_webpath . '/themes/' . $this->bo_theme . '/img/',
			'idController'       => '',
		]);

        parent::initContent();

    }
	
	public function generateParaGridScript($regenerate = false) {
		
		
		if (!empty($this->paragridScript) && !$regenerate) {
			return '<script type="text/javascript">'. PHP_EOL . MediaAdmin::packJS($this->paragridScript) . PHP_EOL .  '</script>';
		}
		
		$gridExtraFunction =  ['function buildProductFamilyFilter(){
        	var conteneur = $(\'#familySelector\').parent().parent();
			$(conteneur).empty();
        	$(conteneur).append(\'' . $this->familySelector . '\');
        	$(\'#familySelect\' ).selectmenu({
				classes: {"ui-selectmenu-menu": "scrollable"},
            	change: function(event, ui) {
                	grid' . $this->className . '.filter({
                    	mode: \'AND\',
                    	rules: [ { dataIndx:\'id_product_family\', condition: \'equal\', value: ui.item.value}]
                	});
            	}
       		});
	   	}',
		];
		
		$option = '';

        foreach (ProductFamily::getProductFamilies() as $family) {
            $option .= '"fam' . $family->id . '": {
             name: \'' . $family->name . ' \',
             icon: "edit",
             callback: function(itemKey, opt, e) {
                 var family = ' . $family->id . ';
                 var selected = selgrid' . $this->className . '.getSelection();
                 var products = [];
                 $.each( selected, function( index, value ){
                    products.push(value.rowData.id_product);
                });
                $.ajax({
                    type: \'POST\',
                    url: AjaxLinkAdminProductFamilies,
                    data: {
                        action: \'applyFamily\',
                        idFamily: family,
                        products:products,
                        ajax: true
                    },
                    async: false,
                    dataType: \'json\',
                    success: function (data) {
                        reloadProductFamilyGrid();
                    }

                })
            }
            },' . PHP_EOL;
        }
		
		$paragrid = new ParamGrid($this->className, $this->controller_name, $this->table, $this->identifier);
		
		$paragrid->height = 700;
		$paragrid->showNumberCell = 0;
		$paragrid->complete = 'function(){
		buildProductFamilyFilter();
		window.dispatchEvent(new Event(\'resize\'));
        }';
		$paragrid->rowInit = 'function (ui) {
        	return {
				attr: \'data-link="' . $this->context->link->getAdminLink($this->controller_name) . '" data-class="ProductFamily" data-rowIndx="\' + ui.rowIndx+\'"  data-object="\' + ui.rowData.id_product+ \'"\',
            };
        }';
		$paragrid->create = 'function(){
        	this.widget().pqTooltip();
        }';
		$paragrid->filterModel =  [
                'on'          => true,
                'mode'        => '\'OR\'',
                'header'      => true,
                'menuIcon'    => 0,
                'gridOptions' => [
                    'numberCell' => [
                        'show' => 0,
                    ],
                    'width'      => '\'flex\'',
                    'flex'       => [
                        'one' => true,
                    ],
                ],
            ];
		$paragrid->showTitle =1;
		$paragrid->editModel = [
                'saveKey'      => '\'$.ui.keyCode.ENTER\'',
                'clicksToEdit' => 1,
            ];
		$paragrid->title = '\'' .$this->l('Management of') . ' ' . $this->publicName. '\'';
		$paragrid->fillHandle = '\'all\'';
		$paragrid->beforeTableView =  'function( event, ui ) {
                $(\'.dropDownSupplier\').each( function() {
                    $(this).selectmenu({
					classes: {
						"ui-selectmenu-menu": "scrollable"
					},
                        change: function(event, ui) {
                            var idProduct = $(ui.item.element).attr("data-product");
                            var idFamily = ui.item.value;
                            $.ajax({
                                type: \'POST\',
                                url: AjaxLinkAdminProductFamilies,
                                data: {
                                    action: \'changeFamily\',
                                    idFamily: idFamily,
                                    idProduct:idProduct,
                                    ajax: true
                                },
                                async: false,
                                dataType: \'json\',
                                success: function (data) {
                                    reloadProductFamilyGrid();
                                }
                            });
                        }

                    });
                });

            }';
		$paragrid->contextMenu = [
                '#grid_' . $this->controller_name => [
                    'selector'  => '\'.pq-body-outer .pq-grid-row\'',
                    'animation' => [
                        'duration' => 250,
                        'show'     => '\'fadeIn\'',
                        'hide'     => '\'fadeOut\'',
                    ],
                    'build'     => 'function($triggerElement, e){
                var rowIndex = $($triggerElement).attr("data-rowIndx");
                var rowData = ' . 'grid' . $this->className . '.getRowData( {rowIndx: rowIndex} );
                selected = selgrid' . $this->className . '.getSelection().length;
                var dataLenght =  ' . 'grid' . $this->className . '.option(\'dataModel.data\').length;
                return {
                    callback: function(){},
                    items: {
                        "add": {
                            name: \'' . $this->l('Add new Family') . ' \',
                            icon: "add",
                            callback: function(itemKey, opt, e) {
                                var datalink = rowData.addLink;
                                openAjaxGridLink(datalink, rowData.' . $this->identifier . ', \'' . $this->controller_name . '\', \'View' . $this->controller_name . '\');
                            }
                        },
                        "select": {
                            name: \'' . $this->l('Select all item') . '\',
                            icon: "list-ul",
                            visible: function(key, opt){
                                var selected = selgrid' . $this->className . '.getSelection().length
                                var dataLenght = ' . 'grid' . $this->className . '.option(\'dataModel.data\').length;
                                if(dataLenght == selected) {
                                    return false;
                                }
                                return true;
                            },
                            callback: function(itemKey, opt, e) {
                                selgrid' . $this->className . '.selectAll({ all: true });
                            }
                        },
                        "unselect": {
                            name: \'' . $this->l('Unselect all item') . '\',
                            icon: "list-ul",
                            visible: function(key, opt){
                                var selected = selgrid' . $this->className . '.getSelection().length;
                                if(selected > 2) {
                                    return true;
                                }
                                return false;
                            },
                            callback: function(itemKey, opt, e) {
                                ' . 'grid' . $this->className . '.setSelection( null );
                            }
                        },
                        "bulkFamily": {
                            name: \'' . $this->l('Apply Family to selected items: ') . '\',
                            icon: "list-ul",
                            visible: function(key, opt){
                                var selected = selgrid' . $this->className . '.getSelection().length;
                                if(selected > 2) {
                                    return true;
                                }
                                return false;
                            },
                            items: {
                                ' . $option . '

                            }
                        },



                    },
                };
            }',
                ]];
		$paragrid->gridExtraFunction = $gridExtraFunction;
		
		
		$paragrid->generateParaGridOption();
		$script =  $paragrid->generateParagridScript() ;
		if($regenerate) {
			return $script;
		}
		$this->paragridScript = $script;
		return '<script type="text/javascript">'. PHP_EOL . MediaAdmin::packJS($this->paragridScript) . PHP_EOL .  '</script>';
	}
	
	public function generateParaGridOption() {

        return '';

    }

    public function getProductFamilyRequest() {

        $productFamilies = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
            (new DbQuery())
                ->select('a.id_product, a.reference, a.id_product_family, pl.name')
                ->from('product', 'a')
                ->leftJoin('product_lang', 'pl', 'pl.`id_product` = a.`id_product` AND pl.`id_lang` = ' . $this->context->language->id . ' AND pl.`id_shop` = ' . $this->context->shop->id)
                ->leftJoin('product_family_lang', 'pfl', 'pfl.`id_product_family` = a.`id_product_family` AND pfl.`id_lang` = ' . $this->context->language->id)
                ->where('a.active = 1')
        );

        $productFamilyLink = $this->context->link->getAdminLink($this->controller_name);
        $families = ProductFamily::getProductFamilies();

        foreach ($productFamilies as &$productFamily) {

            $supOption = '<select name="id_product_family" id="family_' . $productFamily['id_product'] . '" class="dropDownSupplier">';
            $supOption .= '<option value="" >' . $this->l('Choose Family') . '</option>';

            foreach ($families as $family) {
                $supOption .= '<option value="' . $family->id . '" data-product="' . $productFamily['id_product'] . '" ';

                if ($productFamily['id_product_family'] == $family->id) {
                    $supOption .= 'selected="selected"';
                }

                $supOption .= '>' . $family->name . '</option>';
            }

            $supOption .= '</select>';

            $productFamily['families'] = $supOption;

            $productFamily['addLink'] = $productFamilyLink . '&action=addObject&ajax=true&addproduct_family';

        }

        return $productFamilies;

    }
	
	public function ajaxProcessgetProductFamilyRequest() {

        die(Tools::jsonEncode($this->getProductFamilyRequest()));

    }

    public function getViewProductFamilyRequest() {

        $productFamilies = Db::getInstance()->executeS(
            (new DbQuery())
                ->select('b.*, a.*')
                ->from('product_family', 'a')
                ->leftJoin('product_family_lang', 'b', 'b.`id_product_family` = a.`id_product_family` AND b.`id_lang`  = ' . (int) $this->context->language->id)
                ->orderBy('a.`id_product_family` ASC')
        );
        $productFamilyLink = $this->context->link->getAdminLink($this->controller_name);

        foreach ($productFamilies as &$productFamily) {

            $productFamily['addLink'] = $productFamilyLink . '&action=addObject&ajax=true&addproduct_family';
            $productFamily['openLink'] = $productFamilyLink . '&id_product_family=' . $productFamily['id_product_family'] . '&id_object=' . $productFamily['id_product_family'] . '&updateproduct_family&action=initUpdateController&ajax=true';
            $productFamily['deleteLink'] = $productFamilyLink . '&id_product_family=' . $productFamily['id_product_family'] . '&id_object=' . $productFamily['id_product_family'] . '&deleteproduct_family&action=deleteObject&ajax=true';
			$productFamily['updateProductLink'] = $productFamilyLink . '&id_product_family=' . $productFamily['id_product_family'] . '&action=updateRealaysProduct&ajax=true';

        }

        return $productFamilies;
    }
	
	public function ajaxProcessgetViewProductFamilyRequest() {

        die(Tools::jsonEncode($this->getViewProductFamilyRequest()));

    }

    public function getProductFamilyFields() {

        return [
            [
                'title'      => $this->l('ID'),
                'width'      => 50,
                'dataIndx'   => 'id_product',
                'dataType'   => 'integer',
                'editable'   => false,
                'align'      => 'center',
                'filter'     => [
                    'crules' => [['condition' => "begin"]],
                ],
                'hiddenable' => 'no',
            ],

            [
                'title'      => ' ',
                'width'      => 10,
                'dataIndx'   => 'addLink',
                'dataType'   => 'string',
                'hidden'     => true,
                'hiddenable' => 'no',
            ],
            [
                'title'    => $this->l('Name'),
                'width'    => 200,
                'dataIndx' => 'name',
                'dataType' => 'string',
                'editable' => false,
                'filter'   => [
                    'crules' => [['condition' => "contain"]],
                ],
            ],

            [
                'title'    => $this->l('Reference'),
                'width'    => 200,
                'dataIndx' => 'reference',
                'dataType' => 'string',
                'editable' => false,
                'filter'   => [
                    'crules' => [['condition' => "begin"]],
                ],
            ],
            [

                'dataIndx'   => 'id_product_family',
                'dataType'   => 'integer',
                'hidden'     => true,
                'filter'     => [
                    'crules' => [['condition' => "equal"]],
                ],
                'hiddenable' => 'no',
            ],

            [
                'title'    => $this->l('Family'),
                'width'    => 100,
                'dataIndx' => 'families',
                'dataType' => 'html',
                'editable' => false,
                'filter'   => [
                    'attr'   => "id=\"familySelector\", placeholder=" . $this->l('--Select--') . " readonly",
                    'crules' => [['condition' => "equal"]],
                ],

            ],

        ];

    }
	
	public function ajaxProcessgetProductFamilyFields() {

        die(EmployeeConfiguration::get('EXPERT_PRODUCTFAMILIES_FIELDS'));
    }

    public function getViewProductFamilyFields() {

        return [
            [
                'title'      => $this->l('ID'),
                'width'      => 50,
                'dataIndx'   => 'id_product_family',
                'dataType'   => 'integer',
                'editable'   => false,
                'align'      => 'center',
                'filter'     => [
                    'crules' => [['condition' => "begin"]],
                ],
                'hiddenable' => 'no',
            ],
            [
                'title'      => ' ',
                'width'      => 50,
                'dataIndx'   => 'openLink',
                'dataType'   => 'string',
                'hidden'     => true,
                'hiddenable' => 'no',
            ],
            [
                'title'      => ' ',
                'width'      => 10,
                'dataIndx'   => 'addLink',
                'dataType'   => 'string',
                'hidden'     => true,
                'hiddenable' => 'no',
            ],
			[
                'title'      => ' ',
                'width'      => 10,
                'dataIndx'   => 'updateProductLink',
                'dataType'   => 'string',
                'hidden'     => true,
                'hiddenable' => 'no',
            ],
            [
                'title'    => $this->l('Family code'),
                'width'    => 150,
                'exWidth'  => 40,
                'dataIndx' => 'family_code',
                'dataType' => 'string',
                'filter'   => [
                    'crules' => [['condition' => "begin"]],

                ],
            ],
            [
                'title'    => $this->l('Family name'),
                'width'    => 150,
                'exWidth'  => 40,
                'dataIndx' => 'name',
                'dataType' => 'string',
                'filter'   => [
                    'crules' => [['condition' => "begin"]],

                ],
            ],

            [
                'title'    => $this->l('Family description'),
                'width'    => 150,
                'exWidth'  => 40,
                'dataIndx' => 'description',
                'dataType' => 'string',
                'filter'   => [
                    'crules' => [['condition' => "begin"]],

                ],
            ],

        ];
    }
	
	public function ajaxProcessgetViewProductFamilyFields() {

        die(Tools::jsonEncode($this->getViewProductFamilyFields()));
    }

    public function getSpecificPriceProductFamilyRequest($idProductFamily) {

        $return = [];
        $specificGroups = [];
        $currency = new Currency($this->context->currency->id);
        $groups = Group::getGroups($this->context->language->id);

        foreach ($groups as $group) {
            $specificGroups[$group['id_group']] = $group['name'];
        }

        $quanties = Db::getInstance()->executeS(
            (new DbQuery())
                ->select('DISTINCT `from_quantity`')
                ->from('product_family_price')
                ->where('`id_product_family`  = ' . $idProductFamily)
                ->orderBy('`from_quantity` ASC')
        );

        $return = [];

        foreach ($quanties as $quantity) {

            $specificPrices = Db::getInstance()->executeS(
                (new DbQuery())
                    ->select('*')
                    ->from('product_family_price')
                    ->where('`id_product_family`  = ' . $idProductFamily . ' AND `from_quantity` = ' . $quantity['from_quantity'])
                    ->orderBy('`id_group` ASC')
            );
            $prices = [];

            foreach ($groups as $group) {
                $prices['price_' . $group['id_group']] = '<div class="input-flex"><div class="input-group-select"><select name="reduction_type" class="typeSelect" data-id="' . $group['id_group'] . '" data-qty="'.$quantity['from_quantity'].'" id="typeSelect_' . $group['id_group']  . '_'.$quantity['from_quantity'].'"><option value="amount">' . $currency->sign . '</option><option value="percentage">%</option> </select></div><input type="number" data-id="0" class="specificValue" id="specificValue_' . $group['id_group']  . '_'.$quantity['from_quantity'].'" data-group="' . $group['id_group'] . '" value="" ></div>';
            }

            foreach ($specificPrices as $key => $specificPrice) {

                if ($specificPrice['reduction_type'] == 'amount') {
                    $price = number_format($specificPrice['price'], 2);
                } else {
                    $price = number_format(($specificPrice['reduction']) * 100, 2);
                }

                $option = '<select name="reduction_type" class="typeSelect" data-id="' . $specificPrice['id_group'] . '" data-qty="'.$quantity['from_quantity'].'" id="typeSelect_' . $specificPrice['id_group'] . '_'.$quantity['from_quantity'].'">';
                $option .= '<option value="amount" ';

                if ($specificPrice['reduction_type'] == 'amount') {
                    $option .= ' selected="selected" ';
                }

                $option .= '>' . $currency->sign . '</option>';
                $option .= '<option value="percentage" ';

                if ($specificPrice['reduction_type'] == 'percentage') {
                    $option .= ' selected="selected" ';
                }

                $option .= '>%</option></select>';
                $prices['price_' . $specificPrice['id_group']] = '<div class="input-flex"><div class="input-group-select">' . $option . '</div><input type="number"  class="specificValue" id="specificValue_' . $specificPrice['id_group']  . '_'.$quantity['from_quantity'].'" data-id="' . $specificPrice['id_product_family_price'] . '" data-group="' . $specificPrice['id_group'] . '" data-type="' . $specificPrice['reduction_type'] . '" value="' . $price . '" ></div>';
                $prices['from_quantity'] = '<input type="number" class="specificQuantity" value="'.$quantity['from_quantity'].'">';
            }

            $return[] = $prices;
        }

        return $return;

    }

    public function ajaxProcessGetSpecificPriceProductFamilyRequest() {

        $object = Tools::getValue('identifier');

        die(Tools::jsonEncode($this->getSpecificPriceProductFamilyRequest($object)));

    }

    public function getSpecificPriceProductFamilyFields() {

        $familyFields = [];
        $familyFields[] = [

            'title'      => $this->l('Quantity'),
            'width'      => 50,
            'dataIndx'   => 'from_quantity',
			'cls'      => 'formSpecificQuantity',
            'dataType'   => 'html',
            'align'      => 'center',
            'editable'   => false,
            'hiddenable' => 'no',
        ];
        $groups = Group::getGroups($this->context->language->id);

        foreach ($groups as $group) {

            $familyFields[] = [

                'title'    => $group['name'],
                'width'    => 120,
                'cls'      => 'formSpecificPrice',
                'dataIndx' => 'price_' . $group['id_group'],
                'dataType' => 'html',
                'editable' => false,
            ];
        }

        return $familyFields;
    }

    

    public function ajaxProcessgetSpecificPriceProductFamilyFields() {

        die(EmployeeConfiguration::get('EXPERT_PRICESFAMILIES_FIELDS'));
    }

    

    public function initPageHeaderToolbar() {

        if (empty($this->display)) {
            $this->page_header_toolbar_btn['view_product_family'] = [

                'desc'       => $this->l('View product families', null, null, false),
                'function'   => 'viewFamilies()',
                'identifier' => 'view',
                'controller' => 'AdminProductFamilies',
                'icon'       => 'process-icon-preview',
            ];
            $this->page_header_toolbar_btn['new_product_family'] = [
                'href'       => static::$currentIndex . '&action=addObject&ajax=true&addproduct_family&token=' . $this->token,
                'desc'       => $this->l('Add new product family', null, null, false),
                'process'    => 'openAjaxGridLink',
                'identifier' => 'new',
                'controller' => 'AdminProductFamilies',
                'icon'       => 'process-icon-new',
            ];
        }

        parent::initPageHeaderToolbar();
    }

    public function renderForm() {

        $this->fields_form = [
            'legend' => [
                'title' => $this->l('Product family'),
                'icon'  => 'icon-male',
            ],
            'input'  => [
                [
                    'type'     => 'text',
                    'label'    => $this->l('Family Code'),
                    'name'     => 'family_code',
                    'col'      => 2,
                    'hint'     => $this->l('Invalid characters:') . ' 0-9!&lt;&gt;,;?=+()@#"�{}_$%:',
                    'required' => true,
                ],
                [
                    'type'     => 'text',
                    'label'    => $this->l('Family name'),
                    'name'     => 'name',
                    'lang'     => true,
                    'col'      => 4,
                    'hint'     => $this->l('Invalid characters:') . ' 0-9!&lt;&gt;,;?=+()@#"�{}_$%:',
                    'required' => true,
                ],

                [
                    'type'  => 'text',
                    'label' => $this->l('Family description'),
                    'name'  => 'description',
                    'lang'  => true,
                    'col'   => 4,
                    'hint'  => $this->l('Invalid characters:') . ' 0-9!&lt;&gt;,;?=+()@#"�{}_$%:',
                ],
            ],
            'submit' => [
                'title' => $this->l('Save'),
            ],
        ];

        /** @var ProductFamily $obj */

        if (!($obj = $this->loadObject(true))) {
            return;
        }

        $this->tpl_form_vars = [
            'object'         => $obj,
            'languages'      => Language::getLanguages(false),
            'id_lang'        => $this->context->language->id,
            'specificScript' => $this->generateFormScript($obj),
        ];

        return parent::renderForm();
    }

    public function generateFormScript($object) {
		
		$currency = new Currency($this->context->currency->id);
		$groups = Group::getGroups($this->context->language->id);
		$groupPrices = '{';
		$groupPrices .= 'from_quantity: \'<input type="number" class="specificQuantity" value="1">\',';
		foreach ($groups as $group) {
			$groupPrices .= 'price_' . $group['id_group'].': \'<div class="input-flex"><div class="input-group-select"><select name="reduction_type" class="typeSelect" data-id="' . $group['id_group'] . '" id="typeSelect_' . $group['id_group']  . '_1"><option value="amount">' . $currency->sign . '</option><option value="percentage">%</option> </select></div><input type="number" data-id="0" class="specificValue" id="specificValue_' . $group['id_group']  . '_1" data-group="' . $group['id_group'] . '" value="" ></div>\',';
        }
		$groupPrices .= '}';

        return '<script type="text/javascript">' . PHP_EOL . '
                    var objSpecificPrice;
					$(document).ready(function(){' . PHP_EOL . '
						objSpecificPrice = getSpecificPriceProductFamilyObj(' . $object->id . ');
                        SpecificPricesGrid' . $object->id . ' = pq.grid(\'#gridSpecificPrice-' . $object->id . '\', objSpecificPrice);
                        selSpecificPricesGrid' . $object->id . ' = SpecificPricesGrid' . $object->id . '.SelectRow();
                    });' . PHP_EOL . '
					function reloadFamilySpecificPriceGrid() {
  						SpecificPricesGrid' . $object->id . '.option(\'dataModel.data\', getSpecificPriceProductFamilyRequest(' . $object->id . '));
  						SpecificPricesGrid' . $object->id . '.refreshDataAndView();
					}
					function addRowSpecificPrice() {
  						var rowIndx =  SpecificPricesGrid' . $object->id . '.addRow({
							newRow: '.$groupPrices.',
							checkEditable: false
						});
						
						
						SpecificPricesGrid' . $object->id . '.goToPage({
							rowIndx: rowIndx
						});
						$(".specificQuantity").focus();
						
						
					}


                </script>' . PHP_EOL;
		

    }

    public function ajaxProcessApplyFamily() {

        $idFamily = Tools::getValue('idFamily');
        $products = Tools::getValue('products');

        foreach ($products as $product) {
            Db::getInstance()->execute('UPDATE `' . _DB_PREFIX_ . 'product` SET `id_product_family` = ' . (int) $idFamily . ' WHERE `id_product` = ' . (int) $product);

        }

        die(true);

    }

    public function ajaxProcessChangeFamily() {

        $idFamily = Tools::getValue('idFamily');
        $idProduct = Tools::getValue('idProduct');
        Db::getInstance()->execute('UPDATE `' . _DB_PREFIX_ . 'product` SET `id_product_family` = ' . (int) $idFamily . ' WHERE `id_product` = ' . (int) $idProduct);
        die(true);

    }

    protected function ajaxProcessAfterAdd() {

        $this->familySelector = '<div class="pq-theme"><select id="familySelect"><option value="">' . $this->l('--Select--') . '</option><option value="0">' . $this->l('No family') . '</option>';

        foreach (ProductFamily::getProductFamilies() as $family) {
            $this->familySelector .= '<option value="' . $family->id . '">' . $family->name . '</option>';
        }

        $this->familySelector .= '</select></div>';

        $paragrid = $this->generateParagridScript($this->controller_name, $this->paragrid_option);

        $return = [
            'paragrid'       => $paragrid,
            'familySelector' => $this->familySelector,
            'controller'     => $this->controller_name,
        ];

        die(Tools::jsonEncode($return));
    }

    public function generateParaGridView() {

        $this->paragrid_option['paragrids'][] = [
            'paragridVar' => $this->paramGridVar,
            'paraGridId'  => $this->paramGridId,
            'paraGridObj' => $this->paramGridObj,
            'builder'     => [
                'height'         => 700,
                'width'          => '\'100%\'',
                'dataModel'      => [
                    'recIndx' => "'$this->identifier'",
                    'data'    => 'getView' . $this->className . 'Request()',
                ],
                'scrollModel'    => [
                    'autoFit' => true,
                ],
                'complete'       => 'function(){
                    desanimateContent();

                }',
                'colModel'       => 'getView' . $this->className . 'Fields()',
                'numberCell'     => [
                    'show' => 0,
                ],
                'pageModel'      => [
                    'type'       => '\'local\'',
                    'rPP'        => 40,
                    'rPPOptions' => [10, 20, 40, 50, 100, 200, 500],
                ],
                'create'         => 'function (evt, ui) {
                    buildHeadingAction(\'' . $this->paramGridId . '\', \'' . $this->controller_name . '\');

                }',
                'rowInit'        => 'function (ui) {
                return {' . PHP_EOL . '
                    attr: \'data-link="\'+AjaxLink' . $this->controller_name . '+\'" data-class="' . $this->className . '" data-rowIndx="\' + ui.rowIndx+\'"  data-object="\' + ui.rowData.' . $this->identifier . '+\' "\',
                };
                }',
                'change'         => 'function (evt, ui) {
                var grid = this;
                var rowIndx = ui.updateList[0].rowIndx;
                var $tr = grid.getRow( {rowIndxPage: rowIndx} );
                var controllerLink = $($tr).attr("data-link");
                var objectClass = $($tr).attr("data-class");
                var idObject = $($tr).attr("data-object");
                var objectUpdate = ui.updateList[0].newRow;
                updateGridObject(controllerLink, idObject, objectUpdate, objectClass);
                }',
                'showTitle'      => 1,
                'title'          => '\'' . $this->toolbar_title . '\'',
                'collapsible'    => 0,
                'freezeCols'     => 1,
                'rowBorders'     => 1,
                'stripeRows'     => 1,
                'selectionModel' => [
                    'type' => '\'row\'',
                ],
            ],
        ];

        if (!empty($this->contextMenuoption)) {

            

            foreach ($this->paragrid_option['paragrids'] as &$values) {
                $values['contextMenu'] = [
                    '#grid_View_' . $this->controller_name => [
                        'selector'  => '\'.pq-body-outer .pq-grid-row\'',
                        'animation' => [
                            'duration' => 250,
                            'show'     => '\'fadeIn\'',
                            'hide'     => '\'fadeOut\'',
                        ],
                        'build'     => 'function($triggerElement, e){

                var rowIndex = $($triggerElement).attr("data-rowIndx");
                var rowData = ' . $this->paramGridVar . '.getRowData( {rowIndx: rowIndex} );
                selected = selgridView' . $this->className . '.getSelection().length;
                var dataLenght = ' . $this->paramGridVar . '.option(\'dataModel.data\').length;
                return {
                    callback: function(){},
                    items: {
                     "add" :{
                    name: \'' . $this->l('Add new Family') . ' \',
                    icon: "add",
                    callback: function(itemKey, opt, e) {
                        var datalink = rowData.addLink;
                        openAjaxGridLink(datalink, rowData.' . $this->identifier . ', \'' . $this->controller_name . '\', \'View' . $this->controller_name . '\');
                        }
                            },
                "edit" :{
                    name:  \'' . $this->l('Edit the product family') . ' :\'+rowData.name,
                    icon: "edit",
                    visible: function(key, opt){

                            if(selected == 1) {
                                return true;
                            }
                            return false;
                    },
                    callback: function(itemKey, opt, e) {
                        var datalink = rowData.openLink;
                        openAjaxGridLink(datalink, rowData.' . $this->identifier . ', \'' . $this->controller_name . '\', \'View' . $this->controller_name . '\');
                    }
                },
				"sep1": "---------",
				 "update" :{
                    name:  \'' . $this->l('Update product') . ' :\'+rowData.name,
                    icon: "edit",
                    visible: function(key, opt){

                            if(selected == 1) {
                                return true;
                            }
                            return false;
                    },
                    callback: function(itemKey, opt, e) {
                        var datalink = rowData.updateProductLink;
                        processAjax(datalink);
                    }
                },
				
				"sep2": "---------",
                "delete" :{
                    name:  \'' . $this->l('Delete the selected product family') . ' :\'+rowData.name,
                    icon: "delete",
                    visible: function(key, opt){

                            if(selected == 1) {
                                return true;
                            }
                            return false;
                    },
                    callback: function(itemKey, opt, e) {
                        var datalink = rowData.deleteLink;
                        deleteAjaxGridLink(datalink);
						gridViewProductFamily.deleteRow({ rowIndx: rowIndex } );
                        }
                },

}
                };
                }',
                    ],
                ];
            }

        }

    }

    public function generateParagridViewScript() {

        $file = fopen("testgenerateParagridViewScript.js", "w");
        $is_function = false;
        $jsScript = '<script type="text/javascript">' . PHP_EOL;
        $jsScript .= 'var ' . $this->paramGridVar . ';' . PHP_EOL;
        $jsScript .= '$(document).ready(function(){' . PHP_EOL;

        foreach ($this->paragrid_option as $key => $value) {

            if ($key == 'paragrids') {

                foreach ($this->paragrid_option[$key] as $element => $values) {

                    $jsScript .= '  ' . $values['paragridVar'] . ' = pq.grid(\'#' . $values['paraGridId'] . '\', ' . $values['paraGridObj'] . ');' . PHP_EOL;
                    $jsScript .= '  var sel' . $values['paragridVar'] . ' = ' . $values['paragridVar'] . '.SelectRow();' . PHP_EOL;
                    $jsScript .= '  buildFx();' . PHP_EOL;

                    if (isset($values['contextMenu'])) {

                        foreach ($values['contextMenu'] as $contextMenu => $value) {
                            $jsScript .= '  $("' . $contextMenu . '").contextMenu({' . PHP_EOL;

                            foreach ($value as $option => $value) {

                                if (is_array($value)) {
                                    $jsScript .= '      ' . $this->deployArrayScript($option, $value) . PHP_EOL;
                                } else {
                                    $jsScript .= '      ' . $option . ': ' . $value . ',' . PHP_EOL;
                                }

                            }

                            $jsScript .= '  });' . PHP_EOL;
                        }

                    }

                    if (isset($values['detailModel'])) {

                        foreach ($values['detailModel'] as $detailModel => $value) {
                            $jsScript .= '  var ' . $detailModel . ' = function( data ) {' . PHP_EOL;
                            $jsScript .= '      return {' . PHP_EOL;

                            foreach ($value as $option => $value) {

                                if (is_array($value)) {
                                    $jsScript .= '      ' . $this->deployArrayScript($option, $value) . PHP_EOL;
                                } else

                                if (empty($option)) {
                                    $jsScript .= '      ' . $value . ',' . PHP_EOL;
                                } else {
                                    $jsScript .= '      ' . $option . ': ' . $value . ',' . PHP_EOL;
                                }

                            }

                            $jsScript .= '      };' . PHP_EOL;
                            $jsScript .= '  };' . PHP_EOL;
                        }

                    }

                }

            }

            if ($key == 'detailContextMenu') {

                foreach ($this->paragrid_option[$key] as $detailMenu => $value) {
                    $jsScript .= 'function ' . $detailMenu . '(evt, ui) {' . PHP_EOL;
                    $jsScript .= '  return [' . PHP_EOL;

                    foreach ($value as $menus) {

                        if (is_array($menus)) {
                            $jsScript .= '      {' . PHP_EOL;

                            foreach ($menus as $suboption => $value) {
                                $jsScript .= '      ' . $suboption . ': ' . $value . ',' . PHP_EOL;

                            }

                            $jsScript .= '      },' . PHP_EOL;
                        } else {
                            $jsScript .= '      ' . $menus . ',' . PHP_EOL;
                        }

                    }

                    $jsScript .= '  ];' . PHP_EOL;
                    $jsScript .= '};' . PHP_EOL;
                }

            }

            if ($key == 'extraFunction') {

                foreach ($this->paragrid_option[$key] as $function) {
                    $jsScript .= $function;

                }

            }

        }

        $jsScript .= '});' . PHP_EOL . PHP_EOL;

        foreach ($this->paragrid_option as $key => $value) {

            if ($key == 'gridFunction') {
                $is_function = true;

                foreach ($this->paragrid_option[$key] as $function => $value) {
                    $jsScript .= 'function ' . $function . ' {' . PHP_EOL;
                    $jsScript .= $value . PHP_EOL;
                    $jsScript .= '}' . PHP_EOL;
                }

            }

            if ($key == 'otherFunction') {

                foreach ($this->paragrid_option[$key] as $function => $value) {
                    $jsScript .= 'function ' . $function . ' {' . PHP_EOL;
                    $jsScript .= $value . PHP_EOL;
                    $jsScript .= '}' . PHP_EOL;
                }

            }

        }

        $jsScript .= '</script>' . PHP_EOL;
        fwrite($file, $jsScript);
        return $jsScript;

    }

    public function ajaxProcessViewFamilies() {

        $this->displayGrid = true;
		$this->paramGridObj = 'objView' . $this->className;
        $this->paramGridVar = 'gridView' . $this->className;
        $this->paramGridId = 'grid_View_' . $this->controller_name;
        $this->identifier_value = 'view';
        $this->contextMenuoption = [

            'add'    => [
                'name' => '\'' . $this->l('Add new product family') . '\'',
                'icon' => '"add"',
            ],
            'edit'   => [
                'name' => '\'' . $this->l('Edit the product family') . ' :\'' . '+rowData.name',
                'icon' => '"edit"',
            ],
            'delete' => [
                'name' => '\'' . $this->l('Delete the selected product family') . ' :\'' . '+rowData.name',
                'icon' => '"delete"',
            ],

        ];

        $this->generateParaGridView();

        $paragrid = $this->generateParagridViewScript();
        $this->ajax_js = '';
        $this->tab_identifier = 'view' . $this->controller_name . '-' . $this->identifier_value;

        $this->display = 'grid';
        $this->tab_link = 'tab-' . $this->controller_name . '-' . $this->identifier_value;
        $this->tab_liId = 'view-' . $this->controller_name . '-' . $this->identifier_value;
        $this->closeTabButton = '<button type="button" class="close tabdetail" data-id="' . $this->tab_liId . '" ><i class="icon-times-circle" aria-hidden="true"></i></button>';

        $this->displayBackOfficeHeader = '';
        $this->displayBackOfficeFooter = '';

        $this->ajax = false;
        $this->display_header = false;
        $this->show_page_header_toolbar = false;
        $this->show_header_script = false;
        $this->show_footer_script = false;
        $this->tableName = $this->className . '-' . $this->identifier_value;
        $this->initPageHeaderToolbar();
        $this->tab_name = $this->l('Product families List');
        $this->tabOnclick = 'onClick="reloadView' . $this->className . 'Grid();"';

        $this->template = 'grid.tpl';
        $template = $this->createTemplate($this->template);
        $this->context->smarty->assign([
            'toolbar_btn'       => $this->page_header_toolbar_btn,
            'tabTitleBar'       => $this->page_header_toolbar_title,
            'title'             => $this->page_header_toolbar_title,
            'tableName'         => $this->tableName,
            'currentController' => $this->controller_name,
            'currentTab'        => 'tab-' . $this->controller_name . '-' . $this->identifier_value,
            'idController'      => $this->tab_identifier,
            'link'              => $this->context->link,
            'id_tab'            => $this->identifier_value,
            'formId'            => 'form-' . $this->table . '-' . $this->identifier_value,
            'dataId'            => $this->tab_identifier,
            'gridId'            => $this->paramGridId,
            'tabScript'         => $paragrid,
        ]);

        $this->content = $template->fetch();

        $this->ajaxTabDisplay();
    }
	
	public function ajaxProcessUpdateFamilySpecificPrice() {
		
		
		$specificUpdates = Tools::getValue('specificUpdate');
		$idProductFamily = Tools::getValue('idProductFamily');
				
		foreach($specificUpdates as $specificUpdate) {
			$fromQty = $specificUpdate['fromQty'];
			
			foreach($specificUpdate['lines'] as $specificPrice) {
				$price = 0;
				$reduction = 0;
				if(empty($specificPrice['specificValue'])) {
					continue;
				}
				
				$type = $specificPrice['Type'];
				
				if($type == 'amount') {
					$price = $specificPrice['specificValue'];
				} else {
					$price = -1;
					$reduction = $specificPrice['specificValue']/100;
				}
				
				$idSpecificPrice = $specificPrice['idSpecificPrice'];
				if($idSpecificPrice > 0) {
					$familyPrice = new ProductFamilyPrice($idSpecificPrice);
					$familyPrice->price = $price;
					$familyPrice->reduction = $reduction;
					$familyPrice->reduction_type = $type;
					if(!$familyPrice->update()) {
						$this->errors[] = Tools::displayError('An error happen updating an object.');
					}
				} else {
					$familyPrice = new ProductFamilyPrice();
					$familyPrice->id_product_family = $idProductFamily;
					$familyPrice->id_group = $specificPrice['idGroup'];
					$familyPrice->from_quantity = $fromQty;
					$familyPrice->price = $price;
					$familyPrice->reduction = $reduction;
					$familyPrice->reduction_type = $type;
					if(!$familyPrice->add()) {
						$this->errors[] = Tools::displayError('An error happen adding an object.');
					}
				}
			}
		}
		
		if (empty($this->errors)) {
        	$result = [
            	'success' => true,
                'message' => $this->l('Specific price successfully updated'),
            ];
        } else {
        	$this->errors = array_unique($this->errors);
            $result = [
				'success' => false,
                'message' => implode(PHP_EOL, $this->errors),
            ];
        }
		die(Tools::jsonEncode($result));
		
	}
	
	public function ajaxProcessUpdateRealaysProduct() {
		
		
		$idProductFamily = Tools::getValue('id_product_family');
		$family = new ProductFamily($idProductFamily);
		$products = $family->getProductsbyFamilyCollection();
		$familyPrices = ProductFamilyPrice::getProductPricesbyFamilyCollection($family->id);
		foreach($products as $product) {
			if(!ProductAccount::applyFamilyAccount($product->id, $family)) {
				$this->errors[] = Tools::displayError('An error happen updating an Product account collection.');
			}
			
			foreach($familyPrices as $familyPrice) {
				$specificPrices = SpecificPrice::getSpecificPricesbyProduct($product->id, $familyPrice->from_quantity, $familyPrice->id_group);
				
				if(is_array($specificPrices)) {
					foreach($specificPrices as $specificPrice) {
						$specificPrice->price = $familyPrice->price;
						$specificPrice->reduction = $familyPrice->reduction;
						$specificPrice->reduction_type = $familyPrice->reduction_type;
						if(!$specificPrice->update()) {
							$this->errors[] = Tools::displayError('An error happen updating an object SpecificPrice.');
						}
					}
				} else {
					$specificPrice = new SpecificPrice();
					$specificPrice->id_specific_price_rule = 0;
					$specificPrice->id_product = (int) $product->id;
					$specificPrice->id_product_attribute = 0;
					$specificPrice->id_customer = 0;
					$specificPrice->id_shop = (int)$this->context->shop->id;
					$specificPrice->id_shop_group = (int)$this->context->shop->id_shop_group;
					$specificPrice->id_country = 0;
					$specificPrice->id_currency = 0;
					$specificPrice->id_group = (int) $familyPrice->id_group;
					$specificPrice->from_quantity = (int) $familyPrice->from_quantity;
					$specificPrice->price = (float) $familyPrice->price;
					$specificPrice->reduction_type = $familyPrice->reduction_type;
					$specificPrice->reduction_tax = 1;
					$specificPrice->reduction = $familyPrice->reduction;
					$specificPrice->from = '0000-00-00 00:00:00';
					$specificPrice->to = '0000-00-00 00:00:00';
					if(!$specificPrice->add()) {
							$this->errors[] = Tools::displayError('An error happen trying adding object SpecificPrice.');
						}
					
				}
			}
		}
		
		if (empty($this->errors)) {
        	$result = [
            	'success' => true,
                'message' => $this->l('Specific price successfully updated'),
            ];
        } else {
        	$this->errors = array_unique($this->errors);
            $result = [
				'success' => false,
                'message' => implode(PHP_EOL, $this->errors),
            ];
        }
		die(Tools::jsonEncode($result));
		
	}

}
