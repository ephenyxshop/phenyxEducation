<?php

/**
 * Class AdminCronJobsControllerCore
 *
 * @since 1.9.1.0
 */
class AdminCronJobsControllerCore extends AdminController {

	/**
	 * AdminEducationsControllerCore constructor.
	 *
	 * @since 1.9.1.0
	 */
	public function __construct() {

		$this->bootstrap = true;
		$this->table = 'cronjobs';
		$this->className = 'CronJobs';
		$this->publicName = $this->l('CronJobs');
		$this->context = Context::getContext();

		parent::__construct();
		$this->context = Context::getContext();
		EmployeeConfiguration::updateValue('EXPERT_CRONJOBS_SCRIPT', $this->generateParaGridScript());
		$this->paragridScript = EmployeeConfiguration::get('EXPERT_CRONJOBS_SCRIPT');

		if (empty($this->paragridScript)) {
			EmployeeConfiguration::updateValue('EXPERT_CRONJOBS_SCRIPT', $this->generateParaGridScript());
			$this->paragridScript = EmployeeConfiguration::get('EXPERT_CRONJOBS_SCRIPT');
		}

		EmployeeConfiguration::updateValue('EXPERT_CRONJOBS_FIELDS', Tools::jsonEncode($this->getCronJobsFields()));
		$this->configurationField = Tools::jsonDecode(EmployeeConfiguration::get('EXPERT_CRONJOBS_FIELDS'), true);

		if (empty($this->configurationField)) {
			EmployeeConfiguration::updateValue('EXPERT_CRONJOBS_FIELDS', Tools::jsonEncode($this->getCronJobsFields()));
			$this->configurationField = Tools::jsonDecode(EmployeeConfiguration::get('EXPERT_CRONJOBS_FIELDS'), true);
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

		$this->TitleBar = $this->l('Liste des cronjobss');

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
		$this->paramToolbar = [
			'items' => [
				[
					'type'     => '\'button\'',
					'icon'     => '\'ui-icon-disk\'',
					'label'    => '\'' . $this->l('Ajouter un CRON') . '\'',
					'cls'      => '\'ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only pull-right\'',
					'listener' => 'function () {' . PHP_EOL . '
                           addAjaxObject("' . $this->controller_name . '");' . PHP_EOL . '
                          }' . PHP_EOL,
				],

			],
		];
		$this->paramTitle = '\'' . $this->l('Liste des Tâches Crons') . '\'';
		$this->rowInit = 'function (ui) {
			return {' . PHP_EOL . '
                    attr: \'data-link="\'+AjaxLink' . $this->controller_name . '+\'" data-class="' . $this->className . '" data-rowIndx="\' + ui.rowIndx+\'" data-object="\' + ui.rowData.' . $this->identifier . '+\' "\',
            };
        }';

		$this->paramContextMenu = [
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

					"execute": {
                            name: \'' . $this->l('Exécuter la Tâche Cronb ') . ' \'+rowData.task,
                            icon: "edit",
							visible: function(key, opt){
                               var selected = selgrid' . $this->className . '.getSelection().length;
                                if(selected > 1) {
                                    return false;
                                }
								return true;
                            },
                            callback: function(itemKey, opt, e) {

								executeCronJob(rowData.id_cronjobs);
                            }
                        },

					"duplicate": {
                            name: \'' . $this->l('Dupliquer le Cron Job ') . ' \'+rowData.task,
                            icon: "edit",
							visible: function(key, opt){
                               var selected = selgrid' . $this->className . '.getSelection().length;
                                if(selected > 1) {
                                    return false;
                                }
								return true;
                            },
                            callback: function(itemKey, opt, e) {

								duplicateCronJob(rowData.id_cronjobs);
                            }
                        },


                    "edit": {
                            name: \'' . $this->l('Modifier ') . ' \'+rowData.task,
                            icon: "edit",
							visible: function(key, opt){
                               var selected = selgrid' . $this->className . '.getSelection().length;
                                if(selected > 1) {
                                    return false;
                                }
								return true;
                            },
                            callback: function(itemKey, opt, e) {

								editAjaxObject("' . $this->controller_name . '", rowData.id_cronjobs)
                            }
                        },

                        "sep2": "---------",
                        "delete": {
                            name: \'' . $this->l('Supprimer ') . ' \ : \'+rowData.task,
                            icon: "trash",

                            callback: function(itemKey, opt, e) {
                                deleteObject("' . $this->controller_name . '", "' . $this->className . '", "Supprimer une cronjobs", "Etes vous sure de vouloir supprimer "+rowData.name+" ?", "Oui", "Annuler",rowData.id_cronjobs);
                            }
                        },

                    },
                };
            }',
			]];

		return parent::generateParaGridScript();
	}

	public function generateParaGridOption() {

		return '';

	}

	public function getCronJobsRequest() {

		$cronjobs = Db::getInstance()->executeS(
			(new DbQuery())
				->select('*, case when active = 1 then \'<div class="p-active"><i class="fa fa-check" aria-hidden="true" style="color:green;"></i></div>\' else \'<div class="p-inactive"><i class="fa fa-times" aria-hidden="true" style="color:red;"></i></div>\' end as cronjob_state')
				->from('cronjobs')
				->orderBy('`id_license` ASC')
		);

		foreach ($cronjobs as &$cronjob) {
			$licence = new License($cronjob['id_license']);
			$partner = new PartnerCompany($licence->id_partner_company);
			$cronjob['website'] = $licence->website;
		}

		return $cronjobs;

	}

	public function ajaxProcessgetCronJobsRequest() {

		die(Tools::jsonEncode($this->getCronJobsRequest()));

	}

	public function getCronJobsFields() {

		return [
			[
				'title'      => $this->l('ID'),
				'maxWidth'   => 100,
				'dataIndx'   => 'id_cronjobs',
				'dataType'   => 'integer',
				'editable'   => false,
				'align'      => 'center',
				'valign'     => 'center',
				'hidden'     => true,
				'hiddenable' => 'no',
			],

			[
				'title'    => $this->l('Nom'),
				'minWidth' => 150,
				'exWidth'  => 20,
				'dataIndx' => 'description',
				'align'    => 'left',
				'valign'   => 'center',
				'dataType' => 'string',

			],
			[
				'title'    => $this->l('Site web cible'),
				'minWidth' => 150,
				'exWidth'  => 20,
				'dataIndx' => 'website',
				'align'    => 'left',
				'valign'   => 'center',
				'dataType' => 'string',

			],
			[
				'title'    => $this->l('Tache'),
				'minWidth' => 150,
				'exWidth'  => 20,
				'dataIndx' => 'task',
				'align'    => 'left',
				'valign'   => 'center',
				'dataType' => 'string',

			],

			[
				'title'    => $this->l('Dernière activité'),

				'dataIndx' => 'updated_at',
				'align'    => 'center',
				'valign'   => 'center',
				'dataType' => 'date',
				'format'   => 'dd/mm/yy',
				'session'  => false,
				'cls'      => 'pq-calendar pq-side-icon',

			],
			[
				'title'    => $this->l('Cron activé'),
				'maxWidth' => 200,
				'dataIndx' => 'cronjob_state',
				'align'    => 'center',
				'dataType' => 'html',

			],

		];

	}

	public function ajaxProcessgetCronJobsFields() {

		die(EmployeeConfiguration::get('EXPERT_CRONJOBS_FIELDS'));
	}

	public function ajaxProcessExecuteCronJob() {

		$idCronJob = Tools::getValue('idCronJob');
		$cron = new CronJobs($idCronJob);

		$license = new License($cron->id_license);
		$result = $license->executeCronAction($cron->task);
		$query = 'UPDATE ' . _DB_PREFIX_ . 'cronjobs SET `updated_at` = NOW() WHERE `id_cronjobs` = ' . (int) $cron->id;
		Db::getInstance()->execute($query);
		$return = [
			'success' => true,
			'message' => $this->l('La tâche cron a retourner ') . $result,
		];

		die(Tools::jsonEncode($return));

	}

	public function renderForm() {

		$obj = $this->loadObject(true);

		if (!($obj = $this->loadObject(true))) {
			return;
		}

		$licenses = Db::getInstance()->executeS(
			(new DbQuery())
				->select('id_license, website')
				->from('license')
				->where('`active` = 1')
		);

		$this->fields_form = [
			'legend' => [
				'title' => $this->l('CronJobss'),
				'icon'  => 'icon-tags',
			],
			'input'  => [
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
					'name'     => 'description',
					'required' => true,
				],
				[
					'type'     => 'text',
					'label'    => $this->l('Tache'),
					'name'     => 'task',
					'required' => true,
				],
				[
					'type'    => 'select',
					'name'    => 'id_license',
					'label'   => $this->l('OF Partenaire'),
					'options' => [
						'query' => $licenses,
						'id'    => 'id_license', 'name' => 'website',
					],
				],

				[
					'type'    => 'select',
					'name'    => 'hour',
					'label'   => $this->l('Task frequency', 'CronJobsForms'),
					'desc'    => $this->l('At what time should this task be executed?', 'CronJobsForms'),
					'options' => [
						'query' => $this->getHoursFormOptions(),
						'id'    => 'id', 'name' => 'name',
					],
				],
				[
					'type'    => 'select',
					'name'    => 'day',
					'desc'    => $this->l('On which day of the month should this task be executed?', 'CronJobsForms'),
					'options' => [
						'query' => $this->getDaysFormOptions(),
						'id'    => 'id', 'name' => 'name',
					],
				],
				[
					'type'    => 'select',
					'name'    => 'month',
					'desc'    => $this->l('On what month should this task be executed?', 'CronJobsForms'),
					'options' => [
						'query' => $this->getMonthsFormOptions(),
						'id'    => 'id', 'name' => 'name',
					],
				],
				[
					'type'    => 'select',
					'name'    => 'day_of_week',
					'desc'    => $this->l('On which day of the week should this task be executed?', 'CronJobsForms'),
					'options' => [
						'query' => $this->getDaysofWeekFormOptions(),
						'id'    => 'id', 'name' => 'name',
					],
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
			'submit' => [
				'title' => $this->l('Save'),

			],
		];

		$this->fields_value['ajax'] = 1;

		if ($obj->id > 0) {
			$this->fields_value['action'] = 'updateCronJobs';
			$this->editObject = 'Edition d‘une cronjobs';
		} else {
			$this->fields_value['action'] = 'addCronJobs';
			$this->editObject = 'Ajouter une cronjobs';
		}

		return parent::renderForm();
	}

	public function getHoursFormOptions() {

		$data = [['id' => '-1', 'name' => $this->l('Every hour')]];

		for ($hour = 0; $hour < 24; $hour += 1) {
			$data[] = ['id' => $hour, 'name' => date('H:i', mktime($hour, 0, 0, 0, 1))];
		}

		return $data;
	}

	public function getDaysFormOptions() {

		$data = [['id' => '-1', 'name' => $this->l('Every day of the month')]];

		for ($day = 1; $day <= 31; $day += 1) {
			$data[] = ['id' => $day, 'name' => $day];
		}

		return $data;
	}

	public function getMonthsFormOptions() {

		$data = [['id' => '-1', 'name' => $this->l('Every month')]];

		for ($month = 1; $month <= 12; $month += 1) {
			$data[] = ['id' => $month, 'name' => $this->l(date('F', mktime(0, 0, 0, $month, 1)))];
		}

		return $data;
	}

	public function getDaysofWeekFormOptions() {

		$data = [['id' => '-1', 'name' => $this->l('Every day of the week')]];

		for ($day = 1; $day <= 7; $day += 1) {
			$data[] = ['id' => $day, 'name' => $this->l(date('l', strtotime('Sunday +' . $day . ' days')))];
		}

		return $data;
	}

	public function ajaxProcessaddCronJobs() {

		$cronjobs = new CronJobs();

		foreach ($_POST as $key => $value) {

			if (property_exists($cronjobs, $key) && $key != 'id_cronjobs') {

				$cronjobs->{$key}

				= $value;

			}

		}

		try {
			$result = $cronjobs->add();
		} catch (Exception $e) {
			$file = fopen("testAddCronJobs.txt", "w");
			fwrite($file, $e->getMessage());
		}

		if ($result) {
			$return = [
				'success' => true,
				'message' => 'La cronjobs a été ajoutée avec succès',
			];
		} else {
			$return = [
				'success' => false,
				'message' => 'Le webmaster a légèrement merdé',
			];
		}

		die(Tools::jsonEncode($return));

	}

	public function ajaxProcessDuplicateCronJob() {

		$idCronJob = Tools::getValue('idCronJob');
		$cron = new CronJobs($idCronJob);
		$cron = $cron->duplicateObject();

		$idObject = Tools::getValue('idObject');

		$_GET['id_cronjobs'] = $cron->id;
		$_GET['update' . $this->table] = "";

		$html = $this->renderForm();
		$li = '<li id="uperEditAdminCronJobs" data-controller="AdminDashboard"><a href="#contentEditAdminCronJobs">Duplication d\'une tache Cron</a><button type="button" class="close tabdetail" data-id="uperEditAdminCronJobs"><i class="icon icon-times-circle"></i></button></li>';
		$html = '<div id="contentEditAdminCronJobs" class="panel col-lg-12" style="display; flow-root;">' . $html . '</div>';

		$result = [
			'li'   => $li,
			'html' => $html,
		];

		die(Tools::jsonEncode($result));

	}

	public function ajaxprocessUpdateCronJobs() {

		$idCronJobs = Tools::getValue('id_cronjobs');
		$cronjobs = new CronJobs($idCronJobs);

		foreach ($_POST as $key => $value) {

			if (property_exists($cronjobs, $key) && $key != 'id_cronjobs') {
				$cronjobs->{$key}

				= $value;

			}

		}

		$result = $cronjobs->update();

		if ($result) {
			$return = [
				'success' => true,
				'message' => 'La cronjobs a été mis à jour avec succès',
			];
		} else {
			$return = [
				'success' => false,
				'message' => 'Un truc a beuggggéééé',
			];
		}

		die(Tools::jsonEncode($return));

	}

	public function ajaxProcessDeleteCronJobs() {

		$idCronJobs = Tools::getValue('idCronJobs');
		$cronjobs = new CronJobs($idCronJobs);
		$cronjobs->delete();
		$return = [
			'success' => true,
			'message' => 'La cronjobs a été supprimée avec succès',
		];
		die(Tools::jsonEncode($return));
	}

}
