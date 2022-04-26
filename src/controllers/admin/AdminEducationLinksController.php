<?php

/**
 * Class AdminEducationLinksControllerCore
 *
 * @since 1.9.1.0
 */
class AdminEducationLinksControllerCore extends AdminController {

	/**
	 * AdminEducationsControllerCore constructor.
	 *
	 * @since 1.9.1.0
	 */
	public function __construct() {

		$this->bootstrap = true;
		$this->table = 'education_link';
		$this->className = 'EducationLink';
		$this->lang = true;
		$this->publicName = $this->l('Gestion des liens EDOF');
		$this->context = Context::getContext();

		parent::__construct();
		$this->context = Context::getContext();
		EmployeeConfiguration::updateValue('EXPERT_EDUCATIONLINKS_SCRIPT', $this->generateParaGridScript());
		$this->paragridScript = EmployeeConfiguration::get('EXPERT_EDUCATIONLINKS_SCRIPT');

		if (empty($this->paragridScript)) {
			EmployeeConfiguration::updateValue('EXPERT_EDUCATIONLINKS_SCRIPT', $this->generateParaGridScript());
			$this->paragridScript = EmployeeConfiguration::get('EXPERT_EDUCATIONLINKS_SCRIPT');
		}

		EmployeeConfiguration::updateValue('EXPERT_EDUCATIONLINKS_FIELDS', Tools::jsonEncode($this->getEducationLinkFields()));
		$this->configurationField = Tools::jsonDecode(EmployeeConfiguration::get('EXPERT_EDUCATIONLINKS_FIELDS'), true);

		if (empty($this->configurationField)) {
			EmployeeConfiguration::updateValue('EXPERT_EDUCATIONLINKS_FIELDS', Tools::jsonEncode($this->getEducationLinkFields()));
			$this->configurationField = Tools::jsonDecode(EmployeeConfiguration::get('EXPERT_EDUCATIONLINKS_FIELDS'), true);
		}

	}

	public function setMedia() {

		parent::setMedia();
		MediaAdmin::addJsDef([
			'AjaxLink' . $this->controller_name => $this->context->link->getAdminLink($this->controller_name),
		]);
	}

	public function initContent() {

		$this->displayGrid = true;
		$this->paramGridObj = 'obj' . $this->className;
		$this->paramGridVar = 'grid' . $this->className;
		$this->paramGridId = 'grid_' . $this->controller_name;
		$ajaxlink = $this->context->link->getAdminLink($this->controller_name);

		$this->TitleBar = $this->l('Liste des lien EDOF');

		$this->context->smarty->assign([
			'controller'     => Tools::getValue('controller'),
			'tabScript'      => $this->generateTabScript(Tools::getValue('controller')),
			'gridId'         => 'grid_' . $this->controller_name,
			'tableName'      => $this->table,
			'className'      => $this->className,
			'linkController' => $this->context->link->getAdminLink($this->controller_name),
			'AjaxLink'       => $this->context->link->getAdminLink($this->controller_name),
			'paragridScript' => $this->generateParaGridScript(),
			'titleBar'       => $this->TitleBar,
			'bo_imgdir'      => __PS_BASE_URI__ . $this->admin_webpath . '/themes/' . $this->bo_theme . '/img/',
			'idController'   => '',
		]);

		parent::initContent();

	}

	public function generateParaGridScript() {

		$this->paramPageModel = [
			'type'       => '\'local\'',
			'rPP'        => 40,
			'rPPOptions' => [10, 20, 40, 50, 100, 200, 500],
		];
		$this->paramChange = 'function(evt, ui) {
            var grid = this;
            var updateData = ui.updateList[0];
            var newRow = updateData.newRow;
            var dataField = Object.keys(newRow)[0].toString();
            var dataValue = newRow[dataField];
            var dataEducation = updateData.rowData.id_education_link;
            $.ajax({
                type: \'POST\',
                url: AjaxLinkAdminEducationLinks,
                data: {
                    action: \'updateByVal\',
                    idEducation: dataEducation,
                    field: dataField,
                    fieldValue: dataValue,
                    ajax: true
                },
                async: true,
                dataType: \'json\',
                success: function(data) {
                    if (data.success) {
                        showSuccessMessage(data.message);
                     } else {
                        showErrorMessage(data.message);
                    }
                }
            })
        }';

		$this->paramComplete = 'function(){
		grid' . $this->className . '.refreshView();
		window.dispatchEvent(new Event(\'resize\'));

        }';

		$this->paramTitle = '\'' . $this->l('Gestion des liens EDOF') . '\'';

		return parent::generateParaGridScript();
	}

	public function generateParaGridOption() {

		return '';

	}

	public function getEducationLinkRequest() {

		$educationLinks = Db::getInstance()->executeS(
			(new DbQuery())
				->select('el.*, e.reference, edl.name, ea.reference as attributeRef, eal.name as attributeName')
				->from('education_link', 'el')
				->leftJoin('education', 'e', 'e.`id_education` = el.`id_education`')
				->leftJoin('education_lang', 'edl', 'edl.`id_education` = e.`id_education` AND edl.`id_lang` = ' . (int) $this->context->language->id)
				->leftJoin('education_attribute', 'ea', 'ea.`id_education_attribute` = el.`id_education_attribute`')
				->leftJoin('education_attribute_lang', 'eal', 'eal.`id_education_attribute` = ea.`id_education_attribute` AND edl.`id_lang` = ' . (int) $this->context->language->id)
				->orderBy('`id_education_link` ASC')
		);

		foreach ($educationLinks as &$educationLink) {

			if (!empty($educationLink['attributeRef'])) {
				$educationLink['reference'] = $educationLink['attributeRef'];
				$educationLink['name'] = $educationLink['attributeName'];
			}

			unset($educationLink['attributeRef']);
			unset($educationLink['attributeName']);
		}

		return $educationLinks;

	}

	public function ajaxProcessgetEducationLinkRequest() {

		die(Tools::jsonEncode($this->getEducationLinkRequest()));

	}

	public function getEducationLinkFields() {

		return [
			[
				'title'      => $this->l('ID'),
				'maxWidth'   => 100,
				'dataIndx'   => 'id_education_link',
				'dataType'   => 'integer',
				'editable'   => false,
				'align'      => 'center',

				'hiddenable' => 'no',
			],
			[
				'dataIndx'   => 'id_education',
				'dataType'   => 'integer',
				'align'      => 'center',
				'hiddenable' => 'no',
				'hidden'     => true,

			],
			[
				'dataIndx'   => 'id_education_attribute',
				'dataType'   => 'integer',
				'align'      => 'center',
				'hiddenable' => 'no',
				'hidden'     => true,

			],

			[
				'title'    => $this->l('Référence'),
				'maxWidth' => 300,
				'exWidth'  => 20,
				'dataIndx' => 'reference',
				'align'    => 'left',
				'valign'   => 'center',
				'dataType' => 'string',
				'filter'   => [
					'crules' => [['condition' => "begin"]],
				],
			],
			[
				'title'    => $this->l('Formation'),
				'maxWidth' => 150,
				'dataIndx' => 'name',
				'dataType' => 'string',
				'hidden'   => true,
			],
			[
				'title'    => $this->l('Lien Edof'),
				'minWidth' => 400,
				'dataIndx' => 'edof_link',
				'dataType' => 'string',
				'editable' => true,
			],

		];

	}

	public function ajaxProcessgetEducationLinkFields() {

		die(EmployeeConfiguration::get('EXPERT_EDUCATIONLINKS_FIELDS'));
	}

	public function ajaxProcessUpdateByVal() {

		$idEducation = (int) Tools::getValue('idEducation');
		$field = Tools::getValue('field');
		$fieldValue = Tools::getValue('fieldValue');
		$education = new EducationLink($idEducation);
		$classVars = get_class_vars(get_class($education));

		if (isset($classVars['definition']['fields'])) {
			$fields = $classVars['definition']['fields'];
		}

		if (Validate::isLoadedObject($education)) {

			$education->$field = $fieldValue;
			$result = $education->update();

			if (!isset($result) || !$result) {
				$this->errors[] = Tools::displayError('An error occurred while updating the product.');
			} else {
				$result = [
					'success' => true,
					'message' => $this->l('Mise à jour réussis'),
				];
			}

		} else {

			$this->errors[] = Tools::displayError('An error occurred while loading the product.');
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
