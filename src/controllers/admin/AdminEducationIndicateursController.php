<?php

/**
 * Class AdminEducationIndicateursControllerCore
 *
 * @since 1.9.1.0
 */
class AdminEducationIndicateursControllerCore extends AdminController {

	/**
	 * AdminEducationsControllerCore constructor.
	 *
	 * @since 1.9.1.0
	 */
	public function __construct() {

		$this->bootstrap = true;
		$this->table = 'education_indicateur';
		$this->className = 'EducationIndicateur';
		$this->lang = true;
		$this->publicName = $this->l('Indicateur de Performance sur les Formations');
		$this->context = Context::getContext();

		parent::__construct();
		$this->context = Context::getContext();
		EmployeeConfiguration::updateValue('EXPERT_EDUCATIONINDICATEURS_SCRIPT', $this->generateParaGridScript());
		$this->paragridScript = EmployeeConfiguration::get('EXPERT_EDUCATIONINDICATEURS_SCRIPT');

		if (empty($this->paragridScript)) {
			EmployeeConfiguration::updateValue('EXPERT_EDUCATIONINDICATEURS_SCRIPT', $this->generateParaGridScript());
			$this->paragridScript = EmployeeConfiguration::get('EXPERT_EDUCATIONINDICATEURS_SCRIPT');
		}

		EmployeeConfiguration::updateValue('EXPERT_EDUCATIONINDICATEURS_FIELDS', Tools::jsonEncode($this->getEducationIndicateurFields()));
		$this->configurationField = Tools::jsonDecode(EmployeeConfiguration::get('EXPERT_EDUCATIONINDICATEURS_FIELDS'), true);

		if (empty($this->configurationField)) {
			EmployeeConfiguration::updateValue('EXPERT_EDUCATIONINDICATEURS_FIELDS', Tools::jsonEncode($this->getEducationIndicateurFields()));
			$this->configurationField = Tools::jsonDecode(EmployeeConfiguration::get('EXPERT_EDUCATIONINDICATEURS_FIELDS'), true);
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

		$this->paramComplete = 'function(){
		grid' . $this->className . '.refreshView();
		window.dispatchEvent(new Event(\'resize\'));

        }';

		$this->paramTitle = '\'' . $this->l('Gestion des Indicateur par Formation') . '\'';

		return parent::generateParaGridScript();
	}

	public function generateParaGridOption() {

		return '';

	}

	public function getEducationIndicateurRequest() {

		$educationLinks = Db::getInstance()->executeS(
			(new DbQuery())
				->select('*')
				->from('education_indicateur')
				->orderBy('`score` DESC')
		);

		return $educationLinks;

	}

	public function ajaxProcessgetEducationIndicateurRequest() {

		die(Tools::jsonEncode($this->getEducationIndicateurRequest()));

	}

	public function getEducationIndicateurFields() {

		return [
			[
				'title'      => $this->l('ID'),
				'maxWidth'   => 100,
				'dataIndx'   => 'id_education_indicateur',
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
				'title'    => $this->l('Nom de la formation'),
				'width'    => 150,
				'exWidth'  => 20,
				'dataIndx' => 'name',
				'align'    => 'left',
				'valign'   => 'center',
				'dataType' => 'string',
				'filter'   => [
					'crules' => [['condition' => "begin"]],
				],
			],
			[
				'title'    => $this->l('Quantité de formation vendue'),
				'width'    => 150,
				'dataIndx' => 'qty',
				'dataType' => 'integer',
			],
			[
				'title'    => $this->l('Score moyen / 20'),
				'width'    => 200,
				'dataIndx' => 'score',
				'dataType' => 'float',
				'editable' => true,
			],

		];

	}

	public function ajaxProcessgetEducationIndicateurFields() {

		die(EmployeeConfiguration::get('EXPERT_EDUCATIONINDICATEURS_FIELDS'));
	}

}
