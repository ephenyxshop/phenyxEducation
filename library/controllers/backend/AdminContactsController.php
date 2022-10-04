<?php

/**
 * Class AdminContactsControllerCore
 *
 * @since 1.9.1.0
 */
class AdminContactsControllerCore extends AdminController {
    
    public $php_self = 'admincontacts';

    /**
     * AdminContactsControllerCore constructor.
     *
     * @since 1.9.1.0
     */
    public function __construct() {

        $this->bootstrap = true;
        $this->table = 'contact';
        $this->className = 'Contact';
        $this->lang = true;
        $this->publicName = $this->la('Géstion des Contacts');
        $this->context = Context::getContext();
       

        parent::__construct();
    }
    
    public function generateParaGridScript($regenerate = false) {

		
		$this->paramPageModel = [
			'type'       => '\'local\'',
			'rPP'        => 40,
			'rPPOptions' => [10, 20, 40, 50, 100, 200, 500],
		];

		$this->rowInit = 'function (ui) {
			return {' . PHP_EOL . '
                    attr: \'data-link="\'+AjaxLink' . $this->controller_name . '+\'" data-class="' . $this->className . '" data-rowIndx="\' + ui.rowIndx+\'" data-object="\' + ui.rowData.' . $this->identifier . '+\' "\',
            };
        }';
		
		$this->paramToolbar = [
            'items' => [

                ['type' => '\'separator\''],

                [
                    'type'     => '\'button\'',
                    'icon'     => '\'ui-icon-disk\'',
                    'label'    => '\'' . $this->la('Ajouter un contact') . '\'',
                    'cls'      => '\'changes ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only pull-right\'',
                    'listener' => 'function () {' . PHP_EOL . '
                           addAjaxObject("' . $this->controller_name . '");' . PHP_EOL . '
                          }' . PHP_EOL,
                ],

            ],
        ];
		$this->paramTitle = '\'' . $this->la('Gestions des Contacts') . '\'';
		
		$this->filterModel = [
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
                selected = selgrid' . $this->className . '.getSelection().length;
                var dataLenght = grid' . $this->className . '.option(\'dataModel.data\').length;
                return {
                    callback: function(){},
                    items: {
                        "edit": {
                            name : \'' . $this->la('Modifier ') . '\'' . '+rowData.firstname+" "+rowData.lastname,
                            icon: "edit",
                            callback: function(itemKey, opt, e) {
                             	//editSaleAgent(rowData.id_sale_agent);
								 editAjaxObject("' .$this->controller_name.'", rowData.id_contact)
                            }
                        },


                        "sep2": "---------",
                        "delete": {
                            name: \'' . $this->la('Supprimer ') . '\'' . '+rowData.firstname+" "+rowData.lastname,
                            icon: "delete",
                            visible: function(key, opt) {
                                if (selected == 1) {
                                    return true;
                                }
                            return false;
                            },
                            callback: function(itemKey, opt, e) {
                               deleteObject("' . $this->controller_name . '", "' . $this->className . '", "Supprimer un CEF", "Etes-vous sûr(e) de vouloir supprimer "+rowData.firstname+" "+ rowData.lastname+ " ?", "Oui", "Annuler",rowData.id_contact, rowIndex);
                            }
                        },


                    },
                };
            }',
			]];

		return parent::generateParaGridScript();
	}

	public function generateParaGridOption() {

		return true;

	}
    
     public function getContactRequest() {

        $contacts = Db::getInstance(_EPH_USE_SQL_SLAVE_)->executeS(
            (new DbQuery())
                ->select('*, cl.name')
                ->from('contact', 'c')
                ->leftJoin('contact_lang', 'cl', 'c.`id_contact` = cl.`id_contact` AND cl.`id_lang` = ' . (int)  $this->context->language->id)
                ->orderBy('`name` ASC')
        );
        
        foreach ($contacts as &$contact) {

            if ($contact['technical_service'] == 1) {
                $contact['technical_service'] = '<div class="p-active"><i class="fa fa-check" aria-hidden="true" style="color:green;"></i></div>';
            } else {
                $contact['technical_service'] = '<div class="p-inactive"><i class="fa fa-times" aria-hidden="true" style="color:red;"></i></div>';
            }
            
             if ($contact['student_service'] == 1) {
                $contact['student_service'] = '<div class="p-active"><i class="fa fa-check" aria-hidden="true" style="color:green;"></i></div>';
            } else {
                $contact['student_service'] = '<div class="p-inactive"><i class="fa fa-times" aria-hidden="true" style="color:red;"></i></div>';
            }

        }

        return $contacts;
    }

    public function ajaxProcessgetContactRequest() {

        die(Tools::jsonEncode($this->getContactRequest()));

    }

    public function getContactFields() {

        return [
            [
                'title'      => $this->la('ID'),
                'width'      => 50,
                'dataIndx'   => 'id_contact',
                'dataType'   => 'integer',
                'editable'   => false,
                'align'      => 'center',
                'hidden'     => true,
                'hiddenable' => 'no',
            ],

            [
                'title'      => $this->la('Avatar'),
                'width'      => 200,
                'dataIndx'   => 'name',
                'cls'        => 'name-handle',
                'align'      => 'left',
                'editable'   => false,
                'dataType'   => 'string',
                'hiddenable' => 'no',
            ],
            [
                'title'      => $this->la('Prénom'),
                'width'      => 200,
                'dataIndx'   => 'firstname',
                'cls'        => 'name-handle',
                'align'      => 'left',
                'editable'   => false,
                'dataType'   => 'string',
                'hiddenable' => 'no',
            ],
            [
                'title'      => $this->la('Nom'),
                'width'      => 200,
                'dataIndx'   => 'lastname',
                'cls'        => 'name-handle',
                'align'      => 'left',
                'editable'   => false,
                'dataType'   => 'string',
                'hiddenable' => 'no',
            ],
            [
                'title'      => $this->la('Email'),
                'width'      => 200,
                'dataIndx'   => 'email',
                'cls'        => 'name-handle',
                'align'      => 'left',
                'editable'   => false,
                'dataType'   => 'string',
                'hiddenable' => 'no',
            ],
            
          
            [
                'title'    => $this->la('Service Technique'),
                'width'    => 200,
                'dataIndx' => 'technical_service',
                'align'    => 'center',
                'editable' => false,
                'dataType' => 'html',
            ],
             [
                'title'    => $this->la('Service Client'),
                'width'    => 200,
                'dataIndx' => 'student_service',
                'align'    => 'center',
                'editable' => false,
                'dataType' => 'html',
            ],

        ];
    }

    public function ajaxProcessgetContactFields() {

        die(Tools::jsonEncode($this->getContactFields()));
    }


    /**
     * Render form
     *
     * @return string
     *
     * @since 1.9.1.0
     */
    public function renderForm() {

        $obj = $this->loadObject(true);
        
        $this->fields_form = [
            'legend' => [
                'title' => $this->la('Contacts'),
                'icon'  => 'icon-envelope-alt',
            ],
            'input'  => [
                [
                    'type' => 'hidden',
                    'name' => 'ajax',
                ],
                [
                    'type' => 'hidden',
                    'name' => 'action',
                ],
                [
                    'type'     => 'text',
                    'label'    => $this->la('Title'),
                    'name'     => 'name',
                    'required' => true,
                    'lang'     => true,
                    'col'      => 4,
                    'hint'     => $this->la('Contact name (e.g. Customer Support).'),
                ],
                [
                    'type'     => 'text',
                    'label'    => $this->la('Prénom'),
                    'name'     => 'firstname',
                    'required' => true,
                    'col'      => 4,
                ],
                [
                    'type'     => 'text',
                    'label'    => $this->la('Nom'),
                    'name'     => 'lastname',
                    'required' => true,
                    'col'      => 4,
                ],
                [
                    'type'     => 'text',
                    'label'    => $this->la('Email address'),
                    'name'     => 'email',
                    'required' => false,
                    'col'      => 4,
                    'hint'     => $this->la('Emails will be sent to this address.'),
                ],
                [
                    'type'     => 'switch',
                    'label'    => $this->la('Sercive Technique'),
                    'name'     => 'technical_service',
                    'required' => false,
                    'class'    => 't',
                    'is_bool'  => true,
                    'values'   => [
                        [
                            'id'    => 'technical_service_on',
                            'value' => 1,
                            'label' => $this->la('Enabled'),
                        ],
                        [
                            'id'    => 'technical_service_off',
                            'value' => 0,
                            'label' => $this->la('Disabled'),
                        ],
                    ],
                ],
                [
                    'type'     => 'switch',
                    'label'    => $this->la('Service Commerciale'),
                    'name'     => 'student_service',
                    'required' => false,
                    'class'    => 't',
                    'is_bool'  => true,
                    'hint'     => $this->la('If enabled, all messages will be saved in the "Customer Service" page under the "Customer" menu.'),
                    'values'   => [
                        [
                            'id'    => 'student_service_on',
                            'value' => 1,
                            'label' => $this->la('Enabled'),
                        ],
                        [
                            'id'    => 'student_service_off',
                            'value' => 0,
                            'label' => $this->la('Disabled'),
                        ],
                    ],
                ],
                [
                    'type'     => 'textarea',
                    'label'    => $this->la('Description'),
                    'name'     => 'description',
                    'required' => false,
                    'lang'     => true,
                    'col'      => 6,
                    'hint'     => $this->la('Further information regarding this contact.'),
                ],
            ],
            'submit' => [
                'title' => $this->la('Save'),
            ],
        ];

        
        
        $this->form_ajax = 1;

        if ($obj->id > 0) {
            $this->form_action = 'updateContact';
            $this->editObject = 'Editer un contact';
        } else {
            $this->form_action = 'addContact';
            $this->editObject = 'Ajouter un contact';
        }

        return parent::renderForm();
    }
    
    public function ajaxProcessUpdateContact() {
        
        $idContact = Tools::getValue('id_contact');

        if ($idContact && Validate::isUnsignedId($idContact)) {

            $contact = new Contact($idContact);

            if (Validate::isLoadedObject($contact)) {

                foreach ($_POST as $key => $value) {

                    if (property_exists($contact, $key) && $key != 'id_contact') {
                        $contact->{$key} = $value;
                    }

                }

                $classVars = get_class_vars(get_class($contact));
                $fields = [];

                if (isset($classVars['definition']['fields'])) {
                    $fields = $classVars['definition']['fields'];
                }

                foreach ($fields as $field => $params) {

                    if (array_key_exists('lang', $params) && $params['lang']) {

                        foreach (Language::getIDs(false) as $idLang) {

                            if (Tools::isSubmit($field . '_' . (int) $idLang)) {

                                if (!isset($contact->{$field}) || !is_array($contact->{$field})) {
                                    $contact->{$field} = [];
                                }

                                $contact->{$field} [(int) $idLang] = Tools::getValue($field . '_' . (int) $idLang);
                            }

                        }

                    }

                }

                try {
                    $result = $contact->update();
                } catch (Exception $e) {
                   
                }

                if ($result) {
                   

                    $return = [
                        'success' => true,
                        'message' => $this->la('Le contact a été mise à jour avec succès'),
                    ];
                }

            } else {
                $return = [
                    'success' => false,
                    'message' => $this->la('Un true a merdé AVEC PNJET'),
                ];
            }

        } else {
            $return = [
                'success' => false,
                'message' => $this->la('Un true a merdé avec  ID'),
            ];
        }

        die(Tools::jsonEncode($return));
        
    }
    
    public function ajaxProcessaddContact() {
        
        $contact = new Contact();

        foreach ($_POST as $key => $value) {

            if (property_exists($contact, $key) && $key != 'id_contact') {
                $contact->{$key}

                = $value;
            }

        }

        $classVars = get_class_vars(get_class($contact));
        $fields = [];

        if (isset($classVars['definition']['fields'])) {
            $fields = $classVars['definition']['fields'];
        }

        foreach ($fields as $field => $params) {

            if (array_key_exists('lang', $params) && $params['lang']) {

                foreach (Language::getIDs(false) as $idLang) {

                    if (Tools::isSubmit($field . '_' . (int) $idLang)) {

                        if (!isset($contact->{$field}) || !is_array($contact->{$field})) {
                            $contact->{$field}
                            = [];
                        }

                        $contact->{$field}
                        [(int) $idLang] = Tools::getValue($field . '_' . (int) $idLang);
                    }

                }

            }

        }
        try {

            $result = $contact->add();

        } catch (Exception $e) {

            fwrite($file, "Error : " . $e->getMessage() . PHP_EOL);
        }

        if ($result) {
          
            $return = [
                'success' => true,
                'message' => $this->la('Le contact a été ajoutée avec succès'),
            ];
        } else {
            $return = [
                'success' => false,
                'message' => $this->la('Bug merde add'),
            ];
        }

        die(Tools::jsonEncode($return));
    }

    

}
