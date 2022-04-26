<?php

/**
 * Class AdminEducationSuppliesOrdersControllerCore
 *
 * @since 1.8.1.0
 */
class AdminEducationSuppliesOrdersControllerCore extends AdminController {

	/**
	 * AdminEducationSuppliesOrdersControllerCore constructor.
	 *
	 * @since 1.8.1.0
	 */
	public function __construct() {

		$this->bootstrap = true;
		$this->table = 'education_supplies_order';
		$this->className = 'EducationSuppliesOrder';
		$this->publicName = $this->l('Factures des approvisionnement');
		$this->lang = false;
		$this->identifier = 'id_education_supplies_order';
		$this->controller_name = 'AdminEducationSuppliesOrders';
		$this->context = Context::getContext();

		$this->default_form_language = $this->context->language->id;
		EmployeeConfiguration::updateValue('EXPERT_SUPPLIESORDER_FIELDS', Tools::jsonEncode($this->getEducationSuppliesOrderFields()));
		$this->configurationField = Tools::jsonDecode(EmployeeConfiguration::get('EXPERT_SUPPLIESORDER_FIELDS'), true);

		if (empty($this->configurationField)) {
			EmployeeConfiguration::updateValue('EXPERT_SUPPLIESORDER_FIELDS', Tools::jsonEncode($this->getEducationSuppliesOrderFields()));
			$this->configurationField = Tools::jsonDecode(EmployeeConfiguration::get('EXPERT_SUPPLIESORDER_FIELDS'), true);
		}

		EmployeeConfiguration::updateValue('EXPERT_SUPPLIESORDER_SCRIPT', $this->generateParaGridScript(true));
		$this->paragridScript = EmployeeConfiguration::get('EXPERT_SUPPLIESORDER_SCRIPT');

		if (empty($this->paragridScript)) {
			EmployeeConfiguration::updateValue('EXPERT_SUPPLIESORDER_SCRIPT', $this->generateParaGridScript(true));
			$this->paragridScript = EmployeeConfiguration::get('EXPERT_SUPPLIESORDER_SCRIPT');
		}

		parent::__construct();

	}

	

	public function generateParaGridScript($regenerate = false) {

		if (!empty($this->paragridScript) && !$regenerate) {
			return '<script type="text/javascript">' . PHP_EOL . MediaAdmin::packJS($this->paragridScript) . PHP_EOL . '</script>';
		}

		$context = Context::getContext();
		$controllerLink = $context->link->getAdminLink($this->controller_name);

		$paragrid = new ParamGrid($this->className, $this->controller_name, $this->table, $this->identifier);
		$paragrid->paramTable = $this->table;
		$paragrid->paramController = $this->controller_name;
		$paragrid->height = 600;
		$paragrid->showNumberCell = 0;
		$paragrid->pageModel = [
			'type'       => '\'local\'',
			'rPP'        => 40,
			'rPPOptions' => [10, 20, 40, 50, 100, 200, 500],
		];

		$paragrid->rowInit = 'function (ui) {
			return {' . PHP_EOL . '
                    attr: \'data-link="\'+AjaxLink' . $this->controller_name . '+\'" data-class="' . $this->className . '" data-rowIndx="\' + ui.rowIndx+\'" data-object="\' + ui.rowData.' . $this->identifier . '+\' "\',
            };
        }';
		$paragrid->complete = 'function(){

		window.dispatchEvent(new Event(\'resize\'));

        }';
		$paragrid->selectionModelType = 'row';
		$paragrid->showTitle = 1;
		$paragrid->title = '\'' . $this->l('Management of') . ' ' . $this->publicName . '\'';
		$paragrid->fillHandle = '\'all\'';
		$paragrid->toolbar = [
			'items' => [

				[
					'type'     => '\'button\'',
					'label'    => '\'' . $this->l('Ajouter Une Facture') . '\'',
					'cls'      => '\'ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only\'',
					'listener' => 'function () {' . PHP_EOL . '
                           addNewSuppliesOrder();
						}',
				],

			],
		];
		$paragrid->filterModel = [
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
		$paragrid->showTitle = 1;
		$paragrid->title = '\'' . $this->l('Gestion des') . ' ' . $this->publicName . '\'';
		$paragrid->fillHandle = '\'all\'';

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
                var dataLenght = grid' . $this->className . '.option(\'dataModel.data\').length;
                return {
                    callback: function(){},
                    items: {
                        "add": {
                            name: \'' . $this->l('Add une Facture') . ' \',
                            icon: "add",
                            callback: function(itemKey, opt, e) {
                                addNewSuppliesOrder();
                            }
                        },
                        "edit": {
                            name : \'' . $this->l('Modifier ') . '\'' . '+rowData.firstname+" "+rowData.lastname,
                            icon: "edit",
                            callback: function(itemKey, opt, e) {
                             	editSuppliesOrder(rowData.id_education_supplies_order);
                            }
                        },

                    },
                };
            }',
			]];

		$option = $paragrid->generateParaGridOption();
		$script = $paragrid->generateParagridScript();

		if ($regenerate) {
			return $script;
		}

		$this->paragridScript = $script;
		return '<script type="text/javascript">' . PHP_EOL . MediaAdmin::packJS($this->paragridScript) . PHP_EOL . '</script>';
	}

	public function generateParaGridOption() {

		return true;

	}

	public function ajaxProcessinitController() {

		return $this->initGridController();
	}

	public function getEducationSuppliesOrderRequest() {

		$students = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
			(new DbQuery())
				->select('s.*, esr.name as supplier')
				->from('education_supplies_order', 's')
				->leftJoin('education_supplier', 'esr', 'esr.`id_education_supplier` = s.`id_education_supplier`')
				->orderBy('s.`date_add` DESC')
		);

		return $students;

	}

	public function ajaxProcessgetEducationSuppliesOrderRequest() {

		die(Tools::jsonEncode($this->getEducationSuppliesOrderRequest()));

	}

	public function getEducationSuppliesOrderFields() {

		return [
			[
				'title'      => $this->l('ID'),
				'dataIndx'   => 'id_education_supplies_order',
				'dataType'   => 'integer',
				'editable'   => false,
				'hiddenable' => 'no',
				'hidden'     => true,
			],
			[
				'title'      => '',

				'dataIndx'   => 'id_education_supplier',
				'dataType'   => 'integer',
				'align'      => 'center',
				'hiddenable' => 'no',
				'hidden'     => true,

			],
			[
				'title'    => $this->l('Date'),

				'dataIndx' => 'date_add',
				'align'    => 'center',
				'valign'   => 'center',
				'dataType' => 'date',
				'format'   => 'dd/mm/yy',
				'editable' => false,
			],

			[
				'title'    => $this->l('Numéro de Facture'),
				'width'    => 100,
				'dataIndx' => 'invoice_number',
				'align'    => 'left',
				'dataType' => 'string',
				'editable' => true,

			],

			[
				'title'    => $this->l('Fournisseur'),
				'width'    => 100,
				'exWidth'  => 15,
				'dataIndx' => 'supplier',
				'align'    => 'left',
				'dataType' => 'string',
				'editable' => true,
				'filter'   => [
					'crules' => [['condition' => "contain"]],
				],

			],
			[
				'title'        => $this->l('total HT'),

				'dataIndx'     => 'total_price',
				'align'        => 'right',
				'valign'       => 'center',
				'dataType'     => 'float',
				'numberFormat' => '#,##0.00_-"€"',
				'editable'     => false,
				'format'       => "#.###,00 € ",
				'updatable'    => false,
			],
			[
				'title'        => $this->l('total TTC'),

				'dataIndx'     => 'total_price_tax_incl',
				'align'        => 'right',
				'valign'       => 'center',
				'dataType'     => 'float',
				'numberFormat' => '#,##0.00_-"€"',
				'editable'     => false,
				'format'       => "#.###,00 € ",
				'updatable'    => false,
			],

		];

	}

	public function ajaxProcessgetEducationSuppliesOrderFields() {

		$fields = EmployeeConfiguration::get('EXPERT_SUPPLIESORDER_FIELDS');
		die($fields);
	}

	public function manageFieldsVisibility($fields) {

		return parent::manageFieldsVisibility($fields);
	}

	public function ajaxProcessUpdateVisibility() {

		$headerFields = Tools::jsonDecode(EmployeeConfiguration::get('EXPERT_SUPPLIESORDER_FIELDS'), true);
		$visibility = Tools::getValue('visibilities');

		foreach ($headerFields as $key => $headerField) {
			$hidden = '';

			foreach ($headerField as $field => $value) {

				if ($field == 'dataIndx') {

					if ($visibility[$value] == 1) {
						$hidden = false;
					} else

					if ($visibility[$value] == 0) {
						$hidden = true;
					}

				}

			}

			$headerField['hidden'] = $hidden;

			$headerFields[$key] = $headerField;
		}

		$headerFields = Tools::jsonEncode($headerFields);
		EmployeeConfiguration::updateValue('EXPERT_SUPPLIESORDER_FIELDS', $headerFields);
		$response = [
			'headerFields' => $headerFields,
		];

		die($headerFields);
	}

	public function ajaxProcessupdateJsonVisibility() {

		$visibility = Tools::getValue('visibilities');
	}

	public function ajaxProcessAddInvoiceLine() {

		$data = $this->createTemplate('controllers/education_supplies_orders/invoiceLine.tpl');
		$data->assign('i', Tools::getValue('index'));
		$data->assign('supplies', EducationSupplies::getSupplies());

		$address = new Address();
		$address->id_country = (int) $this->context->country->id;
		$taxRulesGroups = TaxRulesGroup::getTaxRulesGroups(true);
		$taxRates = [
			0 => [
				'id_tax_rules_group' => 0,
				'rates'              => [0],
				'computation_method' => 0,
			],
		];

		foreach ($taxRulesGroups as $taxRulesGroup) {
			$idTaxRulesGroup = (int) $taxRulesGroup['id_tax_rules_group'];
			$taxCalculator = TaxManagerFactory::getManager($address, $idTaxRulesGroup)->getTaxCalculator();
			$taxRates[$idTaxRulesGroup] = [
				'id_tax_rules_group' => $idTaxRulesGroup,
				'rates'              => [],
				'computation_method' => (int) $taxCalculator->computation_method,
			];

			if (isset($taxCalculator->taxes) && count($taxCalculator->taxes)) {

				foreach ($taxCalculator->taxes as $tax) {
					$taxRates[$idTaxRulesGroup]['rates'][] = (float) $tax->rate;
				}

			} else {
				$taxRates[$idTaxRulesGroup]['rates'][] = 0;
			}

		}

		$data->assign(
			[
				'currency'          => $currency = $this->context->currency,
				'tax_rules_groups'  => $taxRulesGroups,
				'taxesRatesByGroup' => $taxRates,
			]
		);
		$result = [
			'html' => $data->fetch(),
		];
		die(Tools::jsonEncode($result));
	}

	public function ajaxProcessAddNewSuppliesOrder() {

		$data = $this->createTemplate('controllers/education_supplies_orders/addInvoice.tpl');
		$data->assign('suppliers', EducationSupplier::getSupplier());
		$data->assign('supplies', EducationSupplies::getEducationSupplies());

		$address = new Address();
		$address->id_country = (int) $this->context->country->id;
		$taxRulesGroups = TaxRulesGroup::getTaxRulesGroups(true);
		$taxRates = [
			0 => [
				'id_tax_rules_group' => 0,
				'rates'              => [0],
				'computation_method' => 0,

			],
		];

		foreach ($taxRulesGroups as $taxRulesGroup) {
			$idTaxRulesGroup = (int) $taxRulesGroup['id_tax_rules_group'];
			$taxCalculator = TaxManagerFactory::getManager($address, $idTaxRulesGroup)->getTaxCalculator();
			$taxRates[$idTaxRulesGroup] = [
				'id_tax_rules_group' => $idTaxRulesGroup,
				'rates'              => [],
				'computation_method' => (int) $taxCalculator->computation_method,
			];

			if (isset($taxCalculator->taxes) && count($taxCalculator->taxes)) {

				foreach ($taxCalculator->taxes as $tax) {
					$taxRates[$idTaxRulesGroup]['rates'][] = (float) $tax->rate;
				}

			} else {
				$taxRates[$idTaxRulesGroup]['rates'][] = 0;
			}

		}

		$data->assign(
			[
				'currency'          => $currency = $this->context->currency,
				'tax_rules_groups'  => $taxRulesGroups,
				'taxesRatesByGroup' => $taxRates,
				'taxes'             => Tax::getRulesTaxes($this->context->language->id),
			]
		);

		$result = [
			'html' => $data->fetch(),
		];
		die(Tools::jsonEncode($result));
	}

	public function ajaxProcessSaveSuppliesOrder() {

		$items = Tools::getValue('items');
		$items = $items[0];
		$details = $items['details'];

		$invoice = new EducationSuppliesOrder();

		foreach ($items as $key => $value) {

			if (property_exists($invoice, $key) && $key != 'id_education_supplies_order') {

				if ($key == 'date_add' && !empty($value)) {
					$date = DateTime::createFromFormat('d/m/Y', $value);
					$value = date_format($date, "Y-m-d");
				}

				$invoice->{$key}

				= $value;
			}

		}

		$success = $invoice->add();

		if ($success) {

			foreach ($details as $detail) {
				$orderDetail = new SuppliesOrderDetail();

				foreach ($detail as $key => $value) {

					if (property_exists($orderDetail, $key) && $key != 'id_supplies_order_detail') {

						$orderDetail->{$key}

						= $value;
					}

				}

				$orderDetail->id_education_supplies_order = $invoice->id;
				$orderDetail->date_add = $invoice->date_add;
				$orderDetail->add();

			}

		}

		$result = [
			'success' => true,
			'message' => 'La facture a été ajouté avec succès à la base de donnée.',
		];

		die(Tools::jsonEncode($result));

	}

	public function ajaxProcessEditEducationSuppliesOrder() {

		$id_student = Tools::getValue('id_student');
		$student = new EducationSuppliesOrder($id_student);

		$titleTab = '';

		$data = $this->createTemplate('controllers/students/editEducationSuppliesOrder.tpl');

		if (Validate::isLoadedObject($student)) {
			$data->assign('genders', Gender::getGenders());
			$data->assign('countries', Country::getCountries($this->context->language->id, true));
			$data->assign('student', $student);
			$data->assign('courses', EducationSuppliesOrder::getEducationSuppliesOrderEducations($student->id));
			$data->assign('deletedCourses', EducationSuppliesOrder::getDeletedEducationSuppliesOrderEducations($student->id));
			$data->assign('jsLink', __PS_BASE_URI__ . $this->admin_webpath . '/js/editEducationSuppliesOrder.js');
			$titleTab = $this->l('Edite student') . ' ' . $student->firstname . ' ' . $student->lastname;

		} else {

		}

		$result = [
			'html'     => $data->fetch(),
			'titleTab' => $titleTab,
		];
		die(Tools::jsonEncode($result));

	}

	public function ajaxProcessaddEducationSuppliesOrderEducation() {

		$data = $this->createTemplate('controllers/students/newEducation.tpl');
		$id_student = Tools::getValue('id_student');
		$student = new EducationSuppliesOrder($id_student);
		$data->assign('student', $student);
		$data->assign('supplies', EducationSupply::getSupplies());
		$result = [
			'html' => $data->fetch(),
		];
		die(Tools::jsonEncode($result));
	}

	public function ajaxProcessaddNewEducationSuppliesOrderEducation() {

		$data = $this->createTemplate('controllers/students/addEducation.tpl');
		$id_student = Tools::getValue('id_student');
		$student = new EducationSuppliesOrder($id_student);
		$data->assign('student', $student);
		$data->assign('supplies', EducationSupply::getSupplies());
		$result = [
			'html' => $data->fetch(),
		];
		die(Tools::jsonEncode($result));
	}

	public function ajaxProcessEditEducationSuppliesOrderEducation() {

		$id_student_education = Tools::getValue('id_student_education');
		$student_education = new EducationSuppliesOrderEducation($id_student_education);
		$id_student = Tools::getValue('id_student');
		$student = new EducationSuppliesOrder($id_student);
		$data = $this->createTemplate('controllers/students/editEducation.tpl');

		$data->assign('education', EducationSuppliesOrder::getEducationSuppliesOrderEducationById($id_student_education));
		$data->assign('educationSteps', EducationSuppliesOrderEducationStep::getEducationStep());
		$data->assign('student', $student);
		$data->assign('supplies', EducationSupply::getSupplies());
		$data->assign('student_education', $student_education);

		$result = [
			'html' => $data->fetch(),
		];

		die(Tools::jsonEncode($result));
	}

	public function ajaxProcessSaveNewCompany() {

		$student = new EducationSuppliesOrder();

		foreach ($_POST as $key => $value) {

			if (property_exists($student, $key) && $key != 'id_student_company') {

				$student->{$key}

				= $value;
			}

		}

		try {
			$student->add();
		} catch (Exception $e) {
			$errors[] = $e->getMessage();
		}

		$result = [
			'success' => true,
			'message' => 'L\'étudiants a été ajouté avec succès à la base de donnée.',
		];

		die(Tools::jsonEncode($result));
	}

	public function ajaxProcessDeleteEducationSuppliesOrderEducation() {

		$id_student_education = Tools::getValue('id_student_education');
		$education = new EducationSuppliesOrderEducation($id_student_education);
		$reason = Tools::getValue('reason');
		$education->deleted = 1;
		$education->id_student_education_state = 5;
		$education->deleted_reason = $reason;
		$education->update();
		Db::getInstance()->execute(
			'DELETE FROM `' . _DB_PREFIX_ . 'student_pieces`
                WHERE `id_student_education` = ' . (int) $education->id
		);
		$return = [
			'success' => true,
			'message' => $this->l('La session de formation a été supprimée avec succès'),
		];
		die(Tools::jsonEncode($return));
	}

	public function initToolbarTitle() {

		parent::initToolbarTitle();

		switch ($this->display) {
		case '':
		case 'list':
			array_pop($this->toolbar_title);
			$this->toolbar_title[] = $this->l('Les factures du matériels pédagogique');
			break;
		case 'view':
			/** @var EducationSuppliesOrder $student */

			if (($student = $this->loadObject(true)) && Validate::isLoadedObject($student)) {
				array_pop($this->toolbar_title);
				$this->toolbar_title[] = sprintf($this->l('Information about EducationSuppliesOrder: %s'), mb_substr($student->firstname, 0, 1) . '. ' . $student->lastname);
			}

			break;
		case 'add':
		case 'edit':
			array_pop($this->toolbar_title);
			/** @var EducationSuppliesOrder $student */

			if (($student = $this->loadObject(true)) && Validate::isLoadedObject($student)) {
				$this->toolbar_title[] = sprintf($this->l('Editing EducationSuppliesOrder: %s'), mb_substr($student->firstname, 0, 1) . '. ' . $student->lastname);
			} else {
				$this->toolbar_title[] = $this->l('Creating a new EducationSuppliesOrder');
			}

			break;
		}

		array_pop($this->meta_title);

		if (count($this->toolbar_title) > 0) {
			$this->addMetaTitle($this->toolbar_title[count($this->toolbar_title) - 1]);
		}

	}

	
	public function ajaxProcessUpdateEducationSuppliesOrder() {

		$id = (int) Tools::getValue('id_student');

		if (isset($id) && !empty($id)) {
			/** @var ObjectModel $object */
			$object = new $this->className($id);

			if (Validate::isLoadedObject($object)) {
				/* Specific to objects which must not be deleted */
				$this->copyFromPost($object, $this->table);
				$result = $object->update();
				$this->afterUpdate($object);

				if ($object->id) {
					$this->updateAssoShop($object->id);
				}

				if (!isset($result) || !$result) {
					$this->errors[] = Tools::displayError('An error occurred while updating an object.') . ' <b>' . $this->table . '</b> (' . Db::getInstance()->getMsgError() . ')';
				} else {
					$result = [
						'success'   => true,
						'message'   => $this->l('Update successful'),
						'paragrid'  => $this->paragrid,
						'loadTabs'  => $this->loadTabs,
						'id_object' => $object->id,
					];

				}

				Logger::addLog(sprintf($this->l('%s modification', 'AdminTab', false, false), $this->className), 1, null, $this->className, (int) $object->id, true, (int) $this->context->employee->id);
			} else {
				$this->errors[] = Tools::displayError('An error occurred while updating an object.') . ' <b>' . $this->table . '</b> ' . Tools::displayError('(cannot load object)');
			}

		}

		$this->errors = array_unique($this->errors);

		if (count($this->errors)) {
			$result = [
				'success' => false,
				'message' => implode(PHP_EOL, $this->errors),
			];
		}

		die(Tools::jsonEncode($result));

	}

}
