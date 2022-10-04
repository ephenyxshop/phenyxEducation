<?php

/**
 * Class AdminMetaControllerCore
 *
 * @since 1.9.5.0
 */
class AdminMetaControllerCore extends AdminController {

    // @codingStandardsIgnoreStart
    public $php_self = 'adminmeta';
	public $table = 'meta';
    public $className = 'Meta';
    public $lang = true;

    /** @var ShopUrl */
    protected $url = false;
    protected $toolbar_scroll = false;
    protected $ht_file = '';
    protected $rb_file = '';
    protected $rb_data = [];
    protected $sm_file = '';
    /** @var Meta $object */
    protected $object;
    // @codingStandardsIgnoreEnd

    /**
     * AdminMetaControllerCore constructor.
     *
     * @since 1.8.5.0
     */
    public function __construct() {

        $this->table = 'meta';
        $this->className = 'Meta';
        $this->publicName = $this->la('Meta');

        $this->bootstrap = true;
        $this->identifier_name = 'page';
        $this->ht_file = _EPH_ROOT_DIR_ . '/../.htaccess';
        $this->rb_file = _EPH_ROOT_DIR_ . '/../robots.txt';
        $this->rb_data = $this->getRobotsContent();

        parent::__construct();

        $this->sm_file = _EPH_ROOT_DIR_ . DIRECTORY_SEPARATOR . $this->context->company->id . '_index_sitemap.xml';
        // Options to generate friendly urls
        $modRewrite = Tools::modRewriteActive();
        $generalFields = [
            'EPH_REWRITING_SETTINGS'       => [
                'title'      => $this->la('Friendly URL'),
                'hint'       => ($this->la('This option gives your shop SEO friendly, human readable URLs, e.g. http://example.com/blouse instead of http://example.com/index.php?id_product=1&controller=product (recommended).')),
                'validation' => 'isBool',
                'cast'       => 'intval',
                'type'       => 'bool',
                'desc'       => (!$modRewrite ? $this->la('URL rewriting (mod_rewrite) is not active on your server, or it is not possible to check your server configuration. If you want to use Friendly URLs, you must activate this mod.') : ''),
                'disabled'   => !$modRewrite,
            ],
            'EPH_ALLOW_ACCENTED_CHARS_URL' => [
                'title'      => $this->la('Accented URL'),
                'hint'       => $this->la('Enable this option if you want to allow accented characters in your friendly URLs.') . ' ' . $this->la('You should only activate this option if you are using non-latin characters. For all the latin charsets, your SEO will be better without this option.'),
                'validation' => 'isBool',
                'cast'       => 'intval',
                'type'       => 'bool',
                'desc'       => (!$modRewrite ? $this->la('Not available because URL rewriting (mod_rewrite) isn\'t available.') : ''),
                'disabled'   => !$modRewrite,
            ],
            'EPH_CANONICAL_REDIRECT'       => [
                'title'      => $this->la('Redirect to the canonical URL'),
                'validation' => 'isUnsignedInt',
                'cast'       => 'intval',
                'type'       => 'select',
                'list'       => [
                    ['value' => 0, 'name' => $this->la('No redirection (you may have duplicate content issues)')],
                    ['value' => 1, 'name' => $this->la('302 Moved Temporarily (recommended while setting up your store)')],
                    ['value' => 2, 'name' => $this->la('301 Moved Permanently (recommended once you have gone live)')],
                ],
                'identifier' => 'value',
            ],
        ];

        $urlDescription = '';

        if ($this->checkConfiguration($this->ht_file)) {
            $generalFields['EPH_HTACCESS_DISABLE_MULTIVIEWS'] = [
                'title'      => $this->la('Disable Apache\'s MultiViews option'),
                'hint'       => $this->la('Enable this option only if you have problems with URL rewriting.'),
                'validation' => 'isBool',
                'cast'       => 'intval',
                'type'       => 'bool',
            ];

            $generalFields['EPH_HTACCESS_DISABLE_MODSEC'] = [
                'title'      => $this->la('Disable Apache\'s mod_security module'),
                'hint'       => $this->la('Some of ephenyx\' features might not work correctly with a specific configuration of Apache\'s mod_security module. We recommend to turn it off.'),
                'validation' => 'isBool',
                'cast'       => 'intval',
                'type'       => 'bool',
            ];
        } else {
            $urlDescription = $this->la('Before you can use this tool, you need to:');
            $urlDescription .= $this->la('1) Create a blank .htaccess file in your root directory.');
            $urlDescription .= $this->la('2) Give it write permissions (CHMOD 666 on Unix system).');
        }

        // Options for shop URL if multishop is disabled
        $companyUrlOptions = [
            'title'  => $this->la('Set shop URL'),
            'fields' => [],
        ];

         $this->url = CompanyUrl::getCompanyUrls($this->context->company->id)->where('main', '=', 1)->getFirst();

            if ($this->url) {
                $companyUrlOptions['description'] = $this->la('Here you can set the URL for your shop. If you migrate your shop to a new URL, remember to change the values below.');
                $companyUrlOptions['fields'] = [
                    'domain'     => [
                        'title'        => $this->la('Domaine'),
                        'validation'   => 'isString',
                        'type'         => 'text',
                        'defaultValue' => $this->url->domain,
                    ],
                    'domain_ssl' => [
                        'title'        => $this->la('Domaine SSL'),
                        'validation'   => 'isString',
                        'type'         => 'text',
                        'defaultValue' => $this->url->domain_ssl,
                    ],   
                    'agent_url' => [
                        'title'        => $this->la('Domaine Agents Commerciaux'),
                        'validation'   => 'isString',
                        'type'         => 'text',
                        'defaultValue' => $this->url->agent_url,
                    ],
                    'uri'        => [
                        'title'        => $this->la('Base URI'),
                        'validation'   => 'isString',
                        'type'         => 'text',
                        'defaultValue' => $this->url->physical_uri,
                    ],
                ];
                $companyUrlOptions['submit'] = ['title' => $this->la('Save')];
            }


        // List of options
        $this->fields_options = [
            'general' => [
                'title'       => $this->la('Set up URLs'),
                'description' => $urlDescription,
                'fields'      => $generalFields,
                'submit'      => ['title' => $this->la('Save'), 'id' => 'submitGeneralFields'],
            ],
        ];

        $this->fields_options['shop_url'] = $companyUrlOptions;

        // Add display route options to options form

        if (Configuration::get('EPH_REWRITING_SETTINGS') || Tools::getValue('EPH_REWRITING_SETTINGS')) {

            if (Configuration::get('EPH_REWRITING_SETTINGS')) {
                $this->addAllRouteFields();
            }

            $this->fields_options['routes']['title'] = $this->la('Schema of URLs');
            $this->fields_options['routes']['description'] = $this->la('This section enables you to change the default pattern of your links. In order to use this functionality, ephenyx\' "Friendly URL" option must be enabled, and Apache\'s URL rewriting module (mod_rewrite) must be activated on your web server.') . '<br />' . $this->la('There are several available keywords for each route listed below; note that keywords with * are required!') . '<br />' . $this->la('To add a keyword in your URL, use the {keyword} syntax. If the keyword is not empty, you can add text before or after the keyword with syntax {prepend:keyword:append}. For example {-hey-:meta_title} will add "-hey-my-title" in the URL if the meta title is set.');
            $this->fields_options['routes']['submit'] = ['title' => $this->la('Save'), 'id' => 'submitRoutes'];
        }

        // Options to generate robot.txt
        $robotsDescription = $this->la('Your robots.txt file MUST be in your website\'s root directory and nowhere else (e.g. http://www.example.com/robots.txt).') . ' ';

        if ($this->checkConfiguration($this->rb_file)) {
            $robotsDescription .= $this->la('Generate your "robots.txt" file by clicking on the following button (this will erase the old robots.txt file)');
            $robotsSubmit = [];
        } else {
            $robotsDescription .= $this->la('Before you can use this tool, you need to:');
            $robotsDescription .= $this->la('1) Create a blank robots.txt file in your root directory.');
            $robotsDescription .= $this->la('2) Give it write permissions (CHMOD 666 on Unix system).');
        }

        $this->fields_options['robots'] = [
            'title'       => $this->la('General'),
            'description' => $robotsDescription,
            'icon'        => 'icon-cogs',
            'fields'      => [
                'robots' => [
                    'title'                     => $this->la('robots.txt'),
                    'type'                      => 'code',
                    'mode'                      => 'text',
                    'enableBasicAutocompletion' => true,
                    'enableSnippets'            => true,
                    'enableLiveAutocompletion'  => true,
                    'maxLines'                  => 400,
                    'value'                     => Tools::isSubmit('robots') ? Tools::getValue('robots') : @file_get_contents(_EPH_ROOT_DIR_ . '/robots.txt'),
                    'auto_value'                => false,
                ],
            ],
            'submit'      => isset($robotsSubmit) ? ['title' => $this->la('Save')] : null,
            'buttons'     => [
                'generateRobots' => [
                    'class' => 'btn btn-default pull-left',
                    'title' => $this->la('Generate robots.txt file'),
                    'icon'  => 'process-icon-cogs',
                    'href'  => $this->context->link->getAdminLink('AdminMeta') . '&submitGenerateRobots',
                ],
            ],
        ];

        $this->fields_options['htaccess'] = [
            'title'   => $this->la('.htaccess file'),
            'icon'    => 'icon-cogs',
            'fields'  => [
                'htaccess' => [
                    'title'                     => $this->la('.htaccess'),
                    'type'                      => 'code',
                    'mode'                      => 'apache_conf',
                    'enableBasicAutocompletion' => true,
                    'enableSnippets'            => true,
                    'enableLiveAutocompletion'  => true,
                    'maxLines'                  => 400,
                    'value'                     => Tools::isSubmit('htaccess') ? Tools::getValue('htaccess') : @file_get_contents(_EPH_ROOT_DIR_ . '/.htaccess'),
                    'auto_value'                => false,
                ],
            ],
            'submit'  => ['title' => $this->la('Save')],
            'buttons' => [
                'generateHtaccess' => [
                    'class' => 'btn btn-default pull-left',
                    'title' => $this->la('Generate .htaccess file'),
                    'icon'  => 'process-icon-cogs',
                    'href'  => $this->context->link->getAdminLink('AdminMeta') . '&submitGenerateHtaccess',
                ],
            ],
        ];
        EmployeeConfiguration::updateValue('EXPERT_META_SCRIPT', $this->generateParaGridScript(true));
        $this->paragridScript = EmployeeConfiguration::get('EXPERT_META_SCRIPT');

        if (empty($this->paragridScript)) {
            EmployeeConfiguration::updateValue('EXPERT_META_SCRIPT', $this->generateParaGridScript(true));
            $this->paragridScript = EmployeeConfiguration::get('EXPERT_META_SCRIPT');
        }

       

        $this->extracss = $this->pushCSS([
           _EPH_ADMIN_THEME_DIR_.$this->bo_theme . '/css/metas.css',

        ]);

        $this->ajaxOptions = $this->generateImageConfigurator();

    }

    public function setAjaxMedia() {

        return $this->pushJS([
            _EPH_JS_DIR_ . 'jquery/plugins/jquery.tagify.js',
            'https://cdn.ephenyxapi.com/ace/ace.js',
        ]);
    }
	
	
    public function generateParaGridScript($regenerate = false) {

               
        $this->paramPageModel = [
            'type'       => '\'local\'',
            'rPP'        => 200,
            'rPPOptions' => [10, 20, 40, 50, 100, 200, 500],
        ];
		$this->paramToolbar = [
            'items' => [

                ['type' => '\'separator\''],

                [
                    'type'     => '\'button\'',
                    'icon'     => '\'ui-icon-disk\'',
                    'label'    => '\'' . $this->la('Ajouter une page meta') . '\'',
                    'cls'      => '\'ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only pull-right\'',
                     'listener' => 'function () {' . PHP_EOL . '
                           addAjaxObject("' . $this->controller_name.'");' . PHP_EOL . '
                          }' . PHP_EOL
                ],

            ],
        ];
		$this->paramTitle = '\'' . $this->la('Liste des Pages métas') . '\'';
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
                selected = selgrid' . $this->className . '.getSelection().length;
                var dataLenght = grid' . $this->className . '.option(\'dataModel.data\').length;
                return {
                    callback: function(){},
                    items: {
                        "add": {
                            name: \'' . $this->la('Ajouter une nouvelle page SEO') . ' \',
                            icon: "add",
                            callback: function(itemKey, opt, e) {
                                addAjaxObject("' . $this->controller_name.'");
                            }
                        },
                        "edit": {
                            name : \'' . $this->la('Modifier la page ') . '\'' . '+rowData.page,
                            icon: "edit",
                            callback: function(itemKey, opt, e) {
                                editAjaxObject("' .$this->controller_name.'", rowData.id_meta)
                            }
                        },


                        "sep1": "---------",
                        "delete": {
                            name: \'' . $this->la('Supprimer la page ') . '\'' . '+rowData.page,
                            icon: "delete",
                            visible: function(key, opt) {
                                if (selected == 1) {
                                    return true;
                                }
                            return false;
                            },
                            callback: function(itemKey, opt, e) {
                                 deleteObject("' . $this->controller_name . '", "' . $this->className . '", "Supprimer une Page Meta", "Etes vous sure de vouloir supprimer la page "+rowData.name+ " ?", "Oui", "Annuler",rowData.id_meta);
                            }
                        },


                    },
                };
            }',
            ]];

        return parent::generateParaGridScript();
    }

    public function getMetaRequest() {

        $metas = Db::getInstance()->executeS(
            (new DbQuery())
                ->select('a.`id_meta`, `page`, `title`, `url_rewrite`')
                ->from('meta', 'a')
                ->leftJoin('meta_lang', 'b', 'b.`id_meta` = a.`id_meta` AND b.`id_lang` = ' . $this->context->language->id)
                ->where('a.`configurable` = 1')
                ->groupBy('a.`id_meta`')
                ->orderBy('a.`id_meta` ASC')
        );
        $metaLink = $this->context->link->getAdminLink($this->controller_name);

        
        return $metas;
    }

    public function ajaxProcessgetMetaRequest() {

        die(Tools::jsonEncode($this->getMetaRequest()));

    }

    public function getMetaFields() {

        return [

            [
                'title'      => $this->la('ID'),
                'maxWidth'   => 70,
                'dataIndx'   => 'id_meta',
                'dataType'   => 'integer',
                'editable'   => false,
                'align'      => 'center',
            ],
            
            [
                'title'      => $this->la('Page'),
                'width'      => 200,
                'dataIndx'   => 'page',
                'cls'        => 'name-handle',
                'align'      => 'left',
                'editable'   => false,
                'dataType'   => 'string',
                'hiddenable' => 'no',
                'filter'     => [
                    'crules' => [['condition' => "begin"]],
                ],
            ],
            [
                'title'    => $this->la('Page title'),
                'width'    => 150,
                'dataIndx' => 'title',
                'align'    => 'left',
                'editable' => false,
                'dataType' => 'string',
            ],
            [
                'title'    => $this->la('Friendly URL'),
                'width'    => 200,
                'dataIndx' => 'url_rewrite',
                'align'    => 'center',
                'editable' => false,
                'dataType' => 'string',
            ],

        ];
    }

    public function ajaxProcessgetMetaFields() {

        die(Tools::jsonEncode($this->getMetaFields()));
    }

    
	
	public function generateImageConfigurator() {
		
		$tabs = [];
		$tabs['Configuration des Url'] = [
			'key'		=> 'generalParams',
			'content'	=> $this->generateOptions('general')
		];
		$tabs['URL du Front Office'] = [
			'key'		=> 'shopUrlParams',
			'content'	=> $this->generateOptions('shop_url')
		];
		$tabs['Format des URL'] = [
			'key'		=> 'routesParams',
			'content'	=> $this->generateOptions('routes')
		];
		$tabs['Robot d‘indexation'] = [
			'key'		=> 'fileRobots',
			'content'	=> $this->generateOptions('robots')
		];
		$tabs['Fichier .htaccess'] = [
			'key'		=> 'fileHtaccess',
			'content'	=> $this->generateOptions('htaccess')
		];
		
		
		return $tabs;
	}
	
	public function generateOptions($tab) {
		
		if (Configuration::get('EPH_REWRITING_SETTINGS')) {
            $this->addAllRouteFields();
        }
    	$fields_options =[
	    	$tab => $this->fields_options[$tab]
		];
      	if ($fields_options && is_array($fields_options)) {
			
            $helper = new HelperOptions();
            $this->setHelperDisplay($helper);
            $helper->toolbar_scroll = true;
            $helper->toolbar_btn = [
                'save' => [
                    'href' => '#',
                    'desc' => $this->la('Save'),
                ],
            ];
            $helper->id = $this->id;
            $helper->tpl_vars = $this->tpl_option_vars;
            $options = $helper->generateOptions($fields_options);

            return $options;
        }

        return '';
    }
	
	public function ajaxProcessUpdateAdminMetas() {

        $idShop = $this->context->company->id;

        $company= new Company($idShop);
        $company_url = $company->getUrls();

        $idShopUrl = $company_url[0]['id_company_url'];

        $companyUrl = new CompanyUrl($idShopUrl);

        foreach ($_POST as $key => $value) {

            if ($key == 'action' || $key == 'ajax' || $key == 'robots' || $key == 'htaccess') {

                continue;
            }

            if ($key == 'domain') {
                $companyUrl->domain = $value;
                Configuration::updateValue('EPH_SHOP_DOMAIN', $value);
            } else
            if ($key == 'domain_ssl') {
                $companyUrl->domain_ssl = $value;
                Configuration::updateValue('EPH_SHOP_DOMAIN_SSL', $value);
            } else 	if($key == 'agent_url') {
				$companyUrl->agent_url = $value;
				Configuration::updateValue('EPH_SHOP_AGENT_URL', $value);
			} else
            if ($key == 'uri') {
                $companyUrl->phisical_uri = $value;
            } else {
                Configuration::updateValue($key, $value);
            }

        }

        $companyUrl->update();

        $result = [
            "success" => true,
            "message" => "La mise à jour des paramètres SEO a été réalisé avec succès",
        ];

        die(Tools::jsonEncode($result));

    }

    
    /**
     * @param string $routeId
     * @param string $title
     *
     * @since 1.8.5.0
     */
    public function addFieldRoute($routeId, $title) {

        $keywords = [];

        foreach (Performer::getInstance()->default_routes[$routeId]['keywords'] as $keyword => $data) {
            $keywords[] = ((isset($data['param'])) ? '<span class="red">' . $keyword . '*</span>' : $keyword);
        }

        $this->fields_options['routes']['fields']['EPH_ROUTE_' . $routeId] = [
            'title'        => $title,
            'desc'         => sprintf($this->la('Keywords: %s'), implode(', ', $keywords)),
            'validation'   => 'isString',
            'type'         => 'text',
            'size'         => 70,
            'defaultValue' => Performer::getInstance()->default_routes[$routeId]['rule'],
        ];
    }
	
	

    /**
     * @return string
     *
     * @since 1.8.5.0
     */
    public function renderForm() {
		
		

		$obj = $this->loadObject();
		

        $files = Meta::getPages(true, ($this->object->page ? $this->object->page : false));

        $isIndex = false;

        if (is_object($this->object) && is_array($this->object->url_rewrite) && count($this->object->url_rewrite)) {

            foreach ($this->object->url_rewrite as $rewrite) {

                if ($isIndex != true) {
                    $isIndex = ($this->object->page == 'index' && empty($rewrite)) ? true : false;
                }

            }

        }

        $pages = [
            'common' => [
                'name'  => $this->la('Default pages'),
                'query' => [],
            ],
			 'admin' => [
                'name'  => $this->la('Admin pages'),
                'query' => [],
            ],
            'module' => [
                'name'  => $this->la('Modules pages'),
                'query' => [],
            ],
        ];

        foreach ($files as $name => $file) {
			if (strpos($file, 'admin') !== false) {
				$k = 'admin';	
			} else if (strpos($file, 'module-') !== false) {
				$k = 'module';	
			} else {
				$k = 'common';
			}
			
            $pages[$k]['query'][] = [
                'id'   => $file,
                'page' => $name,
            ];
        }

        $this->fields_form = [
            'legend' => [
                'title' => $this->la('Meta tags'),
                'icon'  => 'icon-tags',
            ],
            'id'     => 'metaForm',
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
                    'type'          => 'select',
                    'label'         => $this->la('Page'),
                    'name'          => 'page',

                    'options'       => [
                        'optiongroup' => [
                            'label' => 'name',
                            'query' => $pages,
                        ],
                        'options'     => [
                            'id'    => 'id',
                            'name'  => 'page',
                            'query' => 'query',
                        ],
                    ],
                    'hint'          => $this->la('Name of the related page.'),
                    'required'      => true,
                    'empty_message' => '<p>' . $this->la('There is no page available!') . '</p>',
                ],
                [
                    'type'  => 'text',
                    'label' => $this->la('Page title'),
                    'name'  => 'title',
                    'lang'  => true,
					'class'    => 'copy2friendlyUrl',
                    'hint'  => [
                        $this->la('Title of this page.'),
                        $this->la('Invalid characters:') . ' &lt;&gt;;=#{}',
                    ],
                ],
                [
                    'type'  => 'text',
                    'label' => $this->la('Meta description'),
                    'name'  => 'description',
                    'lang'  => true,
                    'hint'  => [
                        $this->la('A short description of your shop.'),
                        $this->la('Invalid characters:') . ' &lt;&gt;;=#{}',
                    ],
                ],
                [
                    'type'  => 'tags',
                    'label' => $this->la('Meta keywords'),
                    'name'  => 'keywords',
                    'lang'  => true,
                    'hint'  => [
                        $this->la('List of keywords for search engines.'),
                        $this->la('To add tags, click in the field, write something, and then press the "Enter" key.'),
                        $this->la('Invalid characters:') . ' &lt;&gt;;=#{}',
                    ],
                ],
                [
                    'type'     => 'text',
                    'label'    => $this->la('Rewritten URL'),
                    'name'     => 'url_rewrite',
                    'lang'     => true,
                    'required' => true,
                    'disabled' => (bool) $isIndex,
                    'hint'     => [
                        $this->la('For instance, "contacts" for http://example.com/shop/contacts to redirect to http://example.com/shop/contact-form.php'),
                        $this->la('Only letters and hyphens are allowed.'),
                    ],
                ],
            ],
            'submit' => [
                'title' => $this->la('Save'),
            ],
        ];
		
		if($this->object->id > 0) {
			$this->form_action = 'updataMeta';
             $this->fields_value['action'] = 'updataMeta';
			$this->editObject = 'Edition d‘une page META';
		} else {
			$this->form_action = 'addAdminMeta';
            $this->fields_value['action'] = 'addAdminMeta';
			$this->editObject = 'Ajouter une nouvelle page META';
		}
		$this->form_ajax =1;
		
		

        return parent::renderForm();
    }

    
	public function ajaxProcessAddAdminMeta() {
		
		$meta = new Meta();
		
		foreach ($_POST as $key => $value) {
			if (property_exists($meta, $key) && $key != 'id_meta') {
             	$meta->{$key}  = $value;
            }
        }

        $classVars = get_class_vars(get_class($meta));
        $fields = [];

        if (isset($classVars['definition']['fields'])) {
			$fields = $classVars['definition']['fields'];
        }

        foreach ($fields as $field => $params) {

        	if (array_key_exists('lang', $params) && $params['lang']) {
				foreach (Language::getIDs(false) as $idLang) {
					if (Tools::isSubmit($field . '_' . (int) $idLang)) {
						if (!isset($meta->{$field}) || !is_array($meta->{$field})) {
                        	$meta->{$field} = [];
                        }
                        $meta->{$field}[(int) $idLang] = Tools::getValue($field . '_' . (int) $idLang);
                    }
                }
            }
        }
		foreach (Language::getIDs(false) as $idLang) {

            if (isset($_POST['keywords_' . $idLang])) {
                $_POST['keywords_' . $idLang] = $this->_cleanMetaKeywords(mb_strtolower($_POST['keywords_' . $idLang]));
                // preg_replace('/ *,? +,* /', ',', strtolower($_POST['meta_keywords_'.$id_lang]));
                $meta->keywords[$idLang] = $_POST['keywords_' . $idLang];
            }

        }
		
		if (strpos($meta->page, 'admin') !== false) {
			$meta->controller = 'admin';	
		} else if (strpos($meta->page, 'module-') !== false) {
			$meta->controller = 'module';	
		} else {
			$meta->controller = 'front';
		}
		
        $result = $meta->add();
		$return = [
        	'success' => true,
            'message' => $this->la('Le paramètre SEO a été ajouté avec succès'),
        ];
		
		die(Tools::jsonEncode($return));
	}
	
	
	public function ajaxProcessUpdataMeta() {
		
		$idMeta = Tools::getValue('id_meta');
		$meta = new Meta($idMeta);
		
		foreach ($_POST as $key => $value) {
			if (property_exists($meta, $key) && $key != 'id_meta') {
             	$meta->{$key}  = $value;
            }
        }

        $classVars = get_class_vars(get_class($meta));
        $fields = [];

        if (isset($classVars['definition']['fields'])) {
			$fields = $classVars['definition']['fields'];
        }

        foreach ($fields as $field => $params) {

        	if (array_key_exists('lang', $params) && $params['lang']) {
				foreach (Language::getIDs(false) as $idLang) {
					if (Tools::isSubmit($field . '_' . (int) $idLang)) {
						if (!isset($meta->{$field}) || !is_array($meta->{$field})) {
                        	$meta->{$field} = [];
                        }
                        $meta->{$field}[(int) $idLang] = Tools::getValue($field . '_' . (int) $idLang);
                    }
                }
            }
        }
		foreach (Language::getIDs(false) as $idLang) {

            if (isset($_POST['keywords_' . $idLang])) {
                $_POST['keywords_' . $idLang] = $this->_cleanMetaKeywords(mb_strtolower($_POST['keywords_' . $idLang]));
                // preg_replace('/ *,? +,* /', ',', strtolower($_POST['meta_keywords_'.$id_lang]));
                $meta->keywords[$idLang] = $_POST['keywords_' . $idLang];
            }

        }
		
		if (strpos($meta->page, 'admin') !== false) {
			$meta->controller = 'admin';	
		} else if (strpos($meta->page, 'module-') !== false) {
			$meta->controller = 'module';	
		} else {
			$meta->controller = 'front';
		}
		
        $result = $meta->update();
		$return = [
        	'success' => true,
            'message' => $this->la('Les paramètre SEO ont mis à jour avec succès'),
        ];
		
		die(Tools::jsonEncode($return));
	}
	
	protected function _cleanMetaKeywords($keywords) {

        if (!empty($keywords) && $keywords != '') {
            $out = [];
            $words = explode(',', $keywords);

            foreach ($words as $wordItem) {
                $wordItem = trim($wordItem);

                if (!empty($wordItem) && $wordItem != '') {
                    $out[] = $wordItem;
                }

            }

            return ((count($out) > 0) ? implode(',', $out) : '');
        } else {
            return '';
        }

    }

    public function ajaxProcessgenerateHtaccessFile() {
		
		Tools::generateHtaccess();
		
		$return = [
        	'success' => true,
           	'message' => $this->la('Le fichier htaccess a été régénéré avec succès'),
			'htaccess' =>  @file_get_contents(_EPH_ROOT_DIR_ . '/.htaccess')
        ];
		
		die(Tools::jsonEncode($return));
	}	
	
    public function ajaxProcessGenerateRobotsFile() {
		
		
        if (!$writeFd = @fopen($this->rb_file, 'w')) {
            $this->errors[] = sprintf(Tools::displayError('Cannot write into file: %s. Please check write permissions.'), $this->rb_file);
        } else {
			
            Hook::exec(
                'actionAdminMetaBeforeWriteRobotsFile',
                [
                    'rb_data' => &$this->rb_data,
                ]
            );
			
            // PS Comments
            fwrite($writeFd, "# robots.txt automatically generated by ephenyx e-commerce open-source solution\n");
            fwrite($writeFd, "# http://www.ephenyx.com - http://www.ephenyx.com/forums\n");
            fwrite($writeFd, "# This file is to prevent the crawling and indexing of certain parts\n");
            fwrite($writeFd, "# of your site by web crawlers and spiders run by sites like Yahoo!\n");
            fwrite($writeFd, "# and Google. By telling these \"robots\" where not to go on your site,\n");
            fwrite($writeFd, "# you save bandwidth and server resources.\n");
            fwrite($writeFd, "# For more information about the robots.txt standard, see:\n");
            fwrite($writeFd, "# http://www.robotstxt.org/robotstxt.html\n");

            // User-Agent
            fwrite($writeFd, "User-agent: *\n");

            // Allow Directives

            if (count($this->rb_data['Allow'])) {
                fwrite($writeFd, "# Allow Directives\n");

                foreach ($this->rb_data['Allow'] as $allow) {
                    fwrite($writeFd, 'Allow: ' . $allow . "\n");
                }

            }

            // Private pages

           

            // Directories

            if (count($this->rb_data['Directories'])) {
                fwrite($writeFd, "# Directories\n");

                foreach ($this->rb_data['Directories'] as $dir) {
                    fwrite($writeFd, 'Disallow: */' . $dir . "\n");
                }

            }

            // Files

            if (count($this->rb_data['Files'])) {
                $activeLanguageCount = count(Language::getIDs());
                fwrite($writeFd, "# Files\n");

                foreach ($this->rb_data['Files'] as $isoCode => $files) {

                    foreach ($files as $file) {

                        if ($activeLanguageCount > 1) {
                            // Friendly URLs have language ISO code when multiple languages are active
                            fwrite($writeFd, 'Disallow: /' . $isoCode . '/' . $file . "\n");
                        } else if ($activeLanguageCount == 1) {
                            // Friendly URL does not have language ISO when only one language is active
                            fwrite($writeFd, 'Disallow: /' . $file . "\n");
                        } else {
                            fwrite($writeFd, 'Disallow: /' . $file . "\n");
                        }

                    }

                }

            }

            // Sitemap

            if (file_exists($this->sm_file) && filesize($this->sm_file)) {
                fwrite($writeFd, "# Sitemap\n");
                $sitemapFilename = basename($this->sm_file);
                fwrite($writeFd, 'Sitemap: ' . (Configuration::get('EPH_SSL_ENABLED') ? 'https://' : 'http://') . $_SERVER['SERVER_NAME'] . __EPH_BASE_URI__ . $sitemapFilename . "\n");
            }
			
            Hook::exec(
                'actionAdminMetaAfterWriteRobotsFile',
                [
                    'rb_data'  => $this->rb_data,
                    'write_fd' => &$writeFd,
                ]
            );
			

            fclose($writeFd);
			
            $return = [
        		'success' => true,
            	'message' => $this->la('Le fichier robot.txt a été régénéré avec succès'),
				'robots' => @file_get_contents(_SHOP_ROOT_DIR_ . '/robots.txt')
        	];
		
			die(Tools::jsonEncode($return));
        }

    }

    
    public function checkAndUpdateRoute($route) {

        $defaultRoutes = Performer::getInstance()->default_routes;

        if (!isset($defaultRoutes[$route])) {
            return;
        }

        $multiLang = !Tools::getValue('EPH_ROUTE_' . $route);

        $errors = [];
        $rule = Tools::getValue('EPH_ROUTE_' . $route);

        foreach (Language::getIDs(false) as $idLang) {

            if ($multiLang) {
                $rule = Tools::getValue('EPH_ROUTE_' . $route . '_' . $idLang);
            }

            if (!Performer::getInstance()->validateRoute($route, $rule, $errors)) {

                foreach ($errors as $error) {
                    $this->errors[] = sprintf('Keyword "{%1$s}" required for route "%2$s" (rule: "%3$s")', $error, $route, htmlspecialchars($rule));
                }

            } else if (!$this->checkRedundantRewriteKeywords($rule)) {
                $this->errors[] = sprintf('Rule "%1$s" is invalid. It has duplicate keywords.', htmlspecialchars($rule));
            } else {

                if (preg_match('/}[a-zA-Z0-9-_]*{/', $rule)) {
                    // Two regexes can't be tied together with delimiters that can also occur in the regex itself
                    // The only exception is the ID keyword

                    if (!preg_match('/:\/}[a-zA-Z0-9-_]*{/', $rule) && !preg_match('/}[a-zA-Z0-9-_]*{\/:/', $rule) && !preg_match('#\{([^{}]*:)?id(:[^{}]*)?\}#', $rule)) {
                        $this->errors[] = sprintf('Route "%1$s" with rule: "%2$s" needs a correct delimiter', $route, htmlspecialchars($rule));
                    } else {
                        Configuration::updateValue('EPH_ROUTE_' . $route, [(int) $idLang => $rule]);
                    }

                } else {
                    Configuration::updateValue('EPH_ROUTE_' . $route, [(int) $idLang => $rule]);
                }

            }

        }

    }

    /**
     * Called when EPH_REWRITING_SETTINGS option is saved
     *
     * @since 1.8.5.0
     */
    public function updateOptionPsRewritingSettings() {

        Configuration::updateValue('EPH_REWRITING_SETTINGS', (int) Tools::getValue('EPH_REWRITING_SETTINGS'));

        $this->updateOptionDomain(Tools::getValue('domain'));
        $this->updateOptionDomainSsl(Tools::getValue('domain_ssl'));

        if (Tools::getIsset('uri')) {
            $this->updateOptionUri(Tools::getValue('uri'));
        }

        if (Tools::generateHtaccess($this->ht_file, null, null, '', Tools::getValue('EPH_HTACCESS_DISABLE_MULTIVIEWS'), false, Tools::getValue('EPH_HTACCESS_DISABLE_MODSEC'))) {
            Tools::enableCache();
            Tools::clearCache($this->context->smarty);
            Tools::restoreCacheSettings();
        } else {
            Configuration::updateValue('EPH_REWRITING_SETTINGS', 0);
            // Message copied/pasted from the information tip
            $message = $this->la('Before being able to use this tool, you need to:');
            $message .= '<br />- ' . $this->la('Create a blank .htaccess in your root directory.');
            $message .= '<br />- ' . $this->la('Give it write permissions (CHMOD 666 on Unix system).');
            $this->errors[] = $message;
        }

    }

    /**
     * @since 1.8.5.0
     */
    public function updateOptionPsRouteProductRule() {

        $this->checkAndUpdateRoute('education_rule');
    }

    /**
     * @since 1.8.5.0
     */
    public function updateOptionPsRouteEducationTypeRule() {

        $this->checkAndUpdateRoute('education_type_rule');
    }

    public function updateOptionPsRouteEducationRule() {

        $this->checkAndUpdateRoute('educatione_rule');
    }

    public function updateOptionPsRouteCategoryRule() {

        $this->checkAndUpdateRoute('category_rule');
    }

    /**
     * @since 1.8.5.0
     */
    public function updateOptionPsRouteLayeredRule() {

        $this->checkAndUpdateRoute('layered_rule');
    }

    /**
     * @since 1.8.5.0
     */
    public function updateOptionPsRouteSupplierRule() {

        $this->checkAndUpdateRoute('supplier_rule');
    }

    /**
     * @since 1.8.5.0
     */
    public function updateOptionPsRouteManufacturerRule() {

        $this->checkAndUpdateRoute('manufacturer_rule');
    }

    /**
     * @since 1.8.5.0
     */
    public function updateOptionPsRouteCmsRule() {

        $this->checkAndUpdateRoute('cms_rule');
    }

    /**
     * @since 1.8.5.0
     */
    public function updateOptionPsRouteCmsCategoryRule() {

        $this->checkAndUpdateRoute('cms_category_rule');
    }
	
	public function updateOptionPsRouteFormulaireRule() {

        $this->checkAndUpdateRoute('pfg_rule');
    }
	
	public function updateOptionPsRouteSponsorRule() {

        $this->checkAndUpdateRoute('sponsor_rule');
    }

    /**
     * Update shop domain (for mono shop)
     *
     * @param string $value
     *
     * @throws PhenyxShopException
     *
     * @since 1.8.5.0
     */
    public function updateOptionDomain($value) {

        if (Validate::isCleanHtml($value)) {
                $this->url->domain = $value;
                $this->url->update();
                Configuration::updateGlobalValue('EPH_SHOP_DOMAIN', $value);
            } else {
                $this->errors[] = Tools::displayError('This domain is not valid.');
            }

    }

    /**
     * Update shop SSL domain (for mono shop)
     *
     * @param string $value
     *
     * @throws PhenyxShopException
     *
     * @since 1.8.5.0
     */
     public function updateOptionDomainSsl($value) {

        if (Validate::isCleanHtml($value)) {
                $this->url->domain_ssl = $value;
                $this->url->update();
                Configuration::updateGlobalValue('EPH_SHOP_DOMAIN_SSL', $value);
            } else {
                $this->errors[] = Tools::displayError('The SSL domain is not valid.');
            }
    }

    /**
     * Update shop physical uri for mono shop)
     *
     * @param string $value
     *
     * @throws PhenyxShopException
     *
     * @since 1.8.5.0
     */
    public function updateOptionUri($value) {

        $this->url->physical_uri = $value;
            $this->url->update();

    }

    /**
     * Save robots.txt file
     *
     * @since 1.8.5.0
     */
    public function saveRobotsFile() {

        @file_put_contents(_EPH_ROOT_DIR_ . '/robots.txt', Tools::getValue('robots'));
    }

    /**
     * Save .htaccess file
     *
     * @since 1.8.5.0
     */
    public function saveHtaccessFile() {

        @file_put_contents(_EPH_ROOT_DIR_ . '/.htaccess', Tools::getValue('htaccess'));
    }

    /**
     * Function used to render the options for this controller
     *
     * @return string
     *
     * @since 1.8.5.0
     */
    

    /**
     * Add all custom route fields to the options form
     *
     * @since 1.8.5.0
     */
    public function addAllRouteFields() {

        $this->addFieldRoute('education_rule', $this->la('Route to products'));

        $this->addFieldRoute('layered_rule', $this->la('Route to category which has the "selected_filter" attribute for the "Layered Navigation" (blocklayered) module'));

        $this->addFieldRoute('education_type_rule', $this->la('Route pour les familles de formation'));
        $this->addFieldRoute('education_rule', $this->la('Route pour les formations'));
        $this->addFieldRoute('cms_rule', $this->la('Route to CMS page'));
        $this->addFieldRoute('cms_category_rule', $this->la('Route to CMS category'));
		$this->addFieldRoute('pfg_rule', $this->la('Route des formulaires'));
		$this->addFieldRoute('sponsor_rule', $this->la('Route des sponsors'));
    }

    public function checkConfiguration($file) {

        if (file_exists($file)) {
            return is_writable($file);
        }

        return is_writable(dirname($file));
    }

    public function getRobotsContent() {

        $tab = [];

        // Special allow directives
        $tab['Allow'] = ['*/modules/*.css', '*/modules/*.js'];

        // Directories
        $tab['Directories'] = ['app/classes/', 'app/', 'download/', 'mails/', 'modules/', 'translations/', 'tools/'];

        // Files
        $disallowControllers = [
            'footer', 'get-file', 'header', 'identity', 'images.inc', 'init', 'my-account', 'contract', 'password',
            'pdf-invoice', 'statistics', 'my-student', 'register-student', 'agent-dashboard', 'evaluation',
        ];

        // Rewrite files
        $tab['Files'] = [];

        if (Configuration::get('EPH_REWRITING_SETTINGS')) {
            $sql = 'SELECT ml.url_rewrite, l.iso_code
                    FROM ' . _DB_PREFIX_ . 'meta m
                    INNER JOIN ' . _DB_PREFIX_ . 'meta_lang ml ON ml.id_meta = m.id_meta
                    INNER JOIN ' . _DB_PREFIX_ . 'lang l ON l.id_lang = ml.id_lang
                    WHERE l.active = 1 AND m.page IN (\'' . implode('\', \'', $disallowControllers) . '\')';

            if ($results = Db::getInstance(_EPH_USE_SQL_SLAVE_)->executeS($sql)) {

                foreach ($results as $row) {
                    $tab['Files'][$row['iso_code']][] = $row['url_rewrite'];
                }

            }

        }


        return $tab;
    }

    protected function checkRedundantRewriteKeywords($rule) {

        preg_match_all('#\{([^{}]*:)?([a-zA-Z]+)(:[^{}]*)?\}#', $rule, $matches);

        if (isset($matches[2]) && is_array($matches[2])) {

            foreach (array_count_values($matches[2]) as $val => $c) {

                if ($c > 1) {
                    return false;
                }

            }

        }

        return true;
    }

    public function ajaxProcessDeleteMeta() {

        $idMeta = Tools::getValue('idMeta');
        $meta = new Meta($idMeta);
        $meta->delete();

        $result = [
            'success' => true,
            'message' => 'La page a été supprimé avec succès.',
        ];
        die(Tools::jsonEncode($result));

    }

    public function ajaxProcessUpdateGeneralFields() {

        foreach ($_POST as $key => $value) {

            if ($key == 'action' || $key == 'ajax') {
                continue;
            }

            Configuration::updateValue($key, $value);

        }

        $result = [
            'success' => true,
            'message' => 'Les règlages généreaux ont été mis à jour avec succès.',
        ];
        die(Tools::jsonEncode($result));

    }

    public function ajaxProcessUpdateLinkRoutes() {

        foreach ($_POST as $key => $value) {

            if ($key == 'action' || $key == 'ajax') {
                continue;
            }

            Configuration::updateValue($key, $value);

        }

        $result = [
            'success' => true,
            'message' => 'Les règles de réecriture des routes ont été mis à jour avec succès.',
        ];
        die(Tools::jsonEncode($result));
    }

}
