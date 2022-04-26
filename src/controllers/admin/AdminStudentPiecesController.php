<?php

@ini_set('max_execution_time', 0);
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Class AdminStudentPiecesCore
 *
 * @since 1.8.1.0
 */
class AdminStudentPiecesControllerCore extends AdminController {

	// @codingStandardsIgnoreStart
	/** @var string $toolbar_title */
	public $toolbar_title;
	/** @var array $statuses_array */
	protected $statuses_array = [];
	// @codingStandardsIgnoreEnd

	public $validateSelector;

	public $paymentSelector;

	public $countryStudentPiecesSelector;

	public $orderTomerge = [];

	public $pieceTypes = [];

	public $pieceType = [];

	public $configurationDetailField = [];

	static $_student_eelected;

	static $_pieceDetails = [];

	public $defaultTemplate;

	/**
	 * AdminStudentPiecesCore constructor.
	 *
	 * @since 1.8.1.0
	 */
	public function __construct() {

		$this->bootstrap = true;
		$this->table = 'student_pieces';
		$this->className = 'StudentPieces';
		$this->publicName = $this->l('Pièces étudiants');
		$this->lang = false;
		$this->identifier = 'id_student_piece';
		$this->controller_name = 'AdminStudentPieces';

		$this->context = Context::getContext();

		EmployeeConfiguration::updateValue('EPH_TEMPLATE_DEFAULT', Tools::jsonEncode($this->generateDefaultTemplate()));
		$this->defaultTemplate = Tools::jsonDecode(EmployeeConfiguration::get('EPH_TEMPLATE_DEFAULT'), true);

		if (empty($this->defaultTemplate)) {
			EmployeeConfiguration::updateValue('EPH_TEMPLATE_DEFAULT', Tools::jsonEncode($this->generateDefaultTemplate()));
			$this->defaultTemplate = Tools::jsonDecode(EmployeeConfiguration::get('EPH_TEMPLATE_DEFAULT'), true);
		}

		EmployeeConfiguration::updateValue('EXPERT_STUDENTPIECES_SCRIPT', $this->generateParaGridScript());
		$this->paragridScript = EmployeeConfiguration::get('EXPERT_STUDENTPIECES_SCRIPT');

		if (empty($this->paragridScript)) {
			EmployeeConfiguration::updateValue('EXPERT_STUDENTPIECES_SCRIPT', $this->generateParaGridScript());
			$this->paragridScript = EmployeeConfiguration::get('EXPERT_STUDENTPIECES_SCRIPT');
		}

		EmployeeConfiguration::updateValue('EXPERT_STUDENTPIECES_FIELDS', Tools::jsonEncode($this->getStudentPiecesFields()));
		$this->configurationField = Tools::jsonDecode(EmployeeConfiguration::get('EXPERT_STUDENTPIECES_FIELDS'), true);

		if (empty($this->configurationField)) {
			EmployeeConfiguration::updateValue('EXPERT_STUDENTPIECES_FIELDS', Tools::jsonEncode($this->getStudentPiecesFields()));
			$this->configurationField = Tools::jsonDecode(EmployeeConfiguration::get('EXPERT_STUDENTPIECES_FIELDS'), true);
		}

		EmployeeConfiguration::updateValue('EXPERT_STUDENTPIECES_DETAIL_FIELDS', Tools::jsonEncode($this->getDetailStudentPiecesFields()));
		$this->configurationDetailField = Tools::jsonDecode(EmployeeConfiguration::get('EXPERT_STUDENTPIECES_DETAIL_FIELDS'), true);

		if (empty($this->configurationDetailField)) {
			EmployeeConfiguration::updateValue('EXPERT_STUDENTPIECES_DETAIL_FIELDS', Tools::jsonEncode($this->getDetailStudentPiecesFields()));
			$this->configurationDetailField = Tools::jsonDecode(EmployeeConfiguration::get('EXPERT_STUDENTPIECES_DETAIL_FIELDS'), true);
		}

		$this->validateSelector = '<div class="pq-theme"><select id="validateSelect"><option value="">' . $this->l('--Select--') . '</option><option value="false">' . $this->l('No') . '</option><option value="true">' . $this->l('yes') . '</option></select></div>';

		$this->orderTomerge = StudentPieces::getmergeOrderTable();

		$this->pieceTypes = [
			'QUOTATION' => $this->l('Devis'),
			'ORDER'     => $this->l('Commandes'),
			'INVOICE'   => $this->l('Factures'),
		];

		$this->pieceType = [
			'QUOTATION' => $this->l('Devis'),
			'ORDER'     => $this->l('Commande'),
			'INVOICE'   => $this->l('Facture'),
		];

		parent::__construct();

	}

	/**
	 * Set Media
	 *
	 * @return void
	 *
	 * @since 1.8.1.0
	 */
	public function setMedia() {

		parent::setMedia();

		$this->addjQueryPlugin(['scrollTo', 'alerts', 'chosen', 'autosize', 'fancybox', 'contextMenu', 'tinymce', 'fileupload', 'dropdownmenu']);
		$this->addCSS(__PS_BASE_URI__ . $this->admin_webpath . '/themes/' . $this->bo_theme . '/css/pieces.css', 'all', 0);

		$this->addJS([
			__PS_BASE_URI__ . $this->admin_webpath . '/js/studentpieces.js',
		]);

		MediaAdmin::addJsDef([
			'orderToMerges'              => $this->orderTomerge,
			'AjaxLinkAdminStudentPieces' => $this->context->link->getAdminLink('AdminStudentPieces'),
		]);

	}

	public function initContent($token = null) {

		$this->displayGrid = true;
		$this->paramGridObj = 'obj' . $this->className;
		$this->paramGridVar = 'grid' . $this->className;
		$this->paramGridId = 'grid_' . $this->controller_name;

		$this->toolbar_title = $this->l('Gestion des') . ' ' . $this->publicName;

		$this->TitleBar = $this->l('Liste des pièces étudiants');
		$countOrderTomerge = 0;

		if (isset($this->orderTomerge['todo']) && count($this->orderTomerge['todo'])) {
			$countOrderTomerge = count($this->orderTomerge['todo']);
		}

		$sessions = EducationSession::getInvoicedEducationSession();

		$rangeMonths = Tools::getExerciceMonthRange();

		$this->context->smarty->assign([
			'pieceTypes'         => $this->pieceTypes,
			'orderToMerges'      => $countOrderTomerge,
			'manageHeaderFields' => true,
			'customHeaderFields' => $this->manageFieldsVisibility($this->configurationField),
			'allowExport'        => true,
			'fieldsExport'       => $this->getExportFields(),
			'controller'         => $this->controller_name,
			'linkController'     => $this->context->link->getAdminLink($this->controller_name),
			'className'          => 'StudentPieces',
			'titleBar'           => $this->TitleBar,
			'sessions'           => $sessions,
			'rangeMonths'        => $rangeMonths,
			'gridId'             => $this->paramGridId,
			'tableName'          => $this->table,
			'paragridScript'     => $this->generateParaGridScript(),
		]);

		parent::initContent();

	}

	public function initGridController() {

		$this->paramGridObj = 'obj' . 'StudentPieces';
		$this->paramGridVar = 'grid' . 'StudentPieces';
		$this->paramGridId = 'grid_' . $this->controller_name;

		$this->toolbar_title = $this->l('Management of') . ' ' . $this->publicName;

		$this->TitleBar = $this->l('Gestion des factures de Formation');
		$countOrderTomerge = 0;

		if (isset($this->orderTomerge['todo']) && count($this->orderTomerge['todo'])) {
			$countOrderTomerge = count($this->orderTomerge['todo']);
		}

		$this->context->smarty->assign([
			'pieceTypes'         => $this->pieceTypes,
			'orderToMerges'      => $countOrderTomerge,
			'manageHeaderFields' => true,
			'customHeaderFields' => $this->manageFieldsVisibility($this->configurationField),
			'allowExport'        => true,
			'fieldsExport'       => $this->getExportFields(),
			'controller'         => $this->controller_name,
		]);

		return parent::initGridController();

	}

	public function generateParaGridScript() {

		$ajaxlink = $this->context->link->getAdminLink($this->controller_name);
		$pieceTypes = [
			'QUOTATION' => $this->l('Quotations'),
			'ORDER'     => $this->l('Purchase Orders'),
			'INVOICE'   => $this->l('Invoices'),
		];
		$pieces = [
			'QUOTATION' => $this->l('Quotation'),
			'ORDER'     => $this->l('Purchase Order'),
			'INVOICE'   => $this->l('Invoice'),
		];
		$gridExtraFunction = [
			'

			
			function proceedBulkUpdate(selector, target) {

			var selectionArray = selector.getSelection();
			var idpieces = [];
			$.each(selectionArray, function(index, value) {
			idpieces.push(value.rowData.id_student_piece);

			})

			$.ajax({
				type: \'POST\',
				url: AjaxLinkAdminStudentPieces,
				data: {
					action: \'convertBulkPiece\',
					idPieces: idpieces,
					target: target,
					ajax: true
				},
				async: false,
				dataType: \'json\',
				success: function(data) {
					if (data.success) {
						showSuccessMessage(data.message);
						gridStudentPieces.refreshDataAndView();
					} else {
						showErrorMessage(data.message);
					}
				}
				});

			}
			function validateBulkPieces(selector) {

				var selectionArray = selector.getSelection();
				var idpieces = [];
				$.each(selectionArray, function(index, value) {
					idpieces.push(value.rowData.id_student_piece);
				})

				$.ajax({
					type: \'POST\',
					url: AjaxLinkAdminStudentPieces,
					data: {
						action: \'bulkValidate\',
						idPieces: idpieces,
						ajax: true
					},
					async: false,
					dataType: \'json\',
					success: function(data) {
						if (data.success) {
							showSuccessMessage(data.message);
							gridStudentPieces.refreshDataAndView();
						} else {
							showErrorMessage(data.message);
						}
					}
				});

			}
			function bookBulkPieces(selector) {
				$("html").addClass("csstransitions");
				isAnimating = true;
				var selectionArray = selector.getSelection();

				var idpieces = [];
				$.each(selectionArray, function(index, value) {
					idpieces.push(value.rowData.id_student_piece);
				})

				$("#content").addClass("page-is-changing");
				$(".cd-loading-bar").one("webkitTransitionEnd otransitionend oTransitionEnd msTransitionEnd transitionend", function() {

					proceedbookBulkPieces(idpieces);
					$(".cd-loading-bar").off("webkitTransitionEnd otransitionend oTransitionEnd msTransitionEnd transitionend");
				});
			}
			function proceedbookBulkPieces(idpieces) {

				$.ajax({
					type: \'POST\',
					url: AjaxLinkAdminStudentPieces,
					data: {
						action: \'bulkBook\',
						idPieces: idpieces,
						ajax: true
					},
					async: false,
					dataType: \'json\',
					success: function(data) {
						if (data.success) {
							showSuccessMessage(data.message);
							gridStudentPieces.refreshDataAndView();
						} else {
							showErrorMessage(data.message);
						}
					},
					complete: function(data) {
						$("#content").removeClass("page-is-changing");
						$(".cd-loading-bar").one("webkitTransitionEnd otransitionend oTransitionEnd msTransitionEnd transitionend", function() {
							isAnimating = false;
							$(".cd-loading-bar").off("webkitTransitionEnd otransitionend oTransitionEnd msTransitionEnd transitionend");
						});
					}
				});

			}
			function buildStudentPiecesFilter(){

			$("#pieceSessionSelect" ).selectmenu({
				classes: {
    				"ui-selectmenu-menu": "scrollable"
  				},
	   			"change": function(event, ui) {
		   			gridStudentPieces.filter({
           			mode: \'AND\',
					rules: [
                		{ dataIndx: \'id_education_session\', condition: \'equal\', value: ui.item.value}
						]
						});
					$("#selectedSessionValue").val(ui.item.value);
					if(ui.item.value >0) {
						$("#export-invoice").slideDown();
						$("#export-excel").slideDown();
					} else {
						$("#export-invoice").slideUp();
						$("#export-excel").slideUp();
					}
	   			}
			});

			$("#pieceMonthSelect" ).selectmenu({
				width:300,

				"change": function(event, ui) {
		   			var values = ui.item.value;
					var res = values.split("|");
					gridStudentPieces.filter({
           				mode: \'AND\',
						rules: [
                			{ dataIndx: \'date_add\', condition: \'between\', value: res[0], value2:res[1]}
						]
					});

	   			}

			});

            var validateconteneur = $(\'#validateSelector\').parent().parent();
            $(validateconteneur).empty();
            $(validateconteneur).append(\'' . $this->validateSelector . '\');
            $(\'#validateSelect\' ).selectmenu({
                "change": function(event, ui) {

                    grid' . $this->className . '.filter({
                        mode: \'AND\',
                        rules: [
                            { dataIndx: \'isLocked\', condition: \'equal\', value: ui.item.value}
                        ]
                    });
                }
            });
			}



			',

		];

		$class = 'cls:\'productValidate\'';
		$paragrid = new ParamGrid($this->className, $this->controller_name, $this->table, $this->identifier);
		$paragrid->paramTable = $this->table;
		$paragrid->paramController = $this->controller_name;
		$paragrid->height = '800';
		$paragrid->columnBorders = 1;
		$paragrid->pageModel = [
            'type'       => '\'local\'',
            'rPP'        => 40,
            'rPPOptions' => [10, 20, 40, 50, 100, 200, 500],
        ];

		$paragrid->showNumberCell = 0;

		$paragrid->rowInit = 'function (ui) {
			var applyStyle;
            if(ui.rowData.isLocked) {
            	return {
                attr: \'data-link="\'+AjaxLink' . $this->controller_name . '+\'" data-class="' . $this->className . '" data-rowIndx="\' + ui.rowIndx+\'" data-object="\' + ui.rowData' . $this->identifier . '+\' "\', ' . $class . '
                };
            }  else {
                return {
				attr: \'data-link="\'+AjaxLink' . $this->controller_name . '+\'" data-class="' . $this->className . '" data-object="\' + ui.rowData' . $this->identifier . '+\' "\',
                };
            }
        }';
		$paragrid->toolbar = [
			'items' => [

				[
					'type'     => '\'button\'',
					'label'    => '\'Export PDF les factures de cette session\'',
					'attr'     => '\'id="export-invoice"\'',
					'style'    => '\'display:none;\'',
					'cls'      => '\'ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only\'',
					'listener' => 'exportSessionInvoices',
				],

				[
					'type'     => '\'button\'',
					'label'    => '\'Export Excel les factures de cette session\'',
					'attr'     => '\'id="export-excel"\'',
					'style'    => '\'display:none;\'',
					'cls'      => '\'ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only\'',
					'listener' => 'exportSessionExcel',
				],
				

			],
		];
		$paragrid->complete = 'function(){
		buildStudentPiecesFilter();
		window.dispatchEvent(new Event(\'resize\'));
		var screenHeight = $(window).height() -200;
		console.log(screenHeight);
		grid' . $this->className . '.option( "height", screenHeight );
        }';
		$paragrid->selectionModelType = 'row';

		$paragrid->create = 'function (evt, ui) {
			buildHeadingAction(\'' . 'grid_' . $this->controller_name . '\', \'' . $this->controller_name . '\');
        }';

		$paragrid->filterModel = [
			'on'          => true,
			'mode'        => '\'AND\'',
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
		$paragrid->groupModel = [
			'on'           => true,
			'grandSummary' => true,
			'header'       => 0,
		];
		$paragrid->summaryTitle = [
			'sum' => '"Total : {0}"',
		];
		$paragrid->showTitle = 1;
		$paragrid->title = '""';
		$paragrid->fillHandle = '\'all\'';

		$paragrid->contextMenu = [
			'#grid_' . $this->controller_name => [
				'selector'  => '\'.pq-body-outer .pq-grid-row\'',
				'animation' => [
					'duration' => 250,
					'show'     => '\'fadeIn\'',
					'hide'     => '\'fadeOut\'',
				],
				'build'     => 'function($triggerElement, e) {
                var rowIndex = $($triggerElement).attr("data-rowIndx");
                var rowData = ' . 'grid' . $this->className . '.getRowData( {rowIndx: rowIndex} );
                var pieceType = rowData.pieceType;

                return {
                    callback: function(){},

                    items: {


                    "edit": {
                            name: \'' . $this->l('Modifier la ') . ' \'+rowData.pieceType+ \' : \'+rowData.pieceNumber,
                            icon: "edit",
							visible: function(key, opt){
                               var selected = selgrid' . $this->className . '.getSelection().length;
                                if(selected > 1) {
                                    return false;
                                }
								if(rowData.piece_type == \'INVOICE\' && rowData.isLocked ==true) {
                                    return false;
                                }
                                return true;
                            },
                            callback: function(itemKey, opt, e) {

								editPieces(rowData.id_student_piece);
                            }
                        },
					"view": {
                            name: \'' . $this->l('Consulter la ') . ' \'+rowData.pieceType+ \' : \'+rowData.pieceNumber,
                            icon: "view",
							visible: function(key, opt){
                               var selected = selgrid' . $this->className . '.getSelection().length;
                                if(selected > 1) {
                                    return false;
                                }
								if(rowData.isLocked ==true) {
                                    return true;
                                }
                                return false;
                            },
                            callback: function(itemKey, opt, e) {

								editPieces(rowData.id_student_piece);
                            }
                        },
						"print": {
                            name: \'' . $this->l('Imprimer la') . ' \'+rowData.pieceType+ \' : \'+rowData.pieceNumber,
                            icon: "print",
							visible: function(key, opt){
                               var selected = selgrid' . $this->className . '.getSelection().length;
                                if(selected > 1) {
                                    return false;
                                }

                                return true;
                            },
                            callback: function(itemKey, opt, e) {

								print(rowData.id_student_piece);
                            }
                        },
						"bulkprint": {
                            name: \'' . $this->l('Imprimer les factures sélectionnées') . ' \',
                            icon: "print",
							visible: function(key, opt){
                               var selected = selgrid' . $this->className . '.getSelection().length;
                                 if(selected < 2) {
                                    return false;
                                }

                                return true;
                            },
                            callback: function(itemKey, opt, e) {

								bulkPrint(selgrid' . $this->className . '.getSelection());
                            }
                        },
					"regl": {
                            name: \'' . $this->l('Enregistrer le règlement de la') . ' \'+rowData.pieceType+ \' : \'+rowData.pieceNumber,
                            icon: "pay",
							visible: function(key, opt){
                               var selected = selgrid' . $this->className . '.getSelection().length;
                                if(selected > 1) {
                                    return false;
                                }
								if(rowData.isLocked ==false) {
                                    return false;
                                }
								if(rowData.balanceDue == 0) {
                                    return false;
                                }
                                return true;
                            },
                            callback: function(itemKey, opt, e) {

								generateReglement(rowData.id_student_piece);
                            }
                        },
					"reglBulk": {
                            name: \'' . $this->l('Enregistrer le règlement des pièces sélectionnées') . ' \',
                            icon: "edit",
							visible: function(key, opt){
                               var selected = selgrid' . $this->className . '.getSelection().length;
							   var pieceSelected = selgrid' . $this->className . '.getSelection();
							   var allowed = true;
                                if(selected < 2) {
                                    return false;
                                }
								$.each(pieceSelected, function( index, value ) {
  									if(value.rowData.isLocked == false) {
										allowed = false;
									}
									if(value.rowData.balanceDue == \'0.00\') {
										allowed = false;
									}
  								});
								if(allowed == false) {
									return false;
								}
								return true;
                            },
                            callback: function(itemKey, opt, e) {

								generateBulkReglement(selgrid' . $this->className . '.getSelection());
                            }
                        },
	                "validate": {
                            name: \'' . $this->l('Valider la Facture') . ' \ :\'+rowData.pieceNumber,
                            icon: "lock",
                            visible: function(key, opt){
								var selected = selgrid' . $this->className . '.getSelection().length;
                                if(selected > 1) {
                                    return false;
                                }
                                if(rowData.piece_type == \'INVOICE\' && rowData.isLocked ==false) {
                                    return true;
                                }
                                return false;
                            },
                            callback: function(itemKey, opt, e) {
								validatePieces(rowData.id_student_piece);
                            }
                        },
						"bulkvalidate": {
                            name: \'' . $this->l('Valider les Factures sélectionnées') . '\',
                            icon: "lock",
                            visible: function(key, opt){
								var selected = selgrid' . $this->className . '.getSelection().length;
								var pieceSelected = selgrid' . $this->className . '.getSelection();
							   var allowed = true;
                                if(selected < 2) {
                                    return false;
                                }
								$.each(pieceSelected, function( index, value ) {
  									if(value.rowData.isLocked == true) {
										allowed = false;
									}

  								});
								if(allowed == false) {
									return false;
								}

                                return true;
                            },
                            callback: function(itemKey, opt, e) {
								validateBulkPieces(selgridStudentPieces);
                            }
                        },
						 "book": {
                            name: \'' . $this->l('Comptabiliser la') . ' \'+rowData.pieceType+ \' : \'+rowData.pieceNumber,
                            icon: "book",
                            visible: function(key, opt){
								var selected = selgrid' . $this->className . '.getSelection().length;
                                if(selected > 1) {
                                    return false;
                                }
                                if(rowData.piece_type == \'INVOICE\' && rowData.isBooked  == 0) {
                                    return true;
                                }
                                return false;
                            },
                            callback: function(itemKey, opt, e) {
								bookPieces(rowData.id_student_piece);
                            }
                        },
						"bulkbook": {
                            name: \'' . $this->l('Comptabiliser les Factures sélectionnées') . '\',
                            icon: "book",
                            visible: function(key, opt){
								var selected = selgrid' . $this->className . '.getSelection().length;
								var pieceSelected = selgrid' . $this->className . '.getSelection();
							  	 var allowed = true;
                                if(selected < 2) {
                                    return false;
                                }
								$.each(pieceSelected, function( index, value ) {
  									if(value.rowData.isLocked == false) {
										allowed = false;
									}
  								});
								if(allowed == false) {
									return false;
								}


                                return true;
                            },
                            callback: function(itemKey, opt, e) {
								bookBulkPieces(selgridStudentPieces);
                            }
                        },
						 "viewbook": {
                            name: \'' . $this->l('Ouvrir l‘écriture') . '\',
                            icon: "book",
                            visible: function(key, opt){
								var selected = selgrid' . $this->className . '.getSelection().length;
                                if(selected > 1) {
                                    return false;
                                }
								if(rowData.id_book_record  == 0) {
                                    return false;
                                }
                                if(rowData.isBooked  == 1) {
                                    return true;
                                }
                                return false;
                            },
                            callback: function(itemKey, opt, e) {
								viewbookPiece(rowData.id_book_record);
                            }
                        },



                        "sep1": "---------",
                        "select": {
                            name: \'' . $this->l('Tous sélectionner') . '\',
                            icon: "lock",
                            visible: function(key, opt){
                                var selected = selgrid' . $this->className . '.getSelection().length
                                if(selected > 1) {
                                    return false;
                                }
                                return true;
                            },
                            callback: function(itemKey, opt, e) {
                                selgrid' . $this->className . '.selectAll();
                            }
                        },
                        "unselect": {
                            name: \'' . $this->l('Tous désélectionner') . '\',
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
                        "sep2": "---------",
                        "delete": {
                            name: \'' . $this->l('Supprimer la Facture') . ' \ : \'+rowData.pieceNumber,
                            icon: "list-ul",
                            visible: function(key, opt){
                                if(rowData.isLocked ==true) {
                                    return false;
                                }
                                return true;
                            },
                            callback: function(itemKey, opt, e) {
                                deletePieces(rowData.id_student_piece);
                            }
                        },

                    },
                };
            }',
			]];
		$paragrid->gridExtraFunction = $gridExtraFunction;

		$option = $paragrid->generateParaGridOption();
		$script = $paragrid->generateParagridScript();

		$this->paragridScript = $script;
		return '<script type="text/javascript">' . PHP_EOL . $this->paragridScript . PHP_EOL . '</script>';
	}

	public function generateParaGridOption() {

		return true;
	}

	public function ajaxProcessinitController() {

		return $this->initGridController();
	}

	public function manageFieldsVisibility($fields) {

		return parent::manageFieldsVisibility($fields);
	}

	public function ajaxProcessupdateVisibility() {

		$headerFields = Tools::jsonDecode(EmployeeConfiguration::get('EXPERT_STUDENTPIECES_FIELDS'), true);
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
		EmployeeConfiguration::updateValue('EXPERT_STUDENTPIECES_FIELDS', $headerFields);
		die($headerFields);
	}

	public function ajaxProcessUpdateDetailVisibility() {

		$headerFields = Tools::jsonDecode(EmployeeConfiguration::get('EXPERT_STUDENTPIECES_DETAIL_FIELDS'), true);
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
		EmployeeConfiguration::updateValue('EXPERT_STUDENTPIECES_DETAIL_FIELDS', $headerFields);
		die($headerFields);
	}

	public function getStudentPiecesRequest() {

		$orders = StudentPieces::getRequest();

		$orderLink = $this->context->link->getAdminLink($this->controller_name);

		foreach ($orders as &$order) {

			$order['pieceNumber'] = $this->getStaticPrefix($order['piece_type']) . $order['piece_number'];

			$order['pieceType'] = $this->pieceType[$order['piece_type']];
			

		}

		$orders = $this->removeRequestFields($orders);

		return $orders;

	}

	public function getRequestbyIdeSession($idSession) {

		$context = Context::getContext();

		$query = new DbQuery();
		$query->select('a.*, (a.education_tax_incl+a.shipping_tax_incl) as `total`, (a.education_tax_excl+a.shipping_tax_excl) as `total_tax_excl`, CONCAT(s.`firstname`, " ", s.`lastname`) AS `student`,
		(a.`education_tax_incl` + a.`shipping_tax_incl`  - a.`total_paid`) as `balanceDue`, case when a.validate = 1 then \'<div class="orderValidate"></div>\' else \'<div class="orderOpen"></div>\' end as validate, case when a.validate = 1 then 1 else 0 end as isLocked,  s.`id_country`, s.`address_street`, s.`address_street2`, s.`address_zipcode`, s.`address_city`, cl.`name` AS country, se.reference_edof, case when a.is_book = 1 then \'<div class="orderBook"></div>\' else \'<div class="orderUnBook"></div>\' end as booked, case when a.is_book = 1 then 1 else 0 end as isBooked');
		$query->from('student_pieces', 'a');
		$query->leftJoin('customer', 's', 's.`id_customer` = a.`id_customer`');
		$query->leftJoin('student_education', 'se', 'se.`id_student_education` = a.`id_student_education`');
		$query->leftJoin('country_lang', 'cl', 'cl.`id_country` = s.`id_country` AND cl.`id_lang` = ' . $context->language->id);
		$query->where('se.`id_education_session` = ' . $idSession);
		$query->orderBy('a.`date_add` DESC');
	
		$orders = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);

		foreach ($orders as &$order) {

			$order['pieceNumber'] = $this->getStaticPrefix($order['piece_type']) . $order['piece_number'];

			$order['pieceType'] = $this->pieceType[$order['piece_type']];

		}

		$orders = $this->removeRequestFields($orders);

		return $orders;

		return $orders;

	}

	public function ajaxProcessgetStudentPiecesRequest() {

		die(Tools::jsonEncode($this->getStudentPiecesRequest()));

	}

	public function getStudentPiecesFields() {

		return [
			[
				'title'      => $this->l('ID'),
				'width'      => 50,
				'dataIndx'   => 'id_student_piece',
				'dataType'   => 'integer',
				'editable'   => false,
				'halign'     => 'HORIZONTAL_CENTER',
				'hiddenable' => 'no',
				'align'      => 'center',
				'hidden'     => false,

			],
			[
				'title'      => ' ',
				'width'      => 10,
				'dataIndx'   => 'id_book_record',
				'dataType'   => 'integer',
				'hidden'     => true,
				'hiddenable' => 'no',

			],
			[
				'dataIndx'   => 'openLink',
				'dataType'   => 'string',
				'hidden'     => true,
				'hiddenable' => 'no',
			],

			[

				'dataIndx'   => 'pieceType',
				'dataType'   => 'string',
				'hidden'     => true,
				'hiddenable' => 'no',
			],

			[
				'title'      => ' ',
				'width'      => 50,
				'dataIndx'   => 'piece_type',
				'dataType'   => 'string',
				'hidden'     => true,
				'hiddenable' => 'no',
				'filter'     => [
					'crules' => [['condition' => "begin"]],

				],
			],
			[
				'title'    => $this->l('N° de pièce'),
				'width'    => 130,
				'excel'    => true,
				'dataIndx' => 'pieceNumber',
				'valign'   => 'center',
				'editable' => false,
				'dataType' => 'string',
				'filter'   => [
					'crules' => [['condition' => "contain"]],
				],
			],

			[
				'title'    => $this->l('Société'),
				'width'    => 150,
				'exWidth'  => 40,
				'dataIndx' => 'company_name',
				'halign'   => 'HORIZONTAL_LEFT',
				'valign'   => 'center',
				'dataType' => 'string',
				'editable' => false,
				'hidden'   => true,
				'filter'   => [
					'crules' => [['condition' => "contain"]],

				],
			],
			[
				'title'    => $this->l('Dossier'),
				'width'    => 150,
				'excel'    => true,
				'dataIndx' => 'reference_edof',
				'align'    => 'left',
				'valign'   => 'center',
				'editable' => false,
				'dataType' => 'string',
				'filter'   => [
					'crules' => [['condition' => "contain"]],
				],
			],
			[
				'title'    => $this->l('Etudiant'),
				'width'    => 150,
				'excel'    => true,
				'dataIndx' => 'student',
				'align'    => 'left',
				'valign'   => 'center',
				'editable' => false,
				'dataType' => 'string',
				'filter'   => [
					'crules' => [['condition' => "contain"]],
				],
			],

			[
				'title'    => $this->l('Adresse'),
				'width'    => 150,
				'exWidth'  => 20,
				'dataIndx' => 'address_street',
				'align'    => 'left',
				'valign'   => 'center',
				'halign'   => 'HORIZONTAL_LEFT',
				'dataType' => 'string',
				'editable' => false,
				'hidden'   => true,
				'filter'   => [
					'crules' => [['condition' => "begin"]],
				],
			],
			[
				'title'    => $this->l('Ville'),
				'width'    => 120,
				'exWidth'  => 20,
				'dataIndx' => 'address_city',
				'align'    => 'left',
				'valign'   => 'center',
				'halign'   => 'HORIZONTAL_LEFT',
				'dataType' => 'string',
				'editable' => false,
				'editable' => false,
				'hidden'   => true,
				'filter'   => [
					'crules' => [['condition' => "begin"]],
				],
			],
			[
				'title'    => $this->l('Code postale'),
				'width'    => 100,
				'exWidth'  => 20,
				'dataIndx' => 'address_zipcode',
				'align'    => 'left',
				'valign'   => 'center',
				'halign'   => 'HORIZONTAL_LEFT',
				'dataType' => 'string',
				'editable' => false,
				'editable' => false,
				'hidden'   => true,
				'filter'   => [
					'crules' => [['condition' => "begin"]],
				],
			],
			[

				'dataIndx'   => 'id_country',
				'dataType'   => 'string',
				'editable'   => false,
				'hidden'     => true,
				'hiddenable' => 'no',
				'filter'     => [
					'crules' => [['condition' => "equal"]],
				],

			],
			[
				'title'    => $this->l('Pays'),
				'width'    => 120,
				'exWidth'  => 30,
				'dataIndx' => 'country',
				'halign'   => 'HORIZONTAL_LEFT',
				'align'    => 'left',
				'valign'   => 'center',
				'editable' => false,
				'dataType' => 'string',
				'editable' => false,
				'hidden'   => true,
				'filter'   => [
					'attr'   => "id=\"countryStudentPiecesSelector\", placeholder=" . $this->l('--Select--') . " readonly",
					'crules' => [['condition' => "equal"]],
				],
			],
			[
				'title'        => $this->l('Montant HT'),
				'width'        => 150,
				'exWidth'      => 20,
				'dataIndx'     => 'education_tax_excl',
				'align'        => 'right',
				'halign'       => 'HORIZONTAL_RIGHT',
				'editable'     => false,
				'numberFormat' => '#,##0.00_-"€ ' . $this->l('TTC') . '"',
				'valign'       => 'center',
				'dataType'     => 'float',
				'format'       => '# ##0,00 € ' . $this->l('TTC'),
				'hidden'       => true,
				'filter'       => [
					'crules' => [['condition' => "contain"]],
				],
			],

			[
				'title'        => $this->l('Formation TTC.'),
				'width'        => 150,
				'exWidth'      => 20,
				'dataIndx'     => 'education_tax_incl',
				'align'        => 'right',
				'valign'       => 'center',
				'dataType'     => 'float',
				'halign'       => 'HORIZONTAL_RIGHT',
				'numberFormat' => '#,##0.00_-"€"',
				'editable'     => false,
				'format'       => "#.###,00 € " . $this->l('TTC'),
				'filter'       => [
					'crules' => [['condition' => "contain"]],
				],

				'hidden'       => true,
			],
			[
				'title'        => $this->l('Total HT.'),
				'width'        => 150,
				'excel'        => true,
				'dataIndx'     => 'total_tax_excl',
				'align'        => 'right',
				'valign'       => 'center',
				'dataType'     => 'float',
				'halign'       => 'HORIZONTAL_RIGHT',
				'numberFormat' => '#,##0.00_-"€"',
				'editable'     => false,
				'format'       => "#.###,00 € " . $this->l('HT'),
				'summary'      => [
					'type' => 'sum',
				],
				'filter'       => [
					'crules' => [['condition' => "contain"]],
				],
			],
			[
				'title'        => $this->l('Total TTC'),
				'width'        => 150,
				'excel'        => true,
				'dataIndx'     => 'total',
				'align'        => 'right',
				'valign'       => 'center',
				'dataType'     => 'float',
				'halign'       => 'HORIZONTAL_RIGHT',
				'numberFormat' => '#,##0.00_-"€"',
				'editable'     => false,
				'format'       => "#.###,00 € " . $this->l('TTC'),
				'summary'      => [
					'type' => 'sum',
				],
				'filter'       => [
					'crules' => [['condition' => "contain"]],
				],
			],
			[
				'title'        => $this->l('Réglé'),
				'width'        => 120,
				'excel'        => true,
				'dataIndx'     => 'total_paid',
				'align'        => 'right',
				'valign'       => 'center',
				'dataType'     => 'float',
				'halign'       => 'HORIZONTAL_RIGHT',
				'numberFormat' => '#,##0.00_-"€"',
				'editable'     => false,
				'format'       => "#.###,00 €",
				'summary'      => [
					'type' => 'sum',
				],

			],

			[
				'title'        => $this->l('Solde du'),
				'width'        => 120,
				'excel'        => true,
				'dataIndx'     => 'balanceDue',
				'align'        => 'right',
				'valign'       => 'center',
				'dataType'     => 'float',
				'halign'       => 'HORIZONTAL_RIGHT',
				'numberFormat' => '#,##0.00_-"€"',
				'editable'     => false,
				'format'       => "#.###,00 € ",
				'summary'      => [
					'type' => 'sum',
				],

			],

			[
				'title'    => $this->l('Date'),
				'minWidth' => 150,
				'excel'    => true,
				'dataIndx' => 'date_add',
				'cls'      => 'rangeDate',
				'align'    => 'center',
				'valign'   => 'center',
				'dataType' => 'date',
				'format'   => 'dd/mm/yy',
				'editable' => false,

			],
			[
				'title'      => ' ',
				'width'      => 10,
				'dataIndx'   => 'id_education_session',
				'dataType'   => 'string',
				'align'      => 'center',
				'valign'     => 'center',
				'hidden'     => true,
				'hiddenable' => 'no',
				'filter'     => [
					'crules' => [['condition' => "equal"]],
				],

			],

			[
				'title'      => ' ',
				'width'      => 10,
				'dataIndx'   => 'isLocked',
				'dataType'   => 'string',
				'align'      => 'center',
				'valign'     => 'center',
				'hidden'     => true,
				'hiddenable' => 'no',
				'filter'     => [
					'crules' => [['condition' => "equal"]],
				],

			],
			[
				'title'    => $this->l('Validé'),
				'width'    => 100,
				'exWidth'  => 20,
				'dataIndx' => 'validate',
				'editable' => false,
				'align'    => 'center',
				'valign'   => 'center',
				'halign'   => 'HORIZONTAL_CENTER',
				'cls'      => 'checkValidate',
				'dataType' => 'html',
				'filter'   => [
					'attr'   => "id=\"validateSelector\", placeholder=" . $this->l('--Select--') . " readonly",
					'crules' => [['condition' => "contain"]],
				],
			],
			[
				'title'      => ' ',
				'width'      => 10,
				'dataIndx'   => 'isBooked',
				'dataType'   => 'integer',
				'hidden'     => true,
				'hiddenable' => 'no',

			],
			[
				'title'    => $this->l('Comptabilisé'),
				'width'    => 100,
				'exWidth'  => 20,
				'dataIndx' => 'booked',
				'editable' => false,
				'align'    => 'center',
				'valign'   => 'center',
				'halign'   => 'HORIZONTAL_CENTER',
				'cls'      => 'checkValidate',
				'dataType' => 'html',

			],
			[
				'dataIndx'   => 'deleteLink',
				'dataType'   => 'string',
				'hidden'     => true,
				'hiddenable' => 'no',
			],

		];

	}

	public function ajaxProcessgetStudentPiecesFields() {

		die(EmployeeConfiguration::get('EXPERT_STUDENTPIECES_FIELDS'));
	}

	public function getDetailStudentPiecesFields() {

		return [
			[
				'dataIndx'   => 'id_student_piece_detail',
				'dataType'   => 'integer',
				'hidden'     => true,
				'hiddenable' => 'no',
				'render'     => 'function(ui){
                    if( ui.rowData.summaryRow ){
                        return "<b>"+ui.cellData+"</b>";
                    }
                }',

			],
			[
				'title'      => ' ',
				'width'      => 10,
				'dataIndx'   => 'id_book_record',
				'dataType'   => 'integer',
				'hidden'     => true,
				'hiddenable' => 'no',

			],
			[
				'dataIndx'   => 'id_student_piece',
				'dataType'   => 'integer',
				'hidden'     => true,
				'hiddenable' => 'no',

			],
			[
				'dataIndx'   => 'id_product',
				'dataType'   => 'integer',
				'hidden'     => true,
				'hiddenable' => 'no',

			],
			[
				'dataIndx'   => 'id_product_attribute',
				'dataType'   => 'integer',
				'hidden'     => true,
				'hiddenable' => 'no',
			],
			[
				'title'    => $this->l('Reference'),
				'width'    => 120,
				'dataIndx' => 'product_reference',
				'dataType' => 'string',
				'cls'      => '',
				'editable' => true,
				'editor'   => [
					'type' => "textbox",
					'init' => 'autoCompleteProduct',
				],
			],
			[
				'title'    => $this->l('Product'),
				'width'    => 180,
				'align'    => 'left',
				'dataIndx' => 'product_name',
				'dataType' => 'string',
				'editable' => true,
			],

			[
				'title'    => $this->l('Quantity'),
				'width'    => 80,
				'dataIndx' => 'product_quantity',
				'align'    => 'center',
				'dataType' => 'integer',
				'editable' => true,
			],
			[
				'title'    => $this->l('WholeSale price'),
				'width'    => 120,
				'dataIndx' => 'product_wholesale_price',
				'align'    => 'right',
				'valign'   => 'center',
				'dataType' => 'float',
				'format'   => '# ##0,00 €' . $this->l('HT'),
				'hidden'   => true,
			],
			[
				'dataIndx'   => 'origin_tax_excl',
				'hidden'     => true,
				'hiddenable' => 'no',
				'dataType'   => 'float',
			],
			[
				'title'    => $this->l('Base price'),
				'width'    => 120,
				'dataIndx' => 'unit_tax_excl',
				'align'    => 'right',
				'valign'   => 'center',
				'dataType' => 'float',
				'format'   => "#.###,00 € " . $this->l('HT'),

			],
			[
				'title'    => $this->l('Reduction %'),
				'width'    => 120,
				'dataIndx' => 'reduction_percent',
				'align'    => 'center',
				'valign'   => 'center',
				'dataType' => 'float',
				'format'   => "#.###,00 %",
			],
			[
				'title'    => $this->l('Reduction HT'),
				'width'    => 120,
				'dataIndx' => 'reduction_amount_tax_excl',
				'align'    => 'center',
				'valign'   => 'center',
				'dataType' => 'float',
				'format'   => "#.###,00 € " . $this->l('HT.'),
			],
			[
				'title'    => $this->l('Reduction TTC'),
				'width'    => 120,
				'dataIndx' => 'reduction_amount_tax_incl',
				'align'    => 'center',
				'valign'   => 'center',
				'dataType' => 'float',
				'format'   => "#.###,00 € " . $this->l('HT'),
				'hidden'   => true,
			],
			[
				'title'    => $this->l('Prix TTC'),
				'width'    => 120,
				'dataIndx' => 'unit_tax_incl',
				'align'    => 'right',
				'valign'   => 'center',
				'dataType' => 'float',
				'format'   => "#.###,00 € " . $this->l('TTC'),
			],
			[
				'title'    => $this->l('Eco Tax'),
				'width'    => 100,
				'dataIndx' => 'ecotax',
				'align'    => 'right',
				'valign'   => 'center',
				'dataType' => 'float',
				'format'   => "#.###,00 €",
				'hidden'   => true,
			],
			[
				'title'    => $this->l('Taux TVA'),
				'width'    => 100,
				'dataIndx' => 'tax_rate',
				'valign'   => 'center',
				'dataType' => 'float',
				'format'   => "#.###,00 %",
				'align'    => 'center',

			],
			[
				'title'    => $this->l('Total HT'),
				'width'    => 120,
				'dataIndx' => 'total_tax_excl',
				'align'    => 'right',
				'valign'   => 'center',
				'dataType' => 'float',
				'format'   => '# ##0,00 €' . $this->l('HT.'),
				'summary'  => [
					'type' => '\'sum\'',
				],
			],
			[
				'title'    => $this->l('Total TTC.'),
				'width'    => 120,
				'dataIndx' => 'total_tax_incl',
				'align'    => 'right',
				'valign'   => 'center',
				'dataType' => 'float',
				'format'   => "#.###,00 € " . $this->l('TTC'),
				'summary'  => [
					'type' => '\'sum\'',
				],
			],
			[
				'dataIndx'   => 'product_ean13',
				'dataType'   => 'string',
				'hidden'     => true,
				'hiddenable' => 'no',

			],
			[
				'dataIndx'   => 'product_upc',
				'dataType'   => 'string',
				'hidden'     => true,
				'hiddenable' => 'no',

			],
			[
				'dataIndx'   => 'id_tax_rules_group',
				'dataType'   => 'integer',
				'hidden'     => true,
				'hiddenable' => 'no',

			],
			[
				'dataIndx'   => 'product_weight',
				'dataType'   => 'float',
				'hidden'     => true,
				'hiddenable' => 'no',

			],
			[
				'dataIndx'   => 'origin_total_tax_excl',
				'dataType'   => 'float',
				'hidden'     => true,
				'hiddenable' => 'no',

			],
			[
				'dataIndx'   => 'wholesale_total_tax_excl',
				'dataType'   => 'float',
				'hidden'     => true,
				'hiddenable' => 'no',

			],
			[
				'dataIndx'   => 'line_margin_total_tax_excl',
				'dataType'   => 'float',
				'hidden'     => true,
				'hiddenable' => 'no',

			],

		];
	}

	public function ajaxProcessGetDetailStudentPiecesFields() {

		die(EmployeeConfiguration::get('EXPERT_STUDENTPIECES_DETAIL_FIELDS'));
	}

	public function getDetailStudentPiecesRequest($idCustomerPiece) {

		$transferts = ['QUOTATION', 'ORDER', 'DELIVERYFORM'];

		$piece = new StudentPieces($idCustomerPiece);

		if (in_array($piece->piece_type, $transferts) && $piece->last_transfert > 0) {
			$idPiece = StudentPieces::getPieceIdbyTransfert($piece->last_transfert);
		} else {
			$idPiece = $piece->id;
		}

		$details = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
			(new DbQuery())
				->select('*')
				->from('student_piece_detail')
				->where('`id_student_piece` = ' . $idPiece)
				->orderBy('`id_student_piece_detail` ASC')
		);

		if (!empty($details)) {
			$details = $this->removeDetailRequestFields($details);
		}

		return $details;
	}

	public function ajaxProcessGetDetailStudentPiecesRequest() {

		$idPiece = Tools::getValue('idPiece');
		die(Tools::jsonEncode($this->getDetailStudentPiecesRequest($idPiece)));

	}

	public function ajaxProcessConvertBulkPiece() {

		$idPieces = Tools::getValue('idPieces');
		$target = Tools::getValue('target');

		foreach ($idPieces as $id) {
			$piece = new StudentPieces($id);

			if (Validate::isLoadedObject($piece)) {
				$piece->piece_type = $target;
				$piece->update();

			} else {

				$this->errors[] = Tools::displayError('An error occurred while loading the piece.');
			}

		}

		if (count($this->errors)) {
			$result = [
				'success' => false,
				'message' => implode(PHP_EOL, $this->errors),
			];
		} else {
			$result = [
				'success' => true,
				'message' => $this->l('Les pièces ont été mises à jour avec succès'),
			];

		}

		die(Tools::jsonEncode($result));

	}

	public function ajaxProcessBulkValidate() {

		$idPieces = Tools::getValue('idPieces');

		foreach ($idPieces as $id) {
			$piece = new StudentPieces($id);

			if (Validate::isLoadedObject($piece)) {
				$piece->piece_type = 'INVOICE';
				$piece->validate = 1;
				$piece->update();
				$this->printPdf($piece->id);

			} else {

				$this->errors[] = Tools::displayError('An error occurred while loading the piece.');
			}

		}

		if (count($this->errors)) {
			$result = [
				'success' => false,
				'message' => implode(PHP_EOL, $this->errors),
			];
		} else {
			$result = [
				'success' => true,
				'message' => $this->l('Les pièces ont été mises à jour avec succès'),
			];

		}

		die(Tools::jsonEncode($result));
	}

	public function ajaxProcessConvertPiece() {

		$idPiece = Tools::getValue('idPiece');
		$target = Tools::getValue('target');

		$piece = new StudentPieces($idPiece);

		if (Validate::isLoadedObject($piece)) {
			$piece->piece_type = $target;
			$piece->update();

			$result = [
				'success' => true,
				'message' => $this->l('La pièce a été mise à jour avec succès'),
			];

		} else {

			$this->errors[] = Tools::displayError('An error occurred while loading the piece.');
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

	public function removeDetailRequestFields($requests) {

		$objects = [];

		$fields = [];
		$gridFields = $this->getDetailStudentPiecesFields();

		foreach ($gridFields as $grifField) {
			$fields[] = $grifField['dataIndx'];
		}

		foreach ($requests as $key => $object) {

			foreach ($object as $field => $value) {

				if (in_array($field, $fields)) {
					$objects[$key][$field] = $value;
				}

			}

		}

		return $objects;

	}

	public static function setStudentPiecesCurrency($echo, $tr) {

		$order = new StudentPieces($tr['id_order']);

		return Tools::displayPrice($echo, (int) $order->id_currency);
	}

	
	public function generateDefaultTemplate() {

		return [
			'fields' => [
				'product_reference'       => ['name' => $this->l('Reference'), 'format' => ''],
				'product_name'            => ['name' => $this->l('Description'), 'format' => ''],
				'original_price_tax_excl' => ['name' => $this->l('Unit price tax excl'), 'align' => 'right', 'format' => 'money'],
				'reduction_percent'       => ['name' => $this->l('Reduction percent'), 'align' => 'center', 'format' => ''],
				'product_quantity'        => ['name' => $this->l('Quantity'), 'talign' => 'center', 'align' => 'center', 'format' => ''],
				'unit_tax_excl'           => ['name' => $this->l('Unit tax excl with disount'), 'align' => 'right', 'format' => 'money'],
				'total_tax_excl'          => ['name' => $this->l('Total tax excl'), 'align' => 'right', 'format' => 'money'],
			],
			'color'  => '#1E90FF',

		];
	}

	protected function getInvoicesModels() {

		$idLang = Context::getContext()->language->id;

		$templates = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
			(new DbQuery())
				->select('a.*, b.`value`')
				->from('employee_configuration', 'a')
				->leftJoin('employee_configuration_lang', 'b', 'b.`id_employee_configuration` = a.`id_employee_configuration`  AND b.`id_lang` = ' . $idLang)
				->where('a.`name` LIKE \'EPH_TEMPLATE_%\'')
				->orderBy('a.`id_employee_configuration` DESC ')
		);

		$models = [];

		foreach ($templates as $template) {
			$models[] = [
				'value' => $template['name'],
				'name'  => ucfirst(strtolower(str_replace('EPH_TEMPLATE_', '', $template['name']))),
			];
		}

		return $models;
	}

	public function ajaxProcessEditStudentPieces() {

		$id_student_piece = Tools::getValue('id_student_piece');
		$this->object = new $this->className($id_student_piece);
		$student_education = new StudentEducation($this->object->id_student_education);
		$pieceCost = StudentPieces::getPieceCost($student_education);
		$this->display = 'edit';
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

		$data = $this->createTemplate('controllers/student_pieces/editPiece.tpl');
		$student = new Customer($this->object->id_customer);
		$data->assign('education', Student::getStudentEducationById($this->object->id_student_education));
		$data->assign('educationSteps', StudentEducationStep::getEducationStep());
		$data->assign('student', $student);
		$data->assign('supplies', EducationSupplies::getEducationSupplies());
		$data->assign('student_education', $student_education);
		$data->assign('piece', $this->object);
		$data->assign('invoiceModels', $this->getInvoicesModels());
		$data->assign('nameType', $this->object->nameType);
		$data->assign(
			[
				'currency'          => $currency = $this->context->currency,
				'tax_rules_groups'  => $taxRulesGroups,
				'taxesRatesByGroup' => $taxRates,
				'taxes'             => Tax::getRulesTaxes($this->context->language->id),
				'pieceCost'         => $pieceCost,
			]
		);
		$result = [
			'html'  => $data->fetch(),
			'title' => $this->l('Edition de la pièce ') . $this->object->nameType . $this->object->piece_number,
		];

		die(Tools::jsonEncode($result));

	}
	
	public function ajaxProcessGenerateNewPiece() {
		
		$type = Tools::getValue('type');
        $prefix = $this->getStaticPrefix($type);
		$increment = StudentPieces::getIncrementByType($type);
		
		$data = $this->createTemplate('controllers/student_pieces/newPiece.tpl');

        $this->context->smarty->assign([
            'type'               => $type,
            'nameType'           => $this->pieceType[$type],
            'piece_number'       => $prefix . $increment,
			'customHeaderFields' => $this->manageFieldsVisibility($this->configurationDetailField),
            'taxModes'           => TaxMode::getTaxModes(),
            'currency'           => $this->context->currency,
            'taxes'              => Tax::getRulesTaxes($this->context->language->id),
            'groups'             => Group::getGroups($this->context->language->id),
            'paymentModes'       => PaymentMode::getPaymentModes(),
            'link'               => $this->context->link,
            'id_tab'             => $this->identifier_value,
            'formId'             => 'form-customer' ,
            //'tabScript'          => $this->generatenewPieceScript(),
        ]);
		
		$li = '<li id="uperNewPieces" data-controller="newPieces"><a href="#contentNewPieces">Ajouter une pièce client de type : .'.$this->pieceType[$type].'</a><button type="button" id="closeNewPiece" class="close tabdetail" data-id="uperNewPieces"><i class="icon icon-times-circle"></i></button></li>';
		$html = '<div id="contentNewPieces" class="panel col-lg-12" style="display; flow-root;">' . $data->fetch() . '</div>';

		$result = [
			'li'   => $li,
			'html' => $html,
		];		

		die(Tools::jsonEncode($result));
	}

	public function ajaxProcessDeleteStudentPieces() {

		$id_student_piece = Tools::getValue('id_student_piece');
		$this->object = new $this->className($id_student_piece);
		$this->object->delete();
		$result = [
			'success' => true,
			'message' => $this->l('La pièce a été supprimée avec succès '),
		];

		die(Tools::jsonEncode($result));

	}
	
	public function ajaxProcessSaveNewPiece() {

        $context = Context::getContext();
		$newPiece = new CustomerPiece();
        $pieceDetails = Tools::jsonDecode(Tools::getValue('details'), true);

        foreach ($_GET as $key => $value) {

            if (property_exists($newPiece, $key) && $key != 'id_student_piece') {

                $newPiece->{$key} = $value;
            }

        }

        $newPiece->id_shop = (int) $this->context->shop->id;
        $newPiece->id_shop_group = (int) $this->context->shop->id_shop_group;
        $newPiece->id_lang = $this->context->language->id;
        $newPiece->round_mode = Configuration::get('PS_PRICE_ROUND_MODE');
        $newPiece->round_type = Configuration::get('PS_ROUND_TYPE');
        $newPiece->date_add = date("Y-m-d H:i:s");

        if ($newPiece->add()) {

            foreach ($pieceDetails as $details) {
                $object = new CustomerPieceDetail();
                $object->id_customer_piece = $newPiece->id;
                $object->id_warehouse = 0;

                foreach ($details as $key => $value) {

                    if (property_exists($object, $key) && $key != 'id_student_piece') {
                        $object->{$key}

                        = $value;
                    }

                }

                if (!$object->add()) {
                    $this->errors[] = Tools::displayError('An error occurred while creating an object.') . ' <strong> piece detail (' . Db::getInstance()->getMsgError() . ')</strong>';
                    Logger::addLog('piece detail (' . Db::getInstance()->getMsgError() . ')', 2, null, $this->className, null, true, (int) $this->context->employee->id);
                }

            }

        } else {
            $response = [
                'success' => false,
                'message' => $this->errors[] = Tools::displayError('An error occurred while creating an object.') . ' <strong>' . $this->table . ' (' . Db::getInstance()->getMsgError() . ')</strong>',
            ];
        }

        if (count($this->errors)) {
            $this->errors = array_unique($this->errors);
            $result = [
                'success' => false,
                'message' => implode(PHP_EOL, $this->errors),
            ];
        } else {
            $result = [
                'success' => true,
                'message' => $this->l('New pieces successfully added'),
            ];
        }

        die(Tools::jsonEncode($result));
    }

	public function ajaxProcessValidateStudentPieces() {

		$id_student_piece = Tools::getValue('id_student_piece');
		$this->object = new $this->className($id_student_piece);
		$this->object->validate = 1;
		$this->object->update();
		$this->printPdf($this->object-- > id);
		$result = [
			'success' => true,
			'message' => $this->l('La pièce a été validée avec succès '),
		];

		die(Tools::jsonEncode($result));

	}

	public function ajaxProcessUpdateStudentPiece() {

		
		$id_student_piece = Tools::getValue('id_student_piece');
		$piece = new StudentPieces($id_student_piece);

		foreach ($_POST as $key => $value) {

			if (property_exists($piece, $key) && $key != 'id_student_piece') {

				$piece->{$key}	= $value;

			}

		}
		
		$piece->piece_margin = $piece->education_tax_excl - 550;
		$piece->total_tax = $piece->education_tax_incl - $piece->education_tax_excl;
		$result = $piece->update();

		if ($result) {

			$return = [
				'success' => true,
				'message' => $this->l('La pièce commerciale a été mise à jour avec succès'),
			];
		} else {
			$return = [
				'success' => false,
				'message' => $this->l('Une erreur s\'est produite en essayant de mettre à jour cette pièce commeriale'),
			];
		}

		die(Tools::jsonEncode($return));
	}

	

	public function ajaxProcessgetAutoCompleteCustomer() {

		$students = Db::getInstance()->executeS(
			(new DbQuery())
				->select('c.`id_customer`, c.`customer_code`, c.`lastname`, c.`firstname`')
				->from('customer', 'c')
				->join(Shop::addSqlAssociation('customer', 'c'))
				->where('c.`active` = 1')
		);

		die(Tools::jsonEncode($students));
	}
	
	public function ajaxProcesscreateNewCustomer() {
		
		$data = $this->createTemplate('controllers/student_pieces/newCustomer.tpl');

		$groups = Group::getGroups($this->default_form_language, true);
		
		$allgroups = Group::getGroups($this->default_form_language, true);
		
		$data->assign([
            
			'taxModes'          => TaxMode::getTaxModes(),
            'currency'          => $this->context->currency,
            'countries'         => Country::getCountries($this->context->language->id, false),
			'default_country'   => Configuration::get('PS_COUNTRY_DEFAULT'),
            'taxes'             => Tax::getRulesTaxes($this->context->language->id),
			'tarifs'            => Customer::getTarifs(),
			'genders'           => Gender::getGenders(),
            'paymentModes'      => PaymentMode::getPaymentModes(),
          	'groups'                 => $groups,
			'allgroups'				=> $allgroups,
            'link'              => $this->context->link,
            'id_tab'            => $this->identifier_value,
            'formId'            => 'form-' . $this->table,
           
        ]);
		
		$result = [
			'html' => $data->fetch(),
		];

		die(Tools::jsonEncode($result));
	}

	public function ajaxProcessGetCustomerInformation() {

		$idCustomer = Tools::getValue('idCustomer');
		$customer = new Customer($idCustomer);
		$studentLastAddress = $this->getLastCustomerAddressId((int) $customer->id);
		$addresses = $customer->getAddresses($this->context->language->id);
		$html = '<option value="0">' . $this->l('Select Address') . '</option>';

		foreach ($addresses as $addresse) {
			$html .= '<option value="' . $addresse['id_address'] . '">' . $addresse['alias'] . '</option>';
		}

		$returnAddress = [];

		foreach ($addresses as $key => $addresse) {
			$returnAddress[$addresse['id_address']] = $addresse;
		}

		$result = [
			'customer'            => (array) $customer,
			'selectAddress'      => $html,
			'addresses'          => $returnAddress,
			'studentLastAddress' => $studentLastAddress,
		];
		die(Tools::jsonEncode($result));
	}

	public function getLastCustomerAddressId($id_student, $active = true) {

		if (!$id_student) {
			return false;
		}

		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
			(new DbQuery())
				->select('`id_address`')
				->from('address')
				->where('`deleted` = 0 AND `id_customer` = ' . (int) $id_student)
				->orderBy('`date_add` DESC')
		);
	}

	public function ajaxProcessAutoCompleteProduct() {

		$keyword = Tools::getValue('keyword', false);
		$context = Context::getContext();
		$idCustomer = Tools::getValue('idCustomer');
		$student = new Customer($idCustomer);

		$items = Db::getInstance()->executeS(
			(new DbQuery())
				->select('p.`id_product`, p.`id_tax_rules_group`, p.`reference`, pl.`name`, p.`wholesale_price`, p.`price`, p.`ecotax`, p.`weight`, p.`ean13`, p.`upc`, t.rate, p.`cache_default_attribute` as `id_product_attribute`')
				->from('product', 'p')
				->join(Shop::addSqlAssociation('product', 'p'))
				->leftJoin('product_lang', 'pl', 'pl.id_product = p.id_product AND pl.id_lang = ' . (int) $context->language->id)
				->leftJoin('tax_rules_group', 'tl', 'tl.`id_tax_rules_group` = p.`id_tax_rules_group`')
				->leftJoin('tax', 't', 't.`id_tax` = tl.`id_tax_rules_group`')
				->where('p.active = 1 AND (pl.name LIKE \'%' . pSQL($keyword) . '%\' OR p.reference LIKE \'%' . pSQL($keyword) . '%\') AND p.`active` = 1')
				->groupBy('p.`id_product`')
		);

		if ($items) {

			foreach ($items as &$item) {

				$item['price'] = Product::getPriceStatic($item['id_product'], false, null, 6, null, false, true, 1, false, $idCustomer);
			}

			$results = [];

			foreach ($items as $item) {

				if (Combination::isFeatureActive() && $item['id_product_attribute']) {

					$combinations = Db::getInstance()->executeS(
						(new DbQuery())
							->select('pa.`id_product_attribute`, pa.`reference`, pa.`wholesale_price`, pa.`price`, pa.`ecotax`, ag.`id_attribute_group`, agl.`name` AS group_name, al.`name` AS attribute_name,
                        a.`id_attribute`')
							->from('product_attribute', 'pa')
							->join(Shop::addSqlAssociation('product_attribute', 'pa'))
							->leftJoin('product_attribute_combination', 'pac', 'pac.`id_product_attribute` = pa.`id_product_attribute`')
							->leftJoin('attribute', 'a', 'a.`id_attribute` = pac.`id_attribute`')
							->leftJoin('attribute_lang', 'al', 'a.`id_attribute` = al.`id_attribute` AND al.`id_lang` = ' . (int) $context->language->id)
							->leftJoin('attribute_group', 'ag', 'ag.`id_attribute_group` = a.`id_attribute_group`')
							->leftJoin('attribute_group_lang', 'agl', 'ag.`id_attribute_group` = agl.`id_attribute_group` AND agl.`id_lang` = ' . (int) $context->language->id)
							->where('pa.`id_product` = ' . (int) $item['id_product'])
							->groupBy('pa.`id_product_attribute`, ag.`id_attribute_group`')
							->orderBy('pa.`id_product_attribute`')
					);

					if (!empty($combinations)) {

						foreach ($combinations as $k => $combination) {
							$result = [];
							$result['id_product'] = $item['id_product'];
							$result['id_product_attribute'] = $combination['id_product_attribute'];
							$result['reference'] = $combination['reference'];
							$result['wholesale_price'] = $combination['wholesale_price'] + $item['wholesale_price'];
							$result['price'] = Product::getPriceStatic($item['id_product'], false, $combination['id_product_attribute'], 6, null, false, true, 1, false, $idCustomer);
							$result['ecotax'] = $combination['ecotax'] + $item['ecotax'];
							$result['id_product_attribute'] = $item['id_product_attribute'];
							$result['ean13'] = $item['ean13'];
							$result['upc'] = $item['upc'];
							$result['weight'] = $combination['weight'] + $item['weight'];
							$result['rate'] = $item['rate'];
							$result['name'] = $item['name'] . ' ' . $combination['group_name'] . '-' . $combination['attribute_name'];
							array_push($results, $result);

						}

					} else {
						array_push($results, $item);

					}

				} else {

					array_push($results, $item);
				}

			}

			$results = array_map("unserialize", array_unique(array_map("serialize", $results)));

			$results = Tools::jsonEncode($results, JSON_NUMERIC_CHECK);

			die($results);
		} else {
			json_encode(new stdClass);
		}

	}

	public function getStaticPrefix($pieceType) {

		switch ($pieceType) {

		case 'QUOTATION':
			return $this->l('DE');
			break;
		case 'ORDER':
			return $this->l('CD');
			break;
		case 'INVOICE':
			return $this->l('FA');
			break;

		}

	}

	public function getStaticPieceName($pieceType) {

		switch ($pieceType) {

		case 'QUOTATION':
			return $this->l('Quotation');
			break;
		case 'ORDER':
			return $this->l('Purchase Order');
			break;
		case 'DELIVERYFORM':
			return $this->l('Delivery Form');
			break;
		case 'DOWNPINVOICE':
			return $this->l('Down payment invoice');
			break;
		case 'INVOICE':
			return $this->l('Invoice');
			break;
		case 'ASSET':
			return $this->l('Commercial Asset');
			break;
		}

	}

	public function ajaxProcessPieceToPdf() {

		$idPiece = Tools::getValue('idPiece');
		$context = Context::getContext();
		$studentPiece = new StudentPieces($idPiece);
		$studentEducation = new StudentEducation($studentPiece->id_student_education);
		$studentPiece->prefix = $this->getStaticPrefix($studentPiece->piece_type);
		$studentPiece->nameType = $this->getStaticPieceName($studentPiece->piece_type);
		$student = new Customer($studentPiece->id_customer);

		$context = Context::getContext();
		$idShop = (int) $context->shop->id;

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

		$payments = Payment::getByCustomerPieceId($studentPiece->id, $context->language->id);
		$data = $this->createTemplate('controllers/student_pieces/piecetemplate.tpl');
		$link = $this->renderPdf($idPiece);
		$data->assign(
			[
				'company'          => $context->company,
				'free_text'        => Configuration::get('PS_INVOICE_FREE_TEXT', (int) Context::getContext()->language->id, null, $context->shop->id),
				'logo_path'        => $logo_path,
				'piece'            => $studentPiece,
				'payments'         => $payments,
				'nameType'         => 'FACTURE',
				'student'          => $student,
				'studentEducation' => $studentEducation,
				'link'             => $link,
			]
		);
		$json = [
			'id'  => $studentPiece->id,
			'tpl' => $data->fetch(),
		];
		die(Tools::jsonEncode($json));

	}

	public function renderPdf($idPiece) {

		$context = Context::getContext();
		$studentPiece = new StudentPieces($idPiece);
		$studentEducation = new StudentEducation($studentPiece->id_student_education);
		$studentPiece->prefix = $this->getStaticPrefix($studentPiece->piece_type);
		$studentPiece->nameType = $this->getStaticPieceName($studentPiece->piece_type);
		$student = new Customer($studentPiece->id_customer);

		$context = Context::getContext();
		$idShop = (int) $context->shop->id;

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
			'margin_top'    => 120,
			'margin_bottom' => 75,
			'margin_header' => 10,
			'margin_footer' => 10,
		]);
		$payments = Payment::getByCustomerPieceId($studentPiece->id, $context->language->id);
		$data = $this->createTemplate('controllers/student_pieces/pdf/headertemplate.tpl');

		$data->assign(
			[
				'company'          => $context->company,
				'free_text'        => Configuration::get('PS_INVOICE_FREE_TEXT', (int) Context::getContext()->language->id, null, $context->shop->id),
				'logo_path'        => $pathLogo,
				'piece'            => $studentPiece,
				'nameType'         => 'FACTURE',
				'payments'         => $payments,
				'student'          => $student,
				'studentEducation' => $studentEducation,
			]
		);
		$mpdf->SetHTMLHeader($data->fetch());

		$data = $this->createTemplate('controllers/student_pieces/pdf/footertemplate.tpl');

		$data->assign(
			[
				'company'          => $context->company,
				'free_text'        => Configuration::get('PS_INVOICE_FREE_TEXT', (int) Context::getContext()->language->id, null, $context->shop->id),
				'logo_path'        => $logo_path,
				'piece'            => $studentPiece,
				'payments'         => $payments,
				'nameType'         => 'FACTURE',
				'student'          => $student,
				'studentEducation' => $studentEducation,
			]
		);
		$mpdf->SetHTMLFooter($data->fetch(), 'O');

		$data = $this->createTemplate('controllers/student_pieces/pdf.css.tpl');
		$data->assign(
			[
				'color' => '#fa4616',
			]
		);
		$stylesheet = $data->fetch();

		$data = $this->createTemplate('controllers/student_pieces/pdf/bodytemplate.tpl');

		$data->assign(
			[
				'company'          => $context->company,
				'free_text'        => Configuration::get('PS_INVOICE_FREE_TEXT', (int) Context::getContext()->language->id, null, $context->shop->id),
				'logo_path'        => $logo_path,
				'piece'            => $studentPiece,
				'payments'         => $payments,
				'nameType'         => 'FACTURE',
				'student'          => $student,
				'studentEducation' => $studentEducation,
			]
		);

		if ($studentPiece->validate == 0 && $studentPiece->piece_type == 'INVOICE') {
			$watermark = $this->l('Provisoire');
			$mpdf->SetWatermarkText($watermark);
		} else

		if ($studentPiece->validate == 1 && $studentPiece->piece_type == 'INVOICE') {
			$mpdf->SetProtection(['copy', 'print'], '', _DB_PASSWD_);
		}

		$filePath = _PS_ADMIN_DIR_ . DIRECTORY_SEPARATOR . 'invoices' . DIRECTORY_SEPARATOR;
		$fileName = "Facture_" . $studentPiece->prefix . $studentPiece->piece_number . '_Dossier_n°' . $studentEducation->reference_edof . '_' . $student->lastname . '_' . $student->firstname . '.pdf';
		$mpdf->SetTitle($context->company->company_name . " " . $this->getStaticPieceName($studentPiece->piece_type) . " " . $studentPiece->prefix . $studentPiece->piece_number);
		$mpdf->SetAuthor($context->company->company_name);
		$mpdf->showWatermarkText = true;
		$mpdf->watermark_font = 'DejaVuSansCondensed';
		$mpdf->watermarkTextAlpha = 0.1;
		$mpdf->SetDisplayMode('fullpage');

		$mpdf->WriteHTML($stylesheet, \Mpdf\HTMLParserMode::HEADER_CSS);
		$mpdf->WriteHTML($data->fetch());

		$mpdf->Output($filePath . $fileName, 'F');
		$fileToUpload = 'invoices' . DIRECTORY_SEPARATOR . $fileName;
		$link = '<a  target="_blank"  class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" id="pieceDownloadFile" class="btn btn-default" href="' . $fileToUpload . '">' . $this->l('Exporter au format PDF') . '</a>';
		return $link;

	}

	public function ajaxProcessPrintPdf() {

		$idPiece = Tools::getValue('idPiece');
		$context = Context::getContext();
		$studentPiece = new StudentPieces($idPiece);
		$studentEducation = new StudentEducation($studentPiece->id_student_education);
		$studentPiece->prefix = $this->getStaticPrefix($studentPiece->piece_type);
		$studentPiece->nameType = $this->getStaticPieceName($studentPiece->piece_type);
		$student = new Customer($studentPiece->id_customer);

		$context = Context::getContext();
		$idShop = (int) $context->shop->id;

		if (Configuration::get('PS_LOGO_INVOICE', null, null, $idShop) != false && file_exists(_PS_IMG_DIR_ . Configuration::get('PS_LOGO_INVOICE', null, null, $idShop))) {
			$logo = _PS_IMG_DIR_ . Configuration::get('PS_LOGO_INVOICE', null, null, $idShop);
			$logo_path = _PS_IMG_ . Configuration::get('PS_LOGO_INVOICE', null, null, $idShop);
		} else

		if (Configuration::get('PS_LOGO', null, null, $idShop) != false && file_exists(_PS_IMG_DIR_ . Configuration::get('PS_LOGO', null, null, $idShop))) {
			$logo = _PS_IMG_DIR_ . Configuration::get('PS_LOGO', null, null, $idShop);
			$logo_path = _PS_IMG_ . Configuration::get('PS_LOGO', null, null, $idShop);
		}

		$mpdf = new \Mpdf\Mpdf([
			'margin_left'   => 10,
			'margin_right'  => 10,
			'margin_top'    => 120,
			'margin_bottom' => 75,
			'margin_header' => 10,
			'margin_footer' => 10,
		]);
		$payments = Payment::getByCustomerPieceId($studentPiece->id, $context->language->id);
		$data = $this->createTemplate('controllers/student_pieces/pdf/headertemplate.tpl');

		$data->assign(
			[
				'company'          => $context->company,
				'free_text'        => Configuration::get('PS_INVOICE_FREE_TEXT', (int) Context::getContext()->language->id, null, $context->shop->id),
				'logo_path'        => $logo_path,
				'piece'            => $studentPiece,
				'nameType'         => 'FACTURE',
				'payments'         => $payments,
				'student'          => $student,
				'studentEducation' => $studentEducation,
			]
		);
		$mpdf->SetHTMLHeader($data->fetch());

		$data = $this->createTemplate('controllers/student_pieces/pdf/footertemplate.tpl');

		$data->assign(
			[
				'company'          => $context->company,
				'free_text'        => Configuration::get('PS_INVOICE_FREE_TEXT', (int) Context::getContext()->language->id, null, $context->shop->id),
				'logo_path'        => $logo_path,
				'piece'            => $studentPiece,
				'payments'         => $payments,
				'nameType'         => 'FACTURE',
				'student'          => $student,
				'studentEducation' => $studentEducation,
			]
		);
		$mpdf->SetHTMLFooter($data->fetch(), 'O');

		$data = $this->createTemplate('controllers/student_pieces/pdf.css.tpl');
		$data->assign(
			[
				'color' => '#fa4616',
			]
		);
		$stylesheet = $data->fetch();

		$data = $this->createTemplate('controllers/student_pieces/pdf/bodytemplate.tpl');

		$data->assign(
			[
				'company'          => $context->company,
				'free_text'        => Configuration::get('PS_INVOICE_FREE_TEXT', (int) Context::getContext()->language->id, null, $context->shop->id),
				'logo_path'        => $logo_path,
				'piece'            => $studentPiece,
				'payments'         => $payments,
				'nameType'         => 'FACTURE',
				'student'          => $student,
				'studentEducation' => $studentEducation,
			]
		);

		if ($studentPiece->validate == 0 && $studentPiece->piece_type == 'INVOICE') {
			$watermark = $this->l('Provisoire');
			$mpdf->SetWatermarkText($watermark);
		} else

		if ($studentPiece->validate == 1 && $studentPiece->piece_type == 'INVOICE') {
			$mpdf->SetProtection(['copy', 'print'], '', _DB_PASSWD_);
		}

		$filePath = _PS_ADMIN_DIR_ . DIRECTORY_SEPARATOR . 'invoices' . DIRECTORY_SEPARATOR;
		$fileName = "Facture_" . $studentPiece->prefix . $studentPiece->piece_number . '_Dossier_n°' . $studentEducation->reference_edof . '_' . $student->lastname . '_' . $student->firstname . '.pdf';
		$mpdf->SetTitle($context->company->company_name . " " . $this->getStaticPieceName($studentPiece->piece_type) . " " . $studentPiece->prefix . $studentPiece->piece_number);
		$mpdf->SetAuthor($context->company->company_name);
		$mpdf->showWatermarkText = true;
		$mpdf->watermark_font = 'DejaVuSansCondensed';
		$mpdf->watermarkTextAlpha = 0.1;
		$mpdf->SetDisplayMode('fullpage');

		$mpdf->WriteHTML($stylesheet, \Mpdf\HTMLParserMode::HEADER_CSS);
		$mpdf->WriteHTML($data->fetch());

		$mpdf->Output($filePath . $fileName, 'F');

		$response = [
			'fileExport' => 'invoices' . DIRECTORY_SEPARATOR . $fileName,
		];
		die(Tools::jsonEncode($response));

	}

	public function ajaxProcessgenerateBulkprint() {

		$idPieces = Tools::getValue('pieces');
		$context = Context::getContext();
		$idShop = (int) $context->shop->id;

		if (Configuration::get('PS_LOGO_INVOICE', null, null, $idShop) != false && file_exists(_PS_IMG_DIR_ . Configuration::get('PS_LOGO_INVOICE', null, null, $idShop))) {
			$logo = _PS_IMG_DIR_ . Configuration::get('PS_LOGO_INVOICE', null, null, $idShop);
			$logo_path = _PS_IMG_ . Configuration::get('PS_LOGO_INVOICE', null, null, $idShop);
		} else

		if (Configuration::get('PS_LOGO', null, null, $idShop) != false && file_exists(_PS_IMG_DIR_ . Configuration::get('PS_LOGO', null, null, $idShop))) {
			$logo = _PS_IMG_DIR_ . Configuration::get('PS_LOGO', null, null, $idShop);
			$logo_path = _PS_IMG_ . Configuration::get('PS_LOGO', null, null, $idShop);
		}

		$filePath = 'invoices' . DIRECTORY_SEPARATOR;

		$fileExport = [];

		foreach ($idPieces as $idPiece) {

			$studentPiece = new StudentPieces($idPiece);
			$studentEducation = new StudentEducation($studentPiece->id_student_education);
			$studentPiece->prefix = $this->getStaticPrefix($studentPiece->piece_type);
			$studentPiece->nameType = $this->getStaticPieceName($studentPiece->piece_type);
			$student = new Customer($studentPiece->id_customer);

			$fileName = "Facture_" . $prefix . $studentPiece->piece_number . '_Dossier_n°' . $studentEducation->reference_edof . '_' . $student->lastname . '_' . $student->firstname . '.pdf';

			if (file_exists('invoices' . DIRECTORY_SEPARATOR . $fileName)) {
				$fileExport[] = 'invoices' . DIRECTORY_SEPARATOR . $fileName;
				continue;
			}

			$mpdf = new \Mpdf\Mpdf([
				'margin_left'   => 10,
				'margin_right'  => 10,
				'margin_top'    => 120,
				'margin_bottom' => 75,
				'margin_header' => 10,
				'margin_footer' => 10,
			]);
			$payments = Payment::getByCustomerPieceId($studentPiece->id, $context->language->id);
			$data = $this->createTemplate('controllers/student_pieces/pdf/headertemplate.tpl');

			$data->assign(
				[
					'company'          => $context->company,
					'free_text'        => Configuration::get('PS_INVOICE_FREE_TEXT', (int) Context::getContext()->language->id, null, $context->shop->id),
					'logo_path'        => $logo_path,
					'piece'            => $studentPiece,
					'nameType'         => 'FACTURE',
					'payments'         => $payments,
					'student'          => $student,
					'studentEducation' => $studentEducation,
				]
			);
			$mpdf->SetHTMLHeader($data->fetch());

			$data = $this->createTemplate('controllers/student_pieces/pdf/footertemplate.tpl');

			$data->assign(
				[
					'company'          => $context->company,
					'free_text'        => Configuration::get('PS_INVOICE_FREE_TEXT', (int) Context::getContext()->language->id, null, $context->shop->id),
					'logo_path'        => $logo_path,
					'piece'            => $studentPiece,
					'nameType'         => 'FACTURE',
					'payments'         => $payments,
					'student'          => $student,
					'studentEducation' => $studentEducation,
				]
			);
			$mpdf->SetHTMLFooter($data->fetch(), 'O');

			$data = $this->createTemplate('controllers/student_pieces/pdf.css.tpl');
			$data->assign(
				[
					'color' => '#fa4616',
				]
			);
			$stylesheet = $data->fetch();

			$data = $this->createTemplate('controllers/student_pieces/pdf/bodytemplate.tpl');

			$data->assign(
				[
					'company'          => $context->company,
					'free_text'        => Configuration::get('PS_INVOICE_FREE_TEXT', (int) Context::getContext()->language->id, null, $context->shop->id),
					'logo_path'        => $logo_path,
					'piece'            => $studentPiece,
					'nameType'         => 'FACTURE',
					'payments'         => $payments,
					'student'          => $student,
					'studentEducation' => $studentEducation,
				]
			);

			if ($studentPiece->validate == 0 && $studentPiece->piece_type == 'INVOICE') {
				$watermark = $this->l('Provisoire');
				$mpdf->SetWatermarkText($watermark);
			} else

			if ($studentPiece->validate == 1 && $studentPiece->piece_type == 'INVOICE') {
				$mpdf->SetProtection(['copy', 'print'], '', _DB_PASSWD_);
			}

			$mpdf->SetTitle($context->company->company_name . " " . $this->getStaticPieceName($studentPiece->piece_type) . " " . $studentPiece->prefix . $studentPiece->piece_number);
			$mpdf->SetAuthor($context->company->company_name);
			$mpdf->showWatermarkText = true;
			$mpdf->watermark_font = 'DejaVuSansCondensed';
			$mpdf->watermarkTextAlpha = 0.1;
			$mpdf->SetDisplayMode('fullpage');

			$mpdf->WriteHTML($stylesheet, \Mpdf\HTMLParserMode::HEADER_CSS);
			$mpdf->WriteHTML($data->fetch());

			$mpdf->Output($filePath . $fileName, 'F');

			$fileExport[] = 'invoices' . DIRECTORY_SEPARATOR . $fileName;
		}

		$zip = new ZipArchive;

		if ($zip->open(_PS_EXPORT_DIR_ . 'export_facture_'.time().'.zip', ZipArchive::CREATE) === TRUE) {

			foreach ($fileExport as $invoice) {
				$zip->addFile($invoice, basename($invoice));
			}

			$zip->close();

			$response = [
				'fileExport' => 'fileExport' . DIRECTORY_SEPARATOR . 'export_facture_'.time().'.zip',
			];
			die(Tools::jsonEncode($response));
		}

	}

	public function ajaxProcessExportSessionExcel() {

		$idSession = Tools::getvalue('idSession');

		$session = [
			'id_education_session' => $idSession,
		];

		$fields = $this->getStudentPiecesFields();
		$titles = [];
		$dataIndx = [];

		foreach ($fields as $field) {

			if (isset($field['excel']) && $field['excel'] == 1) {
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

		if (file_exists(_PS_EXPORT_DIR_ . 'Facture_' . $session_date . '.xlsx')) {
			unlink(_PS_EXPORT_DIR_ . 'Facture_' . $session_date . '.xlsx');
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
		$datas = $this->getRequestbyIdeSession($educationSession->id);
		$totalInvoice = 0;

		foreach ($datas as $educations) {
			$totalInvoice = $totalInvoice + $educations['education_tax_excl'];

			foreach ($dataIndx as $k => $title) {

				if (array_key_exists($title, $educations)) {
					$k++;
					$letter = chr(64 + $k);

					switch ($letter) {
					case 'H':
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

		$spreadsheet->setActiveSheetIndex(0)
			->setCellValue('A' . $i, 'Chiffre d‘affaire total pour cette session ' . $totalInvoice . ' €uros HT');
		$spreadsheet->getActiveSheet()->mergeCells('A' . $i . ':' . $column . $i);
		$spreadsheet->getActiveSheet()->getStyle('A' . $i)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
		$spreadsheet->getActiveSheet()->getStyle('A' . $i)->applyFromArray($titleStyle);
		$spreadsheet->getActiveSheet()->getStyle('A' . $i)->getFont()->setSize(12);

		$fileSave = new Xlsx($spreadsheet);
		$fileSave->save(_PS_EXPORT_DIR_ . 'Facture_' . $session_date . '.xlsx');

		$response = [
			'fileExport' => 'fileExport' . DIRECTORY_SEPARATOR . 'Facture_' . $session_date . '.xlsx',
		];
		die(Tools::jsonEncode($response));

	}

	public function ajaxProcessExportSessionInvoices() {

		$idSession = Tools::getValue('idSession');

		$session = new EducationSession($idSession);

		$idPieces = StudentPieces::getInvoicesbyidSession($session->id);

		$context = Context::getContext();
		$idShop = (int) $context->shop->id;

		if (Configuration::get('PS_LOGO_INVOICE', null, null, $idShop) != false && file_exists(_PS_IMG_DIR_ . Configuration::get('PS_LOGO_INVOICE', null, null, $idShop))) {
			$logo = _PS_IMG_DIR_ . Configuration::get('PS_LOGO_INVOICE', null, null, $idShop);
			$logo_path = _PS_IMG_ . Configuration::get('PS_LOGO_INVOICE', null, null, $idShop);
		} else

		if (Configuration::get('PS_LOGO', null, null, $idShop) != false && file_exists(_PS_IMG_DIR_ . Configuration::get('PS_LOGO', null, null, $idShop))) {
			$logo = _PS_IMG_DIR_ . Configuration::get('PS_LOGO', null, null, $idShop);
			$logo_path = _PS_IMG_ . Configuration::get('PS_LOGO', null, null, $idShop);
		}

		$filePath = 'invoices' . DIRECTORY_SEPARATOR;

		$fileExport = [];

		$prefix = 'FA';
		$nameType = 'Facture';

		foreach ($idPieces as $idPiece) {

			$studentPiece = new StudentPieces($idPiece['id_student_piece']);

			$studentEducation = new StudentEducation($studentPiece->id_student_education);

			$student = new Customer($studentPiece->id_customer);
			$fileName = "Facture_" . $prefix . $studentPiece->piece_number . '_Dossier_n°' . $studentEducation->reference_edof . '_' . $student->lastname . '_' . $student->firstname . '.pdf';

			if (file_exists('invoices' . DIRECTORY_SEPARATOR . $fileName)) {
				$fileExport[] = 'invoices' . DIRECTORY_SEPARATOR . $fileName;
				continue;
			}

			$mpdf = new \Mpdf\Mpdf([
				'margin_left'   => 10,
				'margin_right'  => 10,
				'margin_top'    => 120,
				'margin_bottom' => 75,
				'margin_header' => 10,
				'margin_footer' => 10,
			]);
			$payments = Payment::getByCustomerPieceId($studentPiece->id, $context->language->id);
			$data = $this->createTemplate('controllers/student_pieces/pdf/headertemplate.tpl');

			$data->assign(
				[
					'company'          => $context->company,
					'free_text'        => Configuration::get('PS_INVOICE_FREE_TEXT', (int) Context::getContext()->language->id, null, $context->shop->id),
					'logo_path'        => $logo_path,
					'piece'            => $studentPiece,
					'nameType'         => 'FACTURE',
					'student'          => $student,
					'payments'         => $payments,
					'studentEducation' => $studentEducation,
				]
			);
			$mpdf->SetHTMLHeader($data->fetch());

			$data = $this->createTemplate('controllers/student_pieces/pdf/footertemplate.tpl');

			$data->assign(
				[
					'company'          => $context->company,
					'free_text'        => Configuration::get('PS_INVOICE_FREE_TEXT', (int) Context::getContext()->language->id, null, $context->shop->id),
					'logo_path'        => $logo_path,
					'piece'            => $studentPiece,
					'nameType'         => 'FACTURE',
					'student'          => $student,
					'payments'         => $payments,
					'studentEducation' => $studentEducation,
				]
			);
			$mpdf->SetHTMLFooter($data->fetch(), 'O');

			$data = $this->createTemplate('controllers/student_pieces/pdf.css.tpl');
			$data->assign(
				[
					'color' => '#fa4616',
				]
			);
			$stylesheet = $data->fetch();

			$data = $this->createTemplate('controllers/student_pieces/pdf/bodytemplate.tpl');

			$data->assign(
				[
					'company'          => $context->company,
					'free_text'        => Configuration::get('PS_INVOICE_FREE_TEXT', (int) Context::getContext()->language->id, null, $context->shop->id),
					'logo_path'        => $logo_path,
					'piece'            => $studentPiece,
					'nameType'         => 'FACTURE',
					'student'          => $student,
					'payments'         => $payments,
					'studentEducation' => $studentEducation,
				]
			);

			if ($studentPiece->validate == 0 && $studentPiece->piece_type == 'INVOICE') {
				$watermark = $this->l('Provisoire');
				$mpdf->SetWatermarkText($watermark);
			} else

			if ($studentPiece->validate == 1 && $studentPiece->piece_type == 'INVOICE') {
				$mpdf->SetProtection(['copy', 'print'], '', _DB_PASSWD_);
			}

			$mpdf->SetTitle($context->company->company_name . " " . $this->getStaticPieceName($studentPiece->piece_type) . " " . $prefix . $studentPiece->piece_number);
			$mpdf->SetAuthor($context->company->company_name);
			$mpdf->showWatermarkText = true;
			$mpdf->watermark_font = 'DejaVuSansCondensed';
			$mpdf->watermarkTextAlpha = 0.1;
			$mpdf->SetDisplayMode('fullpage');

			$mpdf->WriteHTML($stylesheet, \Mpdf\HTMLParserMode::HEADER_CSS);
			$mpdf->WriteHTML($data->fetch());

			$mpdf->Output($filePath . $fileName, 'F');

			$fileExport[] = 'invoices' . DIRECTORY_SEPARATOR . $fileName;
		}

		$zip = new ZipArchive;

		if ($zip->open('fileExport' . DIRECTORY_SEPARATOR . 'facture_' . $session->session_date . '.zip', ZipArchive::CREATE) === TRUE) {

			foreach ($fileExport as $invoice) {
				$zip->addFile($invoice, basename($invoice));
				//unlink($invoice);
			}

			$zip->close();

			$response = [
				'fileExport' => 'fileExport' . DIRECTORY_SEPARATOR . 'facture_' . $session->session_date . '.zip',
			];
			die(Tools::jsonEncode($response));
		}

	}

	public function ajaxProcessDeleteFile() {

		$file = Tools::getValue('file');

		if (file_exists(_PS_EXPORT_DIR_ . basename($file))) {
			unlink(_PS_EXPORT_DIR_ . basename($file));
		}

		die(true);

	}

	protected function getLogo() {

		$logo = '';
		$context = Context::getContext();
		$idShop = (int) $context->shop->id;

		if (Configuration::get('PS_LOGO_INVOICE', null, null, $idShop) != false && file_exists(_PS_IMG_DIR_ . Configuration::get('PS_LOGO_INVOICE', null, null, $idShop))) {
			$logo = _PS_IMG_DIR_ . Configuration::get('PS_LOGO_INVOICE', null, null, $idShop);
		} else

		if (Configuration::get('PS_LOGO', null, null, $idShop) != false && file_exists(_PS_IMG_DIR_ . Configuration::get('PS_LOGO', null, null, $idShop))) {
			$logo = _PS_IMG_DIR_ . Configuration::get('PS_LOGO', null, null, $idShop);
		}

		return $logo;
	}

	public function ajaxProcessMergeOrderTable() {

		$idOrder = Tools::getValue('idOrder');
		$nbOrder = Tools::getValue('numberOrder');

		if (CustomerPieces::mergeOrderTable($idOrder)) {
			$response = [
				'success' => true,
				'message' => $this->l('Order ') . ' ' . $idOrder . ' ' . $this->l(' has been successfully merged'),
			];
		} else {
			$response = [
				'success' => false,
				'message' => $this->l('Order ') . ' ' . $idOrder . ' ' . $this->l(' has not been merged'),
			];
		}

		die(Tools::jsonEncode($response));
	}

	/**
	 * Initialize toolbar
	 *
	 * @return void
	 *
	 * @since 1.8.1.0
	 */
	public function initToolbar() {

		if ($this->display == 'view') {
			/** @var StudentPieces $order */
			$order = $this->loadObject();
			$student = $this->context->student;

			if (!Validate::isLoadedObject($order)) {
				Tools::redirectAdmin($this->context->link->getAdminLink('AdminStudentPieces'));
			}

			$this->toolbar_title[] = sprintf($this->l('StudentPieces %1$s from %2$s %3$s'), $order->reference, $student->firstname, $student->lastname);
			$this->addMetaTitle($this->toolbar_title[count($this->toolbar_title) - 1]);

			if ($order->hasBeenShipped()) {
				$type = $this->l('Return products');
			} else

			if ($order->hasBeenPaid()) {
				$type = $this->l('Standard refund');
			} else {
				$type = $this->l('Cancel products');
			}

			if (!$order->hasBeenShipped() && !$this->lite_display) {
				$this->toolbar_btn['new'] = [
					'short' => 'Create',
					'href'  => '#',
					'desc'  => $this->l('Add a product'),
					'class' => 'add_product',
				];
			}

			if (Configuration::get('PS_ORDER_RETURN') && !$this->lite_display) {
				$this->toolbar_btn['standard_refund'] = [
					'short' => 'Create',
					'href'  => '',
					'desc'  => $type,
					'class' => 'process-icon-standardRefund',
				];
			}

			if ($order->hasInvoice() && !$this->lite_display) {
				$this->toolbar_btn['partial_refund'] = [
					'short' => 'Create',
					'href'  => '',
					'desc'  => $this->l('Partial refund'),
					'class' => 'process-icon-partialRefund',
				];
			}

		}

		$res = parent::initToolbar();

		if ($this->context->shop->getContext() != Shop::CONTEXT_SHOP && isset($this->toolbar_btn['new']) && Shop::isFeatureActive()) {
			unset($this->toolbar_btn['new']);
		}

		return $res;
	}

	/**
	 * Print PDF icons
	 *
	 * @param int   $idStudentPieces
	 * @param array $tr
	 *
	 * @return string
	 *
	 * @since 1.8.1.0
	 */
	public function printPDFIcons($idStudentPieces, $tr) {

		static $validStudentPiecesState = [];

		$order = new StudentPieces($idStudentPieces);

		if (!Validate::isLoadedObject($order)) {
			return '';
		}

		if (!isset($validStudentPiecesState[$order->current_state])) {
			$validStudentPiecesState[$order->current_state] = Validate::isLoadedObject($order->getCurrentStudentPiecesState());
		}

		if (!$validStudentPiecesState[$order->current_state]) {
			return '';
		}

		$this->context->smarty->assign(
			[
				'order' => $order,
				'tr'    => $tr,
			]
		);

		return $this->createTemplate('_print_pdf_icon.tpl')->fetch();
	}

	/**
	 * Print new student
	 *
	 * @param int   $idStudentPieces
	 * @param array $tr
	 *
	 * @return string
	 *
	 * @since 1.8.1.0
	 */
	public function printNewCustomer($idStudentPieces, $tr) {

		return ($tr['new'] ? $this->l('Yes') : $this->l('No'));
	}

	

	/**
	 * Post processing
	 *
	 * @throws PhenyxShopException
	 *
	 * @since 1.8.1.0
	 */
	public function postProcess() {

		if (Tools::isSubmit('delete' . $this->table)) {

			if ($this->tabAccess['delete'] === '0') {
				$result = [
					'success' => false,
					'message' => Tools::displayError('You cannot delete this item.'),
				];
				die(Tools::jsonEncode($result));
			}

		}

		if (Tools::isSubmit('edit' . $this->table)) {

			if ($this->tabAccess['view'] === '0') {
				$result = [
					'success' => false,
					'message' => Tools::displayError('You are not granted to edit this ' . $this->publicName),
				];
				die(Tools::jsonEncode($result));
			}

		}

		if (Tools::isSubmit('add' . $this->table)) {

			if ($this->tabAccess['add'] === '0') {
				$result = [
					'success' => false,
					'message' => Tools::displayError('You are not granted to add any ' . $this->publicName),
				];
				die(Tools::jsonEncode($result));
			}

		}

		parent::postProcess();
	}

	
	/**
	 * Ajax process search products
	 *
	 * @return void
	 *
	 * @since 1.8.1.0
	 */
	public function ajaxProcessSearchProducts() {

		$this->context->student = new Customer((int) Tools::getValue('id_student'));
		$currency = new Currency((int) Tools::getValue('id_currency'));

		if ($products = Product::searchByName((int) $this->context->language->id, pSQL(Tools::getValue('product_search')))) {

			foreach ($products as &$product) {
				// Formatted price
				$product['formatted_price'] = Tools::displayPrice(Tools::convertPrice($product['price_tax_incl'], $currency), $currency);
				// Concret price
				$product['price_tax_incl'] = Tools::ps_round(Tools::convertPrice($product['price_tax_incl'], $currency), 2);
				$product['price_tax_excl'] = Tools::ps_round(Tools::convertPrice($product['price_tax_excl'], $currency), 2);
				$productObj = new Product((int) $product['id_product'], false, (int) $this->context->language->id);
				$combinations = [];
				$attributes = $productObj->getAttributesGroups((int) $this->context->language->id);

				// Tax rate for this student

				if (Tools::isSubmit('id_address')) {
					$product['tax_rate'] = $productObj->getTaxesRate(new Address(Tools::getValue('id_address')));
				}

				$product['warehouse_list'] = [];

				foreach ($attributes as $attribute) {

					if (!isset($combinations[$attribute['id_product_attribute']]['attributes'])) {
						$combinations[$attribute['id_product_attribute']]['attributes'] = '';
					}

					$combinations[$attribute['id_product_attribute']]['attributes'] .= $attribute['attribute_name'] . ' - ';
					$combinations[$attribute['id_product_attribute']]['id_product_attribute'] = $attribute['id_product_attribute'];
					$combinations[$attribute['id_product_attribute']]['default_on'] = $attribute['default_on'];

					if (!isset($combinations[$attribute['id_product_attribute']]['price'])) {
						$priceTaxIncl = Product::getPriceStatic((int) $product['id_product'], true, $attribute['id_product_attribute']);
						$priceTaxExcl = Product::getPriceStatic((int) $product['id_product'], false, $attribute['id_product_attribute']);
						$combinations[$attribute['id_product_attribute']]['price_tax_incl'] = Tools::ps_round(Tools::convertPrice($priceTaxIncl, $currency), 2);
						$combinations[$attribute['id_product_attribute']]['price_tax_excl'] = Tools::ps_round(Tools::convertPrice($priceTaxExcl, $currency), 2);
						$combinations[$attribute['id_product_attribute']]['formatted_price'] = Tools::displayPrice(Tools::convertPrice($priceTaxExcl, $currency), $currency);
					}

					if (!isset($combinations[$attribute['id_product_attribute']]['qty_in_stock'])) {
						$combinations[$attribute['id_product_attribute']]['qty_in_stock'] = StockAvailable::getQuantityAvailableByProduct((int) $product['id_product'], $attribute['id_product_attribute'], (int) $this->context->shop->id);
					}

					if (Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT') && (int) $product['advanced_stock_management'] == 1) {
						$product['warehouse_list'][$attribute['id_product_attribute']] = Warehouse::getProductWarehouseList($product['id_product'], $attribute['id_product_attribute']);
					} else {
						$product['warehouse_list'][$attribute['id_product_attribute']] = [];
					}

					$product['stock'][$attribute['id_product_attribute']] = Product::getRealQuantity($product['id_product'], $attribute['id_product_attribute']);
				}

				if (Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT') && (int) $product['advanced_stock_management'] == 1) {
					$product['warehouse_list'][0] = Warehouse::getProductWarehouseList($product['id_product']);
				} else {
					$product['warehouse_list'][0] = [];
				}

				$product['stock'][0] = StockAvailable::getQuantityAvailableByProduct((int) $product['id_product'], 0, (int) $this->context->shop->id);

				foreach ($combinations as &$combination) {
					$combination['attributes'] = rtrim($combination['attributes'], ' - ');
				}

				$product['combinations'] = $combinations;

				if ($product['customizable']) {
					$productInstance = new Product((int) $product['id_product']);
					$product['customization_fields'] = $productInstance->getCustomizationFields($this->context->language->id);
				}

			}

			$toReturn = [
				'products' => $products,
				'found'    => true,
			];
		} else {
			$toReturn = ['found' => false];
		}

		$this->content = json_encode($toReturn);
	}

	/**
	 * Ajax process add product on order
	 *
	 * @return void
	 *
	 * @since 1.8.1.0
	 */
	public function ajaxProcessAddProductOnStudentPieces() {

		// Load object
		$order = new StudentPieces((int) Tools::getValue('id_order'));

		if (!Validate::isLoadedObject($order)) {
			$this->ajaxDie(
				json_encode(
					[
						'result' => false,
						'error'  => Tools::displayError('The order object cannot be loaded.'),
					]
				)
			);
		}

		$oldCartRules = $this->context->cart->getCartRules();

		if ($order->hasBeenShipped()) {
			$this->ajaxDie(
				json_encode(
					[
						'result' => false,
						'error'  => Tools::displayError('You cannot add products to delivered orders. '),
					]
				)
			);
		}

		$productInformations = $_POST['add_product'];

		if (isset($_POST['add_invoice'])) {
			$invoiceInformations = $_POST['add_invoice'];
		} else {
			$invoiceInformations = [];
		}

		$product = new Product($productInformations['product_id'], false, $order->id_lang);

		if (!Validate::isLoadedObject($product)) {
			$this->ajaxDie(
				json_encode(
					[
						'result' => false,
						'error'  => Tools::displayError('The product object cannot be loaded.'),
					]
				)
			);
		}

		if (isset($productInformations['product_attribute_id']) && $productInformations['product_attribute_id']) {
			$combination = new Combination($productInformations['product_attribute_id']);

			if (!Validate::isLoadedObject($combination)) {
				$this->ajaxDie(
					json_encode(
						[
							'result' => false,
							'error'  => Tools::displayError('The combination object cannot be loaded.'),
						]
					)
				);
			}

		}

		// Total method
		$totalMethod = Cart::BOTH_WITHOUT_SHIPPING;

		// Create new cart
		$cart = new Cart();
		$cart->id_shop_group = $order->id_shop_group;
		$cart->id_shop = $order->id_shop;
		$cart->id_student = $order->id_student;
		$cart->id_carrier = $order->id_carrier;
		$cart->id_address_delivery = $order->id_address_delivery;
		$cart->id_address_invoice = $order->id_address_invoice;
		$cart->id_currency = $order->id_currency;
		$cart->id_lang = $order->id_lang;
		$cart->secure_key = $order->secure_key;

		// Save new cart
		$cart->add();

		// Save context (in order to apply cart rule)
		$this->context->cart = $cart;
		$this->context->student = new Customer($order->id_student);

		// always add taxes even if there are not displayed to the student
		$useTaxes = true;

		$initialProductPriceTaxIncl = Product::getPriceStatic(
			$product->id,
			$useTaxes,
			isset($combination) ? $combination->id : null,
			2,
			null,
			false,
			true,
			1,
			false,
			$order->id_student,
			$cart->id,
			$order->{Configuration::get('PS_TAX_ADDRESS_TYPE', null, null, $order->id_shop)}
		);

		// Creating specific price if needed

		if ($productInformations['product_price_tax_incl'] != $initialProductPriceTaxIncl) {
			$specificPrice = new SpecificPrice();
			$specificPrice->id_shop = 0;
			$specificPrice->id_shop_group = 0;
			$specificPrice->id_currency = 0;
			$specificPrice->id_country = 0;
			$specificPrice->id_group = 0;
			$specificPrice->id_student = $order->id_student;
			$specificPrice->id_product = $product->id;

			if (isset($combination)) {
				$specificPrice->id_product_attribute = $combination->id;
			} else {
				$specificPrice->id_product_attribute = 0;
			}

			$specificPrice->price = $productInformations['product_price_tax_excl'];
			$specificPrice->from_quantity = 1;
			$specificPrice->reduction = 0;
			$specificPrice->reduction_type = 'amount';
			$specificPrice->reduction_tax = 0;
			$specificPrice->from = '0000-00-00 00:00:00';
			$specificPrice->to = '0000-00-00 00:00:00';
			$specificPrice->add();
		}

		// Add product to cart
		$updateQuantity = $cart->updateQty(
			$productInformations['product_quantity'],
			$product->id,
			isset($productInformations['product_attribute_id']) ? $productInformations['product_attribute_id'] : null,
			isset($combination) ? $combination->id : null,
			'up',
			0,
			new Shop($cart->id_shop)
		);

		if ($updateQuantity < 0) {
			// If product has attribute, minimal quantity is set with minimal quantity of attribute
			$minimalQuantity = ($productInformations['product_attribute_id']) ? Attributes::getAttributeMinimalQty($productInformations['product_attribute_id']) : $product->minimal_quantity;
			$this->ajaxDie(json_encode(['error' => sprintf(Tools::displayError('You must add %d minimum quantity', false), $minimalQuantity)]));
		} else

		if (!$updateQuantity) {
			$this->ajaxDie(json_encode(['error' => Tools::displayError('You already have the maximum quantity available for this product.', false)]));
		}

		// If order is valid, we can create a new invoice or edit an existing invoice

		if ($order->hasInvoice()) {
			$orderInvoice = new StudentPiecesInvoice($productInformations['invoice']);
			// Create new invoice

			if ($orderInvoice->id == 0) {
				// If we create a new invoice, we calculate shipping cost
				$totalMethod = Cart::BOTH;
				// Create Cart rule in order to make free shipping

				if (isset($invoiceInformations['free_shipping']) && $invoiceInformations['free_shipping']) {
					$cartRule = new CartRule();
					$cartRule->id_student = $order->id_student;
					$cartRule->name = [
						Configuration::get('PS_LANG_DEFAULT') => $this->l('[Generated] CartRule for Free Shipping'),
					];
					$cartRule->date_from = date('Y-m-d H:i:s', time());
					$cartRule->date_to = date('Y-m-d H:i:s', time() + 24 * 3600);
					$cartRule->quantity = 1;
					$cartRule->quantity_per_user = 1;
					$cartRule->minimum_amount_currency = $order->id_currency;
					$cartRule->reduction_currency = $order->id_currency;
					$cartRule->free_shipping = true;
					$cartRule->active = 1;
					$cartRule->add();

					// Add cart rule to cart and in order
					$cart->addCartRule($cartRule->id);
					$values = [
						'tax_incl' => $cartRule->getContextualValue(true),
						'tax_excl' => $cartRule->getContextualValue(false),
					];
					$order->addCartRule($cartRule->id, $cartRule->name[Configuration::get('PS_LANG_DEFAULT')], $values);
				}

				$orderInvoice->id_order = $order->id;

				if ($orderInvoice->number) {
					Configuration::updateValue('PS_INVOICE_START_NUMBER', false, false, null, $order->id_shop);
				} else {
					$orderInvoice->number = StudentPieces::getLastInvoiceNumber() + 1;
				}

				$invoiceAddress = new Address((int) $order->{Configuration::get('PS_TAX_ADDRESS_TYPE', null, null, $order->id_shop)});
				$carrier = new Carrier((int) $order->id_carrier);
				$taxCalculator = $carrier->getTaxCalculator($invoiceAddress);

				$orderInvoice->total_paid_tax_excl = Tools::ps_round((float) $cart->getStudentPiecesTotal(false, $totalMethod), 2);
				$orderInvoice->total_paid_tax_incl = Tools::ps_round((float) $cart->getStudentPiecesTotal($useTaxes, $totalMethod), 2);
				$orderInvoice->total_products = (float) $cart->getStudentPiecesTotal(false, Cart::ONLY_PRODUCTS);
				$orderInvoice->total_products_wt = (float) $cart->getStudentPiecesTotal($useTaxes, Cart::ONLY_PRODUCTS);
				$orderInvoice->total_shipping_tax_excl = (float) $cart->getTotalShippingCost(null, false);
				$orderInvoice->total_shipping_tax_incl = (float) $cart->getTotalShippingCost();

				$orderInvoice->total_wrapping_tax_excl = abs($cart->getStudentPiecesTotal(false, Cart::ONLY_WRAPPING));
				$orderInvoice->total_wrapping_tax_incl = abs($cart->getStudentPiecesTotal($useTaxes, Cart::ONLY_WRAPPING));
				$orderInvoice->shipping_tax_computation_method = (int) $taxCalculator->computation_method;

				// Update current order field, only shipping because other field is updated later
				$order->total_shipping += $orderInvoice->total_shipping_tax_incl;
				$order->total_shipping_tax_excl += $orderInvoice->total_shipping_tax_excl;
				$order->total_shipping_tax_incl += ($useTaxes) ? $orderInvoice->total_shipping_tax_incl : $orderInvoice->total_shipping_tax_excl;

				$order->total_wrapping += abs($cart->getStudentPiecesTotal($useTaxes, Cart::ONLY_WRAPPING));
				$order->total_wrapping_tax_excl += abs($cart->getStudentPiecesTotal(false, Cart::ONLY_WRAPPING));
				$order->total_wrapping_tax_incl += abs($cart->getStudentPiecesTotal($useTaxes, Cart::ONLY_WRAPPING));
				$orderInvoice->add();

				$orderInvoice->saveCarrierTaxCalculator($taxCalculator->getTaxesAmount($orderInvoice->total_shipping_tax_excl));

				$orderCarrier = new StudentPiecesCarrier();
				$orderCarrier->id_order = (int) $order->id;
				$orderCarrier->id_carrier = (int) $order->id_carrier;
				$orderCarrier->id_order_invoice = (int) $orderInvoice->id;
				$orderCarrier->weight = (float) $cart->getTotalWeight();
				$orderCarrier->shipping_cost_tax_excl = (float) $orderInvoice->total_shipping_tax_excl;
				$orderCarrier->shipping_cost_tax_incl = ($useTaxes) ? (float) $orderInvoice->total_shipping_tax_incl : (float) $orderInvoice->total_shipping_tax_excl;
				$orderCarrier->add();
			}

			// Update current invoice
			else {
				$orderInvoice->total_paid_tax_excl += Tools::ps_round((float) ($cart->getStudentPiecesTotal(false, $totalMethod)), 2);
				$orderInvoice->total_paid_tax_incl += Tools::ps_round((float) ($cart->getStudentPiecesTotal($useTaxes, $totalMethod)), 2);
				$orderInvoice->total_products += (float) $cart->getStudentPiecesTotal(false, Cart::ONLY_PRODUCTS);
				$orderInvoice->total_products_wt += (float) $cart->getStudentPiecesTotal($useTaxes, Cart::ONLY_PRODUCTS);
				$orderInvoice->update();
			}

		}

		// Create StudentPieces detail information
		$orderDetail = new StudentPiecesDetail();
		$orderDetail->createList($order, $cart, $order->getCurrentStudentPiecesState(), $cart->getProducts(), (isset($orderInvoice) ? $orderInvoice->id : 0), $useTaxes, (int) Tools::getValue('add_product_warehouse'));

		// update totals amount of order
		$order->total_products += (float) $cart->getStudentPiecesTotal(false, Cart::ONLY_PRODUCTS);
		$order->total_products_wt += (float) $cart->getStudentPiecesTotal($useTaxes, Cart::ONLY_PRODUCTS);

		$order->total_paid += Tools::ps_round((float) ($cart->getStudentPiecesTotal(true, $totalMethod)), 2);
		$order->total_paid_tax_excl += Tools::ps_round((float) ($cart->getStudentPiecesTotal(false, $totalMethod)), 2);
		$order->total_paid_tax_incl += Tools::ps_round((float) ($cart->getStudentPiecesTotal($useTaxes, $totalMethod)), 2);

		if (isset($orderInvoice) && Validate::isLoadedObject($orderInvoice)) {
			$order->total_shipping = $orderInvoice->total_shipping_tax_incl;
			$order->total_shipping_tax_incl = $orderInvoice->total_shipping_tax_incl;
			$order->total_shipping_tax_excl = $orderInvoice->total_shipping_tax_excl;
		}

		StockAvailable::updateQuantity($orderDetail->product_id, $orderDetail->product_attribute_id, ($orderDetail->product_quantity * -1), $order->id_shop);

		// discount
		$order->total_discounts += (float) abs($cart->getStudentPiecesTotal(true, Cart::ONLY_DISCOUNTS));
		$order->total_discounts_tax_excl += (float) abs($cart->getStudentPiecesTotal(false, Cart::ONLY_DISCOUNTS));
		$order->total_discounts_tax_incl += (float) abs($cart->getStudentPiecesTotal(true, Cart::ONLY_DISCOUNTS));

		// Save changes of order
		$order->update();

		// Update weight SUM
		$orderCarrier = new StudentPiecesCarrier((int) $order->getIdStudentPiecesCarrier());

		if (Validate::isLoadedObject($orderCarrier)) {
			$orderCarrier->weight = (float) $order->getTotalWeight();

			if ($orderCarrier->update()) {
				$order->weight = sprintf("%.3f " . Configuration::get('PS_WEIGHT_UNIT'), $orderCarrier->weight);
			}

		}

		// Update Tax lines
		$orderDetail->updateTaxAmount($order);

		// Delete specific price if exists

		if (isset($specificPrice)) {
			$specificPrice->delete();
		}

		$products = $this->getProducts($order);

		// Get the last product
		$product = end($products);
		$product['current_stock'] = StockAvailable::getQuantityAvailableByProduct($product['product_id'], $product['product_attribute_id'], $product['id_shop']);
		$resume = StudentPiecesSlip::getProductSlipResume((int) $product['id_order_detail']);
		$product['quantity_refundable'] = $product['product_quantity'] - $resume['product_quantity'];
		$product['amount_refundable'] = $product['total_price_tax_excl'] - $resume['amount_tax_excl'];
		$product['amount_refund'] = Tools::displayPrice($resume['amount_tax_incl']);
		$product['return_history'] = StudentPiecesReturn::getProductReturnDetail((int) $product['id_order_detail']);
		$product['refund_history'] = StudentPiecesSlip::getProductSlipDetail((int) $product['id_order_detail']);

		if ($product['id_warehouse'] != 0) {
			$warehouse = new Warehouse((int) $product['id_warehouse']);
			$product['warehouse_name'] = $warehouse->name;
			$warehouseLocation = WarehouseProductLocation::getProductLocation($product['product_id'], $product['product_attribute_id'], $product['id_warehouse']);

			if (!empty($warehouseLocation)) {
				$product['warehouse_location'] = $warehouseLocation;
			} else {
				$product['warehouse_location'] = false;
			}

		} else {
			$product['warehouse_name'] = '--';
			$product['warehouse_location'] = false;
		}

		// Get invoices collection
		$invoiceCollection = $order->getInvoicesCollection();

		$invoiceArray = [];

		foreach ($invoiceCollection as $invoice) {
			/** @var StudentPiecesInvoice $invoice */
			$invoice->name = $invoice->getInvoiceNumberFormatted($this->context->language->id, (int) $order->id_shop);
			$invoiceArray[] = $invoice;
		}

		// Assign to smarty informations in order to show the new product line
		$this->context->smarty->assign(
			[
				'product'             => $product,
				'order'               => $order,
				'currency'            => new Currency($order->id_currency),
				'can_edit'            => $this->tabAccess['edit'],
				'invoices_collection' => $invoiceCollection,
				'current_id_lang'     => $this->context->language->id,
				'link'                => $this->context->link,
				'current_index'       => static::$currentIndex,
				'display_warehouse'   => (int) Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT'),
			]
		);

		$this->sendChangedNotification($order);
		$newCartRules = $this->context->cart->getCartRules();
		sort($oldCartRules);
		sort($newCartRules);
		$result = array_diff($newCartRules, $oldCartRules);
		$refresh = false;

		$res = true;

		foreach ($result as $cartRule) {
			$refresh = true;
			// Create StudentPiecesCartRule
			$rule = new CartRule($cartRule['id_cart_rule']);
			$values = [
				'tax_incl' => $rule->getContextualValue(true),
				'tax_excl' => $rule->getContextualValue(false),
			];
			$orderCartRule = new StudentPiecesCartRule();
			$orderCartRule->id_order = $order->id;
			$orderCartRule->id_cart_rule = $cartRule['id_cart_rule'];
			$orderCartRule->id_order_invoice = $orderInvoice->id;
			$orderCartRule->name = $cartRule['name'];
			$orderCartRule->value = $values['tax_incl'];
			$orderCartRule->value_tax_excl = $values['tax_excl'];
			$res &= $orderCartRule->add();

			$order->total_discounts += $orderCartRule->value;
			$order->total_discounts_tax_incl += $orderCartRule->value;
			$order->total_discounts_tax_excl += $orderCartRule->value_tax_excl;
			$order->total_paid -= $orderCartRule->value;
			$order->total_paid_tax_incl -= $orderCartRule->value;
			$order->total_paid_tax_excl -= $orderCartRule->value_tax_excl;
		}

		// Update StudentPieces
		$order->update();

		$this->ajaxDie(
			json_encode(
				[
					'result'             => true,
					'view'               => $this->createTemplate('_product_line.tpl')->fetch(),
					'can_edit'           => $this->tabAccess['add'],
					'order'              => $order,
					'invoices'           => $invoiceArray,
					'documents_html'     => $this->createTemplate('_documents.tpl')->fetch(),
					'shipping_html'      => $this->createTemplate('_shipping.tpl')->fetch(),
					'discount_form_html' => $this->createTemplate('_discount_form.tpl')->fetch(),
					'refresh'            => $refresh,
				]
			)
		);
	}

	/**
	 * Send changed notification
	 *
	 * @param StudentPieces|null $order
	 *
	 * @since 1.8.1.0
	 */
	public function sendChangedNotification(StudentPieces $order = null) {

		if (is_null($order)) {
			$order = new StudentPieces(Tools::getValue('id_order'));
		}

		Hook::exec('actionStudentPiecesEdited', ['order' => $order]);
	}

	/**
	 * Ajax proces load product information
	 *
	 * @return void
	 *
	 * @since 1.8.1.0
	 */
	public function ajaxProcessLoadProductInformation() {

		$orderDetail = new StudentPiecesDetail(Tools::getValue('id_order_detail'));

		if (!Validate::isLoadedObject($orderDetail)) {
			$this->ajaxDie(
				json_encode(
					[
						'result' => false,
						'error'  => Tools::displayError('The StudentPiecesDetail object cannot be loaded.'),
					]
				)
			);
		}

		$product = new Product($orderDetail->product_id);

		if (!Validate::isLoadedObject($product)) {
			$this->ajaxDie(
				json_encode(
					[
						'result' => false,
						'error'  => Tools::displayError('The product object cannot be loaded.'),
					]
				)
			);
		}

		$address = new Address(Tools::getValue('id_address'));

		if (!Validate::isLoadedObject($address)) {
			$this->ajaxDie(
				json_encode(
					[
						'result' => false,
						'error'  => Tools::displayError('The address object cannot be loaded.'),
					]
				)
			);
		}

		$this->ajaxDie(json_encode([
			'result'            => true,
			'product'           => $product,
			'tax_rate'          => $product->getTaxesRate($address),
			'price_tax_incl'    => Product::getPriceStatic($product->id, true, $orderDetail->product_attribute_id, 2),
			'price_tax_excl'    => Product::getPriceStatic($product->id, false, $orderDetail->product_attribute_id, 2),
			'reduction_percent' => $orderDetail->reduction_percent,
		]));
	}

	/**
	 * Ajax process edit product on order
	 *
	 * @return void
	 *
	 * @since 1.8.1.0
	 */
	public function ajaxProcessEditProductOnStudentPieces() {

		// Return value
		$res = true;

		$order = new StudentPieces((int) Tools::getValue('id_order'));
		$orderDetail = new StudentPiecesDetail((int) Tools::getValue('product_id_order_detail'));

		if (Tools::isSubmit('product_invoice')) {
			$orderInvoice = new StudentPiecesInvoice((int) Tools::getValue('product_invoice'));
		}

		// If multiple product_quantity, the order details concern a product customized
		$productQuantity = 0;

		if (is_array(Tools::getValue('product_quantity'))) {

			foreach (Tools::getValue('product_quantity') as $idCustomization => $qty) {
				// Update quantity of each customization
				Db::getInstance()->update('customization', ['quantity' => (int) $qty], 'id_customization = ' . (int) $idCustomization);
				// Calculate the real quantity of the product
				$productQuantity += $qty;
			}

		} else {
			$productQuantity = Tools::getValue('product_quantity');
		}

		$this->checkStockAvailable($orderDetail, ($productQuantity - $orderDetail->product_quantity));

		// Check fields validity
		$this->doEditProductValidation($orderDetail, $order, isset($orderInvoice) ? $orderInvoice : null);

		// If multiple product_quantity, the order details concern a product customized
		$productQuantity = 0;

		if (is_array(Tools::getValue('product_quantity'))) {

			foreach (Tools::getValue('product_quantity') as $idCustomization => $qty) {
				// Update quantity of each customization
				Db::getInstance()->update(
					'customization',
					[
						'quantity' => (int) $qty,
					],
					'id_customization = ' . (int) $idCustomization,
					1
				);
				// Calculate the real quantity of the product
				$productQuantity += $qty;
			}

		} else {
			$productQuantity = Tools::getValue('product_quantity');
		}

		$productPriceTaxIncl = Tools::ps_round(Tools::getValue('product_price_tax_incl'), 2);
		$productPriceTaxExcl = Tools::ps_round(Tools::getValue('product_price_tax_excl'), 2);
		$totalProductsTaxIncl = $productPriceTaxIncl * $productQuantity;
		$totalProductsTaxExcl = $productPriceTaxExcl * $productQuantity;

		// Calculate differences of price (Before / After)
		$diffPriceTaxIncl = $totalProductsTaxIncl - $orderDetail->total_price_tax_incl;
		$diffPriceTaxExcl = $totalProductsTaxExcl - $orderDetail->total_price_tax_excl;

		// Apply change on StudentPiecesInvoice

		if (isset($orderInvoice)) {
			// If StudentPiecesInvoice to use is different, we update the old invoice and new invoice

			if ($orderDetail->id_order_invoice != $orderInvoice->id) {
				$oldStudentPiecesInvoice = new StudentPiecesInvoice($orderDetail->id_order_invoice);
				// We remove cost of products
				$oldStudentPiecesInvoice->total_products -= $orderDetail->total_price_tax_excl;
				$oldStudentPiecesInvoice->total_products_wt -= $orderDetail->total_price_tax_incl;

				$oldStudentPiecesInvoice->total_paid_tax_excl -= $orderDetail->total_price_tax_excl;
				$oldStudentPiecesInvoice->total_paid_tax_incl -= $orderDetail->total_price_tax_incl;

				$res &= $oldStudentPiecesInvoice->update();

				$orderInvoice->total_products += $orderDetail->total_price_tax_excl;
				$orderInvoice->total_products_wt += $orderDetail->total_price_tax_incl;

				$orderInvoice->total_paid_tax_excl += $orderDetail->total_price_tax_excl;
				$orderInvoice->total_paid_tax_incl += $orderDetail->total_price_tax_incl;

				$orderDetail->id_order_invoice = $orderInvoice->id;
			}

		}

		if ($diffPriceTaxIncl != 0 && $diffPriceTaxExcl != 0) {
			$orderDetail->unit_price_tax_excl = $productPriceTaxExcl;
			$orderDetail->unit_price_tax_incl = $productPriceTaxIncl;

			$orderDetail->total_price_tax_incl += $diffPriceTaxIncl;
			$orderDetail->total_price_tax_excl += $diffPriceTaxExcl;

			if (isset($orderInvoice)) {
				// Apply changes on StudentPiecesInvoice
				$orderInvoice->total_products += $diffPriceTaxExcl;
				$orderInvoice->total_products_wt += $diffPriceTaxIncl;

				$orderInvoice->total_paid_tax_excl += $diffPriceTaxExcl;
				$orderInvoice->total_paid_tax_incl += $diffPriceTaxIncl;
			}

			// Apply changes on StudentPieces
			$order = new StudentPieces($orderDetail->id_order);
			$order->total_products += $diffPriceTaxExcl;
			$order->total_products_wt += $diffPriceTaxIncl;

			$order->total_paid += $diffPriceTaxIncl;
			$order->total_paid_tax_excl += $diffPriceTaxExcl;
			$order->total_paid_tax_incl += $diffPriceTaxIncl;

			$res &= $order->update();
		}

		$oldQuantity = $orderDetail->product_quantity;

		$orderDetail->product_quantity = $productQuantity;
		$orderDetail->reduction_percent = 0;

		// update taxes
		$res &= $orderDetail->updateTaxAmount($order);

		// Save order detail
		$res &= $orderDetail->update();

		// Update weight SUM
		$orderCarrier = new StudentPiecesCarrier((int) $order->getIdStudentPiecesCarrier());

		if (Validate::isLoadedObject($orderCarrier)) {
			$orderCarrier->weight = (float) $order->getTotalWeight();
			$res &= $orderCarrier->update();

			if ($res) {
				$order->weight = sprintf("%.3f " . Configuration::get('PS_WEIGHT_UNIT'), $orderCarrier->weight);
			}

		}

		// Save order invoice

		if (isset($orderInvoice)) {
			$res &= $orderInvoice->update();
		}

		// Update product available quantity
		StockAvailable::updateQuantity($orderDetail->product_id, $orderDetail->product_attribute_id, ($oldQuantity - $orderDetail->product_quantity), $order->id_shop);

		$products = $this->getProducts($order);
		// Get the last product
		$product = $products[$orderDetail->id];
		$product['current_stock'] = StockAvailable::getQuantityAvailableByProduct($product['product_id'], $product['product_attribute_id'], $product['id_shop']);
		$resume = StudentPiecesSlip::getProductSlipResume($orderDetail->id);
		$product['quantity_refundable'] = $product['product_quantity'] - $resume['product_quantity'];
		$product['amount_refundable'] = $product['total_price_tax_excl'] - $resume['amount_tax_excl'];
		$product['amount_refund'] = Tools::displayPrice($resume['amount_tax_incl']);
		$product['refund_history'] = StudentPiecesSlip::getProductSlipDetail($orderDetail->id);

		if ($product['id_warehouse'] != 0) {
			$warehouse = new Warehouse((int) $product['id_warehouse']);
			$product['warehouse_name'] = $warehouse->name;
			$warehouseLocation = WarehouseProductLocation::getProductLocation($product['product_id'], $product['product_attribute_id'], $product['id_warehouse']);

			if (!empty($warehouseLocation)) {
				$product['warehouse_location'] = $warehouseLocation;
			} else {
				$product['warehouse_location'] = false;
			}

		} else {
			$product['warehouse_name'] = '--';
			$product['warehouse_location'] = false;
		}

		// Get invoices collection
		$invoiceCollection = $order->getInvoicesCollection();

		$invoiceArray = [];

		foreach ($invoiceCollection as $invoice) {
			/** @var StudentPiecesInvoice $invoice */
			$invoice->name = $invoice->getInvoiceNumberFormatted($this->context->language->id, (int) $order->id_shop);
			$invoiceArray[] = $invoice;
		}

		// Assign to smarty informations in order to show the new product line
		$this->context->smarty->assign(
			[
				'product'             => $product,
				'order'               => $order,
				'currency'            => new Currency($order->id_currency),
				'can_edit'            => $this->tabAccess['edit'],
				'invoices_collection' => $invoiceCollection,
				'current_id_lang'     => $this->context->language->id,
				'link'                => $this->context->link,
				'current_index'       => static::$currentIndex,
				'display_warehouse'   => (int) Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT'),
			]
		);

		if (!$res) {
			$this->ajaxDie(
				json_encode(
					[
						'result' => $res,
						'error'  => Tools::displayError('An error occurred while editing the product line.'),
					]
				)
			);
		}

		if (is_array(Tools::getValue('product_quantity'))) {
			$view = $this->createTemplate('_customized_data.tpl')->fetch();
		} else {
			$view = $this->createTemplate('_product_line.tpl')->fetch();
		}

		$this->sendChangedNotification($order);

		$this->ajaxDie(json_encode([
			'result'              => $res,
			'view'                => $view,
			'can_edit'            => $this->tabAccess['add'],
			'invoices_collection' => $invoiceCollection,
			'order'               => $order,
			'invoices'            => $invoiceArray,
			'documents_html'      => $this->createTemplate('_documents.tpl')->fetch(),
			'shipping_html'       => $this->createTemplate('_shipping.tpl')->fetch(),
			'customized_product'  => is_array(Tools::getValue('product_quantity')),
		]));
	}

	/**
	 * @param StudentPiecesDetail $orderDetail
	 * @param int         $addQuantity
	 */
	protected function checkStockAvailable($orderDetail, $addQuantity) {

		if ($addQuantity > 0) {
			$stockAvailable = StockAvailable::getQuantityAvailableByProduct($orderDetail->product_id, $orderDetail->product_attribute_id, $orderDetail->id_shop);
			$product = new Product($orderDetail->product_id, true, null, $orderDetail->id_shop);

			if (!Validate::isLoadedObject($product)) {
				$this->ajaxDie(json_encode([
					'result' => false,
					'error'  => Tools::displayError('The Product object could not be loaded.'),
				]));
			} else {

				if (($stockAvailable < $addQuantity) && (!$product->isAvailableWhenOutOfStock((int) $product->out_of_stock))) {
					$this->ajaxDie(json_encode([
						'result' => false,
						'error'  => Tools::displayError('This product is no longer in stock with those attributes '),
					]));

				}

			}

		}

	}

	/**
	 * Ajax proces delete product line
	 *
	 * @return void
	 *
	 * @since 1.8.1.0
	 */
	public function ajaxProcessDeleteProductLine() {

		$res = true;

		$orderDetail = new StudentPiecesDetail((int) Tools::getValue('id_order_detail'));
		$order = new StudentPieces((int) Tools::getValue('id_order'));

		$this->doDeleteProductLineValidation($orderDetail, $order);

		// Update StudentPiecesInvoice of this StudentPiecesDetail

		if ($orderDetail->id_order_invoice != 0) {
			$orderInvoice = new StudentPiecesInvoice($orderDetail->id_order_invoice);
			$orderInvoice->total_paid_tax_excl -= $orderDetail->total_price_tax_excl;
			$orderInvoice->total_paid_tax_incl -= $orderDetail->total_price_tax_incl;
			$orderInvoice->total_products -= $orderDetail->total_price_tax_excl;
			$orderInvoice->total_products_wt -= $orderDetail->total_price_tax_incl;
			$res &= $orderInvoice->update();
		}

		// Update StudentPieces
		$order->total_paid -= $orderDetail->total_price_tax_incl;
		$order->total_paid_tax_incl -= $orderDetail->total_price_tax_incl;
		$order->total_paid_tax_excl -= $orderDetail->total_price_tax_excl;
		$order->total_products -= $orderDetail->total_price_tax_excl;
		$order->total_products_wt -= $orderDetail->total_price_tax_incl;

		$res &= $order->update();

		// Reinject quantity in stock
		$this->reinjectQuantity($orderDetail, $orderDetail->product_quantity, true);

		// Update weight SUM
		$orderCarrier = new StudentPiecesCarrier((int) $order->getIdStudentPiecesCarrier());

		if (Validate::isLoadedObject($orderCarrier)) {
			$orderCarrier->weight = (float) $order->getTotalWeight();
			$res &= $orderCarrier->update();

			if ($res) {
				$order->weight = sprintf("%.3f " . Configuration::get('PS_WEIGHT_UNIT'), $orderCarrier->weight);
			}

		}

		if (!$res) {
			$this->ajaxDie(json_encode([
				'result' => $res,
				'error'  => Tools::displayError('An error occurred while attempting to delete the product line.'),
			]));
		}

		// Get invoices collection
		$invoiceCollection = $order->getInvoicesCollection();

		$invoiceArray = [];

		foreach ($invoiceCollection as $invoice) {
			/** @var StudentPiecesInvoice $invoice */
			$invoice->name = $invoice->getInvoiceNumberFormatted($this->context->language->id, (int) $order->id_shop);
			$invoiceArray[] = $invoice;
		}

		// Assign to smarty informations in order to show the new product line
		$this->context->smarty->assign([
			'order'               => $order,
			'currency'            => new Currency($order->id_currency),
			'invoices_collection' => $invoiceCollection,
			'current_id_lang'     => $this->context->language->id,
			'link'                => $this->context->link,
			'current_index'       => static::$currentIndex,
		]);

		$this->sendChangedNotification($order);

		$this->ajaxDie(json_encode([
			'result'         => $res,
			'order'          => $order,
			'invoices'       => $invoiceArray,
			'documents_html' => $this->createTemplate('_documents.tpl')->fetch(),
			'shipping_html'  => $this->createTemplate('_shipping.tpl')->fetch(),
		]));
	}

	/**
	 * Ajax process change payment method
	 *
	 * @return void
	 *
	 * @since 1.8.1.0
	 */
	public function ajaxProcessChangePaymentMethod() {

		$student = new Customer(Tools::getValue('id_student'));
		$modules = Module::getAuthorizedModules($student->id_default_group);
		$authorizedModules = [];

		if (!Validate::isLoadedObject($student) || !is_array($modules)) {
			$this->ajaxDie(json_encode(['result' => false]));
		}

		foreach ($modules as $module) {
			$authorizedModules[] = (int) $module['id_module'];
		}

		$paymentModules = [];

		foreach (PaymentModule::getInstalledPaymentModules() as $pModule) {

			if (in_array((int) $pModule['id_module'], $authorizedModules)) {
				$paymentModules[] = Module::getInstanceById((int) $pModule['id_module']);
			}

		}

		$this->context->smarty->assign([
			'payment_modules' => $paymentModules,
		]);

		$this->ajaxDie(json_encode([
			'result' => true,
			'view'   => $this->createTemplate('_select_payment.tpl')->fetch(),
		]));
	}

	/**
	 * Apply discount on invoice
	 *
	 * @param StudentPiecesInvoice $orderInvoice
	 * @param float        $valueTaxIncl
	 * @param float        $valueTaxExcl
	 *
	 * @return bool Indicates whether the invoice was successfully updated
	 *
	 * @since 1.8.1.0
	 * @since 1.0.1 Return update status bool
	 */
	protected function applyDiscountOnInvoice($orderInvoice, $valueTaxIncl, $valueTaxExcl) {

		// Update StudentPiecesInvoice
		$orderInvoice->total_discount_tax_incl += $valueTaxIncl;
		$orderInvoice->total_discount_tax_excl += $valueTaxExcl;
		$orderInvoice->total_paid_tax_incl -= $valueTaxIncl;
		$orderInvoice->total_paid_tax_excl -= $valueTaxExcl;
		$orderInvoice->update();
	}

	/**
	 * Edit production validation
	 *
	 * @param StudentPiecesDetail       $orderDetail
	 * @param StudentPieces             $order
	 * @param StudentPiecesInvoice|null $orderInvoice
	 *
	 * @return void
	 *
	 * @since 1.8.1.0
	 */
	protected function doEditProductValidation(StudentPiecesDetail $orderDetail, StudentPieces $order, StudentPiecesInvoice $orderInvoice = null) {

		if (!Validate::isLoadedObject($orderDetail)) {
			$this->ajaxDie(json_encode([
				'result' => false,
				'error'  => Tools::displayError('The StudentPieces Detail object could not be loaded.'),
			]));
		}

		if (!empty($orderInvoice) && !Validate::isLoadedObject($orderInvoice)) {
			$this->ajaxDie(json_encode([
				'result' => false,
				'error'  => Tools::displayError('The invoice object cannot be loaded.'),
			]));
		}

		if (!Validate::isLoadedObject($order)) {
			$this->ajaxDie(json_encode([
				'result' => false,
				'error'  => Tools::displayError('The order object cannot be loaded.'),
			]));
		}

		if ($orderDetail->id_order != $order->id) {
			$this->ajaxDie(json_encode([
				'result' => false,
				'error'  => Tools::displayError('You cannot edit the order detail for this order.'),
			]));
		}

		// We can't edit a delivered order

		if ($order->hasBeenDelivered()) {
			$this->ajaxDie(json_encode([
				'result' => false,
				'error'  => Tools::displayError('You cannot edit a delivered order.'),
			]));
		}

		if (!empty($orderInvoice) && $orderInvoice->id_order != Tools::getValue('id_order')) {
			$this->ajaxDie(json_encode([
				'result' => false,
				'error'  => Tools::displayError('You cannot use this invoice for the order'),
			]));
		}

		// Clean price
		$productPriceTaxIncl = str_replace(',', '.', Tools::getValue('product_price_tax_incl'));
		$productPriceTaxExcl = str_replace(',', '.', Tools::getValue('product_price_tax_excl'));

		if (!Validate::isPrice($productPriceTaxIncl) || !Validate::isPrice($productPriceTaxExcl)) {
			$this->ajaxDie(json_encode([
				'result' => false,
				'error'  => Tools::displayError('Invalid price'),
			]));
		}

		if (!is_array(Tools::getValue('product_quantity')) && !Validate::isUnsignedInt(Tools::getValue('product_quantity'))) {
			$this->ajaxDie(json_encode([
				'result' => false,
				'error'  => Tools::displayError('Invalid quantity'),
			]));
		} else

		if (is_array(Tools::getValue('product_quantity'))) {

			foreach (Tools::getValue('product_quantity') as $qty) {

				if (!Validate::isUnsignedInt($qty)) {
					$this->ajaxDie(json_encode([
						'result' => false,
						'error'  => Tools::displayError('Invalid quantity'),
					]));
				}

			}

		}

	}

	/**
	 * Delete product line validation
	 *
	 * @param StudentPiecesDetail $orderDetail
	 * @param StudentPieces       $order
	 *
	 * @return void
	 *
	 * @since 1.8.1.0
	 */
	protected function doDeleteProductLineValidation(StudentPiecesDetail $orderDetail, StudentPieces $order) {

		if (!Validate::isLoadedObject($orderDetail)) {
			$this->ajaxDie(json_encode([
				'result' => false,
				'error'  => Tools::displayError('The StudentPieces Detail object could not be loaded.'),
			]));
		}

		if (!Validate::isLoadedObject($order)) {
			$this->ajaxDie(json_encode([
				'result' => false,
				'error'  => Tools::displayError('The order object cannot be loaded.'),
			]));
		}

		if ($orderDetail->id_order != $order->id) {
			$this->ajaxDie(json_encode([
				'result' => false,
				'error'  => Tools::displayError('You cannot delete the order detail.'),
			]));
		}

		// We can't edit a delivered order

		if ($order->hasBeenDelivered()) {
			$this->ajaxDie(json_encode([
				'result' => false,
				'error'  => Tools::displayError('You cannot edit a delivered order.'),
			]));
		}

	}

	/**
	 * @param StudentPiecesDetail $orderDetail
	 * @param int         $qtyCancelProduct
	 * @param bool        $delete
	 *
	 * @return void
	 *
	 * @since 1.8.1.0
	 */
	protected function reinjectQuantity($orderDetail, $qtyCancelProduct, $delete = false) {

		// Reinject product
		$reinjectableQuantity = (int) $orderDetail->product_quantity - (int) $orderDetail->product_quantity_reinjected;
		$quantityToReinject = $qtyCancelProduct > $reinjectableQuantity ? $reinjectableQuantity : $qtyCancelProduct;
		// @since 1.5.0 : Advanced Stock Management
		// FIXME: this should do something
		// $product_to_inject = new Product($orderDetail->product_id, false, (int) $this->context->language->id, (int) $orderDetail->id_shop);

		$product = new Product($orderDetail->product_id, false, (int) $this->context->language->id, (int) $orderDetail->id_shop);

		if (Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT') && $product->advanced_stock_management && $orderDetail->id_warehouse != 0) {
			$manager = StockManagerFactory::getManager();
			$movements = StockMvt::getNegativeStockMvts(
				$orderDetail->id_order,
				$orderDetail->product_id,
				$orderDetail->product_attribute_id,
				$quantityToReinject
			);
			$leftToReinject = $quantityToReinject;

			foreach ($movements as $movement) {

				if ($leftToReinject > $movement['physical_quantity']) {
					$quantityToReinject = $movement['physical_quantity'];
				}

				$leftToReinject -= $quantityToReinject;

				if (Pack::isPack((int) $product->id)) {
					// Gets items

					if ($product->pack_stock_type == 1 || $product->pack_stock_type == 2 || ($product->pack_stock_type == 3 && Configuration::get('PS_PACK_STOCK_TYPE') > 0)) {
						$productsPack = Pack::getItems((int) $product->id, (int) Configuration::get('PS_LANG_DEFAULT'));
						// Foreach item

						foreach ($productsPack as $productPack) {

							if ($productPack->advanced_stock_management == 1) {
								$manager->addProduct(
									$productPack->id,
									$productPack->id_pack_product_attribute,
									new Warehouse($movement['id_warehouse']),
									$productPack->pack_quantity * $quantityToReinject,
									null,
									$movement['price_te'],
									true
								);
							}

						}

					}

					if ($product->pack_stock_type == 0 || $product->pack_stock_type == 2 ||
						($product->pack_stock_type == 3 && (Configuration::get('PS_PACK_STOCK_TYPE') == 0 || Configuration::get('PS_PACK_STOCK_TYPE') == 2))
					) {
						$manager->addProduct(
							$orderDetail->product_id,
							$orderDetail->product_attribute_id,
							new Warehouse($movement['id_warehouse']),
							$quantityToReinject,
							null,
							$movement['price_te'],
							true
						);
					}

				} else {
					$manager->addProduct(
						$orderDetail->product_id,
						$orderDetail->product_attribute_id,
						new Warehouse($movement['id_warehouse']),
						$quantityToReinject,
						null,
						$movement['price_te'],
						true
					);
				}

			}

			$idProduct = $orderDetail->product_id;

			if ($delete) {
				$orderDetail->delete();
			}

			StockAvailable::synchronize($idProduct);
		} else

		if ($orderDetail->id_warehouse == 0) {
			StockAvailable::updateQuantity(
				$orderDetail->product_id,
				$orderDetail->product_attribute_id,
				$quantityToReinject,
				$orderDetail->id_shop
			);

			if ($delete) {
				$orderDetail->delete();
			}

		} else {
			$this->errors[] = Tools::displayError('This product cannot be re-stocked.');
		}

	}

	/**
	 * @param StudentPieces $order
	 *
	 * @return array
	 *
	 * @since 1.8.1.0
	 */
	protected function getProducts($order) {

		$products = $order->getProducts();

		foreach ($products as &$product) {

			if ($product['image'] != null) {
				$name = 'product_mini_' . (int) $product['product_id'] . (isset($product['product_attribute_id']) ? '_' . (int) $product['product_attribute_id'] : '') . '.jpg';
				// generate image cache, only for back office
				$product['image_tag'] = ImageManager::thumbnail(_PS_IMG_DIR_ . 'p/' . $product['image']->getExistingImgPath() . '.jpg', $name, 45, 'jpg');

				if (file_exists(_PS_TMP_IMG_DIR_ . $name)) {
					$product['image_size'] = getimagesize(_PS_TMP_IMG_DIR_ . $name);
				} else {
					$product['image_size'] = false;
				}

			}

		}

		ksort($products);

		return $products;
	}

	public function printPdf($idPiece) {

		$context = Context::getContext();
		$studentPiece = new StudentPieces($idPiece);
		$studentEducation = new StudentEducation($studentPiece->id_student_education);
		$studentPiece->prefix = $this->getStaticPrefix($studentPiece->piece_type);
		$studentPiece->nameType = $this->getStaticPieceName($studentPiece->piece_type);
		$student = new Customer($studentPiece->id_customer);

		$context = Context::getContext();
		$idShop = (int) $context->shop->id;

		if (Configuration::get('PS_LOGO_INVOICE', null, null, $idShop) != false && file_exists(_PS_IMG_DIR_ . Configuration::get('PS_LOGO_INVOICE', null, null, $idShop))) {
			$logo = _PS_IMG_DIR_ . Configuration::get('PS_LOGO_INVOICE', null, null, $idShop);
			$logo_path = _PS_IMG_ . Configuration::get('PS_LOGO_INVOICE', null, null, $idShop);
		} else

		if (Configuration::get('PS_LOGO', null, null, $idShop) != false && file_exists(_PS_IMG_DIR_ . Configuration::get('PS_LOGO', null, null, $idShop))) {
			$logo = _PS_IMG_DIR_ . Configuration::get('PS_LOGO', null, null, $idShop);
			$logo_path = _PS_IMG_ . Configuration::get('PS_LOGO', null, null, $idShop);
		}

		$mpdf = new \Mpdf\Mpdf([
			'margin_left'   => 10,
			'margin_right'  => 10,
			'margin_top'    => 120,
			'margin_bottom' => 75,
			'margin_header' => 10,
			'margin_footer' => 10,
		]);
		$payments = Payment::getByCustomerPieceId($studentPiece->id, $context->language->id);
		$data = $this->createTemplate('controllers/student_pieces/pdf/headertemplate.tpl');

		$data->assign(
			[
				'company'          => $context->company,
				'free_text'        => Configuration::get('PS_INVOICE_FREE_TEXT', (int) Context::getContext()->language->id, null, $context->shop->id),
				'logo_path'        => $logo_path,
				'piece'            => $studentPiece,
				'nameType'         => 'FACTURE',
				'payments'         => $payments,
				'student'          => $student,
				'studentEducation' => $studentEducation,
			]
		);
		$mpdf->SetHTMLHeader($data->fetch());

		$data = $this->createTemplate('controllers/student_pieces/pdf/footertemplate.tpl');

		$data->assign(
			[
				'company'          => $context->company,
				'free_text'        => Configuration::get('PS_INVOICE_FREE_TEXT', (int) Context::getContext()->language->id, null, $context->shop->id),
				'logo_path'        => $logo_path,
				'piece'            => $studentPiece,
				'payments'         => $payments,
				'nameType'         => 'FACTURE',
				'student'          => $student,
				'studentEducation' => $studentEducation,
			]
		);
		$mpdf->SetHTMLFooter($data->fetch(), 'O');

		$data = $this->createTemplate('controllers/student_pieces/pdf.css.tpl');
		$data->assign(
			[
				'color' => '#fa4616',
			]
		);
		$stylesheet = $data->fetch();

		$data = $this->createTemplate('controllers/student_pieces/pdf/bodytemplate.tpl');

		$data->assign(
			[
				'company'          => $context->company,
				'free_text'        => Configuration::get('PS_INVOICE_FREE_TEXT', (int) Context::getContext()->language->id, null, $context->shop->id),
				'logo_path'        => $logo_path,
				'piece'            => $studentPiece,
				'payments'         => $payments,
				'nameType'         => 'FACTURE',
				'student'          => $student,
				'studentEducation' => $studentEducation,
			]
		);

		if ($studentPiece->validate == 0 && $studentPiece->piece_type == 'INVOICE') {
			$watermark = $this->l('Provisoire');
			$mpdf->SetWatermarkText($watermark);
		} else

		if ($studentPiece->validate == 1 && $studentPiece->piece_type == 'INVOICE') {
			$mpdf->SetProtection(['copy', 'print'], '', _DB_PASSWD_);
		}

		$filePath = _PS_ADMIN_DIR_ . DIRECTORY_SEPARATOR . 'invoices' . DIRECTORY_SEPARATOR;
		$fileName = "Facture_" . $studentPiece->prefix . $studentPiece->piece_number . '_Dossier_n°' . $studentEducation->reference_edof . '_' . $student->lastname . '_' . $student->firstname . '.pdf';
		$mpdf->SetTitle($context->company->company_name . " " . $this->getStaticPieceName($studentPiece->piece_type) . " " . $studentPiece->prefix . $studentPiece->piece_number);
		$mpdf->SetAuthor($context->company->company_name);
		$mpdf->showWatermarkText = true;
		$mpdf->watermark_font = 'DejaVuSansCondensed';
		$mpdf->watermarkTextAlpha = 0.1;
		$mpdf->SetDisplayMode('fullpage');

		$mpdf->WriteHTML($stylesheet, \Mpdf\HTMLParserMode::HEADER_CSS);
		$mpdf->WriteHTML($data->fetch());

		$mpdf->Output($filePath . $fileName, 'F');

	}

	public function ajaxProcessGenerateReglement() {

		$idPiece = Tools::getValue('idPiece');
		$studentPiece = new StudentPieces($idPiece);
		$studentPiece->total_paid = $studentPiece->education_tax_incl;
		$studentPiece->update();
		if (!$error) {

			$return = [
				'success' => true,
				'message' => 'Le payement a été correctement enregistré',
			];
		} else {
			$return = [
				'success' => false,
				'message' => 'Jeff a merdé',
			];
		}

		die(Tools::jsonEncode($return));

	}

	public function ajaxProcessGenerateBulkReglement() {

		$pieces = Tools::getValue('pieces');

		foreach ($pieces as $key => $idPiece) {

			$studentPiece = new StudentPieces($idPiece);
			$studentPiece->total_paid = $studentPiece->education_tax_incl;
			$studentPiece->update();

			

		}

		if (!$error) {
			$return = [
				'success' => true,
				'message' => 'Les payements ont été correctement enregistrés',
			];
		} else {
			$return = [
				'success' => false,
				'message' => 'Jeff a merdé',
			];
		}

		die(Tools::jsonEncode($return));

	}

	public function ajaxProcessGetDataMonth() {

		$dateStart = Tools::getValue('start');
		$dateEnd = Tools::getValue('end');

		$payment = StudentPieces::getTotalbyrange($dateStart, $dateEnd);

		$date = new DateTime($dateStart);
		$month = Tools::getMonthById($date->format('m'));

		$html = '<p>Total facturé au mois de ' . $month . ' : ' . number_format($payment[0]['totalHT'], 2, ",", " ") . ' € HT</p>';
		$html .= '<p>Soit ' . number_format($payment[0]['totalTTC'], 2, ",", " ") . ' € TTC</p>';

		$return = [
			'html' => $html,
		];
		die(Tools::jsonEncode($return));

	}

	public function ajaxProcessGetDataSession() {

		$idSession = Tools::getValue('idSession');
		$session = new EducationSession($idSession);

		$payment = StudentPieces::getTotalbyidSession($idSession);

		$html = '<p>Total facturé pour la ' . $session->name . ' : ' . number_format($payment[0]['totalHT'], 2, ",", " ") . ' € HT</p>';
		$html .= '<p>Soit ' . number_format($payment[0]['totalTTC'], 2, ",", " ") . ' € TTC</p>';

		$return = [
			'html' => $html,
		];
		die(Tools::jsonEncode($return));

	}

	public function ajaxProcessBookStudentPieces() {

		
		$id_student_piece = Tools::getValue('id_student_piece');
		$piece = new StudentPieces($id_student_piece);

		$piece->id_book_record = 1;
		$piece->is_book = 1;
		$piece->update();
		$return = [
			'success' => true,
			'message' => 'Le Facture ' . $piece->prefix . $piece->piece_number . ' a été comptabilisée avec succès',
		];
		die(Tools::jsonEncode($return));
	}

	public function ajaxProcessBulkBook() {

		
		$idPieces = Tools::getValue('idPieces');

		foreach ($idPieces as $id) {
			$piece = new StudentPieces($id);

			if ($piece->is_book == 1) {
				continue;
			}

			$piece->id_book_record = 1;
			$piece->is_book = 1;
			$piece->update();
		}

		$result = [
			'success' => true,
			'message' => $this->l('Les pièces ont été comptabilisées avec succès'),
		];

		die(Tools::jsonEncode($result));
	}

}
