<?php

/**
 * Class AdminCertificationsControllerCore
 *
 * @since 1.9.1.0
 */
class AdminCertificationsControllerCore extends AdminController {

	/**
	 * AdminEducationsControllerCore constructor.
	 *
	 * @since 1.9.1.0
	 */
	public function __construct() {

		$this->bootstrap = true;
		$this->table = 'certification';
		$this->className = 'Certification';
		$this->lang = true;
		$this->publicName = $this->l('Certification');
		$this->context = Context::getContext();

		parent::__construct();
		$this->context = Context::getContext();
		EmployeeConfiguration::updateValue('EXPERT_CERTIFICATIONS_SCRIPT', $this->generateParaGridScript());
		$this->paragridScript = EmployeeConfiguration::get('EXPERT_CERTIFICATIONS_SCRIPT');

		if (empty($this->paragridScript)) {
			EmployeeConfiguration::updateValue('EXPERT_CERTIFICATIONS_SCRIPT', $this->generateParaGridScript());
			$this->paragridScript = EmployeeConfiguration::get('EXPERT_CERTIFICATIONS_SCRIPT');
		}

		EmployeeConfiguration::updateValue('EXPERT_CERTIFICATIONS_FIELDS', Tools::jsonEncode($this->getCertificationFields()));
		$this->configurationField = Tools::jsonDecode(EmployeeConfiguration::get('EXPERT_CERTIFICATIONS_FIELDS'), true);

		if (empty($this->configurationField)) {
			EmployeeConfiguration::updateValue('EXPERT_CERTIFICATIONS_FIELDS', Tools::jsonEncode($this->getCertificationFields()));
			$this->configurationField = Tools::jsonDecode(EmployeeConfiguration::get('EXPERT_CERTIFICATIONS_FIELDS'), true);
		}

	}

	public function setMedia() {

		parent::setMedia();
		MediaAdmin::addJsDef([
			'AjaxLink' . $this->controller_name => $this->context->link->getAdminLink($this->controller_name),
		]);
	}

	public function setAjaxMedia() {

		return $this->pushJS([
			$this->admin_webpath . '/js/tiny_mce/tiny_mce.js',
			$this->admin_webpath . '/js/tinymce.inc.js',
		]);
	}

	public function initContent() {

		$this->displayGrid = true;
		$this->paramGridObj = 'obj' . $this->className;
		$this->paramGridVar = 'grid' . $this->className;
		$this->paramGridId = 'grid_' . $this->controller_name;
		$ajaxlink = $this->context->link->getAdminLink($this->controller_name);

		$this->TitleBar = $this->l('Liste des certifications');

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

		$gridExtraFunction = [
			'
			function addNewCertification() {

			$.ajax({
				type: \'GET\',
				url: AjaxLinkAdminCertifications,
				data: {
					action: \'addNewCertification\',
					ajax: true
				},
				async: false,
				dataType: \'json\',
				success: function(data) {
					$("#detailCertifications").html(data.html);
					$("body").addClass("edit");
					$("#paragrid_' . $this->controller_name . '").slideUp();
					gridCertification.refreshDataAndView();
					$("#detailCertifications").slideDown();
				}
				});

			}
			function editCertification(idCertification) {

			$.ajax({
				type: \'POST\',
				url: AjaxLinkAdminCertifications,
				data: {
					action: \'editCertification\',
					idCertification: idCertification,
					ajax: true
				},
				async: false,
				dataType: \'json\',
				success: function(data) {
					$("#detailCertifications").html(data.html);
					$("#paragrid_' . $this->controller_name . '").slideUp();
					gridCertification.refreshDataAndView();
					$("body").addClass("edit");
					$("#detailCertifications").slideDown();
				}
				});

			}
			function deleteCertification(idCertification) {


				$.ajax({
					type: \'POST\',
					url: AjaxLinkAdminCertifications,
					data: {
						action: \'deleteCertification\',
						idCertification: idCertification,
						ajax: true
					},
					async: false,
					dataType: \'json\',
					success: function(data) {
						if (data.success) {
							showSuccessMessage(data.message);
							gridCertification.refreshDataAndView();
						} else {
							showErrorMessage(data.message);
						}
					}
				});

			}


		',

		];

		$paragrid = new ParamGrid($this->className, $this->controller_name, $this->table, $this->identifier);
		$paragrid->paramTable = $this->table;
		$paragrid->paramController = $this->controller_name;
		$paragrid->height = "550";
		$paragrid->showNumberCell = 0;
		$paragrid->pageModel = [
			'type'       => '\'local\'',
			'rPP'        => 40,
			'rPPOptions' => [10, 20, 40, 50, 100, 200, 500],
		];
		$paragrid->create = 'function (evt, ui) {
            buildHeadingAction(\'' . 'grid_' . $this->controller_name . '\', \'' . $this->controller_name . '\');
        }';
		$paragrid->rowInit = 'function (ui) {
			return {' . PHP_EOL . '
                    attr: \'data-link="\'+AjaxLink' . $this->controller_name . '+\'" data-class="' . $this->className . '" data-rowIndx="\' + ui.rowIndx+\'" data-object="\' + ui.rowData.' . $this->identifier . '+\' "\',
            };
        }';
		$paragrid->complete = 'function(){
		//adjustCertificationGridHeight();
		grid' . $this->className . '.refreshView();
		window.dispatchEvent(new Event(\'resize\'));

        }';
		$paragrid->selectionModelType = 'row';
		$paragrid->toolbar = [
			'items' => [

				[
					'type'     => '\'button\'',
					'label'    => '\'Ajouter une Certification\'',
					'cls'      => '\'ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only\'',
					'listener' => 'function () {' . PHP_EOL . '
                           addAjaxObject("' . $this->controller_name . '");' . PHP_EOL . '
                          }' . PHP_EOL,
				],

			],
		];

		$paragrid->showTitle = 1;
		$paragrid->title = '\'' . $this->l('Gestion des certifications') . '\'';
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
                var pieceType = rowData.pieceType;

                return {
                    callback: function(){},

                    items: {


                    "edit": {
                            name: \'' . $this->l('Modifier ') . ' \'+rowData.name,
                            icon: "edit",
							visible: function(key, opt){
                               var selected = selgrid' . $this->className . '.getSelection().length;
                                if(selected > 1) {
                                    return false;
                                }
								return true;
                            },
                            callback: function(itemKey, opt, e) {

								//editCertification(rowData.id_certification);
								editAjaxObject("' . $this->controller_name . '", rowData.id_certification)
                            }
                        },

                        "sep2": "---------",
                        "delete": {
                            name: \'' . $this->l('Supprimer ') . ' \ : \'+rowData.name,
                            icon: "trash",

                            callback: function(itemKey, opt, e) {
                                deleteObject("' . $this->controller_name . '", "' . $this->className . '", "Supprimer une certification", "Etes vous sure de vouloir supprimer "+rowData.name+" ?", "Oui", "Annuler",rowData.id_certification);
                            }
                        },

                    },
                };
            }',
			]];

		$paragrid->gridExtraFunction = $gridExtraFunction;

		$option = $paragrid->generateParaGridOption();
		$this->paragridScript = $paragrid->generateParagridScript();
		return '<script type="text/javascript">' . PHP_EOL . $this->paragridScript . PHP_EOL . '</script>';
	}

	public function generateParaGridOption() {

		return '';

	}

	public function getCertificationRequest() {

		$certifications = Db::getInstance()->executeS(
			(new DbQuery())
				->select('*')
				->from('certification')
				->orderBy('`id_certification` ASC')
		);

		foreach ($certifications as &$certification) {
			$certification['image'] = '<img src="' . $certification['image'] . '" style="width:100px" >';
		}

		return $certifications;

	}

	public function ajaxProcessgetCertificationRequest() {

		die(Tools::jsonEncode($this->getCertificationRequest()));

	}

	public function getCertificationFields() {

		return [
			[
				'title'      => $this->l('ID'),
				'maxWidth'   => 100,
				'dataIndx'   => 'id_certification',
				'dataType'   => 'integer',
				'editable'   => false,
				'align'      => 'center',
				'valign'     => 'center',
				'filter'     => [
					'crules' => [['condition' => "begin"]],
				],
				'hiddenable' => 'no',
			],

			[
				'title'    => $this->l('Nom'),
				'minWidth' => 150,
				'exWidth'  => 20,
				'dataIndx' => 'name',
				'align'    => 'left',
				'valign'   => 'center',
				'dataType' => 'string',

			],
			[
				'title'    => $this->l('Nom de la certification'),
				'minWidth' => 150,
				'exWidth'  => 20,
				'dataIndx' => 'certification_name',
				'align'    => 'left',
				'valign'   => 'center',
				'dataType' => 'string',

			],

			[
				'title'    => $this->l('Image'),
				'width'    => 50,
				'dataIndx' => 'image',
				'dataType' => 'html',
				'editable' => false,
				'align'    => 'center',

			],

		];

	}

	public function ajaxProcessgetCertificationFields() {

		die(EmployeeConfiguration::get('EXPERT_CERTIFICATIONS_FIELDS'));
	}

	public function renderForm() {

		$obj = $this->loadObject(true);

		if (!($obj = $this->loadObject(true))) {
			return;
		}

		$this->fields_form = [
			'tinymce' => true,
			'legend'  => [
				'title' => $this->l('Certifications'),
				'icon'  => 'icon-tags',
			],
			'input'   => [
				[
					'type' => 'hidden',
					'name' => 'action',
				],
				[
					'type' => 'hidden',
					'name' => 'ajax',
				],
				[
					'type'     => 'text',
					'label'    => $this->l('Nom'),
					'name'     => 'name',
					'required' => true,
				],
				[
					'type'     => 'text',
					'label'    => $this->l('Nom de la certification'),
					'name'     => 'certification_name',
					'required' => true,
				],
				[
					'type'         => 'textarea',
					'label'        => $this->l('Description haut de page'),
					'name'         => 'description',
					'autoload_rte' => true,
					'hint'         => $this->l('Invalid characters:') . ' <>;=#{}',
				],
				[
					'type'     => 'text',
					'label'    => $this->l('URL'),
					'name'     => 'certification_url',
					'required' => true,
				],
				[
					'type'     => 'text',
					'label'    => $this->l('Nom Technique'),
					'name'     => 'technique_name',
					'required' => true,
				],
				[
					'type'     => 'image',
					'label'    => $this->l('Image'),
					'name'     => 'image',
					'required' => true,
				],
				[
					'type'     => 'switch',
					'label'    => $this->l('Active'),
					'name'     => 'active',
					'required' => false,
					'is_bool'  => true,
					'values'   => [
						[
							'id'    => 'active_on',
							'value' => 1,
							'label' => $this->l('Enabled'),
						],
						[
							'id'    => 'active_off',
							'value' => 0,
							'label' => $this->l('Disabled'),
						],
					],
				],

			],
			'submit'  => [
				'title' => $this->l('Save'),

			],
		];

		$this->fields_value['ajax'] = 1;

		if ($obj->id > 0) {
			$this->fields_value['action'] = 'updateCertification';
			$this->editObject = 'Edition d‘une certification';
			$this->fields_value['image'] = $obj->image;
		} else {
			$this->fields_value['action'] = 'addCertification';
			$this->editObject = 'Ajouter une certification';
		}

		return parent::renderForm();
	}

	public function ajaxProcessaddCertification() {

		$certification = new Certification();

		foreach ($_POST as $key => $value) {

			if (property_exists($certification, $key) && $key != 'id_certification') {

				$certification->{$key}

				= $value;

			}

		}

		$imageUploader = new HelperImageUploader('imageimage');
		$imageUploader->setAcceptTypes(['jpeg', 'png', 'jpg']);
		$files = $imageUploader->process();

		if (is_array($files) && count($files)) {

			foreach ($files as $image) {
				$type = pathinfo($image['name'], PATHINFO_EXTENSION);
				$data = file_get_contents($image['save_path']);
				$base64_code = base64_encode($data);
				$base64_str = 'data:image/' . $type . ';base64,' . $base64_code;
				$certification->image = $base64_str;

			}

		}

		try {
			$result = $certification->add();
		} catch (Exception $e) {
			$file = fopen("testAddCertification.txt", "w");
			fwrite($file, $e->getMessage());
		}

		if ($result) {
			$return = [
				'success' => true,
				'message' => 'La certification a été ajoutée avec succès',
			];
		} else {
			$return = [
				'success' => false,
				'message' => 'Le webmaster a légèrement merdé',
			];
		}

		die(Tools::jsonEncode($return));

	}

	public function ajaxprocessUpdateCertification() {

		$idCertification = Tools::getValue('id_certification');
		$certification = new Certification($idCertification);

		foreach ($_POST as $key => $value) {

			if (property_exists($certification, $key) && $key != 'id_certification') {
				$certification->{$key}

				= $value;

			}

		}

		$imageUploader = new HelperImageUploader('imageimage');
		$imageUploader->setAcceptTypes(['jpeg', 'png', 'jpg']);
		$files = $imageUploader->process();

		if (is_array($files) && count($files)) {

			foreach ($files as $image) {
				$type = pathinfo($image['name'], PATHINFO_EXTENSION);
				$data = file_get_contents($image['save_path']);
				$base64_code = base64_encode($data);
				$base64_str = 'data:image/' . $type . ';base64,' . $base64_code;
				$certification->image = $base64_str;

			}

		}

		$result = $certification->update();

		if ($result) {
			$return = [
				'success' => true,
				'message' => 'La certification a été mis à jour avec succès',
			];
		} else {
			$return = [
				'success' => false,
				'message' => 'Un truc a beuggggéééé',
			];
		}

		die(Tools::jsonEncode($return));

	}

	public function ajaxProcessDeleteCertification() {

		$idCertification = Tools::getValue('idCertification');
		$certification = new Certification($idCertification);
		$certification->delete();
		$return = [
			'success' => true,
			'message' => 'La certification a été supprimée avec succès',
		];
		die(Tools::jsonEncode($return));
	}

}
