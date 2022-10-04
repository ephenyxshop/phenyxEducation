<?php
use MatthiasMullie\Minify;
/**
 * Class AdminTopMenuControllerCore
 *
 * @since 1.9.1.0
 */
class AdminTopMenuControllerCore extends AdminController {

    public $php_self = 'admintopmenu';
	// @codingStandardsIgnoreEnd


    private $gradient_separator = '-';
	
	public $fieldForm = [];
	
	public $fonts_files;
	
	public static $shortname = 'xprt';

    
    private $allowFileExtension = [
        'gif',
        'jpg',
        'jpeg',
        'png',
    ];

    public $link_targets = [];

    public $_fieldsOptions;

    public $topMenu;

    public $rebuildable_type = [
        3,
        4,
        5,
        10,
    ];
	
	

    
    /**
     * AdminTopMenuControllerCore constructor.
     *
     * @since 1.9.1.0
     */
    public function __construct() {

        $this->bootstrap = true;
        $this->table = 'topmenu';
        $this->className = 'TopMenu';
		$this->publicName = $this->la('Menu Front Office');
        $this->lang = true;
        $this->context = Context::getContext();

        parent::__construct();
		
		$this->updateFontFamily();
		
		$this->ajaxOptions = $this->generateMenuConfigurator();

        $this->link_targets = [
            0         => $this->la('No target. W3C compliant.'),
            '_self'   => $this->la('Open document in the same frame (_self)'),
            '_blank'  => $this->la('Open document in a new window (_blank)'),
            '_top'    => $this->la('Open document in the same window (_top)'),
            '_parent' => $this->la('Open document in the parent frame (_parent)'),
        ];
		
		$this->extracss = $this->pushCSS([
            _EPH_ADMIN_THEME_DIR_.  $this->bo_theme . '/css/topmenu.css',
             _EPH_ADMIN_THEME_DIR_.  $this->bo_theme . '/css/popover.css',
             _EPH_ADMIN_THEME_DIR_.  $this->bo_theme . '/css/custom-font.css',
             _EPH_JS_DIR_.'colorpicker/jquery.colorpicker.css',	
             _EPH_ADMIN_THEME_DIR_.  $this->bo_theme . '/css/codemirror/codemirror.css',
             _EPH_ADMIN_THEME_DIR_.  $this->bo_theme . '/css/codemirror/default.css',
			 _EPH_JS_DIR_.'ace/aceinput.css',
			  _EPH_ADMIN_THEME_DIR_.  $this->bo_theme . '/css/themes.css',
        ]);
		
		$this->extra_vars = [
            'menu_img_dir'              => _EPH_ADMIN_THEME_DIR_.  $this->bo_theme . '/img/topmenu/',
            'bo_imgdir'                 => _EPH_ADMIN_THEME_DIR_.  $this->bo_theme . '/img/',
			'languages'                 => Language::getLanguages(false),
        ];
		$this->fonts_files = _EPH_ROOT_DIR_.'/content/themes/theme/phenyx_fonts.json';
		
		$this->fieldForm = [
            'input' => [
               
                [
                    'name' => 'menu_global_actif',
                ],
                [
                    'name' => 'menu_global_width',
                ],
                [
                    'name' => 'menu_global_height',
                ],
                [
                    'name' => 'menu_cont_padding',
					'type' => '4size'
                ],
                [
                    'name' => 'menu_cont_margin',
					'type' => '4size'
                ],
                [
                    'name' => 'menu_global_bg_color',
					'type'        => 'gradient',
                ],
                [
                    'name' => 'menu_global_border_color',
					'type'        => 'color',
                ],
                [
                    'name' => 'menu_cont_border_size',
                ],
                [
                    'name' => 'menu_center_tab',
                ],
                [
                    'name' => 'menu_width',
                ],
                [
                    'name' => 'menu_padding',
					'type' => '4size'
                ],
                [
                    'name' => 'menu_margin',
					'type' => '4size'
                ],
                [
                    'name' => 'menufontsize',
					'type' => 'select_font_size'
                ],
                [
                    'name' => 'menu_font_bold',
                ],
                [
                    'name' => 'menu_font_underline',
                ],
                [
                    'name' => 'menu_font_underline_hover',
                ],
                [
                    'name' => 'menu_font_transform',
                ],
                [
                    'name' => 'menufont',
                    'type' => 'googlefont',
                ],
                [
                    'name' => 'menu_link_color',
					'type'        => 'color',
                ],
                [
                    'name' => 'menu_link_color_hover',
					'type'        => 'color',
                ],
                [
                    'name' => 'menu_bck_color',
					'type'        => 'color',
                ],
                [
                    'name' => 'menu_bck_color_hover',
					'type'        => 'color',
                ],
				[
                    'name' => 'menu_border_color',
                ],
				[
                    'name' => 'menu_border_size',
					'type' => '4size'
                ],
                [
                    'name' => 'sub_menu_width',
                ],
                [
                    'name' => 'sub_menu_height',
                ],
                [
                    'name' => 'sub_menu_open_method',
                ],
                [
                    'name' => 'sub_menu_position',
                ],
                [
                    'name' => 'sub_menu_bgcolor',
					'type'        => 'color',
                ],
				[
                    'name' => 'sub_menu_bgcolor_hover',
					'type'        => 'color',
                ],
                
                [
                    'name' => 'sub_menu_bck_color',
					'type'        => 'color',
                ],
				[
                    'name' => 'sub_menu_bck_color_hover',
					'type'        => 'color',
                ],
                [
                    'name' => 'sub_menu_border_color',
                ],
                [
                    'name' => 'sub_menu_border_size',
					'type' => '4size'
                ],
				[
					'name' => 'sub_menu_box_shadow'
				],
				[
					'name' => 'sub_menu_box_shadow_color'
				],
				[
					'name' => 'sub_menu_box_shadow_opacity'
				],
				[
					'name' => 'sub_menu_open_delay'
				],
				[
					'name' => 'sub_menu_fade_speed'
				],
				[
					'name' => 'column_wrap_padding'
				],
				[
					'name' => 'column_padding',
					'type' => '4size'
				],
				[
					'name' => 'column_margin',
					'type' => '4size'
				],
				[
					'name' => 'column_title_padding',
					'type' => '4size'
				],
				[
					'name' => 'column_title_margin',
					'type' => '4size'
				],
				[
					'name' => 'column_font_size',
					'type' => 'select_font_size'
				],
				[
					'name' => 'column_font_bold'
				],
				[
					'name' => 'column_font_underline'
				],
				[
					'name' => 'column_font_underline_hover'
				],
				[
					'name' => 'column_font_transform'
				],
				[
					'name' => 'columnfont',
					'type' => 'googlefont',
				],
				[
					'name' => 'column_link_color',
					'type'        => 'color',
				],
				[
					'name' => 'column_link_color_hover',
					'type'        => 'color',
				],

                [
                    'name' => 'menu_orientation',
                ],
                [
                    'name' => 'sub_menu_bck',
					'type'        => 'color',
                ],
                [
                    'name' => 'sub_menu_bck_hover',
					'type'        => 'color',
                ],
                [
                    'name' => 'menu_text_capitalize',
                ],
				[
					'name' => 'menu_custom_css'
				],				
				[
					'name' => 'menu_responsive_active'
				],				
				[
					'name' => 'screen_responsive_size'
				],
				[
					'name' => 'menu_mobile_button_height'
				],
				[
					'name' => 'menu_mobile_button_f_size'
				],
				[
					'name' => 'menu_mobile_button_libelle'
				],
				[
					'name' => 'menu_mobile_close_icon',
					'type' => 'img_base64'
				],
				[
					'name' => 'menu_mobile_open_icon',
					'type' => 'img_base64'
				],
				[
					'name' => 'menu_mobile_user_img',
					'type' => 'img_base64'
				],
				[
					'name' => 'menu_mobile_padding',
					'type' => '4size'
				],
				[
					'name' => 'menu_mobile_margin',
					'type' => '4size'
				],
				[
					'name' => 'menu_mobile_font_size',
					'type' => 'select_font_size'
				],
				[
					'name' => 'menu_mobile_font_bold'
				],
				[
					'name' => 'menu_mobile_font_transform'
				],
				[
					'name' => 'menu_mobile_font',
                    'type' => 'googlefont',
				],
				[
					'name' => 'menu_mobile_link_color'
				],
				[
					'name' => 'menu_mobile_link_color_hover'
				],
				[
					'name' => 'menu_mobile_bck_color',
					'type'        => 'gradient',
				],
				[
					'name' => 'menu_mobile_bck_color_hover'
				],
				[
					'name' => 'menu_mobile_border_color'
				],
				[
					'name' => 'menu_mobile_border_size',
				],
                [
					'name' => 'sub_menu_mobile_bgcolor',
				],
				[
					'name' => 'sub_menu_mobile_padding',
					'type' => '4size'
				],
				[
					'name' => 'sub_menu_mobile_border_color',
				],
				[
					'name' => 'sub_menu_mobile_border_size',
					'type' => '4size'
				],
				[
					'name' => 'sub_menu_mobile_open_icon',
					'type' => 'img_base64'
				],
				[
					'name' => 'sub_menu_mobile_close_icon',
					'type' => 'img_base64'
				],
				[
					'name' => 'menu_mobile_column_wrap_padding',
					'type' => '4size'
				],
				[
					'name' => 'menu_mobile_column_wrap_margin',
					'type' => '4size'
				],
				[
					'name' => 'menu_mobile_column_wrap_border_color',
				],
				[
					'name' => 'menu_mobile_column_wrap_border_size',
					'type' => '4size'
				],
				[
					'name' => 'menu_mobile_column_padding',
					'type' => '4size'
				],
				[
					'name' => 'menu_mobile_column_margin',
					'type' => '4size'
				],
				[
					'name' => 'menu_mobile_column_title_padding',
					'type' => '4size'
				],
				[
					'name' => 'menu_mobile_column_title_margin',
					'type' => '4size'
				],
				[

					'name' => 'menu_mobile_column_font_size',
					'type' => 'select_font_size'
				],
				[
					'name' => 'menu_mobile_column_font_bold',
				],
				[
					'name' => 'menu_mobile_column_font_transform',
				],
				[
					'name' => 'menu_mobile_column_font',
					'type' => 'googlefont',
				],
				[
					'name' => 'menu_mobile_column_link_color',
				],
				[
					'name' => 'menu_text_shadow',
					'type' => 'text_shadow',
				],
				
               
            ],

        ];

        Configuration::updateValue('EPH_EXPERT_MENU_FIELDS', Tools::jsonEncode($this->fieldForm));

    }
	
	public function generateMenuConfigurator() {

        $tabs = [];

        $tabs['Menus'] = [
            'key'     => 'menu',
            'content' => $this->renderMenus(),
        ];

        $tabs['Réglage Du Menu'] = [
            'key'     => 'desktopParam',
            'content' => $this->generateMenuParams(),
        ];
        $tabs['Réglage Mobile'] = [
            'key'     => 'mobileParam',
            'content' => $this->generateMobileMenuParams(),
        ];
		
       

        return $tabs;

    }

	public function setAjaxMedia() {
		
		return $this->pushJS([
			_EPH_JS_DIR_.'tinymce/tinymce.min.js',
			_EPH_JS_DIR_.'tinymce.inc.js',
			_EPH_JS_DIR_.'topmenu.js',
            _EPH_JS_DIR_.'popover.js',
            _EPH_JS_DIR_.'colorpicker/jquery.colorpicker.js',
			_EPH_JS_DIR_.'colorpicker/i18n/jquery.ui.colorpicker-fr.js',
			_EPH_JS_DIR_.'colorpicker/swatches/jquery.ui.colorpicker-pantone.js',			
			_EPH_JS_DIR_.'colorpicker/swatches/jquery.ui.colorpicker-crayola.js',			
			_EPH_JS_DIR_.'colorpicker/swatches/jquery.ui.colorpicker-ral-classic.js',			
			_EPH_JS_DIR_.'colorpicker/swatches/jquery.ui.colorpicker-x11.js',			
			_EPH_JS_DIR_.'colorpicker/swatches/jquery.ui.colorpicker-copic.js',			
			_EPH_JS_DIR_.'colorpicker/swatches/jquery.ui.colorpicker-prismacolor.js',
			_EPH_JS_DIR_.'colorpicker/swatches/jquery.ui.colorpicker-isccnbs.js',
			_EPH_JS_DIR_.'colorpicker/swatches/jquery.ui.colorpicker-din6164.js',			
			_EPH_JS_DIR_.'colorpicker/parts/jquery.ui.colorpicker-rgbslider.js',
			_EPH_JS_DIR_.'colorpicker/parts/jquery.ui.colorpicker-memory.js',
			_EPH_JS_DIR_.'colorpicker/parts/jquery.ui.colorpicker-swatchesswitcher.js',			
			_EPH_JS_DIR_.'colorpicker/parsers/jquery.ui.colorpicker-cmyk-parser.js',
			_EPH_JS_DIR_.'colorpicker/parsers/jquery.ui.colorpicker-cmyk-percentage-parser.js',
            _EPH_JS_DIR_.'codemirror/codemirror.js',
            _EPH_JS_DIR_.'codemirror/css.js',
            _EPH_JS_DIR_.'jquery.tipTip.js',
			_EPH_JS_DIR_.'ace/ace.js',
			_EPH_JS_DIR_.'ace/ext-language_tools.js',
			_EPH_JS_DIR_.'ace/ext-language_tools.js',
			_EPH_JS_DIR_.'codemirror/css.js',
		]);
	}
	
	public function ajaxProcessOpenTargetController() {

		$targetController = $this->targetController;
		
		
		$this->setAjaxMedia();

		$data = $this->createTemplate($this->table.'.tpl');
		$displayMenuFormVars = $this->displayMenuForm();
		$this->_fieldsOptions = $this->_fieldsOptions;
		$this->link_targets = $this->link_targets;
		
		foreach ($displayMenuFormVars as $key => $displayMenuFormVar) {
            $data->assign($key, $displayMenuFormVar);
        }

        $displayConfig = $this->displayConfig();

		
		$extracss = $this->pushCSS([
			 _EPH_ADMIN_THEME_DIR_.  $this->bo_theme . '/css/topmenu.css',
             _EPH_ADMIN_THEME_DIR_.  $this->bo_theme . '/css/popover.css',
             _EPH_ADMIN_THEME_DIR_.  $this->bo_theme . '/css/custom-font.css',
             _EPH_JS_DIR_.'colorpicker/jquery.colorpicker.css',	
             _EPH_ADMIN_THEME_DIR_.  $this->bo_theme . '/css/codemirror/codemirror.css',
             _EPH_ADMIN_THEME_DIR_.  $this->bo_theme . '/css/codemirror/default.css',
			 _EPH_JS_DIR_.'ace/aceinput.css'
		
		]);

		
		

		$data->assign([
			'menu_img_dir'              => _EPH_ADMIN_THEME_DIR_.  $this->bo_theme . '/img/topmenu/',
            'bo_imgdir'                 => _EPH_ADMIN_THEME_DIR_.  $this->bo_theme . '/img/',
            'fieldsOptions'             => $displayConfig,
            'languages'                 => Language::getLanguages(false),
            'EPH_USE_PHENYXMENU'        => Configuration::get('EPH_USE_PHENYXMENU'),
			'controller'         => $this->controller_name,
			'link'           => $this->context->link,
			'tabs'               => $this->generateMenuConfigurator(),
			'extraJs'        => $extraJs,
			'extracss'       => $extracss,
			'extraJs'        => $this->push_js_files,
		]);

		$li = '<li id="uper'.$targetController.'" data-controller="AdminDashboard" data-self="'.$this->link_rewrite.'" data-name="'.$this->page_title.'"><a href="#content'.$targetController.'">'.$this->publicName.'</a><button type="button" class="close tabdetail" data-id="uper'.$targetController.'"><i class="icon icon-times-circle"></i></button></li>';
		$html = '<div id="content'.$targetController.'" class="panel col-lg-12" style="display; flow-root;">' . $data->fetch() . '</div>';

		$result = [
			'li'   => $li,

			'html' => $html,
		];

		die(Tools::jsonEncode($result));
	}
	
	public function renderMenus() {
		
		$data = $this->createTemplate('controllers/top_menu/topmenu_render.tpl');
		
        $displayMenuFormVars = $this->displayMenuForm();
		
		foreach ($displayMenuFormVars as $key => $displayMenuFormVar) {
            $data->assign($key, $displayMenuFormVar);
        }
        		
		$data->assign([
			'fieldsOptions'             => $this->displayConfig(),
            'languages'                 => Language::getLanguages(false),
			'controller'         => $this->controller_name,
			'link'           => $this->context->link,
		]);

        return $data->fetch();
	}
	
	public function generateMenuParams() {

        $forms = [];

        $fields_forms['menu_barre'] = [
            'legend'  => [
                'title' => $this->la('Réglages de la barre de menu'),
            ],

            'id_form' => 'menu_barre',
            'input'   => [
                [
                    'type'        => 'switch',
                    'label'       => $this->la('Faire ressortir l‘onglet courant (état actif)'),
                    'name'        => 'menu_global_actif',
                    'desc'        => 'La couleur de fond et la couleur du texte des valeurs au survol seront utilisées',
                    'class'       => 't',
                    'is_bool'     => true,
                    'default_val' => '1',
                    'values'      => [
                        [
                            'id'      => 'menu_global_actif',
                            'value'   => 1,
                            'label_1' => $this->la('Oui'),
                        ],
                        [
                            'id'      => 'menu_global_actif',
                            'value'   => 0,
                            'label_0' => $this->la('Non'),
                        ],
                    ],
                ],
                [
                    'type'        => 'larger',
                    'label'       => $this->la('Largeur'),
                    'name'        => 'menu_global_width',
                    'desc'        => 'Mettre 0 pour une largeur automatique',
                    'default_val' => '0',
                    'required'    => true,
                ],
                [
                    'type'        => 'height',
                    'label'       => $this->la('Hauteur'),
                    'name'        => 'menu_global_height',
					
                    'default_val' => '50',
                    'required'    => true,
                ],
                [
                    'type'        => 'padding',
                    'label'       => $this->la('Espacement intérieur - padding (px)'),
                    'name'        => 'menu_cont_padding',
					
                    'default_val' => '0 0 0 0',
                ],
                [
                    'type'        => 'margin',
                    'label'       => $this->la('Espacement extérieur - margin (px)'),
                    'name'        => 'menu_cont_margin',
					
                    'default_val' => '0 0 0 0',
                ],
                [
                    'type'        => 'gradient',
                    'label'       => $this->la('Couleur de fond (gradiant)'),
                    'name'        => 'menu_global_bg_color',
                    'default_val' => '#6e7072',
                ],
                [
                    'type'        => 'color',
                    'label'       => $this->la('Couleur du contour'),
                    'name'        => 'menu_global_border_color',
                    'default_val' => 'transparent',
                ],
                [
                    'type'        => 'border_size',
                    'label'       => $this->la('Epaisseur des bordures (px)'),
                    'name'        => 'menu_cont_border_size',
					
                    'default_val' => '0 0 0 0',
                ],

            ],
            'submit'  => [
                'title' => $this->la('Save'),
                'class' => 'button',
            ],
        ];

        $fields_forms['onglets'] = [
            'legend'  => [
                'title' => $this->la('Réglages des onglets'),
            ],

            'id_form' => 'menu_onglets',
            'input'   => [
                [
                    'type'    => 'select',
                    'label'   => $this->la('Centrage des onglets'),
                    'name'    => 'menu_center_tab',
                    'desc'    => 'Choisissez une méthode de centrage des onglets dans la barre de menu (ordinateur uniquement)',
                    'options' => [
                        'id'    => 'id',
                        'name'  => 'name',
                        'query' => [
                            [
                                'id'   => '1',
                                'name' => 'Aligner à gauche (défaut)',
                            ],
                            [
                                'id'   => '2',
                                'name' => 'Centrer',
                            ],
                            [
                                'id'   => '4',
                                'name' => 'Aligner à droite',
                            ],
                            [
                                'id'   => '3',
                                'name' => 'Justifier',
                            ],
                        ],
                    ],
                ],
                [
                    'type'        => 'larger',
                    'label'       => $this->la('Largeur'),
                    'name'        => 'menu_width',
                    'desc'        => 'Mettre 0 pour une largeur automatique',
                    'default_val' => '0',
                    'required'    => true,
                ],
                [
                    'type'        => 'padding',
                    'label'       => $this->la('Espacement intérieur - padding (px)'),
                    'name'        => 'menu_padding',
					
                    'default_val' => '0 0 0 0',
                ],
                [
                    'type'        => 'margin',
                    'label'       => $this->la('Espacement extérieur - margin (px)'),
                    'name'        => 'menu_margin',
					
                    'default_val' => '0 0 0 0',
                ],
                [
                    'type'        => 'select_font_size',
                    'label'       => $this->la('Taille de la police'),
                    'name'        => 'menufontsize',					
                    'default_val' => '13',
                ],
				[
                    'type'        => 'text_shadow',
                    'label'       => $this->la('Text Shadow'),
                    'name'        => 'menu_text_shadow',					
                    'default_val' => '0 0 0 rgba(255,255,255,1)',
                ],
				[
                    'type'     => 'font_weight',
                    'label'    => $this->la('Poids de la Police'),
                    'name'     => 'menu_font_bold',
					'default_val' => 'normal',                    
                ],
                [
                    'type'     => 'switch',
                    'label'    => $this->la('Texte souligné'),
                    'name'     => 'menu_font_underline',
					
                    'required' => false,
                    'is_bool'  => true,
                    'values'   => [
                        [
                            'id'      => 'menu_font_underline_on',
                            'value'   => 1,
                            'label_1' => $this->la('Oui'),
                        ],
                        [
                            'id'      => 'menu_font_underline_off',
                            'value'   => 0,
                            'label_0' => $this->la('Non'),
                        ],
                    ],
                ],
                [
                    'type'     => 'switch',
                    'label'    => $this->la('Texte souligné (survol)'),
                    'name'     => 'menu_font_underline_hover',
					
                    'required' => false,
                    'is_bool'  => true,
                    'values'   => [
                        [
                            'id'      => 'menu_font_underline_hover_on',
                            'value'   => 1,
                            'label_1' => $this->la('Oui'),
                        ],
                        [
                            'id'      => 'menu_font_underline_hover_off',
                            'value'   => 0,
                            'label_0' => $this->la('Non'),
                        ],
                    ],
                ],
                [
                    'type'    => 'select',
                    'label'   => $this->la('Transformation du texte'),
                    'name'    => 'menu_font_transform',
					
                    'options' => [
                        'id'    => 'id',
                        'name'  => 'name',
                        'query' => [
                            [
                                'id'   => 'none',
                                'name' => 'Normal (héritage)',
                            ],
                            [
                                'id'   => 'lowercase',
                                'name' => 'minuscules',
                            ],
                            [
                                'id'   => 'uppercase',
                                'name' => 'MAJUSCULES',
                            ],
                            [
                                'id'   => 'capitalize',
                                'name' => 'Première lettre en majuscule',
                            ],
                        ],
                    ],
                ],
                [
                    'type'       => 'googlefont',
                    'label'      => $this->la('Police de caractères'),
                    'name'       => 'menufont',
                    'sublabel'   => 'Select additional Ephnyx font',
                    'colorclass' => 'success',
                ],
                [
                    'type'        => 'color',
                    'label'       => $this->la('Couleur du texte'),
                    'name'        => 'menu_link_color',
                    'default_val' => '#6e7072',
                ],
                [
                    'type'        => 'color',
                    'label'       => $this->la('Couleur du texte (survol)'),
                    'name'        => 'menu_link_color_hover',
                    'default_val' => '#6e7072',
                ],
                [
                    'type'        => 'color',
                    'label'       => $this->la('Couleur de fond'),
                    'name'        => 'menu_bck_color',
					
                    'default_val' => '#6e7072',
                ],
				 [
                    'type'        => 'color',
                    'label'       => $this->la('Couleur de fond (survol)'),
                    'name'        => 'menu_bck_color_hover',
					 
                    'default_val' => '#6e7072',
                ],
                [
                    'type'        => 'color',
                    'label'       => $this->la('Couleur du contour'),
                    'name'        => 'menu_border_color',
					
                    'default_val' => '#6e7072',
                ],
                [
                    'type'        => 'border_size',
                    'label'       => $this->la('Epaisseur des bordures (px)'),
                    'name'        => 'menu_border_size',
					
                    'default_val' => '0 0 0 0',
                ],

            ],
            'submit'  => [
                'title' => $this->la('Save'),
                'class' => 'button',
            ],
        ];

        $fields_forms['sub_menus'] = [
            'legend'  => [
                'title' => $this->la('Réglages des sous-menus'),
            ],

            'id_form' => 'menu_sub_menu',
            'input'   => [
                [
                    'type'        => 'larger',
                    'label'       => $this->la('Largeur'),
                    'name'        => 'sub_menu_width',
                    'desc'        => 'Mettre 0 pour une largeur automatique',
                    'default_val' => '0',
                    'required'    => true,
                ],
                [
                    'type'        => 'height',
                    'label'       => $this->la('Hauteur minimale'),
                    'name'        => 'sub_menu_height',
                    'default_val' => '',
                    'required'    => true,
                ],
                [
                    'type'    => 'select',
                    'label'   => $this->la('Méthode d‘ouverture'),
                    'name'    => 'sub_menu_open_method',
                    'options' => [
                        'id'    => 'id',
                        'name'  => 'name',
                        'query' => [
                            [
                                'id'   => '1',
                                'name' => 'Au survol',
                            ],
                            [
                                'id'   => '2',
                                'name' => 'Au clic',
                            ],

                        ],
                    ],
                ],
                [
                    'type'  => 'select',
                    'label' => $this->la('Position'),
                    'name'  => 'sub_menu_position',
					'options' => [
                        'id'    => 'id',
                        'name'  => 'name',
                        'query' => [
                            [
                                'id'   => '1',
                                'name' => 'Aligner les sous-menus sur le côté gauche du menu courant',
                            ],
                            [
                                'id'   => '2',
                                'name' => 'Aligner les sous-menus sur le menu global',
                            ],

                        ],
                    ],                    
                ],
                [
                    'type'        => 'color',
                    'label'       => $this->la('Couleur de fond'),
                    'name'        => 'sub_menu_bgcolor',
                    'default_val' => '#ffffff',
                ],
				[
                    'type'        => 'color',
                    'label'       => $this->la('Couleur de fond Hover'),
                    'name'        => 'sub_menu_bgcolor_hover',
                    'default_val' => '#ffffff',
                ],
                
               
                [
                    'type'        => 'color',
                    'label'       => $this->la('Couleur du contour'),
                    'name'        => 'sub_menu_border_color',
                    'default_val' => 'transparent',
                ],
                [
                    'type'        => 'border_size',
                    'label'       => $this->la('Epaisseur des bordures (px)'),
                    'name'        => 'sub_menu_border_size',
                    'default_val' => '0 0 0 0',
                ],
                [
                    'type'        => 'shadow',
                    'label'       => $this->la('Ombre portée'),
                    'name'        => 'sub_menu_box_shadow',
                    'default_val' => '0px 5px 13px 0px',
                ],
                [
                    'type'        => 'color',
                    'label'       => $this->la('Couleur de l‘ombre portée'),
                    'name'        => 'sub_menu_box_shadow_color',
                    'default_val' => '#000000',
                ],
                [
                    'type'        => 'slider',
                    'label'       => $this->la('Opacité de l‘ombre portée'),
                    'name'        => 'sub_menu_box_shadow_opacity',
                    'default_val' => '20',
                    'min'         => 0,
                    'max'         => 100,
                    'step'        => 1,
                    'suffix'      => '%',
                ],
				[
                    'type'        => 'slider',
                    'label'       => $this->la('Délai avant ouverture'),
                    'name'        => 'sub_menu_open_delay',
                    'default_val' => '0.3',
                    'min'         => 0,
                    'max'         => 2,
                    'step'        => 0.1,
                    'suffix'      => 's',
                ],
				[
                    'type'        => 'slider',
                    'label'       => $this->la('Durée de l‘effet « fondu »'),
                    'name'        => 'sub_menu_fade_speed',
                    'default_val' => '0.3',
                    'min'         => 0,
                    'max'         => 2,
                    'step'        => 0.1,
                    'suffix'      => 's',
                ],

            ],
            'submit'  => [
                'title' => $this->la('Save'),
                'class' => 'button',
            ],
        ];
		
		$fields_forms['columns_seetings'] = [
            'legend'  => [
                'title' => $this->la('Réglages des colonnes'),
            ],

            'id_form' => 'menu_columns_seetings',
            'input'   => [
               [
                    'type'        => 'padding',
                    'label'       => $this->la('Espacement intérieur - padding (px)'),
                    'name'        => 'column_wrap_padding',
                    'default_val' => '0 0 0 0',
                ],

            ],
            'submit'  => [
                'title' => $this->la('Save'),
                'class' => 'button',
            ],
        ];
		
		$fields_forms['element_group_seetings'] = [
            'legend'  => [
                'title' => $this->la('Réglages des groupes d‘éléments'),
            ],

            'id_form' => 'menu_element_group_seetings',
            'input'   => [
				[
                    'type' => 'infoheading',
            		'label' => $this->la('Réglages du conteneur'),
            		'name' => 'infoheading',
                    'colorclass' => 'info_custom success',
                ],

               [
                    'type'        => 'padding',
                    'label'       => $this->la('Espacement intérieur - padding (px)'),
                    'name'        => 'column_padding',
                    'default_val' => '0 0 0 0',
                ],
				[
                    'type'        => 'margin',
                    'label'       => $this->la('Espacement extérieur - margin (px)'),
                    'name'        => 'column_margin',
                    'default_val' => '0 0 0 0',
                ],
				[
                    'type' => 'infoheading',
            		'label' => $this->la('Réglages du titre'),
            		'name' => 'infoheading',
                    'colorclass' => 'info_custom success',
                ],
				[
                    'type'        => 'padding',
                    'label'       => $this->la('Espacement intérieur - padding (px)'),
                    'name'        => 'column_title_padding',
                    'default_val' => '0 0 0 0',
                ],
				[
                    'type'        => 'margin',
                    'label'       => $this->la('Espacement extérieur - margin (px)'),
                    'name'        => 'column_title_margin',
                    'default_val' => '0 0 0 0',
                ],
				[
                    'type'        => 'select_font_size',
                    'label'       => $this->la('Taille du Texte'),
                    'name'        => 'column_font_size',
                    'default_val' => '13',
                ],
				[
                    'type'     => 'font_weight',
                    'label'    => $this->la('Titre en gras'),
                    'name'     => 'column_font_bold',
					'default_val' => 'normal',                    
                ],
                [
                    'type'     => 'switch',
                    'label'    => $this->la('Texte souligné'),
                    'name'     => 'column_font_underline',
                    'required' => false,
                    'is_bool'  => true,
                    'values'   => [
                        [
                            'id'      => 'column_font_underline_on',
                            'value'   => 1,
                            'label_1' => $this->la('Oui'),
                        ],
                        [
                            'id'      => 'column_font_underline_off',
                            'value'   => 0,
                            'label_0' => $this->la('Non'),
                        ],
                    ],
                ],
                [
                    'type'     => 'switch',
                    'label'    => $this->la('Texte souligné (survol)'),
                    'name'     => 'column_font_underline_hover',
                    'required' => false,
                    'is_bool'  => true,
                    'values'   => [
                        [
                            'id'      => 'column_font_underline_hover_on',
                            'value'   => 1,
                            'label_1' => $this->la('Oui'),
                        ],
                        [
                            'id'      => 'column_font_underline_hover_off',
                            'value'   => 0,
                            'label_0' => $this->la('Non'),
                        ],
                    ],
                ],
                [
                    'type'    => 'select',
                    'label'   => $this->la('Transformation du texte'),
                    'name'    => 'column_font_transform',
                    'options' => [
                        'id'    => 'id',
                        'name'  => 'name',
                        'query' => [
                            [
                                'id'   => 'none',
                                'name' => 'Normal (héritage)',
                            ],
                            [
                                'id'   => 'lowercase',
                                'name' => 'minuscules',
                            ],
                            [
                                'id'   => 'uppercase',
                                'name' => 'MAJUSCULES',
                            ],
                            [
                                'id'   => 'capitalize',
                                'name' => 'Première lettre en majuscule',
                            ],
                        ],
                    ],
                ],
                [
                    'type'       => 'googlefont',
                    'label'      => $this->la('Police de caractères'),
                    'name'       => 'columnfont',
                    'sublabel'   => 'Select additional Ephnyx font',
                    'colorclass' => 'success',
                ],
                [
                    'type'        => 'color',
                    'label'       => $this->la('Couleur du texte'),
                    'name'        => 'column_link_color',
                    'default_val' => '#6e7072',
                ],
                [
                    'type'        => 'color',
                    'label'       => $this->la('Couleur du texte (survol)'),
                    'name'        => 'column_link_color_hover',
                    'default_val' => '#6e7072',
                ],

            ],
            'submit'  => [
                'title' => $this->la('Save'),
                'class' => 'button',
            ],
        ];
		
		
        $url_no_image = Context::getContext()->link->getBaseFrontLink() . 'img/fr.jpg';

        foreach ($fields_forms as $key => $fields_form) {

            $this->fields_form = $fields_form;
           
            $url_no_image = $this->context->link->getBaseFrontLink() . 'img/fr.jpg';
            $this->tpl_form_vars = [
                'gets_fonts_family'    => $this->gets_fonts_family(),
                'gets_fonts_variants'  => $this->gets_fonts_variants('ABeeZee'),
                'gets_fonts_subsets'   => $this->gets_fonts_subsets('ABeeZee'),
            ];
            $this->fields_value = [];
            $this->assignFormValue($this->fields_form);

            $html = parent::renderForm();
            $forms[$fields_form['legend']['title']] = [
                'key'     => $fields_form['id_form'],
                'content' => $html,
            ];


        }

        $data = $this->createTemplate('controllers/themes/menu_tabs.tpl');

        $data->assign([
            'forms' => $forms,

        ]);

        return $data->fetch();
    }
	
	public function generateMobileMenuParams() {

        $forms = [];

        $fields_forms['menu_responsif'] = [
            'legend'  => [
                'title' => $this->la('Réglages Responsivité'),
            ],

            'id_form' => 'menu_responsif',
            'input'   => [
                [
                    'type'        => 'switch',
                    'label'       => $this->la('Activer le mode responsive'),
                    'name'        => 'menu_responsive_active',
                    'class'       => 't',
                    'is_bool'     => true,
                    'default_val' => '1',
                    'values'      => [
                        [
                            'id'      => 'menu_responsive_active_on',
                            'value'   => 1,
                            'label_1' => $this->la('Oui'),
                        ],
                        [
                            'id'      => 'menu_responsive_active_off',
                            'value'   => 0,
                            'label_0' => $this->la('Non'),
                        ],
                    ],
                ],
                [
                    'type'        => 'text',
                    'label'       => $this->la('Activer le menu mobile jusqu‘à'),
                    'name'        => 'screen_responsive_size',
                    'default_val' => '767',
                ],
               
            ],
            'submit'  => [
                'title' => $this->la('Save'),
                'class' => 'button',
            ],
        ];
		
		$fields_forms['menu_mobile_button'] = [
            'legend'  => [
                'title' => $this->la('Réglages du bouton d‘ouverture du menu'),
            ],

            'id_form' => 'menu_mobile_button',
            'input'   => [
                [
                    'type'        => 'height',
                    'label'       => $this->la('Hauteur'),
                    'name'        => 'menu_mobile_button_height',
					
                    'default_val' => '50',
                    'required'    => true,
                ],
				[
                    'type'        => 'select_font_size',
                    'label'       => $this->la('Taille du texte'),
                    'name'        => 'menu_mobile_button_f_size',
                    'default_val' => '13',
                    'size'        => 10,
                ],
                [
                    'type'        => 'text',
                    'label'       => $this->la('Libellé'),
                    'name'        => 'menu_mobile_button_libelle',
                ],
				[
                    'type'        => 'img_upload',
                    'label'       => $this->la('Icone menu Fermé'),
                    'name'        => 'menu_mobile_close_icon',
                ],
				[
                    'type'        => 'img_upload',
                    'label'       => $this->la('Icone menu Ouvert'),
                    'name'        => 'menu_mobile_open_icon',
                ],
				[
                    'type'        => 'img_upload',
                    'label'       => $this->la('Icone Default User'),
                    'name'        => 'menu_mobile_user_img',
                ],
               
            ],
            'submit'  => [
                'title' => $this->la('Save'),
                'class' => 'button',
            ],
        ];

        $fields_forms['menu_mobile_onglets'] = [
            'legend'  => [
                'title' => $this->la('Réglages Mobile des onglets'),
            ],

            'id_form' => 'menu_mobile_onglets',
            'input'   => [
               
                [
                    'type'        => 'padding',
                    'label'       => $this->la('Espacement intérieur - padding (px)'),
                    'name'        => 'menu_mobile_padding',
					
                    'default_val' => '0 0 0 0',
                ],
                [
                    'type'        => 'margin',
                    'label'       => $this->la('Espacement extérieur - margin (px)'),
                    'name'        => 'menu_mobile_margin',
					
                    'default_val' => '0 0 0 0',
                ],
                [

                    'type'        => 'select_font_size',
                    'label'       => $this->la('Taille de la police'),
                    'name'        => 'menu_mobile_font_size',
					
                    'default_val' => '13',
                ],
                [
                    'type'     => 'switch',
                    'label'    => $this->la('Texte en gras'),
                    'name'     => 'menu_mobile_font_bold',
					
                    'required' => false,
                    'is_bool'  => true,
                    'values'   => [
                        [
                            'id'      => 'menu_mobile_font_bold_on',
                            'value'   => 1,
                            'label_1' => $this->la('Oui'),
                        ],
                        [
                            'id'      => 'menu_mobile_font_bold_off',
                            'value'   => 0,
                            'label_0' => $this->la('Non'),
                        ],
                    ],
                ],
              
                [
                    'type'    => 'select',
                    'label'   => $this->la('Transformation du texte'),
                    'name'    => 'menu_mobile_font_transform',
					
                    'options' => [
                        'id'    => 'id',
                        'name'  => 'name',
                        'query' => [
                            [
                                'id'   => 'none',
                                'name' => 'Normal (héritage)',
                            ],
                            [
                                'id'   => 'lowercase',
                                'name' => 'minuscules',
                            ],
                            [
                                'id'   => 'uppercase',
                                'name' => 'MAJUSCULES',
                            ],
                            [
                                'id'   => 'capitalize',
                                'name' => 'Première lettre en majuscule',
                            ],
                        ],
                    ],
                ],
                [
                    'type'       => 'googlefont',
                    'label'      => $this->la('Police de caractères'),
                    'name'       => 'menu_mobile_font',
                    'sublabel'   => 'Select additional Ephnyx font',
                    'colorclass' => 'success',
                ],
                [
                    'type'        => 'color',
                    'label'       => $this->la('Couleur du texte'),
                    'name'        => 'menu_mobile_link_color',
                    'default_val' => '#6e7072',
                ],
                [
                    'type'        => 'color',
                    'label'       => $this->la('Couleur du texte (survol)'),
                    'name'        => 'menu_mobile_link_color_hover',
                    'default_val' => '#6e7072',
                ],
                [
                    'type'        => 'gradient',
                    'label'       => $this->la('Couleur de fond'),
                    'name'        => 'menu_mobile_bck_color',
					
                    'default_val' => '#6e7072',
                ],
				 [
                    'type'        => 'color',
                    'label'       => $this->la('Couleur de fond (survol)'),
                    'name'        => 'menu_mobile_bck_color_hover',
					 
                    'default_val' => '#6e7072',
                ],
                [
                    'type'        => 'color',
                    'label'       => $this->la('Couleur du contour'),
                    'name'        => 'menu_mobile_border_color',
					
                    'default_val' => '#6e7072',
                ],
                [
                    'type'        => 'border_size',
                    'label'       => $this->la('Epaisseur des bordures (px)'),
                    'name'        => 'menu_mobile_border_size',
					
                    'default_val' => '0 0 0 0',
                ],

            ],
            'submit'  => [
                'title' => $this->la('Save'),
                'class' => 'button',
            ],
        ];

        $fields_forms['sub_menus'] = [
            'legend'  => [
                'title' => $this->la('Réglages des sous-menus'),
            ],

            'id_form' => 'menu_sub_menu',
            'input'   => [
               
                [
                    'type'        => 'color',
                    'label'       => $this->la('Couleur de fond'),
                    'name'        => 'sub_menu_mobile_bgcolor',
                    'default_val' => '#ffffff',
                ],
				[
                    'type'        => 'padding',
                    'label'       => $this->la('Espacement intérieur du contener- padding (px)'),
                    'name'        => 'sub_menu_mobile_padding',
                    'default_val' => '0 0 0 0',
                ],
                [
                    'type'        => 'color',
                    'label'       => $this->la('Couleur du contour'),
                    'name'        => 'sub_menu_mobile_border_color',
                    'default_val' => 'transparent',
                ],
                [
                    'type'        => 'border_size',
                    'label'       => $this->la('Epaisseur des bordures (px)'),
                    'name'        => 'sub_menu_mobile_border_size',
                    'default_val' => '0 0 0 0',
                ],
				[
                    'type'        => 'img_upload',
                    'label'       => $this->la('Icône pour l‘état ouvert'),
                    'name'        => 'sub_menu_mobile_open_icon',
                ],
				[
                    'type'        => 'img_upload',
                    'label'       => $this->la('Icône pour l‘état fermé'),
                    'name'        => 'sub_menu_mobile_close_icon',
                ],
             

            ],
            'submit'  => [
                'title' => $this->la('Save'),
                'class' => 'button',
            ],
        ];
		
		$fields_forms['menu_mobile_columns_seetings'] = [
            'legend'  => [
                'title' => $this->la('Réglages Mobile des colonnes'),
            ],

            'id_form' => 'menu_mobile_columns_seetings',
            'input'   => [
               [
                    'type'        => 'padding',
                    'label'       => $this->la('Espacement intérieur - padding (px)'),
                    'name'        => 'menu_mobile_column_wrap_padding',
                    'default_val' => '0 0 0 0',
                ],
				[
                    'type'        => 'margin',
                    'label'       => $this->la('Espacement intérieur - padding (px)'),
                    'name'        => 'menu_mobile_column_wrap_margin',
                    'default_val' => '0 0 0 0',
                ],
				[
                    'type'        => 'color',
                    'label'       => $this->la('Couleur du contour'),
                    'name'        => 'menu_mobile_column_wrap_border_color',
                    'default_val' => 'transparent',
                ],
                [
                    'type'        => 'border_size',
                    'label'       => $this->la('Epaisseur des bordures (px)'),
                    'name'        => 'menu_mobile_column_wrap_border_size',
                    'default_val' => '0 0 0 0',
                ],


            ],
            'submit'  => [
                'title' => $this->la('Save'),
                'class' => 'button',
            ],
        ];
		
		$fields_forms['menu_mobile_element_group_seetings'] = [
            'legend'  => [
                'title' => $this->la('Réglages Mobile des groupes d‘éléments'),

            ],

            'id_form' => 'menu_mobile_element_group_seetings',
            'input'   => [
				[
                    'type' => 'infoheading',
            		'label' => $this->la('Réglages du conteneur'),
            		'name' => 'infoheading',
                    'colorclass' => 'info_custom success',
                ],

               [
                    'type'        => 'padding',
                    'label'       => $this->la('Espacement intérieur - padding (px)'),
                    'name'        => 'menu_mobile_column_padding',
                    'default_val' => '0 0 0 0',
                ],
				[
                    'type'        => 'margin',
                    'label'       => $this->la('Espacement extérieur - margin (px)'),
                    'name'        => 'menu_mobile_column_margin',
                    'default_val' => '0 0 0 0',
                ],
				[
                    'type' => 'infoheading',
            		'label' => $this->la('Réglages du titre'),
            		'name' => 'infoheading',
                    'colorclass' => 'info_custom success',
                ],
				[
                    'type'        => 'padding',
                    'label'       => $this->la('Espacement intérieur - padding (px)'),
                    'name'        => 'menu_mobile_column_title_padding',
                    'default_val' => '0 0 0 0',
                ],
				[
                    'type'        => 'margin',
                    'label'       => $this->la('Espacement extérieur - margin (px)'),
                    'name'        => 'menu_mobile_column_title_margin',
                    'default_val' => '0 0 0 0',
                ],
				[
                    'type'        => 'select_font_size',
                    'label'       => $this->la('Taille du Texte'),
                    'name'        => 'menu_mobile_column_font_size',
                    'default_val' => '13',
                ],
                [
                    'type'     => 'switch',
                    'label'    => $this->la('Texte en gras'),
                    'name'     => 'menu_mobile_column_font_bold',
                    'required' => false,
                    'is_bool'  => true,
                    'values'   => [
                        [
                            'id'      => 'column_font_bold_on',
                            'value'   => 1,
                            'label_1' => $this->la('Oui'),
                        ],
                        [
                            'id'      => 'column_font_bold_off',
                            'value'   => 0,
                            'label_0' => $this->la('Non'),
                        ],
                    ],
                ],
                [
                    'type'    => 'select',
                    'label'   => $this->la('Transformation du texte'),
                    'name'    => 'menu_mobile_column_font_transform',
                    'options' => [
                        'id'    => 'id',
                        'name'  => 'name',
                        'query' => [
                            [
                                'id'   => 'none',
                                'name' => 'Normal (héritage)',
                            ],
                            [
                                'id'   => 'lowercase',
                                'name' => 'minuscules',
                            ],
                            [
                                'id'   => 'uppercase',
                                'name' => 'MAJUSCULES',
                            ],
                            [
                                'id'   => 'capitalize',
                                'name' => 'Première lettre en majuscule',
                            ],
                        ],
                    ],
                ],
                [
                    'type'       => 'googlefont',
                    'label'      => $this->la('Police de caractères'),
                    'name'       => 'menu_mobile_column_font',
                    'sublabel'   => 'Select additional Ephnyx font',
                    'colorclass' => 'success',
                ],
                [
                    'type'        => 'color',
                    'label'       => $this->la('Couleur du texte'),
                    'name'        => 'menu_mobile_column_link_color',
                    'default_val' => '#6e7072',
                ],
                

            ],
            'submit'  => [
                'title' => $this->la('Save'),
                'class' => 'button',
            ],
        ];		
		
		$fields_forms['medi_custom_seetings'] = [
            'legend'  => [
                'title' => $this->la('Réglages Mobiles'),
            ],

            'id_form' => 'menu_element_mobile_seetings',
            'input'   => [
				[
                    'type' => 'infoheading',
            		'label' => $this->la('Injecter ici vos règles Css pour média <= 767'),
            		'name' => 'infoheading',
                    'colorclass' => 'info_custom success',
                ],
				[
                    'type'        => 'customtextarea',
                    'label'       => $this->la('Custom CSS'),
                    'name'        => 'menu_custom_css',
                    'desc'        => $this->la('Please Enter Your Custom CSS'),
                    'rows'        => 30,
                    'cols'        => 25,
                    'mode'        => 'css',
                    'class'       => "custom_css_class",
                    'default_val' => '',
                ],

             
            ],
            'submit'  => [
                'title' => $this->la('Save'),
                'class' => 'button',
            ],
        ];
		
        $url_no_image = Context::getContext()->link->getBaseFrontLink() . 'img/fr.jpg';

        foreach ($fields_forms as $key => $fields_form) {

            $this->fields_form = $fields_form;
           
            $url_no_image = $this->context->link->getBaseFrontLink() . 'img/fr.jpg';
            $this->tpl_form_vars = [
                'gets_fonts_family'    => $this->gets_fonts_family(),
                'gets_fonts_variants'  => $this->gets_fonts_variants('ABeeZee'),
                'gets_fonts_subsets'   => $this->gets_fonts_subsets('ABeeZee'),
            ];
            $this->fields_value = [];
            $this->assignFormValue($this->fields_form);

            $html = parent::renderForm();
            $forms[$fields_form['legend']['title']] = [
                'key'     => $fields_form['id_form'],
                'content' => $html,
            ];


        }

        $data = $this->createTemplate('controllers/themes/mobile_menu_tabs.tpl');

        $data->assign([
            'forms' => $forms,

        ]);

        return $data->fetch();
    }
	
	
	
	public function assignFormValue($fields_form) {

        foreach ($fields_form['input'] as $mvalue) {

            if (isset($mvalue['lang']) && $mvalue['lang'] == true && isset($mvalue['name'])) {
                $languages = Language::getLanguages(false);

                foreach ($languages as $lang) {
                    $this->fields_value[$mvalue['name']][$lang['id_lang']] = Configuration::get('xprt' . $mvalue['name'], $lang['id_lang']);
                }

            } else {

                if (isset($mvalue['name'])) {
					if (isset($mvalue['type']) && ($mvalue['type'] == "color")) {
				
						$fields_value = !is_null(Configuration::get('xprt' . $mvalue['name'])) ? Configuration::get('xprt' . $mvalue['name']) : '';
						if(!empty($fields_value)) {
							$fields_value = $this->timberpress_rgb_to_hex( $fields_value );
						}
						 $this->fields_value[$mvalue['name']] = $fields_value;
				
                
				
            		} else {
                    	$this->fields_value[$mvalue['name']] = !is_null(Configuration::get('xprt' . $mvalue['name'])) ? Configuration::get('xprt' . $mvalue['name']) : '';
					}
                }

            }

        }
    }
	
	public function ajaxProcessSaveMenuRules() {
		
		$file = fopen("testsaveMenuTheme.txt", "w");
        $fields_form = $this->fieldForm;
		
		foreach ($fields_form['input'] as $mvalue) {
			if(empty(Tools::getValue($mvalue['name']))) {
				continue;
			}
            if (isset($mvalue['lang']) && $mvalue['lang'] == true && isset($mvalue['name'])) {
                $languages = Language::getLanguages(false);

                foreach ($languages as $lang) {
                    ${$mvalue['name'] . '_lang'}

                    [$lang['id_lang']] = Tools::getvalue($mvalue['name'] . '_' . $lang['id_lang']);
                }

            }

        }

        foreach ($fields_form['input'] as $mvalue) {

            if (isset($mvalue['type']) && ($mvalue['type'] == "googlefont")) {
                $this->SaveGoogleFonts($mvalue['name']);

            } else
            if (isset($mvalue['type']) && $mvalue['type'] == "4size") {
                $result = '';
                $values = Tools::getValue($mvalue['name']);
                $result = $values[0] . ' ' . $values[1] . ' ' . $values[2] . ' ' . $values[3];
                Configuration::updateValue('xprt' . $mvalue['name'], $result);

            } else
            if (isset($mvalue['type']) && ($field['type'] == 'shadow')) {
                Configuration::updateValue('xprt' . $mvalue['name'], $this->getBorderSizeFromArray(Tools::getValue($mvalue['name'])));

            } else
            if (isset($mvalue['type']) && ($mvalue['type'] == "color")) {
                Configuration::updateValue('xprt' . $mvalue['name'], $this->_hex2rgb(Tools::getValue($mvalue['name'])));

            } else
            if (isset($mvalue['type']) && ($mvalue['type'] == "select_font_size")) {
                $type = Tools::getValue($mvalue['name']);
                $size = Tools::getValue($mvalue['name'] . '_' . $type);
                Configuration::updateValue('xprt' . $mvalue['name'], $size . $type);

            } else
            if (isset($mvalue['type']) && ($mvalue['type'] == "rvb_opacity")) {
                $values = Tools::getValue($mvalue['name']);

                $color = $values[0];

                $opacity = $values[1];
                Configuration::updateValue('xprt' . $mvalue['name'], $this->_hex2rgb($color, true, $opacity));

            } else
            if (isset($mvalue['type']) && ($mvalue['type'] == "img_base64")) {

                $key = $mvalue['name'] . '_mid';
                $imageUploader = new HelperImageUploader($key);
                $imageUploader->setAcceptTypes(['jpeg', 'png', 'jpg']);
                $files = $imageUploader->process();

                if (is_array($files) && count($files)) {

                    foreach ($files as $image) {
                        $type = pathinfo($image['name'], PATHINFO_EXTENSION);
                        $data = file_get_contents($image['save_path']);
                        $base64_code = base64_encode($data);
                        $base64_str = 'data:image/' . $type . ';base64,' . $base64_code;
                        $image = $base64_str;
                    }

                    Configuration::updateValue('xprt' . $mvalue['name'], $image);
                }

            } else
            if (isset($mvalue['type']) && ($mvalue['type'] == "gradient")) {

                $value = Tools::getValue($mvalue['name']);
                $key = '';

                $key = $this->_hex2rgb($value[0]);

                if (!empty($value[1])) {
                    $key .= '-' . $this->_hex2rgb($value[1]);

                    if (!empty($value[2])) {
                        $key .= '-' . $value[2];
                    } else {
                        $key .= '-100';
                    }

                    if (!empty($value[3])) {
                        $key .= '-' . $value[3];
                    } else {
                        $key .= '-%';
                    }

                }

                Configuration::updateValue('xprt' . $mvalue['name'], $key);

            }  else
            if (isset($mvalue['type']) && ($mvalue['type'] == "text_shadow") || ($mvalue['type'] == "box_shadow")) {
				
                $value = Tools::getValue($mvalue['name']);
				$result = $value[0].' '.$value[1].' '.$value[2].' '.$this->_hex2rgb($value[3], true, $value[4]);
                

                Configuration::updateValue('xprt' . $mvalue['name'], $result);

            } else
            if (isset($mvalue['type']) && ($mvalue['type'] == "background_size")) {

                $value = Tools::getValue($mvalue['name']);

                if ($value == 'custom') {
                    $width = Tools::getValue($mvalue['name'] . '_width');
                    $height = Tools::getValue($mvalue['name'] . '_height');
                    $value = $width . ' ' . $height;
                }

                Configuration::updateValue('xprt' . $mvalue['name'], $value);

            } else
            if (isset($mvalue['type']) && ($mvalue['type'] == "contener_position")) {
                $positions = ['0', '0', '0', '0'];
                $position = Tools::getValue($mvalue['name']);
                $value = [];
                $value['position'] = $position;

                if ($position == 'absolute' || $position == 'fixed') {
                    $top = Tools::getValue($mvalue['name'] . '_top');
                    $right = Tools::getValue($mvalue['name'] . '_right');
                    $bottom = Tools::getValue($mvalue['name'] . '_bottom');
                    $left = Tools::getValue($mvalue['name'] . '_left');
                    $positions = [$top, $right, $bottom, $left];
                }

                $value['positions'] = $positions;
                Configuration::updateValue('xprt' . $mvalue['name'], Tools::jsonEncode($value));

            } else
            if (isset($mvalue['type']) && ($mvalue['type'] == "element_before") || ($mvalue['type'] == "element_after")) {

                $value = Tools::getValue($mvalue['name'] . '_bck_type');

                if (!empty($value)) {
                    $result = [];
                    $result['bck_type'] = $value;
                    $result['height'] = Tools::getValue($mvalue['name']);
                    $top = Tools::getValue($mvalue['name'] . '_top');
                    $right = Tools::getValue($mvalue['name'] . '_right');
                    $bottom = Tools::getValue($mvalue['name'] . '_bottom');
                    $left = Tools::getValue($mvalue['name'] . '_left');
                    $result['position'] = [$top, $right, $bottom, $left];

                    if ($value == 'bck') {
                        $result['bck_color'] = $this->_hex2rgb(Tools::getValue($mvalue['name'] . '_bck'));
                    } else {
                        $color1 = $this->_hex2rgb(Tools::getValue($mvalue['name'] . '_bck1'));
                        $color2 = $this->_hex2rgb(Tools::getValue($mvalue['name'] . '_bck2'));
                        $profondeur = Tools::getValue($mvalue['name'] . '_gradient');
                        $type = Tools::getValue($mvalue['name'] . '_type');
                        $result['bck_value'] = $color1 . '-' . $color2 . '-' . $profondeur . '-' . $type;
                    }

                    Configuration::updateValue('xprt' . $mvalue['name'], Tools::jsonEncode($result));
                }

            }  else
            if (isset($mvalue['type']) && ($mvalue['type'] == "contener_border")) {

                $type = Tools::getValue($mvalue['name']);
				$result = [];
                $result['type'] = $type;

                if ($type != 'none') {
                    $result['style'] = Tools::getValue($mvalue['name'].'_border_type');
					$result['size'] = Tools::getValue($mvalue['name'].'_size');
					$result['color'] = Tools::getValue($mvalue['name'].'_color');                    
                }
				Configuration::updateValue('xprt' . $mvalue['name'], Tools::jsonEncode($result));
            }   else
                    if (isset($mvalue['type']) && ($mvalue['type'] == "4size")) {
                        
                        $values = Configuration::get('xprt' . $mvalue['name']);
						$keys = explode(' ', $values);
						$top = $keys[0];
						$right = $keys[1];
						$bottom = $keys[2];
						$left = $keys[3];
						if(count(array_unique($keys)) === 1) {
    						$xprt[$mvalue['name']] = $keys[0];
						} else if($top == $bottom && $right == $left) {
							$xprt[$mvalue['name']] = $keys[0].' '.$keys[1];
						} else {
							$xprt[$mvalue['name']] = $values;
						}
						


                    } else {

                if (isset($mvalue['lang']) && $mvalue['lang'] == true && isset($mvalue['name'])) {

                    Configuration::updateValue('xprt' . $mvalue['name'], ${$mvalue['name'] . '_lang'});
                } else {

                    if (isset($mvalue['name'])) {
                        Configuration::updateValue('xprt' . $mvalue['name'], Tools::getvalue($mvalue['name']));
                    }

                }

            }

        }
		$this->GenerateCustomCss();
        $return = [
            'success' => true,
            'message' => 'Les réglages du menu ont été été mis à jour avec succès',
        ];

        die(Tools::jsonEncode($return));
    }
	
	public function GenerateCustomCss() {

        $context = Context::getContext();
        $url_no_image = $context->link->getBaseFrontLink() . 'img/fr.jpg';
		$xprt = $this->AsignGlobalSettingValue();
		
		
		$tpl = $context->smarty->createTemplate(_EPH_ALL_THEMES_DIR_ . "xprtmenu_css_.tpl");
		foreach ($xprt as $key => $value) {

            if ($key == 'custom_css' || $key == 'menu_custom_css') {

                $value = str_replace(["\\r\\n", "\\r"], '', $value);
				$minifier = new Minify\CSS();
				$minifier->add($value);
				$value = $minifier->minify();

            }

            $tpl->assign([
                $key => $value,
            ]);
        }
        $custom_css = $tpl->fetch();
        $css = fopen($this->cssMenufile, 'w');
        fwrite($css, $custom_css);
		
		$xprt = $this->AsignGlobalSettingValue();
		$tpl = $context->smarty->createTemplate(_EPH_ALL_THEMES_DIR_ . "xprtroot_css_.tpl");
        
       
        $logo_pictospace = !empty(Configuration::get('EPH_PICTO_MYSPACE')) ? $this->context->link->getBaseFrontLink() . 'img/' . Configuration::get('EPH_PICTO_MYSPACE') : $url_no_image;

        $logo_fixed_space = !empty(Configuration::get('EPH_FIXED_PICTO_MYSPACE')) ? $this->context->link->getBaseFrontLink() . 'img/' . Configuration::get('EPH_FIXED_PICTO_MYSPACE') : $url_no_image;

        $tpl->assign([
            'polygon'          => Configuration::get('EPH_LOGO'),
            'logo_pictospace'  => $logo_pictospace,
            'logo_fixed_space' => $logo_fixed_space,
            'logo_shop'        => !empty(Configuration::get('EPH_LOGO')) ? $this->context->link->getBaseFrontLink() . 'img/' . Configuration::get('EPH_LOGO') : $url_no_image,
			'logo_mobile'        => !empty(Configuration::get('EPH_LOGO_MOBILE')) ? $this->context->link->getBaseFrontLink() . 'img/' . Configuration::get('EPH_LOGO_MOBILE') : $url_no_image,
        ]);

        $imageCertif = !empty(Configuration::get('EPH_CERTIFICATION_IMAGE')) ? $context->link->getBaseFrontLink() . 'img/' . Configuration::get('EPH_CERTIFICATION_IMAGE') : $url_no_image;
        $tpl->assign([
            'imageCertif' => $imageCertif,
        ]);

        foreach ($xprt as $key => $value) {
            
            $tpl->assign([
                $key => $value,
            ]);
        }

        $custom_css = $tpl->fetch();
        $css = fopen($this->cssRootfile, 'w');
        fwrite($css, $custom_css);
        $css = fopen($this->agent_cssRootfile, 'w');
        fwrite($css, $custom_css);
		
		$tpl = $context->smarty->createTemplate(_EPH_ALL_THEMES_DIR_ . "xprtcustom_css.tpl");
		foreach ($xprt as $key => $value) {

            if ($key == 'custom_css' || $key == 'menu_custom_css') {

                $value = str_replace(["\\r\\n", "\\r"], '', $value);
				$minifier = new Minify\CSS();
				$minifier->add($value);
				$value = $minifier->minify();

            }

            $tpl->assign([
                $key => $value,
            ]);
        }
        $custom_css = $tpl->fetch();
        $css = fopen($this->cssfile, 'w');
        fwrite($css, $custom_css);
		$css = fopen($this->agent_cssfile, 'w');
        fwrite($css, $custom_css);
       
		
		$tpl = $context->smarty->createTemplate(_EPH_ALL_THEMES_DIR_ . "xprtpolygon_css.tpl");
		foreach ($xprt as $key => $value) {            
            $tpl->assign([
                $key => $value,
            ]);
        }
		$custom_css = $tpl->fetch();
        $css = fopen($this->cssPolygonfile, 'w');
        fwrite($css, $custom_css);
        $css = fopen($this->agent_cssPolygonfile, 'w');
        fwrite($css, $custom_css);
       
    }
	
	public function AsignGlobalSettingValue() {

        $id_lang = Context::getcontext()->language->id;
        $multiple_arr = [];
        $xprt = [];
        $theme_dir = Context::getcontext()->company->theme_directory;
       
        $xprt['xprtpatternsurl'] = Context::getContext()->company->getBaseURL() . _EPH_THEMES_DIR_ . $theme_dir . '/img/patterns/';
        $xprt['xprtimageurl'] = Context::getContext()->company->getBaseURL() . 'img/theme/';
        $file = fopen("testAsignGlobalMenuSettingValue.txt", "w");
		$field_common = Tools::jsonDecode(Configuration::get('EPH_EXPERT_THEME_FIELDS'), true);
		if (is_array($field_common)) {
            $fields_form['input'] = array_merge(
				$this->fieldForm['input'],
           		$field_common['input']
        	);
        } else {
            $fields_form['input'] = $this->fieldForm['input'];
        }
		
        fwrite($file, print_r($fields_form['input'], true));

        foreach ($fields_form['input'] as $mvalue) {

            if (isset($mvalue['lang']) && $mvalue['lang'] == true && isset($mvalue['name'])) {
                $xprt[$mvalue['name']] = Configuration::get('xprt' . $mvalue['name'], $id_lang);
            } else {

                if (isset($mvalue['name'])) {

                    if (isset($mvalue['type']) && ($mvalue['type'] == "gradient")) {
                        $value = Configuration::get('xprt' . $mvalue['name']);
                        $keys = explode('-', $value);
                        $key = $keys[0];

                        if (!empty($keys[1])) {
                            $xprt[$mvalue['name']] = 'linear-gradient(to bottom, ' . $keys[0] . ' 0%, ' . $keys[1] . ' ' . $keys[2] . $keys[3] . ')';
                        } else {

                            $xprt[$mvalue['name']] = $keys[0];
                        }

                    } else
                    if (isset($mvalue['type']) && ($mvalue['type'] == "element_before") || ($mvalue['type'] == "element_after")) {
                        $result = [];
                        $fields_value = Tools::jsonDecode(stripslashes(Configuration::get('xprt' . $mvalue['name'])));
                        fwrite($file, print_r($fields_value, true) . PHP_EOL);
                        $result['content'] = '" "';
                        $result['display'] = 'block';
                        $result['position'] = 'absolute';
                        $result['z-index'] = '-1';
                        $result['height'] = $fields_value->height . 'px';

                        if ($fields_value->position[0] != '') {
                            $result['top'] = $fields_value->position[0];
                        }

                        if ($fields_value->position[1] != '') {
                            $result['right'] = $fields_value->position[1];
                        }

                        if ($fields_value->position[2] != '') {
                            $result['bottom'] = $fields_value->position[2];
                        }

                        if ($fields_value->position[3] != '') {
                            $result['left'] = $fields_value->position[3];
                        }

                        if ($fields_value->bck_type == 'bck') {
                            $result['background'] = $fields_value->bck_color;
                        } else {
                            $keys = explode('-', $fields_value->bck_value);
                            $result['background-image'] = 'linear-gradient(to bottom, ' . $keys[0] . ' 0%, ' . $keys[1] . '  ' . $keys[2] . $keys[3] . ')';
                        }

                        fwrite($file, print_r($result, true) . PHP_EOL);
                        $xprt[$mvalue['name']] = $result;

                    } else
                    if (isset($mvalue['type']) && ($mvalue['type'] == "contener_position")) {
                        $result = [];
                        $fields_value = Tools::jsonDecode(stripslashes(Configuration::get('xprt' . $mvalue['name'])));
                        fwrite($file, print_r($fields_value, true) . PHP_EOL);
                        $result['position'] = $fields_value->position;

                        if ($fields_value->position == 'absolute' || $fields_value->position == 'fixed') {

                            if ($fields_value->positions[0] != '') {
                                $result['top'] = $fields_value->positions[0];
                            }

                            if ($fields_value->positions[1] != '') {
                                $result['right'] = $fields_value->positions[1];
                            }

                            if ($fields_value->positions[2] != '') {
                                $result['bottom'] = $fields_value->positions[2];
                            }

                            if ($fields_value->positions[3] != '') {
                                $result['left'] = $fields_value->positions[3];
                            }

                        }

                        fwrite($file, print_r($result, true) . PHP_EOL);
                        $xprt[$mvalue['name']] = $result;

                    }  else
                    if (isset($mvalue['type']) && ($mvalue['type'] == "contener_border")) {
                        $result = [];
                        $fields_value = Tools::jsonDecode(stripslashes(Configuration::get('xprt' . $mvalue['name'])));
                        
                        if ($fields_value->type != 'none') {
							$result[$fields_value->type] = $fields_value->size.' '.$fields_value->style.' '.$fields_value->color;
							$xprt[$mvalue['name']] = $result;
                        }


                    } else {
                        $xprt[$mvalue['name']] = Configuration::get('xprt' . $mvalue['name']);
                    }

                }

            }

        }


        
        return $xprt;
    }


    public function displayMenuForm() {

        $context = Context::getContext();
        $menus = TopMenu::getMenus($context->cookie->id_lang, false);

        $cms = CMS::listCms((int) $context->cookie->id_lang);
        $cmsNestedCategories = $this->getNestedCmsCategories((int) $context->cookie->id_lang);
        $cmsCategories = [];

        foreach ($cmsNestedCategories as $cmsCategory) {
            $cmsCategory['level_depth'] = (int) $cmsCategory['level_depth'];
            $cmsCategories[] = $cmsCategory;
            $this->getChildrenCmsCategories($cmsCategories, $cmsCategory, null);
        }

        $alreadyDefinedCurrentIdMenu = $context->smarty->getTemplateVars('current_id_topmenu');

        if (empty($alreadyDefinedCurrentIdMenu)) {
            $currentIdMenu = Tools::getValue('id_topmenu', false);
        } else {
            $currentIdMenu = $alreadyDefinedCurrentIdMenu;
        }

        $ObjEphenyxTopMenuClass = false;
        $ObjEphenyxTopMenuColumnWrapClass = false;
        $ObjtopMenuColumn = false;
        $ObjEphenyxTopMenuElementsClass = false;

        if (!Tools::getValue('editColumnWrap') && !Tools::getValue('editColumn') && !Tools::getValue('editElement')) {

            if (Tools::getValue('editMenu') && Tools::getValue('id_topmenu')) {
                $ObjEphenyxTopMenuClass = new TopMenu(Tools::getValue('id_topmenu'));
            }

        }

        if (!Tools::getValue('editMenu') && !Tools::getValue('editColumn') && !Tools::getValue('editElement')) {

            if (Tools::getValue('editColumnWrap') && Tools::getValue('id_topmenu_columns_wrap')) {
                $ObjEphenyxTopMenuColumnWrapClass = new TopMenuColumnWrap(Tools::getValue('id_topmenu_columns_wrap'));
            }

        }

        if (!Tools::getValue('editMenu') && !Tools::getValue('editColumnWrap') && !Tools::getValue('editElement')) {

            if (Tools::getValue('editColumn') && Tools::getValue('id_topmenu_column')) {
                $ObjtopMenuColumn = new TopMenuColumn(Tools::getValue('id_topmenu_column'));
                
            }

        }

        if (!Tools::getValue('editMenu') && !Tools::getValue('editColumnWrap') && !Tools::getValue('editColumn')) {

            if (Tools::getValue('editElement') && Tools::getValue('id_topmenu_element')) {
                $ObjEphenyxTopMenuElementsClass = new TopMenuElements(Tools::getValue('id_topmenu_element'));
            }

        }

        $rebuildable_type = [
            3,
            4,
            5,
            10,
        ];
        //$tpl = $this->context->smarty->createTemplate('controllers/top_menu/tabs/display_form.tpl' , $context->smarty);

        $vars = [
            'menus'                   => $menus,
            'rebuildable_type'        => $this->rebuildable_type,
            'current_id_topmenu'      => $currentIdMenu,
            'displayTabElement'       => (!Tools::getValue('editColumnWrap') && !Tools::getValue('editColumn') && !Tools::getValue('editElement')),
            'displayColumnElement'    => (!Tools::getValue('editMenu') && !Tools::getValue('editColumn') && !Tools::getValue('editElement')),
            'displayGroupElement'     => (!Tools::getValue('editMenu') && !Tools::getValue('editColumnWrap') && !Tools::getValue('editElement')),
            'displayItemElement'      => (!Tools::getValue('editMenu') && !Tools::getValue('editColumnWrap') && !Tools::getValue('editColumn')),
            'editMenu'                => (Tools::getValue('editMenu') && Tools::getValue('id_topmenu')),
            'editColumn'              => (Tools::getValue('editColumnWrap') && Tools::getValue('id_topmenu_columns_wrap')),
            'editGroup'               => (Tools::getValue('editColumn') && Tools::getValue('id_topmenu_column')),
            'editElement'             => (Tools::getValue('editElement') && Tools::getValue('id_topmenu_element')),
            'cms'                     => $cms,
            'cmsCategories'           => $cmsCategories,
            'linkTopMenu'             => $context->link->getAdminLink('AdminTopMenu'),
            'ObjTopMenu'              => $ObjEphenyxTopMenuClass,
            'ObjTopMenuColumnWrap'    => $ObjEphenyxTopMenuColumnWrapClass,
            'ObjTopMenuColumn'        => $ObjtopMenuColumn,
            'ObjTopMenuElements'      => $ObjEphenyxTopMenuElementsClass,
            'linkTopMenu'             => $context->link->getAdminLink('AdminTopMenu'),
            'menu_img_dir'            => '/content/themes/'.$context->employee->bo_theme.'/img/topmenu/',
            'current_iso_lang'        => Language::getIsoById($this->context->cookie->id_lang),
            'current_id_lang'         => (int) $this->context->language->id,
            'default_language'        => (int) Configuration::get('EPH_LANG_DEFAULT'),
            'languages'               => Language::getLanguages(false),
            'shopFeatureActive'       => false,
        ];

        return $vars;
    }

    public function getNestedCmsCategories($id_lang) {

        $nestedArray = [];
        $cmsCategories = Db::getInstance(_EPH_USE_SQL_SLAVE_)->executeS(
            'SELECT cc.*, ccl.*
            FROM `' . _DB_PREFIX_ . 'cms_category` cc
            LEFT JOIN `' . _DB_PREFIX_ . 'cms_category_lang` ccl ON cc.`id_cms_category` = ccl.`id_cms_category`
            WHERE ccl.`id_lang` = ' . (int) $id_lang . '
            AND cc.`id_parent` != 0
            ORDER BY cc.`level_depth` ASC, cc.`position` ASC'
        );
        $buff = [];

        foreach ($cmsCategories as $row) {
            $current = &$buff[$row['id_cms_category']];
            $current = $row;

            if (!$row['active']) {
                $current['name'] .= ' ' . '(disabled)';
            }

            if ((int) $row['id_parent'] == 1) {
                $nestedArray[$row['id_cms_category']] = &$current;
            } else {
                $buff[$row['id_parent']]['children'][$row['id_cms_category']] = &$current;
            }

        }

        return $nestedArray;
    }

    public function getChildrenCmsCategories(&$cmsList, $cmsCategory, $levelDepth = false) {

        if (isset($cmsCategory['children']) && self::isFilledArray($cmsCategory['children'])) {

            foreach ($cmsCategory['children'] as $cmsInformation) {
                $cmsInformation['level_depth'] = (int) $cmsInformation['level_depth'];
                $cmsList[] = $cmsInformation;
                $this->getChildrenCmsCategories($cmsList, $cmsInformation, ($levelDepth !== false ? $levelDepth + 1 : $levelDepth));
            }

        }

    }

    public static function isFilledArray($array) {

        return $array && is_array($array) && count($array);
    }

    public function displayConfig() {

        if (!isset($this->_fieldsOptions) or !count($this->_fieldsOptions)) {
            return;
        }
		$file = fopen("testdisplayConfig.txt","w");
        if (version_compare(_EPH_VERSION_, '1.7.0.0', '<') && isset($this->_fieldsOptions['EPHTM_MENU_CONT_HOOK'])) {
            unset($this->_fieldsOptions['EPHTM_MENU_CONT_HOOK']['list'][2]);
        }

        $fieldsOptions = $this->_fieldsOptions;
        $fieldsMobile = [];
        $fieldsAdvance = [];

        foreach ($fieldsOptions as $key => $field) {

            if (isset($field['mobile']) && $field['mobile']) {
                $fieldsMobile[] = $field;
                unset($fieldsOptions[$key]);
            }

            if (isset($field['advanced']) && $field['advanced']) {
                $fieldsAdvance[] = $field;
                unset($fieldsOptions[$key]);
            }

        }

        $languages = Language::getLanguages(false);

        foreach ($fieldsOptions as $key => &$field) {

            $val = Tools::getValue($key, Configuration::get($key));
            $field['title'] = html_entity_decode($field['title']);

            switch ($field['type']) {
            case 'select':

                foreach ($field['list'] as &$value) {
                    $value[$field['identifier']] = (isset($field['cast']) ? $field['cast']($value[$field['identifier']]) : $value[$field['identifier']]);
                    $value['is_selected'] = (($val === false && isset($field['default']) && $field['default'] === $value[$field['identifier']]) || ($val == $value[$field['identifier']]));
                }

                $field['field'] = $field;
                $field['template'] = 'controllers/top_menu/core/form/select.tpl';
                break;
            case 'bool':
                $field['template'] = 'controllers/top_menu/core/form/bool.tpl';
                break;
            case 'textLang':
                $vars['values'] = [];

                foreach ($languages as $language) {
                    $vars['lang_values'][(int) $language['id_lang']] = Tools::getValue($key . '_' . (int) $language['id_lang'], Configuration::get($key, (int) $language['id_lang']));
                }

                $field['template'] = 'controllers/top_menu/core/form/input_text_lang.tpl';
                break;
            case 'color':
                $field['template'] = 'controllers/top_menu/core/form/input_color.tpl';
                break;
            case 'gradient':

                if (!is_array($val)) {
                    $val = explode('-', $val);
                }

                $vars['color1'] = $val[0];
                $vars['color2'] = null;

                if (isset($val[1])) {
                    $vars['color2'] = $val[1];
                }

                $field['template'] = 'controllers/top_menu/core/form/input_gradient_color.tpl';
                break;
            case '4size':
				fwrite($file,"4size".PHP_EOL);
				fwrite($file,$key.PHP_EOL);
				fwrite($file, print_r($val, true));
                $vars['borders_size_tab'] = null;

                if ($val || (isset($field['default']) && $field['default'])) {
                    $borders_size_tab = ($val !== false ? $val : $field['default']);

                    if (!is_array($borders_size_tab)) {
                        $borders_size_tab = explode(' ', $borders_size_tab);
                    }

                    if (is_array($borders_size_tab)) {

                        foreach ($borders_size_tab as &$borderValue) {

                            if ($borderValue == '' || $borderValue == 'unset') {
                                $borderValue = '';
                            } else

                            if ($borderValue != 'auto') {
                                $borderValue = (int) preg_replace('#px#', '', $borderValue);
                            }

                        }

                    }

                    $vars['borders_size_tab'] = $borders_size_tab;
                }

                $field['template'] = 'controllers/top_menu/core/form/input_4size.tpl';
                break;
            case '4size_position':
                $vars['borders_size_tab'] = null;

                if ($val || (isset($field['default']) && $field['default'])) {
                    $borders_size_tab = ($val !== false ? $val : $field['default']);

                    if (!is_array($borders_size_tab)) {
                        $borders_size_tab = explode(' ', $borders_size_tab);
                    }

                    if (is_array($borders_size_tab)) {

                        foreach ($borders_size_tab as &$borderValue) {

                            if (Tools::strlen($borderValue)) {
                                $borderValue = (int) preg_replace('#px#', '', $borderValue);
                            } else {
                                $borderValue = '';
                            }

                        }

                    }

                    $vars['borders_size_tab'] = $borders_size_tab;
                }

                $field['template'] = 'controllers/top_menu/core/form/input_4size_position.tpl';
                break;
            case 'image':
                $field['template'] = 'controllers/top_menu/core/form/input_image.tpl';
                break;
            case 'shadow':
                $vars['borders_size_tab'] = null;

                if ($val || (isset($field['default']) && $field['default'])) {
                    $borders_size_tab = ($val !== false ? $val : @$field['default']);

                    if (!is_array($borders_size_tab)) {
                        $borders_size_tab = explode(' ', $borders_size_tab);
                    }

                    if (is_array($borders_size_tab)) {

                        foreach ($borders_size_tab as &$borderValue) {

                            if (Tools::strlen($borderValue)) {
                                $borderValue = (int) preg_replace('#px#', '', $borderValue);
                            } else {
                                $borderValue = 0;
                            }

                        }

                    }

                    $vars['borders_size_tab'] = $borders_size_tab;
                }

                $field['template'] = 'controllers/top_menu/core/form/input_shadow.tpl';
                break;
            case 'slider':
                $field['template'] = 'controllers/top_menu/core/form/slider.tpl';
                break;
            case 'text':
            default:
                $field['template'] = 'controllers/top_menu/core/form/input_text.tpl';
            }

        }

        return $fieldsOptions;
    }

    public function displayMobileConfig() {

        if (!isset($this->_fieldsOptions) or !count($this->_fieldsOptions)) {
            return;
        }

        $fieldsOptions = $this->_fieldsOptions;

        foreach ($fieldsOptions as $key => $field) {

            if (!isset($field['mobile']) || isset($field['mobile']) && !$field['mobile']) {
                unset($fieldsOptions[$key]);
            } else

            if (!empty($field['mobile']) && version_compare(_EPH_VERSION_, '1.7.0.0', '<') && $key == 'EPHTM_RESP_TOGGLE_ENABLED') {
                unset($fieldsOptions[$key]);
            } else

            if (!empty($field['advanced']) && version_compare(_EPH_VERSION_, '1.7.0.0', '<') && $key == 'EPHTM_MENU_HAMBURGER_SELECTORS') {
                unset($fieldsOptions[$key]);
            }

        }

        $languages = Language::getLanguages(false);

        foreach ($fieldsOptions as $key => &$field) {

            $val = Tools::getValue($key, Configuration::get($key));
            $field['title'] = html_entity_decode($field['title']);

            switch ($field['type']) {
            case 'select':

                foreach ($field['list'] as &$value) {
                    $value[$field['identifier']] = (isset($field['cast']) ? $field['cast']($value[$field['identifier']]) : $value[$field['identifier']]);
                    $value['is_selected'] = (($val === false && isset($field['default']) && $field['default'] === $value[$field['identifier']]) || ($val == $value[$field['identifier']]));
                }

                $field['field'] = $field;
                $field['template'] = 'controllers/top_menu/core/form/select.tpl';
                break;
            case 'bool':
                $field['template'] = 'controllers/top_menu/core/form/bool.tpl';
                break;
            case 'textLang':
                $vars['values'] = [];

                foreach ($languages as $language) {
                    $vars['lang_values'][(int) $language['id_lang']] = Tools::getValue($key . '_' . (int) $language['id_lang'], Configuration::get($key, (int) $language['id_lang']));
                }

                $field['template'] = 'controllers/top_menu/core/form/input_text_lang.tpl';
                break;
            case 'color':
                $field['template'] = 'controllers/top_menu/core/form/input_color.tpl';
                break;
            case 'gradient':

                if (!is_array($val)) {
                    $val = explode('-', $val);
                }

                $vars['color1'] = $val[0];
                $vars['color2'] = null;

                if (isset($val[1])) {
                    $vars['color2'] = $val[1];
                }

                $field['template'] = 'controllers/top_menu/core/form/input_gradient_color.tpl';
                break;
            case '4size':
                $vars['borders_size_tab'] = null;

                if ($val || (isset($field['default']) && $field['default'])) {
                    $borders_size_tab = ($val !== false ? $val : $field['default']);

                    if (!is_array($borders_size_tab)) {
                        $borders_size_tab = explode(' ', $borders_size_tab);
                    }

                    if (is_array($borders_size_tab)) {

                        foreach ($borders_size_tab as &$borderValue) {

                            if ($borderValue == '' || $borderValue == 'unset') {
                                $borderValue = '';
                            } else

                            if ($borderValue != 'auto') {
                                $borderValue = (int) preg_replace('#px#', '', $borderValue);
                            }

                        }

                    }

                    $vars['borders_size_tab'] = $borders_size_tab;
                }

                $field['template'] = 'controllers/top_menu/core/form/input_4size.tpl';
                break;
            case '4size_position':
                $vars['borders_size_tab'] = null;

                if ($val || (isset($field['default']) && $field['default'])) {
                    $borders_size_tab = ($val !== false ? $val : $field['default']);

                    if (!is_array($borders_size_tab)) {
                        $borders_size_tab = explode(' ', $borders_size_tab);
                    }

                    if (is_array($borders_size_tab)) {

                        foreach ($borders_size_tab as &$borderValue) {

                            if (Tools::strlen($borderValue)) {
                                $borderValue = (int) preg_replace('#px#', '', $borderValue);
                            } else {
                                $borderValue = '';
                            }

                        }

                    }

                    $vars['borders_size_tab'] = $borders_size_tab;
                }

                $field['template'] = 'controllers/top_menu/core/form/input_4size_position.tpl';
                break;
            case 'image':
                $field['template'] = 'controllers/top_menu/core/form/input_image.tpl';
                break;
            case 'shadow':
                $vars['borders_size_tab'] = null;

                if ($val || (isset($field['default']) && $field['default'])) {
                    $borders_size_tab = ($val !== false ? $val : @$field['default']);

                    if (!is_array($borders_size_tab)) {
                        $borders_size_tab = explode(' ', $borders_size_tab);
                    }

                    if (is_array($borders_size_tab)) {

                        foreach ($borders_size_tab as &$borderValue) {

                            if (Tools::strlen($borderValue)) {
                                $borderValue = (int) preg_replace('#px#', '', $borderValue);
                            } else {
                                $borderValue = 0;
                            }

                        }

                    }

                    $vars['borders_size_tab'] = $borders_size_tab;
                }

                $field['template'] = 'controllers/top_menu/core/form/input_shadow.tpl';
                break;
            case 'slider':
                $field['template'] = 'controllers/top_menu/core/form/slider.tpl';
                break;
            case 'text':
            default:
                $field['template'] = 'controllers/top_menu/core/form/input_text.tpl';
            }

        }

        return $fieldsOptions;
    }

    protected function displayAdvancedConfig() {

        $fieldsOptions = $this->_fieldsOptions;

        foreach ($fieldsOptions as $key => $field) {

            if (!isset($field['advanced']) || isset($field['advanced']) && !$field['advanced']) {
                unset($fieldsOptions[$key]);
            }

        }

        $languages = Language::getLanguages(false);

        foreach ($fieldsOptions as $key => &$field) {

            $val = Tools::getValue($key, Configuration::get($key));
            $field['title'] = html_entity_decode($field['title']);

            switch ($field['type']) {
            case 'select':

                foreach ($field['list'] as &$value) {
                    $value[$field['identifier']] = (isset($field['cast']) ? $field['cast']($value[$field['identifier']]) : $value[$field['identifier']]);
                    $value['is_selected'] = (($val === false && isset($field['default']) && $field['default'] === $value[$field['identifier']]) || ($val == $value[$field['identifier']]));
                }

                $field['field'] = $field;
                $field['template'] = 'controllers/top_menu/core/form/select.tpl';
                break;
            case 'bool':
                $field['template'] = 'controllers/top_menu/core/form/bool.tpl';
                break;
            case 'textLang':
                $vars['values'] = [];

                foreach ($languages as $language) {
                    $vars['lang_values'][(int) $language['id_lang']] = Tools::getValue($key . '_' . (int) $language['id_lang'], Configuration::get($key, (int) $language['id_lang']));
                }

                $field['template'] = 'controllers/top_menu/core/form/input_text_lang.tpl';
                break;
            case 'color':
                $field['template'] = 'controllers/top_menu/core/form/input_color.tpl';
                break;
            case 'gradient':

                if (!is_array($val)) {
                    $val = explode('-', $val);
                }

                $vars['color1'] = $val[0];
                $vars['color2'] = null;

                if (isset($val[1])) {
                    $vars['color2'] = $val[1];
                }

                $field['template'] = 'controllers/top_menu/core/form/input_gradient_color.tpl';
                break;
            case '4size':
                $vars['borders_size_tab'] = null;

                if ($val || (isset($field['default']) && $field['default'])) {
                    $borders_size_tab = ($val !== false ? $val : $field['default']);

                    if (!is_array($borders_size_tab)) {
                        $borders_size_tab = explode(' ', $borders_size_tab);
                    }

                    if (is_array($borders_size_tab)) {

                        foreach ($borders_size_tab as &$borderValue) {

                            if ($borderValue == '' || $borderValue == 'unset') {
                                $borderValue = '';
                            } else

                            if ($borderValue != 'auto') {
                                $borderValue = (int) preg_replace('#px#', '', $borderValue);
                            }

                        }

                    }

                    $vars['borders_size_tab'] = $borders_size_tab;
                }

                $field['template'] = 'controllers/top_menu/core/form/input_4size.tpl';
                break;
            case '4size_position':
                $vars['borders_size_tab'] = null;

                if ($val || (isset($field['default']) && $field['default'])) {
                    $borders_size_tab = ($val !== false ? $val : $field['default']);

                    if (!is_array($borders_size_tab)) {
                        $borders_size_tab = explode(' ', $borders_size_tab);
                    }

                    if (is_array($borders_size_tab)) {

                        foreach ($borders_size_tab as &$borderValue) {

                            if (Tools::strlen($borderValue)) {
                                $borderValue = (int) preg_replace('#px#', '', $borderValue);
                            } else {
                                $borderValue = '';
                            }

                        }

                    }

                    $vars['borders_size_tab'] = $borders_size_tab;
                }

                $field['template'] = 'controllers/top_menu/core/form/input_4size_position.tpl';
                break;
            case 'image':
                $field['template'] = 'controllers/top_menu/core/form/input_image.tpl';
                break;
            case 'shadow':
                $vars['borders_size_tab'] = null;

                if ($val || (isset($field['default']) && $field['default'])) {
                    $borders_size_tab = ($val !== false ? $val : @$field['default']);

                    if (!is_array($borders_size_tab)) {
                        $borders_size_tab = explode(' ', $borders_size_tab);
                    }

                    if (is_array($borders_size_tab)) {

                        foreach ($borders_size_tab as &$borderValue) {

                            if (Tools::strlen($borderValue)) {
                                $borderValue = (int) preg_replace('#px#', '', $borderValue);
                            } else {
                                $borderValue = 0;
                            }

                        }

                    }

                    $vars['borders_size_tab'] = $borders_size_tab;
                }

                $field['template'] = 'controllers/top_menu/core/form/input_shadow.tpl';
                break;
            case 'slider':
                $field['template'] = 'controllers/top_menu/core/form/slider.tpl';
                break;
            case 'text':
            default:
                $field['template'] = 'controllers/top_menu/core/form/input_text.tpl';
            }

        }

        return $fieldsOptions;
    }

    public function getAdminWrapOutputPrivacyValue($id_wrapper) {

        $privacy = TopMenuWrap::getWrapperPrivacy($id_wrapper);
        $vars = [
            'privacy' => $privacy,
        ];
        return $this->fetchTemplate('form_components/privacy.tpl', $vars);
    }

    public function outputSelectColumnsWrap($id_topmenu = false, $columnWrap_selected = false) {

        $columnsWrap = TopMenuColumnWrap::getMenuColumnsWrap((int) $id_topmenu, $this->context->cookie->id_lang, false);

        $data = $this->createTemplate('controllers/top_menu/columnwrap_select.tpl');
        $data->assign(
            [
                'columnsWrap'         => $columnsWrap,
                'columnWrap_selected' => $columnWrap_selected,
            ]);
        return $data->fetch();
    }

    public function outputFormItem($key, $field) {

        $languages = Language::getLanguages(false);
        $val = Tools::getValue($key, Configuration::get($key));
        $field['title'] = html_entity_decode($field['title']);
        $vars = [
            'val'   => $val,
            'key'   => $key,
            'field' => $field,
        ];

        switch ($field['type']) {
        case 'select':

            foreach ($field['list'] as &$value) {
                $value[$field['identifier']] = (isset($field['cast']) ? $field['cast']($value[$field['identifier']]) : $value[$field['identifier']]);
                $value['is_selected'] = (($val === false && isset($field['default']) && $field['default'] === $value[$field['identifier']]) || ($val == $value[$field['identifier']]));
            }

            $vars['field'] = $field;
            return $this->fetchTemplate('core/form/select.tpl', $vars);
        case 'bool':
            return $this->fetchTemplate('core/form/bool.tpl', $vars);
        case 'textLang':
            $vars['values'] = [];

            foreach ($languages as $language) {
                $vars['lang_values'][(int) $language['id_lang']] = Tools::getValue($key . '_' . (int) $language['id_lang'], Configuration::get($key, (int) $language['id_lang']));
            }

            return $this->fetchTemplate('core/form/input_text_lang.tpl', $vars);
        case 'color':
            return $this->fetchTemplate('core/form/input_color.tpl', $vars);
        case 'gradient':

            if (!is_array($val)) {
                $val = explode('-', $val);
            }

            $vars['color1'] = $val[0];
            $vars['color2'] = null;

            if (isset($val[1])) {
                $vars['color2'] = $val[1];
            }

            return $this->fetchTemplate('core/form/input_gradient_color.tpl', $vars);
        case '4size':
            $vars['borders_size_tab'] = null;

            if ($val || (isset($field['default']) && $field['default'])) {
                $borders_size_tab = ($val !== false ? $val : $field['default']);

                if (!is_array($borders_size_tab)) {
                    $borders_size_tab = explode(' ', $borders_size_tab);
                }

                if (is_array($borders_size_tab)) {

                    foreach ($borders_size_tab as &$borderValue) {

                        if ($borderValue == '' || $borderValue == 'unset') {
                            $borderValue = '';
                        } else

                        if ($borderValue != 'auto') {
                            $borderValue = (int) preg_replace('#px#', '', $borderValue);
                        }

                    }

                }

                $vars['borders_size_tab'] = $borders_size_tab;
            }

            return $this->fetchTemplate('core/form/input_4size.tpl', $vars);
        case '4size_position':
            $vars['borders_size_tab'] = null;

            if ($val || (isset($field['default']) && $field['default'])) {
                $borders_size_tab = ($val !== false ? $val : $field['default']);

                if (!is_array($borders_size_tab)) {
                    $borders_size_tab = explode(' ', $borders_size_tab);
                }

                if (is_array($borders_size_tab)) {

                    foreach ($borders_size_tab as &$borderValue) {

                        if (Tools::strlen($borderValue)) {
                            $borderValue = (int) preg_replace('#px#', '', $borderValue);
                        } else {
                            $borderValue = '';
                        }

                    }

                }

                $vars['borders_size_tab'] = $borders_size_tab;
            }

            return $this->fetchTemplate('core/form/input_4size_position.tpl', $vars);
        case 'image':
            return $this->fetchTemplate('core/form/input_image.tpl', $vars);
        case 'shadow':
            $vars['borders_size_tab'] = null;

            if ($val || (isset($field['default']) && $field['default'])) {
                $borders_size_tab = ($val !== false ? $val : @$field['default']);

                if (!is_array($borders_size_tab)) {
                    $borders_size_tab = explode(' ', $borders_size_tab);
                }

                if (is_array($borders_size_tab)) {

                    foreach ($borders_size_tab as &$borderValue) {

                        if (Tools::strlen($borderValue)) {
                            $borderValue = (int) preg_replace('#px#', '', $borderValue);
                        } else {
                            $borderValue = 0;
                        }

                    }

                }

                $vars['borders_size_tab'] = $borders_size_tab;
            }

            return $this->fetchTemplate('core/form/input_shadow.tpl', $vars);
        case 'slider':
            return $this->fetchTemplate('core/form/slider.tpl', $vars);
        case 'text':
        default:
            return $this->fetchTemplate('core/form/input_text.tpl', $vars);
        }

    }

    public function getAdminOutputNameValue($row, $withExtra = true, $type = false, $id = null) {

        $return = '';
        $context = Context::getContext();
        $_iso_lang = Language::getIsoById($context->cookie->id_lang);

        if ($row['type'] == 10) {
            return 'Hook Cart';
        } else

        if ($row['type'] == 11) {
            return 'Hook Search';
        } else

        if ($row['type'] == 12) {
            return 'Custom Hook';
        }

        if ($id > 0) {

            if ($withExtra && trim($row['have_icon'])) {

                if (in_array($row['image_type'], ['i-fa', 'i-mi'])) {

                    if ($row['image_type'] == 'i-mi') {
                        $row['image_class'] = 'zmdi ' . $row['image_class'];
                    }

                    $return .= '<i class="pmAtmIcon ' . $row['image_class'] . '"></i>';
                } else {
                    $return .= '<img src="' . _EPH_IMG_ . $type . '_icons/' . $id . '-' . $_iso_lang . '.' . ($row['image_type'] ?: 'jpg') . '" alt="' . htmlentities($row['name'], ENT_COMPAT, 'UTF-8') . '" title="' . htmlentities($row['name'], ENT_COMPAT, 'UTF-8') . '" />';
                }

            }

            if (trim($row['name'])) {
                $return .= htmlentities($row['name'], ENT_COMPAT, 'UTF-8');
            } else {
                $return .= htmlentities($row['meta_title'], ENT_COMPAT, 'UTF-8');
            }

            return $return;
        }

        if ($row['type'] == 1) {

            if ($withExtra && trim($row['have_icon'])) {

                if (in_array($row['image_type'], ['i-fa', 'i-mi'])) {

                    if ($row['image_type'] == 'i-mi') {
                        $row['image_class'] = 'zmdi ' . $row['image_class'];
                    }

                    $return .= '<i class="pmAtmIcon ' . $row['image_class'] . '"></i>';
                } else {
                    $return .= '<img src="' . _EPH_IMG_ . $type . '_icons/' . $row['id_top' . $type] . '-' . $_iso_lang . '.' . ($row['image_type'] ?: 'jpg') . '" alt="' . htmlentities($row['name'], ENT_COMPAT, 'UTF-8') . '" title="' . htmlentities($row['name'], ENT_COMPAT, 'UTF-8') . '" />';
                }

            }

            if (trim($row['name'])) {
                $return .= htmlentities($row['name'], ENT_COMPAT, 'UTF-8');
            } else {
                $return .= htmlentities($row['meta_title'], ENT_COMPAT, 'UTF-8');
            }

        } else

        if ($row['type'] == 2) {

            if ($withExtra && trim($row['have_icon'])) {

                if (in_array($row['image_type'], ['i-fa', 'i-mi'])) {

                    if ($row['image_type'] == 'i-mi') {
                        $row['image_class'] = 'zmdi ' . $row['image_class'];
                    }

                    $return .= '<i class="pmAtmIcon ' . $row['image_class'] . '"></i>';
                } else {
                    $return .= '<img src="' . _EPH_IMG_ . $type . '_icons/' . $row['id_top' . $type] . '-' . $_iso_lang . '.' . ($row['image_type'] ?: 'jpg') . '" alt="' . htmlentities($row['name'], ENT_COMPAT, 'UTF-8') . '" title="' . htmlentities($row['name'], ENT_COMPAT, 'UTF-8') . '" />';
                }

            }

            if (trim($row['name'])) {
                $return .= htmlentities($row['name'], ENT_COMPAT, 'UTF-8');
            } else {
                $return .= $this->la('No label');
            }

        } else

        if ($row['type'] == 3) {

            if ($withExtra && trim($row['have_icon'])) {

                if (in_array($row['image_type'], ['i-fa', 'i-mi'])) {

                    if ($row['image_type'] == 'i-mi') {
                        $row['image_class'] = 'zmdi ' . $row['image_class'];
                    }

                    $return .= '<i class="pmAtmIcon ' . $row['image_class'] . '"></i>';
                } else {
                    $return .= '<img src="' . _EPH_IMG_ . $type . '_icons/' . $row['id_top' . $type] . '-' . $_iso_lang . '.' . ($row['image_type'] ?: 'jpg') . '" alt="' . htmlentities($row['name'], ENT_COMPAT, 'UTF-8') . '" title="' . htmlentities($row['name'], ENT_COMPAT, 'UTF-8') . '" />';
                }

            }

            if (trim($row['name'])) {
                $return .= htmlentities($row['name'], ENT_COMPAT, 'UTF-8');
            } else {
                $return .= htmlentities($row['category_name'], ENT_COMPAT, 'UTF-8');
            }

        } else

      
        if ($row['type'] == 6) {

            if ($withExtra && trim($row['have_icon'])) {

                if (in_array($row['image_type'], ['i-fa', 'i-mi'])) {

                    if ($row['image_type'] == 'i-mi') {
                        $row['image_class'] = 'zmdi ' . $row['image_class'];
                    }

                    $return .= '<i class="pmAtmIcon ' . $row['image_class'] . '"></i>';
                } else {
                    $return .= '<img src="' . _EPH_IMG_ . $type . '_icons/' . $row['id_top' . $type] . '-' . $_iso_lang . '.' . ($row['image_type'] ?: 'jpg') . '" alt="' . htmlentities($row['name'], ENT_COMPAT, 'UTF-8') . '" title="' . htmlentities($row['name'], ENT_COMPAT, 'UTF-8') . '" />';
                }

            }

            if (trim($row['name'])) {
                $return .= htmlentities($row['name'], ENT_COMPAT, 'UTF-8');
            } else {
                $return .= $this->la('No label');
            }

        } else

        if ($row['type'] == 7) {

            if ($withExtra && trim($row['have_icon'])) {

                if (in_array($row['image_type'], ['i-fa', 'i-mi'])) {

                    if ($row['image_type'] == 'i-mi') {
                        $row['image_class'] = 'zmdi ' . $row['image_class'];
                    }

                    $return .= '<i class="pmAtmIcon ' . $row['image_class'] . '"></i>';
                } else {
                    $return .= '<img src="' . _EPH_IMG_ . $type . '_icons/' . $row['id_topmenu_' . $type] . '-' . $_iso_lang . '.' . ($row['image_type'] ?: 'jpg') . '" alt="' . htmlentities($row['name'], ENT_COMPAT, 'UTF-8') . '" title="' . htmlentities($row['name'], ENT_COMPAT, 'UTF-8') . '" />';
                }

            }

            $return .= 'No label';
        } else

        if ($row['type'] == 9) {

            if (!trim($row['name'])) {
                $page = Meta::getMetaByPage($row['id_specific_page'], (int) $context->cookie->id_lang);
                $row['name'] = (!empty($page['title']) ? $page['title'] : $page['page']);
            }

            if ($withExtra && trim($row['have_icon'])) {
                $return .= '<img src="' . _EPH_IMG_ . $type . '_icons/' . $row['id_top' . $type] . '-' . $_iso_lang . '.' . ($row['image_type'] ?: 'jpg') . '" alt="' . htmlentities($row['name'], ENT_COMPAT, 'UTF-8') . '" title="' . htmlentities($row['name'], ENT_COMPAT, 'UTF-8') . '" />';
            } else {
                $return .= htmlentities($row['name'], ENT_COMPAT, 'UTF-8');
            }

        } else

        if ($row['type'] == 10) {

            if (!trim($row['name'])) {
                $cmsCategory = new CMSCategory((int) $row['id_cms_category']);
                $row['name'] = $cmsCategory->getName((int) $context->cookie->id_lang);
            }

            if ($withExtra && trim($row['have_icon'])) {

                if (in_array($row['image_type'], ['i-fa', 'i-mi'])) {

                    if ($row['image_type'] == 'i-mi') {
                        $row['image_class'] = 'zmdi ' . $row['image_class'];
                    }

                    $return .= '<i class="pmAtmIcon ' . $row['image_class'] . '"></i>';
                    $return .= htmlentities($row['name'], ENT_COMPAT, 'UTF-8');
                } else {
                    $return .= '<img src="' . _EPH_IMG_ . $type . '_icons/' . $row['id_' . $type] . '-' . $_iso_lang . '.' . ($row['image_type'] ?: 'jpg') . '" alt="' . htmlentities($row['name'], ENT_COMPAT, 'UTF-8') . '" title="' . htmlentities($row['name'], ENT_COMPAT, 'UTF-8') . '" />';
                }

            } else {
                $return .= htmlentities($row['name'], ENT_COMPAT, 'UTF-8');
            }

        }

        return $return;
    }

    public function outputCategoriesSelect($object) {

        $rootCategoryId = Category::getRootCategory()->id;

        $selected = ($object ? $object->id_category : 0);
        $categoryList = [];
        $context = Context::getContext();

        foreach ($this->getNestedCategories($rootCategoryId, $context->cookie->id_lang) as $idCategory => $categoryInformations) {

            if ($rootCategoryId != $idCategory) {
                $categoryList[] = $categoryInformations;
            }

            $this->getChildrensCategories($categoryList, $categoryInformations, $selected);
        }

        $vars = [
            'categoryList' => $categoryList,
            'selected'     => $selected,
        ];
        return $this->fetchTemplate('form_components/category_select.tpl', $vars);
    }

    public function getNestedCategories($root_category = null, $id_lang = false) {

        $result = Db::getInstance(_EPH_USE_SQL_SLAVE_)->executeS(
            'SELECT c.*, cl.*
            FROM `' . _DB_PREFIX_ . 'category` c
            LEFT JOIN `' . _DB_PREFIX_ . 'category_lang` cl ON c.`id_category` = cl.`id_category`
            RIGHT JOIN `' . _DB_PREFIX_ . 'category` c2 ON c2.`id_category` = ' . (int) $root_category . ' AND c.`nleft` >= c2.`nleft` AND c.`nright` <= c2.`nright`
            WHERE `id_lang` = ' . (int) $id_lang . '
            ORDER BY c.`level_depth` ASC, c.`position` ASC'
        );
        $categories = [];
        $buff = [];

        foreach ($result as $row) {
            $current = &$buff[$row['id_category']];
            $current = $row;

            if (!$row['active']) {
                $current['name'] .= ' ' . $this->la('(disabled)');
            }

            if ($row['id_category'] == $root_category) {
                $categories[$row['id_category']] = &$current;
            } else {
                $buff[$row['id_parent']]['children'][$row['id_category']] = &$current;
            }

        }

        return $categories;
    }

    public function getChildrensCategories(&$categoryList, $categoryInformations, $selected, $levelDepth = false) {

        if (isset($categoryInformations['children']) && self::isFilledArray($categoryInformations['children'])) {

            foreach ($categoryInformations['children'] as $categoryInformations) {
                $categoryList[] = $categoryInformations;
                $this->getChildrensCategories($categoryList, $categoryInformations, $selected, ($levelDepth !== false ? $levelDepth + 1 : $levelDepth));
            }

        }

    }

    public function getType($type) {

        if ($type == 1) {
            return $this->la('CMS');
        } else

        if ($type == 2) {
            return $this->la('Link');
        } else

        if ($type == 3) {
            return $this->la('Category');
        } else

        

        if ($type == 6) {
            return $this->la('Search');
        } else

        if ($type == 7) {
            return $this->la('Only image or icon');
        } else

        if ($type == 9) {
            return $this->la('Specific page');
        } else

        if ($type == 10) {
            return $this->la('CMS category');
        }

    }

    public function outputTargetSelect($object) {

        $vars = [
            'link_targets' => $this->link_targets,
            'selected'     => ($object ? $object->target : 0),
        ];
        return $this->fetchTemplate('form_components/target_select.tpl', $vars);
    }

    public function outputCmsCategoriesSelect($cmsCategories, $object) {

        $vars = [
            'cmsCategoriesList' => $cmsCategories,
            'selected'          => ($object ? $object->id_cms_category : 0),
        ];
        return $this->fetchTemplate('form_components/cms_category_select.tpl', $vars);
    }

    public function outputCmsSelect($cmss, $object) {

        $vars = [
            'cmsList'  => $cmss,
            'selected' => ($object ? $object->id_cms : 0),
        ];
        return $this->fetchTemplate('form_components/cms_select.tpl', $vars);
    }

    

    public function outputSpecificPageSelect($object) {

        $pages = Meta::getMetasByIdLang((int) $this->context->cookie->id_lang);
        $default_routes = Performer::getInstance()->default_routes;

        foreach ($pages as $p => $page) {

            if (isset($default_routes[$page['page']]) && is_array($default_routes[$page['page']]['keywords']) && count($default_routes[$page['page']]['keywords'])) {
                unset($pages[$p]);
            } else

            if (isset($default_routes[$page['page']])) {

                if (empty($page['title'])) {
                    $pages[$p]['title'] = $default_routes[$page['page']]['rule'];
                }

            }

        }

        $vars = [
            'pagesList' => $pages,
            'selected'  => ($object ? $object->id_specific_page : 0),
        ];
        return $this->fetchTemplate('form_components/specific_page_select.tpl', $vars);
    }

    public function getAdminOutputPrivacyValue($privacy) {

        $vars = [
            'privacy' => $privacy,
        ];
        return $this->fetchTemplate('form_components/privacy.tpl', $vars);
    }

    public function fetchTemplate($tpl, $customVars = [], $configOptions = []) {

        //$data = $this->createTemplate('controllers/top_menu/' . $tpl);
        $context = Context::getContext();
        $admin_webpath = str_ireplace(_EPH_CORE_DIR_, '', _EPH_ROOT_DIR_);
        $admin_webpath = preg_replace('/^' . preg_quote(DIRECTORY_SEPARATOR, '/') . '/', '', $admin_webpath);

        $tpl = $context->smarty->createTemplate('controllers/top_menu/' . $tpl, $context->smarty);
        $tpl->assign(
            [
                'linkTopMenu'            => $context->link->getAdminLink('AdminTopMenu'),
                'AdminTopMenuController' => new AdminTopMenuController(),
                'menu_img_dir'           => '/content/themes/'.$context->employee->bo_theme.'/img/topmenu/',
                'current_iso_lang'       => Language::getIsoById($context->cookie->id_lang),
                'current_id_lang'        => (int) $context->language->id,
                'default_language'       => (int) Configuration::get('EPH_LANG_DEFAULT'),
                'languages'              => Language::getLanguages(false),
                'options'                => $configOptions,
                'shopFeatureActive'      => false,
            ]
        );

        if (is_array($customVars) && count($customVars)) {
            $tpl->assign($customVars);
        }

        return $tpl->fetch();

        //return $context->smarty->fetch('controllers/top_menu/' . $tpl);
    }

    public function getCategoryList() {

        $rootCategoryId = Category::getRootCategory()->id;

        $categoryList = [];
        $context = Context::getContext();

        foreach ($this->getNestedCategories($rootCategoryId, $context->cookie->id_lang) as $idCategory => $categoryInformations) {

            if ($rootCategoryId != $idCategory) {
                $categoryList[] = $categoryInformations;
            }

            $this->getChildrensCategories($categoryList, $categoryInformations, $selected);
        }

        return $categoryList;
    }

    public function getPageList() {

        $pages = Meta::getMetasByIdLang((int) $this->context->cookie->id_lang);
        $default_routes = Performer::getInstance()->default_routes;

        foreach ($pages as $p => $page) {

            if (isset($default_routes[$page['page']]) && is_array($default_routes[$page['page']]['keywords']) && count($default_routes[$page['page']]['keywords'])) {
                unset($pages[$p]);
            } else

            if (isset($default_routes[$page['page']])) {

                if (empty($page['title'])) {
                    $pages[$p]['title'] = $default_routes[$page['page']]['rule'];
                }

            }

        }

        return $pages;
    }

    public function ajaxProcessOutputMenuForm() {

        $idTopMenu = Tools::getValue('id_topmenu');
        $topMenu = new TopMenu($idTopMenu);
        $imgIconMenuDirIsWritable = is_writable(_EPH_ROOT_DIR_ . '/img/menu_icons');
        $haveDepend = false;
		$ids_lang = 'menuname¤menulink¤menu_value_over¤menu_value_under¤menuimage¤menuimagelegend¤iconPickingButton';
		$img_src = '../img/menu_icons/no-icone.png';		
		
        if ($topMenu->id > 0) {
            $haveDepend = TopMenu::menuHaveDepend($topMenu->id);
			if($topmenu->have_icon) {
				$icone = $topmenu->image_hash;
				
				if (!empty($icone)) {
                    $img_src = $icone;
                } 
			}

        }
        $context = Context::getContext();
        $admin_webpath = str_ireplace(_EPH_CORE_DIR_, '', _EPH_ROOT_DIR_);
        $admin_webpath = preg_replace('/^' . preg_quote(DIRECTORY_SEPARATOR, '/') . '/', '', $admin_webpath);

        $tpl = $this->createTemplate('controllers/top_menu/tabs/display_menu_form.tpl');
        $iso = $this->context->language->iso_code;

        $tpl->assign(
            [
                'ids_lang'                  => $ids_lang,
				'img_src'				    => $img_src,
				'topMenu'                   => $topMenu,
                'haveDepend'                => $haveDepend,
                'imgIconMenuDirIsWritable'  => $imgIconMenuDirIsWritable,
                'fnd_color_menu_tab_color1' => false,
                'fnd_color_menu_tab_color2' => false,
                'categoryList'              => EducationType::getEducationType((int) $this->context->cookie->id_lang),
                'cms'                       => CMS::listCms((int) $this->context->cookie->id_lang),
                'cmsCategories'             => $this->getNestedCmsCategories((int) $this->context->cookie->id_lang),
                'pagesList'                 => $this->getPageList(),
                'link_targets'              => $this->link_targets,
                'iso'                       => file_exists(_EPH_CORE_DIR_ . _EPH_JS_DIR_.'tiny_mce/langs/' . $iso . '.js') ? $iso : 'en',
                'pathCSS'                   => _THEME_CSS_DIR_,
                'ad'                        => __EPH_BASE_URI__ . basename(_EPH_ROOT_DIR_),
                'topMenu_img_dir'           => _EPH_MENU_DIR_,
                'menu_img_dir'              => _EPH_ADMIN_THEME_DIR_.  $this->bo_theme . '/img/topmenu/',
                'current_iso_lang'          => Language::getIsoById($context->cookie->id_lang),
                'current_id_lang'           => (int) $context->language->id,
                'default_language'          => (int) Configuration::get('EPH_LANG_DEFAULT'),
                'languages'                 => Language::getLanguages(false),
                'linkTopMenu'               => $context->link->getAdminLink('AdminTopMenu'),
            ]
        );

        if ($topMenu && $topMenu->fnd_color_menu_tab) {
            $val = explode('-', $topMenu->fnd_color_menu_tab);
            $vars['fnd_color_menu_tab_color1'] = $val[0];

            if (isset($val[1])) {
                $tpl->assign('fnd_color_menu_tab_color2', $val[1]);
            }

        }

        if ($topMenu && $topMenu->fnd_color_menu_tab_over) {
            $val = explode('-', $topMenu->fnd_color_menu_tab_over);
            $tpl->assign('fnd_color_menu_tab_over_color1', $val[0]);

            if (isset($val[1])) {
                $tpl->assign('fnd_color_menu_tab_over_color2', $val[1]);
            }

        } else {
            $tpl->assign('fnd_color_menu_tab_over_color1', false);
            $tpl->assign('fnd_color_menu_tab_over_color2', false);
        }

        $vars['borders_size_tab'] = null;

        if ($topMenu->id > 0) {
            $vars['borders_size_tab'] = explode(' ', $topMenu->border_size_tab);

            if (is_array($vars['borders_size_tab'])) {

                foreach ($vars['borders_size_tab'] as &$borderValue) {
                    $borderValue = (int) preg_replace('#px#', '', $borderValue);
                }

            }

        }

        $vars['fnd_color_submenu_color1'] = false;
        $vars['fnd_color_submenu_color2'] = false;

        if ($topMenu && $topMenu->fnd_color_submenu) {
            $val = explode('-', $topMenu->fnd_color_submenu);
            $vars['fnd_color_submenu_color1'] = $val[0];

            if (isset($val[1])) {
                $vars['fnd_color_submenu_color2'] = $val[1];
            }

        }

        $vars['borders_size_submenu'] = null;

        if ($topMenu->id > 0) {
            $vars['borders_size_submenu'] = explode(' ', $topMenu->border_size_submenu);

            if (is_array($vars['borders_size_submenu'])) {

                foreach ($vars['borders_size_submenu'] as &$borderValue) {
                    $borderValue = (int) preg_replace('#px#', '', $borderValue);
                }

            }

        }

        $vars['hasAdditionnalText'] = false;
        $languages = Language::getLanguages(false);

        foreach ($languages as $language) {

            if ($topMenu && isset($topMenu->value_over[$language['id_lang']]) && !empty($topMenu->value_over[$language['id_lang']]) || isset($topMenu->value_under[$language['id_lang']]) && !empty($topMenu->value_under[$language['id_lang']])) {
                $vars['hasAdditionnalText'] = true;
                break;
            }

        }

        foreach ($vars as $key => $value) {
            $tpl->assign($key, $value);
        }
		$li = '<li id="uperEditAdminTopMenu" data-controller="AdminDashboard"><a href="#contentEditAdminTopMenu">Ajouter ou éditer un onglet</a><button type="button" class="close tabdetail" data-id="uperEditAdminTopMenu"><i class="icon icon-times-circle"></i></button></li>';
		$html = '<div id="contentEditAdminTopMenu" class="panel col-lg-12" style="display; flow-root;">' . $tpl->fetch() . '</div>';

			$result = [
				'success' => true,
				'li'      => $li,
				'html'    => $html,
			];

       
        die(Tools::jsonEncode($result));
    }
    public function ajaxProcessOutputColumnWrapForm() {

        $id_column_wrap = Tools::getValue('id_column_wrap');
        $context = Context::getContext();
        $ObjTopMenuColumnWrap = new TopMenuColumnWrap($id_column_wrap);
        $menus = TopMenu::getMenus($context->cookie->id_lang, false);
        $ids_lang = 'columnwrap_value_over¤columnwrap_value_under';
        $vars = [
            'ids_lang'             => $ids_lang,
            'menus'                => $menus,
            'ObjTopMenuColumnWrap' => $ObjTopMenuColumnWrap,
        ];
        $vars['bg_color_color1'] = false;
        $vars['bg_color_color2'] = false;

        if ($ObjTopMenuColumnWrap && $ObjTopMenuColumnWrap->bg_color) {
            $val = explode('-', $ObjTopMenuColumnWrap->bg_color);
            $vars['bg_color_color1'] = $val[0];

            if (isset($val[1])) {
                $vars['bg_color_color2'] = $val[1];
            }

        }
		
		$vars['borders_size_wrap'] = null;
        if ($ObjTopMenuColumnWrap && $ObjTopMenuColumnWrap->border_size_wrap) {
            $vars['borders_size_wrap'] = explode(' ', $ObjTopMenuColumnWrap->border_size_wrap);
            if (is_array($vars['borders_size_wrap'])) {
                foreach ($vars['borders_size_wrap'] as &$borderValue) {
                    $borderValue = (int)preg_replace('#px#', '', $borderValue);
                }
            }
        }

        $vars['hasAdditionnalText'] = false;
        $languages = Language::getLanguages(false);

        foreach ($languages as $language) {

            if (isset($ObjTopMenuColumnWrap->value_over[$language['id_lang']]) && !empty($ObjTopMenuColumnWrap->value_over[$language['id_lang']]) || isset($ObjTopMenuColumnWrap->value_under[$language['id_lang']]) && !empty($ObjTopMenuColumnWrap->value_under[$language['id_lang']])) {
                $vars['hasAdditionnalText'] = true;
                break;
            }

        }

        $tpl = $this->createTemplate('controllers/top_menu/tabs/display_columnwrap_form.tpl');
        $iso = $this->context->language->iso_code;
        $tpl->assign(
            [
                'linkTopMenu'       => $context->link->getAdminLink('AdminTopMenu'),
                'menu_img_dir'      => '/content/themes/'.$context->employee->bo_theme.'/img/topmenu/',
                'current_iso_lang'  => Language::getIsoById($context->cookie->id_lang),
                'current_id_lang'   => (int) $context->language->id,
                'default_language'  => (int) Configuration::get('EPH_LANG_DEFAULT'),
                'languages'         => Language::getLanguages(false),
                'shopFeatureActive' => false,
                'groups'            => Group::getGroups((int) $this->context->cookie->id_lang),
                'iso'               => file_exists(_EPH_CORE_DIR_ . _EPH_JS_DIR_.'tiny_mce/langs/' . $iso . '.js') ? $iso : 'en',
                'pathCSS'           => _THEME_CSS_DIR_,
                'ad'                => __EPH_BASE_URI__ . basename(_EPH_ROOT_DIR_),
            ]
        );

        foreach ($vars as $key => $value) {
            $tpl->assign($key, $value);
        }

       
		
		$li = '<li id="uperEditAdminTopMenuColumn" data-controller="AdminDashboard"><a href="#contentEditAdminTopMenuColumn">Ajouter une colomne</a><button type="button" class="close tabdetail" data-id="uperEditAdminTopMenuColumn"><i class="icon icon-times-circle"></i></button></li>';
		$html = '<div id="contentEditAdminTopMenuColumn" class="panel col-lg-12" style="display; flow-root;">' . $tpl->fetch() . '</div>';

			$result = [
				'success' => true,
				'li'      => $li,
				'html'    => $html,
			];

       
        die(Tools::jsonEncode($result));

    }

    public function ajaxProcessOutputColumnForm() {

        $ids_lang = 'columnname¤columnlink¤column_value_over¤column_value_under¤columnimage¤columnimagelegend¤iconPickingButton';
        $id_column = Tools::getValue('id_column');
        $context = Context::getContext();
        $ObjTopMenuColumn = new TopMenuColumn($id_column);
        $menus = TopMenu::getMenus($context->cookie->id_lang, false);
		$img_src = '';
        $haveDepend = false;

        if ($id_column > 0) {
            $haveDepend = TopMenuColumn::columnHaveDepend($ObjTopMenuColumn->id);
			if($ObjTopMenuColumn->have_icon) {
				$icone = $ObjTopMenuColumn->image_hash;
				
				if (!empty($icone)) {
                    $img_src = $icone;
                } else {
					 $img_src = '../img/menu_icons/no-icone.png';
				}
			}
        }
		
		
        

        $columnsWrap = TopMenuColumnWrap::getMenuColumnsWrap((int) $ObjTopMenuColumn->id_topmenu, $context->cookie->id_lang, false);

        $currentProductName = 'N/A';

        if ($id_column > 0 && isset($ObjTopMenuColumn->id_product) && $ObjTopMenuColumn->id_product) {
            $productObj = new Product($ObjTopMenuColumn->id_product, false, $this->context->cookie->id_lang);

            if (Validate::isLoadedObject($productObj)) {
                $currentProductName = $productObj->name;
            }

        }

        $languages = Language::getLanguages(false);

        $hasHtmlOver = false;

        foreach ($languages as $language) {

            if (isset($ObjTopMenuColumn->img_value_over[$language['id_lang']]) && !empty($ObjTopMenuColumn->img_value_over[$language['id_lang']])) {
                $hasHtmlOver = true;
                break;
            }

        }

        $hasAdditionnalText = false;

        foreach ($languages as $language) {

            if (isset($ObjTopMenuColumn->value_over[$language['id_lang']]) && !empty($ObjTopMenuColumn->value_over[$language['id_lang']]) || isset($ObjTopMenuColumn->value_under[$language['id_lang']]) && !empty($ObjTopMenuColumn->value_under[$language['id_lang']])) {
                $hasAdditionnalText = true;
                break;
            }

        }

        $rebuildable_type = [
            3,
            4,
            5,
            10,
        ];
        $tpl = $this->createTemplate('controllers/top_menu/tabs/display_column_form.tpl');
        $iso = $this->context->language->iso_code;
        $tpl->assign(
            [
                'topMenuColumn'    	  => $ObjTopMenuColumn,
                'linkTopMenu'         => $context->link->getAdminLink('AdminTopMenu'),
                'ids_lang'            => $ids_lang,
                'menus'               => $menus,
                'haveDepend'          => $haveDepend,
                'hasAdditionnalText'  => $hasAdditionnalText,
                'rebuildable_type'    => $rebuildable_type,
                'menu_img_dir'        => '/content/themes/'.$context->employee->bo_theme.'/img/topmenu/',
                'current_iso_lang'    => Language::getIsoById($context->cookie->id_lang),
                'current_id_lang'     => (int) $context->language->id,
                'default_language'    => (int) Configuration::get('EPH_LANG_DEFAULT'),
                'languages'           => Language::getLanguages(false),
                'shopFeatureActive'   => false,
                'groups'              => Group::getGroups((int) $this->context->cookie->id_lang),
                'iso'                 => file_exists(_EPH_CORE_DIR_ . _EPH_JS_DIR_.'tiny_mce/langs/' . $iso . '.js') ? $iso : 'en',
                'pathCSS'             => _THEME_CSS_DIR_,
                'ad'                  => __EPH_BASE_URI__ . basename(_EPH_ROOT_DIR_),
                'columnsWrap'         => $columnsWrap,
                'columnWrap_selected' => $ObjTopMenuColumn->id_topmenu_wrap,
                'categoryList'        => $this->getCategoryList(),
                'cms'                 => CMS::listCms((int) $this->context->cookie->id_lang),
                'cmsCategories'       => $this->getNestedCmsCategories((int) $this->context->cookie->id_lang),
                'categoryList'              => EducationType::getEducationType((int) $this->context->cookie->id_lang),
                'pagesList'           => $this->getPageList(),
                'link_targets'        => $this->link_targets,
                'hasHtmlOver'         => $hasHtmlOver,
				'img_src'			  => $img_src
            ]
        );

       
		
		$li = '<li id="uperEditAdminTopMenuItemGroup" data-controller="AdminDashboard"><a href="#contentEditAdminTopMenuItemGroup">Ajouter une colomne</a><button type="button" class="close tabdetail" data-id="uperEditAdminTopMenuItemGroup"><i class="icon icon-times-circle"></i></button></li>';
		$html = '<div id="contentEditAdminTopMenuItemGroup" class="panel col-lg-12" style="display; flow-root;">' . $tpl->fetch() . '</div>';

			$result = [
				'success' => true,
				'li'      => $li,
				'html'    => $html,
			];

       
        die(Tools::jsonEncode($result));
    }

    protected function getProductsImagesTypes() {

        $a = [];

        foreach (ImageType::getImagesTypes('products') as $imageType) {
            $a[$imageType['name']] = $imageType['name'] . ' (' . $imageType['width'] . ' x ' . $imageType['height'] . ' pixels)';
        }

        return $a;
    }

    public function ajaxProcessOutputElementForm() {

        $tpl = $this->createTemplate('controllers/top_menu/tabs/display_element_form.tpl');
        $ids_lang = 'elementname¤elementlink¤elementimage¤elementimagelegend¤iconPickingButton';
        $id_item = Tools::getValue('id_item');
        $context = Context::getContext();
        $ObjTopMenuElement = new TopMenuElements($id_item);
        $columns = TopMenuColumn::getMenuColumsByIdMenu((int) $ObjTopMenuElement->id_topmenu, $this->context->cookie->id_lang, false);

        if (is_array($columns)) {

            foreach ($columns as $k => $column) {
                $columns[$k]['admin_name'] = TopMenu::getAdminOutputNameValue($column, false);
            }

        }

        $tpl->assign(
            [
                'ObjTopMenuElement' => $ObjTopMenuElement,
                'linkTopMenu'       => $context->link->getAdminLink('AdminTopMenu'),
                'ids_lang'          => $ids_lang,
                'menus'             => TopMenu::getMenus($context->cookie->id_lang, false),
                'columns'           => $columns,
                'topMenu_img_dir'   => _EPH_MENU_DIR_,
                'menu_img_dir'      => _EPH_ADMIN_THEME_DIR_.  $this->bo_theme . '/img/topmenu/',
                'current_iso_lang'  => Language::getIsoById($context->cookie->id_lang),
                'current_id_lang'   => (int) $context->language->id,
                'default_language'  => (int) Configuration::get('EPH_LANG_DEFAULT'),
                'languages'         => Language::getLanguages(false),
                'cms'               => CMS::listCms((int) $this->context->cookie->id_lang),
                'cmsCategories'     => $this->getNestedCmsCategories((int) $this->context->cookie->id_lang),
                'pagesList'         => $this->getPageList(),
                'categoryList'      => EducationType::getEducationType((int) $this->context->cookie->id_lang),
                'link_targets'      => $this->link_targets,
            ]
        );

        $return = [
            'html' => $tpl->fetch(),
        ];
        die(Tools::jsonEncode($return));
    }

    public function ajaxProcessGetColumsNameByIdMenu() {

        $id_menu = Tools::getValue('id_menu');

        $columns = TopMenuColumnWrap::getMenuColumnsWrap($id_menu, $this->context->cookie->id_lang);
        $html = '<select name="id_topmenu_columns_wrap" id="idWrap" class="fixed-width-xxl">';
        $html .= '<option>-- ' . $this->la('Choose') . ' --</option>';

        foreach ($columns as $columnWrap) {
            $html .= '<option value="' . $columnWrap['id_columns_wrap'] . '">' . $columnWrap['internal_name'] . '</option>';
        }

        $html .= '</select>';

        die(Tools::jsonEncode($html));
    }

    public function ajaxProcessDeleteMenu() {

        $id_menu = Tools::getValue('id_menu');

        $ObjEphenyxTopMenuClass = new TopMenu($id_menu);

        if ($ObjEphenyxTopMenuClass->delete()) {
            $result = [
                'success' => true,
                'message' => $this->la('The tab was successfully deleted.'),
            ];
        } else {
            $result = [
                'success' => false,
                'message' => $this->la('An error occurred while deleting the column'),
            ];
        }

        die(Tools::jsonEncode($result));
    }

    public function ajaxProcessActiveMenu() {

        $id_menu = Tools::getValue('id_menu');

        $ObjEphenyxTopMenuClass = new TopMenu($id_menu);

        if ($ObjEphenyxTopMenuClass->active == 1) {
            $ObjEphenyxTopMenuClass->active = 0;
            $value = 0;
        } else {
            $ObjEphenyxTopMenuClass->active = 1;
            $value = 1;
        }

        if ($ObjEphenyxTopMenuClass->update()) {
            $result = [
                'success' => true,
                'value'   => $value,
                'message' => $this->la('The tab status was successfully updated.'),
            ];
        } else {
            $result = [
                'success' => false,
                'message' => $this->la('An error occur while updated the tab status.'),
            ];
        }

        die(Tools::jsonEncode($result));
    }

    public function ajaxProcessActiveDesktopMenu() {

        $id_menu = Tools::getValue('id_menu');

        $ObjEphenyxTopMenuClass = new TopMenu($id_menu);

        if ($ObjEphenyxTopMenuClass->active_desktop == 1) {
            $ObjEphenyxTopMenuClass->active_desktop = 0;
            $value = 0;
        } else {
            $ObjEphenyxTopMenuClass->active_desktop = 1;
            $value = 1;
        }

        if ($ObjEphenyxTopMenuClass->update()) {
            $result = [
                'success' => true,
                'value'   => $value,
                'message' => $this->la('The tab status was successfully updated.'),
            ];
        } else {
            $result = [
                'success' => false,
                'message' => $this->la('An error occur while updated the tab status.'),
            ];
        }

        die(Tools::jsonEncode($result));
    }

    public function ajaxProcessActiveMobileMenu() {

        $id_menu = Tools::getValue('id_menu');

        $ObjEphenyxTopMenuClass = new TopMenu($id_menu);

        if ($ObjEphenyxTopMenuClass->active_mobile == 1) {
            $ObjEphenyxTopMenuClass->active_mobile = 0;
            $value = 0;
        } else {
            $ObjEphenyxTopMenuClass->active_mobile = 1;
            $value = 1;
        }

        if ($ObjEphenyxTopMenuClass->update()) {
            $result = [
                'success' => true,
                'value'   => $value,
                'message' => $this->la('The tab status was successfully updated.'),
            ];
        } else {
            $result = [
                'success' => false,
                'message' => $this->la('An error occur while updated the tab status.'),
            ];
        }

        die(Tools::jsonEncode($result));
    }

    public function ajaxProcessDeleteColumnWrap() {

        $id_column_wrap = Tools::getValue('id_column_wrap');

        $ObjEphenyxTopColumnWrapClass = new TopMenuColumnWrap($id_column_wrap);

        if ($ObjEphenyxTopColumnWrapClass->delete()) {
            $result = [
                'success' => true,
                'message' => $this->la('The column was successfully deleted.'),
            ];
        } else {
            $result = [
                'success' => false,
                'message' => $this->la('An error occurred while deleting the column'),
            ];
        }

        die(Tools::jsonEncode($result));
    }

    public function ajaxProcessActiveColumnWrap() {

        $id_menu = Tools::getValue('id_menu');
        $file = fopen("testActiveColumnWrap.txt", "w");

        $ObjEphenyxTopMenuColumnWrap = new TopMenuColumnWrap($id_menu);

        if ($ObjEphenyxTopMenuColumnWrap->active == 1) {
            $ObjEphenyxTopMenuColumnWrap->active = 0;
            $value = 0;
        } else {
            $ObjEphenyxTopMenuColumnWrap->active = 1;
            $value = 1;
        }

        if ($ObjEphenyxTopMenuColumnWrap->save()) {
            fwrite($file, "Update" . PHP_EOL);
            $result = [
                'success' => true,
                'value'   => $value,
                'message' => $this->la('The column status was successfully updated.'),
            ];
        } else {
            fwrite($file, "pas Update" . PHP_EOL);
            $result = [
                'success' => false,
                'message' => $this->la('An error occur while updated the column status.'),
            ];
        }

        die(Tools::jsonEncode($result));
    }

    public function ajaxProcessActiveDesktopColumnWrap() {

        $id_menu = Tools::getValue('id_menu');

        $ObjEphenyxTopMenuClass = new TopMenuColumnWrap($id_menu);

        if ($ObjEphenyxTopMenuClass->active_desktop == 1) {
            $ObjEphenyxTopMenuClass->active_desktop = 0;
            $value = 0;
        } else {
            $ObjEphenyxTopMenuClass->active_desktop = 1;
            $value = 1;
        }

        if ($ObjEphenyxTopMenuClass->save()) {
            $result = [
                'success' => true,
                'value'   => $value,
                'message' => $this->la('The column status was successfully updated.'),
            ];
        } else {
            $result = [
                'success' => false,
                'message' => $this->la('An error occur while updated the column status.'),
            ];
        }

        die(Tools::jsonEncode($result));
    }

    public function ajaxProcessActiveMobileColumnWrap() {

        $id_menu = Tools::getValue('id_menu');

        $ObjEphenyxTopMenuClass = new TopMenuColumnWrap($id_menu);

        if ($ObjEphenyxTopMenuClass->active_mobile == 1) {
            $ObjEphenyxTopMenuClass->active_mobile = 0;
            $value = 0;
        } else {
            $ObjEphenyxTopMenuClass->active_mobile = 1;
            $value = 1;
        }

        if ($ObjEphenyxTopMenuClass->save()) {
            $result = [
                'success' => true,
                'value'   => $value,
                'message' => $this->la('The column status was successfully updated.'),
            ];
        } else {
            $result = [
                'success' => false,
                'message' => $this->la('An error occur while updated the column status.'),
            ];
        }

        die(Tools::jsonEncode($result));
    }

    public function ajaxProcessDeleteItemGroup() {

        $id_topmenu_column = Tools::getValue('id_topmenu_column');

        $ObjEphenyxTopColumClass = new TopMenuColumn($id_topmenu_column);

        if ($ObjEphenyxTopColumClass->delete()) {
            $result = [
                'success' => true,
                'message' => $this->la('The column was successfully deleted.'),
            ];
        } else {
            $result = [
                'success' => false,
                'message' => $this->la('An error occurred while deleting the column'),
            ];
        }

        die(Tools::jsonEncode($result));
    }
	
	public function ajaxProcessDeleteElement() {

        $id_topmenu_elements = Tools::getValue('id_topmenu_elements');

        $ObjEphenyxTopElementClass = new TopMenuElements($id_topmenu_elements);

        if ($ObjEphenyxTopElementClass->delete()) {
            $result = [
                'success' => true,
                'message' => $this->la('The element was successfully deleted.'),
            ];
        } else {
            $result = [
                'success' => false,
                'message' => $this->la('An error occurred while deleting the element'),
            ];
        }

        die(Tools::jsonEncode($result));
    }

    public function ajaxProcessActiveColumn() {

        $id_menu = Tools::getValue('id_menu');
        $file = fopen("testActiveColumnWrap.txt", "w");

        $ObjEphenyxTopMenuColumnWrap = new TopMenuColumn($id_menu);

        if ($ObjEphenyxTopMenuColumnWrap->active == 1) {
            $ObjEphenyxTopMenuColumnWrap->active = 0;
            $value = 0;
        } else {
            $ObjEphenyxTopMenuColumnWrap->active = 1;
            $value = 1;
        }

        if ($ObjEphenyxTopMenuColumnWrap->save()) {
            fwrite($file, "Update" . PHP_EOL);
            $result = [
                'success' => true,
                'value'   => $value,
                'message' => $this->la('The column status was successfully updated.'),
            ];
        } else {
            fwrite($file, "pas Update" . PHP_EOL);
            $result = [
                'success' => false,
                'message' => $this->la('An error occur while updated the column status.'),
            ];
        }

        die(Tools::jsonEncode($result));
    }

    public function ajaxProcessActiveDesktopColumn() {

        $id_menu = Tools::getValue('id_menu');

        $ObjEphenyxTopMenuClass = new TopMenuColumn($id_menu);

        if ($ObjEphenyxTopMenuClass->active_desktop == 1) {
            $ObjEphenyxTopMenuClass->active_desktop = 0;
            $value = 0;
        } else {
            $ObjEphenyxTopMenuClass->active_desktop = 1;
            $value = 1;
        }

        if ($ObjEphenyxTopMenuClass->save()) {
            $result = [
                'success' => true,
                'value'   => $value,
                'message' => $this->la('The column status was successfully updated.'),
            ];
        } else {
            $result = [
                'success' => false,
                'message' => $this->la('An error occur while updated the column status.'),
            ];
        }

        die(Tools::jsonEncode($result));
    }

    public function ajaxProcessActiveMobileColumn() {

        $id_menu = Tools::getValue('id_menu');

        $ObjEphenyxTopMenuClass = new TopMenuColumn($id_menu);

        if ($ObjEphenyxTopMenuClass->active_mobile == 1) {
            $ObjEphenyxTopMenuClass->active_mobile = 0;
            $value = 0;
        } else {
            $ObjEphenyxTopMenuClass->active_mobile = 1;
            $value = 1;
        }

        if ($ObjEphenyxTopMenuClass->save()) {
            $result = [
                'success' => true,
                'value'   => $value,
                'message' => $this->la('The column status was successfully updated.'),
            ];
        } else {
            $result = [
                'success' => false,
                'message' => $this->la('An error occur while updated the column status.'),
            ];
        }

        die(Tools::jsonEncode($result));
    }

    public function ajaxProcessTopMenuForm() {

        
		$id_topmenu = Tools::getValue('id_topmenu', false);
        $topMenu = new TopMenu($id_topmenu);
       
        if (!Tools::getValue('type', 0)) {
            $this->errors[] = $this->la('The type of the tab is required.');
        } else

        if (Tools::getValue('type') == 1 && !Tools::getValue('id_cms')) {
            $this->errors[] = $this->la('You need to select the related CMS.');
        } else

        if (Tools::getValue('type') == 9 && !Tools::getValue('id_specific_page')) {
            $this->errors[] = $this->la('You need to select the related specific page.');
        }

        if (!count($this->errors)) {

            $this->menucopyFromPost($topMenu);
            $topMenu->border_size_tab = $this->getBorderSizeFromArray(Tools::getValue('border_size_tab'));
            $topMenu->border_size_submenu = $this->getBorderSizeFromArray(Tools::getValue('border_size_submenu'));
            $fnd_color_menu_tab = Tools::getValue('fnd_color_menu_tab');
            $topMenu->fnd_color_menu_tab = $fnd_color_menu_tab[0] . (Tools::getValue('fnd_color_menu_tab_gradient') && isset($fnd_color_menu_tab[1]) && $fnd_color_menu_tab[1] ? $this->gradient_separator . $fnd_color_menu_tab[1] : '');
            $fnd_color_menu_tab_over = Tools::getValue('fnd_color_menu_tab_over');
            $topMenu->fnd_color_menu_tab_over = $fnd_color_menu_tab_over[0] . (Tools::getValue('fnd_color_menu_tab_over_gradient') && isset($fnd_color_menu_tab_over[1]) && $fnd_color_menu_tab_over[1] ? $this->gradient_separator . $fnd_color_menu_tab_over[1] : '');
            $fnd_color_submenu = Tools::getValue('fnd_color_submenu');
            $topMenu->fnd_color_submenu = $fnd_color_submenu[0] . (Tools::getValue('fnd_color_submenu_gradient') && isset($fnd_color_submenu[1]) && $fnd_color_submenu[1] ? $this->gradient_separator . $fnd_color_submenu[1] : '');
            $topMenu->chosen_groups = Tools::getIsset('chosen_groups') ? Tools::jsonEncode(Tools::getValue('chosen_groups')) : '';
			$imageUploader = new HelperImageUploader('iconFormMenu');
            $imageUploader->setAcceptTypes([ 'png']);
            $files = $imageUploader->process(); 
			if(is_array($files) && count($files)) {
				foreach ($files as $image) {
					$type = pathinfo($image['name'] , PATHINFO_EXTENSION);
					$image = new Imagick($image['save_path']);
					$image->resizeImage(200, 50, Imagick::FILTER_LANCZOS, 1, true);
					$data = $image->getImageBlob();
					$image->clear(); 
					$base64_code = base64_encode($data);
					$base64_str = 'data:image/' . $type . ';base64,' . $base64_code;
					$topMenu->image_hash = $base64_str;
				
				}
			}

            if (!Tools::getValue('tinymce_container_toggle_menu', 0)) {
                $topMenu->value_over = [];
                $topMenu->value_under = [];
            }

            $languages = Language::getLanguages(false);

            if (!$id_topmenu) {

                if (!$topMenu->add()) {
                    $this->errors[] = $this->la('An error occurred while adding the tab');
                } else {
                    $this->context->smarty->assign([
                        'current_id_menu' => $topMenu->id,
                    ]);
                }

            } else
            if (!$topMenu->update()) {
                $this->errors[] = $this->la('An error occurred while updating the tab');
            }

            
            unset($_POST['active']);
        }

        $this->errors = array_unique($this->errors);

        if (count($this->errors)) {
            $result = [
                'success' => false,
                'message' => implode(PHP_EOL, $this->errors),
            ];
        } else {
			
			$data = $this->createTemplate('controllers/top_menu/newMenu.tpl');
			$data->assign([
				'topMenu'		=> $topMenu,
				 'menu_img_dir'              => _EPH_ADMIN_THEME_DIR_.  $this->bo_theme . '/img/topmenu/',
			]);
			$li = '<li unique-id="'.$topMenu->id.'" id="tab_'.$topMenu->id.'"><span class="menu-dragHandler pmIconContainer"><i class="pmIcon icon-move"></i></span><a href="#topmenu-tab-'.$topMenu->id.'">'.$topMenu->outPutName.'</a></li>';
			$html = '<div id="topmenu-tab-'.$topMenu->id.'" class="tab-menu-content">'.$data->fetch().'</div>';
            $result = [
                'success' => true,
                'message' => $this->la('Tab has been successfully saved'),
				'li' => $li,
				'html' => $html
            ];
        }

        die(Tools::jsonEncode($result));
    }


    public function getConfigKeys() {

        $config = $configResponsive = [];

        foreach ($this->_fieldsOptions as $key => $data) {

            if (isset($data['mobile']) && $data['mobile']) {
                $configResponsive[] = $key;
            } else {
                $config[] = $key;
            }

        }

        return [
            $config,

            $configResponsive,
        ];
    }

    

    protected function menucopyFromPost(&$object) {

        $data = Tools::getAllValues();

        foreach ($data as $key => $value) {

            if ($key == 'active_column' || $key == 'active_menu' || $key == 'active_element') {
                $key = 'active';
            } else

            if ($key == 'active_desktop_column' || $key == 'active_desktop_menu' || $key == 'active_desktop_element') {
                $key = 'active_desktop';
            } else

            if ($key == 'active_mobile_column' || $key == 'active_mobile_menu' || $key == 'active_mobile_element') {
                $key = 'active_mobile';
            }

            if (property_exists($object, $key)) {
                $object->{$key}

                = $value;
            }

        }

        $rules = call_user_func([get_class($object), 'getValidationRules'], get_class($object));

        if (count($rules['validateLang'])) {
            $languages = Language::getLanguages(false);

            foreach ($languages as $language) {

                foreach (array_keys($rules['validateLang']) as $field) {

                    if (Tools::getIsset($field . '_' . (int) $language['id_lang'])) {
                        $object->{$field}

                        [(int) $language['id_lang']] = Tools::getValue($field . '_' . (int) $language['id_lang']);
                    }

                }

            }

        }

    }

    
    public function updateMenuType($EphenyxTopMenuClass) {

        
		if (Tools::getValue('rebuild') && in_array($EphenyxTopMenuClass->type, $this->rebuildable_type)) {
            $columnsWrap = TopMenuColumnWrap::getColumnWrapIds($EphenyxTopMenuClass->id);

            foreach ($columnsWrap as $idWrap) {
                $columnWrap = new TopMenuColumnWrap((int) $idWrap);
                $columnWrap->delete();
            }

        }

        switch ($EphenyxTopMenuClass->type) {
        case 3:
			
            if (!Tools::getValue('include_subs') || empty($EphenyxTopMenuClass->id_category)) {
                return;
            }
			
            $firstChildCategories = $this->getSubCategoriesId($EphenyxTopMenuClass->id_category, true, true);
            $lastChildCategories = [];
            $columnWithNoDepth = $columnWrapWithNoDepth = false;
			
            if (!count($firstChildCategories)) {
                return;
            }
			
            $nbColumnsToCreate = (int) Tools::getValue('nbColumnsToCreate');
            $nbColumnsToCreate = max(1, $nbColumnsToCreate);
            $nbCategories = count($firstChildCategories);

            if ($nbCategories < $nbColumnsToCreate) {
                $nbColumnsToCreate = $nbCategories;
            }

            $nbCategoriesByColumn = round($nbCategories / $nbColumnsToCreate);
            $nbColumnWrapsCreated = $nbElementsInCurrentColumnWrap = 0;
            $currentColumnWrap = null;

            foreach ($firstChildCategories as $firstChildCategory) {
                $idColumn = false;

                if (Tools::getValue('id_topmenu', false)) {
                    $idColumn = TopMenuColumn::getIdColumnCategoryDepend($EphenyxTopMenuClass->id, $firstChildCategory['id_category']);

                    if (!$idColumn && !Tools::getValue('rebuild')) {
                        continue;
                    }

                }

                $topMenuColumn = $this->fetchOrCreateColumnObject($idColumn, $EphenyxTopMenuClass, 'id_category', $firstChildCategory);

                if (!$idColumn) {

                    if ($nbColumnWrapsCreated == 0 || ($nbColumnWrapsCreated < $nbColumnsToCreate && $nbElementsInCurrentColumnWrap == $nbCategoriesByColumn)) {
                        $EphenyxTopMenuColumnWrapClass = $this->createColumnWrap($EphenyxTopMenuClass->id);
                        $topMenuColumn->id_topmenu_columns_wrap = $EphenyxTopMenuColumnWrapClass->id;
                        $currentColumnWrap = $EphenyxTopMenuColumnWrapClass;
                        $nbElementsInCurrentColumnWrap = 0;
                        $nbColumnWrapsCreated++;
                    }

                    $topMenuColumn->id_topmenu_columns_wrap = $currentColumnWrap->id;
                    $nbElementsInCurrentColumnWrap++;
                }
				
                if (!$topMenuColumn->save()) {
                    $this->errors[] = $this->la('An error occurred while saving children category');
                    continue;
                }
				
                $lastChildCategories = $this->getSubCategoriesId($firstChildCategory['id_category'], true, true);

                if (!count($lastChildCategories)) {
                    continue;
                }

                $elementPosition = 0;

                foreach ($lastChildCategories as $lastChildCategory) {
                    $idElement = false;

                    if (Tools::getValue('id_menu', false)) {
                        $idElement = TopMenuElements::getIdElementCategoryDepend($idColumn, $lastChildCategory['id_category']);

                        if (!$idElement && !Tools::getValue('rebuild')) {
                            continue;
                        }

                    }

                    $EphenyxTopMenuElementsClass = $this->fetchOrCreateElementObject($idElement, $topMenuColumn, 'id_category', $lastChildCategory, $EphenyxTopMenuClass->type);

                    if (!$idElement) {
                        $EphenyxTopMenuElementsClass->position = $elementPosition;
                    }
					
                    if (!$EphenyxTopMenuElementsClass->save()) {
                        $this->errors[] = $this->la('An error occurred while saving children category');
                    }

                    $elementPosition++;
                }

            }

            break;
       
       
        case 10:

            if (!Tools::getValue('include_subs_cms') || empty($EphenyxTopMenuClass->id_cms_category)) {
                return;
            }

            $firstChildCategories = $this->getCmsSubCategoriesId($EphenyxTopMenuClass->id_cms_category, true, true);
            $columnWithNoDepth = $columnWrapWithNoDepth = false;

            if (count($firstChildCategories)) {

                foreach ($firstChildCategories as $firstChildCategory) {
                    $childCmsPages = $this->getCmsByCategory((int) $firstChildCategory['id_cms_category']);

                    if (count($childCmsPages)) {
                        $idColumn = false;

                        if (Tools::getValue('id_menu', false)) {
                            $idColumn = TopMenuColumn::getIdColumnCmsCategoryDepend($EphenyxTopMenuClass->id, $firstChildCategory['id_cms_category']);

                            if (!$idColumn && !Tools::getValue('rebuild')) {
                                continue;
                            }

                        }

                        $topMenuColumn = $this->fetchOrCreateColumnObject($idColumn, $EphenyxTopMenuClass, 'id_cms_category', $firstChildCategory);

                        if (!$idColumn) {
                            $EphenyxTopMenuColumnWrapClass = $this->createColumnWrap($EphenyxTopMenuClass->id);
                            $topMenuColumn->id_topmenu_columns_wrap = $EphenyxTopMenuColumnWrapClass->id;
                        }

                        if ($topMenuColumn->save()) {
                            $elementPosition = 0;

                            foreach ($childCmsPages as $cmsPage) {
                                $idElement = false;

                                if (Tools::getValue('id_menu', false)) {
                                    $idElement = TopMenuElements::getIdElementCmsDepend($idColumn, (int) $cmsPage['id_cms']);

                                    if (!$idElement && !Tools::getValue('rebuild')) {
                                        continue;
                                    }

                                }

                                $EphenyxTopMenuElementsClass = $this->fetchOrCreateElementObject($idElement, $topMenuColumn, 'id_cms', $cmsPage, 1);

                                if (!$idElement) {
                                    $EphenyxTopMenuElementsClass->position = $elementPosition;
                                }

                                if (!$EphenyxTopMenuElementsClass->save()) {
                                    $this->errors[] = $this->la('An error occurred while saving children CMS page');
                                }

                                $elementPosition++;
                            }

                        } else {
                            $this->errors[] = $this->la('An error occurred while saving children CMS page');
                        }

                    } else {
                        $idColumn = false;
                        $columnWithNoDepth = false;

                        if (Tools::getValue('id_menu', false)) {
                            $idColumn = TopMenuColumn::getIdColumnCmsCategoryDepend($EphenyxTopMenuClass->id, $firstChildCategory['id_cms_category']);

                            if (!$idColumn && !Tools::getValue('rebuild')) {
                                continue;
                            }

                            if ($idColumn) {
                                $columnWithNoDepth = $idColumn;
                            }

                        }

                        $topMenuColumn = $this->fetchOrCreateColumnObject($columnWithNoDepth, $EphenyxTopMenuClass, 'id_cms_category', $firstChildCategory, $EphenyxTopMenuClass->type);

                        if (!$columnWithNoDepth) {
                            $EphenyxTopMenuColumnWrapClass = $this->createColumnWrap($EphenyxTopMenuClass->id);
                            $topMenuColumn->id_topmenu_columns_wrap = $EphenyxTopMenuColumnWrapClass->id;
                        }

                        if (!$topMenuColumn->save()) {
                            $this->errors[] = $this->la('An error occurred while saving children category');
                            continue;
                        }

                        if (!$columnWrapWithNoDepth) {
                            $columnWrapWithNoDepth = $topMenuColumn->id_topmenu_columns_wrap;
                        }

                    }

                }

            } else {
                $categoryCmsPages = $this->getCmsByCategory($EphenyxTopMenuClass->id_cms_category);

                if (count($categoryCmsPages)) {
                    $idColumn = false;
                    $columnWithNoDepth = false;

                    if (Tools::getValue('id_menu', false)) {
                        $idColumn = TopMenuColumn::getIdColumnCmsCategoryDepend($EphenyxTopMenuClass->id, $EphenyxTopMenuClass->id_cms_category);

                        if (!$idColumn && !Tools::getValue('rebuild')) {
                            return;
                        }

                        if ($idColumn) {
                            $columnWithNoDepth = $idColumn;
                        }

                    }

                    $topMenuColumn = $this->fetchOrCreateColumnObject($columnWithNoDepth, $EphenyxTopMenuClass, 'id_cms_category', $firstChildCategory, 2);

                    if (!$columnWithNoDepth) {
                        $EphenyxTopMenuColumnWrapClass = $this->createColumnWrap($EphenyxTopMenuClass->id);
                        $topMenuColumn->id_topmenu_columns_wrap = $EphenyxTopMenuColumnWrapClass->id;
                    }

                    if (!$topMenuColumn->save()) {
                        $this->errors[] = $this->la('An error occurred while saving children CMS page');
                        return;
                    }

                    $elementPosition = 0;

                    foreach ($categoryCmsPages as $cmsPage) {
                        $idElement = false;

                        if (Tools::getValue('id_menu', false)) {
                            $idElement = TopMenuElements::getIdElementCmsDepend($columnWithNoDepth, (int) $cmsPage['id_cms']);

                            if (!$idElement && !Tools::getValue('rebuild')) {
                                continue;
                            }

                        }

                        $EphenyxTopMenuElementsClass = $this->fetchOrCreateElementObject($idElement, $topMenuColumn, 'id_cms', $cmsPage, 1);

                        if (!$idElement) {
                            $EphenyxTopMenuElementsClass->position = $elementPosition;
                        }

                        if (!$EphenyxTopMenuElementsClass->save()) {
                            $this->errors[] = $this->la('An error occurred while saving children CMS page');
                        }

                        $elementPosition++;
                    }

                }

            }

            break;
        }

        return true;

    }

    public function getSubCategoriesId($id_category, $active = true, $with_position = false) {

        if (!Validate::isBool($active)) {
            die(Tools::displayError());
        }

        if (!Validate::isBool($with_position)) {
            die(Tools::displayError());
        }

        $orderBy = 'c.`position`';
        $with_position_field = 'c.`position`';
        return Db::getInstance(_EPH_USE_SQL_SLAVE_)->ExecuteS('
            SELECT c.id_category' . ($with_position ? ', ' . $with_position_field : '') . '
            FROM `' . _DB_PREFIX_ . 'category` c
            WHERE `id_parent` = ' . (int) $id_category . '
            ' . ($active ? 'AND `active` = 1' : '') . '
            GROUP BY c.`id_category`
            ORDER BY ' . $orderBy . ' ASC');
    }

    
    public function getCmsSubCategoriesId($id_cms_category, $active = true, $with_position = false) {

        if (!Validate::isBool($active)) {
            die(Tools::displayError());
        }

        if (!Validate::isBool($with_position)) {
            die(Tools::displayError());
        }

        $orderBy = 'c.`position`';
        $with_position_field = 'c.`position`';
        return Db::getInstance(_EPH_USE_SQL_SLAVE_)->ExecuteS('
            SELECT c.id_cms_category' . ($with_position ? ', ' . $with_position_field : '') . '
            FROM `' . _DB_PREFIX_ . 'cms_category` c
            WHERE `id_parent` = ' . (int) $id_cms_category . '
            ' . ($active ? 'AND `active` = 1' : '') . '
            GROUP BY c.`id_cms_category`
            ORDER BY ' . $orderBy . ' ASC');
    }

    public function getCmsByCategory($idCategory) {

        return Db::getInstance(_EPH_USE_SQL_SLAVE_)->executeS(
            'SELECT c.*
            FROM `' . _DB_PREFIX_ . 'cms` c
            WHERE c.`id_cms_category` = ' . (int) $idCategory . '
            AND c.`active` = 1;'
        );
    }

    public function fetchOrCreateColumnObject($idColumn, $advancedTopMenuClass, $fieldName, $entity, $columnType = null) {

        $topMenuColumn = new TopMenuColumn($idColumn);
        $topMenuColumn->active = ($idColumn ? $topMenuColumn->active : 1);
        $topMenuColumn->id_topmenu = $advancedTopMenuClass->id;
        $topMenuColumn->id_topmenu_depend = $advancedTopMenuClass->id;
        $topMenuColumn->type = (!empty($columnType) ? $columnType : $advancedTopMenuClass->type);
        $topMenuColumn->{$fieldName} = $entity[$fieldName];
        $topMenuColumn->position = isset($entity['position']) ? $entity['position'] : '0';
        return $topMenuColumn;
    }

    public function createColumnWrap($idMenu) {

        $EphenyxTopMenuColumnWrapClass = new TopMenuColumnWrap();
        $EphenyxTopMenuColumnWrapClass->active = 1;
        $EphenyxTopMenuColumnWrapClass->id_topmenu = $idMenu;
        $EphenyxTopMenuColumnWrapClass->id_topmenu_depend = $idMenu;
        $EphenyxTopMenuColumnWrapClass->save();
        $EphenyxTopMenuColumnWrapClass->internal_name = $this->la('column') . '-' . $EphenyxTopMenuColumnWrapClass->id_menu . '-' . $EphenyxTopMenuColumnWrapClass->id;

        if (!$EphenyxTopMenuColumnWrapClass->save()) {
            $this->errors[] = $this->la('An error occurred while saving column');
        }

        return $EphenyxTopMenuColumnWrapClass;
    }

    public function fetchOrCreateElementObject($idElement, $advancedTopMenuColumnClass, $fieldName, $entity, $columnType = null) {

        $advancedTopMenuElementsClass = new TopMenuElements($idElement);
        $advancedTopMenuElementsClass->active = ($idElement ? $advancedTopMenuElementsClass->active : 1);

        if (!empty($columnType)) {
            $advancedTopMenuElementsClass->type = $columnType;
        } else {
            $advancedTopMenuElementsClass->type = 2;
        }

        $advancedTopMenuElementsClass->{$fieldName}

        = $entity[$fieldName];
        $advancedTopMenuElementsClass->id_topmenu_column = $advancedTopMenuColumnClass->id;
        $advancedTopMenuElementsClass->id_topmenu_column_depend = $advancedTopMenuColumnClass->id;
        $advancedTopMenuElementsClass->position = isset($entity['position']) ? $entity['position'] : '0';
        return $advancedTopMenuElementsClass;
    }

    public function ajaxProcessTopColumnWrapForm() {

        $id_wrap = Tools::getValue('id_topmenu_columns_wrap', false);
        $id_menu = Tools::getValue('id_topmenu', false);

        if (!$id_menu) {
            $this->errors[] = $this->la('An error occurred while adding the column - Parent tab is not set');
        } else {
            $EphenyxTopMenuColumnWrapClass = new TopMenuColumnWrap($id_wrap);

            if (!count($this->errors)) {
                $this->menucopyFromPost($EphenyxTopMenuColumnWrapClass);
                $bg_color = Tools::getValue('bg_color');
                $EphenyxTopMenuColumnWrapClass->bg_color = $bg_color[0] . (Tools::getValue('bg_color_gradient') && isset($bg_color[1]) && $bg_color[1] ? $this->gradient_separator . $bg_color[1] : '');
                $EphenyxTopMenuColumnWrapClass->chosen_groups = Tools::getIsset('chosen_groups') ? Tools::jsonEncode(Tools::getValue('chosen_groups')) : '';

                if (!Tools::getValue('tinymce_container_toggle_menu', 0)) {
                    $EphenyxTopMenuColumnWrapClass->value_over = [];
                    $EphenyxTopMenuColumnWrapClass->value_under = [];
                }

                unset($_POST['active']);

                if (!$id_wrap) {

                    if (!$EphenyxTopMenuColumnWrapClass->add()) {
                        $this->errors[] = $this->la('An error occurred while adding the column');
                    }

                } else

                if (!$EphenyxTopMenuColumnWrapClass->update()) {
                    $this->errors[] = $this->la('An error occurred while updating the column');
                }

                
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
                'message' => $this->la('Column has been successfully saved'),
            ];
        }

        die(Tools::jsonEncode($result));

    }

    public function ajaxProcessTopColumnForm() {

        
        $id_column = Tools::getValue('id_topmenu_column', false);
        $topMenuColumn = new TopMenuColumn($id_column);

        if (!Tools::getValue('type', 0)) {
            $this->errors[] = $this->la('The type of the column is required.');
        } else
        if (Tools::getValue('type') == 1 && !Tools::getValue('id_cms')) {
            $this->errors[] = $this->la('You need to select the related CMS.');
        } else
        if (Tools::getValue('type') == 9 && !Tools::getValue('id_specific_page')) {
            $this->errors[] = $this->la('You need to select the related specific page.');
        }

        if (!count($this->errors)) {
            $this->menucopyFromPost($topMenuColumn);

            if (!(int) $topMenuColumn->id_topmenu_columns_wrap) {
                $this->errors[] = $this->la('You need to choose the parent column');
            }

            if (!Tools::getValue('tinymce_container_toggle_menu', 0)) {
                $topMenuColumn->value_over = [];
                $topMenuColumn->value_under = [];
            }

            $languages = Language::getLanguages(false);
            unset($_POST['active']);

            if (!$id_column) {

                if (!$topMenuColumn->add()) {
                    $this->errors[] = $this->la('An error occurred while adding the group of items');
                }

            } else

            if (!$topMenuColumn->update()) {
                $this->errors[] = $this->la('An error occurred while updating the group of items');
            }

            if (!count($this->errors)) {
                $this->updateColumnType($topMenuColumn);

                $imageUploader = new HelperImageUploader('iconFormMenuColumn');
            	$imageUploader->setAcceptTypes([ 'png']);
            	$files = $imageUploader->process(); 
				if(is_array($files) && count($files)) {
					foreach ($files as $image) {
						$type = pathinfo($image['name'] , PATHINFO_EXTENSION);
						$image = new Imagick($image['save_path']);
						$image->resizeImage(200, 50, Imagick::FILTER_LANCZOS, 1, true);
						$data = $image->getImageBlob();
						$image->clear(); 
						$base64_code = base64_encode($data);
						$base64_str = 'data:image/' . $type . ';base64,' . $base64_code;
						$topMenuColumn->image_hash = $base64_str;
				
					}
				}

                if ($topMenuColumn->type == 8) {
                    $productElementsObj->id_topmenu_column = $topMenuColumn->id;
                    $productElementsObj->save();
                }

            }

        }

      

        if (count($this->errors)) {
            $this->errors = array_unique($this->errors);
            $result = [
                'success' => false,
                'message' => implode(PHP_EOL, $this->errors),
            ];
        } else {
			
			$columnsWraps = TopMenuColumnWrap::getMenuColumnsWrap((int) $topMenuColumn->id_topmenu, $this->context->cookie->id_lang, false);
			
			foreach($columnsWraps as &$columnsWrap) {
				$columnsWrap['columns'] = TopMenuColumn::getMenuColums($columnsWrap['id_columns_wrap'], $this->context->cookie->id_lang, false);
			}
		
			$data = $this->createTemplate('controllers/top_menu/columnWrapSortContent.tpl');
			$data->assign([
				'columnsWraps'		=> $columnsWraps,
				 'menu_img_dir'     => _EPH_ADMIN_THEME_DIR_.  $this->bo_theme . '/img/topmenu/',
			]);
			
            $result = [
                'success' => true,
                'message' => $this->la('Column has been successfully saved'),
				'idMenu' => $topMenuColumn->id_topmenu,
				'html' => $data->fetch()
            ];
			
        }

        die(Tools::jsonEncode($result));

    }
    public function updateColumnType($topMenuColumn) {

        if (Tools::getValue('rebuild') && in_array($topMenuColumn->type, $this->rebuildable_type)) {
            $elements = TopMenuElements::getElementIds((int) $topMenuColumn->id);

            foreach ($elements as $idElement) {
                $element = new TopMenuElements((int) $idElement);
                $element->delete();
            }

        }

        switch ($topMenuColumn->type) {
        case 3:

            if (!Tools::getValue('include_subs') || empty($topMenuColumn->id_category)) {
                return;
            }

            $childCategories = $this->getSubCategoriesId($topMenuColumn->id_category);

            if (!count($childCategories)) {
                return;
            }

            $elementPosition = 0;

            foreach ($childCategories as $childCategory) {
                $idElement = false;

                if (Tools::getValue('id_topmenu_column', false)) {
                    $idElement = TopMenuElements::getIdElementCategoryDepend(Tools::getValue('id_topmenu_column'), $childCategory['id_category']);

                    if (!$idElement && !Tools::getValue('rebuild')) {
                        continue;
                    }

                }

                $EphenyxTopMenuElementsClass = $this->fetchOrCreateElementObject($idElement, $topMenuColumn, 'id_category', $childCategory, $topMenuColumn->type);

                if (!$idElement) {
                    $EphenyxTopMenuElementsClass->position = $elementPosition;
                }

                if (!$EphenyxTopMenuElementsClass->save()) {
                    $this->errors[] = $this->la('An error occurred while saving children category');
                }

                $elementPosition++;
            }

            break;
       
      
        case 13:

            if (!Tools::getValue('include_subs_cms') || empty($topMenuColumn->id_cms_category)) {
                return;
            }

            $cmsPages = $this->getCmsByCategory((int) $topMenuColumn->id_cms_category);

            if (!count($cmsPages)) {
                return;
            }

            $elementPosition = 0;

            foreach ($cmsPages as $cmsPage) {
                $idElement = false;

                if (Tools::getValue('id_topmenu_column', false)) {
                    $idElement = TopMenuElements::getIdElementCmsDepend(Tools::getValue('id_topmenu_column'), (int) $cmsPage['id_cms']);

                    if (!$idElement && !Tools::getValue('rebuild')) {
                        continue;
                    }

                }

                $EphenyxTopMenuElementsClass = $this->fetchOrCreateElementObject($idElement, $topMenuColumn, 'id_cms', $cmsPage, 1);

                if (!$idElement) {
                    $EphenyxTopMenuElementsClass->position = $elementPosition;
                }

                if (!$EphenyxTopMenuElementsClass->save()) {
                    $this->errors[] = $this->la('An error occurred while saving children CMS page');
                }

                $elementPosition++;
            }

            break;
        }

    }

    public function ajaxProcessMenuPosition() {

        $order = Tools::getValue('orderMenu') ? explode(',', Tools::getValue('orderMenu')) : [];

        foreach ($order as $position => $id_menu) {

            if (!trim($id_menu)) {
                continue;
            }

            $row = ['position' => (int) $position];
            Db::getInstance()->update('topmenu', $row, 'id_topmenu =' . (int) $id_menu);
        }

        $result = [
            'success' => true,
            'message' => $this->la('Position Saved'),
        ];
        die(Tools::jsonEncode($result));
    }

    public function ajaxProcessColumnWrapPosition() {

        $order = Tools::getValue('orderColumnWrap') ? explode(',', Tools::getValue('orderColumnWrap')) : [];

        foreach ($order as $position => $id_wrap) {

            if (!trim($id_wrap)) {
                continue;
            }

            $row = ['position' => (int) $position];
            Db::getInstance()->update('topmenu_columns_wrap', $row, 'id_topmenu_columns_wrap =' . (int) $id_wrap);
        }

        $result = [
            'success' => true,
            'message' => $this->la('Position Saved'),
        ];
        die(Tools::jsonEncode($result));
    }

    public function ajaxProcessColumnPosition() {

        $order = Tools::getValue('orderColumn') ? explode(',', Tools::getValue('orderColumn')) : [];

        foreach ($order as $position => $id_column) {

            if (!trim($id_column)) {
                continue;
            }

            $row = ['position' => (int) $position];
            Db::getInstance()->update('topmenu_columns', $row, 'id_topmenu_column =' . (int) $id_column);
        }

        $result = [
            'success' => true,
            'message' => $this->la('Position Saved'),
        ];
        die(Tools::jsonEncode($result));
    }

    public function ajaxProcessupdateColumWrap() {

        $idColumn = Tools::getValue('idColumn');
        $idColumnWrap = Tools::getValue('idColumnWrap');

        $exist = TopMenuColumn::getnbColumninWrap($idColumnWrap);

        $topMenuColumn = new TopMenuColumn($idColumn);
        $topMenuColumn->id_topmenu_columns_wrap = $idColumnWrap;
        $topMenuColumn->update();

        $result = [
            'success' => true,
            'exist'   => $exist,
            'message' => $this->la('The group item has been successfully moved'),
        ];

        die(Tools::jsonEncode($result));
    }

    public function getPositionSizeFromArray($positionArray, $toCSSString = true) {

        if (!is_array($positionArray) || sizeof($positionArray) < 4) {
            return '';
        }

        $positionStr = '';

        if ($toCSSString) {

            if (Tools::strlen(trim($positionArray[0])) > 0) {
                $positionStr .= 'top:' . (int) $positionArray[0] . 'px;';
            }

            if (Tools::strlen(trim($positionArray[1])) > 0) {
                $positionStr .= 'right:' . (int) $positionArray[1] . 'px;';
            }

            if (Tools::strlen(trim($positionArray[2])) > 0) {
                $positionStr .= 'bottom:' . (int) $positionArray[2] . 'px;';
            }

            if (Tools::strlen(trim($positionArray[3])) > 0) {
                $positionStr .= 'left:' . (int) $positionArray[3] . 'px;';
            }

        } else {

            foreach ($positionArray as $position) {

                if (Tools::strlen(trim($position)) > 0) {
                    $positionStr .= (int) $position . 'px ';
                } else {
                    $positionStr .= ' ';
                }

            }

        }

        return $positionStr;
    }

    public static function getDataSerialized($data, $type = 'base64') {

        if (is_array($data)) {
            return array_map($type . '_encode', [$data]);
        } else {
            return current(array_map($type . '_encode', [$data]));
        }

    }

    public function ajaxProcessSaveGeneralConfig() {

        
        foreach ($this->_fieldsOptions as $key => $field) {

            if (isset($field['mobile']) && $field['mobile']) {
                continue;
            }

            if ($field['type'] == '4size' || $field['type'] == 'shadow') {
                Configuration::updateValue($key, $this->getBorderSizeFromArray(Tools::getValue($key)));
            } else
            if ($field['type'] == '4size_position') {
                Configuration::updateValue($key, $this->getPositionSizeFromArray(Tools::getValue($key), false));
            } else
            if ($field['type'] == 'gradient') {
                $gradientValue = Tools::getValue($key);
                $newValue = $gradientValue[0] . (Tools::getValue($key . '_gradient') && isset($gradientValue[1]) && $gradientValue[1] ? $this->gradient_separator . $gradientValue[1] : '');
                Configuration::updateValue($key, $newValue);
            } else
            if ($field['type'] == 'textLang') {
                $languages = Language::getLanguages(false);
                $list = [];

                foreach ($languages as $language) {
                    $list[(int) $language['id_lang']] = (isset($field['cast']) ? $field['cast'](Tools::getValue($key . '_' . $language['id_lang'])) : Tools::getValue($key . '_' . $language['id_lang']));
                }

                Configuration::updateValue($key, $list);
            } else
            if ($field['type'] == 'image') {

                if (isset($_FILES[$key]) && is_array($_FILES[$key]) && isset($_FILES[$key]['size']) && $_FILES[$key]['size'] > 0 && isset($_FILES[$key]['tmp_name']) && isset($_FILES[$key]['error']) && !$_FILES[$key]['error'] && file_exists($_FILES[$key]['tmp_name']) && filesize($_FILES[$key]['tmp_name']) > 0) {
                    $val = 'data:' . (isset($_FILES[$key]['type']) && !empty($_FILES[$key]['type']) && preg_match('/image/', $_FILES[$key]['type']) ? $_FILES[$key]['type'] : 'image/jpg') . ';base64,' . self::getDataSerialized(Tools::file_get_contents($_FILES[$key]['tmp_name']));
                    Configuration::updateValue($key, $val);
                } else
                if (Configuration::get($key) === false && !Tools::getValue($key . '_delete')) {
                    Configuration::updateValue($key, $field['default']);
                }

                if (Tools::getValue($key . '_delete')) {
                    Configuration::updateValue($key, '');
                }

            } else {

                if (!isset($field['disable'])) {
                    Configuration::updateValue($key, (isset($field['cast']) ? $field['cast'](Tools::getValue($key)) : Tools::getValue($key)));
                }

            }

        }

        

        

        $result = [
            'success' => true,
            'message' => $this->la('Configuration updated successfully'),
        ];

        die(Tools::jsonEncode($result));
    }

    public function ajaxProcessSaveMobileConfig() {

        
        foreach ($this->_fieldsOptions as $key => $field) {

            if (isset($field['mobile']) && $field['mobile']) {

                if ($field['type'] == '4size' || $field['type'] == 'shadow') {
                    Configuration::updateValue($key, $this->getBorderSizeFromArray(Tools::getValue($key)));
                } else
                if ($field['type'] == '4size_position') {
                    Configuration::updateValue($key, $this->getPositionSizeFromArray(Tools::getValue($key), false));
                } else
                if ($field['type'] == 'gradient') {
                    $gradientValue = Tools::getValue($key);
                    $newValue = $gradientValue[0] . (Tools::getValue($key . '_gradient') && isset($gradientValue[1]) && $gradientValue[1] ? $this->gradient_separator . $gradientValue[1] : '');
                    Configuration::updateValue($key, $newValue);
                } else
                if ($field['type'] == 'textLang') {
                    $languages = Language::getLanguages(false);
                    $list = [];

                    foreach ($languages as $language) {
                        $list[(int) $language['id_lang']] = (isset($field['cast']) ? $field['cast'](Tools::getValue($key . '_' . $language['id_lang'])) : Tools::getValue($key . '_' . $language['id_lang']));
                    }

                    Configuration::updateValue($key, $list);
                } else
                if ($field['type'] == 'image') {

                    if (isset($_FILES[$key]) && is_array($_FILES[$key]) && isset($_FILES[$key]['size']) && $_FILES[$key]['size'] > 0 && isset($_FILES[$key]['tmp_name']) && isset($_FILES[$key]['error']) && !$_FILES[$key]['error'] && file_exists($_FILES[$key]['tmp_name']) && filesize($_FILES[$key]['tmp_name']) > 0) {
                        $val = 'data:' . (isset($_FILES[$key]['type']) && !empty($_FILES[$key]['type']) && preg_match('/image/', $_FILES[$key]['type']) ? $_FILES[$key]['type'] : 'image/jpg') . ';base64,' . self::getDataSerialized(Tools::file_get_contents($_FILES[$key]['tmp_name']));
                        Configuration::updateValue($key, $val);
                    } else
                    if (Configuration::get($key) === false && !Tools::getValue($key . '_delete')) {
                        Configuration::updateValue($key, $field['default']);
                    }

                    if (Tools::getValue($key . '_delete')) {
                        Configuration::updateValue($key, '');
                    }

                } else {
                    if (!isset($field['disable'])) {
                        Configuration::updateValue($key, (isset($field['cast']) ? $field['cast'](Tools::getValue($key)) : Tools::getValue($key)));
                    }

                }

            }

        }



        

        $result = [
            'success' => true,
            'message' => $this->la('Configuration updated successfully'),
        ];

        die(Tools::jsonEncode($result));
    }


}
