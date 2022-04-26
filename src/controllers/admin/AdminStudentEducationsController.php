<?php

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class AdminStudentEducationsControllerCore extends AdminController {

	public $pdfTemplate = [];

	public function __construct() {

		$this->bootstrap = true;
		$this->table = 'student_education';
		$this->identifier = 'id_student_education';
		$this->className = 'StudentEducation';
		$this->publicName = $this->l('Student Educations Sessions');
		$this->lang = false;
		$this->context = Context::getContext();

		parent::__construct();

		//EmployeeConfiguration::updateValue('EXPERT_STUDENTEDUCATION_FIELDS', Tools::jsonEncode($this->getStudentEducationFields()), $this->context->employee->id);
		$this->configurationField = Tools::jsonDecode(EmployeeConfiguration::get('EXPERT_STUDENTEDUCATION_FIELDS', $this->context->employee->id), true);

		if (empty($this->configurationField)) {
			EmployeeConfiguration::updateValue('EXPERT_STUDENTEDUCATION_FIELDS', Tools::jsonEncode($this->getStudentEducationFields()), $this->context->employee->id);
			$this->configurationField = Tools::jsonDecode(EmployeeConfiguration::get('EXPERT_STUDENTEDUCATION_FIELDS'), true);
		}

		EmployeeConfiguration::updateValue('EXPERT_STUDENTEDUCATION_SCRIPT', $this->generateParaGridScript(true), $this->context->employee->id);
		$this->paragridScript = EmployeeConfiguration::get('EXPERT_STUDENTEDUCATION_SCRIPT');

		if (empty($this->paragridScript)) {
			EmployeeConfiguration::updateValue('EXPERT_STUDENTEDUCATION_SCRIPT', $this->generateParaGridScript(true), $this->context->employee->id);
			$this->paragridScript = EmployeeConfiguration::get('EXPERT_STUDENTEDUCATION_SCRIPT');
		}

		$this->pdfTemplate = [
			'launch'                  => $this->l('Bien démarrer sa formation '),
			'cpmateriel'              => $this->l('Convention de remise de matériel'),
			'tosa'                    => $this->l('Formation Tosa'),
			'pipplet'                 => $this->l('Formation Pipplet'),
			'attestation_bureautique' => $this->l('Attestation de fin de formation Bureautique'),
			'attestation_langue'      => $this->l('Attestation de fin de formation Langue'),
		];

		EducationSession::generateSessionDate();

	}

	
	public function ajaxProcessOpenTargetController() {

		$targetController = $this->targetController;
		$data = $this->createTemplate('controllers/' . $this->table . '.tpl');
		$extracss = $this->pushCSS([
			$this->admin_webpath . '/themes/' . $this->bo_theme . '/css/confirm-box.css',

		]);

		$sessions = EducationSession::getFilledEducationSession();
		$lastEducatinOpen = EducationSession::getLastEducatinOpen();
		$data->assign([
			'sessions'         => $sessions,
			'lastEducatinOpen' => $lastEducatinOpen['id_education_session'],
		]);

		$steps = StudentEducationStep::getEducationStep();

		$platforms = Platform::getPlatforms();
		$data->assign([
			'paragridScript'     => $this->generateParaGridScript(),
			'controller'         => $this->controller_name,
			'tableName'          => $this->table,
			'className'          => $this->className,
			'link'               => $this->context->link,
			'manageHeaderFields' => true,
			'customHeaderFields' => $this->manageFieldsVisibility($this->configurationField),
			'saleAgents'         => SaleAgent::getSaleAgents(),
			'platforms'          => $platforms,
			'isSession'          => Configuration::get('PS_SESSION_FEATURE_ACTIVE'),
			'steps'              => $steps,
			'extracss'           => $extracss,
		]);

		$li = '<li id="uper' . $targetController . '" data-controller="AdminDashboard"><a href="#content' . $targetController . '">' . $this->publicName . '</a><button type="button" class="close tabdetail" data-id="uper' . $targetController . '"><i class="icon icon-times-circle"></i></button></li>';
		$html = '<div id="content' . $targetController . '" class="panel col-lg-12" style="display; flow-root;">' . $data->fetch() . '</div>';

		$result = [
			'li'   => $li,
			'html' => $html,
		];

		die(Tools::jsonEncode($result));
	}

	public function generateParaGridScript($regenerate = false) {

		$context = Context::getContext();
		$this->controller_name = 'AdminStudentEducations';
		$controllerLink = $context->link->getAdminLink($this->controller_name);

		$sessions = EducationSession::getEducationSession();
		$sessionSelector = '<div class="pq-theme"><select id="sessionSelect"><option value="">' . $this->l('--Select--') . '</option>';

		foreach (EducationSession::getEducationSession() as $session) {
			$sessionSelector .= '<option value="' . $session['id_education_session'] . '">' . $session['name'] . '</option>';
		}

		$sessionSelector .= '</select></div>';

		$lenghtSelector = '<div class="pq-theme"><select id="lenghtSelect"><option value="">' . $this->l('--Select--') . '</option>';
		$lenghtSelector .= '<option value="0">Aucune durée</option>';
		$lenghtSelector .= '<option value="1">Durée positive</option>';

		$dropdownSession = Tools::jsonEncode(EducationSession::getDropDownEducationSession());
		$lenghtSelector .= '</select></div>';

		$this->uppervar = 'var pqVS = {

            rpp: 100,
            init: function () {
                this.totalRecords = 0;
                this.requestPage = 1;
                this.data = [];
				this.needInstance = 1;
				this.hasFilter = 0;
				this.isSort = 0;
            }
        };' . PHP_EOL . '

		pqVS.init();' . PHP_EOL;

		$this->requestModel = '{
			beforeSend: function( jqXHR, settings ){

				if(pqVS.hasFilter == 0 && pqVS.isSort == 0) {
				var grid = this;
				var init = (pqVS.requestPage - 1) * pqVS.rpp;
				var datalen = pq_data.length;

				if(init < datalen) {
					var end = init + pqVS.rpp;
					var nextSet = [];
					for (var i = init; i < end; i++) {
						nextSet.push(pq_data[i]);
					}
					grid.hideLoading( );
					pqVS.needInstance = false;


					return { totalRecords: totalRecords, data: nextSet };
					jqXHR.abort();
				}
				}

 			},
            location: "remote",
            dataType: "json",
            method: "POST",
			recIndx: "id_customer",
			url: AjaxLinkAdminStudentEducations,
			postData: function () {
                return {
                    action: "getStudentEducationRequest",
                    ajax: 1,
					pq_data: JSON.stringify(pq_data),
					pq_curpage: pqVS.requestPage,
                    pq_rpp: pqVS.rpp,
					needInstance: pqVS.needInstance,
					totalRecords: pqVS.totalRecords
                };
            },
            getData: function (response) {
				var data = response.data;
				var len = data.length;				
				var datalen = pq_data.length;
				var init = (response.curPage - 1) * pqVS.rpp;
				var totalForCache = init+len;
				pqVS.totalRecords = response.totalRecords;
				var nextSet = [];
				console.log("init : "+init)
				
				if(pqVS.hasFilter == 0 && pqVS.isSort == 0) {
					if(totalForCache == pqVS.totalRecords) {
						for (var i = 0; i < len; i++) {
							pq_data.push(data[i]);
						}
						var end = totalForCache;
					} else 	if(init == datalen) {
						for (var i = 0; i < len; i++) {
							pq_data.push(data[i]);
						}
						var end = init + pqVS.rpp;
					} else {
						var end = init +pqVS.rpp;
					}
					var nextSet = [];
					for (var i = init; i < end; i++) {
						nextSet.push(pq_data[i]);
					} 
				} else {
					nextSet = response.data;
				} 
				console.log("end : "+end)


                return { totalRecords: response.totalRecords, data: nextSet }
            }
        }';

		$this->paramPageModel = [
			'type'       => '\'remote\'',
			'rPP'        => 100,
			'rPPOptions' => [100],
		];

		$this->paramChange = 'function(evt, ui) {

            var grid = this;
            var updateData = ui.updateList[0];
            var newRow = updateData.newRow;
            var dataField = Object.keys(newRow)[0].toString();
            var dataValue = newRow[dataField];
            var dataEducation = updateData.rowData.id_student_education;
			var rowIndex = updateData.rowIndx;
            $.ajax({
                type: "POST",
                url: AjaxLinkAdminStudentEducations,
                data: {
                    action: "updateByVal",
                    idEducation: dataEducation,
                    field: dataField,
                    fieldValue: dataValue,
					rowIndex: rowIndex,
                    ajax: true
                },
                async: true,
                dataType: "json",
                success: function(data) {
                    if (data.success) {

						education = data.education;

						grid.updateRow( {
    						rowIndx: rowIndex,
							checkEditable: false,
    						newRow: { "date_start": education.date_start, "date_end": education.date_end, "dateSession": education.sessionName }
						});
                     } else {
                        showErrorMessage(data.message);
                    }
                }
            })

        }';

		$this->sortModel = [
			'cancel' => true,
			'type'   => '\'remote\'',
		];
		$this->beforeSort = '
		function (evt) {
        	if (evt.originalEvent) {
            	pqVS.init();
				pqVS.isSort = true;
            }
            }
		';

		$this->filterModel = [
			'on'          => true,
			'mode'        => '\'AND\'',
			'header'      => true,
			'type'        => '\'remote\'',
			'menuIcon'    => true,
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
		$this->beforeFilter = 'function(event, ui ){
			console.log(ui);
			var value = ui.rules[0].value;
			if(value == "") {
				console.log("empty value")
			}
			if(typeof value !== "undefined" && !$.isNumeric(value) && value != ""  && value.length < 3) {
				return false;
			}
			
            pqVS.init();
			pqVS.hasFilter = true;
        }';
		$this->gridAfterLoadFunction = 'gridStudentEducation.pager().on("change", function(evt, ui){

			pqVS.requestPage = ui.curPage;
    	});';

		$this->paramComplete = 'function(){

		if(hasFilters) {

				gridStudentEducation.refresh( );


			}
		$(\'.pq-grid-title.ui-corner-top\').append(\'<div class="panel" id="uploadLink" style="display: none"></div>\');

		$("#pieceTSessionSelect" ).selectmenu({
			 	width: 180,
			 	classes: {
    				"ui-selectmenu-menu": "scrollable"
  				},
				change: function(event, ui) {

                    grid' . $this->className . '.filter({
                        mode: "AND",
                        rules: [
                            { dataIndx: "id_education_session", condition: "equal", value: ui.item.value}
                        ]
                    });
					checkSessionStep(ui.item.value);
					$("#selectedSessionValue").val(ui.item.value);
					if(ui.item.value >0) {
						getDataSession(ui.item.value);
						$("#expedition-pdf").slideDown();
						$("#session-pdf").slideDown();
						$("#export_format").slideDown();
						$("#sync_alter").slideDown();
					} else {
						$("#heading-actionAdminStudentEducations").html("");
						$("#session-pdf").slideUp();
						$("#sessionInfo").html("");
					}
					checkSessionIsOpen(ui.item.value);
					checkSessionIsClosed(ui.item.value);
					checkSessionIsEnded(ui.item.value);

                }
            });
		$("#educationType").selectmenu({
				width: 250,
				"change": function(event, ui) {
		   			grid' . $this->className . '.filter({
           			mode: "AND",
					rules: [
                		{ dataIndx: "educationPlatform", condition: "equal", value: ui.item.value}
						]
					});

	   			}
			});
		
		$("#export_format-button").slideUp();
		$("#stepSessionSelect" ).selectmenu({

                change: function(event, ui) {

                    grid' . $this->className . '.filter({
                        mode: "AND",
                        rules: [
                            { dataIndx: "id_student_education_state", condition: "equal", value: ui.item.value}
                        ]
                    });
                }
            });
		$("#stepSynchroSelect" ).selectmenu({

                "change": function(event, ui) {

					if(ui.item.value == "edof") {
						importEdof();
						$("#stepSynchroSelect").val("");
						$("#stepSynchroSelect").selectmenu("refresh");
					} else if(ui.item.value == "alterCampus") {
						proceedOffice();
						$("#stepSynchroSelect").val("");
						$("#stepSynchroSelect").selectmenu("refresh");
					} else if(ui.item.value == "7speaking") {
						proceedLanguage();
						$("#stepSynchroSelect").val("");
						$("#stepSynchroSelect").selectmenu("refresh");
					}

                }
            });



        }';

		$this->windowHeight = '300';
		$this->paramTitle = '"' . $this->l('Gestion des sessions de formation') . '"';
		$this->rowInit = 'function (ui) {
			return {' . PHP_EOL . '
                    attr: \'data-link="\'+AjaxLink' . $this->controller_name . '+\'" data-class="' . $this->className . '" data-rowIndx="\' + ui.rowIndx+\'" data-object="\' + ui.rowData.' . $this->identifier . '+\' "\',
            };
        }';

		$this->paramToolbar = [
			'items' => [

				[
					'type'     => '\'button\'',
					'label'    => '\'' . $this->l('Fichier d‘inscription langues') . '\'',
					'attr'     => '\'id="export_format"\'',
					'style'    => '\'display:none;\'',
					'cls'      => '\'ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only\'',
					'listener' => 'proceedLanguageFile',
				],
				[
					'type'     => '\'button\'',
					'label'    => '\'' . $this->l('Synchronistion Plateforme') . '\'',
					'attr'     => '\'id="sync_alter"\'',
					'style'    => '\'display:none;\'',
					'cls'      => '\'ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only\'',
					'listener' => 'proceedSynchAlter',
				],
				[
					'type'     => '\'button\'',
					'label'    => '\'' . $this->l('Fichier expédition') . '\'',
					'attr'     => '\'id="expedition-pdf"\'',
					'style'    => '\'display:none;\'',
					'cls'      => '\'ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only\'',
					'listener' => 'exportExpedition',
				],
				[
					'type'     => '\'button\'',
					'label'    => '\'' . $this->l('Exporter cette session') . '\'',
					'attr'     => '\'id="session-pdf"\'',
					'style'    => '\'display:none;\'',
					'cls'      => '\'ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only\'',
					'listener' => 'exportSession',
				],

				[
					'type'     => '\'button\'',
					'label'    => '\'' . $this->l('Exporter le publipostage') . '\'',
					'attr'     => '\'id="generate-pdf"\'',
					'style'    => '\'display:none;\'',
					'cls'      => '\'ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only\'',
					'listener' => 'pdfSessionGenerate',
				],
				[
					'type'     => '\'button\'',
					'label'    => '\'' . $this->l('Générer le rapport VDI') . '\'',
					'attr'     => '\'id="generate-vdi"\'',
					'style'    => '\'display:none;\'',
					'cls'      => '\'ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only\'',
					'listener' => 'excelVdiGenerate',
				],

				[
					'type'     => '\'button\'',
					'label'    => '\'' . $this->l('Fermer cette session') . '\'',
					'attr'     => '\'id="close-session"\'',
					'style'    => '\'display:none;\'',
					'cls'      => '\'ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only\'',
					'listener' => 'closeSession',
				],

				[
					'type'     => '\'button\'',
					'label'    => '\'' . $this->l('Exports des indicateurs pour cette session') . '\'',
					'attr'     => '\'id="exportIndicateur"\'',
					'style'    => '\'display:none;\'',
					'cls'      => '\'ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only\'',
					'listener' => 'exportIndicateur',
				],

			],
		];

		$this->filterModel = [
			'on'          => true,
			'mode'        => '\'AND\'',
			'header'      => true,
			'type'        => '\'remote\'',
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

		$this->editModel = [
			'clicksToEdit' => 1,
			'keyUpDown'    => 0,
		];

		$this->paramContextMenu = [
			'#grid_' . $this->controller_name => [
				'selector'  => '".pq-body-outer .pq-grid-row"',
				'animation' => [
					'duration' => 250,
					'show'     => '"fadeIn"',
					'hide'     => '"fadeOut"',
				],
				'build'     => 'function($triggerElement, e){

                var rowIndex = $($triggerElement).attr("data-rowIndx");
                var rowData = ' . 'grid' . $this->className . '.getRowData( {rowIndx: rowIndex} );
                selected = selgrid' . $this->className . '.getSelection().length;
                var dataLenght = grid' . $this->className . '.option("dataModel.data").length;
				var today = new Date();
				var dd = String(today.getDate()).padStart(2, "0");
				var mm = String(today.getMonth() + 1).padStart(2, "0"); //January is 0!
				var yyyy = today.getFullYear();
				today = yyyy + mm + dd;
				var sessionDate = rowData.date_start;
				sessionDate = sessionDate.replace("-", "").replace("-", "");
			    return {
                    callback: function(){},
                    items: {
                        "edit": {
                            name : "' . $this->l('Modifier la session de formation de  ') . '"' . '+rowData.firstname+" "+rowData.lastname,
                            icon: "edit",
							visible: function(key, opt){
                               var selected = selgrid' . $this->className . '.getSelection().length;
                                if(selected > 1) {
                                    return false;
                                }
                                return true;
                            },
                            callback: function(itemKey, opt, e) {
                             	//editEducation(rowData.id_student_education, rowData.id_customer);
								editAjaxObject("' . $this->controller_name . '", rowData.id_student_education)
                            }
                        },
						"expSelect": {
                            name : "' . $this->l('Exporter la sélection ') . '" ,
                            icon: "excel",
							visible: function(key, opt){
                               var selected = selgrid' . $this->className . '.getSelection().length;
                                if(selected < 2) {
                                    return false;
                                }

                                return true;
                            },
                            callback: function(itemKey, opt, e) {
								exportSelection(selgrid' . $this->className . ');

                            }
                        },
						"editST": {
                            name : "' . $this->l('Visualiser ou modifier ') . '"' . '+rowData.firstname+" "+rowData.lastname,
                            icon: "view",
							visible: function(key, opt){
                               var selected = selgrid' . $this->className . '.getSelection().length;
                                if(selected > 1) {
                                    return false;
                                }
                                return true;
                            },
                            callback: function(itemKey, opt, e) {
                             	editAjaxObject("AdminCustomers", rowData.id_customer)
                            }
                        },
						"transform": {
                            name: "' . $this->l('Changer le status en ') . ' ",
                            icon: "add",
							visible: function(key, opt){
                               var selected = selgrid' . $this->className . '.getSelection().length;
                                if(selected > 1) {
                                    return false;
                                }
								if(rowData.id_student_education_state == 9) {
											return false;
								}
                                return true;
                            },
                            items: {

								"dDev": {
                                    name: "' . $this->l('Inscription validée') . ' ",
                                    icon: "edit",
									visible: function(key, opt){
                              			if(rowData.id_student_education_state >= 3) {
											return false;
										}
								       	return true;
                            		},
									callback: function(itemKey, opt, e) {
                                        var dataiD = rowData.' . $this->identifier . ';
                                        changeEducationStepId(dataiD,  3);
                                    }
                                },
								"dAcc": {
                                    name: "' . $this->l('Inscription Accepté') . ' ",
                                    icon: "edit",
									visible: function(key, opt){
                              			if(rowData.id_student_education_state >= 4) {
											return false;
										}
								       	return true;
                            		},
									callback: function(itemKey, opt, e) {
                                        var dataiD = rowData.' . $this->identifier . ';
                                        changeEducationStepId(dataiD,  4);
                                    }
                                },
                                "dform": {
                                    name: "' . $this->l('En formation') . ' ",
                                    icon: "edit",
									visible: function(key, opt){
                              			if(rowData.id_student_education_state >= 7) {
											return false;
										}
								       	return true;
                            		},
									callback: function(itemKey, opt, e) {
                                        var dataiD = rowData.' . $this->identifier . ';
                                        changeEducationStepId(dataiD,  7);
                                    }
                                },
								"dtrait": {
                                    name: "' . $this->l('L‘étudiant est sorti de Formation') . ' ",
                                    icon: "edit",
									visible: function(key, opt){
                              			if(rowData.id_student_education_state >= 8) {
											return false;
										}
								       	return true;
                            		},
									callback: function(itemKey, opt, e) {
                                        var dataiD = rowData.' . $this->identifier . ';
                                        changeEducationStepId(dataiD,  8);
                                    }
                                },
								"isFact": {
                                    name: "' . $this->l('Facturé') . ' ",
                                    icon: "edit",
									visible: function(key, opt){
                              			if(rowData.id_student_education_state >= 9) {
											return false;
										}
								       	return true;
                            		},
									callback: function(itemKey, opt, e) {
                                        var dataiD = rowData.' . $this->identifier . ';
                                        changeEducationStepId(dataiD,  9);
                                    }
                                },
								"annuleTitulaire": {
                                    name: "' . $this->l('Annulation Titulaire') . ' ",
                                    icon: "edit",
									visible: function(key, opt){
                              			if(rowData.id_student_education_state > 4) {
											return false;
										}
								       	return true;
                            		},
									callback: function(itemKey, opt, e) {
                                        var dataiD = rowData.' . $this->identifier . ';
                                        changeEducationStepId(dataiD,  10);
                                    }
                                },

								}


                            },
						"bulktransform": {
                            name: "' . $this->l('Changer le status des éléments sélectionnés') . ' ",
                            icon: "add",
							visible: function(key, opt){
                               var selected = selgrid' . $this->className . '.getSelection().length;
							   var pieceSelected = selgrid' . $this->className . '.getSelection();
							   var allowed = true;
                                if(selected < 2) {
                                    return false;
                                }
								$.each(pieceSelected, function( index, value ) {
  									if(value.rowData.id_student_education_state == 9) {
										allowed = false;
									}
  								});
								if(allowed == false) {
									return false;
								}

                                return true;
                            },
                            items: {

                               "dDev": {
                                    name: "' . $this->l('Inscription validée') . ' ",
                                    icon: "edit",
									visible: function(key, opt){
                              			var pieceSelected = selgrid' . $this->className . '.getSelection();
							   			var allowed = true;
                                		$.each(pieceSelected, function( index, value ) {
  											if(value.rowData.id_student_education_state >= 3) {
												allowed = false;
											}
  										});
										if(allowed == false) {
											return false;
										}
										return true;
										},
									callback: function(itemKey, opt, e) {

                                       changeBulkEducationStepId(selgrid' . $this->className . ',  3);
                                    }
                                },
								"dAcc": {
                                    name: "' . $this->l('Inscription Accepté') . ' ",
                                    icon: "edit",
									visible: function(key, opt){
                              			var pieceSelected = selgrid' . $this->className . '.getSelection();
							   			var allowed = true;
                                		$.each(pieceSelected, function( index, value ) {
  											if(value.rowData.id_student_education_state >= 4) {
												allowed = false;
											}
  										});
										if(allowed == false) {
											return false;
										}
										return true;
										},
									callback: function(itemKey, opt, e) {

                                       changeBulkEducationStepId(selgrid' . $this->className . ',  4);
                                    }
                                },
                                "dform": {
                                    name: "' . $this->l('En formation') . ' ",
                                    icon: "edit",
									visible: function(key, opt){
                              			var pieceSelected = selgrid' . $this->className . '.getSelection();
							   			var allowed = true;
                                		$.each(pieceSelected, function( index, value ) {
  											if(value.rowData.id_student_education_state >= 7) {
												allowed = false;
											}
  										});
										if(allowed == false) {
											return false;
										}
										return true;
										},
									callback: function(itemKey, opt, e) {

                                        changeBulkEducationStepId(selgrid' . $this->className . ',  7);
                                    }
                                },
								"dtrait": {
                                    name: "' . $this->l('L‘étudiant est sorti de Formation') . ' ",
                                    icon: "edit",
									visible: function(key, opt){
                              			var pieceSelected = selgrid' . $this->className . '.getSelection();
							   			var allowed = true;
                                		$.each(pieceSelected, function( index, value ) {
  											if(value.rowData.id_student_education_state >= 8) {
												allowed = false;
											}
  										});
										if(allowed == false) {
											return false;
										}
										return true;
										},
									callback: function(itemKey, opt, e) {

                                        changeBulkEducationStepId(selgrid' . $this->className . ',  8);
                                    }
                                },
								"isFact": {
                                    name: "' . $this->l('Facturé') . ' ",
                                    icon: "edit",
									visible: function(key, opt){
                              			var pieceSelected = selgrid' . $this->className . '.getSelection();
							   			var allowed = true;
                                		$.each(pieceSelected, function( index, value ) {
  											if(value.rowData.id_student_education_state > 8) {
												allowed = false;
											}
  										});
										if(allowed == false) {
											return false;
										}
										return true;
										},
									callback: function(itemKey, opt, e) {

                                        changeBulkEducationStepId(selgrid' . $this->className . ',  9);
                                    }
                                },
								}


                            },
						"genereBulkFact": {
                                    name: "' . $this->l('Générer les Factures') . ' ",
                                    icon: "edit",
									visible: function(key, opt){
                              			var pieceSelected = selgrid' . $this->className . '.getSelection();
							   			var allowed = true;
										 if(selected < 2) {
                                    		return false;
                                		}
                                		$.each(pieceSelected, function( index, value ) {
  											if(value.rowData.id_student_education_state != 8) {
												allowed = false;
											}
  										});
										if(allowed == false) {
											return false;
										}
										return true;
										},
									callback: function(itemKey, opt, e) {
                                       console.log(selgrid' . $this->className . ');
                                        genereBulkInvoice(selgrid' . $this->className . ');
                                    }
                            },
						"genereFact": {
                                    name: "' . $this->l('Générer la Facture pour ') . '"' . '+rowData.firstname+" "+rowData.lastname,
                                    icon: "edit",
									visible: function(key, opt){
                              			var selected = selgrid' . $this->className . '.getSelection().length;
										if(selected > 1) {
                                    		return false;
										}
										if(rowData.id_student_education_state == 8) {
											return true;
										}

                                		return false;
                            		},
									callback: function(itemKey, opt, e) {

                                        genereInvoice(rowData.id_student_education);
                                    }
                            },
						"registerSuivi": {
                            name : "' . $this->l('Enregistrer un évènement pour ') . '"' . '+rowData.firstname+" "+rowData.lastname,
                            icon: "commenting",
							visible: function(key, opt){
                               var selected = selgrid' . $this->className . '.getSelection().length;
                                if(selected > 1) {
                                    return false;
                                }

                                return true;
                            },
                            callback: function(itemKey, opt, e) {
								registerEvent(rowData.id_student_education);
                            }
                        },
						"suivi": {
                            name : "' . $this->l('Fenêtre de suivis pour la session de ') . '"' . '+rowData.firstname+" "+rowData.lastname,
                            icon: "window",
							visible: function(key, opt){
                               var selected = selgrid' . $this->className . '.getSelection().length;
                                if(selected > 1) {
                                    return false;
                                }

                                return true;
                            },
                            callback: function(itemKey, opt, e) {
								openWindowSuivie(rowData.id_student_education);
                            }
                        },
						"studentAttest": {
                            name : "' . $this->l('Documents et attestations pour ') . '"' . '+rowData.firstname+" "+rowData.lastname,
                            icon: "file-text",
							visible: function(key, opt){
                               var selected = selgrid' . $this->className . '.getSelection().length;
                                if(selected > 1) {
                                    return false;
                                }
								if(rowData.id_student_education_state < 4) {
									return false;
								}

                                return true;
                            },
                            callback: function(itemKey, opt, e) {
								openWindowDocuments(rowData.id_student_education);
                            }
                        },
						"resendEmail": {
                            name: "' . $this->l('Contacter l‘étudiant par email') . ' ",
                            icon: "email",
							visible: function(key, opt){
                               var selected = selgrid' . $this->className . '.getSelection().length;
							   var pieceSelected = selgrid' . $this->className . '.getSelection();
							   var allowed = true;
                                if(selected > 2) {
                                    return false;
                                }
								$.each(pieceSelected, function( index, value ) {
  									if(value.rowData.id_student_education_state == 9) {
										allowed = false;
									}
  								});
								if(allowed == false) {
									return false;
								}

                                return true;
                            },
                            items: {

                                "sendLink": {
                            name : "' . $this->l('Renvoyer le lien EDOF de la formation  ') . '"' . '+rowData.name,
                            icon: "link",
							visible: function(key, opt){
                               var selected = selgrid' . $this->className . '.getSelection().length;
                                if(selected > 1) {
                                    return false;
                                }
								if(rowData.id_student_education_state > 2) {
									return false;
								}


                                return true;
                            },
                            callback: function(itemKey, opt, e) {
                             	resendEducationLink(rowData.id_student_education);
                            }
                        },

								"requestAccept": {
                            		name : "' . $this->l('Renvoyer l‘email invitant à accépter la proposition de formation') . '",
                            		icon: "link",
									visible: function(key, opt){
                               			var selected = selgrid' . $this->className . '.getSelection().length;
                                		if(selected > 1) {
                                    		return false;
                                		}
										if(rowData.id_student_education_state == 3) {
											return true;
										}
										return false;
                            		},
                            		callback: function(itemKey, opt, e) {
                             			resendRegistrationValidate(rowData.id_student_education);
                            		}
                        		},
								"sendAccLink": {
                            name : "' . $this->l('Renvoyer le lien de récupération du matériel pédagogique') . '",
                            icon: "link",
							visible: function(key, opt){
                               var selected = selgrid' . $this->className . '.getSelection().length;
                                if(selected > 1) {
                                    return false;
                                }
								if(rowData.id_student_education_state <4) {
											return false;
										}
								if(rowData.docReturn == 1) {
									return false;
								}



                                return true;
                            },
                            callback: function(itemKey, opt, e) {
                             	resendMaterialLink(rowData.id_student_education);
                            }
                        },

								"sendConvoc": {
                            		name : "' . $this->l('Renvoyer l‘email de Convocation à la formation') . '",
                            		icon: "link",
									visible: function(key, opt){
                               		var selected = selgrid' . $this->className . '.getSelection().length;
                                	if(selected > 1) {
                                    	return false;
										}
									if(rowData.id_student_education_state <4) {
										return false;
									}
									if(sessionDate > today) {
										return false;
									}

                                return true;
                            },
                            callback: function(itemKey, opt, e) {
                             	resendConvocationEmail(rowData.id_student_education);
                            }
                        },
						"sendEmail": {
                            name : "' . $this->l('Envoyer un email libre à ') . '"' . '+rowData.firstname+" "+rowData.lastname,
                            icon: "email",

                            callback: function(itemKey, opt, e) {
                             	sendEmail(rowData.id_student_education);
                            }
                        },


                            },

							},

						"delete": {
                            name : "' . $this->l('Supprimer la formation de  ') . '"' . '+rowData.firstname+" "+rowData.lastname,
                            icon: "delete",
							visible: function(key, opt){
                               var selected = selgrid' . $this->className . '.getSelection().length;
                                if(selected > 1) {
                                    return false;
                                }
								if(rowData.id_student_education_state == 10) {
										return true;
									}
								if(sessionDate < today && rowData.id_student_education_state < 4) {
										return true;
									}
								else if(rowData.id_student_education_state > 2) {
									return false;
								}
                                return true;
                            },
                            callback: function(itemKey, opt, e) {
                             	deleteObject("' . $this->controller_name . '", "' . $this->className . '", "Supprimer une session de formation", "Etes vous sure de vouloir supprimer cette formation ?", "Oui", "Annuler",rowData.id_student_education);
                            }
                        },
                    },
				}
            }',
			]];

		return parent::generateParaGridScript();
	}

	public function generateParaGridOption() {

		return true;

	}

	public function ajaxProcessinitController() {

		return $this->initGridController();
	}

	public function getStudentEducationRequest($pq_curpage, $pq_rpp, $pq_filter, $pq_sort) {

		$nbRecords = Db::getInstance()->getValue(
			(new DbQuery())
				->select('count( * )')
				->from($this->table)
		);

		$hasFilter = false;
		$file = fopen("testgetStudentEducationRequest.txt", "w");
		$customerArray = ['firstname', 'lastname', 'birthname', 'email'];
		$addressArray = ['phone', 'phone_mobile', 'postcode', 'city'];


		$query = new DbQuery();
		$query->select('s.*, c.firstname, c.lastname, c.birthname, c.email, c.password, est.`name` as state, gl.name as title, e.id_platform as educationPlatform, es.name as `dateSession`, es.session_date, esu.name as supplyName, sep.id_student_education_prerequis,  a.phone, a.phone_mobile, a.postcode, a.city');
		$query->from('student_education', 's');
		$query->leftJoin('customer', 'c', 'c.`id_customer` = s.`id_customer`');
		$query->innerJoin('address', 'a', 'a.`id_customer` = c.`id_customer`');
		$query->leftJoin('student_education_state_lang', 'est', 'est.`id_student_education_state` = s.`id_student_education_state` AND est.`id_lang` = ' . $this->context->language->id);
		$query->leftJoin('education_session', 'es', 'es.`id_education_session` = s.`id_education_session`');
		$query->leftJoin('education', 'e', 'e.`id_education` = s.`id_education`');
		$query->leftJoin('gender_lang', 'gl', 'gl.`id_gender` = c.`id_gender` AND gl.`id_lang` = ' . $this->context->language->id);
		$query->leftJoin('education_supplies', 'esu', 'esu.`id_education_supplies` = s.`id_education_supplies` ');
		$query->leftJoin('student_education_prerequis', 'sep', 'sep.`id_student_education` = s.`id_student_education` ')->where('s.deleted = 0');

		
		if (is_array($pq_filter) && count($pq_filter)) {
			$hasFilter = true;
			$mode = $pq_filter['mode'];
			$filter = $pq_filter['data'];

			foreach ($filter as $key => $value) {

				if ($value['condition'] == 'equal') {
					$operator = ' = ' . $value['value'];
				} else
				if ($value['condition'] == 'contain') {
					$operator = ' LIKE \'%' . $value['value'] . '%\'';
				} else
				if ($value['condition'] == 'begin') {
					$operator = ' LIKE \'' . $value['value'] . '%\'';
				}

				if (in_array($value['dataIndx'], $customerArray)) {
					$tag = 'c.' . $value['dataIndx'];
				} else
				if (in_array($value['dataIndx'], $addressArray)) {
					$tag = 'a.' . $value['dataIndx'];
				} else
				if ($value['dataIndx'] == 'educationPlatform') {
					$tag = 'e.id_platform';
				} else {
					$tag = 's.' . $value['dataIndx'];
				}

				$query->where($tag . $operator);
			}

		} else {

			if ($pq_curpage > 1) {
				$query->limit($pq_rpp, $pq_rpp * ($pq_curpage - 1));
			} else {
				$query->limit($pq_rpp);
			}

		}

		if (is_array($pq_sort) && count($pq_sort)) {

			foreach ($pq_sort as $key => $value) {

				if ($value['dir'] == 'up') {
					$arg = 'ASC';
				} else {
					$arg = 'DESC';
				}
				if (in_array($value['dataIndx'], $customerArray)) {
					$tag = 'c.' . $value['dataIndx'];
				} else {
					$tag = 's.' . $value['dataIndx'];
				}

				$query->orderBy($tag . ' ' . $arg);
			}

		} else {
			$query->orderBy('s.`id_education_session` DESC');
		}

		$students = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);

		if ($hasFilter && is_array($students) && count($students)) {
			$nbRecords = count($students);

			if ($pq_curpage > 1) {
				$query->limit($pq_rpp, $pq_rpp * ($pq_curpage - 1));
			} else {
				$query->limit($pq_rpp);
			}

			$students = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);

		}

		fwrite($file, $query);

		foreach ($students as &$student) {

			$educations = Education::getEducationDetails($student['id_education'], $student['id_education_attribute'], false);

			foreach ($educations as $key => $value) {

				$student[$key] = $value;
			}
			
			
			$student['FinalPrice'] = $student['price'] * (1 + $student['rate'] / 100);
			$student['agent'] = '';

			if ($student['id_sale_agent'] > 0) {
				$agent = SaleAgent::getSaleAgentbyId($student['id_sale_agent']);
				$student['agent'] = $agent['firstname'] . ' ' . $agent['lastname'];
			}

			$student['connexionLenght'] = (int) str_replace(":", "", $student['education_lenghts']) > 0 ? 1 : 0;
			$time = strtotime($student['session_date']);

			if ($student['certification']) {
				$student['certification'] = '<div class="p-active" onClick="uncheckCertification(' . $student['id_student_education'] . ')"><i class="fa fa-check" aria-hidden="true" style="color:green;"></i></div>';
			} else {
				$student['certification'] = '<div class="p-inactive" onClick="checkCertification(' . $student['id_student_education'] . ')"><i class="fa fa-times" aria-hidden="true" style="color:red;"></i></div>';
			}

			if ($student['doc_return']) {
				$student['docReturn'] = 1;
				$student['doc_return'] = '<div><i class="icon icon-check" aria-hidden="true" style="color:green;"></i></div>';
			} else {
				$student['docReturn'] = 0;
				$student['doc_return'] = '<div><i class="icon icon-times" aria-hidden="true" style="color:red;"></i></div>';
			}

			$lenght = explode(":", $student['education_lenghts']);
			$time = Tools::convertTimetoHex($lenght[0], $lenght[1]);

			if ($student['hours'] > 0) {
				$student['ratio'] = round($time * 100 / $student['hours'], 2) . ' %';
			} else {
				$student['ratio'] = '0 %';
			}

			if (!Validate::isUnsignedId($student['id_student_education_prerequis'])) {
				$student['id_student_education_prerequis'] = 0;
			}

		}

		return [
			'data'         => $students,
			'curPage'      => $pq_curpage,
			'totalRecords' => $nbRecords,
			'hasFilter'    => $hasFilter,
		];

	}

	public function ajaxProcessgetStudentEducationRequest() {

		
		$needInstance = Tools::getValue('needInstance');
		$pq_data = Tools::getValue('pq_data');
		$pq_data = Tools::jsonDecode($pq_data, true);
		$totalRecords = Tools::getValue('totalRecords');
		$pq_curpage = Tools::getValue('pq_curpage');

		if ($needInstance) {

			$pq_rpp = Tools::getValue('pq_rpp');
			$pq_filter = Tools::getValue('pq_filter');
			$pq_filter = Tools::jsonDecode($pq_filter, true);
			$pq_sort = Tools::getValue('pq_sort');
			$pq_sort = Tools::jsonDecode($pq_sort, true);
			header("Content-type: application/json");

			die(Tools::jsonEncode($this->getStudentEducationRequest($pq_curpage, $pq_rpp, $pq_filter, $pq_sort)));
		} else {
			return [
				'data'         => $pq_data,
				'curPage'      => $pq_curpage,
				'totalRecords' => $totalRecords,
			];
			die(Tools::jsonEncode($return));
		}

	}

	public function getStudentEducationDatas($id_education_session) {

		$students = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
			(new DbQuery())
				->select('s.*, c.firstname, c.lastname, c.birthname, c.email, gl.name as title, es.name as `dateSession`, es.session_date, esu.name as supplyName, sep.id_student_education_prerequis')
				->from('student_education', 's')
				->leftJoin('customer', 'c', 'c.`id_customer` = s.`id_customer`')
				->leftJoin('address', 'a', 'a.`id_customer` = s.`id_customer`')
				->leftJoin('student_education_state_lang', 'est', 'est.`id_student_education_state` = s.`id_student_education_state` AND est.`id_lang` = ' . $this->context->language->id)
				->leftJoin('education_session', 'es', 'es.`id_education_session` = s.`id_education_session`')
				->leftJoin('gender_lang', 'gl', 'gl.`id_gender` = c.`id_gender` AND gl.`id_lang` = ' . $this->context->language->id)
				->leftJoin('country_lang', 'cl', 'cl.`id_country` = a.`id_country` AND cl.`id_lang` = ' . $this->context->language->id)
				->leftJoin('education_supplies', 'esu', 'esu.`id_education_supplies` = s.`id_education_supplies` ')
				->leftJoin('student_education_prerequis', 'sep', 'sep.`id_student_education` = s.`id_student_education` ')->where('s.deleted = 0')
				->where('s.id_education_session = ' . $id_education_session)
				->where('s.deleted = 0')
				->orderBy('st.lastname')
				->orderBy('s.reference_edof')
		);

		foreach ($students as &$student) {

			$educations = Education::getEducationDetails($student['id_education'], $student['id_education_attribute'], false);
			$id_address = Address::getFirstCustomerAddressId($student['id_customer']);

			if ($id_address > 0) {
				$address = new Address((int) $id_address);
				$student['phone'] = $address->phone;
				$student['phone_mobile'] = $address->phone_mobile;
				$student['address1'] = $address->address1;
				$student['postcode'] = $address->postcode;
				$student['city'] = $address->city;

			}

			foreach ($educations as $key => $value) {
				$student[$key] = $value;
			}

			$platform = new Platform($student['educationPlatform']);

			if ($platform->has_webservice) {
				$student['is_synch'] = 1;
			} else {
				$student['is_synch'] = 0;
			}

			$student['connexionLenght'] = (int) str_replace(":", "", $student['education_lenghts']) > 0 ? 1 : 0;
			$student['FinalPrice'] = round($student['price'] * (1 + $student['rate'] / 100), 2);
			$agent = SaleAgent::getSaleAgentbyId($student['id_sale_agent']);
			$student['agent'] = $agent['firstname'] . ' ' . $agent['lastname'];

		}

		return $students;

	}

	public function getStudentEducationDatasByState($id_education_session, $idState) {

		$students = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
			(new DbQuery())
				->select('s.*, c.firstname, c.lastname, c.birthname, c.email, c.password,  est.`name` as state, gl.name as title,  es.name as `dateSession`, es.session_date, esu.name as supplyName, sep.id_student_education_prerequis')
				->from('student_education', 's')
				->leftJoin('customer', 'c', 'c.`id_customer` = s.`id_customer`')
				->leftJoin('address', 'a', 'a.`id_customer` = s.`id_customer`')
				->leftJoin('student_education_state_lang', 'est', 'est.`id_student_education_state` = s.`id_student_education_state` AND est.`id_lang` = ' . $this->context->language->id)
				->leftJoin('education_session', 'es', 'es.`id_education_session` = s.`id_education_session`')
				->leftJoin('gender_lang', 'gl', 'gl.`id_gender` = c.`id_gender` AND gl.`id_lang` = ' . $this->context->language->id)
				->leftJoin('country_lang', 'cl', 'cl.`id_country` = a.`id_country` AND cl.`id_lang` = ' . $this->context->language->id)
				->leftJoin('education_supplies', 'esu', 'esu.`id_education_supplies` = s.`id_education_supplies` ')
				->leftJoin('student_education_prerequis', 'sep', 'sep.`id_student_education` = s.`id_student_education` ')->where('s.deleted = 0')
				->where('s.id_education_session = ' . $id_education_session)
				->where('s.id_student_education_state >= ' . $idState)
				->where('s.deleted = 0')
				->orderBy('st.lastname')
				->orderBy('s.reference_edof')
		);

		foreach ($students as &$student) {

			$educations = Education::getEducationDetails($student['id_education'], $student['id_education_attribute'], false);

			foreach ($educations as $key => $value) {
				$student[$key] = $value;
			}

			$id_address = Address::getFirstCustomerAddressId($student['id_customer']);

			if ($id_address > 0) {
				$address = new Address((int) $id_address);
				$student['phone'] = $address->phone;
				$student['phone_mobile'] = $address->phone_mobile;
				$student['address1'] = $address->address1;
				$student['postcode'] = $address->postcode;
				$student['city'] = $address->city;

			}

			$student['connexionLenght'] = (int) str_replace(":", "", $student['education_lenghts']) > 0 ? 1 : 0;
			$student['FinalPrice'] = round($student['price'] * (1 + $student['rate'] / 100), 2);
			$agent = SaleAgent::getSaleAgentbyId($student['id_sale_agent']);
			$student['agent'] = $agent['firstname'] . ' ' . $agent['lastname'];

		}

		return $students;

	}

	public function getStudentEducationDatasById($id_education_session) {

		$datas = [];

		$agents = SaleAgent::getRemuneratadSaleAgents();

		foreach ($agents as $agent) {

			$students = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
				(new DbQuery())
					->select('s.*, c.firstname, c.lastname, c.birthname, c.email,c.password,est.`name` as state, gl.name as title,  es.name as `dateSession`, es.session_date, esu.name as supplyName, sep.id_student_education_prerequis')
					->from('student_education', 's')
					->leftJoin('customer', 'c', 'c.`id_customer` = s.`id_customer`')
					->leftJoin('address', 'a', 'a.`id_customer` = c.`id_customer`')
					->leftJoin('student_education_state_lang', 'est', 'est.`id_student_education_state` = s.`id_student_education_state` AND est.`id_lang` = ' . $this->context->language->id)
					->leftJoin('education_session', 'es', 'es.`id_education_session` = s.`id_education_session`')
					->leftJoin('gender_lang', 'gl', 'gl.`id_gender` = c.`id_gender` AND gl.`id_lang` = ' . $this->context->language->id)
					->leftJoin('country_lang', 'cl', 'cl.`id_country` = a.`id_country` AND cl.`id_lang` = ' . $this->context->language->id)
					->leftJoin('education_supplies', 'esu', 'esu.`id_education_supplies` = s.`id_education_supplies` ')
					->leftJoin('student_education_prerequis', 'sep', 'sep.`id_student_education` = s.`id_student_education` ')->where('s.deleted = 0')
					->leftJoin('sale_agent', 'sa', 'sa.`id_sale_agent` = s.`id_sale_agent` ')
					->where('s.id_education_session = ' . $id_education_session)
					->where('s.id_student_education_state >= 3')
					->where('s.id_sale_agent = ' . $agent['id_sale_agent'])
					->where('s.deleted = 0')
					->orderBy('st.lastname')
			);

			foreach ($students as &$student) {

				$educations = Education::getEducationDetails($student['id_education'], $student['id_education_attribute'], false);

				foreach ($educations as $key => $value) {
					$student[$key] = $value;
				}

				$id_address = Address::getFirstCustomerAddressId($student['id_customer']);

				if ($id_address > 0) {
					$address = new Address((int) $id_address);
					$student['phone'] = $address->phone;
					$student['phone_mobile'] = $address->phone_mobile;
					$student['address1'] = $address->address1;
					$student['postcode'] = $address->postcode;
					$student['city'] = $address->city;

				}
				
				

				$platform = new Platform($student['educationPlatform']);

				if ($platform->has_webservice) {
					$student['is_synch'] = 1;
				} else {
					$student['is_synch'] = 0;
				}

				$student['connexionLenght'] = (int) str_replace(":", "", $student['education_lenghts']) > 0 ? 1 : 0;

				if ((int) str_replace(":", "", $student['education_lenghts']) > 0) {
					$student['commissionDue'] = 1;
				} else {
					$student['commissionDue'] = 0;
				}

				$agent = SaleAgent::getSaleAgentbyId($student['id_sale_agent']);
				$student['agent'] = $agent['firstname'] . ' ' . $agent['lastname'];

			}

			$datas[$agent['id_sale_agent']] = $students;
		}

		return $datas;

	}

	public function getStudentEducationFields() {

		return [
			[
				'title'    => 'ID',
				'maxWidth' => 70,
				'dataIndx' => 'id_student_education',
				'dataType' => 'integer',
				'editable' => false,
				'vdi'      => true,
				'align'    => 'center',
				'valign'   => 'center',
				'session'  => true,
			],
			[
				'title'      => '',

				'dataIndx'   => 'is_edof',
				'dataType'   => 'integer',
				'align'      => 'center',
				'hiddenable' => 'no',
				'hidden'     => true,

			],
			[
				'title'      => $this->l('Réference EDOF'),

				'dataIndx'   => 'reference_edof',
				'maxWidth'   => 150,
				'align'      => 'right',
				'valign'     => 'center',
				'align'      => 'left',
				'session'    => true,
				'dataType'   => 'string',
				'editable'   => true,
				'hiddenable' => 'no',
				'filter'     => [
					'crules' => [['condition' => "contain"]],
				],

			],
			[
				'title'      => $this->l('Contr. matériel'),

				'maxWidth'   => 100,
				'dataIndx'   => 'doc_return',
				'align'      => 'right',
				'valign'     => 'center',
				'align'      => 'center',
				'session'    => true,
				'dataType'   => 'html',
				'editable'   => true,
				'hiddenable' => 'no',
				'sortable' => false

			],
			[
				'title'      => '',

				'dataIndx'   => 'is_synch',
				'dataType'   => 'integer',
				'align'      => 'center',
				'hiddenable' => 'no',
				'hidden'     => true,

			],
			[
				'title'      => '',

				'dataIndx'   => 'id_platform',
				'dataType'   => 'integer',
				'align'      => 'center',
				'hiddenable' => 'no',
				'hidden'     => true,

			],
			[
				'title'      => 'eval hot',

				'dataIndx'   => 'docReturn',
				'dataType'   => 'integer',
				'align'      => 'center',
				'hiddenable' => 'no',
				'hidden'     => true,

			],
			[
				'title'      => 'eval hot',

				'dataIndx'   => 'eval_hot',
				'dataType'   => 'integer',
				'align'      => 'center',
				'hiddenable' => 'no',
				'hidden'     => true,

			],
			[
				'title'      => 'education type',

				'dataIndx'   => 'educationPlatform',
				'dataType'   => 'integer',
				'align'      => 'center',
				'hiddenable' => 'no',
				'hidden'     => true,

			],
			[
				'title'      => 'prerequis',

				'dataIndx'   => 'id_student_education_prerequis',
				'dataType'   => 'integer',
				'align'      => 'center',
				'hiddenable' => 'no',
				'hidden'     => true,

			],

			[
				'title'      => 'id customer',

				'dataIndx'   => 'id_customer',
				'dataType'   => 'integer',
				'align'      => 'center',
				'hiddenable' => 'no',
				'hidden'     => true,

			],
			[
				'title'      => 'publiPost',

				'dataIndx'   => 'publiPost',
				'dataType'   => 'integer',
				'align'      => 'center',
				'hiddenable' => 'no',
				'hidden'     => true,

			],

			[
				'title'      => 'id educ session',
				'dataIndx'   => 'id_education_session',
				'dataType'   => 'integer',
				'hidden'     => true,
				'hiddenable' => 'no',
			],
			[
				'title'      => 'id sale agent',
				'dataIndx'   => 'id_sale_agent',
				'dataType'   => 'integer',
				'hidden'     => true,
				'hiddenable' => 'no',
			],
			[
				'title'      => 'connexion Lenght',
				'dataIndx'   => 'connexionLenght',
				'dataType'   => 'integer',
				'hidden'     => true,
				'hiddenable' => 'no',
			],
			[
				'title'    => $this->l('Agent Commerciale'),
				'maxWidth' => 150,
				'dataIndx' => 'agent',
				'valign'   => 'center',
				'dataType' => 'string',
				'session'  => true,
				'vdi'      => true,
				'align'    => 'left',
				'editable' => false,
				'sortable' => false

			],
			[
				'title'    => $this->l('Session'),
				'minWidth' => 150,
				'dataIndx' => 'dateSession',
				'valign'   => 'center',
				'dataType' => 'string',
				'hidden'   => false,
				'vdi'      => true,
				'align'    => 'left',

				// Très bonne idée mais les utilisateurs trouve ça dangeureux : 
				// Dixit "Gros risque d'erreurs"
				// 'render' => 'checkEducationStepModifiable',
				// 'editor' => 'checkEducationStep',
				'editable' => false,

				'sortable' => false


			],
			[
				'title'      => $this->l('Date de démarrage'),

				'minWidth'   => 150,
				'dataIndx'   => 'date_start',
				'align'      => 'center',
				'valign'     => 'center',
				'cls'        => 'rangeDate',
				'session'    => true,
				'expedition' => true,
				'dataType'   => 'date',
				'format'     => 'dd/mm/yy',
				'render' => 'checkEducationStepModifiable',
				'editor' => 'checkEducationStep',
				'sortable' => false
				

			],
			[
				'title'    => $this->l('Date de fin'),

				'maxWidth' => 200,
				'dataIndx' => 'date_end',
				'align'    => 'center',
				'valign'   => 'center',
				'cls'      => 'rangeDate',
				'dataType' => 'date',
				'format'   => 'dd/mm/yy',
				'editable' => false,
				'hidden'   => true,
				'sortable' => false

			],
			[
				'title'      => 'id session state',
				'dataIndx'   => 'id_student_education_state',
				'dataType'   => 'integer',
				'hidden'     => true,
				'hiddenable' => 'no',
			],
			[
				'title'      => $this->l('Etat'),
				'maxWidth'   => 300,
				'dataIndx'   => 'state',
				'dataType'   => 'string',
				'valign'     => 'center',
				'expedition' => true,
				'align'      => 'left',
				'session'    => true,
				'editable'   => false,
				'sortable' => false

			],

			[
				'title'      => $this->l('Social title'),
				'width'      => 100,
				'dataIndx'   => 'title',
				'align'      => 'center',
				'valign'     => 'center',
				'dataType'   => 'string',
				'editable'   => false,
				'hidden'     => true,
				'expedition' => true,
				'caliopi'    => true,
				'sortable' => false

			],
			[
				'title'      => $this->l('First Name'),
				'maxWidth'   => 150,
				'exWidth'    => 25,
				'dataIndx'   => 'firstname',
				'align'      => 'left',
				'valign'     => 'center',
				'session'    => true,
				'vdi'        => true,
				'expedition' => true,
				'editable'   => false,
				'dataType'   => 'string',
				'filter'     => [
					'crules' => [['condition' => "contain"]],
				],
				'caliopi'    => true,
			],
			[
				'title'      => $this->l('Last Name'),
				'maxWidth'   => 150,
				'exWidth'    => 25,
				'dataIndx'   => 'lastname',
				'session'    => true,
				'vdi'        => true,
				'expedition' => true,
				'dataType'   => 'string',
				'valign'     => 'center',
				'editable'   => false,
				'filter'     => [
					'crules' => [['condition' => "contain"]],
				],
				'caliopi'    => true,

			],
			[
				'title'      => $this->l('Nom de naissance'),
				'width'      => 100,
				'exWidth'    => 25,
				'dataIndx'   => 'birthname',
				'dataType'   => 'string',
				'expedition' => true,
				'valign'     => 'center',
				'hidden'     => true,
				'filter'     => [
					'crules' => [['condition' => "contain"]],
				],
				'editable'   => false,

			],

			[
				'title'    => $this->l('Email address'),
				'width'    => 150,
				'cls'      => 'jsCopyClipBoard',
				'dataIndx' => 'email',
				'dataType' => 'string',
				'valign'   => 'center',
				'editable' => false,
				'vdi'      => true,
				'hidden'   => true,
				'session'  => true,
				'filter'   => [
					'crules' => [['condition' => "contain"]],
				],

			],
			[
				'title'    => $this->l('Mot de passe'),
				'width'    => 150,
				'dataIndx' => 'password',
				'dataType' => 'string',
				'valign'   => 'center',
				'editable' => false,
				'hidden'   => true,

			],

			[
				'title'      => $this->l('Address'),
				'width'      => 150,
				'dataIndx'   => 'address1',
				'align'      => 'left',
				'valign'     => 'center',
				'dataType'   => 'string',
				'editable'   => false,
				'session'    => true,
				'expedition' => true,
				'hidden'     => true,
				'sortable' => false,
				'filter'     => [
					'crules' => [['condition' => "contain"]],
				],
			],
			
			[
				'title'      => $this->l('Post Code'),
				'width'      => 150,
				'dataIndx'   => 'postcode',
				'align'      => 'left',
				'valign'     => 'center',
				'dataType'   => 'string',
				'editable'   => false,
				'session'    => true,
				'expedition' => true,
				'expedition' => true,
				'hidden'     => true,
				'filter'     => [
					'crules' => [['condition' => "begin"]],
				],
				'caliopi'    => true,
				'sortable' => false
			],
			[
				'title'      => $this->l('City'),
				'width'      => 150,
				'dataIndx'   => 'city',
				'valign'     => 'center',
				'align'      => 'left',
				'dataType'   => 'string',
				'editable'   => false,
				'hidden'     => true,
				'session'    => true,
				'expedition' => true,
				'sortable' => false,
				'filter'     => [
					'crules' => [['condition' => "contain"]],
				],
			],
			[
				'title'    => $this->l('Phone'),
				'width'    => 150,
				'cls'      => 'jsCopyClipBoard',
				'dataIndx' => 'phone',
				'valign'   => 'center',
				'align'    => 'left',
				'dataType' => 'string',
				'editable' => false,

				'hidden'   => true,
				
				'sortable' => false,
				'filter'     => [
					'crules' => [['condition' => "contain"]],
				],
			],
			[
				'title'      => $this->l('Mobile Phone'),
				'width'      => 150,
				'dataIndx'   => 'phone_mobile',
				'valign'     => 'center',
				'align'      => 'left',
				'cls'        => 'jsCopyClipBoard',
				'dataType'   => 'string',
				'editable'   => false,
				'session'    => true,
				'expedition' => true,
				'hidden'     => true,
				
				'sortable' => false,
				'filter'     => [
					'crules' => [['condition' => "contain"]],
				],
			],
			[
				'title'    => $this->l('Formation'),
				'width'    => 150,
				'dataIndx' => 'name',
				'align'    => 'left',
				'valign'   => 'center',
				'session'  => true,
				'vdi'      => true,
				'dataType' => 'string',
				'editable' => false,
				'hidden'   => false,
				'filter'   => [
					'crules' => [['condition' => "contain"]],
				],
				'caliopi'  => true,
			],

			[
				'title'        => $this->l('Montant HT'),

				'dataIndx'     => 'price',
				'align'        => 'right',
				'valign'       => 'center',
				'dataType'     => 'float',
				'numberFormat' => '#,##0.00_-"€"',
				'editable'     => false,
				'format'       => "#.###,00 € ",
				'updatable'    => false,
				'session'      => true,
				'summary'      => [
					'type' => 'sum',
				],
				'sortable' => false
			],
			[
				'title'        => $this->l('Montant TTC'),

				'dataIndx'     => 'FinalPrice',
				'align'        => 'right',
				'valign'       => 'center',
				'dataType'     => 'float',
				'numberFormat' => '#,##0.00_-"€"',
				'editable'     => false,
				'format'       => "#.###,00 € ",
				'updatable'    => false,
				'hidden'       => true,
				'sortable' => false

			],

			[
				'title'    => $this->l('Identifiant de connexion'),

				'dataIndx' => 'identifiant',
				'align'    => 'right',
				'valign'   => 'center',
				'session'  => false,
				'align'    => 'left',
				'dataType' => 'string',
				'editable' => false,
				'hidden'   => true,
				'sortable' => false
			],
			[
				'title'    => $this->l('Mot de passe de connexion'),

				'dataIndx' => 'passwd_link',
				'align'    => 'right',
				'valign'   => 'center',
				'align'    => 'left',
				'session'  => false,
				'dataType' => 'string',
				'editable' => false,
				'hidden'   => true,
				'sortable' => false
			],
			[
				'title'    => $this->l('N° tracking'),

				'dataIndx' => 'shipping_number',
				'align'    => 'right',
				'valign'   => 'center',
				'align'    => 'left',
				'session'  => false,
				'dataType' => 'string',
				'editable' => true,
				'hidden'   => false,
				'sortable' => false
			],
			[
				'title'       => $this->l('Première connexion'),

				'dataIndx'    => 'first_connection',
				'minWidth'    => 150,
				'align'       => 'center',
				'valign'      => 'center',
				'dataType'    => 'date',
				'format'      => 'dd/mm/yy',
				'session'     => false,
				'vdi'         => true,
				'editable'    => true,
				'hidden'      => false,
				'cls'         => 'pq-calendar pq-side-icon',
				'editor'      => [
					'type'    => "textbox",
					'init'    => 'dateEditor',
					'getData' => 'getDataDate',
				],
				
				'sortable' => false,
				'render' 	=> 'renderDateGrid'
			],
			[
				'title'    => $this->l('Durée d‘apprentissage'),
				'dataIndx' => 'education_lenghts',
				'align'    => 'center',
				'valign'   => 'center',
				'dataType' => 'html',
				'session'  => false,
				'vdi'      => true,
				'editable' => true,
				'hidden'   => true,

				'caliopi'  => true,
				'sortable' => false
			],
			[
				'title'    => $this->l('Pourcentage Accomplis'),

				'dataIndx' => 'ratio',
				'align'    => 'center',
				'valign'   => 'center',
				'dataType' => 'string',
				//'format'       => "#.###,00 %",
				'session'  => false,
				'editable' => false,

				'caliopi'  => true,
				'sortable' => false
			],

			[
				'title'      => $this->l('Matériel remis'),
				'dataIndx'   => 'supplyName',
				'align'      => 'right',
				'valign'     => 'center',
				'expedition' => true,
				'align'      => 'left',
				'dataType'   => 'string',
				'editable'   => false,
				'sortable' => false

			],
			[
				'title'      => $this->l('Notes'),
				'dataIndx'   => 'notes',
				'align'      => 'left',
				'valign'     => 'center',
				'expedition' => true,
				'align'      => 'left',
				'dataType'   => 'string',
				'editable'   => true,
				'sortable' => false

			],
			[

				'title'    => $this->l('Certification'),
				'dataIndx' => 'certificationName',
				'align'    => 'right',
				'valign'   => 'center',
				'align'    => 'left',
				'dataType' => 'html',
				'session'  => false,
				'hidden'   => true,
				'caliopi'  => true,
				'sortable' => false
			],
			[

				'title'    => $this->l('Certification'),
				'dataIndx' => 'certification',
				'align'    => 'right',
				'valign'   => 'center',
				'align'    => 'left',
				'dataType' => 'html',
				'session'  => false,
				'hidden'   => true,
				'caliopi'  => true,
				'sortable' => false
			],

		];

	}

	public function ajaxProcessgetDateStartDropDown() {

		$dropdownSession = Tools::jsonEncode(EducationSession::getDropDownDateStart());
		die($dropdownSession);
	}

	public function ajaxProcessGetSessionDropDown() {

		$dropdownSession = Tools::jsonEncode(EducationSession::getDropDownEducationSession());
		die($dropdownSession);
	}

	public function ajaxProcessUpdateByVal() {

		$idEducation = (int) Tools::getValue('idEducation');
		$field = Tools::getValue('field');
		$fieldValue = Tools::getValue('fieldValue');

		if ($field == 'first_connection') {
			$date = DateTime::createFromFormat('m/d/Y', $fieldValue);

			$fieldValue = $date->format('Y-m-d');
		}

		$education = new StudentEducation($idEducation);
		$classVars = get_class_vars(get_class($education));

		if (isset($classVars['definition']['fields'])) {
			$fields = $classVars['definition']['fields'];
		}

		if (Validate::isLoadedObject($education)) {

			$education->$field = $fieldValue;
			$result = $education->update();

			if (!isset($result) || !$result) {
				$result = [
					'success' => false,
					'message' => Tools::displayError('An error occurred while updating the field.'),
				];
				$this->errors[] = Tools::displayError('An error occurred while updating the product.');
			} else {
				$result = [
					'success'   => true,
					'education' => $education,
					'message'   => $this->l('Update successful'),
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

	public function ajaxProcessExportExcel() {

		$fields = $this->configurationField;

		$titles = [];
		$dataIndx = [];

		foreach ($fields as $field) {

			if ($field['hiddenable'] == 'no') {
				continue;
			}

			$titles[] = $field['title'];
			$dataIndx[] = $field['dataIndx'];

		}

		$column = chr(64 + count($titles));
		$titleStyle = [
			'font'    => [
				'bold' => true,
			],
			'borders' => [
				'bottom' => [
					'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
				],
			],
		];
		$corpStyle = [
			'font' => [
				'bold' => false,
			],

		];

		$spreadsheet = new Spreadsheet();

		$sessions = StudentEducation::getFilledSession();
		$spreadsheet->getActiveSheet()->getPageSetup()->setFitToWidth(1);
		$educationSession = new EducationSession($sessions[0]['id_education_session']);
		$spreadsheet->getActiveSheet()->setTitle($educationSession->name);

		foreach ($titles as $key => $value) {
			$key++;
			$letter = chr(64 + $key);

			$spreadsheet->setActiveSheetIndex(0)
				->setCellValue($letter . '1', $value);

		}

		$spreadsheet->getActiveSheet()->getStyle('A1:' . $column . '1')->getAlignment()->setVertical(Alignment::HORIZONTAL_CENTER);
		$spreadsheet->getActiveSheet()->getStyle('A1:' . $column . '1')->applyFromArray($titleStyle);
		$spreadsheet->getActiveSheet()->getStyle('A1:' . $column . '1')->getFont()->setSize(12);
		$i = 2;
		$datas = $this->getStudentEducationDatas($educationSession->id);

		foreach ($datas as $educations) {

			foreach ($dataIndx as $k => $title) {

				if (array_key_exists($title, $educations)) {
					$k++;
					$letter = chr(64 + $k);

					switch ($letter) {
					case 'D':
					case 'W':
						$phpdate = strtotime($educations[$title]);
						$mysqldate = date('d/m/Y', $phpdate);
						$value = $mysqldate;
						$spreadsheet->setActiveSheetIndex(0)
							->setCellValue($letter . $i, $value);
						break;
					case 'Q':
					case 'R':
					case 'S':
						$spreadsheet->setActiveSheetIndex(0)
							->setCellValue($letter . $i, $educations[$title]);
						$spreadsheet->getActiveSheet()->getStyle($letter . $i)->getNumberFormat()
							->setFormatCode('#,##0.00');
						break;
					default:
						$spreadsheet->setActiveSheetIndex(0)
							->setCellValue($letter . $i, $educations[$title]);
						break;
					}

					$spreadsheet->getActiveSheet()->getColumnDimension($letter)->setAutoSize(true);
					$spreadsheet->getActiveSheet()->getStyle($letter . $i)->applyFromArray($corpStyle);

				}

			}

			$i++;

		}

		$spreadsheet->getActiveSheet()->setCellValue('P' . ($i + 1), 'Total:')
			->setCellValue('Q' . ($i + 1), '=SUM(Q2:Q' . ($i - 1) . ')')
			->setCellValue('R' . ($i + 1), '=SUM(R2:R' . ($i - 1) . ')')
			->setCellValue('S' . ($i + 1), '=SUM(S2:S' . ($i - 1) . ')');

		unset($sessions[0]);
		$index = 1;

		foreach ($sessions as $session) {

			$educationSession = new EducationSession($session['id_education_session']);
			$clonedSheet = clone $spreadsheet->getActiveSheet();
			$clonedSheet->setTitle($educationSession->name);
			$newSheet = $spreadsheet->createSheet();
			$newSheet->setTitle($educationSession->name);

			foreach ($titles as $key => $value) {
				$key++;
				$letter = chr(64 + $key);

				$spreadsheet->setActiveSheetIndex($index)
					->setCellValue($letter . '1', $value);

			}

			$spreadsheet->getActiveSheet()->getStyle('A1:' . $column . '1')->getAlignment()->setVertical(Alignment::HORIZONTAL_CENTER);
			$spreadsheet->getActiveSheet()->getStyle('A1:' . $column . '1')->applyFromArray($titleStyle);
			$spreadsheet->getActiveSheet()->getStyle('A1:' . $column . '1')->getFont()->setSize(12);
			$i = 2;

			$datas = $this->getStudentEducationDatas($educationSession->id);

			foreach ($datas as $educations) {

				foreach ($dataIndx as $k => $title) {

					if (array_key_exists($title, $educations)) {
						$k++;
						$letter = chr(64 + $k);

						switch ($letter) {
						case 'D':
						case 'W':

							$phpdate = strtotime($educations[$title]);
							$value = date('d/m/Y', $phpdate);

							$spreadsheet->setActiveSheetIndex($index)
								->setCellValue($letter . $i, $value);
							break;
						case 'Q':
						case 'R':
							$spreadsheet->setActiveSheetIndex($index)
								->setCellValue($letter . $i, $educations[$title]);
							$spreadsheet->getActiveSheet()->getStyle($letter . $i)->getNumberFormat()
								->setFormatCode('#,##0.00');
							break;
						case 'S':
							$spreadsheet->setActiveSheetIndex($index)
								->setCellValue($letter . $i, $educations[$title]);
							$spreadsheet->getActiveSheet()->getStyle($letter . $i)->getNumberFormat()
								->setFormatCode('#,##0.00');

							break;
						default:
							$spreadsheet->setActiveSheetIndex($index)
								->setCellValue($letter . $i, $educations[$title]);
							break;
						}

						$spreadsheet->setActiveSheetIndex($index)
							->setCellValue($letter . $i, $educations[$title]);
						$spreadsheet->getActiveSheet()->getColumnDimension($letter)->setAutoSize(true);
					}

				}

				$i++;

			}

			$spreadsheet->getActiveSheet()->setCellValue('P' . ($i + 1), 'Total:')
				->setCellValue('Q' . ($i + 1), '=SUM(Q2:Q' . ($i - 1) . ')')
				->setCellValue('R' . ($i + 1), '=SUM(R2:R' . ($i - 1) . ')')
				->setCellValue('S' . ($i + 1), '=SUM(S2:S' . ($i - 1) . ')');
			$index++;
		}

		$fileSave = new Xlsx($spreadsheet);
		$fileSave->save(_PS_EXPORT_DIR_ . 'exportSessions.xlsx');
		$response = [
			'fileExport' => 'fileExport' . DIRECTORY_SEPARATOR . 'exportSessions.xlsx',
		];
		die(Tools::jsonEncode($response));
	}

	public function ajaxProcessGetimportField() {

		$fieldsImport = [];
		$fields = $this->getStudentEducationFields();

		foreach ($fields as $field) {

			if (isset($field['session']) && $field['session'] == 1) {
				$field['hidden'] = false;
				$fieldsImport[] = $field;
			}

		}

		die(Tools::jsonEncode($fieldsImport));
	}

	public function ajaxProcessgetStudentEducationFields() {

		$fields = EmployeeConfiguration::get('EXPERT_STUDENTEDUCATION_FIELDS', $this->context->employee->id);
		die($fields);
	}

	public function manageFieldsVisibility($fields) {

		return parent::manageFieldsVisibility($fields);
	}

	public function ajaxProcessUpdateVisibility() {

		$headerFields = Tools::jsonDecode(EmployeeConfiguration::get('EXPERT_STUDENTEDUCATION_FIELDS', $this->context->employee->id), true);
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
		EmployeeConfiguration::updateValue('EXPERT_STUDENTEDUCATION_FIELDS', $headerFields, $this->context->employee->id);
		$response = [
			'headerFields' => $headerFields,
		];

		die($headerFields);
	}

	public function ajaxProcessupdateJsonVisibility() {

		$visibility = Tools::getValue('visibilities');
	}

	public function ajaxProcessEditObject() {

		if ($this->tabAccess['edit'] == 1) {   
		$id_student_education = Tools::getValue('idObject');
		$student_education = new StudentEducation($id_student_education);
		$student = new Customer($student_education->id_customer);
		$data = $this->createTemplate('controllers/student_educations/editEducation.tpl');
		$scripHeader = $this->displayBackOfficeHeader = Hook::exec('displayBackOfficeHeader', []);
		$scriptFooter = $this->displayBackOfficeFooter = Hook::exec('displayBackOfficeFooter', []);

		$historySteps = StudentEducationStep::getEducationStepHistory($student_education->id, $this->context->language->id);

		$agent = SaleAgent::getSaleAgentbyId($student_education->id_sale_agent);
		$data->assign('agent', $agent);
		$data->assign('agents', SaleAgent::getSaleAgents());
		$data->assign('education', Student::getStudentEducationById($id_student_education));
		$data->assign('educationSteps', StudentEducationStep::getEducationStep());
		$data->assign('student', $student);
		$data->assign('student_education', $student_education);
		$data->assign('supplies', EducationSupplies::getEducationSupplies());
		$data->assign('historySteps', $historySteps);
		$data->assign('img_dir', __PS_BASE_URI__ . $this->admin_webpath . '/themes/' . $this->bo_theme . '/img/');
		$data->assign('postageFiles', $this->getPubliPostageMaterial($id_student_education));
		$data->assign('controller', $this->controller_name);

		$li = '<li id="uperEdit' . $this->controller_name . '" data-controller="AdminDashboard"><a href="#contentEdit' . $this->controller_name . '">Modifier la formation pour ' . $student->firstname . ' ' . $student->lastname . '</a><button type="button" class="close tabdetail" data-id="uperEdit' . $this->controller_name . '"><i class="icon icon-times-circle"></i></button></li>';
		$html = '<div id="contentEdit' . $this->controller_name . '" class="panel col-lg-12" style="display; flow-root;">' . $data->fetch() . '</div>';

		$result = [
			'success' => true,
			'li'   => $li,
			'html' => $html,
		];
		} else {
            $result = [
				'success' => false,
				'message'   => 'Votre profile administratif ne vous permet pas d‘éditer les sessions de formations',
			];
        }

		die(Tools::jsonEncode($result));
	}

	public function getPubliPostageMaterial($id_student_education) {

		$return = [];

		foreach ($this->pdfTemplate as $key => $template) {
			$file = _PS_PDF_STUDENT_DIR_ . $id_student_education . '_' . $key . '.pdf';

			if (file_exists($file)) {
				$return[] = [
					'template' => $template,
					'link'     => _PDF_STUDENT_DIR_ . $id_student_education . '_' . $key . '.pdf',
				];
			}

		}

		return $return;
	}

	public function ajaxprocessChangeEducationStepId() {

		$idEducation = (int) Tools::getvalue('idEducation');
		$idStep = (int) Tools::getValue('step');

		$step = new StudentEducationStep($idStep, $this->context->language->id);
		$education = new StudentEducation($idEducation);

		if ($education->id_student_education_state != $step->id) {

			if ($step->id == 4) {

				if (empty($education->reference_edof) || empty($education->identifiant) || empty($education->passwd_link)) {
					$result = [
						'success' => false,
						'message' => $this->l('Certain champs obligatoires ne sont pas remplies '),
					];

					die(Tools::jsonEncode($result));
				}

				$id_education_prerequis = $education->id_education_prerequis;

				if (!Validate::isUnsignedId($education->id_education_prerequis)) {

					$id_education_prerequis = Db::getInstance()->getValue(
						(new DbQuery())
							->select('id_education_prerequis')
							->from('education_attribute')
							->where('id_education = ' . $education->id_education)
							->where('id_education_attribute = ' . $education->id_education_attribute)
					);

				}

				$prerequis = new EducationPrerequis($id_education_prerequis);
				$content = $prerequis->content;
				$score = 0;
				$result = [];

				if (is_array($content) && count($content)) {

					$nbQuestions = count($content);

					$delta = $nbQuestions - $prerequis->min_score;

					$match = $prerequis->min_score + rand(1, $delta);

					$rand_keys = array_rand($content, $match);

					foreach ($content as $key => $quesion) {

						if ($quesion['name'] == 'profession') {
							continue;
						}

						if (in_array($key, $rand_keys)) {
							$score = $score + 1;
							$result[$quesion['name']] = 1;
						} else {
							$result[$quesion['name']] = 0;
						}

					}

					$date_add = Db::getInstance()->getValue(
						(new DbQuery())
							->select('`date_add`')
							->from('student_education_suivie')
							->where('id_student_education = ' . $education->id)
							->where('id_student_education_state = 3')
					);

					$studentPrerequis = new StudentEducationPrerequis();
					$studentPrerequis->id_student_education = $education->id;
					$studentPrerequis->id_education_prerequis = $id_education_prerequis;
					$studentPrerequis->content = serialize($result);
					$studentPrerequis->score = $score;
					$studentPrerequis->date_add = $date_add;
					$studentPrerequis->add();
				}

			}

			$education->id_student_education_state = $step->id;
			$education->update();

			if ($step->send_email == 1) {
				$emailContents = $this->sendStudentEmail($education->id, $step);
			}

			if ($step->send_sms == 1) {
				$smsContent = $this->sendStudentSms($education->id, $step);
			}

			if ($step->invoice == 1) {
				$this->generateInvoice($education);
			}

			if ($step->is_suivie == 1) {
				$suivie = new StudentEducationSuivie();
				$suivie->suivie_date = date('Y-m-d');
				$suivie->id_student_education = $education->id;
				$suivie->id_student_education_state = $step->id;

				if (is_array($emailContents) && count($emailContents)) {
					$suivie->email_title = $emailContents['email_title'];
					$suivie->email_content = $emailContents['email_content'];
				}

				if (is_array($smsContent) && count($smsContent)) {
					$suivie->email_title = $smsContent['sms_title'];
					$suivie->email_content = $smsContent['sms_content'];
				}

				$suivie->content = $step->suivie;
				$suivie->add();

			}

		}

		$data = $this->createTemplate('controllers/student_educations/tableStep.tpl');
		$historySteps = StudentEducationStep::getEducationStepHistory($education->id, $this->context->language->id);
		$data->assign('historySteps', $historySteps);
		$result = [
			'success' => true,
			'html'    => $data->fetch(),
			'message' => $this->l('L‘état de la formation a été mise à jour avec succès '),
		];

		die(Tools::jsonEncode($result));
	}

	public function generateInvoice(StudentEducation $studenEducation) {

		$studentPiece = StudentPieces::getPieceIdEducationSession($studenEducation->id);
		$studentPiece->piece_type = "INVOICE";
		$studentPiece->update();
	}

	public function ajaxProcessGenereInvoice() {

		$idEducation = Tools::getValue('idEducation');
		$studentEducation = new StudentEducation($idEducation);
		CustomerPieces::mergeOrderTable($studentEducation->id);
		$studentEducation->id_student_education_state = 9;
		$studentEducation->update();
		$result = [
			'success' => true,
			'message' => $this->l('La formation a été facturée avec succès'),
		];

		die(Tools::jsonEncode($result));
	}

	public function ajaxProcessGenereBulkInvoice() {

		$educations = Tools::getvalue('idEducations');

		foreach ($educations as $idEducation) {
			$studentEducation = new StudentEducation($idEducation);
			CustomerPieces::mergeOrderTable($studentEducation->id);
			$studentEducation->id_student_education_state = 9;
			$studentEducation->update();

		}

		$result = [
			'success' => true,
			'message' => $this->l('Les formations ont été facturées avec succès'),
		];

		die(Tools::jsonEncode($result));
	}

	public function ajaxProcessChangeBulkEducationStepId() {

		$idStep = Tools::getValue('step');
		$step = new StudentEducationStep($idStep, $this->context->language->id);
		$educations = Tools::getvalue('idEducations');

		foreach ($educations as $idEducation) {
			$education = new StudentEducation($idEducation);

			if ($education->id_student_education_state != $step->id) {

				if ($step->id == 4) {

					if (empty($education->reference_edof) || empty($education->identifiant) || empty($education->passwd_link)) {
						continue;
					}

				}

				$education->id_student_education_state = $step->id;
				$education->update();
				$story = new StudentEducationHistory();
				$story->id_student_education = $education->id;
				$story->id_student_education_state = $step->id;
				$story->id_employee = (int) $this->context->employee->id;
				$story->date_add = $education->date_upd;
				$story->add();

				if ($step->send_email == 1) {
					$emailContents = $this->sendStudentEmail($education->id, $step);

				}

				if ($step->send_sms == 1) {
					$smsContent = $this->sendStudentSms($education->id, $step);
				}

				if ($step->invoice == 1) {
					$this->generateInvoice($education);
				}

				if ($step->is_suivie == 1) {
					$suivie = new StudentEducationSuivie();
					$suivie->suivie_date = date('Y-m-d');
					$suivie->id_student_education = $education->id;
					$suivie->id_student_education_state = $step->id;

					if (is_array($emailContents) && count($emailContents)) {
						$suivie->email_title = $emailContents['email_title'];
						$suivie->email_content = $emailContents['email_content'];
					}

					if (is_array($smsContent) && count($smsContent)) {
						$suivie->email_title = $smsContent['sms_title'];
						$suivie->email_content = $smsContent['sms_content'];
					}

					$suivie->content = $step->suivie;
					$suivie->add();

				}

			}

		}

		$result = [
			'success' => true,
			'message' => $this->l('L\'état des formations ont été mise à jour avec succès '),
		];

		die(Tools::jsonEncode($result));

	}

	public function sendStudentEmail($idEducation, StudentEducationStep $step) {

		$studentEducation = new StudentEducation($idEducation);
		$student = new Student($studentEducation->id_student);
		$customer = new Customer($studentEducation->id_customer);

		if (!empty($step->template)) {
			$topic = $step->description;
			$education = new Education($studentEducation->id_education);
			$date_start = $studentEducation->date_start;
			$fileAttachement = null;

			$attachement = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
				(new DbQuery())
					->select('fileName')
					->from('education_programme')
					->where('`id_education` = ' . (int) $studentEducation->id_education)
					->where('`id_education_attribute` = ' . (int) $studentEducation->id_education_attribute)
			);

			if ($attachement != '') {
				$fileName = _PS_PROGRAM_DIR_ . $attachement;
				$fileAttachement[] = [
					'content' => chunk_split(base64_encode(file_get_contents($fileName))),
					'name'    => $attachement,
				];
			}

			$secret_iv = _COOKIE_KEY_;
			$secret_key = _PHP_ENCRYPTION_KEY_;
			$string = $customer->id . '-' . $customer->lastname . $customer->passwd;
			$crypto_key = Tools::encrypt_decrypt('encrypt', $string, $secret_key, $secret_iv);
			$linkContract = Context::getContext()->link->getPageLink('index', true, Context::getContext()->language->id, ['crypto_key' => $crypto_key], false, 1) . '&submitContract&idStudentEducation=' . $studentEducation->id;

			$tpl = $this->context->smarty->createTemplate(_PS_MAIL_DIR_ . '/fr/' . $step->template . '.tpl');
			$tpl->assign([
				'student'          => $customer,
				'studentEducation' => $studentEducation,
				'linkContract'     => $linkContract,
				'is_video_tuto'    => Configuration::get('EPH_ALLOW_VIDEO_TUTO'),
				'tutoVideo'        => Configuration::get('EPH_TUTO_VIDEO'),

			]);

			$postfields = [
				'sender'      => [
					'name'  => "Service  Administratif " . Configuration::get('PS_SHOP_NAME'),
					'email' => 'no-reply@' . Configuration::get('PS_SHOP_URL'),
				],
				'to'          => [
					[
						'name'  => $customer->firstname . ' ' . $customer->lastname,
						'email' => $customer->email,
					],
				],

				'subject'     => $step->description,
				"htmlContent" => $tpl->fetch(),
				'attachment'  => $fileAttachement,
			];

			$result = Tools::sendEmail($postfields);

			return [
				'email_title'   => $step->description,
				'email_content' => $tpl->fetch(),
			];
		}

	}

	public function ajaxProcessResendEducationLink() {

		$idSession = Tools::getValue('idSession');
		$studentEducation = new StudentEducation($idSession);
		$student = new Student($studentEducation->id_student);
		$customer = new Customer($studentEducation->id_customer);
		$tpl = $this->context->smarty->createTemplate(_PS_MAIL_DIR_ . '/fr/registrationReminder.tpl');

		$tpl->assign([
			'student'          => $customer,
			'studentEducation' => $studentEducation,
			'is_video_tuto'    => Configuration::get('EPH_ALLOW_VIDEO_TUTO'),
			'tutoVideo'        => Configuration::get('EPH_TUTO_VIDEO'),
		]);

		$postfields = [
			'sender'      => [
				'name'  => "Service  Administratif " . Configuration::get('PS_SHOP_NAME'),
				'email' => 'no-reply@' . Configuration::get('PS_SHOP_URL'),
			],
			'to'          => [
				[
					'name'  => $customer->firstname . ' ' . $customer->lastname,
					'email' => $customer->email,
				],
			],

			'subject'     => 'Rappel concernant votre dossier d’inscription pour votre formation "' . $studentEducation->name . '"',
			"htmlContent" => $tpl->fetch(),
		];

		$result = Tools::sendEmail($postfields);

		$suivie = new StudentEducationSuivie();
		$suivie->suivie_date = date('Y-m-d');
		$suivie->id_employee = Context::getContext()->employee->id;
		$suivie->id_student_education = $studentEducation->id;
		$suivie->id_student_education_state = $studentEducation->id_student_education_state;
		$suivie->email_title = 'Rappel concernant votre dossier d’inscription pour votre formation "' . $studentEducation->name . '"';
		$suivie->email_content = $tpl->fetch();
		$suivie->content = 'Rappel concernant votre dossier d’inscription pour votre formation "' . $studentEducation->name . '"';
		$suivie->add();

		$return = [
			'success' => true,
			'message' => $this->l('Le lien EDOF a été ré envoyé avec succès'),
		];
		die(Tools::jsonEncode($return));

	}

	public function ajaxProcessResendMaterialLink() {

		$idSession = Tools::getValue('idSession');
		$studentEducation = new StudentEducation($idSession);
		$customer = new Customer($studentEducation->id_customer);
		$tpl = $this->context->smarty->createTemplate(_PS_MAIL_DIR_ . '/fr/inscription.tpl');

		$secret_iv = _COOKIE_KEY_;
		$secret_key = _PHP_ENCRYPTION_KEY_;
		$string = $customer->id . '-' . $customer->lastname . $customer->passwd;
		$crypto_key = Tools::encrypt_decrypt('encrypt', $string, $secret_key, $secret_iv);
		$linkContract = Context::getContext()->link->getPageLink('index', true, Context::getContext()->language->id, ['crypto_key' => $crypto_key], false, 1) . '&submitContract&idStudentEducation=' . $studentEducation->id;

		$tpl->assign([
			'student'          => $customer,
			'studentEducation' => $studentEducation,
			'linkContract'     => $linkContract,
		]);

		$postfields = [
			'sender'      => [
				'name'  => "Service  Administratif " . Configuration::get('PS_SHOP_NAME'),
				'email' => 'no-reply@' . Configuration::get('PS_SHOP_URL'),
			],
			'to'          => [
				[
					'name'  => $customer->firstname . ' ' . $customer->lastname,
					'email' => $customer->email,
				],
			],

			'subject'     => 'Votre Inscription est validée',
			"htmlContent" => $tpl->fetch(),
		];

		$result = Tools::sendEmail($postfields);

		$suivie = new StudentEducationSuivie();
		$suivie->suivie_date = date('Y-m-d');
		$suivie->id_employee = Context::getContext()->employee->id;
		$suivie->id_student_education = $studentEducation->id;
		$suivie->id_student_education_state = $studentEducation->id_student_education_state;
		$suivie->email_title = 'Renvoie du mail Votre Inscription est validée';
		$suivie->email_content = $tpl->fetch();
		$suivie->content = 'Renvoie du mail Votre Inscription est validée';
		$suivie->add();

		$return = [
			'success' => true,
			'message' => $this->l('Le lien EDOF a été ré envoyé avec succès'),
		];
		die(Tools::jsonEncode($return));

	}

	public function sendStudentSms($idEducation, StudentEducationStep $step) {

		if (file_exists(_PS_SMS_DIR_ . $step->sms_template . '.tpl')) {
			$studentEducation = new StudentEducation($idEducation);
			$customer = new Customer($studentEducation->id_customer);
			$this->context->smarty->assign(
				[
					'student'          => $customer,
					'studentEducation' => $studentEducation,
				]
			);

			$content = $this->context->smarty->fetch(_PS_SMS_DIR_ . $step->sms_template . '.tpl');

			$recipient = $customer->phone_mobile;
			Tools::sendSms($recipient, $content);
			return [
				'sms_title'   => $step->description,
				'sms_content' => $content,
			];
		}

	}

	public function ajaxProcessDeleteStudentEducation() {

		$id_student_education = Tools::getValue('id_student_education');
		$education = new StudentEducation($id_student_education);
		$education->delete();
		$return = [
			'success' => true,
			'message' => $this->l('La session de formation a été supprimée avec succès'),
		];
		die(Tools::jsonEncode($return));
	}

	public static function ajaxProcessgetAutoCompleteSession() {

		$query = Tools::getValue('search');

		$sessions = Db::getInstance()->executeS(
			(new DbQuery())
				->select('id_education_session, session_date, name')
				->from('education_session')
				->where('name LIKE \'%' . pSQL($query) . '%\'')
		);
		die(Tools::jsonEncode($sessions));
	}

	public static function ajaxProcessGetAutoCompleteSaleAgent() {

		$context = Context::getContext();
		$results = [];
		$query = Tools::getValue('search');
		$request = Db::getInstance()->executeS(
			(new DbQuery())
				->select('`id_sale_agent`, `firstname`, `lastname`')
				->from('sale_agent')
				->where('`firstname` LIKE \'%' . pSQL($query) . '%\' OR `lastname` LIKE \'%' . pSQL($query) . '%\'')
		);
		die(Tools::jsonEncode($request));
	}

	public static function ajaxProcessGetAutoCompleteEducation() {

		$context = Context::getContext();
		$results = [];
		$query = Tools::getValue('search');
		$sql = 'SELECT p.`id_education`, pl.`link_rewrite`, p.`reference`, p.`id_formatpack`, pl.`name`, image_education.`id_image_education` id_image, il.`legend`, p.`cache_default_attribute`
		FROM `' . _DB_PREFIX_ . 'education` p
		LEFT JOIN `' . _DB_PREFIX_ . 'education_lang` pl ON (pl.id_education = p.id_education AND pl.id_lang = ' . (int) $context->language->id . ')
		LEFT JOIN `' . _DB_PREFIX_ . 'image_education` image_education
			ON (image_education.`id_education` = p.`id_education` AND image_education.cover=1)
		LEFT JOIN `' . _DB_PREFIX_ . 'image_education_lang` il ON (image_education.`id_image_education` = il.`id_image_education` AND il.`id_lang` = ' . (int) $context->language->id . ')
		WHERE (pl.name LIKE \'%' . pSQL($query) . '%\' OR p.reference LIKE \'%' . pSQL($query) . '%\')' .
			' GROUP BY p.id_education';

		$items = Db::getInstance()->executeS($sql);

		if ($items) {

			foreach ($items as $item) {

				if ($item['cache_default_attribute']) {
					$sql = 'SELECT pa.`id_education_attribute`, pa.`reference`, pa..`id_formatpack`, ag.`id_attribute_group`, pai.`id_image`, agl.`name` AS group_name, al.`name` AS attribute_name,
						a.`id_attribute`
					FROM `' . _DB_PREFIX_ . 'education_attribute` pa
					' . Shop::addSqlAssociation('education_attribute', 'pa') . '
					LEFT JOIN `' . _DB_PREFIX_ . 'education_attribute_combination` pac ON pac.`id_education_attribute` = pa.`id_education_attribute`
					LEFT JOIN `' . _DB_PREFIX_ . 'attribute` a ON a.`id_attribute` = pac.`id_attribute`
					LEFT JOIN `' . _DB_PREFIX_ . 'attribute_group` ag ON ag.`id_attribute_group` = a.`id_attribute_group`
					LEFT JOIN `' . _DB_PREFIX_ . 'attribute_lang` al ON (a.`id_attribute` = al.`id_attribute` AND al.`id_lang` = ' . (int) $context->language->id . ')
					LEFT JOIN `' . _DB_PREFIX_ . 'attribute_group_lang` agl ON (ag.`id_attribute_group` = agl.`id_attribute_group` AND agl.`id_lang` = ' . (int) $context->language->id . ')
					LEFT JOIN `' . _DB_PREFIX_ . 'education_attribute_image` pai ON pai.`id_education_attribute` = pa.`id_education_attribute`
					WHERE pa.`id_education` = ' . (int) $item['id_education'] . '
					GROUP BY pa.`id_education_attribute`, ag.`id_attribute_group`
					ORDER BY pa.`id_education_attribute`';

					$combinations = Db::getInstance()->executeS($sql);

					if (!empty($combinations)) {

						foreach ($combinations as $k => $combination) {
							$results[$combination['id_education_attribute']]['id_education'] = $item['id_education'];
							$results[$combination['id_education_attribute']]['id_education_attribute'] = $combination['id_education_attribute'];
							!empty($results[$combination['id_education_attribute']]['name']) ? $results[$combination['id_education_attribute']]['name'] .= ' ' . $combination['group_name'] . '-' . $combination['attribute_name']
							: $results[$combination['id_education_attribute']]['name'] = $item['name'] . ' ' . $combination['group_name'] . '-' . $combination['attribute_name'];

							if (!empty($combination['reference'])) {
								$results[$combination['id_education_attribute']]['ref'] = $combination['reference'];
							} else {
								$results[$combination['id_education_attribute']]['ref'] = !empty($item['reference']) ? $item['reference'] : '';
							}

							$results[$combination['id_education_attribute']]['id_formatpack'] = $item['id_formatpack'];

						}

					} else {
						$education = [
							'id_education'           => (int) ($item['id_education']),
							'id_education_attribute' => 0,
							'name'                   => $item['name'],
							'ref'                    => (!empty($item['reference']) ? $item['reference'] : ''),
							'id_formatpack'          => $item['id_formatpack'],

						];
					}

				} else {
					$education = [
						'id_education'           => (int) ($item['id_education']),
						'id_education_attribute' => 0,
						'name'                   => $item['name'],
						'ref'                    => (!empty($item['reference']) ? $item['reference'] : ''),
						'id_formatpack'          => $item['id_formatpack'],
					];
					array_push($results, $education);
				}

			}

			$results = array_values($results);
		}

		die(Tools::jsonEncode($results));

	}

	public function ajaxProcessGetEducationDetails() {

		$id_education = Tools::getValue('id_education');
		$id_education_attribute = Tools::getValue('id_education_attribute');

		$education = Education::getEducationDetails($id_education, $id_education_attribute);

		die(Tools::jsonEncode(['education' => $education]));
	}

	public function beforeUpdate($education) {

		if ($education->id_education_session > 0) {
			$days = Education::getDaysEducation($education->id_education, $education->id_education_attribute);
			$session = new EducationSession($education->id_education_session);
			$date = new DateTime($session->session_date);
			$date->modify('+' . $days . ' days');
			$education->date_start = $session->session_date;
			$education->date_end = $date->format('Y-m-d');
		}

	}

	public function ajaxProcessReserveEducation() {

		$education = new StudentEducation();

		$customer = new Customer(Tools::getValue('id_customer'));

		foreach ($_POST as $key => $value) {

			if (property_exists($education, $key) && $key != 'id_student_education') {

				if (Tools::getValue('id_student_education') && empty($value)) {
					continue;
				}

				if ($key == 'date_start' && !empty($value)) {
					$date = DateTime::createFromFormat('d/m/Y', $value);
					$value = date_format($date, "Y-m-d");
				}

				if ($key == 'date_end' && !empty($value)) {
					$date = DateTime::createFromFormat('d/m/Y', $value);
					$value = date_format($date, "Y-m-d");
				}

				$education->{$key}
				= $value;
			}

		}

		$education->id_customer = $customer->id;
		$education->id_student_education_state = 2;
		$education->identifiant = $customer->email;
		$education->passwd_link = $customer->password;

		if ($education->id_education_session > 0) {

			$days = Education::getDaysEducation($education->id_education, $education->id_education_attribute);
			$session = new EducationSession($education->id_education_session);
			$date = new DateTime($session->session_date);
			$date->modify('+' . $days . ' days');
			$education->date_start = $session->session_date;
			$education->date_end = $date->format('Y-m-d');
		} else {

			$idSession = EducationSession::getIdSesseddioinbyDate($education->date_start);

			if ($idSession > 0) {
				$education->id_education_session = $idSession;
			} else {
				$session = new EducationSession();
				$session->session_date = $education->date_start;
				$Newdate = DateTime::createFromFormat('Y-m-d', $education->date_start);
				$session->name = 'Session du ' . EducationSession::convertinFrench($Newdate->format("d F Y"));
				$session->add();
				$education->id_education_session = $session->id;
			}

		}

		if ($education->id_education_supplies == 3) {
			$education->id_formatpack = 4;

		} else {

			$education->id_formatpack = 1;
		}

		$education->add();

		$studentEducation = new StudentEducation($education->id);

		$suivie = new StudentEducationSuivie();
		$suivie->suivie_date = date('Y-m-d');
		$suivie->id_employee = $this->context->employee->id;
		$suivie->id_student_education = $studentEducation->id;
		$suivie->content = 'Inscription de ' . $customer->firstname . ' ' . $customer->lastname . ' a une formation "' . $studentEducation->name . ' par ' . $this->context->employee->firstname . ' ' . $this->context->employee->lastname;
		$suivie->add();
		$step = new StudentEducationStep(2, $this->context->language->id);

		if ($step->is_suivie == 1) {
			$suivie = new StudentEducationSuivie();
			$suivie->suivie_date = date('Y-m-d');
			$suivie->id_student_education = $education->id;
			$suivie->id_student_education_state = $step->id;
			$suivie->content = $step->suivie;
			$suivie->add();

		}

		$studentEducation = new StudentEducation($education->id);
		$education = new Education($studentEducation->id_education);

		$date_start = $studentEducation->date_start;
		$fileAttachement = null;
		$attachement = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
			(new DbQuery())
				->select('fileName')
				->from('education_programme')
				->where('`id_education` = ' . (int) $studentEducation->id_education)
				->where('`id_education_attribute` = ' . (int) $studentEducation->id_education_attribute)
		);

		if ($attachement != '') {
			$fileName = _PS_PROGRAM_DIR_ . $attachement;
			$fileAttachement[] = [
				'content' => chunk_split(base64_encode(file_get_contents($fileName))),
				'name'    => $attachement,
			];
		}

		if ($studentEducation->id_sale_agent > 0) {

			$agent = new SaleAgent($studentEducation->id_sale_agent);

			$tpl = $this->context->smarty->createTemplate(_PS_MAIL_DIR_ . '/fr/registrationRequestAgent.tpl');

			$tpl->assign([
				'student'          => $customer,
				'studentEducation' => $studentEducation,
				'agent_firstname'  => $agent->firstname,
				'agent_lastname'   => $agent->lastname,
				'reservationLink'  => $studentEducation->reservationLink,
				'is_video_tuto'    => Configuration::get('EPH_ALLOW_VIDEO_TUTO'),
				'tutoVideo'        => Configuration::get('EPH_TUTO_VIDEO'),
			]);
			$postfields = [
				'sender'      => [
					'name'  => "Service  Administratif " . Configuration::get('PS_SHOP_NAME'),
					'email' => 'no-reply@' . Configuration::get('PS_SHOP_URL'),
				],
				'to'          => [
					[
						'name'  => $customer->firstname . ' ' . $customer->lastname,
						'email' => $customer->email,
					],
				],
				'subject'     => 'Votre formation ' . $studentEducation->name,
				"htmlContent" => $tpl->fetch(),
				'attachment'  => $fileAttachement,
			];
			$result = Tools::sendEmail($postfields);

			if ($agent->sale_commission_amount > 0) {

				$tpl = $this->context->smarty->createTemplate(_PS_MAIL_DIR_ . '/fr/registrationAgentRequest.tpl');
				$tpl->assign([
					'title'           => $customer->title,
					'lastname'        => $customer->lastname,
					'birthname'       => $customer->birthname,
					'firstname'       => $customer->firstname,
					'education_name'  => $studentEducation->name,
					'date_start'      => $studentEducation->date_start,
					'date_end'        => $studentEducation->date_end,
					'last_days'       => $studentEducation->days,
					'last_hours'      => $studentEducation->hours,
					'supplyName'      => $studentEducation->supplyName,
					'certification'   => $education->certification,
					'agent_lastname'  => $agent->lastname,
					'agent_firstname' => $agent->firstname,
					'agent_com'       => $agent->sale_commission_amount,

				]);
				$postfields = [
					'sender'      => [
						'name'  => "Service  Administratif " . Configuration::get('PS_SHOP_NAME'),
						'email' => 'no-reply@' . Configuration::get('PS_SHOP_URL'),
					],
					'to'          => [
						[
							'name'  => $agent->firstname . ' ' . $agent->lastname,
							'email' => $agent->email,
						],
					],
					'subject'     => 'Inscription de votre étudiant à une formation en ' . $studentEducation->name,
					"htmlContent" => $tpl->fetch(),
				];
				$result = Tools::sendEmail($postfields);

			}

		} else {

			$tpl = $this->context->smarty->createTemplate(_PS_MAIL_DIR_ . '/fr/registrationRequest.tpl');

			$tpl->assign([
				'student'          => $customer,
				'studentEducation' => $studentEducation,
				'is_video_tuto'    => Configuration::get('EPH_ALLOW_VIDEO_TUTO'),
				'tutoVideo'        => Configuration::get('EPH_TUTO_VIDEO'),
			]);
			$postfields = [
				'sender'      => [
					'name'  => "Service  Administratif " . Configuration::get('PS_SHOP_NAME'),
					'email' => 'no-reply@' . Configuration::get('PS_SHOP_URL'),
				],
				'to'          => [
					[
						'name'  => $customer->firstname . ' ' . $customer->lastname,
						'email' => $customer->email,
					],
				],
				'subject'     => 'Votre formation ' . $studentEducation->name,
				"htmlContent" => $tpl->fetch(),
				'attachment'  => $fileAttachement,
			];
			$result = Tools::sendEmail($postfields);
		}

		$return = [
			'success' => true,
			'message' => $this->l('La session de formation a été ajoué avec duccès'),
		];
		die(Tools::jsonEncode($return));

	}

	public function ajaxProcessCheckSessionStep() {

		$id_education_session = Tools::getValue('idSession');

		$session = new EducationSession($id_education_session);
		$today = date("Y-m-d");

		if ($session->session_date < $today) {
			$isValidate = true;
		} else {
			$isValidate = false;
		}

		$return = [
			'success' => $isValidate,
		];
		die(Tools::jsonEncode($return));
	}

	public function ajaxProcessCheckSessionIsEnded() {

		$today = date("Y-m-d");
		$date = new DateTime($today);
		$date->modify('-30 days');
		$dateEnd = $date->format('Y-m-d');
		$id_education_session = Tools::getValue('idSession');
		$session = new EducationSession($id_education_session);
		$isEnded = false;

		if ($dateEnd > $session->session_date) {
			$isEnded = true;
		}

		$return = [
			'isEnded' => $isEnded,
		];
		die(Tools::jsonEncode($return));
	}

	public function ajaxProcessCheckSessionIsClosed() {

		if ($this->context->employee->id_profile > 1) {
			$return = [
				'isClosed' => false,
			];
			die(Tools::jsonEncode($return));
		}

		$id_education_session = Tools::getValue('idSession');
		$session = new EducationSession($id_education_session);
		$isClosed = false;

		if ($session->sessionClosed == 0 && $session->sessionOpen == 1) {
			$isClosed = true;
		}

		$return = [
			'isClosed' => $isClosed,
		];
		die(Tools::jsonEncode($return));

	}

	public function ajaxProcesscheckSessionIsOpen() {

		$id_education_session = Tools::getValue('idSession');
		$session = new EducationSession($id_education_session);
		$isOpen = false;

		if ($session->sessionOpen == 1) {
			$isOpen = true;
		}

		$return = [
			'isOpen' => $isOpen,
		];
		die(Tools::jsonEncode($return));
	}

	public function ajaxProcessGetDataSession() {

		$id_education_session = Tools::getValue('idSession');

		$attendees = StudentEducation::getNbAttendees($id_education_session);

		$turnover = StudentEducation::getSessionTurnover($id_education_session);

		$expected = StudentEducation::getSessionExpectedTurnover($id_education_session);

		$html = $this->l('Accepté : ') . number_format($turnover, 2, ",", " ") . $this->l(' € HT');

		if ((int) $expected > 0) {
			$html .= '<br>En attente : ' . number_format($expected, 2, ",", " ") . $this->l(' € HT');
		}

		$return = [
			'html' => '<sup class="accepteAttente">'.$html.'</sup>',
		];
		die(Tools::jsonEncode($return));

	}

	public function ajaxProcessBulkDelete() {

		$students = Tools::getValue('students');

		foreach ($students as $student) {
			$object = new StudentEducation($student);

			if (!$object->delete()) {
				$this->errors[] = Tools::displayError('An error occurred while deleting the student ' . $object->firstname);
			}

		}

		$this->errors = array_unique($this->errors);

		if (count($this->errors)) {
			$result = [
				'success' => false,
				'message' => implode(PHP_EOL, $this->errors),
			];
		} else {
			$result = [
				'success' => true,

				'message' => $this->l('Selection of students has been properly deleted'),
			];
		}

		die(Tools::jsonEncode($result));

	}

	public function ajaxProcessSearchStudentEducations() {

		$searches = explode(' ', Tools::getValue('student_search'));
		$students = [];
		$searches = array_unique($searches);

		foreach ($searches as $search) {

			if (!empty($search) && $results = StudentEducation::searchByName($search, 50)) {

				foreach ($results as $result) {

					if ($result['active']) {
						$students[$result['id_student']] = $result;
					}

				}

			}

		}

		if (count($students)) {
			$toReturn = [
				'students' => $students,
				'found'    => true,
			];
		} else {
			$toReturn = ['found' => false];
		}

		$this->content = json_encode($toReturn);
	}

	public function ajaxProcessUpdateStudentEducationNote() {

		if ($this->tabAccess['edit'] === '1') {
			$note = Tools::htmlentitiesDecodeUTF8(Tools::getValue('note'));
			$student = new StudentEducation((int) Tools::getValue('id_student'));

			if (!Validate::isLoadedObject($student)) {
				die('error:update');
			}

			if (!empty($note) && !Validate::isCleanHtml($note)) {
				die('error:validation');
			}

			$student->note = $note;

			if (!$student->update()) {
				die('error:update');
			}

			die('ok');
		}

	}

	public function ajaxProcessUpdateStudentEducation() {

		$id = (int) Tools::getValue('id_student_education');

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

	protected function processBulkDelete() {

		$this->_setDeletedMode();
		parent::processBulkDelete();
	}

	protected function afterDelete($object, $oldId) {

		$student = new StudentEducation($oldId);
		$addresses = $student->getAddresses($this->default_form_language);

		foreach ($addresses as $k => $v) {
			$address = new Address($v['id_address']);
			$address->id_student = $object->id;
			$address->save();
		}

		return true;
	}

	public function ajaxProcessGeneratePdf() {

		$id_student_education = Tools::getValue('id_student_education');
		$studentEducation = new StudentEducation($id_student_education);
		$customer = new Customer($studentEducation->id_customer);
		$education = new Education($studentEducation->id_education);

		$this->context->smarty->assign([
			'student'          => $customer,
			'logo_path'        => _PS_ROOT_DIR_ . '/img/' . Configuration::get('PS_LOGO'),
			'img_ps_dir'       => 'http://' . Tools::getMediaServer(_PS_IMG_) . _PS_IMG_,
			'studentEducation' => $studentEducation,
			'education'        => $education,
			'launchRef'        => 'Bien démarrer sa formation V.1.9.04.01.2022',
			'ciseaux'          => _PS_ROOT_DIR_ . '/img/pointille.png',
			'checkbox'         => _PS_ROOT_DIR_ . '/img/checkbox.png',
		]);

		$pdf = new EducationTemplate('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
		$pdf->SetMargins(5, 2, 5, true);
		$pdf->setPrintHeader(false);
		$pdf->SetFont('helvetica', '', 9);
		$pdf->AddPage();
		$pdf->pieceFooter = $this->context->smarty->fetch(_PS_PDF_TEMPLATE_DIR_ . 'footer.tpl');
		$html = $this->context->smarty->fetch(_PS_PDF_TEMPLATE_DIR_ . 'educationLaunch.tpl');
		$pdf->writeHTML($html, false);
		$pdf->Output(_PS_PDF_STUDENT_DIR_ . $studentEducation->id . '_launch.pdf', 'F');

		if ($education->certification == 'TOSA') {
			$this->context->smarty->assign([
				'launchRef' => 'TOSA V.1.4.04.05.21',
				'logo_tosa' => _PS_ROOT_DIR_ . '/img/tosa.png',
			]);
			$pdf = new EducationTemplate('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
			$pdf->SetMargins(10, 2, 10, true);
			$pdf->setPrintHeader(false);
			$pdf->SetFont('helvetica', '', 9);
			$pdf->AddPage();
			$pdf->pieceFooter = $this->context->smarty->fetch(_PS_PDF_TEMPLATE_DIR_ . 'footer.tpl');
			$html = $this->context->smarty->fetch(_PS_PDF_TEMPLATE_DIR_ . 'tosa.tpl');
			$pdf->writeHTML($html, false);
			$pdf->Output(_PS_PDF_STUDENT_DIR_ . $studentEducation->id . '_tosa.pdf', 'F');

		} else

		if ($education->certification == 'PIPPLET') {
			$this->context->smarty->assign([
				'launchRef'    => 'PIPPLET V.1.4.04.05.21',
				'logo_pipplet' => _PS_ROOT_DIR_ . '/img/pipplet.png',
			]);
			$pdf = new EducationTemplate('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
			$pdf->SetMargins(10, 2, 10, true);
			$pdf->setPrintHeader(false);
			$pdf->SetFont('helvetica', '', 9);
			$pdf->AddPage();
			$pdf->pieceFooter = $this->context->smarty->fetch(_PS_PDF_TEMPLATE_DIR_ . 'footer.tpl');
			$html = $this->context->smarty->fetch(_PS_PDF_TEMPLATE_DIR_ . 'pipplet.tpl');
			$pdf->writeHTML($html, false);
			$pdf->Output(_PS_PDF_STUDENT_DIR_ . $studentEducation->id . '_pipplet.pdf', 'F');

		}

		$result = [
			'success' => true,
			'message' => $this->l('Publipostage généré avec succès'),
		];

		die(Tools::jsonEncode($result));
	}

	public function ajaxProcessSendTrackingSms() {

		$idEducation = Tools::getValue('idEducation');
		$tracking = Tools::getValue('tracking');
		$phoneMobile = Tools::getValue('phoneMobile');

		$studentEducation = new StudentEducation($idEducation);
		$studentEducation->shipping_number = $tracking;
		$studentEducation->shipping_sms = 1;
		$studentEducation->update();

		$student = new Student($studentEducation->id_student);
		$customer = new Customer($studentEducation->id_customer);

		$this->context->smarty->assign(
			[
				'student'          => $customer,
				'studentEducation' => $studentEducation,
				'tracking'         => $tracking,
			]
		);

		$content = $this->context->smarty->fetch(_PS_SMS_DIR_ . 'smstracking.tpl');
		$recipient = $phoneMobile;
		Tools::sendSms($recipient, $content);

		$result = [
			'success' => true,
			'message' => $this->l('Le sms a été envoyé avec succès'),
		];

		die(Tools::jsonEncode($result));
	}

	public function sendSms($recipient, $content) {

		$curl = curl_init();
		$postfields = [
			'type'      => 'transactional',
			'recipient' => $recipient,
			'content'   => $content,
			'sender'    => 'LDFormation',
		];

		curl_setopt_array($curl, [

			CURLOPT_URL            => "https://api.sendinblue.com/v3/transactionalSMS/sms",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING       => "",

			CURLOPT_MAXREDIRS      => 10,
			CURLOPT_TIMEOUT        => 30,
			CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST  => "POST",
			CURLOPT_POSTFIELDS     => Tools::jsonEncode($postfields),
			CURLOPT_HTTPHEADER     => [
				"Accept: application/json",
				"Content-Type: application/json",
				"api-key: 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",
			],
		]);

		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);

	}

	public function ajaxProcessPubliAttestBulk() {

		$studentEducations = Tools::getValue('idEducations');

		foreach ($studentEducations as $id_student_education) {

			$studentEducation = new StudentEducation($id_student_education);

			$student = new Student($studentEducation->id_student);
			$customer = new Customer($studentEducation->id_customer);
			$education = new Education($studentEducation->id_education);

			$this->context->smarty->assign([
				'student'          => $customer,
				'logo_path'        => _PS_ROOT_DIR_ . '/img/' . Configuration::get('PS_LOGO'),
				'img_ps_dir'       => 'http://' . Tools::getMediaServer(_PS_IMG_) . _PS_IMG_,
				'studentEducation' => $studentEducation,
				'education'        => $education,
				'launchRef'        => 'Bien démarrer sa formation V.1.6.25.02.21',
				'ciseaux'          => _PS_ROOT_DIR_ . '/img/pointille.png',
				'checkbox'         => _PS_ROOT_DIR_ . '/img/checkbox.png',
			]);

			if ($education->certification == 'TOSA' && !file_exists(_PS_PDF_STUDENT_DIR_ . $studentEducation->id . '_endOffice.pdf')) {
				$this->context->smarty->assign([
					'launchRef' => 'Attestation B V.1.4.29.03.21',
				]);
				$pdf = new EducationTemplate('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
				$pdf->SetMargins(10, 2, 10, true);
				$pdf->setPrintHeader(false);
				$pdf->SetFont('helvetica', '', 9);
				$pdf->AddPage();
				$pdf->pieceFooter = $this->context->smarty->fetch(_PS_PDF_TEMPLATE_DIR_ . 'footer.tpl');
				$html = $this->context->smarty->fetch(_PS_PDF_TEMPLATE_DIR_ . 'endOffice.tpl');
				$pdf->writeHTML($html, false);
				$pdf->Output(_PS_PDF_STUDENT_DIR_ . $studentEducation->id . '_endOffice.pdf', 'F');

			} else

			if ($education->certification == 'PIPPLET' && !file_exists(_PS_PDF_STUDENT_DIR_ . $studentEducation->id . '_endLangue.pdf')) {
				$this->context->smarty->assign([
					'launchRef' => 'Attestation LV.1.4.29.03.21',
				]);
				$pdf = new EducationTemplate('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
				$pdf->SetMargins(10, 2, 10, true);
				$pdf->setPrintHeader(false);
				$pdf->SetFont('helvetica', '', 9);
				$pdf->AddPage();
				$pdf->pieceFooter = $this->context->smarty->fetch(_PS_PDF_TEMPLATE_DIR_ . 'footer.tpl');
				$html = $this->context->smarty->fetch(_PS_PDF_TEMPLATE_DIR_ . 'endLangue.tpl');
				$pdf->writeHTML($html, false);
				$pdf->Output(_PS_PDF_STUDENT_DIR_ . $studentEducation->id . '_endLangue.pdf', 'F');

			}

			$studentEducation->attest_end = 1;
			$studentEducation->update();

		}

		$result = [
			'success' => true,
			'message' => $this->l('Publipostage généré avec succès'),
		];

		die(Tools::jsonEncode($result));
	}

	public function ajaxProcessPubliPostBulk() {

		$studentEducations = Tools::getValue('idEducations');

		foreach ($studentEducations as $id_student_education) {

			$studentEducation = new StudentEducation($id_student_education);

			if ($studentEducation->id_student_education_state < 3) {
				continue;
			}

			$student = new Student($studentEducation->id_student);
			$customer = new Customer($studentEducation->id_customer);
			$education = new Education($studentEducation->id_education);

			$this->context->smarty->assign([
				'student'          => $customer,
				'logo_path'        => _PS_ROOT_DIR_ . '/img/' . Configuration::get('PS_LOGO'),
				'img_ps_dir'       => 'http://' . Tools::getMediaServer(_PS_IMG_) . _PS_IMG_,
				'studentEducation' => $studentEducation,
				'education'        => $education,
				'launchRef'        => 'Bien démarrer sa formation V.1.9.04.05.2021',
				'ciseaux'          => _PS_ROOT_DIR_ . '/img/pointille.png',
				'checkbox'         => _PS_ROOT_DIR_ . '/img/checkbox.png',
			]);

			if (!file_exists(_PS_PDF_STUDENT_DIR_ . $studentEducation->id . '_cpmateriel.pdf')) {
				$this->context->smarty->assign([
					'launchRef'   => 'Matériel pédagogique V 1.4.27.04.21',
					'logo_tampon' => _PS_ROOT_DIR_ . '/img/' . Configuration::get('EPH_SOURCE_STAMP'),
				]);
				$pdf = new EducationTemplate('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
				$pdf->SetMargins(10, 2, 10, true);
				$pdf->setPrintHeader(false);
				$pdf->SetFont('helvetica', '', 9);
				$pdf->AddPage();
				$pdf->pieceFooter = $this->context->smarty->fetch(_PS_PDF_TEMPLATE_DIR_ . 'footer.tpl');
				$html = $this->context->smarty->fetch(_PS_PDF_TEMPLATE_DIR_ . 'cpmateriel.tpl');
				$pdf->writeHTML($html, false);
				$pdf->Output(_PS_PDF_STUDENT_DIR_ . $studentEducation->id . '_cpmateriel.pdf', 'F');
			}

			$studentEducation->publiPost = 1;
			$studentEducation->update();

			$session = new EducationSession($studentEducation->id_education_session);
			$session->publiPost = 1;
			$session->update();
		}

		$result = [
			'success' => true,
			'message' => $this->l('Publipostage généré avec succès'),
		];

		die(Tools::jsonEncode($result));
	}

	public function ajaxProcessGenerateAttestation() {

		$idSession = Tools::getValue('idSession');
		$studentEducation = new StudentEducation($idSession);
		$student = new Student($studentEducation->id_student);
		$customer = new Customer($studentEducation->id_customer);
		$education = new Education($studentEducation->id_education);

		$this->context->smarty->assign([
			'student'          => $customer,
			'logo_path'        => _PS_ROOT_DIR_ . '/img/' . Configuration::get('PS_LOGO'),
			'img_ps_dir'       => 'http://' . Tools::getMediaServer(_PS_IMG_) . _PS_IMG_,
			'studentEducation' => $studentEducation,
			'education'        => $education,
			'launchRef'        => '	Attestation de fin de formation V.1.6.02.06.21',
			'ciseaux'          => _PS_ROOT_DIR_ . '/img/pointille.png',
			'checkbox'         => _PS_ROOT_DIR_ . '/img/checkbox.png',
			'logo_tampon'      => _PS_ROOT_DIR_ . '/img/' . Configuration::get('EPH_SOURCE_STAMP'),
		]);

		if ($education->id_education_type == 1) {
			$this->context->smarty->assign([
				'launchRef' => '	Attestation de fin de formation Bureautique V.1.6.06.06.21',
			]);

			if (!file_exists(_PS_PDF_STUDENT_DIR_ . $studentEducation->id . '_attestation_bureautique.pdf')) {
				$pdf = new EducationTemplate('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
				$pdf->SetMargins(10, 2, 10, true);
				$pdf->setPrintHeader(false);
				$pdf->SetFont('helvetica', '', 9);
				$pdf->AddPage();
				$pdf->pieceFooter = $this->context->smarty->fetch(_PS_PDF_TEMPLATE_DIR_ . 'footer.tpl');
				$html = $this->context->smarty->fetch(_PS_PDF_TEMPLATE_DIR_ . 'attestation_bureautique.tpl');
				$pdf->writeHTML($html, false);
				$pdf->Output(_PS_PDF_STUDENT_DIR_ . $studentEducation->id . '_attestation_bureautique.pdf', 'F');
			}

		} else {
			$this->context->smarty->assign([
				'launchRef' => '	Attestation de fin de formation Langue V.1.6.06.06.21',
			]);

			if (!file_exists(_PS_PDF_STUDENT_DIR_ . $studentEducation->id . '_attestation_langue.pdf')) {
				$pdf = new EducationTemplate('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
				$pdf->SetMargins(10, 2, 10, true);
				$pdf->setPrintHeader(false);
				$pdf->SetFont('helvetica', '', 9);
				$pdf->AddPage();
				$pdf->pieceFooter = $this->context->smarty->fetch(_PS_PDF_TEMPLATE_DIR_ . 'footer.tpl');
				$html = $this->context->smarty->fetch(_PS_PDF_TEMPLATE_DIR_ . 'attestation_langue.tpl');
				$pdf->writeHTML($html, false);
				$pdf->Output(_PS_PDF_STUDENT_DIR_ . $studentEducation->id . '_attestation_langue.pdf', 'F');
			}

		}

		$result = [
			'success' => true,
			'message' => $this->l('Attestation générée avec succès'),
		];

		die(Tools::jsonEncode($result));
	}

	public function ajaxProcessMoveStudentSession() {

		$oldEducationSessionId = Tools::getValue('idEducationSession');
		$nextIdSession = Tools::getValue('idSession');

		$session = new EducationSession($oldEducationSessionId);
		$date_origin = $session->session_date;

		$nextSession = new EducationSession($nextIdSession);

		$mailAgent = [];
		$studentEducations = StudentEducation::getSessionbyIdSession($session->id);

		foreach ($studentEducations as $studentEducation) {

			$education = new StudentEducation($studentEducation['id_student_education']);

			if ($education->id_student_education_state < 3) {
				$date = new DateTime($nextSession->session_date);
				$days = Education::getDaysEducation($education->id_education, $education->id_education_attribute);
				$date->modify('+' . $days . ' days');
				$education->date_start = $nextSession->session_date;
				$education->date_end = $date->format('Y-m-d');
				$education->id_education_session = $nextIdSession;
				$education->id_student_education_state = 2;
				$education->update();

				$customer = new Customer($education->id_customer);

				if ($education->id_sale_agent > 0) {
					$agent = new SaleAgent($education->id_sale_agent);

					if ($agent->sale_commission_amount > 0) {
						$mailAgent[$agent->id][] = [
							'studentEducation' => $education->id,
						];
					}

				}

				$tpl = $this->context->smarty->createTemplate(_PS_MAIL_DIR_ . '/fr/registrationDelayed.tpl');

				$tpl->assign([
					'education'   => $education,
					'student'     => $customer,
					'nextSession' => $nextSession,
					'date_origin' => $date_origin,
					'session'     => $session,

				]);
				$postfields = [
					'sender'      => [
						'name'  => "Automation CRM",
						'email' => Configuration::get('PS_SHOP_EMAIL'),
					],
					'to'          => [
						[
							'name'  => $customer->firstname . ' ' . $customer->lastname,
							'email' => $customer->email,
						],
					],
					'subject'     => 'Cloture et réinscription pour votre formation ' . $education->name,
					"htmlContent" => $tpl->fetch(),
				];
				$result = Tools::sendEmail($postfields);
			}

		}

		if (is_array($mailAgent) && count($mailAgent)) {

			foreach ($mailAgent as $key => $values) {

				$html = '';
				$agent = new SaleAgent($key);
				$i = 0;

				foreach ($values as $value) {
					$idEducationSession = $value['studentEducation'];
					$studentEducation = new StudentEducation($idEducationSession);
					$student = new Student($studentEducation->id_student);
					$customer = new Customer($studentEducation->id_customer);
					$html .= '<tr>
						<td>' . $customer->firstname . '</td>
						<td>' . $customer->lastname . '</td>
						<td>' . $customer->phone_mobile . '</td>
						<td>' . $studentEducation->sessionName . '</td>
						<td>' . $studentEducation->reference . '</td>
						</tr>';
					$i++;

				}

				$tpl = $this->context->smarty->createTemplate(_PS_MAIL_DIR_ . '/fr/educationDelayed.tpl');
				$tpl->assign([

					'session_name'    => $studentEducation->sessionName,
					'agent_lastname'  => $agent->lastname,
					'agent_firstname' => $agent->firstname,
					'html'            => $html,
					'count'           => $i,
					'date_origin'     => $originalDate,
					'date_start'      => $studentEducation->date_start,

				]);
				$postfields = [
					'sender'      => [
						'name'  => "Automation CRM",
						'email' => Configuration::get('PS_SHOP_EMAIL'),
					],
					'to'          => [
						[
							'name'  => $agent->firstname . ' ' . $agent->lastname,
							'email' => $agent->email,
						],
					],
					'subject'     => 'Report des inscriptions du ' . $studentEducation->sessionName,
					"htmlContent" => $tpl->fetch(),
				];
				$result = Tools::sendEmail($postfields);

			}

		}

		$session->sessionClosed = 1;
		$session->update();

		StudentEducation::syncAlterSession($session->id);
		StudentEducation::synchCloudLearning($session->id);

		$result = [
			'success' => true,
			'message' => $this->l('La session a été fermée avec succès'),
		];

		die(Tools::jsonEncode($result));
	}

	public function ajaxProcessCloseSession() {

		$idEducationSession = Tools::getValue('idSession');

		$session = new EducationSession($idEducationSession);

		//$studentEducation = new StudentEducation($idEducationSession);

		$nextSessions = EducationSession::getForwardedEducations($idEducationSession);

		$data = $this->createTemplate('controllers/student_educations/sessions.tpl');

		$data->assign('studentEducation', $session);
		$data->assign('nextSessions', $nextSessions);
		$data->assign('idEducationSession', $idEducationSession);

		$result = [
			'html' => $data->fetch(),
		];

		die(Tools::jsonEncode($result));
	}

	public function ajaxProcessUploadSessionFile() {

		$name = 'file';

		if (isset($_FILES[$name]['name'])) {

			$Upload = [];
			$Upload['content'] = Tools::file_get_contents($_FILES[$name]['tmp_name']);
			$Upload['name'] = $_FILES[$name]['name'];
			$Upload['mime'] = $_FILES[$name]['type'];
			$sourcePath = $_FILES[$name]['tmp_name'];
			$uploadfile = _PS_UPLOAD_DIR_ . $_FILES[$name]['name'];
			move_uploaded_file($sourcePath, $uploadfile);

			$result = [
				'success'    => true,
				'uploadFile' => $uploadfile,
			];

			die(Tools::jsonEncode($result));
		}

	}

	public function ajaxProcessUploadEdofFile() {

		$name = 'file';
		$file = fopen("testProcessUploadEdofFile.txt", "w");

		if (isset($_FILES[$name]['name'])) {

			$Upload = [];
			$Upload['content'] = Tools::file_get_contents($_FILES[$name]['tmp_name']);
			$Upload['name'] = $_FILES[$name]['name'];
			$Upload['mime'] = $_FILES[$name]['type'];
			$sourcePath = $_FILES[$name]['tmp_name'];
			$uploadfile = _PS_UPLOAD_DIR_ . 'edof.csv';
			move_uploaded_file($sourcePath, $uploadfile);

			if (file_exists($uploadfile)) {
				$inputFileType = 'Csv';
				$reader = IOFactory::createReader($inputFileType);
				$today = date('Y-m-d');
				$isSynch = StudentEducationSynch::isSessionSync();

				if ($isSynch > 0) {
					$sync = new StudentEducationSynch($isSynch);
					$sync->edof = $today;
					$sync->update();

				} else {
					$sync = new StudentEducationSynch();
					$sync->edof = $today;
					$sync->add();
				}

				$date = new DateTime($today);
				$date->modify('-2 month');
				$data_limit = $date->format('Y-m-d');

				$spreadsheet = $reader->load($uploadfile);

				$valides = ['Accepté', 'En formation', 'Facturé', 'Validé', 'Non traité'];
				$sessionEdof = [];
				$sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

				foreach ($sheetData as $key => $sheets) {

					if ($key == 1) {
						continue;
					}

					if (!str_contains($sheets['H'], '/')) {
						continue;
					}

					$phpdate = DateTime::createFromFormat('d/m/Y', $sheets['H']);
					$mysqldate = $phpdate->format('Y-m-d');

					if ($mysqldate < $data_limit) {
						continue;
					}

					$lastname = Tools::skip_accents(strtolower($sheets['B']));
					$firstname = Tools::skip_accents(strtolower($sheets['C']));
					$idSession = 0;
					$idSession = Db::getInstance()->getValue('SELECT `id_education_session`
    					FROM `' . _DB_PREFIX_ . 'education_session`
    					WHERE `session_date` LIKE \'' . $mysqldate . '\'');

					if ($idSession > 0) {

						if (in_array($sheets['F'], $valides)) {
							$sessionEdof[$idSession][] = [
								'edof'      => $sheets['A'],
								'lastname'  => $lastname,
								'firstname' => $firstname,
								'status'    => $sheets['F'],
							];
						}

					}

				}

				ksort($sessionEdof);
				fwrite($file, print_r($sessionEdof, true));

				foreach ($sessionEdof as $key => $values) {

					foreach ($values as $value) {

						$idSession = StudentEducation::getIdSessionbyEdof($value['edof']);

						if ($idSession > 0) {

							$studentEducation = new StudentEducation($idSession);

							$customer = new Customer($studentEducation->id_customer);
							$customer->lastname = ucwords($customer->lastname);
							$customer->birthname = ucwords($value['lastname']);
							$customer->firstname = ucwords($value['firstname']);

							try {
								$result = $customer->update(false, false);
							} catch (Exception $e) {
								fwrite($file, 'Student Error ' . $e->getMessage() . PHP_EOL);
							}

							try {
								//$result = $customer->update(false);
							} catch (Exception $e) {
								fwrite($file, 'Student Error ' . $e->getMessage() . PHP_EOL);
							}

							if ($value['status'] == 'Validé' && $studentEducation->id_student_education_state < 3) {
								StudentEducation::changeEducationStepId($studentEducation->id, 3);
							}

							if ($value['status'] == 'Accepté' && $studentEducation->id_student_education_state < 4) {
								StudentEducation::changeEducationStepId($studentEducation->id, 4);
							}

							if ($value['status'] == 'En formation' && $studentEducation->id_student_education_state < 5) {
								StudentEducation::changeEducationStepId($studentEducation->id, 5);
							}

						}

					}

				}

			}

			$result = [
				'success' => true,
				'message' => 'Les références Edof ont été mise à jour avec succès',
			];

			die(Tools::jsonEncode($result));
		}

	}

	public function ajaxProcessUploadLanguageFile() {

		$name = 'file';

		if (isset($_FILES[$name]['name'])) {

			$Upload = [];
			$Upload['content'] = Tools::file_get_contents($_FILES[$name]['tmp_name']);
			$Upload['name'] = $_FILES[$name]['name'];
			$Upload['mime'] = $_FILES[$name]['type'];
			$sourcePath = $_FILES[$name]['tmp_name'];
			$uploadfile = _PS_UPLOAD_DIR_ . 'language.xlsx';
			move_uploaded_file($sourcePath, $uploadfile);

			if (file_exists($uploadfile)) {
				$inputFileType = 'Xlsx';
				$reader = IOFactory::createReader($inputFileType);

				$isSynch = StudentEducationSynch::isSessionSync();

				if ($isSynch > 0) {
					$sync = new StudentEducationSynch($isSynch);
					$sync->speaking = date('Y-m-d');
					$sync->update();

				} else {
					$sync = new StudentEducationSynch();
					$sync->speaking = date('Y-m-d');
					$sync->add();
				}

				$spreadsheet = $reader->load($uploadfile);

				$fieldUtils = ['Nom', 'Prénom', 'Adresse email', 'ID interne', 'Date début formation', 'Date de première connexion', 'Temps passé Weblearning'];
				$fieldFounds = [];
				$sessionEdof = [];

				$sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

				$today = date('Y-m-d');
				$date = new DateTime($today);
				$date->modify('-2 month');
				$dateRef = $date->format('Y-m-d');

				foreach ($sheetData as $key => $sheets) {

					if ($key == 1) {

						for ($i = 1; $i <= count($sheets); $i++) {
							$entete = $sheets[chr(64 + $i)];

							if (in_array($entete, $fieldUtils)) {
								$fieldFounds[$entete] = chr(64 + $i);
							}

						}

						break;
					}

				}

				foreach ($sheetData as $key => $sheets) {

					if ($key == 1) {
						continue;
					}

					$phpdate = explode('/', $sheets[$fieldFounds['Date début formation']]);
					$year = $phpdate[2];
					$month = strlen($phpdate[0]) == 2 ? $phpdate[0] : '0' . $phpdate[0];
					$day = strlen($phpdate[1]) == 2 ? $phpdate[1] : '0' . $phpdate[1];
					$mysqldate = $year . '-' . $month . '-' . $day;

					if ($mysqldate < $dateRef) {
						continue;
					}

					$idSession = 0;
					$idSession = Db::getInstance()->getValue('SELECT `id_education_session`
    					FROM `' . _DB_PREFIX_ . 'education_session`
    					WHERE `session_date` LIKE \'' . $mysqldate . '\'');

					if ($idSession > 0) {

						$datefirst = '0000-00-00';
						$firstconnection = 0;

						if (!empty($sheets[$fieldFounds['Date de première connexion']])) {
							$phpdate = explode('/', $sheets[$fieldFounds['Date de première connexion']]);
							$year = $phpdate[2];
							$month = strlen($phpdate[0]) == 2 ? $phpdate[0] : '0' . $phpdate[0];
							$day = strlen($phpdate[1]) == 2 ? $phpdate[1] : '0' . $phpdate[1];
							$datefirst = $year . '-' . $month . '-' . $day;
							$firstconnection = 1;
						}

						$education_lenghts = '00:00:00';

						if (!empty($sheets[$fieldFounds['Temps passé Weblearning']])) {
							$education_lenghts = $sheets[$fieldFounds['Temps passé Weblearning']];
						}

						$idInterne = $sheets[$fieldFounds['ID interne']];

						if (Validate::isUnsignedId($idInterne)) {
							$sessionEdof[$idInterne] = [
								'has_connect'       => $firstconnection,
								'first_connection'  => $datefirst,
								'education_lenghts' => $education_lenghts,
								'email'             => $sheets[$fieldFounds['Adresse email']],
							];
						}

					}

				}

				ksort($sessionEdof);

				foreach ($sessionEdof as $key => $value) {

					$studentEducation = new StudentEducation($key);

					if (Validate::isLoadedObject($studentEducation)) {

						if ($studentEducation->id_student_education_state < 7 && $value['has_connect'] == 1) {
							StudentEducation::changeEducationStepId($studentEducation->id, 7);
							$studentEducation->id_student_education_state = 7;
						}

						$studentEducation->first_connection = $value['first_connection'];
						$studentEducation->education_lenghts = $value['education_lenghts'];
						$studentEducation->update();
					}

				}

			}

			$result = [
				'success' => true,
				'message' => 'Les Dates de première connexion et temps de e learning ont été mise à jour avec succès',
			];

			die(Tools::jsonEncode($result));
		}

	}

	public function ajaxProcessUploadOfficeFile() {

		$name = 'file';

		if (isset($_FILES[$name]['name'])) {

			$Upload = [];
			$Upload['content'] = Tools::file_get_contents($_FILES[$name]['tmp_name']);
			$Upload['name'] = $_FILES[$name]['name'];
			$Upload['mime'] = $_FILES[$name]['type'];
			$sourcePath = $_FILES[$name]['tmp_name'];
			$uploadfile = _PS_UPLOAD_DIR_ . 'office.xlsx';
			move_uploaded_file($sourcePath, $uploadfile);

			if (file_exists($uploadfile)) {
				$inputFileType = 'Xlsx';
				$reader = IOFactory::createReader($inputFileType);

				$spreadsheet = $reader->load($uploadfile);
				$today = date("Y-m-d");

				$sessionEdof = [];
				$isSynch = StudentEducationSynch::isSessionSync();

				if ($isSynch > 0) {
					$sync = new StudentEducationSynch($isSynch);
					$sync->altercampus = date('Y-m-d');
					$sync->update();

				} else {
					$sync = new StudentEducationSynch();
					$sync->altercampus = date('Y-m-d');
					$sync->add();
				}

				$sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

				$today = date('Y-m-d');
				$date = new DateTime($today);
				$date->modify('-3 month');
				$dateRef = $date->format('Y-m-d');

				foreach ($sheetData as $key => $sheets) {

					if ($key == 1) {
						continue;
					}

					$dateStart = $sheets['G'];

					if ($dateStart < $dateRef) {
						continue;
					}

					$idSession = 0;
					$idSession = Db::getInstance()->getValue('SELECT `id_education_session`
    					FROM `' . _DB_PREFIX_ . 'education_session`
    					WHERE `session_date` LIKE \'' . $dateStart . '\'');

					if ($idSession > 0) {
						$lastname = Tools::skip_accents(strtolower($sheets['C']));
						$firstname = Tools::skip_accents(strtolower($sheets['D']));
						$datefirst = '0000-00-00';

						if ($sheets['I'] == "-") {
							continue;
						}

						if ($sheets['K'] == "-") {
							continue;
						}

						$datefirst = $sheets['I'];
						$education_lenghts = '00:00:00';
						$pos = strpos($sheets['K'], 'min');

						if ($pos === false) {
							$education_lenghts = str_replace('h', ':', $sheets['K']) . ':00';

						}

						$sessionEdof[$idSession][] = [
							'lastname'          => $lastname,
							'firstname'         => $firstname,
							'idSession'         => $idSession,
							'first_connection'  => $datefirst,
							'education_lenghts' => $education_lenghts,
						];
					}

				}

				ksort($sessionEdof);

				foreach ($sessionEdof as $key => $values) {

					$students = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
						(new DbQuery())

							->select('s.*,  st.firstname, st.lastname')
							->from('student_education', 's')
							->leftJoin('student', 'st', 'st.`id_student` = s.`id_student`')
							->leftJoin('education_session', 'es', 'es.`id_education_session` = s.`id_education_session`')
							->where('s.id_education_session = ' . $key)
							->where('s.deleted = 0')
							->orderBy('st.lastname')
					);

					foreach ($values as $value) {
						$Searchstudent = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow(
							(new DbQuery())
								->select('s.*,  st.firstname, st.lastname')
								->from('student_education', 's')
								->leftJoin('student', 'st', 'st.`id_student` = s.`id_student`')
								->leftJoin('education_session', 'es', 'es.`id_education_session` = s.`id_education_session`')
								->where('s.id_education_session = ' . $key)
								->where('s.deleted = 0')
								->where('st.firstname LIKE \'' . $value['firstname'] . '\'')
								->where('st.lastname LIKE \'' . $value['lastname'] . '\'')
								->orderBy('st.lastname')
						);

						if (is_array($Searchstudent) && $Searchstudent['id_student_education'] > 0) {
							$studentEducation = new StudentEducation($Searchstudent['id_student_education']);

							if ($studentEducation->id_student_education_state < 7) {
								StudentEducation::changeEducationStepId($studentEducation->id, 7);
							}

							$studentEducation->first_connection = $value['first_connection'];
							$studentEducation->education_lenghts = $value['education_lenghts'];
							$studentEducation->update();
						} else {

							foreach ($students as $student) {
								$cleanLastname = Tools::skip_accents(strtolower($student['lastname']));
								$cleanFirstname = Tools::skip_accents(strtolower($student['firstname']));
								$simLastName = similar_text($cleanLastname, $value['lastname'], $perc);

								if ($perc >= 75) {
									$simFirstName = similar_text($cleanFirstname, $value['firstname'], $percent);

									if ($percent >= 75) {
										$studentEducation = new StudentEducation($student['id_student_education']);

										if ($studentEducation->id_student_education_state < 7) {
											StudentEducation::changeEducationStepId($studentEducation->id, 7);
										}

										$studentEducation->first_connection = $value['first_connection'];
										$studentEducation->education_lenghts = $value['education_lenghts'];
										$studentEducation->update();
									}

									break;
								}

							}

						}

					}

				}

			}

			$result = [
				'success' => true,
				'message' => 'Les Dates de première connexion et temps de e learning ont été mise à jour avec succès',
			];

			die(Tools::jsonEncode($result));
		}

	}

	public function ajaxProcessGetUploadElement() {

		$uploadfile = Tools::getValue('uploadFile');

		if (file_exists($uploadfile)) {

			$fieldsImport = [];
			$fields = $this->getStudentEducationFields();
			$i = 1;

			foreach ($fields as $field) {

				if (isset($field['session']) && $field['session'] == 1) {
					$field['hidden'] = false;
					$fieldsImport[chr(64 + $i)] = $field;
					$i++;
				}

			}

			$inputFileType = 'Xlsx';
			$reader = IOFactory::createReader($inputFileType);
			$spreadsheet = $reader->load($uploadfile);
			$sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
			$uploadData = [];

			foreach ($sheetData as $key => $sheets) {

				if ($key == 1) {
					continue;
				}

				$colData = [];

				foreach ($sheets as $k => $value) {
					$kz = $fieldsImport[$k]['dataIndx'];
					$colData[$kz] = $value;
				}

				$uploadData[] = $colData;

			}

			die(Tools::jsonEncode($uploadData));

		}

	}

	public function ajaxProcessImportSession() {

		$sessionEntries = Tools::getValue('sessionEntry');

		foreach ($sessionEntries as $session) {

			$idSession = $session['id_student_education'];
			$education = new StudentEducation($idSession);
			$education->reference_edof = $session['reference_edof'];
			$education->identifiant = $session['identifiant'];
			$education->passwd_link = $session['passwd_link'];
			$education->shipping_number = $session['shipping_number'];
			$education->first_connection = Tools::convertFrenchDate($session['first_connection']);
			$education->education_lenghts = Tools::convertTime($session['education_lenghts']);
			$education->certification = $session['certification'];
			$education->update();
		}

		$result = [
			'success' => true,
			'message' => $this->l('Les sessions ont été mise à jour avec succès'),
		];

		die(Tools::jsonEncode($result));

	}

	public function ajaxProcessUncheckCertification() {

		$idEducation = Tools::getValue('idEducation');
		$education = new StudentEducation($idEducation);
		$education->certification = 0;
		$education->update();

		$result = [
			'success' => true,
			'message' => $this->l('La session a été mise à jour avec succès'),
		];

		die(Tools::jsonEncode($result));
	}

	public function ajaxProcessCheckCertification() {

		$idEducation = Tools::getValue('idEducation');
		$education = new StudentEducation($idEducation);
		$education->certification = 1;
		$education->update();

		$result = [
			'success' => true,
			'message' => $this->l('La session a été mise à jour avec succès'),
		];

		die(Tools::jsonEncode($result));
	}

	public function ajaxProcessExcelVdiGenerate() {

		$idSession = Tools::getvalue('idSession');

		$session = [
			'id_education_session' => $idSession,
		];

		$fields = $this->getStudentEducationFields();
		$titles = [];
		$dataIndx = [];

		foreach ($fields as $field) {

			if (isset($field['vdi']) && $field['vdi'] == 1) {
				$titles[] = $field['title'];
				$dataIndx[] = $field['dataIndx'];
			}

		}

		$lenght = count($titles);

		$column = chr(64 + count($titles));
		$titleStyle = [
			'font'    => [
				'bold' => true,
			],
			'borders' => [
				'bottom' => [
					'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
				],
			],
		];
		$vdiStyle = [
			'font'    => [
				'bold' => true,
			],
			'borders' => [
				'bottom' => [
					'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
				],
			],
		];
		$totalStyle = [
			'font'    => [
				'bold' => true,
			],
			'borders' => [
				'bottom' => [
					'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
				],
			],
		];
		$corpStyle = [
			'font' => [
				'bold' => false,
			],

		];

		$spreadsheet = new Spreadsheet();

		$spreadsheet->getActiveSheet()->getPageSetup()->setFitToWidth(1);
		$educationSession = new EducationSession($idSession);
		$session_date = $educationSession->session_date;

		if (file_exists(_PS_EXPORT_DIR_ . 'vdi_' . $session_date . '.xlsx')) {
			unlink(_PS_EXPORT_DIR_ . 'vdi_' . $session_date . '.xlsx');
		}

		$spreadsheet->getActiveSheet()->setTitle($educationSession->name);

		foreach ($titles as $key => $value) {
			$key++;
			$letter = chr(64 + $key);

			$spreadsheet->setActiveSheetIndex(0)
				->setCellValue($letter . '1', $value);

		}

		$spreadsheet->getActiveSheet()->getStyle('A1:' . $column . '1')->getAlignment()->setVertical(Alignment::HORIZONTAL_CENTER);
		$spreadsheet->getActiveSheet()->getStyle('A1:' . $column . '1')->applyFromArray($titleStyle);
		$spreadsheet->getActiveSheet()->getStyle('A1:' . $column . '1')->getFont()->setSize(12);
		$i = 2;
		$datas = $this->getStudentEducationDatasById($educationSession->id);
		$totalCommission = 0;

		foreach ($datas as $key => $educations) {
			$nbEducation = count($educations);

			if ($nbEducation == 0) {
				continue;
			}

			$commission = $nbEducation * 100;
			$totalCommission = $totalCommission + $commission;
			$vdi = new SaleAgent($key);
			$spreadsheet->setActiveSheetIndex(0)
				->setCellValue('A' . $i, $vdi->firstname . ' ' . $vdi->lastname);
			$spreadsheet->getActiveSheet()->mergeCells('A' . $i . ':' . $column . $i);
			$spreadsheet->getActiveSheet()->getStyle('A' . $i . ':' . $column . $i)->applyFromArray($vdiStyle);
			$i++;
			$commissionDue = 0;

			foreach ($educations as $education) {

				if ($education['connexionLenght'] == 1) {
					$commissionDue = $commissionDue + 100;
				}

			}

			foreach ($educations as $education) {

				foreach ($dataIndx as $k => $title) {

					if (array_key_exists($title, $education)) {
						$k++;
						$letter = chr(64 + $k);

						switch ($letter) {
						case 'H':
							$phpdate = strtotime($education[$title]);
							$mysqldate = date('d/m/Y', $phpdate);
							$value = $mysqldate;
							$spreadsheet->setActiveSheetIndex(0)
								->setCellValue($letter . $i, $value);
							break;

						default:
							$spreadsheet->setActiveSheetIndex(0)
								->setCellValue($letter . $i, $education[$title]);
							break;
						}

						$spreadsheet->getActiveSheet()->getColumnDimension($letter)->setAutoSize(true);
						$spreadsheet->getActiveSheet()->getStyle($letter . $i)->applyFromArray($corpStyle);

					}

				}

				$i++;
			}

			$spreadsheet->setActiveSheetIndex(0)
				->setCellValue('A' . $i, 'Total Commission théorique pour ' . $vdi->firstname . ' ' . $vdi->lastname . ' ' . $commission . ' €uros');
			$spreadsheet->getActiveSheet()->mergeCells('A' . $i . ':' . $column . $i);
			$spreadsheet->getActiveSheet()->getStyle('A' . $i)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
			$spreadsheet->getActiveSheet()->getStyle('A' . $i)->applyFromArray($titleStyle);
			$spreadsheet->getActiveSheet()->getStyle('A' . $i)->getFont()->setSize(12);
			$i++;
			$spreadsheet->setActiveSheetIndex(0)
				->setCellValue('A' . $i, 'Commission due pour ' . $vdi->firstname . ' ' . $vdi->lastname . ' ' . $commissionDue . ' €uros');
			$spreadsheet->getActiveSheet()->mergeCells('A' . $i . ':' . $column . $i);
			$spreadsheet->getActiveSheet()->getStyle('A' . $i)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
			$spreadsheet->getActiveSheet()->getStyle('A' . $i)->applyFromArray($titleStyle);
			$spreadsheet->getActiveSheet()->getStyle('A' . $i)->getFont()->setSize(12);
			$i++;

		}

		$spreadsheet->setActiveSheetIndex(0)
			->setCellValue('A' . $i, 'Total Commission pour cette la  : ' . $educationSession->name . ' :' . $totalCommission . ' €uros');
		$spreadsheet->getActiveSheet()->mergeCells('A' . $i . ':' . $column . $i);
		$spreadsheet->getActiveSheet()->getStyle('A' . $i)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
		$spreadsheet->getActiveSheet()->getStyle('A' . $i)->applyFromArray($titleStyle);
		$spreadsheet->getActiveSheet()->getStyle('A' . $i)->getFont()->setSize(12);

		$fileSave = new Xlsx($spreadsheet);
		$fileSave->save(_PS_EXPORT_DIR_ . 'vdi_' . $session_date . '.xlsx');
		$fileName = _PS_PROGRAM_DIR_ . 'programme' . str_replace(' ', '', $attachement) . '.pdf';
		$fileAttachement[] = [
			'content' => chunk_split(base64_encode(file_get_contents(_PS_EXPORT_DIR_ . 'vdi_' . $session_date . '.xlsx'))),
			'name'    => 'vdi_' . $session_date . '.xlsx',
		];
		$tpl = $this->context->smarty->createTemplate(_PS_MAIL_DIR_ . '/fr/vdi_report.tpl');
		$tpl->assign([
			'session_date' => $session_date,

		]);
		$postfields = [
			'sender'      => [
				'name'  => "Service  Administratif " . Configuration::get('PS_SHOP_NAME'),
				'email' => 'no-reply@' . Configuration::get('PS_SHOP_URL'),
			],
			'to'          => [

				[
					'name'  => Context::getContext()->company->firstname,
					'email' => Configuration::get('PS_SHOP_ADMIN_EMAIL'),
				],

			],
			'cc'          => [
				[
					'name'  => "Service  Administratif " . Configuration::get('PS_SHOP_NAME'),
					'email' => Configuration::get('PS_SHOP_EMAIL'),
				],
			],
			'subject'     => 'Rapport VDI pour la session du ' . $session_date,
			"htmlContent" => $tpl->fetch(),
			'attachment'  => $fileAttachement,
		];

		Tools::sendEmail($postfields);
		$result = [
			'success' => true,
			'message' => 'Un email récapitulatif vient d’être envoyé à la direction avec le rapport de session pour les VDI',
		];
		die(Tools::jsonEncode($result));
	}

	public function ajaxProcessExportSelection() {

		$educations = Tools::getvalue('idEducations');

		$fields = $this->configurationField;
		$titles = [];
		$dataIndx = [];

		foreach ($fields as $field) {

			if (isset($field['hidden']) && $field['hidden'] == 1) {
				continue;
			}

			$titles[] = $field['title'];
			$dataIndx[] = $field['dataIndx'];
		}

		$column = chr(64 + count($titles));
		$titleStyle = [
			'font'    => [
				'bold' => true,
			],
			'borders' => [
				'bottom' => [
					'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
				],
			],
		];
		$corpStyle = [
			'font' => [
				'bold' => false,
			],

		];

		$spreadsheet = new Spreadsheet();

		$spreadsheet->getActiveSheet()->getPageSetup()->setFitToWidth(1);

		foreach ($titles as $key => $value) {
			$key++;
			$letter = chr(64 + $key);

			$spreadsheet->setActiveSheetIndex(0)
				->setCellValue($letter . '1', $value);

		}

		$spreadsheet->getActiveSheet()->getStyle('A1:' . $column . '1')->getAlignment()->setVertical(Alignment::HORIZONTAL_CENTER);
		$spreadsheet->getActiveSheet()->getStyle('A1:' . $column . '1')->applyFromArray($titleStyle);
		$spreadsheet->getActiveSheet()->getStyle('A1:' . $column . '1')->getFont()->setSize(12);
		$i = 2;

		foreach ($educations as $key => $idEducation) {

			$studentEducation = new StudentEducation($idEducation);

			foreach ($dataIndx as $k => $title) {

				if (property_exists($studentEducation, $title)) {
					$k++;
					$letter = chr(64 + $k);
					$spreadsheet->setActiveSheetIndex(0)
						->setCellValue($letter . $i, $studentEducation->$title);

					$spreadsheet->getActiveSheet()->getColumnDimension($letter)->setAutoSize(true);
					$spreadsheet->getActiveSheet()->getStyle($letter . $i)->applyFromArray($corpStyle);

				}

			}

			$i++;
		}

		if (file_exists(_PS_EXPORT_DIR_ . 'exportSelection.xlsx')) {
			unlink(_PS_EXPORT_DIR_ . 'exportSelection.xlsx');
		}
		$tag = date("H-i-s");
		$fileSave = new Xlsx($spreadsheet);
		$fileSave->save(_PS_EXPORT_DIR_ . 'exportSelection' . $tag . '.xlsx');
		$response = [
			'fileExport' => 'fileExport' . DIRECTORY_SEPARATOR . 'exportSelection' . $tag . '.xlsx',
		];
		die(Tools::jsonEncode($response));

	}

	public function ajaxProcessGenerateExpeditionFile() {

		$idSession = Tools::getValue('idSession');
		$session = [
			'id_education_session' => $idSession,
		];

		$fields = $this->getStudentEducationFields();
		$titles = [];
		$dataIndx = [];

		foreach ($fields as $field) {

			if (isset($field['expedition']) && $field['expedition'] == 1) {
				$titles[] = $field['title'];
				$dataIndx[] = $field['dataIndx'];
			}

		}

		$column = chr(64 + count($titles));
		$titleStyle = [
			'font'    => [
				'bold' => true,
			],
			'borders' => [
				'bottom' => [
					'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
				],
			],
		];
		$corpStyle = [
			'font' => [
				'bold' => false,
			],

		];

		$spreadsheet = new Spreadsheet();

		$spreadsheet->getActiveSheet()->getPageSetup()->setFitToWidth(1);
		$educationSession = new EducationSession($idSession);
		$session_date = $educationSession->session_date;

		$spreadsheet->getActiveSheet()->setTitle($educationSession->name);

		foreach ($titles as $key => $value) {
			$key++;
			$letter = chr(64 + $key);

			$spreadsheet->setActiveSheetIndex(0)
				->setCellValue($letter . '1', $value);

		}

		$spreadsheet->getActiveSheet()->getStyle('A1:' . $column . '1')->getAlignment()->setVertical(Alignment::HORIZONTAL_CENTER);
		$spreadsheet->getActiveSheet()->getStyle('A1:' . $column . '1')->applyFromArray($titleStyle);
		$spreadsheet->getActiveSheet()->getStyle('A1:' . $column . '1')->getFont()->setSize(12);
		$i = 2;
		$datas = $this->getStudentEducationDatasByState($educationSession->id, 2);

		foreach ($datas as $educations) {

			foreach ($dataIndx as $k => $title) {

				if (array_key_exists($title, $educations)) {
					$k++;
					$letter = chr(64 + $k);

					switch ($letter) {
					case 'A':
						$phpdate = strtotime($educations[$title]);
						$mysqldate = date('d/m/Y', $phpdate);
						$value = $mysqldate;
						$spreadsheet->setActiveSheetIndex(0)
							->setCellValue($letter . $i, $value);
						break;

					default:
						$spreadsheet->setActiveSheetIndex(0)
							->setCellValue($letter . $i, $educations[$title]);
						break;
					}

					$spreadsheet->getActiveSheet()->getColumnDimension($letter)->setAutoSize(true);
					$spreadsheet->getActiveSheet()->getStyle($letter . $i)->applyFromArray($corpStyle);

				}

			}

			$i++;

		}

		if (file_exists(_PS_EXPORT_DIR_ . 'expedition_' . $session_date . '.xlsx')) {
			unlink(_PS_EXPORT_DIR_ . 'expedition_' . $session_date . '.xlsx');
		}

		$fileSave = new Xlsx($spreadsheet);
		$fileSave->save(_PS_EXPORT_DIR_ . 'expedition_' . $session_date . '.xlsx');

		$response = [
			'fileExport' => 'fileExport' . DIRECTORY_SEPARATOR . 'expedition_' . $session_date . '.xlsx',
		];
		die(Tools::jsonEncode($response));
	}

	public function getStudentEducationIndicateur($idSession) {

		$educationSession = new EducationSession($idSession);
		$date = new DateTime($educationSession->session_date);
		$dateStart = $date->format('d/m/Y');
		$students = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
			(new DbQuery())
				->select('s.*,  st.firstname, st.lastname, st.birthname, a.postcode, gl.name as title')
				->from('student_education', 's')
				->leftJoin('customer', 'st', 'st.`id_customer` = s.`id_customer`')
				->leftJoin('address', 'a', 'a.`id_customer` = s.`id_customer`')
				->leftJoin('student_education_state_lang', 'est', 'est.`id_student_education_state` = s.`id_student_education_state` AND est.`id_lang` = ' . $this->context->language->id)
				->leftJoin('education_session', 'es', 'es.`id_education_session` = s.`id_education_session`')
				->leftJoin('gender_lang', 'gl', 'gl.`id_gender` = st.`id_gender` AND gl.`id_lang` = ' . $this->context->language->id)
				->where('s.id_education_session = ' . $idSession)
				->where('s.deleted = 0')
				->orderBy('st.lastname')
		);

		foreach ($students as &$student) {

			$educations = Education::getEducationDetails($student['id_education'], $student['id_education_attribute'], false);

			foreach ($educations as $key => $value) {
				$student[$key] = $value;
			}

			$student['connexionLenght'] = (int) str_replace(":", "", $student['education_lenghts']) > 0 ? 1 : 0;

			if ($student['certification']) {
				$student['certification'] = 'Oui';
			} else {
				$student['certification'] = 'Non';
			}

			$student['dateStart'] = $dateStart;
			$lenght = explode(":", $student['education_lenghts']);
			$time = Tools::convertTimetoHex($lenght[0], $lenght[1]);
			$student['ratio'] = round($time * 100 / $student['hours'], 2) . ' %';
			$student['suivis'] = StudentEducationSuivie::getEducationSuivieById($student['id_student_education']);

		}

		return $students;

	}

	public function ajaxProcessExportIndicateur() {

		$idSession = Tools::getValue('idSession');

		$titles = ['Civilité', 'Nom', 'Nom de naissance', 'Prénom', 'Code Postal', 'Module et niveau', 'Date de départ en formation', 'Heures prévues de formation', 'Nombre d‘heures réalisée', 'Pourcentage d‘acquisition', 'Certification', 'Demande Certification'];
		$dataIndx = ['title', 'lastname', 'birthname', 'firstname', 'postcode', 'reference', 'dateStart', 'hours', 'education_lenghts', 'ratio', 'certificationName', 'certification'];

		$educationSession = new EducationSession($idSession);
		$session_date = $educationSession->session_date;

		$column = chr(64 + count($titles));
		$titleStyle = [
			'font'    => [
				'bold' => true,
			],
			'borders' => [
				'bottom' => [
					'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
				],
			],
		];
		$vdiStyle = [
			'font'    => [
				'bold' => true,
			],
			'borders' => [
				'bottom' => [
					'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
				],
			],
		];
		$totalStyle = [

			'font'    => [
				'bold' => true,
			],
			'borders' => [
				'bottom' => [
					'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
				],
			],
		];
		$corpStyle = [
			'font' => [
				'bold' => false,
			],

		];

		$spreadsheet = new Spreadsheet();
		$spreadsheet->createSheet();
		$spreadsheet->setActiveSheetIndex(0);
		$spreadsheet->getActiveSheet()->setTitle($educationSession->name);

		$i = 1;

		$spreadsheet->setActiveSheetIndex(0)
			->setCellValue('A' . $i, $educationSession->name);
		$spreadsheet->getActiveSheet()->mergeCells('A' . $i . ':' . $column . $i);
		$spreadsheet->getActiveSheet()->getStyle('A' . $i . ':' . $column . $i)->applyFromArray($vdiStyle);
		$spreadsheet->getActiveSheet()->getStyle('A1:' . $column . $i)->applyFromArray($titleStyle);
		$i++;

		$spreadsheet->getActiveSheet()->getPageSetup()->setFitToWidth(1);

		foreach ($titles as $key => $value) {
			$key++;
			$letter = chr(64 + $key);

			$spreadsheet->setActiveSheetIndex(0)
				->setCellValue($letter . $i, $value);

		}

		$spreadsheet->getActiveSheet()->getStyle('A1:' . $column . $i)->getAlignment()->setVertical(Alignment::HORIZONTAL_CENTER);
		$spreadsheet->getActiveSheet()->getStyle('A1:' . $column . $i)->applyFromArray($titleStyle);
		$spreadsheet->getActiveSheet()->getStyle('A1:' . $column . $i)->getFont()->setSize(12);
		$i = 3;
		$datas = $this->getStudentEducationIndicateur($educationSession->id);

		foreach ($datas as $educations) {
			$customer = new Customer($educations['id_customer']);

			foreach ($dataIndx as $k => $title) {

				if (array_key_exists($title, $educations)) {
					$k++;
					$letter = chr(64 + $k);
					$spreadsheet->setActiveSheetIndex(0)
						->setCellValue($letter . $i, $educations[$title]);

					$spreadsheet->getActiveSheet()->getColumnDimension($letter)->setAutoSize(true);
					$spreadsheet->getActiveSheet()->getStyle($letter . $i)->applyFromArray($corpStyle);

				}

			}

			$i++;

		}

		$spreadsheet->createSheet();
		$spreadsheet->setActiveSheetIndex(1);
		$spreadsheet->getActiveSheet()->setTitle('Détail du suivi étudiant');
		$i = 1;
		$spreadsheet->setActiveSheetIndex(1)
			->setCellValue('A' . $i, "Date");

		$spreadsheet->setActiveSheetIndex(1)
			->setCellValue('B' . $i, "Action");

		$spreadsheet->setActiveSheetIndex(1)
			->setCellValue('C' . $i, "Type");
		$spreadsheet->getActiveSheet()->mergeCells('F' . $i . ':' . "I" . $i);

		$i++;

		foreach ($datas as $educations) {
			$customer = new Customer($educations['id_customer']);
			$spreadsheet->setActiveSheetIndex(1)
				->setCellValue('A' . $i, "Détails du suivi étudiant pour " . $customer->firstname . " " . $customer->lastname);
			$spreadsheet->getActiveSheet()->mergeCells('A' . $i . ':' . $column . $i);
			$spreadsheet->getActiveSheet()->getStyle('A' . $i . ':' . $column . $i)->applyFromArray($vdiStyle);
			$spreadsheet->getActiveSheet()->getStyle('A1:' . $column . $i)->applyFromArray($titleStyle);
			$i++;

			foreach ($educations['suivis'] as $suivi) {
				$date = new DateTime($suivi['suivie_date']);
				$value = $date->format('d/m/Y');
				$spreadsheet->setActiveSheetIndex(1)
					->setCellValue('A' . $i, $value);

				$spreadsheet->setActiveSheetIndex(1)
					->setCellValue('B' . $i, $suivi['content']);

				$spreadsheet->setActiveSheetIndex(1)
					->setCellValue('C' . $i, $suivi['type']);

				$i++;
			}

		}

		$spreadsheet->setActiveSheetIndex(0);
		$sheetIndex = $spreadsheet->getIndex(
			$spreadsheet->getSheetByName('Worksheet')
		);
		$spreadsheet->removeSheetByIndex($sheetIndex);

		$fileSave = new Xlsx($spreadsheet);
		$fileSave->save(_PS_EXPORT_DIR_ . 'exportIndicateur_' . $session_date . '.xlsx');
		$response = [
			'fileExport' => 'fileExport' . DIRECTORY_SEPARATOR . 'exportIndicateur_' . $session_date . '.xlsx',
		];
		die(Tools::jsonEncode($response));
	}

	public function ajaxProcessExportSession() {

		$idSession = Tools::getValue('idSession');
		$session = [
			'id_education_session' => $idSession,
		];

		$fields = $this->getStudentEducationFields();
		$titles = [];
		$dataIndx = [];

		foreach ($fields as $field) {

			if (isset($field['session']) && $field['session'] == 1) {
				$titles[] = $field['title'];
				$dataIndx[] = $field['dataIndx'];
			}

		}

		$column = chr(64 + count($titles));
		$titleStyle = [
			'font'    => [
				'bold' => true,
			],
			'borders' => [
				'bottom' => [
					'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
				],
			],
		];
		$corpStyle = [
			'font' => [
				'bold' => false,
			],

		];

		$spreadsheet = new Spreadsheet();

		$spreadsheet->getActiveSheet()->getPageSetup()->setFitToWidth(1);
		$educationSession = new EducationSession($idSession);
		$session_date = $educationSession->session_date;

		$spreadsheet->getActiveSheet()->setTitle($educationSession->name);

		foreach ($titles as $key => $value) {
			$key++;
			$letter = chr(64 + $key);

			$spreadsheet->setActiveSheetIndex(0)
				->setCellValue($letter . '1', $value);

		}

		$spreadsheet->getActiveSheet()->getStyle('A1:' . $column . '1')->getAlignment()->setVertical(Alignment::HORIZONTAL_CENTER);
		$spreadsheet->getActiveSheet()->getStyle('A1:' . $column . '1')->applyFromArray($titleStyle);
		$spreadsheet->getActiveSheet()->getStyle('A1:' . $column . '1')->getFont()->setSize(12);
		$i = 2;
		$datas = $this->getStudentEducationDatas($educationSession->id);

		foreach ($datas as $educations) {

			foreach ($dataIndx as $k => $title) {

				if (array_key_exists($title, $educations)) {
					$k++;
					$letter = chr(64 + $k);

					switch ($letter) {
					case 'D':
						$phpdate = strtotime($educations[$title]);
						$mysqldate = date('d/m/Y', $phpdate);
						$value = $mysqldate;
						$spreadsheet->setActiveSheetIndex(0)
							->setCellValue($letter . $i, $value);
						break;

					default:
						$spreadsheet->setActiveSheetIndex(0)
							->setCellValue($letter . $i, $educations[$title]);
						break;
					}

					$spreadsheet->getActiveSheet()->getColumnDimension($letter)->setAutoSize(true);
					$spreadsheet->getActiveSheet()->getStyle($letter . $i)->applyFromArray($corpStyle);

				}

			}

			$i++;

		}

		if (file_exists(_PS_EXPORT_DIR_ . 'exportSession_' . $session_date . '.xlsx')) {
			unlink(_PS_EXPORT_DIR_ . 'exportSession_' . $session_date . '.xlsx');
		}

		$fileSave = new Xlsx($spreadsheet);
		$fileSave->save(_PS_EXPORT_DIR_ . 'exportSession_' . $session_date . '.xlsx');
		$response = [
			'fileExport' => 'fileExport' . DIRECTORY_SEPARATOR . 'exportSession_' . $session_date . '.xlsx',
		];
		die(Tools::jsonEncode($response));

		//$fileSave = new Xlsx($spreadsheet);
		//$fileSave->save(_PS_EXPORT_DIR_ . 'export.xlsx');
	}

	public function ajaxProcessDeleteFile() {

		$file = Tools::getValue('file');

		if (file_exists(_PS_EXPORT_DIR_ . basename($file))) {
			unlink(_PS_EXPORT_DIR_ . basename($file));
		}

		die(true);

	}

	public function ajaxProcessLanguageFile() {

		$idSession = Tools::getValue('idSession');

		$educationLangues = [
			1 => 'ANGLAIS',
			2 => 'ITALIEN',
			3 => 'ESPAGNOL',
			4 => 'ALLEMAND',
			5 => 'FRANCAIS',
		];
		$educations = [];

		foreach ($educationLangues as $key => $langue) {

			$educations[$key] = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
				(new DbQuery())
					->select('s.id_student_education, st.firstname, st.lastname, st.email, st.password')
					->from('student_education', 's')
					->leftJoin('customer', 'st', 'st.`id_customer` = s.`id_customer`')
					->where('s.deleted = 0')
					->where('s.id_education = ' . (int) $key)
					->where('s.id_education_session = ' . (int) $idSession)
					->orderBy('st.lastname')
			);

		}

		foreach ($educations as $key => $education) {
			$name = $educationLangues[$key];
			$spreadsheet = new Spreadsheet();
			$spreadsheet->getActiveSheet()->getPageSetup()->setFitToWidth(1);
			$spreadsheet->getActiveSheet()->setTitle($name);

			$spreadsheet->setActiveSheetIndex(0)->setCellValue('A1', 'Nom');
			$spreadsheet->setActiveSheetIndex(0)->setCellValue('B1', 'Prenom');
			$spreadsheet->setActiveSheetIndex(0)->setCellValue('C1', 'Email');
			$spreadsheet->setActiveSheetIndex(0)->setCellValue('D1', 'Langue');
			$spreadsheet->setActiveSheetIndex(0)->setCellValue('E1', 'Identifiant');
			$spreadsheet->setActiveSheetIndex(0)->setCellValue('F1', 'Mot de Passe');
			$spreadsheet->setActiveSheetIndex(0)->setCellValue('G1', 'Id Interne');
			$i = 2;

			foreach ($education as $stagiaire) {
				$spreadsheet->setActiveSheetIndex(0)->setCellValue('A' . $i, $stagiaire['lastname']);
				$spreadsheet->setActiveSheetIndex(0)->setCellValue('B' . $i, $stagiaire['firstname']);
				$spreadsheet->setActiveSheetIndex(0)->setCellValue('C' . $i, $stagiaire['email']);
				$spreadsheet->setActiveSheetIndex(0)->setCellValue('D' . $i, 'FRE');
				$spreadsheet->setActiveSheetIndex(0)->setCellValue('E' . $i, $stagiaire['email']);
				$spreadsheet->setActiveSheetIndex(0)->setCellValue('F' . $i, $stagiaire['password']);
				$spreadsheet->setActiveSheetIndex(0)->setCellValue('G' . $i, $stagiaire['id_student_education']);
				$i = $i + 1;
			}

			$spreadsheet->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
			$spreadsheet->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
			$spreadsheet->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
			$spreadsheet->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
			$spreadsheet->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
			$spreadsheet->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);
			$spreadsheet->getActiveSheet()->getColumnDimension('G')->setAutoSize(true);

			$fileSave = new Xlsx($spreadsheet);
			$fileSave->save(_PS_EXPORT_DIR_ . 'inscription_7s_' . $name . '.xlsx');
		}

		$zip = new ZipArchive;

		if ($zip->open(_PS_EXPORT_DIR_ . 'inscription_7s.zip', ZipArchive::CREATE) === TRUE) {

			foreach ($educationLangues as $key => $langue) {
				$zip->addFile(_PS_EXPORT_DIR_ . 'inscription_7s_' . $langue . '.xlsx', basename(_PS_EXPORT_DIR_ . 'inscription_7s_' . $langue . '.xlsx'));
			}

			$zip->close();

			foreach ($educationLangues as $key => $langue) {
				UNLINK(_PS_EXPORT_DIR_ . 'inscription_7s_' . $langue . '.xlsx');
			}

			$response = [
				'fileExport' => 'fileExport' . '/inscription_7s.zip',
			];
			die(Tools::jsonEncode($response));

		}

	}

	public function ajaxProcessOfficeFile() {

		$idSession = Tools::getValue('idSession');
		StudentEducation::syncAlterSession($idSession);

		die(Tools::jsonEncode($result));

	}

	public function ajaxProcessUpdateEducation() {

		$id_student_education = Tools::getValue('id_student_education');

		$education = new StudentEducation($id_student_education);

		foreach ($_POST as $key => $value) {

			if (property_exists($education, $key) && $key != 'id_student_education') {

				if (Tools::getValue('id_student_education') && empty($value)) {
					continue;
				}

				if ($key == 'date_start' && !empty($value)) {
					$date = DateTime::createFromFormat('d/m/Y', $value);
					$value = date_format($date, "Y-m-d");
				}

				if ($key == 'date_end' && !empty($value)) {
					$date = DateTime::createFromFormat('d/m/Y', $value);
					$value = date_format($date, "Y-m-d");
				}

				$education->{$key}

				= $value;

			}

		}

		if ($education->id_education_session > 0) {
			$days = Education::getDaysEducation($education->id_education, $education->id_education_attribute);
			$session = new EducationSession($education->id_education_session);
			$date = new DateTime($session->session_date);
			$date->modify('+' . $days . ' days');
			$education->date_start = $session->session_date;
			$education->date_end = $date->format('Y-m-d');
		}

		$result = $education->update();

		if ($result) {

			$return = [
				'success' => true,
				'message' => $this->l('La session de formation a été mise à jour avec succès'),
			];
		} else {
			$return = [
				'success' => false,
				'message' => $this->l('Une erreur s\'est produite en essayant de mettre à jour cette session de formation'),
			];
		}

		die(Tools::jsonEncode($return));
	}

	public function ajaxProcessEditStudent() {

		$id_student = Tools::getValue('id_student');
		$this->object = new Customer($id_student);
		$context = Context::getContext();

		$data = $this->createTemplate('controllers/customers/editCustomer.tpl');

		$address = null;
		$addresses = Address::getBAddressesByCustomerId($this->object->id);
		$id_address = Address::getFirstCustomerAddressId($this->object->id);

		if ($id_address > 0) {
			$address = new Address((int) $id_address);
		}

		$genders = Gender::getGenders();

		$tarifs = Customer::getTarifs();

		$groups = Group::getGroups($this->default_form_language, true);

		$this->context->customer = $this->object;

		$customerStats = $this->object->getStats();

		if ($total_customer = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
			(new DbQuery())
			->select('SUM(`total_paid_real`)')
			->from('orders')
			->where('`id_customer` = ' . (int) $this->object->id)
			->where('`valid` = 1')
		)) {
			Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
				(new DbQuery())
					->select('SQL_CALC_FOUND_ROWS COUNT(*)')
					->from('orders')
					->where('`valid` = 1')
					->where('`id_customer` != ' . (int) $this->object->id)
					->groupBy('id_customer')
					->having('SUM(`total_paid_real`) > ' . (int) $total_customer)
			);
			$countBetterCustomers = (int) Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('SELECT FOUND_ROWS()') + 1;
		} else {
			$countBetterCustomers = '-';
		}

		$orders = CustomerPieces::getOrdersbyIdCustomer($this->object->id);
		$totalOrders = count($orders);

		$customerTurnover = CustomerPieces::getOrderTotalbyIdCustomer($this->object->id);

		$messages = CustomerThread::getCustomerMessages((int) $this->object->id);

		$totalMessages = count($messages);

		for ($i = 0; $i < $totalMessages; $i++) {
			$messages[$i]['message'] = substr(strip_tags(html_entity_decode($messages[$i]['message'], ENT_NOQUOTES, 'UTF-8')), 0, 75);
			$messages[$i]['date_add'] = Tools::displayDate($messages[$i]['date_add'], null, true);

			if (isset(static::$meaning_status[$messages[$i]['status']])) {
				$messages[$i]['status'] = static::$meaning_status[$messages[$i]['status']];
			}

		}

		$products = $this->object->getBoughtProducts();

		$carts = Cart::getCustomerCarts($this->object->id);
		$totalCarts = count($carts);

		for ($i = 0; $i < $totalCarts; $i++) {
			$cart = new Cart((int) $carts[$i]['id_cart']);
			$this->context->cart = $cart;
			$currency = new Currency((int) $carts[$i]['id_currency']);
			$this->context->currency = $currency;
			$summary = $cart->getSummaryDetails();
			$carrier = new Carrier((int) $carts[$i]['id_carrier']);
			$carts[$i]['id_cart'] = sprintf('%06d', $carts[$i]['id_cart']);
			$carts[$i]['date_add'] = Tools::displayDate($carts[$i]['date_add'], null, true);
			$carts[$i]['total_price'] = Tools::displayPrice($summary['total_price'], $currency);
			$carts[$i]['name'] = $carrier->name;
		}

		$this->context->currency = Currency::getDefaultCurrency();

		$sql = 'SELECT DISTINCT cp.id_product, c.id_cart, c.id_shop, cp.id_shop AS cp_id_shop
                FROM ' . _DB_PREFIX_ . 'cart_product cp

                JOIN ' . _DB_PREFIX_ . 'cart c ON (c.id_cart = cp.id_cart)
                JOIN ' . _DB_PREFIX_ . 'product p ON (cp.id_product = p.id_product)
                WHERE c.id_customer = ' . (int) $this->object->id . '
                    AND NOT EXISTS (
                            SELECT 1
                            FROM ' . _DB_PREFIX_ . 'orders o
                            JOIN ' . _DB_PREFIX_ . 'order_detail od ON (o.id_order = od.id_order)
                            WHERE product_id = cp.id_product AND o.valid = 1 AND o.id_customer = ' . (int) $this->object->id . '
                        )';
		$interested = Db::getInstance()->executeS($sql);
		$totalInterested = count($interested);

		for ($i = 0; $i < $totalInterested; $i++) {
			$product = new Product($interested[$i]['id_product'], false, $this->default_form_language, $interested[$i]['id_shop']);

			if (!Validate::isLoadedObject($product)) {
				continue;
			}

			$interested[$i]['url'] = $this->context->link->getProductLink(
				$product->id,
				$product->link_rewrite,
				Category::getLinkRewrite($product->id_category_default, $this->default_form_language),
				null,
				null,
				$interested[$i]['cp_id_shop']
			);
			$interested[$i]['id'] = (int) $product->id;
			$interested[$i]['name'] = Tools::htmlentitiesUTF8($product->name);
		}

		$emails = $this->object->getLastEmails();

		$connections = $this->object->getLastConnections();

		if (!is_array($connections)) {
			$connections = [];
		}

		$totalConnections = count($connections);

		for ($i = 0; $i < $totalConnections; $i++) {
			$connections[$i]['http_referer'] = $connections[$i]['http_referer'] ? preg_replace('/^www./', '', parse_url($connections[$i]['http_referer'], PHP_URL_HOST)) : $this->l('Direct link');
		}

		$referrers = Referrer::getReferrers($this->object->id);
		$totalReferrers = count($referrers);

		for ($i = 0; $i < $totalReferrers; $i++) {
			$referrers[$i]['date_add'] = Tools::displayDate($referrers[$i]['date_add'], null, true);
		}

		$groups = $this->object->getGroups();
		$customerGroups = [];

		foreach ($groups as $group) {

			$customerGroups[] = $group;
		}

		$totalGroups = count($groups);

		for ($i = 0; $i < $totalGroups; $i++) {
			$group = new Group($groups[$i]);
			$groups[$i] = [];
			$groups[$i]['id_group'] = $group->id;
			$groups[$i]['name'] = $group->name[$this->default_form_language];
		}

		$allgroups = Group::getGroups($this->default_form_language, true);

		$data->assign([
			'customer'               => $this->object,
			'taxModes'               => TaxMode::getTaxModes(),
			'currency'               => $context->currency,
			'countries'              => Country::getCountries($this->context->language->id, false),
			'default_country'        => Configuration::get('PS_COUNTRY_DEFAULT'),
			'taxes'                  => Tax::getRulesTaxes($this->context->language->id),
			'tarifs'                 => Customer::getTarifs(),
			'genders'                => Gender::getGenders(),
			'paymentModes'           => PaymentMode::getPaymentModes(),
			'addresses'              => $addresses,
			'registration_date'      => Tools::displayDate($customer->date_add, null, true),
			'customer_stats'         => $customerStats,
			'last_visit'             => Tools::displayDate($customerStats['last_visit'], null, true),
			'count_better_customers' => $countBetterCustomers,
			'messages'               => $messages,
			'groups'                 => $groups,
			'allgroups'              => $allgroups,
			'customerGroups'         => $customerGroups,
			'courses'                => Customer::getStudentEducations($this->object->id),
			'orders'                 => $orders,
			'totalOrders'            => $totalOrders,
			'customerTurnover'       => $customerTurnover,
			'products'               => $products,
			'carts'                  => $carts,
			'interested'             => $interested,
			'connections'            => $connections,
			'referrers'              => $referrers,
			'link'                   => $this->context->link,
			'id_tab'                 => $this->identifier_value,
			'formId'                 => 'form-' . $this->table,

		]);

		$li = '<li id="uperEditCustomer" data-controller="' . $this->controller_name . '"><a href="#contentEditCustomer">Voir ou éditer ' . $this->object->firstname . ' ' . $this->object->lastname . '</a><button type="button" class="close tabdetail" onClick="cancelViewCustomer();" data-id="uperEditCustomer"><i class="icon icon-times-circle"></i></button></li>';
		$html = '<div id="contentEditCustomer" class="panel col-lg-12" style="display; flow-root;">' . $data->fetch() . '</div>';

		$result = [
			'li'   => $li,
			'html' => $html,
		];

		die(Tools::jsonEncode($result));

	}

	public function ajaxProcessOpenWindowPrerequis() {

		$idSession = Tools::getValue('idSession');
		$studentEducation = new StudentEducation($idSession);
		$idPrerequis = Tools::getValue('idPrerequis');
		$prerequis = new StudentEducationPrerequis($idPrerequis);

		$student = new Student($studentEducation->id_student);
		$customer = new Customer($studentEducation->id_customer);
		$educationPrerequis = new EducationPrerequis($prerequis->id_education_prerequis, 1);

		$values = [];

		foreach ($educationPrerequis->content as $question) {

			if (array_key_exists($question['name'], $prerequis->content)) {
				$values[] = [
					'name'   => $question['name'],
					'type'   => $question['type'],
					'value'  => $question['values'],
					'answer' => $prerequis->content[$question['name']],
				];
			}

		}

		$data = $this->createTemplate('controllers/student_educations/prerequis.tpl');
		$data->assign('studentEducation', $studentEducation);
		$data->assign('prerequis', $values);
		$data->assign('studentPrerequis', $prerequis);
		$data->assign('student', $customer);
		$data->assign('educationPrerequis', $educationPrerequis);
		$data->assign('bo_imgdir', '/administration/themes/' . $this->bo_theme . '/img/');
		$data->assign('link', $this->renderPrerequisPdf($prerequis));
		$result = [
			'html'    => $data->fetch(),
			'success' => true,
		];
		die(Tools::jsonEncode($result));
	}

	public function ajaxProcessOpenWindowDocuments() {

		$context = Context::getContext();
		$idSession = Tools::getValue('idSession');
		$studentEducation = new StudentEducation($idSession);
		$customer = new Customer($studentEducation->id_customer);

		if ($studentEducation->id_education_prerequis > 0) {

			$prerequis = new StudentEducationPrerequis($studentEducation->id_education_prerequis);
			$educationPrerequis = new EducationPrerequis($prerequis->id_education_prerequis, 1);
			$values = [];

			foreach ($educationPrerequis->content as $question) {

				if (array_key_exists($question['name'], $prerequis->content)) {
					$values[] = [
						'name'   => $question['name'],
						'type'   => $question['type'],
						'value'  => $question['values'],
						'answer' => $prerequis->content[$question['name']],
					];
				}

			}

			$data = $this->createTemplate('controllers/student_educations/prerequis.tpl');
			$data->assign('studentEducation', $studentEducation);
			$data->assign('prerequis', $values);
			$data->assign('studentPrerequis', $prerequis);
			$data->assign('student', $customer);
			$data->assign('educationPrerequis', $educationPrerequis);
			$data->assign('bo_imgdir', '/administration/themes/' . $this->bo_theme . '/img/');
			$documents[] = [
				'title'    => 'Prérequis étudiant',
				'document' => $data->fetch(),
				'link'     => $this->renderPrerequisPdf($prerequis),
			];
		}

		if ($studentEducation->id_student_education_state > 7) {

			$template = 'attestation';

			$tpl = $context->smarty->createTemplate(_PS_MAIL_DIR_ . '/fr/' . $template . '.tpl');

			$tpl->assign([
				'studentEducation' => $studentEducation,
				'student'          => $customer,
				'company'          => $context->company,
			]);
			$attestation = $tpl->fetch();
			$tpl = $context->smarty->createTemplate(_PS_MAIL_DIR_ . '/fr/certificat.tpl');
			$tpl->assign([
				'studentEducation' => $studentEducation,
				'student'          => $customer,
				'company'          => $context->company,
			]);
			$certificat = $tpl->fetch();
			$documents[] = [
				'title'    => 'ATTESTATION D’ASSIDUITE',
				'document' => $attestation,
				'link'     => $this->processPrintAttestation($idSession),
			];
			$documents[] = [
				'title'    => 'CERTIFICAT DE REALISATION',
				'document' => $certificat,
				'link'     => $this->processPrintAttestation($idSession, 1),
			];

			if ($studentEducation->eval_hot == 1) {

				$idEvaluation = StudentEvaluation::getEvaluationHotByIdSession($studentEducation->id);
				$evaluation = new StudentEvaluation($idEvaluation);
				$contents = unserialize($evaluation->content);
				$student = new Customer($evaluation->id_customer);

				$tpl = $context->smarty->createTemplate(_PS_MAIL_DIR_ . '/fr/evaluation_hot_render.tpl');

				$tpl->assign([
					'studentEducation' => $studentEducation,
					'student'          => $student,
					'check'            => _THEME_IMG_DIR_ . 'check.png',
					'angry'            => _THEME_IMG_DIR_ . 'angry.png',
					'unhappy'          => _THEME_IMG_DIR_ . 'unhappy.png',
					'happy'            => _THEME_IMG_DIR_ . 'happy.png',
					'great'            => _THEME_IMG_DIR_ . 'great.png',
				]);

				foreach ($contents as $key => $value) {
					$tpl->assign($key, trim($value));
				}

				$evalHot = $tpl->fetch();

				$documents[] = [
					'title'    => 'Evaluation à chaud',
					'document' => $evalHot,
					'link'     => $this->printEvaluationHot($customer, $studentEducation, $evaluation),
				];
			}

		}

		$data = $this->createTemplate('controllers/student_educations/attestation.tpl');
		$data->assign('documents', $documents);
		$data->assign('studentEducation', $studentEducation);
		$data->assign('student', $customer);
		$result = [
			'html'    => $data->fetch(),
			'success' => true,
		];
		die(Tools::jsonEncode($result));
	}

	public function printEvaluationHot(Customer $student, StudentEducation $studentEducation, StudentEvaluation $evaluation) {

		$context = Context::getContext();
		$contents = unserialize($evaluation->content);
		$idShop = $this->context->shop->id;

		if (Configuration::get('PS_LOGO_INVOICE', null, null, $idShop) != false && file_exists(_PS_IMG_DIR_ . Configuration::get('PS_LOGO_INVOICE', null, null, $idShop))) {
			$logo = _PS_IMG_DIR_ . Configuration::get('PS_LOGO_INVOICE', null, null, $idShop);
			$logo_path = _PS_IMG_ . Configuration::get('PS_LOGO_INVOICE', null, null, $idShop);
		} else

		if (Configuration::get('PS_LOGO', null, null, $idShop) != false && file_exists(_PS_IMG_DIR_ . Configuration::get('PS_LOGO', null, null, $idShop))) {
			$logo = _PS_IMG_DIR_ . Configuration::get('PS_LOGO', null, null, $idShop);
			$logo_path = _PS_IMG_ . Configuration::get('PS_LOGO', null, null, $idShop);
		}

		$width = 0;
		$height = 0;

		if (!empty($logo_path)) {
			list($width, $height) = getimagesize(_PS_ROOT_DIR_ . $logo_path);
		}

		$maximumHeight = 100;

		if ($height > $maximumHeight) {
			$ratio = $maximumHeight / $height;
			$height *= $ratio;
			$width *= $ratio;
		}

		$mpdf = new \Mpdf\Mpdf([
			'margin_left'   => 10,
			'margin_right'  => 10,
			'margin_top'    => 100,
			'margin_bottom' => 30,
			'margin_header' => 10,
			'margin_footer' => 10,
		]);

		$data = $this->createTemplate('controllers/student_educations/evaluation/headerEvaluation.tpl');

		$data->assign(
			[
				'logo_path'        => $logo_path,
				'student'          => $student,
				'studentEducation' => $studentEducation,
				'evaluation'       => $evaluation,
			]
		);
		$mpdf->SetHTMLHeader($data->fetch());

		$data = $this->createTemplate('controllers/student_educations/evaluation/footer.tpl');

		$data->assign(
			[
				'launchRef' => 'Fiche d’évaluation à froid V.1.1.22.04.21',
				'tags'      => Configuration::get('EPH_FOOTER_EMAIL'),
				'company'   => $this->context->company,
			]
		);
		$mpdf->SetHTMLFooter($data->fetch(), 'O');

		$data = $this->createTemplate('controllers/student_educations/pdf.css.tpl');
		$data->assign(
			[
				'color' => '#fff',
			]
		);
		$stylesheet = $data->fetch();

		$data = $this->createTemplate('controllers/student_educations/evaluation/evaluation.tpl');

		foreach ($contents as $key => $value) {
			$data->assign($key, trim($value));
		}

		$data->assign(
			[
				'check'   => _THEME_IMG_DIR_ . 'check.png',
				'angry'   => _THEME_IMG_DIR_ . 'angry.png',
				'unhappy' => _THEME_IMG_DIR_ . 'unhappy.png',
				'happy'   => _THEME_IMG_DIR_ . 'happy.png',
				'great'   => _THEME_IMG_DIR_ . 'great.png',
			]
		);

		$filePath = _PS_PDF_STUDENT_DIR_;
		$fileName = $studentEducation->id . '_evaluation.pdf';

		if (file_exists($filePath . $fileName)) {
			unlink($filePath . $fileName);
		}

		$mpdf->SetTitle($studentEducation->id . '_evaluation');
		$mpdf->SetAuthor($context->company->company_name);

		$mpdf->SetDisplayMode('fullpage');

		$mpdf->WriteHTML($stylesheet, \Mpdf\HTMLParserMode::HEADER_CSS);
		$mpdf->WriteHTML($data->fetch());

		$mpdf->Output($filePath . $fileName, 'F');

		$fileToUpload = '../pdfStudent' . DIRECTORY_SEPARATOR . $fileName;
		$link = '<a  target="_blank"  class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" id="pieceDownloadFile" class="btn btn-default" href="' . $fileToUpload . '">Exporter au format PDF</a>';

		return $link;
	}

	public function processPrintAttestation($idSession, $type = false) {

		$context = Context::getContext();
		$studentEducation = new StudentEducation($idSession);
		$student = new Customer($studentEducation->id_customer);

		if ($type) {
			$header = 'headerAttestation';
			$template = 'attestation';
			$ref = 'Attestation Assiduité V 16.01.2022';
			$fileName = $studentEducation->id . '_assiduite.pdf';
		} else {
			$header = 'header';
			$template = 'certificat';
			$ref = 'Certificat de réalisation V 16.01.2022';
			$fileName = $studentEducation->id . '_realisation.pdf';

		}

		$idShop = $this->context->shop->id;

		if (Configuration::get('PS_LOGO_INVOICE', null, null, $idShop) != false && file_exists(_PS_IMG_DIR_ . Configuration::get('PS_LOGO_INVOICE', null, null, $idShop))) {
			$logo = _PS_IMG_DIR_ . Configuration::get('PS_LOGO_INVOICE', null, null, $idShop);
			$logo_path = _PS_IMG_ . Configuration::get('PS_LOGO_INVOICE', null, null, $idShop);
		} else

		if (Configuration::get('PS_LOGO', null, null, $idShop) != false && file_exists(_PS_IMG_DIR_ . Configuration::get('PS_LOGO', null, null, $idShop))) {
			$logo = _PS_IMG_DIR_ . Configuration::get('PS_LOGO', null, null, $idShop);
			$logo_path = _PS_IMG_ . Configuration::get('PS_LOGO', null, null, $idShop);
		}

		$width = 0;
		$height = 0;

		if (!empty($logo_path)) {
			list($width, $height) = getimagesize(_PS_ROOT_DIR_ . $logo_path);
		}

		$maximumHeight = 100;

		if ($height > $maximumHeight) {
			$ratio = $maximumHeight / $height;
			$height *= $ratio;
			$width *= $ratio;
		}

		$mpdf = new \Mpdf\Mpdf([
			'margin_left'   => 10,
			'margin_right'  => 10,
			'margin_top'    => 60,
			'margin_bottom' => 30,
			'margin_header' => 10,
			'margin_footer' => 10,
		]);

		$data = $this->createTemplate('controllers/student_educations/attestations/' . $header . '.tpl');

		$data->assign(
			[

				'logo_path' => $logo_path,
			]
		);
		$mpdf->SetHTMLHeader($data->fetch());

		$data = $this->createTemplate('controllers/student_educations/attestations/footer.tpl');

		$data->assign(
			[
				'version'    => $ref,
				'tag_footer' => Configuration::get('EPH_FOOTER_PROGRAM'),
				'tags'       => Configuration::get('EPH_FOOTER_EMAIL'),
				'company'    => $this->context->company,
			]
		);
		$mpdf->SetHTMLFooter($data->fetch(), 'O');

		$data = $this->createTemplate('controllers/student_educations/pdf.css.tpl');
		$data->assign(
			[
				'color' => '#fff',
			]
		);
		$stylesheet = $data->fetch();

		$data = $this->createTemplate('controllers/student_educations/attestations/' . $template . '.tpl');

		$data->assign(
			[
				'title'            => $student->title,
				'student'          => $student,
				'studentEducation' => $studentEducation,
				'company'          => $context->company,
				'logo_tampon'      => _PS_ROOT_DIR_ . '/img/' . Configuration::get('EPH_SOURCE_STAMP'),
			]
		);

		$filePath = _PS_PDF_STUDENT_DIR_;

		$mpdf->SetTitle($emplate);
		$mpdf->SetAuthor($this->context->company->company_name);

		$mpdf->SetDisplayMode('fullpage');

		$mpdf->WriteHTML($stylesheet, \Mpdf\HTMLParserMode::HEADER_CSS);
		$mpdf->WriteHTML($data->fetch());

		$mpdf->Output($filePath . $fileName, 'F');

		$fileToUpload = '..' . DIRECTORY_SEPARATOR . 'pdfStudent' . DIRECTORY_SEPARATOR . $fileName;
		$link = '<a  target="_blank"  class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" id="pieceDownloadFile" class="btn btn-default" href="' . $fileToUpload . '">' . $this->l('Exporter au format PDF') . '</a>';
		return $link;

	}

	public function ajaxProcessOpenWindowSuivie() {

		$idSession = Tools::getValue('idSession');
		$studentEducation = new StudentEducation($idSession);

		$student = new Customer($studentEducation->id_customer);
		$context = Context::getContext();

		$linkContract = '';

		$suivies = StudentEducationSuivie::getEducationSuivieById($studentEducation->id);

		foreach ($suivies as &$suivie) {
			$suivie['link'] = '';

			if ($suivie['email_title'] == '') {
				$suivie['has_email'] = 0;
			} else {
				$suivie['has_email'] = 1;
				$link = $this->processPrintEmail($suivie['id_student_education_suivie']);
				$suivie['email_content'] = $this->processPrintEmail($suivie['id_student_education_suivie'], true);

				if (strpos($link, '.pdf') !== false) {
					$suivie['link'] = $link;
				} else {
					$suivie['link'] = false;
				}

			}

			if ($suivie['sms_content'] == '') {
				$suivie['has_sms'] = 0;
			} else {
				$suivie['has_sms'] = 1;
			}

			if ($suivie['id_employee'] == 0) {
				$suivie['employee'] = 'Automation CRM';
			} else {
				$suivie['employee'] = $suivie['firstname'] . ' ' . $suivie['lastname'];
			}

			if ($suivie['id_sale_agent'] > 0) {
				$suivie['employee'] = 'CEF : ' . $suivie['agent_firstname'] . ' ' . $suivie['agent_lastname'];
			}

		}

		$employees = Employee::getEmployees();
		$data = $this->createTemplate('controllers/student_educations/suivie.tpl');
		$data->assign('studentEducation', $studentEducation);
		$data->assign('suivies', $suivies);
		$data->assign('student', $student);
		$data->assign('employees', $employees);
		$data->assign('idEmployee', $context->employee->id);
		$result = [
			'html'    => $data->fetch(),
			'success' => true,
		];
		die(Tools::jsonEncode($result));
	}

	public function ajaxProcessRegisterEvent() {

		$context = Context::getContext();
		$idSession = Tools::getValue('idSession');
		$studentEducation = new StudentEducation($idSession);

		$student = new Customer($studentEducation->id_customer);
		$employees = Employee::getEmployees();

		$data = $this->createTemplate('controllers/student_educations/event.tpl');
		$data->assign('studentEducation', $studentEducation);
		$data->assign('student', $student);
		$data->assign('employees', $employees);
		$data->assign('idEmployee', $context->employee->id);
		$result = [
			'html'    => $data->fetch(),
			'success' => true,
		];
		die(Tools::jsonEncode($result));
	}

	public function ajaxProcessEditSuivie() {

		$idSuivie = Tools::getValue('idSuivie');
		$suivie = new StudentEducationSuivie($idSuivie);
		$employee = new Employee($suivie->id_employee);
		$studentEducation = new StudentEducation($suivie->id_student_education);
		$student = new Customer($studentEducation->id_customer);
		$steps = StudentEducationStep::getEducationStep();
		$data = $this->createTemplate('controllers/student_educations/editEvent.tpl');
		$data->assign('suivie', $suivie);
		$data->assign('studentEducation', $studentEducation);
		$data->assign('student', $student);
		$data->assign('contact', $employee);
		$data->assign('employees', Employee::getEmployees());
		$data->assign('idEmployee', $context->employee->id);
		$result = [
			'html'    => $data->fetch(),
			'success' => true,
		];
		die(Tools::jsonEncode($result));

	}

	public function ajaxProcessdeleteSuivie() {

		$idSuivie = Tools::getValue('idSuivie');
		$suivie = new StudentEducationSuivie($idSuivie);
		$suivie->delete();
		$result = [
			'success' => true,
		];
		die(Tools::jsonEncode($result));
	}

	public function processPrintEmail($idSuivie, $html = false) {

		$suivie = new StudentEducationSuivie($idSuivie);

		if ($suivie->id_student_education_state == 10) {
			return false;
		}

		if ($html) {
			$email_content = '';
		}

		$studentEducation = new StudentEducation($suivie->id_student_education);
		$step = new StudentEducationStep($suivie->id_student_education_state, 1);
		$template = $step->template;

		if (empty($template)) {
			return false;
		}

		if ($suivie->id_student_education_state == 8) {

			$template = 'attestation';

		}

		$student = new Customer($studentEducation->id_customer);
		$education = new Education($studentEducation->id_education);

		$idShop = $this->context->shop->id;

		if (Configuration::get('PS_LOGO_INVOICE', null, null, $idShop) != false && file_exists(_PS_IMG_DIR_ . Configuration::get('PS_LOGO_INVOICE', null, null, $idShop))) {
			$logo = _PS_IMG_DIR_ . Configuration::get('PS_LOGO_INVOICE', null, null, $idShop);
			$logo_path = _PS_IMG_ . Configuration::get('PS_LOGO_INVOICE', null, null, $idShop);
		} else

		if (Configuration::get('PS_LOGO', null, null, $idShop) != false && file_exists(_PS_IMG_DIR_ . Configuration::get('PS_LOGO', null, null, $idShop))) {
			$logo = _PS_IMG_DIR_ . Configuration::get('PS_LOGO', null, null, $idShop);
			$logo_path = _PS_IMG_ . Configuration::get('PS_LOGO', null, null, $idShop);
		}

		$width = 0;
		$height = 0;

		if (!empty($logo_path)) {
			list($width, $height) = getimagesize($logo_path);
		}

		$maximumHeight = 100;

		if ($height > $maximumHeight) {
			$ratio = $maximumHeight / $height;
			$height *= $ratio;
			$width *= $ratio;
		}

		$mpdf = new \Mpdf\Mpdf([
			'margin_left'   => 10,
			'margin_right'  => 10,
			'margin_top'    => 50,
			'margin_bottom' => 30,
			'margin_header' => 10,
			'margin_footer' => 10,
		]);

		$data = $this->createTemplate('controllers/student_educations/mails/header.tpl');

		$data->assign(
			[

				'logo_path' => $logo_path,
			]
		);

		if ($html) {
			$email_header = $data->fetch();
		} else {
			$mpdf->SetHTMLHeader($data->fetch());
		}

		$data = $this->createTemplate('controllers/student_educations/mails/footer.tpl');

		$data->assign(
			[
				'version'    => 'V 2022-05-20',
				'tag_footer' => Configuration::get('EPH_FOOTER_PROGRAM'),
				'tags'       => Configuration::get('EPH_FOOTER_EMAIL'),
				'company'    => $this->context->company,

			]
		);

		if ($html) {
			$email_footer = $data->fetch();
		} else {
			$mpdf->SetHTMLFooter($data->fetch(), 'O');
		}

		$data = $this->createTemplate('controllers/student_educations/pdf.css.tpl');
		$data->assign(
			[
				'color' => '#fff',
			]
		);
		$stylesheet = $data->fetch();

		$data = $this->createTemplate('controllers/student_educations/mails/' . $template . '.tpl');

		$data->assign(
			[
				'title'            => $student->title,
				'lastname'         => $student->lastname,
				'birthname'        => $student->birthname,
				'firstname'        => $student->firstname,
				'email'            => $student->email,
				'password'         => $student->password,
				'student'          => $student,
				'studentEducation' => $studentEducation,
				'education_name'   => $studentEducation->name,
				'date_start'       => $studentEducation->date_start,
				'date_end'         => $studentEducation->date_end,
				'last_days'        => $studentEducation->days,
				'last_hours'       => $studentEducation->hours,
				'courseLink'       => $studentEducation->courseLink,
				'reservationLink'  => $studentEducation->reservationLink,
				'supplyName'       => $studentEducation->supplyName,
				'date_limit'       => $studentEducation->date_limit,
				'identifiant'      => $studentEducation->identifiant,
				'passwd_link'      => $studentEducation->passwd_link,
				'certification'    => $education->certification,
				'linkContract'     => $linkContract,
				'student'          => $student,
				'referent'         => Configuration::get('EPH_HANDICAP_REFERENT'),
			]
		);

		if ($html) {
			return $email_header . $data->fetch() . $email_footer;
		}

		$filePath = _PS_ADMIN_DIR_ . DIRECTORY_SEPARATOR . 'fileExport' . DIRECTORY_SEPARATOR;

		$fileName = 'email' . $suivie->id . '.pdf';

		if ($suivie->id_student_education_state == 8) {
			$fileName = $studentEducation->id . '_mail de cloture.pdf';
		}

		$mpdf->SetTitle($suivie->email_title);
		$mpdf->SetAuthor($this->context->company->company_name);

		$mpdf->SetDisplayMode('fullpage');

		$mpdf->WriteHTML($stylesheet, \Mpdf\HTMLParserMode::HEADER_CSS);
		$mpdf->WriteHTML($data->fetch());

		$mpdf->Output($filePath . $fileName, 'F');
		$suivie->pdf_content = base64_encode(file_get_contents(_PS_ADMIN_DIR_ . DIRECTORY_SEPARATOR . 'fileExport' . DIRECTORY_SEPARATOR . $fileName));
		$suivie->update();

		$fileToUpload = 'fileExport' . DIRECTORY_SEPARATOR . $fileName;
		$link = '<a  target="_blank"  class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" id="pieceDownloadFile" class="btn btn-default" href="' . $fileToUpload . '">' . $this->l('Exporter au format PDF') . '</a>';
		return $link;

	}

	public function ajaxProcessUpdateEducationSuivie() {

		$id_student_education_suivie = Tools::getValue('id_student_education_suivie');
		$educationSuivie = new StudentEducationSuivie($id_student_education_suivie);

		foreach ($_POST as $key => $value) {

			if (property_exists($educationSuivie, $key) && $key != 'id_student_education_suivie') {
				$educationSuivie->{$key}

				= $value;
			}

		}

		$result = $educationSuivie->update();
		$result = [
			'message' => 'Le suivis a été mis à jour avec succès',
			'success' => true,
		];
		die(Tools::jsonEncode($result));

	}

	public function ajaxProcessAddNewSuivie() {

		$suivie = new StudentEducationSuivie();

		foreach ($_POST as $key => $value) {

			if (property_exists($suivie, $key) && $key != 'id_student_education_suivie') {
				$suivie->{$key}

				= $value;
			}

		}

		$suivie->suivie_date = date('Y-m-d');
		$suivie->add();
		$html = ' <tr id="suivie_' . $suivie->id . '"><td>Le ' . date('d/m/Y') . '</td><td>' . $content . '</td><td>' . $sms_content . '</td><td></td></tr>';
		$result = [
			'message' => 'Le suivis a été ajouté avec succès',
			'success' => true,
			'html'    => $html,
		];
		die(Tools::jsonEncode($result));
	}

	public function ajaxProcessProceedSmsSuivie() {

		$idEducation = Tools::getValue('idSession');
		$studentEducation = new StudentEducation($idEducation);
		$student = new Customer($studentEducation->id_customer);
		$context = Context::getContext();
		$context->smarty->assign(
			[
				'student'          => $student,
				'studentEducation' => $studentEducation,
			]
		);

		$content = $context->smarty->fetch(_PS_SMS_DIR_ . 'education_suivi.tpl');

		$recipient = $student->phone_mobile;
		Tools::sendSms($recipient, $content);
		$result = [
			'success' => true,
		];
		die(Tools::jsonEncode($result));
	}

	public function renderPrerequisPdf(StudentEducationPrerequis $prerequis) {

		$studentEducation = new StudentEducation($prerequis->id_student_education);

		$student = new Customer($studentEducation->id_customer);
		$educationPrerequis = new EducationPrerequis($prerequis->id_education_prerequis, 1);

		$values = [];

		foreach ($educationPrerequis->content as $question) {

			if (array_key_exists($question['name'], $prerequis->content)) {
				$values[] = [
					'name'   => $question['name'],
					'type'   => $question['type'],
					'value'  => $question['values'],
					'answer' => $prerequis->content[$question['name']],
				];
			}

		}

		$idShop = $this->context->shop->id;

		if (Configuration::get('PS_LOGO_INVOICE', null, null, $idShop) != false && file_exists(_PS_IMG_DIR_ . Configuration::get('PS_LOGO_INVOICE', null, null, $idShop))) {
			$logo = _PS_IMG_DIR_ . Configuration::get('PS_LOGO_INVOICE', null, null, $idShop);
			$logo_path = _PS_IMG_ . Configuration::get('PS_LOGO_INVOICE', null, null, $idShop);
		} else

		if (Configuration::get('PS_LOGO', null, null, $idShop) != false && file_exists(_PS_IMG_DIR_ . Configuration::get('PS_LOGO', null, null, $idShop))) {
			$logo = _PS_IMG_DIR_ . Configuration::get('PS_LOGO', null, null, $idShop);
			$logo_path = _PS_IMG_ . Configuration::get('PS_LOGO', null, null, $idShop);
		}

		$width = 0;
		$height = 0;

		if (!empty($logo_path)) {
			list($width, $height) = getimagesize($logo_path);
		}

		$maximumHeight = 100;

		if ($height > $maximumHeight) {
			$ratio = $maximumHeight / $height;
			$height *= $ratio;
			$width *= $ratio;
		}

		$mpdf = new \Mpdf\Mpdf([
			'margin_left'   => 10,
			'margin_right'  => 10,
			'margin_top'    => 130,
			'margin_bottom' => 30,
			'margin_header' => 10,
			'margin_footer' => 10,
		]);

		$data = $this->createTemplate('controllers/student_educations/pdf/headertemplate.tpl');

		$data->assign(
			[

				'logo_path'          => $logo_path,
				'student'            => $student,
				'studentEducation'   => $studentEducation,
				'educationPrerequis' => $educationPrerequis,
				'studentPrerequis'   => $prerequis,
			]
		);
		$mpdf->SetHTMLHeader($data->fetch());

		$data = $this->createTemplate('controllers/student_educations/pdf/footertemplate.tpl');

		$data->assign(
			[
				'educationPrerequis' => $educationPrerequis,
				'tags'               => Configuration::get('EPH_FOOTER_EMAIL'),
				'company'            => $this->context->company,
			]
		);
		$mpdf->SetHTMLFooter($data->fetch(), 'O');

		$data = $this->createTemplate('controllers/student_educations/pdf.css.tpl');
		$data->assign(
			[
				'color' => '#fff',
			]
		);
		$stylesheet = $data->fetch();

		$data = $this->createTemplate('controllers/student_educations/pdf/bodytemplate.tpl');

		$data->assign(
			[
				'student'            => $student,
				'prerequis'          => $values,
				'studentEducation'   => $studentEducation,
				'educationPrerequis' => $educationPrerequis,
				'studentPrerequis'   => $prerequis,
				'bo_imgdir'          => '/administration/themes/' . $this->bo_theme . '/img/',
			]
		);

		$filePath = _PS_ADMIN_DIR_ . DIRECTORY_SEPARATOR . 'fileExport' . DIRECTORY_SEPARATOR;
		$fileName = 'Prerequis__Dossier_n°' . $studentEducation->reference_edof . '_' . $student->lastname . '_' . $student->firstname . '.pdf';
		$mpdf->SetTitle('Prerequis__Dossier_n°' . $studentEducation->reference_edof . '_' . $student->lastname . '_' . $student->firstname);
		$mpdf->SetAuthor($this->context->company->company_name);

		$mpdf->SetDisplayMode('fullpage');

		$mpdf->WriteHTML($stylesheet, \Mpdf\HTMLParserMode::HEADER_CSS);
		$mpdf->WriteHTML($data->fetch());

		$mpdf->Output($filePath . $fileName, 'F');
		$fileToUpload = 'fileExport' . DIRECTORY_SEPARATOR . $fileName;
		$link = '<a  target="_blank"  class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" id="pieceDownloadFile" class="btn btn-default" href="' . $fileToUpload . '">' . $this->l('Exporter au format PDF') . '</a>';
		return $link;

	}

	public function ajaxProcessOpenPopupEmail() {

		$idSession = Tools::getValue('idSession');
		$studentEducation = new StudentEducation($idSession);
		$student = new Customer($studentEducation->id_customer);
		$data = $this->createTemplate('sendPopupMail.tpl');

		$extraJs = $this->getJsContent([
			$this->admin_webpath . 'js/tiny_mce/tiny_mce.js',
			_PS_JS_DIR_ . 'js/tinymce.inc.js',
		]);
		$data->assign(
			[
				'studentEducation' => $studentEducation,
				'student'          => $student,
				'tinymce'          => true,
				'iso'              => file_exists(_PS_CORE_DIR_ . '/js/tiny_mce/langs/' . $iso . '.js') ? $iso : 'en',
				'path_css'         => _THEME_CSS_DIR_,
				'ad'               => __PS_BASE_URI__ . basename(_PS_ADMIN_DIR_),
				'languages'        => Language::getLanguages(false),
				'extraJs'          => $extraJs,
			]
		);

		$return = [
			'html' => $data->fetch(),
		];

		die(Tools::jsonEncode($return));
	}

	public function ajaxProcessSentStudentEmail() {

		$idStudent = Tools::getValue('idStudent');
		$student = new Customer($idStudent);

		$tpl = $this->context->smarty->createTemplate(_PS_MAIL_DIR_ . '/fr/freeMessage.tpl');
		$tpl->assign([
			'title'   => Tools::getValue('subjetc'),
			'content' => Tools::getValue('message'),
		]);
		$content = $tpl->fetch();
		$postfields = [
			'sender'      => [
				'name'  => "Service  Administratif " . Configuration::get('PS_SHOP_NAME'),
				'email' => Configuration::get('PS_SHOP_EMAIL'),
			],
			'to'          => [
				[
					'name'  => $student->firstname . ' ' . $student->lastname,
					'email' => $student->email,
				],
			],
			'subject'     => Tools::getValue('subjetc'),
			"htmlContent" => $content,
		];
		Tools::sendEmail($postfields);

		$suivie = new StudentEducationSuivie();
		$suivie->suivie_date = date('Y-m-d');
		$suivie->id_student_education = Tools::getValue('idStudentEducation');
		$suivie->id_student_education_state = 10;
		$suivie->id_employee = $this->context->employee->id;
		$suivie->email_title = Tools::getValue('subjetc');
		$suivie->email_content = $content;
		$suivie->content = 'Contact par email';
		$suivie->add();
		$result = [
			'success' => true,
		];
		die(Tools::jsonEncode($result));
	}

	public function ajaxProcessSynchAlter() {

		$idSession = Tools::getValue('idSession');
		StudentEducation::syncAlterSession($idSession);
		StudentEducation::synchCloudLearning($idSession);

		$return = [
			'success' => true,
			'message' => 'La synchronisation avec Alter Campus a été effectuée avec succès',
		];

		die(Tools::jsonEncode($return));
	}

	public function ajaxProcessResendRegistrationValidate() {

		$idSession = Tools::getValue('idSession');
		$studentEducation = new StudentEducation($idSession);

		$tpl = $this->context->smarty->createTemplate(_PS_MAIL_DIR_ . '/fr/registrationValidate.tpl');

		$student = new Customer($studentEducation->id_customer);

		$tpl->assign([
			'title'           => $student->title,
			'lastname'        => $student->lastname,
			'birthname'       => $student->birthname,
			'firstname'       => $student->firstname,
			'email'           => $student->email,
			'password'        => $student->password,
			'education_name'  => $studentEducation->name,
			'date_start'      => $studentEducation->date_start,
			'date_end'        => $studentEducation->date_end,
			'last_days'       => $studentEducation->days,
			'last_hours'      => $studentEducation->hours,
			'courseLink'      => $url,
			'reservationLink' => $studentEducation->reservationLink,
			'supplyName'      => $studentEducation->supplyName,
			'date_limit'      => $studentEducation->date_limit,
			'identifiant'     => $studentEducation->identifiant,
			'passwd_link'     => $studentEducation->passwd_link,
			'sessionId'       => $studentEducation->id,
		]);

		$postfields = [
			'sender'      => [
				'name'  => "Service  Administratif " . Configuration::get('PS_SHOP_NAME'),
				'email' => Configuration::get('PS_SHOP_EMAIL'),
			],
			'to'          => [
				[
					'name'  => $student->firstname . ' ' . $student->lastname,
					'email' => $student->email,
				],
			],

			'subject'     => 'N‘oubliez pas d‘accepter votre devis pour valider votre formation',
			"htmlContent" => $tpl->fetch(),
		];

		$result = Tools::sendEmail($postfields);

		$suivie = new StudentEducationSuivie();
		$suivie->suivie_date = date('Y-m-d');
		$suivie->id_employee = Context::getContext()->employee->id;

		$suivie->id_student_education = $studentEducation->id;
		$suivie->id_student_education_state = $studentEducation->id_student_education_state;
		$suivie->email_title = 'Rappel pour valider pour votre formation "' . $studentEducation->name . '"';
		$suivie->email_content = $tpl->fetch();
		$suivie->content = 'Rappel pour valider pour votre formation "' . $studentEducation->name . '"';
		$suivie->add();

		$return = [
			'success' => true,
			'message' => $this->l('Le mail invitant le stagiaire à accepter le devis a été ré envoyé avec succès'),
		];
		die(Tools::jsonEncode($return));

	}

	public function ajaxProcesssendConvocationEmail() {

		$idSession = Tools::getValue('idSession');
		$studentEducation = new StudentEducation($idSession);

		$tpl = $this->context->smarty->createTemplate(_PS_MAIL_DIR_ . '/fr/convocation.tpl');

		$customer = new Customer($studentEducation->id_customer);

		$tpl->assign([
			'studentEducation' => $studentEducation,
			'student'          => $customer,
			'referent'         => Configuration::get('EPH_HANDICAP_REFERENT'),
		]);

		$postfields = [
			'sender'      => [
				'name'  => "Service  Administratif " . Configuration::get('PS_SHOP_NAME'),
				'email' => Configuration::get('PS_SHOP_EMAIL'),
			],
			'to'          => [
				[
					'name'  => $customer->firstname . ' ' . $customer->lastname,
					'email' => $customer->email,
				],
			],

			'subject'     => 'Convocation à votre formation du ' . $studentEducation->date_start,
			"htmlContent" => $tpl->fetch(),
		];

		$result = Tools::sendEmail($postfields);

		$suivie = new StudentEducationSuivie();
		$suivie->suivie_date = date('Y-m-d');
		$suivie->id_employee = Context::getContext()->employee->id;
		$suivie->id_student_education = $studentEducation->id;
		$suivie->id_student_education_state = $studentEducation->id_student_education_state;
		$suivie->email_title = 'Renvoie Convocation à votre formation du ' . $studentEducation->date_start;
		$suivie->email_content = $tpl->fetch();
		$suivie->content = 'Renvoie Convocation à votre formation du ' . $studentEducation->date_start;
		$suivie->add();

		$return = [
			'success' => true,
			'message' => $this->l('Le mail de convocation a été ré envoyé avec succès'),
		];
		die(Tools::jsonEncode($return));

	}

}
