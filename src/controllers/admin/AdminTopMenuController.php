<?php

/**
 * Class AdminTopMenuControllerCore
 *
 * @since 1.9.1.0
 */
class AdminTopMenuControllerCore extends AdminController {

    // @codingStandardsIgnoreEnd

    const GLOBAL_CSS_FILE = _PS_ROOT_DIR_ . '/css/ephtopmenu_global.css';
    const ADVANCED_CSS_FILE = _PS_ROOT_DIR_ . '/css/ephtopmenu_advanced.css';
    const ADVANCED_CSS_FILE_RESTORE = _PS_ROOT_DIR_ . '/css/reset-ephtopmenu_advanced.css';
    const DYN_CSS_FILE = _PS_ROOT_DIR_ . '/css/ephtopmenu.css';

    private $gradient_separator = '-';

    private $font_families = [
        'Arial, Helvetica, sans-serif',
        "'Arial Black', Gadget, sans-serif",
        "'Bookman Old Style', serif",
        "'Comic Sans MS', cursive",
        'Courier, monospace',
        "'Courier New', Courier, monospace",
        'Garamond, serif',
        'Georgia, serif',
        'Impact, Charcoal, sans-serif',
        "'Lucida Console', Monaco, monospace",
        "'Lucida Sans Unicode', 'Lucida Grande', sans-serif",
        "'MS Sans Serif', Geneva, sans-serif",
        "'MS Serif', 'New York', sans-serif",
        "'Palatino Linotype', 'Book Antiqua', Palatino, serif",
        'Symbol, sans-serif',
        'Tahoma, Geneva, sans-serif',
        "'Times New Roman', Times, serif",
        "'Trebuchet MS', Helvetica, sans-serif",
        'Verdana, Geneva, sans-serif',
        'Webdings, sans-serif',
        "Wingdings, 'Zapf Dingbats', sans-serif",
    ];

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
		$this->publicName = $this->l('Menu Front Office');
        $this->lang = true;
        $this->context = Context::getContext();

        parent::__construct();

        $this->link_targets = [
            0         => $this->l('No target. W3C compliant.'),
            '_self'   => $this->l('Open document in the same frame (_self)'),
            '_blank'  => $this->l('Open document in a new window (_blank)'),
            '_top'    => $this->l('Open document in the same window (_top)'),
            '_parent' => $this->l('Open document in the parent frame (_parent)'),
        ];

        $this->_fieldsOptions = [
            'EPH_USE_PHENYXMENU'                 => [
                'title'   => $this->l('Activate integrated PhenyxShop menu'),
                'cast'    => 'intval',
                'type'    => 'bool',
                'default' => true,
            ],
            'EPHTM_CONT_CLASSES'                 => [
                'title'    => $this->l('Menu container (.ephtm_menu_container)'),
                'desc'     => $this->l('On bootstrap themes, you may have to enter "container" in order to center the menu'),
                'type'     => 'text',
                'default'  => (version_compare(_PS_VERSION_, '1.7.0.0', '>=') ? 'container' : ''),
                'advanced' => true,
            ],
            'EPHTM_RESP_CONT_CLASSES'            => [
                'title'    => $this->l('Menu (#ephtm_menu)'),
                'type'     => 'text',
                'default'  => '',
                'advanced' => true,
            ],
            'EPHTM_MENU_HAMBURGER_SELECTORS'     => [
                'title'    => $this->l('Selector of hamburger icon'),
                'desc'     => $this->l('On default theme, should be "#menu-icon, .menu-icon" most of the time'),
                'type'     => 'text',
                'default'  => '#menu-icon, .menu-icon',
                'advanced' => true,
            ],
            'EPHTM_INNER_CLASSES'                => [
                'title'    => $this->l('Menu subcontainer (#ephtm_menu_inner)'),
                'desc'     => $this->l('On bootstrap themes, you may have to enter "container" in order to center the menu when using sticky view'),
                'type'     => 'text',
                'default'  => 'clearfix',
                'advanced' => true,
            ],
            'EPHTM_RESPONSIVE_MODE'              => [
                'title'   => $this->l('Activate responsive mode'),
                'desc'    => $this->l('Enable only if your theme manage this behaviour'),
                'cast'    => 'intval',
                'type'    => 'bool',
                'default' => true,
                'mobile'  => true,
            ],
            'EPHTM_RESPONSIVE_THRESHOLD'         => [
                'title'   => $this->l('Activate mobile mode up to'),
                'desc'    => '',
                'type'    => 'text',
                'default' => '767',
                'mobile'  => true,
                'suffix'  => 'px',
            ],
            'EPHTM_RESP_TOGGLE_ENABLED'          => [
                'title'   => $this->l('Activate menu toggle mode'),
                'desc'    => $this->l('Enable only if your theme doesn\'t manage an "hamburger" icon'),
                'cast'    => 'intval',
                'type'    => 'bool',
                'default' => true,
                'mobile'  => true,
            ],
            'EPHTM_RESP_TOGGLE_HEIGHT'           => [
                'title'   => $this->l('Height'),
                'desc'    => '',
                'type'    => 'text',
                'default' => '40',
                'mobile'  => true,
                'class'   => 'resp_toggle',
                'suffix'  => 'px',
            ],
            'EPHTM_RESP_TOGGLE_FONT_SIZE'        => [
                'title'      => $this->l('Text size (px)'),
                'desc'       => '',
                'type'       => 'select',
                'default'    => 16,
                'list'       => [],
                'identifier' => 'id',
                'mobile'     => true,
                'class'      => 'resp_toggle',
            ],
            'EPHTM_RESP_TOGGLE_BG_COLOR_OP'      => [
                'title'   => $this->l('Background color (open state)'),
                'desc'    => '',
                'type'    => 'gradient',
                'default' => '#ffffff',
                'mobile'  => true,
                'class'   => 'resp_toggle',
            ],
            'EPHTM_RESP_TOGGLE_BG_COLOR_CL'      => [
                'title'   => $this->l('Background color (close state)'),
                'desc'    => '',
                'type'    => 'gradient',
                'default' => '#e5e5e5',
                'mobile'  => true,
                'class'   => 'resp_toggle',
            ],
            'EPHTM_RESP_TOGGLE_COLOR_OP'         => [
                'title'   => $this->l('Text color (opened state)'),
                'desc'    => '',
                'type'    => 'color',
                'default' => '#333333',
                'mobile'  => true,
                'class'   => 'resp_toggle',
            ],
            'EPHTM_RESP_TOGGLE_COLOR_CL'         => [
                'title'   => $this->l('Text color (closed state)'),
                'desc'    => '',
                'type'    => 'color',
                'default' => '#666666',
                'mobile'  => true,
                'class'   => 'resp_toggle',
            ],
            'EPHTM_RESP_TOGGLE_ICON'             => [
                'title'   => $this->l('Icon'),
                'desc'    => '',
                'type'    => 'image',
                'default' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYAgMAAACdGdVrAAAACVBMVEUAAAAAAAAAAACDY+nAAAAAAnRSTlMA3Pn2U8cAAAAaSURBVAjXY4CCrFVAsJJhFRigUjA5FEBvfQDmRTo/uCG3BQAAAABJRU5ErkJggg==',
                'mobile'  => true,
                'class'   => 'resp_toggle',
            ],
            'EPHTM_RESP_ENABLE_STICKY'           => [
                'title'      => $this->l('Enable Sticky mode on mobile?'),
                'desc'       => $this->l('We recommend to disable the sticky mode if the menu is in a hamburger type sidebar'),
                'type'       => 'bool',
                'default'    => (version_compare(_PS_VERSION_, '1.7.0.0', '>=') ? false : true),
                'identifier' => 'id',
                'mobile'     => true,
                'class'      => 'mobile_sticky',
            ],
            'EPHTM_RESP_MENU_PADDING'            => [
                'title'   => $this->l('Inner spaces - padding (px)'),
                'desc'    => '',
                'type'    => '4size',
                'default' => '5px 10px 5px 10px',
                'mobile'  => true,
            ],
            'ATMR_MENU_MARGIN'                   => [
                'title'   => $this->l('Outer spaces - margin (px)'),
                'desc'    => '',
                'type'    => '4size',
                'default' => '0 0 0 0',
                'mobile'  => true,
            ],
            'EPHTM_RESP_MENU_FONT_SIZE'          => [
                'title'      => $this->l('Text size (px)'),
                'desc'       => '',
                'type'       => 'select',
                'default'    => 18,
                'list'       => [],
                'identifier' => 'id',
                'mobile'     => true,
            ],
            'ATMR_MENU_FONT_BOLD'                => [
                'title'   => $this->l('Text in bold'),
                'desc'    => '',
                'cast'    => 'intval',
                'type'    => 'bool',
                'default' => true,
                'mobile'  => true,
            ],
            'ATMR_MENU_FONT_TRANSFORM'           => [
                'title'      => $this->l('Text transformation'),
                'desc'       => '',
                'type'       => 'select',
                'default'    => 'uppercase',
                'list'       => [],
                'identifier' => 'id',
                'mobile'     => true,
            ],
            'ATMR_MENU_FONT_FAMILY'              => [
                'title'      => $this->l('Text font'),
                'desc'       => '',
                'type'       => 'select',
                'default'    => 0,
                'list'       => [],
                'identifier' => 'id',
                'mobile'     => true,
            ],
            'ATMR_MENU_BGCOLOR_OP'               => [
                'title'   => $this->l('Background color (opened state)'),
                'desc'    => '',
                'type'    => 'gradient',
                'default' => '#333333-#000000',
                'mobile'  => true,
            ],
            'ATMR_MENU_BGCOLOR_CL'               => [
                'title'   => $this->l('Background color (closed state)'),
                'desc'    => '',
                'type'    => 'gradient',
                'default' => '',
                'mobile'  => true,
            ],
            'ATMR_MENU_COLOR'                    => [
                'title'   => $this->l('Text color'),
                'desc'    => '',
                'type'    => 'color',
                'default' => '#484848',
                'mobile'  => true,
            ],
            'ATMR_MENU_BORDERCOLOR'              => [
                'title'   => $this->l('Border color'),
                'desc'    => '',
                'type'    => 'color',
                'default' => '#d6d4d4',
                'mobile'  => true,
            ],
            'ATMR_MENU_BORDERSIZE'               => [
                'title'   => $this->l('Border width (px)'),
                'desc'    => '',
                'type'    => '4size',
                'default' => '0 1px 1px 1px',
                'mobile'  => true,
            ],
            'ATMR_SUBMENU_BGCOLOR'               => [
                'title'   => $this->l('Background color'),
                'desc'    => '',
                'type'    => 'gradient',
                'default' => '#ffffff-#fcfcfc',
                'mobile'  => true,
            ],
            'ATMR_SUBMENU_BORDERCOLOR'           => [
                'title'   => $this->l('Border color'),
                'desc'    => '',
                'type'    => 'color',
                'default' => '#e5e5e5',
                'mobile'  => true,
            ],
            'ATMR_SUBMENU_BORDERSIZE'            => [
                'title'   => $this->l('Border width (px)'),
                'desc'    => '',
                'type'    => '4size',
                'default' => '0 1px 0 1px',
                'mobile'  => true,
            ],
            'EPHTM_RESP_SUBMENU_ICON_OP'         => [
                'title'   => $this->l('Icon for opened state'),
                'desc'    => '',
                'type'    => 'image',
                'default' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYBAMAAAASWSDLAAAAFVBMVEUAAAAAAAAAAAAAAAAAAAAAAAAAAAASAQCkAAAABnRSTlMAHiXy6t8iJwLjAAAARUlEQVQY02OgKWBUAJFMYJJB1AhEChuCOSLJCkBpNxAHRBsBRVIUIJpUkhVgEmAlIKVgAFIDUgmXgkmAzXWCMqA20hgAAI+xB05evnCbAAAAAElFTkSuQmCC',
                'mobile'  => true,
            ],
            'EPHTM_RESP_SUBMENU_ICON_CL'         => [
                'title'   => $this->l('Icon for closed state'),
                'desc'    => '',
                'type'    => 'image',
                'default' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYBAMAAAASWSDLAAAAFVBMVEUAAAAAAAAAAAAAAAAAAAAAAAAAAAASAQCkAAAABnRSTlMAHiXy6t8iJwLjAAAANUlEQVQY02MgFwgisZmMFZA4Zo5IUiLJSFKMbkZESqUoYKjDNFw5RYAYCSckW0IEULxAPgAAZQ0HP01tIysAAAAASUVORK5CYII=',
                'mobile'  => true,
            ],
            'ATMR_COLUMNWRAP_PADDING'            => [
                'title'   => $this->l('Inner spaces - padding (px)'),
                'desc'    => '',
                'type'    => '4size',
                'default' => '0 0 0 0',
                'mobile'  => true,
            ],
            'ATMR_COLUMNWRAP_MARGIN'             => [
                'title'   => $this->l('Outer spaces - margin (px)'),
                'desc'    => '',
                'type'    => '4size',
                'default' => '0 0 0 0',
                'mobile'  => true,
            ],
            'ATMR_COLUMNWRAP_BORDERCOLOR'        => [
                'title'   => $this->l('Border color'),
                'desc'    => '',
                'type'    => 'color',
                'default' => '#e5e5e5',
                'mobile'  => true,
            ],
            'ATMR_COLUMNWRAP_BORDERSIZE'         => [
                'title'   => $this->l('Border width (px)'),
                'desc'    => '',
                'type'    => '4size',
                'default' => '0 0 1px 0',
                'mobile'  => true,
            ],
            'ATMR_COLUMN_PADDING'                => [
                'title'   => $this->l('Inner spaces - padding (px)'),
                'desc'    => '',
                'type'    => '4size',
                'default' => '0 0 5px 0',
                'mobile'  => true,
            ],
            'ATMR_COLUMN_MARGIN'                 => [
                'title'   => $this->l('Outer spaces - margin (px)'),
                'desc'    => '',
                'type'    => '4size',
                'default' => '0 10px 5px 10px',
                'mobile'  => true,
            ],
            'ATMR_COLUMNTITLE_PADDING'           => [
                'title'   => $this->l('Inner spaces - padding (px)'),
                'desc'    => '',
                'type'    => '4size',
                'default' => '0 0 0 0',
                'mobile'  => true,
            ],
            'ATMR_COLUMNTITLE_MARGIN'            => [
                'title'   => $this->l('Outer spaces - margin (px)'),
                'desc'    => '',
                'type'    => '4size',
                'default' => '8px 10px 8px 0',
                'mobile'  => true,
            ],
            'EPHTM_RESP_COLUMN_FONT_SIZE'        => [
                'title'      => $this->l('Text size (px)'),
                'desc'       => '',
                'type'       => 'select',
                'default'    => 18,
                'list'       => [],
                'identifier' => 'id',
                'mobile'     => true,
            ],
            'ATMR_COLUMN_FONT_BOLD'              => [
                'title'   => $this->l('Text in bold'),
                'desc'    => '',
                'cast'    => 'intval',
                'type'    => 'bool',
                'default' => true,
                'mobile'  => true,
            ],
            'ATMR_COLUMN_FONT_TRANSFORM'         => [
                'title'      => $this->l('Text transformation'),
                'desc'       => '',
                'type'       => 'select',
                'default'    => 'none',
                'list'       => [],
                'identifier' => 'id',
                'mobile'     => true,
            ],
            'ATMR_COLUMN_FONT_FAMILY'            => [
                'title'      => $this->l('Text font'),
                'desc'       => '',
                'type'       => 'select',
                'default'    => 0,
                'list'       => [],
                'identifier' => 'id',
                'mobile'     => true,
            ],
            'ATMR_COLUMN_TITLE_COLOR'            => [
                'title'   => $this->l('Text color'),
                'desc'    => '',
                'type'    => 'color',
                'default' => '#333333',
                'mobile'  => true,
            ],
            'ATMR_COLUMN_ITEM_PADDING'           => [
                'title'   => $this->l('Inner spaces - padding (px)'),
                'desc'    => '',
                'type'    => '4size',
                'default' => '5px 0 5px 10px',
                'mobile'  => true,
            ],
            'ATMR_COLUMN_ITEM_MARGIN'            => [
                'title'   => $this->l('Outer spaces - margin (px)'),
                'desc'    => '',
                'type'    => '4size',
                'default' => '15px 0 15px 0',
                'mobile'  => true,
            ],
            'EPHTM_RESP_COLUMN_ITEM_FONT_SIZE'   => [
                'title'      => $this->l('Text size (px)'),
                'desc'       => '',
                'type'       => 'select',
                'default'    => 16,
                'list'       => [],
                'identifier' => 'id',
                'mobile'     => true,
            ],
            'ATMR_COLUMN_ITEM_FONT_BOLD'         => [
                'title'   => $this->l('Text in bold'),
                'desc'    => '',
                'cast'    => 'intval',
                'type'    => 'bool',
                'default' => false,
                'mobile'  => true,
            ],
            'ATMR_COLUMN_ITEM_FONT_TRANSFORM'    => [
                'title'      => $this->l('Text transformation'),
                'desc'       => '',
                'type'       => 'select',
                'default'    => 'none',
                'list'       => [],
                'identifier' => 'id',
                'mobile'     => true,
            ],
            'ATMR_COLUMN_ITEM_FONT_FAMILY'       => [
                'title'      => $this->l('Text font'),
                'desc'       => '',
                'type'       => 'select',
                'default'    => 0,
                'list'       => [],
                'identifier' => 'id',
                'mobile'     => true,
            ],
            'ATMR_COLUMN_ITEM_COLOR'             => [
                'title'   => $this->l('Text color'),
                'desc'    => '',
                'type'    => 'color',
                'default' => '#777777',
                'mobile'  => true,
            ],
            'EPHTM_MENU_CONT_HOOK'               => [
                'title'      => $this->l('Menu position'),
                'onchange'   => 'setMenuContHook(this.value);',
                'desc'       => '',
                'type'       => 'select',
                'default'    => 'top',
                'list'       => [
                    [
                        'id'   => 'top',
                        'name' => 'displayTop ' . $this->l('(default)'),
                    ],
                    [
                        'id'   => 'nav',
                        'name' => 'displayNav',
                    ],
                ],
                'identifier' => 'id',
                'default'    => 'top',
            ],
            'EPHTM_THEME_COMPATIBILITY_MODE'     => [
                'title'   => $this->l('Activate theme compatibility mode'),
                'desc'    => $this->l('Enable only if theme layout is corrupted after installation'),
                'cast'    => 'intval',
                'type'    => 'bool',
                'default' => true,
            ],
            'EPHTM_CACHE'                        => [
                'title'   => $this->l('Activate cache (faster processing)'),
                'desc'    => '',
                'cast'    => 'intval',
                'type'    => 'bool',
                'default' => true,
            ],
            'EPHTM_OBFUSCATE_LINK'               => [
                'title'   => $this->l('Obfuscate all menu links except level 1 (improve link juice - SEO)'),
                'desc'    => '',
                'cast'    => 'intval',
                'type'    => 'bool',
                'default' => false,
            ],
            'EPHTM_AUTOCOMPLET_SEARCH'           => [
                'title'   => $this->l('Activate autocompletion in search input'),
                'desc'    => '',
                'cast'    => 'intval',
                'type'    => 'bool',
                'default' => true,
            ],
            'EPHTM_MENU_CONT_PADDING'            => [
                'title'   => $this->l('Inner spaces - padding (px)'),
                'desc'    => '',
                'type'    => '4size',
                'default' => '0 0 0 0',
            ],
            'EPHTM_MENU_CONT_MARGIN'             => [
                'title'   => $this->l('Outer spaces - margin (px)'),
                'desc'    => '',
                'type'    => '4size',
                'default' => '20px 0 0 0',
            ],
            'EPHTM_MENU_CONT_BORDERCOLOR'        => [
                'title'   => $this->l('Border color'),
                'desc'    => '',
                'type'    => 'color',
                'default' => '#333333',
            ],
            'EPHTM_MENU_CONT_BORDERSIZE'         => [
                'title'   => $this->l('Border width (px)'),
                'desc'    => '',
                'type'    => '4size',
                'default' => '5px 0 0 0',
            ],
            'EPHTM_MENU_CONT_POSITION'           => [
                'title'      => $this->l('Position'),
                'desc'       => '',
                'type'       => 'select',
                'default'    => 'relative',
                'list'       => [
                    [
                        'id'   => 'relative',
                        'name' => $this->l('Relative (default)'),
                    ],
                    [
                        'id'   => 'absolute',
                        'name' => $this->l('Absolute'),
                    ],
                    [
                        'id'   => 'sticky',
                        'name' => $this->l('Sticky'),
                    ],
                ],
                'identifier' => 'id',
            ],
            'EPHTM_MENU_CONT_POSITION_TRBL'      => [
                'title'   => $this->l('Positioning (px)'),
                'desc'    => '',
                'type'    => '4size_position',
                'default' => '',
            ],
            'EPHTM_MENU_GLOBAL_ACTIF'            => [
                'title'   => $this->l('Highlight current tab (status:active)'),
                'desc'    => $this->l('Background and font color values from over settings will be used'),
                'cast'    => 'intval',
                'type'    => 'bool',
                'default' => true,
            ],
            'EPHTM_MENU_GLOBAL_WIDTH'            => [
                'title'   => $this->l('Width'),
                'desc'    => $this->l('Put 0 for automatic width'),
                'type'    => 'text',
                'default' => '0',
                'suffix'  => 'px',
            ],
            'EPHTM_MENU_GLOBAL_HEIGHT'           => [
                'title'   => $this->l('Height'),
                'desc'    => '',
                'type'    => 'text',
                'default' => '56',
                'suffix'  => 'px',
            ],
            'EPHTM_MENU_GLOBAL_PADDING'          => [
                'title'   => $this->l('Inner spaces - padding (px)'),
                'desc'    => '',
                'type'    => '4size',
                'default' => '0 0 0 0',
            ],
            'EPHTM_MENU_GLOBAL_MARGIN'           => [
                'title'   => $this->l('Outer spaces - margin (px)'),
                'desc'    => '',
                'type'    => '4size',
                'default' => '0 0 0 0',
            ],
            'EPHTM_MENU_GLOBAL_ZINDEX'           => [
                'title'   => $this->l('Z-index value (CSS)'),
                'desc'    => $this->l('Increase if your cart block is under the menu bar'),
                'type'    => 'text',
                'default' => '9',
                'short'   => true,
            ],
            'EPHTM_MENU_GLOBAL_BGCOLOR'          => [
                'title'   => $this->l('Background color'),
                'desc'    => '',
                'type'    => 'gradient',
                'default' => '#f6f6f6-#e6e6e6',
            ],
            'EPHTM_MENU_GLOBAL_BORDERCOLOR'      => [
                'title'   => $this->l('Border color'),
                'desc'    => '',
                'type'    => 'color',
                'default' => '#e9e9e9',
            ],
            'EPHTM_MENU_GLOBAL_BORDERSIZE'       => [
                'title'   => $this->l('Border width (px)'),
                'desc'    => '',
                'type'    => '4size',
                'default' => '0 0 3px 0',
            ],
            'EPHTM_MENU_BOX_SHADOW'              => [
                'title'   => $this->l('Drop shadow'),
                'desc'    => '',
                'type'    => 'shadow',
                'default' => '0px 5px 13px 0px',
            ],
            'EPHTM_MENU_BOX_SHADOWCOLOR'         => [
                'title'   => $this->l('Drop shadow color'),
                'desc'    => '',
                'type'    => 'color',
                'default' => '#000000',
            ],
            'EPHTM_MENU_BOX_SHADOWOPACITY'       => [
                'title'   => $this->l('Drop shadow opacity'),
                'desc'    => '',
                'type'    => 'slider',
                'default' => 20,
                'min'     => 0,
                'max'     => 100,
                'step'    => 1,
                'suffix'  => '%',
            ],
            'EPHTM_MENU_CENTER_TABS'             => [
                'title'      => $this->l('Tabs centering'),
                'desc'       => $this->l('Choose a position for the tabs within the menu bar (desktop only)'),
                'type'       => 'select',
                'list'       => [
                    [
                        'id'   => 1,
                        'name' => $this->l('Align to the left (default)'),
                    ],
                    [
                        'id'   => 2,
                        'name' => $this->l('Center'),
                    ],
                    [
                        'id'   => 4,
                        'name' => $this->l('Align to the right'),
                    ],
                    [
                        'id'   => 3,
                        'name' => $this->l('Justify'),
                    ],
                ],
                'default'    => 1,
                'identifier' => 'id',
            ],
            'EPHTM_MENU_PADDING'                 => [
                'title'   => $this->l('Inner spaces - padding (px)'),
                'desc'    => '',
                'type'    => '4size',
                'default' => '0 20px 0 20px',
            ],
            'EPHTM_MENU_MARGIN'                  => [
                'title'   => $this->l('Outer spaces - margin (px)'),
                'desc'    => '',
                'type'    => '4size',
                'default' => '0 0 0 0',
            ],
            'EPHTM_MENU_FONT_SIZE'               => [
                'title'      => $this->l('Text size (px)'),
                'desc'       => '',
                'type'       => 'select',
                'default'    => 18,
                'list'       => [],
                'identifier' => 'id',
            ],
            'EPHTM_MENU_FONT_BOLD'               => [
                'title'   => $this->l('Text in bold'),
                'desc'    => '',
                'cast'    => 'intval',
                'type'    => 'bool',
                'default' => false,
            ],
            'EPHTM_MENU_FONT_UNDERLINE'          => [
                'title'   => $this->l('Underline text'),
                'desc'    => '',
                'cast'    => 'intval',
                'type'    => 'bool',
                'default' => false,
            ],
            'EPHTM_MENU_FONT_UNDERLINEOV'        => [
                'title'   => $this->l('Underline text (on mouse over)'),
                'desc'    => '',
                'cast'    => 'intval',
                'type'    => 'bool',
                'default' => false,
            ],
            'EPHTM_MENU_FONT_TRANSFORM'          => [
                'title'      => $this->l('Text transformation'),
                'desc'       => '',
                'type'       => 'select',
                'default'    => 'none',
                'list'       => [],
                'identifier' => 'id',
            ],
            'EPHTM_MENU_FONT_FAMILY'             => [
                'title'      => $this->l('Text font'),
                'desc'       => '',
                'type'       => 'select',
                'default'    => 0,
                'list'       => [],
                'identifier' => 'id',
            ],
            'EPHTM_MENU_COLOR'                   => [
                'title'   => $this->l('Text color'),
                'desc'    => '',
                'type'    => 'color',
                'default' => '#484848',
            ],
            'EPHTM_MENU_COLOR_OVER'              => [
                'title'   => $this->l('Text color (on mouse over)'),
                'desc'    => '',
                'type'    => 'color',
                'default' => '#ffffff',
            ],
            'EPHTM_MENU_BGCOLOR'                 => [
                'title'   => $this->l('Background color'),
                'desc'    => '',
                'type'    => 'gradient',
                'default' => '',
            ],
            'EPHTM_MENU_BGCOLOR_OVER'            => [
                'title'   => $this->l('Background color (on mouse over)'),
                'desc'    => '',
                'type'    => 'gradient',
                'default' => '#333333-#000000',
            ],
            'EPHTM_MENU_BORDERCOLOR'             => [
                'title'   => $this->l('Border color'),
                'desc'    => '',
                'type'    => 'color',
                'default' => '#d6d4d4',
            ],
            'EPHTM_MENU_BORDERSIZE'              => [
                'title'   => $this->l('Border width (px)'),
                'desc'    => '',
                'type'    => '4size',
                'default' => '0 1px 0 1px',
            ],
            'EPHTM_SUBMENU_WIDTH'                => [
                'title'   => $this->l('Width'),
                'desc'    => $this->l('Put 0 for automatic width'),
                'type'    => 'text',
                'default' => '0',
                'suffix'  => 'px',
            ],
            'EPHTM_SUBMENU_HEIGHT'               => [
                'title'   => $this->l('Minimum height'),
                'desc'    => '',
                'type'    => 'text',
                'default' => '0',
                'suffix'  => 'px',
            ],
            'EPHTM_SUBMENU_ZINDEX'               => [
                'title'   => $this->l('Z-index value (CSS)'),
                'desc'    => $this->l('Increase if submenus are under your main content'),
                'type'    => 'text',
                'default' => '1000',
            ],
            'EPHTM_SUBMENU_OPEN_METHOD'          => [
                'title'      => $this->l('Opening method'),
                'desc'       => '',
                'cast'       => 'intval',
                'type'       => 'select',
                'default'    => 1,
                'list'       => [
                    [
                        'id'   => 1,
                        'name' => $this->l('On mouse over'),
                    ],
                    [
                        'id'   => 2,
                        'name' => $this->l('On mouse click'),
                    ],
                ],
                'identifier' => 'id',
            ],
            'EPHTM_SUBMENU_POSITION'             => [
                'title'      => $this->l('Position'),
                'desc'       => '',
                'cast'       => 'intval',
                'type'       => 'select',
                'default'    => 2,
                'list'       => [
                    [
                        'id'   => 1,
                        'name' => $this->l('Left-aligned current menu'),
                    ],
                    [
                        'id'   => 2,
                        'name' => $this->l('Left-aligned global menu'),
                    ],
                ],
                'identifier' => 'id',
            ],
            'EPHTM_SUBMENU_BGCOLOR'              => [
                'title'   => $this->l('Background color'),
                'desc'    => '',
                'type'    => 'gradient',
                'default' => '#ffffff-#fcfcfc',
            ],
            'EPHTM_SUBMENU_BGOPACITY'            => [
                'title'   => $this->l('Background color opacity'),
                'desc'    => '',
                'type'    => 'slider',
                'default' => 100,
                'min'     => 0,
                'max'     => 100,
                'step'    => 1,
                'suffix'  => '%',
            ],
            'EPHTM_SUBMENU_BORDERCOLOR'          => [
                'title'   => $this->l('Border color'),
                'desc'    => '',
                'type'    => 'color',
                'default' => '#e5e5e5',
            ],
            'EPHTM_SUBMENU_BORDERSIZE'           => [
                'title'   => $this->l('Border width (px)'),
                'desc'    => '',
                'type'    => '4size',
                'default' => '0 1px 1px 1px',
            ],
            'EPHTM_SUBMENU_BOX_SHADOW'           => [
                'title'   => $this->l('Drop shadow'),
                'desc'    => '',
                'type'    => 'shadow',
                'default' => '0px 5px 13px 0px',
            ],
            'EPHTM_SUBMENU_BOX_SHADOWCOLOR'      => [
                'title'   => $this->l('Drop shadow color'),
                'desc'    => '',
                'type'    => 'color',
                'default' => '#000000',
            ],
            'EPHTM_SUBMENU_BOX_SHADOWOPACITY'    => [
                'title'   => $this->l('Drop shadow opacity'),
                'desc'    => '',
                'type'    => 'slider',
                'default' => 20,
                'min'     => 0,
                'max'     => 100,
                'step'    => 1,
                'suffix'  => '%',
            ],
            'EPHTM_SUBMENU_OPEN_DELAY'           => [
                'title'   => $this->l('Opening delay'),
                'desc'    => '',
                'type'    => 'slider',
                'default' => 0.3,
                'min'     => 0,
                'max'     => 2,
                'step'    => 0.1,
                'suffix'  => 's',
            ],
            'EPHTM_SUBMENU_FADE_SPEED'           => [
                'title'   => $this->l('Fading effect duration'),
                'desc'    => '',
                'type'    => 'slider',
                'default' => 0.3,
                'min'     => 0,
                'max'     => 2,
                'step'    => 0.1,
                'suffix'  => 's',
            ],
            'EPHTM_COLUMNWRAP_PADDING'           => [
                'title'   => $this->l('Inner spaces - padding (px)'),
                'desc'    => '',
                'type'    => '4size',
                'default' => '0 0 0 0',
            ],
            'EPHTM_COLUMN_PADDING'               => [
                'title'   => $this->l('Inner spaces - padding (px)'),
                'desc'    => '',
                'type'    => '4size',
                'default' => '0 0 0 0',
            ],
            'EPHTM_COLUMN_MARGIN'                => [
                'title'   => $this->l('Outer spaces - margin (px)'),
                'desc'    => '',
                'type'    => '4size',
                'default' => '0 10px 0 10px',
            ],
            'EPHTM_COLUMNTITLE_PADDING'          => [
                'title'   => $this->l('Inner spaces - padding (px)'),
                'desc'    => '',
                'type'    => '4size',
                'default' => '0 0 0 0',
            ],
            'EPHTM_COLUMNTITLE_MARGIN'           => [
                'title'   => $this->l('Outer spaces - margin (px)'),
                'desc'    => '',
                'type'    => '4size',
                'default' => '0 10px 0 0',
            ],
            'EPHTM_COLUMN_FONT_SIZE'             => [
                'title'      => $this->l('Text size (px)'),
                'desc'       => '',
                'type'       => 'select',
                'default'    => 16,
                'list'       => [],
                'identifier' => 'id',
            ],
            'EPHTM_COLUMN_FONT_BOLD'             => [
                'title'   => $this->l('Text in bold'),
                'desc'    => '',
                'cast'    => 'intval',
                'type'    => 'bool',
                'default' => true,
            ],
            'EPHTM_COLUMN_FONT_UNDERLINE'        => [
                'title'   => $this->l('Underline text'),
                'desc'    => '',
                'cast'    => 'intval',
                'type'    => 'bool',
                'default' => false,
            ],
            'EPHTM_COLUMN_FONT_UNDERLINEOV'      => [
                'title'   => $this->l('Underline text (on mouse over)'),
                'desc'    => '',
                'cast'    => 'intval',
                'type'    => 'bool',
                'default' => false,
            ],
            'EPHTM_COLUMN_FONT_TRANSFORM'        => [
                'title'      => $this->l('Text transformation'),
                'desc'       => '',
                'type'       => 'select',
                'default'    => 'none',
                'list'       => [],
                'identifier' => 'id',
            ],
            'EPHTM_COLUMN_FONT_FAMILY'           => [
                'title'      => $this->l('Text font'),
                'desc'       => '',
                'type'       => 'select',
                'default'    => 0,
                'list'       => [],
                'identifier' => 'id',
            ],
            'EPHTM_COLUMN_TITLE_COLOR'           => [
                'title'   => $this->l('Heading text color'),
                'desc'    => '',
                'type'    => 'color',
                'default' => '#333333',
            ],
            'EPHTM_COLUMN_TITLE_COLOR_OVER'      => [
                'title'   => $this->l('Heading text color (on mouse over)'),
                'desc'    => '',
                'type'    => 'color',
                'default' => '#515151',
            ],
            'EPHTM_COLUMN_ITEM_PADDING'          => [
                'title'   => $this->l('Inner spaces - padding (px)'),
                'desc'    => '',
                'type'    => '4size',
                'default' => '3px 0 3px 0',
            ],
            'EPHTM_COLUMN_ITEM_MARGIN'           => [
                'title'   => $this->l('Outer spaces - margin (px)'),
                'desc'    => '',
                'type'    => '4size',
                'default' => '0 0 0 0',
            ],
            'EPHTM_COLUMN_ITEM_FONT_SIZE'        => [
                'title'      => $this->l('Text size (px)'),
                'desc'       => '',
                'type'       => 'select',
                'default'    => 13,
                'list'       => [],
                'identifier' => 'id',
            ],
            'EPHTM_COLUMN_ITEM_FONT_BOLD'        => [
                'title'   => $this->l('Text in bold'),
                'desc'    => '',
                'cast'    => 'intval',
                'type'    => 'bool',
                'default' => false,
            ],
            'EPHTM_COLUMN_ITEM_FONT_UNDERLINE'   => [
                'title'   => $this->l('Underline text'),
                'desc'    => '',
                'cast'    => 'intval',
                'type'    => 'bool',
                'default' => false,
            ],
            'EPHTM_COLUMN_ITEM_FONT_UNDERLINEOV' => [
                'title'   => $this->l('Underline text (on mouse over)'),
                'desc'    => '',
                'cast'    => 'intval',
                'type'    => 'bool',
                'default' => false,
            ],
            'EPHTM_COLUMN_ITEM_FONT_TRANSFORM'   => [
                'title'      => $this->l('Text transformation'),
                'desc'       => '',
                'type'       => 'select',
                'default'    => 'none',
                'list'       => [],
                'identifier' => 'id',
            ],
            'EPHTM_COLUMN_ITEM_FONT_FAMILY'      => [
                'title'      => $this->l('Text font'),
                'desc'       => '',
                'type'       => 'select',
                'default'    => 0,
                'list'       => [],
                'identifier' => 'id',
            ],
            'EPHTM_COLUMN_ITEM_COLOR'            => [
                'title'   => $this->l('Text color'),
                'desc'    => '',
                'type'    => 'color',
                'default' => '#777777',
            ],
            'EPHTM_COLUMN_ITEM_COLOR_OVER'       => [
                'title'   => $this->l('Text color (on mouse over)'),
                'desc'    => '',
                'type'    => 'color',
                'default' => '#333333',
            ],
        ];

        foreach (array_keys($this->_fieldsOptions) as $key) {

            if (strpos($key, 'FONT_TRANSFORM') !== false) {
                $this->_fieldsOptions[$key]['list'] = [
                    [
                        'id'   => 'none',
                        'name' => $this->l('Normal (inherit)'),
                    ],
                    [
                        'id'   => 'lowercase',
                        'name' => $this->l('lowercase'),
                    ],
                    [
                        'id'   => 'uppercase',
                        'name' => $this->l('UPPERCASE'),
                    ],
                    [
                        'id'   => 'capitalize',
                        'name' => $this->l('Capitalize'),
                    ],
                ];
            } else

            if (strpos($key, 'FONT_FAMILY') !== false) {
                $this->_fieldsOptions[$key]['list'][] = [
                    'id'   => 0,
                    'name' => $this->l('Inherit from my theme'),
                ];

                foreach ($this->font_families as $font_family) {
                    $this->_fieldsOptions[$key]['list'][] = [
                        'id'   => $font_family,
                        'name' => $font_family,
                    ];
                }

            } else

            if (strpos($key, 'FONT_SIZE') !== false) {
                $this->_fieldsOptions[$key]['list'][] = [
                    'id'   => 0,
                    'name' => $this->l('Inherit from my theme'),
                ];

                for ($i = 8; $i <= 30; $i++) {
                    $this->_fieldsOptions[$key]['list'][] = [
                        'id'   => $i,
                        'name' => $i,
                    ];
                }

            }

        }

    }

    
	
	public function setAjaxMedia() {
		
		return $this->pushJS([
			$this->admin_webpath . '/js/tiny_mce/tiny_mce.js',
			$this->admin_webpath . '/js/tinymce.inc.js',
			$this->admin_webpath . '/js/topmenu.js',
            $this->admin_webpath . '/js/popover.js',
            $this->admin_webpath . '/js/colorpicker/colorpicker.js',
            $this->admin_webpath . '/js/codemirror/codemirror.js',
            $this->admin_webpath . '/js/codemirror/css.js',
            $this->admin_webpath . '/js/jquery.tipTip.js',
			$this->admin_webpath . '/js/ace/ace.js',
			$this->admin_webpath . '/js/ace/ext-language_tools.js',
			$this->admin_webpath . '/js/ace/ext-language_tools.js',
			$this->admin_webpath . '/js/codemirror/css.js',
		]);
	}
	
	public function ajaxProcessOpenTargetController() {

		$targetController = $this->targetController;
		
			
		
		$this->setAjaxMedia();

		$data = $this->createTemplate('controllers/'.$this->table.'.tpl');
		$displayMenuFormVars = $this->displayMenuForm();
		$this->_fieldsOptions = $this->_fieldsOptions;
		$this->link_targets = $this->link_targets;
		
		foreach ($displayMenuFormVars as $key => $displayMenuFormVar) {
            $data->assign($key, $displayMenuFormVar);
        }

        $displayConfig = $this->displayConfig();
        $displayMobileConfig = $this->displayMobileConfig();
        $displayAdvancedConfig = $this->displayAdvancedConfig();

        $id_shop = (int) $this->context->shop->id;
        $advanced_css_file = str_replace('.css', '-' . $id_shop . '.css', self::ADVANCED_CSS_FILE);

        if (!file_exists($advanced_css_file)) {
            file_put_contents($advanced_css_file, Tools::file_get_contents(self::ADVANCED_CSS_FILE_RESTORE));
        }
		
		$extracss = $this->pushCSS([
			 $this->admin_webpath . '/themes/' . $this->bo_theme . '/css/topmenu.css',
             $this->admin_webpath . '/themes/' . $this->bo_theme . '/css/popover.css',
             $this->admin_webpath . '/themes/' . $this->bo_theme . '/css/custom-font.css',
             $this->admin_webpath . '/themes/' . $this->bo_theme . '/css/colorpicker/colorpicker.css',
             $this->admin_webpath . '/themes/' . $this->bo_theme . '/css/codemirror/codemirror.css',
             $this->admin_webpath . '/themes/' . $this->bo_theme . '/css/codemirror/default.css',
			 $this->admin_webpath . '/js/ace/aceinput.css'
		
		]);

		
		

		$data->assign([
			'menu_img_dir'              => __PS_BASE_URI__ . $this->admin_webpath . '/themes/' . $this->bo_theme . '/img/topmenu/',
            'bo_imgdir'                 => __PS_BASE_URI__ . $this->admin_webpath . '/themes/' . $this->bo_theme . '/img/',
            'fieldsOptions'             => $displayConfig,
            'fieldsMobileOptions'       => $displayMobileConfig,
            'fieldsAdvanceOptions'      => $displayAdvancedConfig,
            'languages'                 => Language::getLanguages(false),
            'advancedStylesContent'     => Tools::file_get_contents($advanced_css_file),
            'EPH_USE_PHENYXMENU'        => Configuration::get('EPH_USE_PHENYXMENU'),
			'controller'     			=> 'AdminTopMenu',
			'link'           => $this->context->link,
			'extraJs'        => $extraJs,
			'extracss'       => $extracss,
			'extraJs'        => $this->push_js_files,
		]);

		$li = '<li id="uper'.$targetController.'" data-controller="AdminDashboard"><a href="#content'.$targetController.'">'.$this->publicName.'</a><button type="button" class="close tabdetail" data-id="uper'.$targetController.'"><i class="icon icon-times-circle"></i></button></li>';
		$html = '<div id="content'.$targetController.'" class="panel col-lg-12" style="display; flow-root;">' . $data->fetch() . '</div>';

		$result = [
			'li'   => $li,

			'html' => $html,
		];

		die(Tools::jsonEncode($result));
	}


    public function initMain() {

        $vars = [
            'menu_img_dir'            => __PS_BASE_URI__ . $this->admin_webpath . '/themes/' . $this->bo_theme . '/img/topmenu/',
            'display_form'            => $this->displayMenuForm(),
            'display_config'          => $this->displayConfig(),
            'display_mobile_config'   => $this->displayMobileConfig(),
            'display_advanced_styles' => $this->displayAdvancedConfig(),

        ];

        return $this->fetchTemplate('content.tpl', $vars);

    }

    private function initClassVar() {

        $controller = Tools::getValue('controller');
        $this->base_config_url = $_SERVER['SCRIPT_NAME'] . ($controller ? '?controller=' . $controller : '') . '&configure=' . $this->name . '&token=' . Tools::getValue('token');
        $languages = Language::getLanguages(false);
        $this->defaultLanguage = (int) Configuration::get('PS_LANG_DEFAULT');
        $this->_iso_lang = Language::getIsoById($this->context->cookie->id_lang);
        $this->languages = $languages;
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
        $ObjEphenyxTopMenuColumnClass = false;
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
                $ObjEphenyxTopMenuColumnClass = new TopMenuColumn(Tools::getValue('id_topmenu_column'));
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
            'ObjTopMenuColumn'        => $ObjEphenyxTopMenuColumnClass,
            'ObjTopMenuElements'      => $ObjEphenyxTopMenuElementsClass,
            'topMenu_img_dir'         => _PS_MENU_DIR_,
            'menu_img_dir'            => __PS_BASE_URI__ . $this->admin_webpath . '/themes/' . $this->bo_theme . '/img/topmenu/',
            'current_iso_lang'        => Language::getIsoById($this->context->cookie->id_lang),
            'current_id_lang'         => (int) $this->context->language->id,
            'default_language'        => (int) Configuration::get('PS_LANG_DEFAULT'),
            'languages'               => Language::getLanguages(false),
        ];

        return $vars;
    }

    private function getNestedCmsCategories($id_lang) {

        $nestedArray = [];
        $cmsCategories = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
            'SELECT cc.*, ccl.*
            FROM `' . _DB_PREFIX_ . 'cms_category` cc
            ' . Shop::addSqlAssociation('cms_category', 'cc') . '
            LEFT JOIN `' . _DB_PREFIX_ . 'cms_category_lang` ccl ON cc.`id_cms_category` = ccl.`id_cms_category`' . Shop::addSqlRestrictionOnLang('ccl') . '
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

    private function getChildrenCmsCategories(&$cmsList, $cmsCategory, $levelDepth = false) {

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

        if (version_compare(_PS_VERSION_, '1.7.0.0', '<') && isset($this->_fieldsOptions['EPHTM_MENU_CONT_HOOK'])) {
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

            if (!empty($field['mobile']) && version_compare(_PS_VERSION_, '1.7.0.0', '<') && $key == 'EPHTM_RESP_TOGGLE_ENABLED') {
                unset($fieldsOptions[$key]);
            } else

            if (!empty($field['advanced']) && version_compare(_PS_VERSION_, '1.7.0.0', '<') && $key == 'EPHTM_MENU_HAMBURGER_SELECTORS') {
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

    public function displayAdvancedConfig() {

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
                    $return .= '<img src="' . _PS_IMG_ . $type . '_icons/' . $id . '-' . $_iso_lang . '.' . ($row['image_type'] ?: 'jpg') . '" alt="' . htmlentities($row['name'], ENT_COMPAT, 'UTF-8') . '" title="' . htmlentities($row['name'], ENT_COMPAT, 'UTF-8') . '" />';
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
                    $return .= '<img src="' . _PS_IMG_ . $type . '_icons/' . $row['id_top' . $type] . '-' . $_iso_lang . '.' . ($row['image_type'] ?: 'jpg') . '" alt="' . htmlentities($row['name'], ENT_COMPAT, 'UTF-8') . '" title="' . htmlentities($row['name'], ENT_COMPAT, 'UTF-8') . '" />';
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
                    $return .= '<img src="' . _PS_IMG_ . $type . '_icons/' . $row['id_top' . $type] . '-' . $_iso_lang . '.' . ($row['image_type'] ?: 'jpg') . '" alt="' . htmlentities($row['name'], ENT_COMPAT, 'UTF-8') . '" title="' . htmlentities($row['name'], ENT_COMPAT, 'UTF-8') . '" />';
                }

            }

            if (trim($row['name'])) {
                $return .= htmlentities($row['name'], ENT_COMPAT, 'UTF-8');
            } else {
                $return .= $this->l('No label');
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
                    $return .= '<img src="' . _PS_IMG_ . $type . '_icons/' . $row['id_top' . $type] . '-' . $_iso_lang . '.' . ($row['image_type'] ?: 'jpg') . '" alt="' . htmlentities($row['name'], ENT_COMPAT, 'UTF-8') . '" title="' . htmlentities($row['name'], ENT_COMPAT, 'UTF-8') . '" />';
                }

            }

            if (trim($row['name'])) {
                $return .= htmlentities($row['name'], ENT_COMPAT, 'UTF-8');
            } else {
                $return .= htmlentities($row['category_name'], ENT_COMPAT, 'UTF-8');
            }

        } else

        if ($row['type'] == 4) {

            if ($withExtra && trim($row['have_icon'])) {

                if (in_array($row['image_type'], ['i-fa', 'i-mi'])) {

                    if ($row['image_type'] == 'i-mi') {
                        $row['image_class'] = 'zmdi ' . $row['image_class'];
                    }

                    $return .= '<i class="pmAtmIcon ' . $row['image_class'] . '"></i>';
                } else {
                    $return .= '<img src="' . _PS_IMG_ . $type . '_icons/' . $row['id_top' . $type] . '-' . $_iso_lang . '.' . ($row['image_type'] ?: 'jpg') . '" alt="' . htmlentities($row['name'], ENT_COMPAT, 'UTF-8') . '" title="' . htmlentities($row['name'], ENT_COMPAT, 'UTF-8') . '" />';
                }

            }

            if (trim($row['name'])) {
                $return .= htmlentities($row['name'], ENT_COMPAT, 'UTF-8');
            } 

        } else

        if ($row['type'] == 5) {

            if ($withExtra && trim($row['have_icon'])) {

                if (in_array($row['image_type'], ['i-fa', 'i-mi'])) {

                    if ($row['image_type'] == 'i-mi') {
                        $row['image_class'] = 'zmdi ' . $row['image_class'];
                    }

                    $return .= '<i class="pmAtmIcon ' . $row['image_class'] . '"></i>';
                } else {
                    $return .= '<img src="' . _PS_IMG_ . $type . '_icons/' . $row['id_top' . $type] . '-' . $_iso_lang . '.' . ($row['image_type'] ?: 'jpg') . '" alt="' . htmlentities($row['name'], ENT_COMPAT, 'UTF-8') . '" title="' . htmlentities($row['name'], ENT_COMPAT, 'UTF-8') . '" />';
                }

            }

            if (trim($row['name'])) {
                $return .= htmlentities($row['name'], ENT_COMPAT, 'UTF-8');
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
                    $return .= '<img src="' . _PS_IMG_ . $type . '_icons/' . $row['id_top' . $type] . '-' . $_iso_lang . '.' . ($row['image_type'] ?: 'jpg') . '" alt="' . htmlentities($row['name'], ENT_COMPAT, 'UTF-8') . '" title="' . htmlentities($row['name'], ENT_COMPAT, 'UTF-8') . '" />';
                }

            }

            if (trim($row['name'])) {
                $return .= htmlentities($row['name'], ENT_COMPAT, 'UTF-8');
            } else {
                $return .= $this->l('No label');
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
                    $return .= '<img src="' . _PS_IMG_ . $type . '_icons/' . $row['id_topmenu_' . $type] . '-' . $_iso_lang . '.' . ($row['image_type'] ?: 'jpg') . '" alt="' . htmlentities($row['name'], ENT_COMPAT, 'UTF-8') . '" title="' . htmlentities($row['name'], ENT_COMPAT, 'UTF-8') . '" />';
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
                $return .= '<img src="' . _PS_IMG_ . $type . '_icons/' . $row['id_top' . $type] . '-' . $_iso_lang . '.' . ($row['image_type'] ?: 'jpg') . '" alt="' . htmlentities($row['name'], ENT_COMPAT, 'UTF-8') . '" title="' . htmlentities($row['name'], ENT_COMPAT, 'UTF-8') . '" />';
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
                    $return .= '<img src="' . _PS_IMG_ . $type . '_icons/' . $row['id_' . $type] . '-' . $_iso_lang . '.' . ($row['image_type'] ?: 'jpg') . '" alt="' . htmlentities($row['name'], ENT_COMPAT, 'UTF-8') . '" title="' . htmlentities($row['name'], ENT_COMPAT, 'UTF-8') . '" />';
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

    private function getNestedCategories($root_category = null, $id_lang = false) {

        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
            'SELECT c.*, cl.*
            FROM `' . _DB_PREFIX_ . 'category` c
            ' . Shop::addSqlAssociation('category', 'c') . '
            LEFT JOIN `' . _DB_PREFIX_ . 'category_lang` cl ON c.`id_category` = cl.`id_category`' . Shop::addSqlRestrictionOnLang('cl') . '
            RIGHT JOIN `' . _DB_PREFIX_ . 'category` c2 ON c2.`id_category` = ' . (int) $root_category . ' AND c.`nleft` >= c2.`nleft` AND c.`nright` <= c2.`nright`
            WHERE `id_lang` = ' . (int) $id_lang . '
            ORDER BY c.`level_depth` ASC, category_shop.`position` ASC'
        );
        $categories = [];
        $buff = [];

        foreach ($result as $row) {
            $current = &$buff[$row['id_category']];
            $current = $row;

            if (!$row['active']) {
                $current['name'] .= ' ' . $this->l('(disabled)');
            }

            if ($row['id_category'] == $root_category) {
                $categories[$row['id_category']] = &$current;
            } else {
                $buff[$row['id_parent']]['children'][$row['id_category']] = &$current;
            }

        }

        return $categories;
    }

    private function getChildrensCategories(&$categoryList, $categoryInformations, $selected, $levelDepth = false) {

        if (isset($categoryInformations['children']) && self::isFilledArray($categoryInformations['children'])) {

            foreach ($categoryInformations['children'] as $categoryInformations) {
                $categoryList[] = $categoryInformations;
                $this->getChildrensCategories($categoryList, $categoryInformations, $selected, ($levelDepth !== false ? $levelDepth + 1 : $levelDepth));
            }

        }

    }

    public function getType($type) {

        if ($type == 1) {
            return $this->l('CMS');
        } else

        if ($type == 2) {
            return $this->l('Link');
        } else

        if ($type == 3) {
            return $this->l('Category');
        }  else

        if ($type == 6) {
            return $this->l('Search');
        } else

        if ($type == 7) {
            return $this->l('Only image or icon');
        } else

        if ($type == 9) {
            return $this->l('Specific page');
        } else

        if ($type == 10) {
            return $this->l('CMS category');
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
        $default_routes = Dispatcher::getInstance()->default_routes;

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
        $admin_webpath = str_ireplace(_PS_CORE_DIR_, '', _PS_ADMIN_DIR_);
        $admin_webpath = preg_replace('/^' . preg_quote(DIRECTORY_SEPARATOR, '/') . '/', '', $admin_webpath);

        $tpl = $context->smarty->createTemplate('controllers/top_menu/' . $tpl, $context->smarty);
        $tpl->assign(
            [
                'linkTopMenu'            => $context->link->getAdminLink('AdminTopMenu'),
                'AdminTopMenuController' => new AdminTopMenuController(),
                'topMenu_img_dir'        => _PS_MENU_DIR_,
                'menu_img_dir'           => __PS_BASE_URI__ . $admin_webpath . '/themes/' . $this->bo_theme . '/img/topmenu/',
                'current_iso_lang'       => Language::getIsoById($context->cookie->id_lang),
                'current_id_lang'        => (int) $context->language->id,
                'default_language'       => (int) Configuration::get('PS_LANG_DEFAULT'),
                'languages'              => Language::getLanguages(false),
                'options'                => $configOptions,
            ]
        );

        if (is_array($customVars) && count($customVars)) {
            $tpl->assign($customVars);
        }

        return $tpl->fetch();

        //return $context->smarty->fetch('controllers/top_menu/' . $tpl);
    }

    public function getPageList() {

        $pages = Meta::getMetasByIdLang((int) $this->context->cookie->id_lang);
        $default_routes = Dispatcher::getInstance()->default_routes;

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
        $imgIconMenuDirIsWritable = is_writable(_PS_ROOT_DIR_ . '/img/menu_icons');
        $haveDepend = false;

        if ($topMenu->id > 0) {
            $haveDepend = TopMenu::menuHaveDepend($topMenu->id);

        }

        $context = Context::getContext();
        $admin_webpath = str_ireplace(_PS_CORE_DIR_, '', _PS_ADMIN_DIR_);
        $admin_webpath = preg_replace('/^' . preg_quote(DIRECTORY_SEPARATOR, '/') . '/', '', $admin_webpath);

        $tpl = $this->createTemplate('controllers/top_menu/tabs/display_menu_form.tpl');
        $iso = $this->context->language->iso_code;

        $tpl->assign(
            [
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
                'iso'                       => file_exists(_PS_CORE_DIR_ . '/js/tiny_mce/langs/' . $iso . '.js') ? $iso : 'en',
                'pathCSS'                   => _THEME_CSS_DIR_,
                'ad'                        => __PS_BASE_URI__ . basename(_PS_ADMIN_DIR_),
                'topMenu_img_dir'           => _PS_MENU_DIR_,
                'menu_img_dir'              => __PS_BASE_URI__ . $admin_webpath . '/themes/' . $this->bo_theme . '/img/topmenu/',
                'current_iso_lang'          => Language::getIsoById($context->cookie->id_lang),
                'current_id_lang'           => (int) $context->language->id,
                'default_language'          => (int) Configuration::get('PS_LANG_DEFAULT'),
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

        $return = [
            'html' => $tpl->fetch(),
        ];
        die(Tools::jsonEncode($return));
    }

    public function ajaxProcessOutputColumnWrapForm() {

        $idColumnWrap = Tools::getValue('id_column_wrap');
        if(Validate::isUnsignedInt($idColumnWrap)) {
        	$columnWrap = new TopMenuColumnWrap($idColumnWrap);
			$context = Context::getContext();
        	$menus = TopMenu::getMenus($context->cookie->id_lang, false);
        	$ids_lang = 'columnwrap_value_over¤columnwrap_value_under';
        	$vars = [
            	'ids_lang'             => $ids_lang,
            	'menus'                => $menus,
            	'ObjTopMenuColumnWrap' => $columnWrap,
        	];
        	$vars['bg_color_color1'] = false;
        	$vars['bg_color_color2'] = false;

        	if ($columnWrap && $columnWrap->bg_color) {
            	$val = explode('-', $columnWrap->bg_color);
            	$vars['bg_color_color1'] = $val[0];

            	if (isset($val[1])) {
                	$vars['bg_color_color2'] = $val[1];
            	}

        	}

        	$vars['hasAdditionnalText'] = false;
        	$languages = Language::getLanguages(false);

        	foreach ($languages as $language) {

            	if (isset($columnWrap->value_over[$language['id_lang']]) && !empty($columnWrap->value_over[$language['id_lang']]) || isset($columnWrap->value_under[$language['id_lang']]) && !empty($columnWrap->value_under[$language['id_lang']])) {
                $vars['hasAdditionnalText'] = true;
                break;
				}

        	}

        	$tpl = $this->createTemplate('controllers/top_menu/tabs/display_columnwrap_form.tpl');
        	$iso = $this->context->language->iso_code;
        	$tpl->assign(
            	[
                'linkTopMenu'       => $context->link->getAdminLink('AdminTopMenu'),
                'topMenu_img_dir'   => _PS_MENU_DIR_,
                'menu_img_dir'      => __PS_BASE_URI__ . $admin_webpath . '/themes/' . $this->bo_theme . '/img/topmenu/',
                'current_iso_lang'  => Language::getIsoById($context->cookie->id_lang),
                'current_id_lang'   => (int) $context->language->id,
                'default_language'  => (int) Configuration::get('PS_LANG_DEFAULT'),
                'languages'         => Language::getLanguages(false),
                'iso'               => file_exists(_PS_CORE_DIR_ . '/js/tiny_mce/langs/' . $iso . '.js') ? $iso : 'en',
                'pathCSS'           => _THEME_CSS_DIR_,
                'ad'                => __PS_BASE_URI__ . basename(_PS_ADMIN_DIR_),
            	]
        	);

        	foreach ($vars as $key => $value) {
            	$tpl->assign($key, $value);
        	}

        	$return = [
            	'html' => $tpl->fetch(),
        	];
			die(Tools::jsonEncode($return));
		}

    }

    public function ajaxProcessOutputColumnForm() {

        $ids_lang = 'columnname¤columnlink¤column_value_over¤column_value_under¤columnimage¤columnimagelegend¤iconPickingButton';
        $id_column = Tools::getValue('id_column');
        $context = Context::getContext();
        $ObjTopMenuColumn = new TopMenuColumn($id_column);
        $menus = TopMenu::getMenus($context->cookie->id_lang, false);

        $haveDepend = false;

        if ($id_column > 0) {
            $haveDepend = TopMenuColumn::columnHaveDepend($ObjTopMenuColumn->id);
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
                'ObjTopMenuColumn'    => $ObjTopMenuColumn,
                'linkTopMenu'         => $context->link->getAdminLink('AdminTopMenu'),
                'ids_lang'            => $ids_lang,
                'menus'               => $menus,
                'haveDepend'          => $haveDepend,
                'hasAdditionnalText'  => $hasAdditionnalText,
                'rebuildable_type'    => $rebuildable_type,
                'topMenu_img_dir'     => _PS_MENU_DIR_,
                'menu_img_dir'        => __PS_BASE_URI__ . $admin_webpath . '/themes/' . $this->bo_theme . '/img/topmenu/',
                'current_iso_lang'    => Language::getIsoById($context->cookie->id_lang),
                'current_id_lang'     => (int) $context->language->id,
                'default_language'    => (int) Configuration::get('PS_LANG_DEFAULT'),
                'languages'           => Language::getLanguages(false),
                'iso'                 => file_exists(_PS_CORE_DIR_ . '/js/tiny_mce/langs/' . $iso . '.js') ? $iso : 'en',
                'pathCSS'             => _THEME_CSS_DIR_,
                'ad'                  => __PS_BASE_URI__ . basename(_PS_ADMIN_DIR_),
                'columnsWrap'         => $columnsWrap,
                'columnWrap_selected' => $ObjTopMenuColumn->id_topmenu_wrap,
                'categoryList'        => EducationType::getEducationType((int) $this->context->cookie->id_lang),
                'cms'                 => CMS::listCms((int) $this->context->cookie->id_lang),
                'cmsCategories'       => $this->getNestedCmsCategories((int) $this->context->cookie->id_lang),
                'pagesList'           => $this->getPageList(),
                'link_targets'        => $this->link_targets,
                'hasHtmlOver'         => $hasHtmlOver,
            ]
        );

        $return = [
            'html' => $tpl->fetch(),
        ];
        die(Tools::jsonEncode($return));
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
                'topMenu_img_dir'   => _PS_MENU_DIR_,
                'menu_img_dir'      => __PS_BASE_URI__ . $admin_webpath . '/themes/' . $this->bo_theme . '/img/topmenu/',
                'current_iso_lang'  => Language::getIsoById($context->cookie->id_lang),
                'current_id_lang'   => (int) $context->language->id,
                'default_language'  => (int) Configuration::get('PS_LANG_DEFAULT'),
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
        $html .= '<option>-- ' . $this->l('Choose') . ' --</option>';

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
                'message' => $this->l('The tab was successfully deleted.'),
            ];
        } else {
            $result = [
                'success' => false,
                'message' => $this->l('An error occurred while deleting the column'),
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
                'message' => $this->l('The tab status was successfully updated.'),
            ];
        } else {
            $result = [
                'success' => false,
                'message' => $this->l('An error occur while updated the tab status.'),
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
                'message' => $this->l('The tab status was successfully updated.'),
            ];
        } else {
            $result = [
                'success' => false,
                'message' => $this->l('An error occur while updated the tab status.'),
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
                'message' => $this->l('The tab status was successfully updated.'),
            ];
        } else {
            $result = [
                'success' => false,
                'message' => $this->l('An error occur while updated the tab status.'),
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
                'message' => $this->l('The column was successfully deleted.'),
            ];
        } else {
            $result = [
                'success' => false,
                'message' => $this->l('An error occurred while deleting the column'),
            ];
        }

        die(Tools::jsonEncode($result));
    }

    public function ajaxProcessActiveColumnWrap() {

        $id_menu = Tools::getValue('id_menu');

        $ObjEphenyxTopMenuColumnWrap = new TopMenuColumnWrap($id_menu);

        if ($ObjEphenyxTopMenuColumnWrap->active == 1) {
            $ObjEphenyxTopMenuColumnWrap->active = 0;
            $value = 0;
        } else {
            $ObjEphenyxTopMenuColumnWrap->active = 1;
            $value = 1;
        }

        if ($ObjEphenyxTopMenuColumnWrap->save()) {
           
            $result = [
                'success' => true,
                'value'   => $value,
                'message' => $this->l('The column status was successfully updated.'),
            ];
        } else {
           
            $result = [
                'success' => false,
                'message' => $this->l('An error occur while updated the column status.'),
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
                'message' => $this->l('The column status was successfully updated.'),
            ];
        } else {
            $result = [
                'success' => false,
                'message' => $this->l('An error occur while updated the column status.'),
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
                'message' => $this->l('The column status was successfully updated.'),
            ];
        } else {
            $result = [
                'success' => false,
                'message' => $this->l('An error occur while updated the column status.'),
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
                'message' => $this->l('The column was successfully deleted.'),
            ];
        } else {
            $result = [
                'success' => false,
                'message' => $this->l('An error occurred while deleting the column'),
            ];
        }

        die(Tools::jsonEncode($result));
    }

    public function ajaxProcessActiveColumn() {

        $id_menu = Tools::getValue('id_menu');
        

        $ObjEphenyxTopMenuColumnWrap = new TopMenuColumn($id_menu);

        if ($ObjEphenyxTopMenuColumnWrap->active == 1) {
            $ObjEphenyxTopMenuColumnWrap->active = 0;
            $value = 0;
        } else {
            $ObjEphenyxTopMenuColumnWrap->active = 1;
            $value = 1;
        }

        if ($ObjEphenyxTopMenuColumnWrap->save()) {
            $result = [
                'success' => true,
                'value'   => $value,
                'message' => $this->l('The column status was successfully updated.'),
            ];
        } else {
            $result = [
                'success' => false,
                'message' => $this->l('An error occur while updated the column status.'),
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
                'message' => $this->l('The column status was successfully updated.'),
            ];
        } else {
            $result = [
                'success' => false,
                'message' => $this->l('An error occur while updated the column status.'),
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
                'message' => $this->l('The column status was successfully updated.'),
            ];
        } else {
            $result = [
                'success' => false,
                'message' => $this->l('An error occur while updated the column status.'),
            ];
        }

        die(Tools::jsonEncode($result));
    }

    public function ajaxProcessTopMenuForm() {

        
		$id_topmenu = Tools::getValue('id_topmenu', false);
        $topMenu = new TopMenu($id_topmenu);
       
        if (!Tools::getValue('type', 0)) {
            $this->errors[] = $this->l('The type of the tab is required.');
        } else

        if (Tools::getValue('type') == 1 && !Tools::getValue('id_cms')) {
            $this->errors[] = $this->l('You need to select the related CMS.');
        } else

        if (Tools::getValue('type') == 9 && !Tools::getValue('id_specific_page')) {
            $this->errors[] = $this->l('You need to select the related specific page.');
        }

        if (!count($this->errors)) {

            //$this->menucopyFromPost($topMenu);
			foreach ($_POST as $key => $value) {

    			if (property_exists($topMenu, $key) && $key != 'id_tomenu') {
        			$topMenu->{$key}  = $value;
        		}
    		}
    		$classVars = get_class_vars(get_class($topMenu));
    		$fields = [];
    		if (isset($classVars['definition']['fields'])) {
				$fields = $classVars['definition']['fields'];
    		}
    		foreach ($fields as $field => $params) {
				if (array_key_exists('lang', $params) && $params['lang']) {
					if (property_exists($topMenu, $field)) {
						foreach (Language::getIDs(false) as $idLang) {
							$topMenu->{$field}[(int) $idLang] = Tools::getValue($field . '_' . (int) $idLang);
                		}
           			}
       			}
   			}
			if (Tools::getValue('type') == 2 && Tools::getValue('clickable') == 1) {
				foreach (Language::getIDs(false) as $idLang) {
               		$topMenu->link[(int) $idLang] = 'javascript:void(0)';
                }
			}
            $topMenu->border_size_tab = $this->getBorderSizeFromArray(Tools::getValue('border_size_tab'));
            $topMenu->border_size_submenu = $this->getBorderSizeFromArray(Tools::getValue('border_size_submenu'));
            $fnd_color_menu_tab = Tools::getValue('fnd_color_menu_tab');
            $topMenu->fnd_color_menu_tab = $fnd_color_menu_tab[0] . (Tools::getValue('fnd_color_menu_tab_gradient') && isset($fnd_color_menu_tab[1]) && $fnd_color_menu_tab[1] ? $this->gradient_separator . $fnd_color_menu_tab[1] : '');
            $fnd_color_menu_tab_over = Tools::getValue('fnd_color_menu_tab_over');
            $topMenu->fnd_color_menu_tab_over = $fnd_color_menu_tab_over[0] . (Tools::getValue('fnd_color_menu_tab_over_gradient') && isset($fnd_color_menu_tab_over[1]) && $fnd_color_menu_tab_over[1] ? $this->gradient_separator . $fnd_color_menu_tab_over[1] : '');
            $fnd_color_submenu = Tools::getValue('fnd_color_submenu');
            $topMenu->fnd_color_submenu = $fnd_color_submenu[0] . (Tools::getValue('fnd_color_submenu_gradient') && isset($fnd_color_submenu[1]) && $fnd_color_submenu[1] ? $this->gradient_separator . $fnd_color_submenu[1] : '');
            $topMenu->chosen_groups = Tools::getIsset('chosen_groups') ? Tools::jsonEncode(Tools::getValue('chosen_groups')) : '';

            if (!Tools::getValue('tinymce_container_toggle_menu', 0)) {
                $topMenu->value_over = [];
                $topMenu->value_under = [];
            }

            $languages = Language::getLanguages(false);

            if (!$id_topmenu) {

                if (!$topMenu->add()) {
                    $this->errors[] = $this->l('An error occurred while adding the tab');
                } else {
                    $this->context->smarty->assign([
                        'current_id_menu' => $topMenu->id,
                    ]);
                }

            } else
            if (!$topMenu->update()) {
                $this->errors[] = $this->l('An error occurred while updating the tab');
            }

            if (!count($this->errors)) {
                $this->updateMenuType($topMenu);

                if (!count($this->errors)) {

                    foreach ($languages as $language) {
                        $fileKey = 'icon_' . $language['id_lang'];

                        if (isset($_FILES[$fileKey]['tmp_name']) and $_FILES[$fileKey]['tmp_name'] != null) {
                            $ext = $this->getFileExtension($_FILES[$fileKey]['name']);

                            if (!in_array($ext, $this->allowFileExtension) || !getimagesize($_FILES[$fileKey]['tmp_name']) || !move_uploaded_file($_FILES[$fileKey]['tmp_name'], _PS_ROOT_DIR_ . '/img//menu_icons/' . $EphenyxTopMenuClass->id . '-' . $language['iso_code'] . '.' . $ext)) {
                                $this->errors[] = $this->l('An error occurred during the image upload');
                            } else {
                                $EphenyxTopMenuClass->image_class[$language['id_lang']] = null;
                                $EphenyxTopMenuClass->image_type[$language['id_lang']] = $ext;
                                $EphenyxTopMenuClass->have_icon[$language['id_lang']] = 1;
                                $EphenyxTopMenuClass->update();
                            }

                        } else

                        if (Tools::getValue('unlink_icon_' . $language['id_lang'])) {
                            unlink(_PS_ROOT_DIR_ . '/img/menu_icons/' . $topMenu->id . '-' . $language['iso_code'] . '.' . ($topMenu->image_type[$language['id_lang']] ?: 'jpg'));
                            $topMenu->have_icon[$language['id_lang']] = '';
                            $topMenu->image_type[$language['id_lang']] = '';
                            $topMenu->image_legend[$language['id_lang']] = '';
                            $topMenu->update();
                        }

                        if (!isset($_FILES[$fileKey]['tmp_name']) || $_FILES[$fileKey]['tmp_name'] == null) {
                            $iconLibraryKey = 'iconLibrary_' . $language['id_lang'];
                            $iconLibraryValue = Tools::getValue($iconLibraryKey);

                            if (!in_array($iconLibraryValue, ['fa', 'mi'])) {
                                // $this->errors[] = $this->l('An error occurred while saving the selected icon');
                            }

                            $libIconKey = 'lib_icon_' . $language['id_lang'];
                            $libIconValue = Tools::getValue($libIconKey);

                            if (!empty($libIconValue)) {

                                if ($libIconValue === 'empty') {
                                    $topMenu->image_type[$language['id_lang']] = '';
                                    $topMenu->image_class[$language['id_lang']] = '';
                                    $topMenu->have_icon[$language['id_lang']] = '';
                                } else {
                                    $topMenu->image_type[$language['id_lang']] = 'i-' . $iconLibraryValue;
                                    $topMenu->image_class[$language['id_lang']] = $libIconValue;
                                    $topMenu->have_icon[$language['id_lang']] = 1;
                                }

                                $topMenu->update();
                            }

                        }

                    }

                    $this->generateCss();

                }

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
				 'menu_img_dir'              => __PS_BASE_URI__ . $this->admin_webpath . '/themes/' . $this->bo_theme . '/img/topmenu/',
			]);
			$li = '<li unique-id="'.$topMenu->id.'" id="tab_'.$topMenu->id.'"><span class="menu-dragHandler pmIconContainer"><i class="pmIcon icon-move"></i></span><a href="#topmenu-tab-'.$topMenu->id.'">'.$topMenu->outPutName.'</a></li>';
			$html = '<div id="topmenu-tab-'.$topMenu->id.'" class="tab-menu-content">'.$data->fetch().'</div>';
            $result = [
                'success' => true,
                'message' => $this->l('Tab has been successfully saved'),
				'li' => $li,
				'html' => $html
            ];
        }

        die(Tools::jsonEncode($result));
    }

    private function getConfigKeys() {

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

    protected function generateCss() {

        list($config, $configResponsive) = $this->getConfigKeys();
        $menus = TopMenu::getMenus($this->context->cookie->id_lang, true, true);
        $columnsWrap = TopMenuColumnWrap::getColumnsWrap();
        $css = [];

        if (is_array($menus) && count($menus)) {

            foreach ($menus as $menu) {

                if ((int) $menu['id_shop'] != false) {
                    $configGlobalCss = Configuration::getMultiple($config, null, null, (int) $menu['id_shop']);
                    $configResponsiveCss = Configuration::getMultiple($configResponsive, null, null, (int) $menu['id_shop']);
                } else {
                    $configGlobalCss = Configuration::getMultiple($config);
                    $configResponsiveCss = Configuration::getMultiple($configResponsive);
                }

                $hoverCSSselector = ':hover';

                if (!empty($configGlobalCss['EPHTM_SUBMENU_OPEN_METHOD']) && $configGlobalCss['EPHTM_SUBMENU_OPEN_METHOD'] == 2) {
                    $hoverCSSselector = '.EPHTM_clicked';
                }

                if ($menu['txt_color_menu_tab']) {
                    $css[] = '.phtm_menu_' . $menu['id_menu'] . ' .phtm_menu_span_' . $menu['id_menu'] . ', .phtm_menu_' . $menu['id_menu'] . ' a .phtm_menu_span_' . $menu['id_menu'] . ' {color:' . htmlentities($menu['txt_color_menu_tab'], ENT_COMPAT, 'UTF-8') . '!important;}';
                }

                if ($menu['txt_color_menu_tab_hover']) {
                    $css[] = '.phtm_menu_' . $menu['id_menu'] . ' a:hover .phtm_menu_span_' . $menu['id_menu'] . ', .phtm_menu_' . $menu['id_menu'] . ' a.phtm_menu_actif .phtm_menu_span_' . $menu['id_menu'] . ', .phtm_menu_' . $menu['id_menu'] . ':hover > a.a-niveau1 .phtm_menu_span_' . $menu['id_menu'] . ' {color:' . htmlentities($menu['txt_color_menu_tab_hover'], ENT_COMPAT, 'UTF-8') . '!important;}';
                    $css[] = '* html .phtm_menu_' . $menu['id_menu'] . ' a:hover .phtm_menu_span_' . $menu['id_menu'] . ', * html .phtm_menu_' . $menu['id_menu'] . ' a.phtm_menu_actif .phtm_menu_span_' . $menu['id_menu'] . ' {color:' . htmlentities($menu['txt_color_menu_tab_hover'], ENT_COMPAT, 'UTF-8') . '!important;}';

                    if ($hoverCSSselector != ':hover') {
                        $css[] = '.phtm_menu_' . $menu['id_menu'] . $hoverCSSselector . ' a .phtm_menu_span_' . $menu['id_menu'] . ', .phtm_menu_' . $menu['id_menu'] . ' a.phtm_menu_actif .phtm_menu_span_' . $menu['id_menu'] . ', .phtm_menu_' . $menu['id_menu'] . $hoverCSSselector . ' > a.a-niveau1 .phtm_menu_span_' . $menu['id_menu'] . ' {color:' . htmlentities($menu['txt_color_menu_tab_hover'], ENT_COMPAT, 'UTF-8') . '!important;}';
                        $css[] = '* html .phtm_menu_' . $menu['id_menu'] . $hoverCSSselector . ' a .phtm_menu_span_' . $menu['id_menu'] . ', * html .phtm_menu_' . $menu['id_menu'] . ' a.phtm_menu_actif .phtm_menu_span_' . $menu['id_menu'] . ' {color:' . htmlentities($menu['txt_color_menu_tab_hover'], ENT_COMPAT, 'UTF-8') . '!important;}';
                    }

                }

                if ($menu['fnd_color_menu_tab']) {
                    $menu['fnd_color_menu_tab'] = explode($this->gradient_separator, $menu['fnd_color_menu_tab']);

                    if (isset($menu['fnd_color_menu_tab'][1])) {
                        $color1 = htmlentities($menu['fnd_color_menu_tab'][0], ENT_COMPAT, 'UTF-8');
                        $color2 = htmlentities($menu['fnd_color_menu_tab'][1], ENT_COMPAT, 'UTF-8');
                        $css[] = '.phtm_menu_' . $menu['id_menu'] . ' a .phtm_menu_span_' . $menu['id_menu'] . ' {background-color: ' . $color1 . '; background: linear-gradient(' . $color1 . ', ' . $color2 . ')!important;}';

                        if ($configResponsiveCss['EPHTM_RESPONSIVE_MODE'] == 1 && (int) $configResponsiveCss['EPHTM_RESPONSIVE_THRESHOLD'] > 0) {

                            if (isset($configResponsiveCss['EPHTM_RESP_SUBMENU_ICON_CL']) && !empty($configResponsiveCss['EPHTM_RESP_SUBMENU_ICON_CL'])) {
                                $css[] = '@media (max-width: ' . (int) $configResponsiveCss['EPHTM_RESPONSIVE_THRESHOLD'] . 'px) { .ephtm_menu_toggle_open .phtm_menu_' . $menu['id_menu'] . ' a .phtm_menu_span_' . $menu['id_menu'] . ' {background-color: ' . $color1 . '; background: url(' . $configResponsiveCss['EPHTM_RESP_SUBMENU_ICON_CL'] . ') no-repeat right 15px center, linear-gradient(' . $color1 . ', ' . $color2 . ')!important;} }';
                            }

                            if (isset($configResponsiveCss['EPHTM_RESP_SUBMENU_ICON_OP']) && !empty($configResponsiveCss['EPHTM_RESP_SUBMENU_ICON_OP'])) {
                                $css[] = '@media (max-width: ' . (int) $configResponsiveCss['EPHTM_RESPONSIVE_THRESHOLD'] . 'px) { .ephtm_menu_toggle_open .phtm_menu_' . $menu['id_menu'] . '.ephtm_sub_open a .phtm_menu_span_' . $menu['id_menu'] . ' {background-color: ' . $color1 . '; background: url(' . $configResponsiveCss['EPHTM_RESP_SUBMENU_ICON_OP'] . ') no-repeat right 15px center, linear-gradient(' . $color1 . ', ' . $color2 . ')!important;} }';
                            }

                        }

                    } else {
                        $css[] = '.phtm_menu_' . $menu['id_menu'] . ' a .phtm_menu_span_' . $menu['id_menu'] . ' {background-color:' . htmlentities($menu['fnd_color_menu_tab'][0], ENT_COMPAT, 'UTF-8') . '!important;filter: none!important;}';
                    }

                }

                if ($menu['fnd_color_menu_tab_over']) {
                    $menu['fnd_color_menu_tab_over'] = explode($this->gradient_separator, $menu['fnd_color_menu_tab_over']);

                    if (isset($menu['fnd_color_menu_tab_over'][1])) {
                        $color1 = htmlentities($menu['fnd_color_menu_tab_over'][0], ENT_COMPAT, 'UTF-8');
                        $color2 = htmlentities($menu['fnd_color_menu_tab_over'][1], ENT_COMPAT, 'UTF-8');
                        $css[] = '.phtm_menu_' . $menu['id_menu'] . ' a:hover .phtm_menu_span_' . $menu['id_menu'] . ', .phtm_menu_' . $menu['id_menu'] . ' a.phtm_menu_actif .phtm_menu_span_' . $menu['id_menu'] . ', .phtm_menu_' . $menu['id_menu'] . ':hover > a.a-niveau1 .phtm_menu_span_' . $menu['id_menu'] . ' {background-color: ' . $color1 . '!important; background: linear-gradient(' . $color1 . ', ' . $color2 . ')!important;}';
                        $css[] = '* html .phtm_menu_' . $menu['id_menu'] . ' a:hover .phtm_menu_span_' . $menu['id_menu'] . ', * html .phtm_menu_' . $menu['id_menu'] . ' a.phtm_menu_actif .phtm_menu_span_' . $menu['id_menu'] . ' {background-color:transparent!important;background:transparent!important;filter:none!important;}';

                        if ($hoverCSSselector != ':hover') {
                            $css[] = '.phtm_menu_' . $menu['id_menu'] . $hoverCSSselector . ' a .phtm_menu_span_' . $menu['id_menu'] . ', .phtm_menu_' . $menu['id_menu'] . ' a.phtm_menu_actif .phtm_menu_span_' . $menu['id_menu'] . ', .phtm_menu_' . $menu['id_menu'] . $hoverCSSselector . ' > a.a-niveau1 .phtm_menu_span_' . $menu['id_menu'] . ' {background-color: ' . $color1 . '!important; background: linear-gradient(' . $color1 . ', ' . $color2 . ')!important;}';
                            $css[] = '* html .phtm_menu_' . $menu['id_menu'] . $hoverCSSselector . ' a .phtm_menu_span_' . $menu['id_menu'] . ', * html .phtm_menu_' . $menu['id_menu'] . ' a.phtm_menu_actif .phtm_menu_span_' . $menu['id_menu'] . ' {background-color:transparent!important;background:transparent!important;filter:none!important;}';
                        }

                        if ($configResponsiveCss['EPHTM_RESPONSIVE_MODE'] == 1 && (int) $configResponsiveCss['EPHTM_RESPONSIVE_THRESHOLD'] > 0) {

                            if (isset($configResponsiveCss['EPHTM_RESP_SUBMENU_ICON_CL']) && !empty($configResponsiveCss['EPHTM_RESP_SUBMENU_ICON_CL'])) {
                                $css[] = '@media (max-width: ' . (int) $configResponsiveCss['EPHTM_RESPONSIVE_THRESHOLD'] . 'px) { .ephtm_menu_toggle_open .phtm_menu_' . $menu['id_menu'] . ' a:hover .phtm_menu_span_' . $menu['id_menu'] . ', .ephtm_menu_toggle_open .phtm_menu_' . $menu['id_menu'] . ' a.phtm_menu_actif .phtm_menu_span_' . $menu['id_menu'] . ', .ephtm_menu_toggle_open .phtm_menu_' . $menu['id_menu'] . ':hover > a.a-niveau1 .phtm_menu_span_' . $menu['id_menu'] . ' {background-color: ' . $color1 . '; background: url(' . $configResponsiveCss['EPHTM_RESP_SUBMENU_ICON_CL'] . ') no-repeat right 15px center, linear-gradient(' . $color1 . ', ' . $color2 . ')!important;} }';
                            }

                            if (isset($configResponsiveCss['EPHTM_RESP_SUBMENU_ICON_OP']) && !empty($configResponsiveCss['EPHTM_RESP_SUBMENU_ICON_OP'])) {
                                $css[] = '@media (max-width: ' . (int) $configResponsiveCss['EPHTM_RESPONSIVE_THRESHOLD'] . 'px) { .ephtm_menu_toggle_open .phtm_menu_' . $menu['id_menu'] . '.ephtm_sub_open a:hover .phtm_menu_span_' . $menu['id_menu'] . ', .ephtm_menu_toggle_open .phtm_menu_' . $menu['id_menu'] . '.ephtm_sub_open a.phtm_menu_actif .phtm_menu_span_' . $menu['id_menu'] . ', .ephtm_menu_toggle_open .phtm_menu_' . $menu['id_menu'] . '.ephtm_sub_open:hover > a.a-niveau1 .phtm_menu_span_' . $menu['id_menu'] . ' {background-color: ' . $color1 . '; background: url(' . $configResponsiveCss['EPHTM_RESP_SUBMENU_ICON_OP'] . ') no-repeat right 15px center, linear-gradient(' . $color1 . ', ' . $color2 . ')!important;} }';
                            }

                        }

                    } else {
                        $css[] = '.phtm_menu_' . $menu['id_menu'] . ' a:hover .phtm_menu_span_' . $menu['id_menu'] . ', .phtm_menu_' . $menu['id_menu'] . ' a.phtm_menu_actif .phtm_menu_span_' . $menu['id_menu'] . ', .phtm_menu_' . $menu['id_menu'] . ':hover > a.a-niveau1 .phtm_menu_span_' . $menu['id_menu'] . ' {background-color:' . htmlentities($menu['fnd_color_menu_tab_over'][0], ENT_COMPAT, 'UTF-8') . '!important;filter: none!important;}';
                        $css[] = '* html .phtm_menu_' . $menu['id_menu'] . ' a:hover .phtm_menu_span_' . $menu['id_menu'] . ', .phtm_menu_' . $menu['id_menu'] . ' a.phtm_menu_actif .phtm_menu_span_' . $menu['id_menu'] . ' {background-color:' . htmlentities($menu['fnd_color_menu_tab_over'][0], ENT_COMPAT, 'UTF-8') . '!important;filter:none!important;}';
                        $css[] = '* html .phtm_menu_' . $menu['id_menu'] . ' a:hover, .phtm_menu_' . $menu['id_menu'] . ' a.phtm_menu_actif {filter:none!important;}';

                        if ($hoverCSSselector != ':hover') {
                            $css[] = '.phtm_menu_' . $menu['id_menu'] . $hoverCSSselector . ' a .phtm_menu_span_' . $menu['id_menu'] . ', .phtm_menu_' . $menu['id_menu'] . ' a.phtm_menu_actif .phtm_menu_span_' . $menu['id_menu'] . ', .phtm_menu_' . $menu['id_menu'] . $hoverCSSselector . ' > a.a-niveau1 .phtm_menu_span_' . $menu['id_menu'] . ' {background-color:' . htmlentities($menu['fnd_color_menu_tab_over'][0], ENT_COMPAT, 'UTF-8') . '!important;filter: none!important;}';
                            $css[] = '* html .phtm_menu_' . $menu['id_menu'] . $hoverCSSselector . ' a .phtm_menu_span_' . $menu['id_menu'] . ', .phtm_menu_' . $menu['id_menu'] . ' a.phtm_menu_actif .phtm_menu_span_' . $menu['id_menu'] . ' {background-color:' . htmlentities($menu['fnd_color_menu_tab_over'][0], ENT_COMPAT, 'UTF-8') . '!important;filter:none!important;}';
                            $css[] = '* html .phtm_menu_' . $menu['id_menu'] . $hoverCSSselector . ' a, .phtm_menu_' . $menu['id_menu'] . ' a.phtm_menu_actif {filter:none!important;}';
                        }

                    }

                }

                if ($menu['border_size_tab']) {
                    $css[] = 'li.phtm_menu_' . $menu['id_menu'] . ' a.a-niveau1 {border-width:' . htmlentities($menu['border_size_tab'], ENT_COMPAT, 'UTF-8') . '!important;}';
                }

                if ($menu['border_color_tab']) {
                    $css[] = 'li.phtm_menu_' . $menu['id_menu'] . ' a.a-niveau1 {border-color:' . htmlentities($menu['border_color_tab'], ENT_COMPAT, 'UTF-8') . '!important;}';
                }

                if ($menu['width_submenu']) {

                    if ($configResponsiveCss['EPHTM_RESPONSIVE_MODE'] == 1 && (int) $configResponsiveCss['EPHTM_RESPONSIVE_THRESHOLD'] > 0) {
                        $css[] = '@media (min-width: ' . (int) ($configResponsiveCss['EPHTM_RESPONSIVE_THRESHOLD'] + 1) . 'px) { .phtm_menu_' . $menu['id_menu'] . ' .ephtm_sub {width:' . htmlentities($menu['width_submenu'], ENT_COMPAT, 'UTF-8') . 'px!important;} }';
                    } else {
                        $css[] = '.phtm_menu_' . $menu['id_menu'] . ' .ephtm_sub {width:' . htmlentities($menu['width_submenu'], ENT_COMPAT, 'UTF-8') . 'px!important;}';
                    }

                }

                if ($menu['minheight_submenu']) {
                    $css[] = '.phtm_menu_' . $menu['id_menu'] . ' .ephtm_sub {min-height:' . htmlentities($menu['minheight_submenu'], ENT_COMPAT, 'UTF-8') . 'px!important;}';
                    $css[] = '* html .phtm_menu_' . $menu['id_menu'] . ' .ephtm_sub {height:' . htmlentities($menu['minheight_submenu'], ENT_COMPAT, 'UTF-8') . 'px!important;}';
                    $css[] = '#ephtm_menu .phtm_menu_' . $menu['id_menu'] . ' div.ephtm_column_wrap {min-height:' . htmlentities($menu['minheight_submenu'], ENT_COMPAT, 'UTF-8') . 'px!important;}';
                    $css[] = '* html #ephtm_menu .phtm_menu_' . $menu['id_menu'] . ' div.ephtm_column_wrap {height:' . htmlentities($menu['minheight_submenu'], ENT_COMPAT, 'UTF-8') . 'px!important;}';
                } else

                if ($menu['minheight_submenu'] === '0') {
                    $css[] = '.phtm_menu_' . $menu['id_menu'] . ' .ephtm_sub {height:auto!important;min-height:0!important;}';
                    $css[] = '#ephtm_menu .phtm_menu_' . $menu['id_menu'] . ' div.ephtm_column_wrap {height:auto!important;min-height:0!important;}';
                }

                if ($menu['position_submenu']) {

                    if ((int) $menu['position_submenu'] == 1 || (int) $menu['position_submenu'] == 3) {
                        $css[] = '#ephtm_menu ul#menu li.phtm_menu_' . $menu['id_menu'] . ':hover, #ephtm_menu ul#menu li.phtm_menu_' . $menu['id_menu'] . ' a.a-niveau1:hover {position:relative!important;}';
                    } else

                    if ((int) $menu['position_submenu'] == 2) {
                        $css[] = '#ephtm_menu ul#menu li.phtm_menu_' . $menu['id_menu'] . ':hover, #ephtm_menu ul#menu li.phtm_menu_' . $menu['id_menu'] . ' a.a-niveau1:hover {position:static!important;}';
                    }

                    if ((int) $menu['position_submenu'] == 3) {
                        $css[] = '#ephtm_menu ul#menu li.phtm_menu_' . $menu['id_menu'] . ':hover div.ephtm_sub {left:auto!important;right:0!important;}';
                        $css[] = '#ephtm_menu ul#menu li.phtm_menu_' . $menu['id_menu'] . ' a:hover div.ephtm_sub {left:auto!important;right:1px!important;}';
                    }

                    if ($hoverCSSselector != ':hover') {

                        if ((int) $menu['position_submenu'] == 1 || (int) $menu['position_submenu'] == 3) {
                            $css[] = '#ephtm_menu ul#menu li.phtm_menu_' . $menu['id_menu'] . $hoverCSSselector . ', #ephtm_menu ul#menu li.phtm_menu_' . $menu['id_menu'] . $hoverCSSselector . ' a.a-niveau1 {position:relative!important;}';
                        } else

                        if ((int) $menu['position_submenu'] == 2) {
                            $css[] = '#ephtm_menu ul#menu li.phtm_menu_' . $menu['id_menu'] . $hoverCSSselector . ', #ephtm_menu ul#menu li.phtm_menu_' . $menu['id_menu'] . $hoverCSSselector . ' a.a-niveau1 {position:static!important;}';
                        }

                        if ((int) $menu['position_submenu'] == 3) {
                            $css[] = '#ephtm_menu ul#menu li.phtm_menu_' . $menu['id_menu'] . $hoverCSSselector . ' div.ephtm_sub {left:auto!important;right:0!important;}';
                            $css[] = '#ephtm_menu ul#menu li.phtm_menu_' . $menu['id_menu'] . $hoverCSSselector . ' a div.ephtm_sub {left:auto!important;right:1px!important;}';
                        }

                    }

                }

                if ($menu['fnd_color_submenu']) {
                    $menu['fnd_color_submenu'] = explode($this->gradient_separator, $menu['fnd_color_submenu']);

                    if (isset($menu['fnd_color_submenu'][1])) {
                        $color1 = htmlentities($menu['fnd_color_submenu'][0], ENT_COMPAT, 'UTF-8');
                        $color2 = htmlentities($menu['fnd_color_submenu'][1], ENT_COMPAT, 'UTF-8');
                        $css[] = '.phtm_menu_' . $menu['id_menu'] . ' .ephtm_sub {background-color: ' . $color1 . '; background: linear-gradient(' . $color1 . ', ' . $color2 . ')!important;}';
                    } else {
                        $css[] = '.phtm_menu_' . $menu['id_menu'] . ' .ephtm_sub {background-color:' . htmlentities($menu['fnd_color_submenu'][0], ENT_COMPAT, 'UTF-8') . '!important;filter: none!important;}';
                    }

                }

                if ($menu['border_color_submenu']) {
                    $css[] = '.phtm_menu_' . $menu['id_menu'] . ' div.ephtm_sub {border-color:' . htmlentities($menu['border_color_submenu'], ENT_COMPAT, 'UTF-8') . '!important;}';
                }

                if ($menu['border_size_submenu']) {
                    $css[] = '.phtm_menu_' . $menu['id_menu'] . ' div.ephtm_sub {border-width:' . htmlentities($menu['border_size_submenu'], ENT_COMPAT, 'UTF-8') . '!important;}';
                }

                foreach ($columnsWrap as $columnWrap) {

                    if ($columnWrap['id_menu'] != $menu['id_menu']) {
                        continue;
                    }

                    if ($columnWrap['bg_color']) {
                        $columnWrap['bg_color'] = explode($this->gradient_separator, $columnWrap['bg_color']);

                        if (isset($columnWrap['bg_color'][1])) {
                            $color1 = htmlentities($columnWrap['bg_color'][0], ENT_COMPAT, 'UTF-8');
                            $color2 = htmlentities($columnWrap['bg_color'][1], ENT_COMPAT, 'UTF-8');
                            $css[] = '.phtm_column_wrap_td_' . $columnWrap['id_wrap'] . ' {background-color: ' . $color1 . '; background: linear-gradient(' . $color1 . ', ' . $color2 . ')!important;}';
                        } else {
                            $css[] = '.phtm_column_wrap_td_' . $columnWrap['id_wrap'] . ' {background-color:' . htmlentities($columnWrap['bg_color'][0], ENT_COMPAT, 'UTF-8') . '!important;filter: none!important;}';
                        }

                    }

                    if ($columnWrap['width']) {

                        if (empty($menu['position_submenu']) && (int) $configGlobalCss['EPHTM_SUBMENU_POSITION'] == 2 || $menu['position_submenu'] == 2) {

                            if ($configResponsiveCss['EPHTM_RESPONSIVE_MODE'] == 1 && (int) $configResponsiveCss['EPHTM_RESPONSIVE_THRESHOLD'] > 0) {
                                $css[] = '@media (min-width: ' . (int) ($configResponsiveCss['EPHTM_RESPONSIVE_THRESHOLD'] + 1) . 'px) { .phtm_column_wrap_td_' . $columnWrap['id_wrap'] . ' {width:' . htmlentities($columnWrap['width'], ENT_COMPAT, 'UTF-8') . 'px!important;} }';
                            } else {
                                $css[] = '.phtm_column_wrap_td_' . $columnWrap['id_wrap'] . ' {width:' . htmlentities($columnWrap['width'], ENT_COMPAT, 'UTF-8') . 'px!important;}';
                            }

                            $css['fix_table_layout_' . $menu['id_menu'] . '_2'] = '#ephtm_menu .phtm_menu_' . $menu['id_menu'] . ' table.columnWrapTable {table-layout:fixed}';
                        } else {

                            if ($configResponsiveCss['EPHTM_RESPONSIVE_MODE'] == 1 && (int) $configResponsiveCss['EPHTM_RESPONSIVE_THRESHOLD'] > 0) {
                                $css[] = '@media (min-width: ' . (int) ($configResponsiveCss['EPHTM_RESPONSIVE_THRESHOLD'] + 1) . 'px) { .phtm_column_wrap_td_' . $columnWrap['id_wrap'] . ' {width:' . htmlentities($columnWrap['width'], ENT_COMPAT, 'UTF-8') . 'px!important;} }';
                            } else {
                                $css[] = '.phtm_column_wrap_' . $columnWrap['id_wrap'] . ' {width:' . htmlentities($columnWrap['width'], ENT_COMPAT, 'UTF-8') . 'px!important;}';
                            }

                            $css['fix_table_layout_' . $menu['id_menu'] . '_1'] = '.li-niveau1.phtm_menu_' . $menu['id_menu'] . ' .ephtm_sub {width: auto}';
                            $css['fix_table_layout_' . $menu['id_menu'] . '_2'] = '#ephtm_menu .phtm_menu_' . $menu['id_menu'] . ' table.columnWrapTable {table-layout:auto}';
                        }

                    }

                    if ($columnWrap['txt_color_column']) {
                        $css[] = '.phtm_column_wrap_' . $columnWrap['id_wrap'] . ' span.column_wrap_title, .phtm_column_wrap_' . $columnWrap['id_wrap'] . ' span.column_wrap_title a {color:' . htmlentities($columnWrap['txt_color_column'], ENT_COMPAT, 'UTF-8') . '!important;}';
                    }

                    if ($columnWrap['txt_color_column_over']) {
                        $css[] = '.phtm_column_wrap_' . $columnWrap['id_wrap'] . ' span.column_wrap_title a:hover {color:' . htmlentities($columnWrap['txt_color_column_over'], ENT_COMPAT, 'UTF-8') . '!important;}';
                    }

                    if ($columnWrap['txt_color_element']) {
                        $css[] = '.phtm_column_wrap_' . $columnWrap['id_wrap'] . ', .phtm_column_wrap_' . $columnWrap['id_wrap'] . ' a {color:' . htmlentities($columnWrap['txt_color_element'], ENT_COMPAT, 'UTF-8') . '!important;}';
                    }

                    if ($columnWrap['txt_color_element_over']) {
                        $css[] = '.phtm_column_wrap_' . $columnWrap['id_wrap'] . ' a:hover {color:' . htmlentities($columnWrap['txt_color_element_over'], ENT_COMPAT, 'UTF-8') . '!important;}';
                    }

                    if ((int) $configGlobalCss['EPHTM_SUBMENU_POSITION'] != 2 && $menu['position_submenu'] == 2) {
                        $css[] = '.phtm_menu_' . $menu['id_menu'] . ' .li-niveau1 .ephtm_sub {width: 100%;}';
                        $css['fix_table_layout_' . $menu['id_menu'] . '_2'] = '#ephtm_menu .phtm_menu_' . $menu['id_menu'] . ' table.columnWrapTable {table-layout:fixed; width: 0}';
                        $css['fix_table_layout_' . $menu['id_menu'] . '_3'] = '#ephtm_menu .phtm_menu_' . $menu['id_menu'] . ':hover table.columnWrapTable {width: 100%}';

                        if ($hoverCSSselector != ':hover') {
                            $css['fix_table_layout_' . $menu['id_menu'] . '_3'] = '#ephtm_menu .phtm_menu_' . $menu['id_menu'] . $hoverCSSselector . ' table.columnWrapTable {width: 100%}';
                        }

                    }

                }

            }

        }

        $advanced_css_file = self::ADVANCED_CSS_FILE;
        $old_advanced_css_file_exists = file_exists($advanced_css_file);
        $ids_shop = array_values(Shop::getCompleteListOfShopsID());

        foreach ($ids_shop as $id_shop) {
            $advanced_css_file_shop = str_replace('.css', '-' . $id_shop . '.css', $advanced_css_file);

            if (!$old_advanced_css_file_exists && !file_exists($advanced_css_file_shop)) {
                file_put_contents($advanced_css_file_shop, Tools::file_get_contents(self::ADVANCED_CSS_FILE_RESTORE));
            } else

            if ($old_advanced_css_file_exists && count($ids_shop) == 1 && !file_exists($advanced_css_file_shop)) {
                file_put_contents($advanced_css_file_shop, Tools::file_get_contents(self::ADVANCED_CSS_FILE));
                @unlink(self::ADVANCED_CSS_FILE);
            } else

            if (!file_exists($advanced_css_file_shop)) {
                file_put_contents($advanced_css_file_shop, Tools::file_get_contents(self::ADVANCED_CSS_FILE_RESTORE));
            }

        }

        $ids_shop = array_values(Shop::getCompleteListOfShopsID());
        $specific_css_file = [];

        foreach ($ids_shop as $id_shop) {
            $specific_css_file[] = str_replace('.css', '-' . $id_shop . '.css', self::DYN_CSS_FILE);
        }

        if (count($css) && count($specific_css_file)) {

            foreach ($specific_css_file as $value) {
                file_put_contents($value, implode("\n", $css));
            }

        } else

        if (!count($css) && count($specific_css_file)) {

            foreach ($specific_css_file as $value) {
                file_put_contents($value, '');
            }

        }

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
            } else

            if (property_exists($object, $key)) {
                $object->{$key} = $value;
            }

        }
		
		$classVars = get_class_vars(get_class($object));
		$fields = [];
		if (isset($classVars['definition']['fields'])) {
			$fields = $classVars['definition']['fields'];
		}
		foreach ($data as $key => $value) {
			if (array_key_exists('lang', $value) && $value['lang']) {
				if (property_exists($object, $key)) {
					foreach (Language::getIDs(false) as $idLang) {
						if (!isset($data[$key][(int) $idLang]) || !is_array($data[$field])) {
       						$object->{$key}  = [];
       					}
       					$object->{$key}[(int) $idLang] = $data[$key][(int) $idLang];
						
       				}
				}
			}
			
		}
		if (Tools::getValue('type') == 2 && Tools::getValue('clickable') == 1) {
			foreach (Language::getIDs(false) as $idLang) {
               	$object->link[(int) $idLang] = 'javascript:void(0)';
                			}

						}

        

    }

    private function getBorderSizeFromArray($borderArray) {

        if (!is_array($borderArray)) {
            return false;
        }

        $borderStr = '';

        foreach ($borderArray as $border) {

            if ($border == 'auto') {
                $borderStr .= 'auto ';
            } else {

                if (is_numeric($border)) {
                    $borderStr .= (int) $border . 'px ';
                } else {
                    $borderStr .= 'unset ';
                }

            }

        }

        return rtrim($borderStr);
    }

    private function updateMenuType($EphenyxTopMenuClass) {

		
        switch ($EphenyxTopMenuClass->type) {

        case 13:

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

                        $EphenyxTopMenuColumnClass = $this->fetchOrCreateColumnObject($idColumn, $EphenyxTopMenuClass, 'id_cms_category', $firstChildCategory);

                        if (!$idColumn) {
                            $EphenyxTopMenuColumnWrapClass = $this->createColumnWrap($EphenyxTopMenuClass->id);
                            $EphenyxTopMenuColumnClass->id_topmenu_columns_wrap = $EphenyxTopMenuColumnWrapClass->id;
                        }

                        if ($EphenyxTopMenuColumnClass->save()) {
                            $elementPosition = 0;

                            foreach ($childCmsPages as $cmsPage) {
                                $idElement = false;

                                if (Tools::getValue('id_menu', false)) {
                                    $idElement = TopMenuElements::getIdElementCmsDepend($idColumn, (int) $cmsPage['id_cms']);

                                    if (!$idElement && !Tools::getValue('rebuild')) {
                                        continue;
                                    }

                                }

                                $EphenyxTopMenuElementsClass = $this->fetchOrCreateElementObject($idElement, $EphenyxTopMenuColumnClass, 'id_cms', $cmsPage, 1);

                                if (!$idElement) {
                                    $EphenyxTopMenuElementsClass->position = $elementPosition;
                                }

                                if (!$EphenyxTopMenuElementsClass->save()) {
                                    $this->errors[] = $this->l('An error occurred while saving children CMS page');
                                }

                                $elementPosition++;
                            }

                        } else {
                            $this->errors[] = $this->l('An error occurred while saving children CMS page');
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

                        $EphenyxTopMenuColumnClass = $this->fetchOrCreateColumnObject($columnWithNoDepth, $EphenyxTopMenuClass, 'id_cms_category', $firstChildCategory, $EphenyxTopMenuClass->type);

                        if (!$columnWithNoDepth) {
                            $EphenyxTopMenuColumnWrapClass = $this->createColumnWrap($EphenyxTopMenuClass->id);
                            $EphenyxTopMenuColumnClass->id_topmenu_columns_wrap = $EphenyxTopMenuColumnWrapClass->id;
                        }

                        if (!$EphenyxTopMenuColumnClass->save()) {
                            $this->errors[] = $this->l('An error occurred while saving children category');
                            continue;
                        }

                        if (!$columnWrapWithNoDepth) {
                            $columnWrapWithNoDepth = $EphenyxTopMenuColumnClass->id_topmenu_columns_wrap;
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

                    $EphenyxTopMenuColumnClass = $this->fetchOrCreateColumnObject($columnWithNoDepth, $EphenyxTopMenuClass, 'id_cms_category', $firstChildCategory, 2);

                    if (!$columnWithNoDepth) {
                        $EphenyxTopMenuColumnWrapClass = $this->createColumnWrap($EphenyxTopMenuClass->id);
                        $EphenyxTopMenuColumnClass->id_topmenu_columns_wrap = $EphenyxTopMenuColumnWrapClass->id;
                    }

                    if (!$EphenyxTopMenuColumnClass->save()) {
                        $this->errors[] = $this->l('An error occurred while saving children CMS page');
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

                        $EphenyxTopMenuElementsClass = $this->fetchOrCreateElementObject($idElement, $EphenyxTopMenuColumnClass, 'id_cms', $cmsPage, 1);

                        if (!$idElement) {
                            $EphenyxTopMenuElementsClass->position = $elementPosition;
                        }

                        if (!$EphenyxTopMenuElementsClass->save()) {
                            $this->errors[] = $this->l('An error occurred while saving children CMS page');
                        }

                        $elementPosition++;
                    }

                }

            }

            break;
        }

        if (count($this->errors)) {
            $result = [
                'success' => false,
                'message' => implode(PHP_EOL, $this->errors),
            ];
        } else {
			
			$topmenu = new TopMenu($EphenyxTopMenuClass->id);
			
            $data = $this->createTemplate('controllers/top_menu/newMenu.tpl');
			$data->assign([
				'topMenu'		=> $topmenu,
				 'menu_img_dir'              => __PS_BASE_URI__ . $this->admin_webpath . '/themes/' . $this->bo_theme . '/img/topmenu/',
			]);
			$li = '<li unique-id="'.$topmenu->id.'" id="tab_'.$topmenu->id.'"><span class="menu-dragHandler pmIconContainer"><i class="pmIcon icon-move"></i></span><a href="#topmenu-tab-'.$topmenu->id.'">'.$topmenu->outPutName.'</a></li>';
			
			$html = '<div id="topmenu-tab-'.$topmenu->id.'" class="tab-menu-content">'.$data->fetch().'</div>';
            $result = [
                'success' => true,
                'message' => $this->l('Tab has been successfully saved'),
				'li' => $li,
				'html' => $html
            ];
        }

        die(Tools::jsonEncode($result));

    }

    private function getSubCategoriesId($id_category, $active = true, $with_position = false) {

        if (!Validate::isBool($active)) {
            die(Tools::displayError());
        }

        if (!Validate::isBool($with_position)) {
            die(Tools::displayError());
        }

        $orderBy = 'category_shop.`position`';
        $with_position_field = 'category_shop.`position`';
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
            SELECT c.id_category' . ($with_position ? ', ' . $with_position_field : '') . '
            FROM `' . _DB_PREFIX_ . 'category` c
            ' . Shop::addSqlAssociation('category', 'c') . '
            WHERE `id_parent` = ' . (int) $id_category . '
            ' . ($active ? 'AND `active` = 1' : '') . '
            GROUP BY c.`id_category`
            ORDER BY ' . $orderBy . ' ASC');
    }

    

    private function getCmsSubCategoriesId($id_cms_category, $active = true, $with_position = false) {

        if (!Validate::isBool($active)) {
            die(Tools::displayError());
        }

        if (!Validate::isBool($with_position)) {
            die(Tools::displayError());
        }

        $orderBy = 'c.`position`';
        $with_position_field = 'c.`position`';
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
            SELECT c.id_cms_category' . ($with_position ? ', ' . $with_position_field : '') . '
            FROM `' . _DB_PREFIX_ . 'cms_category` c
            ' . Shop::addSqlAssociation('cms_category', 'c') . '
            WHERE `id_parent` = ' . (int) $id_cms_category . '
            ' . ($active ? 'AND `active` = 1' : '') . '
            GROUP BY c.`id_cms_category`
            ORDER BY ' . $orderBy . ' ASC');
    }

    private function getCmsByCategory($idCategory) {

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
            'SELECT c.*
            FROM `' . _DB_PREFIX_ . 'cms` c
            ' . Shop::addSqlAssociation('cms', 'c') . '
            WHERE c.`id_cms_category` = ' . (int) $idCategory . '
            AND c.`active` = 1;'
        );
    }

    private function fetchOrCreateColumnObject($idColumn, $advancedTopMenuClass, $fieldName, $entity, $columnType = null) {

        $EphenyxTopMenuColumnClass = new TopMenuColumn($idColumn);
        $EphenyxTopMenuColumnClass->active = ($idColumn ? $EphenyxTopMenuColumnClass->active : 1);
        $EphenyxTopMenuColumnClass->id_topmenu = $advancedTopMenuClass->id;
        $EphenyxTopMenuColumnClass->id_topmenu_depend = $advancedTopMenuClass->id;
        $EphenyxTopMenuColumnClass->type = (!empty($columnType) ? $columnType : $advancedTopMenuClass->type);
        $EphenyxTopMenuColumnClass->{$fieldName}

        = $entity[$fieldName];
        $EphenyxTopMenuColumnClass->position = isset($entity['position']) ? $entity['position'] : '0';
        return $EphenyxTopMenuColumnClass;
    }

    private function createColumnWrap($idMenu) {

        $EphenyxTopMenuColumnWrapClass = new TopMenuColumnWrap();
        $EphenyxTopMenuColumnWrapClass->active = 1;
        $EphenyxTopMenuColumnWrapClass->id_topmenu = $idMenu;
        $EphenyxTopMenuColumnWrapClass->id_topmenu_depend = $idMenu;
        $EphenyxTopMenuColumnWrapClass->save();
        $EphenyxTopMenuColumnWrapClass->internal_name = $this->l('column') . '-' . $EphenyxTopMenuColumnWrapClass->id_menu . '-' . $EphenyxTopMenuColumnWrapClass->id;

        if (!$EphenyxTopMenuColumnWrapClass->save()) {
            $this->errors[] = $this->l('An error occurred while saving column');
        }

        return $EphenyxTopMenuColumnWrapClass;
    }

    private function fetchOrCreateElementObject($idElement, $advancedTopMenuColumnClass, $fieldName, $entity, $columnType = null) {

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
            $this->errors[] = $this->l('An error occurred while adding the column - Parent tab is not set');
        } else {
            $columnWrap = new TopMenuColumnWrap($id_wrap);

            if (!count($this->errors)) {
                $this->menucopyFromPost($columnWrap);
                $bg_color = Tools::getValue('bg_color');
                $columnWrap->bg_color = $bg_color[0] . (Tools::getValue('bg_color_gradient') && isset($bg_color[1]) && $bg_color[1] ? $this->gradient_separator . $bg_color[1] : '');
                $columnWrap->chosen_groups = Tools::getIsset('chosen_groups') ? Tools::jsonEncode(Tools::getValue('chosen_groups')) : '';

                if (!Tools::getValue('tinymce_container_toggle_menu', 0)) {
                    $columnWrap->value_over = [];
                    $columnWrap->value_under = [];
                }

                unset($_POST['active']);

                if (!$id_wrap) {

                    if (!$columnWrap->add()) {
                        $this->errors[] = $this->l('An error occurred while adding the column');
                    }

                } else

                if (!$columnWrap->update()) {
                    $this->errors[] = $this->l('An error occurred while updating the column');
                }

                if (!count($this->errors)) {
                    $this->generateCss();

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
			$columnsWraps = TopMenuColumnWrap::getMenuColumnsWrap((int) $id_menu, $this->context->cookie->id_lang, false);
			foreach($columnsWraps as &$columnsWrap) {
				$columnsWrap['columns'] = TopMenuColumn::getMenuColums($columnsWrap['id_columns_wrap'], $this->context->cookie->id_lang, false);
			}
			$data = $this->createTemplate('controllers/top_menu/columnWrapSortContent.tpl');
			$data->assign([
				'columnsWraps'		=> $columnsWraps,
				 'menu_img_dir'              => __PS_BASE_URI__ . $this->admin_webpath . '/themes/' . $this->bo_theme . '/img/topmenu/',
			]);
			
            $result = [
                'success' => true,
                'message' => $this->l('Column has been successfully saved'),
				'html' => $data->fetch(),
				'idMenu' => $id_menu,
            ];
        }

        die(Tools::jsonEncode($result));

    }

    public function ajaxProcessTopColumnForm() {

        
        $id_column = Tools::getValue('id_topmenu_column', false);
        $topMenuColumn = new TopMenuColumn($id_column);

        if (!Tools::getValue('type', 0)) {
            $this->errors[] = $this->l('The type of the column is required.');
        } else
        if (Tools::getValue('type') == 1 && !Tools::getValue('id_cms')) {
            $this->errors[] = $this->l('You need to select the related CMS.');
        } else
        if (Tools::getValue('type') == 9 && !Tools::getValue('id_specific_page')) {
            $this->errors[] = $this->l('You need to select the related specific page.');
        }

        if (!count($this->errors)) {
            $this->menucopyFromPost($topMenuColumn);

            if (!(int) $topMenuColumn->id_topmenu_columns_wrap) {
                $this->errors[] = $this->l('You need to choose the parent column');
            }

            if (!Tools::getValue('tinymce_container_toggle_menu', 0)) {
                $topMenuColumn->value_over = [];
                $topMenuColumn->value_under = [];
            }

            $languages = Language::getLanguages(false);
            unset($_POST['active']);

            if (!$id_column) {

                if (!$topMenuColumn->add()) {
                    $this->errors[] = $this->l('An error occurred while adding the group of items');
                }

            } else

            if (!$topMenuColumn->update()) {
                $this->errors[] = $this->l('An error occurred while updating the group of items');
            }

            if (!count($this->errors)) {
                $this->updateColumnType($topMenuColumn);

                foreach ($languages as $language) {
                    $fileKey = 'icon_' . $language['id_lang'];

                    if (isset($_FILES[$fileKey]['tmp_name']) and $_FILES[$fileKey]['tmp_name'] != null) {
                        $ext = $this->getFileExtension($_FILES[$fileKey]['name']);

                        if (!in_array($ext, $this->allowFileExtension) || !getimagesize($_FILES[$fileKey]['tmp_name']) || !move_uploaded_file($_FILES[$fileKey]['tmp_name'], _PS_ROOT_DIR_ . '/img/column_icons/' . $topMenuColumn->id . '-' . $language['iso_code'] . '.' . $ext)) {
                            $this->errors[] = $this->l('An error occurred during the image upload');
                        } else {
                            $topMenuColumn->image_class[$language['id_lang']] = null;
                            $topMenuColumn->image_type[$language['id_lang']] = $ext;
                            $topMenuColumn->have_icon[$language['id_lang']] = 1;
                            $topMenuColumn->update();
                        }

                    } else

                    if (Tools::getValue('unlink_icon_' . $language['id_lang'])) {
                        unlink(_PS_ROOT_DIR_ . '/img/column_icons/' . $topMenuColumn->id . '-' . $language['iso_code'] . '.' . ($topMenuColumn->image_type[$language['id_lang']] ?: 'jpg'));
                        $topMenuColumn->have_icon[$language['id_lang']] = '';
                        $topMenuColumn->image_type[$language['id_lang']] = '';
                        $topMenuColumn->image_legend[$language['id_lang']] = '';
                        $topMenuColumn->update();
                    }

                    if (!isset($_FILES[$fileKey]['tmp_name']) || $_FILES[$fileKey]['tmp_name'] == null) {
                        $iconLibraryKey = 'iconLibrary_' . $language['id_lang'];
                        $iconLibraryValue = Tools::getValue($iconLibraryKey);

                        if (!in_array($iconLibraryValue, ['fa', 'mi'])) {
                            //$this->errors[] = $this->l('An error occurred while saving the selected icon');
                        }

                        $libIconKey = 'lib_icon_' . $language['id_lang'];
                        $libIconValue = Tools::getValue($libIconKey);

                        if (!empty($libIconValue)) {

                            if ($libIconValue === 'empty') {
                                $topMenuColumn->image_type[$language['id_lang']] = '';
                                $topMenuColumn->image_class[$language['id_lang']] = '';
                                $topMenuColumn->have_icon[$language['id_lang']] = '';
                            } else {
                                $topMenuColumn->image_type[$language['id_lang']] = 'i-' . $iconLibraryValue;
                                $topMenuColumn->image_class[$language['id_lang']] = $libIconValue;
                                $topMenuColumn->have_icon[$language['id_lang']] = 1;
                            }

                            $topMenuColumn->update();
                        }

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
				 'menu_img_dir'     => __PS_BASE_URI__ . $this->admin_webpath . '/themes/' . $this->bo_theme . '/img/topmenu/',
			]);
			
            $result = [
                'success' => true,
                'message' => $this->l('Column has been successfully saved'),
				'idMenu' => $topMenuColumn->id_topmenu,
				'html' => $data->fetch()
            ];
			
        }

        die(Tools::jsonEncode($result));

    }

    private function updateColumnType($EphenyxTopMenuColumnClass) {

        if (Tools::getValue('rebuild') && in_array($EphenyxTopMenuColumnClass->type, $this->rebuildable_type)) {
            $elements = TopMenuElements::getElementIds((int) $EphenyxTopMenuColumnClass->id);

            foreach ($elements as $idElement) {
                $element = new TopMenuElements((int) $idElement);
                $element->delete();
            }

        }

        switch ($EphenyxTopMenuColumnClass->type) {

        
        case 5:

            break;
        case 13:

            if (!Tools::getValue('include_subs_cms') || empty($EphenyxTopMenuColumnClass->id_cms_category)) {
                return;
            }

            $cmsPages = $this->getCmsByCategory((int) $EphenyxTopMenuColumnClass->id_cms_category);

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

                $EphenyxTopMenuElementsClass = $this->fetchOrCreateElementObject($idElement, $EphenyxTopMenuColumnClass, 'id_cms', $cmsPage, 1);

                if (!$idElement) {
                    $EphenyxTopMenuElementsClass->position = $elementPosition;
                }

                if (!$EphenyxTopMenuElementsClass->save()) {
                    $this->errors[] = $this->l('An error occurred while saving children CMS page');
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
            'message' => $this->l('Position Saved'),
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
            'message' => $this->l('Position Saved'),
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
            'message' => $this->l('Position Saved'),
        ];
        die(Tools::jsonEncode($result));
    }

    public function ajaxProcessupdateColumWrap() {

        $idColumn = Tools::getValue('idColumn');
        $idColumnWrap = Tools::getValue('idColumnWrap');

        $exist = TopMenuColumn::getnbColumninWrap($idColumnWrap);

        $EphenyxTopMenuColumnClass = new TopMenuColumn($idColumn);
        $EphenyxTopMenuColumnClass->id_topmenu_columns_wrap = $idColumnWrap;
        $EphenyxTopMenuColumnClass->update();

        $result = [
            'success' => true,
            'exist'   => $exist,
            'message' => $this->l('The group item has been successfully moved'),
        ];

        die(Tools::jsonEncode($result));
    }

    private function getPositionSizeFromArray($positionArray, $toCSSString = true) {

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

        if (Shop::isFeatureActive()) {

            foreach (Shop::getShops(true, null, true) as $id_shop) {
                $this->generateGlobalCss($id_shop);
            }

        } else {
            $this->generateGlobalCss();
        }

        $this->generateCss();

        $result = [
            'success' => true,
            'message' => $this->l('Configuration updated successfully'),
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

        if (Shop::isFeatureActive()) {

            foreach (Shop::getShops(true, null, true) as $id_shop) {
                $this->generateGlobalCss($id_shop);
            }

        } else {
            $this->generateGlobalCss();
        }

        $this->generateCss();

        $result = [
            'success' => true,
            'message' => $this->l('Configuration updated successfully'),
        ];

        die(Tools::jsonEncode($result));
    }

    protected function generateGlobalCss($id_shop = false) {

    
        list($config, $configResponsive) = $this->getConfigKeys();
     
        if ($id_shop != false) {
            $configGlobalCss = Configuration::getMultiple($config, null, null, $id_shop);
            $configResponsiveCss = Configuration::getMultiple($configResponsive, null, null, $id_shop);
        } else {
            $configGlobalCss = Configuration::getMultiple($config);
            $configResponsiveCss = Configuration::getMultiple($configResponsive);
        }

        if (empty($configResponsiveCss['ATMR_MENU_BGCOLOR_OP'])) {
            $configResponsiveCss['ATMR_MENU_BGCOLOR_OP'] = $configGlobalCss['EPHTM_MENU_BGCOLOR_OVER'];
        }

        if (empty($configResponsiveCss['ATMR_MENU_BGCOLOR_CL'])) {
            $configResponsiveCss['ATMR_MENU_BGCOLOR_CL'] = $configGlobalCss['EPHTM_MENU_BGCOLOR'];
        }

        $hoverCSSselector = ':hover';

        if (!empty($configGlobalCss['EPHTM_SUBMENU_OPEN_METHOD']) && $configGlobalCss['EPHTM_SUBMENU_OPEN_METHOD'] == 2) {
            $hoverCSSselector = '.atm_clicked';
        }

        $specificDesktopCss = [];
        $css = [];
        $configGlobalCss['EPHTM_MENU_GLOBAL_BGCOLOR'] = explode($this->gradient_separator, $configGlobalCss['EPHTM_MENU_GLOBAL_BGCOLOR']);

        if (isset($configGlobalCss['EPHTM_MENU_GLOBAL_BGCOLOR'][1])) {
            $color1 = htmlentities($configGlobalCss['EPHTM_MENU_GLOBAL_BGCOLOR'][0], ENT_COMPAT, 'UTF-8');
            $color2 = htmlentities($configGlobalCss['EPHTM_MENU_GLOBAL_BGCOLOR'][1], ENT_COMPAT, 'UTF-8');
            $css[] = '#ephtm_menu_inner {background-color: ' . $color1 . '; background: linear-gradient(' . $color1 . ', ' . $color2 . ');}';
        } else {
            $css[] = '#ephtm_menu_inner {background-color:' . htmlentities($configGlobalCss['EPHTM_MENU_GLOBAL_BGCOLOR'][0], ENT_COMPAT, 'UTF-8') . ';}';
        }

        $configGlobalCss['EPHTM_MENU_BOX_SHADOWOPACITY'] = round($configGlobalCss['EPHTM_MENU_BOX_SHADOWOPACITY'] / 100, 1);

        if ($configGlobalCss['EPHTM_MENU_CONT_POSITION'] == 'sticky') {
            $css[] = '#ephtm_menu {position:relative;' . $this->generateOptimizedCssRule('padding', $configGlobalCss['EPHTM_MENU_CONT_PADDING']) . $this->generateOptimizedCssRule('margin', $configGlobalCss['EPHTM_MENU_CONT_MARGIN']) . ';border-color:' . htmlentities($configGlobalCss['EPHTM_MENU_CONT_BORDERCOLOR'], ENT_COMPAT, 'UTF-8') . ';border-width:' . htmlentities($configGlobalCss['EPHTM_MENU_CONT_BORDERSIZE'], ENT_COMPAT, 'UTF-8') . ';box-shadow: ' . htmlentities($configGlobalCss['EPHTM_MENU_BOX_SHADOW'], ENT_COMPAT, 'UTF-8') . ' ' . htmlentities($this->hex2rgb($configGlobalCss['EPHTM_MENU_BOX_SHADOWCOLOR'], $configGlobalCss['EPHTM_MENU_BOX_SHADOWOPACITY']), ENT_COMPAT, 'UTF-8') . ';}';
        } else {

            $css[] = '#ephtm_menu {position:' . htmlentities($configGlobalCss['EPHTM_MENU_CONT_POSITION'], ENT_COMPAT, 'UTF-8') . ';' . $this->generateOptimizedCssRule('padding', $configGlobalCss['EPHTM_MENU_CONT_PADDING']) . $this->generateOptimizedCssRule('margin', $configGlobalCss['EPHTM_MENU_CONT_MARGIN']) . ';border-color:' . htmlentities($configGlobalCss['EPHTM_MENU_CONT_BORDERCOLOR'], ENT_COMPAT, 'UTF-8') . ';border-width:' . htmlentities($configGlobalCss['EPHTM_MENU_CONT_BORDERSIZE'], ENT_COMPAT, 'UTF-8') . '; box-shadow: ' . htmlentities($configGlobalCss['EPHTM_MENU_BOX_SHADOW'], ENT_COMPAT, 'UTF-8') . ' ' . htmlentities($this->hex2rgb($configGlobalCss['EPHTM_MENU_BOX_SHADOWCOLOR'], $configGlobalCss['EPHTM_MENU_BOX_SHADOWOPACITY']), ENT_COMPAT, 'UTF-8') . ';}';
        }

       
        $configGlobalCss['EPHTM_MENU_CONT_POSITION_TRBL'] = $this->getPositionSizeFromArray(explode(' ', $configGlobalCss['EPHTM_MENU_CONT_POSITION_TRBL']));
      

        if (!empty($configGlobalCss['EPHTM_MENU_CONT_POSITION_TRBL'])) {
            $css[] = '#ephtm_menu {' . htmlentities($configGlobalCss['EPHTM_MENU_CONT_POSITION_TRBL'], ENT_COMPAT, 'UTF-8') . '}';
        }

        $css[] = '#ephtm_menu_inner {' . $this->generateOptimizedCssRule('padding', $configGlobalCss['EPHTM_MENU_GLOBAL_PADDING']) . $this->generateOptimizedCssRule('margin', $configGlobalCss['EPHTM_MENU_GLOBAL_MARGIN']) . ';border-color:' . htmlentities($configGlobalCss['EPHTM_MENU_GLOBAL_BORDERCOLOR'], ENT_COMPAT, 'UTF-8') . ';border-width:' . htmlentities($configGlobalCss['EPHTM_MENU_GLOBAL_BORDERSIZE'], ENT_COMPAT, 'UTF-8') . ';}';
        $css[] = '#ephtm_menu .li-niveau1 a.a-niveau1 {min-height:' . (int) $configGlobalCss['EPHTM_MENU_GLOBAL_HEIGHT'] . 'px;line-height:' . (int) $configGlobalCss['EPHTM_MENU_GLOBAL_HEIGHT'] . 'px;}';
        $css[] = '#ephtm_menu .li-niveau1 a.a-niveau1.a-multiline {line-height:' . number_format((int) $configGlobalCss['EPHTM_MENU_GLOBAL_HEIGHT'] / 2, 2) . 'px;}';

        if ($configGlobalCss['EPHTM_MENU_GLOBAL_WIDTH']) {
            $css[] = '#ephtm_menu_inner {width:' . htmlentities($configGlobalCss['EPHTM_MENU_GLOBAL_WIDTH'], ENT_COMPAT, 'UTF-8') . 'px !important;}';
        }

        $css[] = '#ephtm_menu .li-niveau1 {min-height:' . (int) $configGlobalCss['EPHTM_MENU_GLOBAL_HEIGHT'] . 'px; line-height:' . ((int) $configGlobalCss['EPHTM_COLUMN_FONT_SIZE'] + 5) . 'px;}';
        $css[] = '#ephtm_menu .li-niveau1 a.a-niveau1 .phtm_menu_span {min-height:' . ((int) $configGlobalCss['EPHTM_MENU_GLOBAL_HEIGHT']) . 'px;line-height:' . (int) $configGlobalCss['EPHTM_MENU_GLOBAL_HEIGHT'] . 'px;}';
        $css[] = '#ephtm_menu .li-niveau1 a.a-niveau1.a-multiline .phtm_menu_span {line-height:' . number_format((int) $configGlobalCss['EPHTM_MENU_GLOBAL_HEIGHT'] / 2, 2) . 'px;}';
        $css[] = '#ephtm_menu .li-niveau1 .searchboxATM { display: table-cell; height:' . (int) $configGlobalCss['EPHTM_MENU_GLOBAL_HEIGHT'] . 'px; vertical-align: middle; }';
        $css[] = '#ephtm_menu .li-niveau1 .searchboxATM .ephtm_search_submit_button { height:' . (int) $configGlobalCss['EPHTM_MENU_GLOBAL_HEIGHT'] . 'px; }';
        $topDiff = 0;
        $atmMenuMarginTable = explode(' ', $configGlobalCss['EPHTM_MENU_MARGIN']);
        $atmMenuPaddingTable = explode(' ', $configGlobalCss['EPHTM_MENU_PADDING']);

        if (count($atmMenuMarginTable) == 4) {
            $topDiff += (int) $atmMenuMarginTable[0] + (int) $atmMenuMarginTable[2];
        }

        if (count($atmMenuPaddingTable) == 4) {
            $topDiff += (int) $atmMenuPaddingTable[0] + (int) $atmMenuPaddingTable[2];
        }

        $css[] = '#ephtm_menu ul#menu li div.ephtm_sub {top:' . ((int) $configGlobalCss['EPHTM_MENU_GLOBAL_HEIGHT'] + $topDiff) . 'px;}';
        $css[] = '.li-niveau1 a span {' . $this->generateOptimizedCssRule('padding', $configGlobalCss['EPHTM_MENU_PADDING']) . $this->generateOptimizedCssRule('margin', $configGlobalCss['EPHTM_MENU_MARGIN']) . '}';
        $css[] = '.li-niveau1 .phtm_menu_span, .li-niveau1 a .phtm_menu_span {color:' . htmlentities($configGlobalCss['EPHTM_MENU_COLOR'], ENT_COMPAT, 'UTF-8') . ';}';
        $css[] = '@media (min-width: ' . ((int) $configResponsiveCss['EPHTM_RESPONSIVE_THRESHOLD'] + 1) . 'px) {';
        $css[] = '#ephtm_menu ul#menu {display:flex;flex-wrap:wrap;}';
        $css[] = '}';

        if ((int) $configGlobalCss['EPHTM_MENU_CENTER_TABS'] == 1) {
            $css[] = '@media (min-width: ' . ((int) $configResponsiveCss['EPHTM_RESPONSIVE_THRESHOLD'] + 1) . 'px) {';
            $css[] = '#ephtm_menu ul#menu {justify-content:flex-start;}';
            $css[] = '}';
        } else

        if (in_array((int) $configGlobalCss['EPHTM_MENU_CENTER_TABS'], [2, 3])) {
            $css[] = '@media (min-width: ' . ((int) $configResponsiveCss['EPHTM_RESPONSIVE_THRESHOLD'] + 1) . 'px) {';
            $css[] = '#ephtm_menu ul#menu {justify-content:center;}';
            $css[] = '}';

            if ((int) $configGlobalCss['EPHTM_MENU_CENTER_TABS'] == 3) {
                $css[] = '@media (min-width: ' . ((int) $configResponsiveCss['EPHTM_RESPONSIVE_THRESHOLD'] + 1) . 'px) {';
                $css[] = '#ephtm_menu ul#menu li.li-niveau1 {flex:1;}';
                $css[] = '#ephtm_menu ul#menu li.li-niveau1 a.a-niveau1 {float:none;}';
                $css[] = '#ephtm_menu ul#menu li.li-niveau1 a.a-niveau1 .phtm_menu_span {display:flex;justify-content:center;flex:1;align-items:center;}';
                $css[] = '}';
            }

        } else

        if ((int) $configGlobalCss['EPHTM_MENU_CENTER_TABS'] == 4) {
            $css[] = '@media (min-width: ' . ((int) $configResponsiveCss['EPHTM_RESPONSIVE_THRESHOLD'] + 1) . 'px) {';
            $css[] = '#ephtm_menu ul#menu {justify-content:flex-end;}';
            $css[] = '}';
        }

        $configGlobalCss['EPHTM_MENU_BGCOLOR'] = explode($this->gradient_separator, $configGlobalCss['EPHTM_MENU_BGCOLOR']);

        if (isset($configGlobalCss['EPHTM_MENU_BGCOLOR'][1])) {
            $color1 = htmlentities($configGlobalCss['EPHTM_MENU_BGCOLOR'][0], ENT_COMPAT, 'UTF-8');
            $color2 = htmlentities($configGlobalCss['EPHTM_MENU_BGCOLOR'][1], ENT_COMPAT, 'UTF-8');
            $css[] = '.li-niveau1 a .phtm_menu_span, .li-niveau1 .phtm_menu_span {background-color: ' . $color1 . '; background: linear-gradient(' . $color1 . ', ' . $color2 . ');}';
        } else {
            $css[] = '.li-niveau1 a .phtm_menu_span, .li-niveau1 .phtm_menu_span {background-color:' . htmlentities($configGlobalCss['EPHTM_MENU_BGCOLOR'][0], ENT_COMPAT, 'UTF-8') . ';}';
        }

        $configGlobalCss['EPHTM_MENU_BGCOLOR_OVER'] = explode($this->gradient_separator, $configGlobalCss['EPHTM_MENU_BGCOLOR_OVER']);

        if (isset($configGlobalCss['EPHTM_MENU_BGCOLOR_OVER'][1])) {
            $color1 = htmlentities($configGlobalCss['EPHTM_MENU_BGCOLOR_OVER'][0], ENT_COMPAT, 'UTF-8');
            $color2 = htmlentities($configGlobalCss['EPHTM_MENU_BGCOLOR_OVER'][1], ENT_COMPAT, 'UTF-8');
            $specificDesktopCss[] = '.li-niveau1 a:hover .phtm_menu_span, .li-niveau1 .phtm_menu_span:hover, .li-niveau1:hover > a.a-niveau1 .phtm_menu_span {background-color: ' . $color1 . '; background: linear-gradient(' . $color1 . ', ' . $color2 . ');}';
            $css[] = '.li-niveau1 a.phtm_menu_actif .phtm_menu_span {background-color: ' . $color1 . '; background: linear-gradient(' . $color1 . ', ' . $color2 . ');}';

            if ($hoverCSSselector != ':hover') {
                $css[] = '.li-niveau1' . $hoverCSSselector . ' a .phtm_menu_span, .li-niveau1 a.phtm_menu_actif .phtm_menu_span, .li-niveau1' . $hoverCSSselector . ' .phtm_menu_span, .li-niveau1' . $hoverCSSselector . ' > a.a-niveau1 .phtm_menu_span {background-color: ' . $color1 . '; background: linear-gradient(' . $color1 . ', ' . $color2 . ');}';
            }

        } else {
            $specificDesktopCss[] = '.li-niveau1 a:hover .phtm_menu_span, .li-niveau1 .phtm_menu_span:hover, .li-niveau1:hover > a.a-niveau1 .phtm_menu_span {background-color:' . htmlentities($configGlobalCss['EPHTM_MENU_BGCOLOR_OVER'][0], ENT_COMPAT, 'UTF-8') . ';}';
            $css[] = '.li-niveau1 a.phtm_menu_actif .phtm_menu_span {background-color:' . htmlentities($configGlobalCss['EPHTM_MENU_BGCOLOR_OVER'][0], ENT_COMPAT, 'UTF-8') . ';}';

            if ($hoverCSSselector != ':hover') {
                $css[] = '.li-niveau1' . $hoverCSSselector . ' a .phtm_menu_span, .li-niveau1 a.phtm_menu_actif .phtm_menu_span, .li-niveau1' . $hoverCSSselector . ' .phtm_menu_span, .li-niveau1' . $hoverCSSselector . ' > a.a-niveau1 .phtm_menu_span {background-color:' . htmlentities($configGlobalCss['EPHTM_MENU_BGCOLOR_OVER'][0], ENT_COMPAT, 'UTF-8') . ';}';
            }

        }

        $css[] = '.li-niveau1 a.a-niveau1 {border-color:' . htmlentities($configGlobalCss['EPHTM_MENU_BORDERCOLOR'], ENT_COMPAT, 'UTF-8') . ';border-width:' . htmlentities($configGlobalCss['EPHTM_MENU_BORDERSIZE'], ENT_COMPAT, 'UTF-8') . ';}';
        $configGlobalCss['EPHTM_SUBMENU_BOX_SHADOWOPACITY'] = round($configGlobalCss['EPHTM_SUBMENU_BOX_SHADOWOPACITY'] / 100, 1);
        $css[] = '.li-niveau1 .ephtm_sub {border-color:' . htmlentities($configGlobalCss['EPHTM_SUBMENU_BORDERCOLOR'], ENT_COMPAT, 'UTF-8') . '; border-width:' . htmlentities($configGlobalCss['EPHTM_SUBMENU_BORDERSIZE'], ENT_COMPAT, 'UTF-8') . '; box-shadow: ' . htmlentities($configGlobalCss['EPHTM_SUBMENU_BOX_SHADOW'], ENT_COMPAT, 'UTF-8') . ' ' . htmlentities($this->hex2rgb($configGlobalCss['EPHTM_SUBMENU_BOX_SHADOWCOLOR'], $configGlobalCss['EPHTM_SUBMENU_BOX_SHADOWOPACITY']), ENT_COMPAT, 'UTF-8') . ';}';
        $configGlobalCss['EPHTM_SUBMENU_BGOPACITY'] = round($configGlobalCss['EPHTM_SUBMENU_BGOPACITY'] / 100, 1);
        $configGlobalCss['EPHTM_SUBMENU_BGCOLOR'] = explode($this->gradient_separator, $configGlobalCss['EPHTM_SUBMENU_BGCOLOR']);

        if (isset($configGlobalCss['EPHTM_SUBMENU_BGCOLOR'][1])) {
            $color1 = htmlentities($this->hex2rgb($configGlobalCss['EPHTM_SUBMENU_BGCOLOR'][0], $configGlobalCss['EPHTM_SUBMENU_BGOPACITY']), ENT_COMPAT, 'UTF-8');
            $color2 = htmlentities($this->hex2rgb($configGlobalCss['EPHTM_SUBMENU_BGCOLOR'][1], $configGlobalCss['EPHTM_SUBMENU_BGOPACITY']), ENT_COMPAT, 'UTF-8');
            $css[] = '.li-niveau1 .ephtm_sub {background-color: ' . $color1 . '; background: linear-gradient(' . $color1 . ', ' . $color2 . ');}';
        } else {
            $css[] = '.li-niveau1 .ephtm_sub {background-color:' . htmlentities($this->hex2rgb($configGlobalCss['EPHTM_SUBMENU_BGCOLOR'][0], $configGlobalCss['EPHTM_SUBMENU_BGOPACITY']), ENT_COMPAT, 'UTF-8') . ';}';
        }

        if ($configGlobalCss['EPHTM_SUBMENU_WIDTH']) {
            $css[] = '.li-niveau1 .ephtm_sub {width:' . htmlentities($configGlobalCss['EPHTM_SUBMENU_WIDTH'], ENT_COMPAT, 'UTF-8') . 'px;}';
        }

        if ($configGlobalCss['EPHTM_SUBMENU_HEIGHT']) {
            $css[] = '.li-niveau1 .ephtm_sub {min-height:' . htmlentities($configGlobalCss['EPHTM_SUBMENU_HEIGHT'], ENT_COMPAT, 'UTF-8') . 'px;}';
            $css[] = '* html .li-niveau1 .ephtm_sub {height:' . htmlentities($configGlobalCss['EPHTM_SUBMENU_HEIGHT'], ENT_COMPAT, 'UTF-8') . 'px;}';
            $css[] = '#ephtm_menu div.ephtm_column_wrap {min-height:' . htmlentities($configGlobalCss['EPHTM_SUBMENU_HEIGHT'], ENT_COMPAT, 'UTF-8') . 'px;}';
            $css[] = '* html #ephtm_menu div.ephtm_column_wrap {height:' . htmlentities($configGlobalCss['EPHTM_SUBMENU_HEIGHT'], ENT_COMPAT, 'UTF-8') . 'px;}';
        }

        $css[] = '#ephtm_menu ul#menu .li-niveau1 div.ephtm_sub {opacity: 0; visibility: hidden;}';
        $openingDelay = (!empty($configGlobalCss['EPHTM_SUBMENU_OPEN_DELAY']) ? (float) $configGlobalCss['EPHTM_SUBMENU_OPEN_DELAY'] : 0);
        $fadingSpeed = (!empty($configGlobalCss['EPHTM_SUBMENU_FADE_SPEED']) ? (float) $configGlobalCss['EPHTM_SUBMENU_FADE_SPEED'] : 0);
        $css[] = '#ephtm_menu ul#menu .li-niveau1' . $hoverCSSselector . ' div.ephtm_sub { opacity: 1;visibility: visible; transition:visibility 0s linear ' . $openingDelay . 's, opacity ' . $fadingSpeed . 's linear ' . $openingDelay . 's;}';
        $css[] = '.ephtm_column_wrap span.column_wrap_title, .ephtm_column_wrap span.column_wrap_title a, .ephtm_column_wrap span.column_wrap_title span[data-href] {color:' . htmlentities($configGlobalCss['EPHTM_COLUMN_TITLE_COLOR'], ENT_COMPAT, 'UTF-8') . ';}';
        $css[] = '.ephtm_column_wrap a, .ephtm_column_wrap span[data-href] {color:' . htmlentities($configGlobalCss['EPHTM_COLUMN_ITEM_COLOR'], ENT_COMPAT, 'UTF-8') . ';}';
        $css[] = '#ephtm_menu .ephtm_column_wrap {' . $this->generateOptimizedCssRule('padding', $configGlobalCss['EPHTM_COLUMNWRAP_PADDING']) . '}';
        $css[] = '#ephtm_menu .ephtm_column {' . $this->generateOptimizedCssRule('padding', $configGlobalCss['EPHTM_COLUMN_PADDING']) . $this->generateOptimizedCssRule('margin', $configGlobalCss['EPHTM_COLUMN_MARGIN']) . '}';
        $css[] = '#ephtm_menu .ephtm_column ul.ephtm_elements li a, #ephtm_menu .ephtm_column ul.ephtm_elements li span[data-href] {' . $this->generateOptimizedCssRule('padding', $configGlobalCss['EPHTM_COLUMN_ITEM_PADDING']) . $this->generateOptimizedCssRule('margin', $configGlobalCss['EPHTM_COLUMN_ITEM_MARGIN']) . '}';
        $css[] = '#ephtm_menu .ephtm_column_wrap span.column_wrap_title {' . $this->generateOptimizedCssRule('padding', $configGlobalCss['EPHTM_COLUMNTITLE_PADDING']) . $this->generateOptimizedCssRule('margin', $configGlobalCss['EPHTM_COLUMNTITLE_MARGIN']) . '}';
        $css[] = '#ephtm_menu .li-niveau1 a.a-niveau1 .phtm_menu_span {' . ($configGlobalCss['EPHTM_MENU_FONT_SIZE'] ? 'font-size:' . htmlentities($configGlobalCss['EPHTM_MENU_FONT_SIZE'], ENT_COMPAT, 'UTF-8') . 'px;' : '') . ' font-weight:' . ($configGlobalCss['EPHTM_MENU_FONT_BOLD'] ? 'bold' : 'normal') . '; text-decoration:' . ($configGlobalCss['EPHTM_MENU_FONT_UNDERLINE'] ? 'underline' : 'none') . '; text-transform:' . htmlentities($configGlobalCss['EPHTM_MENU_FONT_TRANSFORM'], ENT_COMPAT, 'UTF-8') . ';}';
        $specificDesktopCss[] = '#ephtm_menu .li-niveau1 a.a-niveau1:hover .phtm_menu_span, .li-niveau1:hover > a.a-niveau1 .phtm_menu_span {color:' . htmlentities($configGlobalCss['EPHTM_MENU_COLOR_OVER'], ENT_COMPAT, 'UTF-8') . '; text-decoration:' . ($configGlobalCss['EPHTM_MENU_FONT_UNDERLINEOV'] ? 'underline' : 'none') . ';}';
        $css[] = '#ephtm_menu .li-niveau1 a.phtm_menu_actif .phtm_menu_span {color:' . htmlentities($configGlobalCss['EPHTM_MENU_COLOR_OVER'], ENT_COMPAT, 'UTF-8') . '; text-decoration:' . ($configGlobalCss['EPHTM_MENU_FONT_UNDERLINEOV'] ? 'underline' : 'none') . ';}';

        if ($hoverCSSselector != ':hover') {
            $css[] = '#ephtm_menu .li-niveau1' . $hoverCSSselector . ' a.a-niveau1 .phtm_menu_span, #ephtm_menu .li-niveau1 a.phtm_menu_actif .phtm_menu_span, .li-niveau1' . $hoverCSSselector . ' > a.a-niveau1 .phtm_menu_span {color:' . htmlentities($configGlobalCss['EPHTM_MENU_COLOR_OVER'], ENT_COMPAT, 'UTF-8') . '; text-decoration:' . ($configGlobalCss['EPHTM_MENU_FONT_UNDERLINEOV'] ? 'underline' : 'none') . ';}';
        }

        if ($configGlobalCss['EPHTM_MENU_FONT_FAMILY']) {
            $css[] = '#ephtm_menu .li-niveau1 a.a-niveau1 .phtm_menu_span {font-family:' . htmlentities($configGlobalCss['EPHTM_MENU_FONT_FAMILY'], ENT_COMPAT, 'UTF-8') . ';}';
        }

        $css[] = '#ephtm_menu .ephtm_column span.column_wrap_title, #ephtm_menu .ephtm_column span.column_wrap_title a, #ephtm_menu .ephtm_column span.column_wrap_title span[data-href] {' . ($configGlobalCss['EPHTM_COLUMN_FONT_SIZE'] ? 'font-size:' . htmlentities($configGlobalCss['EPHTM_COLUMN_FONT_SIZE'], ENT_COMPAT, 'UTF-8') . 'px;' : '') . ' font-weight:' . ($configGlobalCss['EPHTM_COLUMN_FONT_BOLD'] ? 'bold' : 'normal') . '; text-decoration:' . ($configGlobalCss['EPHTM_COLUMN_FONT_UNDERLINE'] ? 'underline' : 'none') . '; text-transform:' . htmlentities($configGlobalCss['EPHTM_COLUMN_FONT_TRANSFORM'], ENT_COMPAT, 'UTF-8') . ';}';
        $css[] = '#ephtm_menu .ephtm_column span.column_wrap_title:hover, #ephtm_menu .ephtm_column span.column_wrap_title a:hover, #ephtm_menu .ephtm_column span.column_wrap_title span[data-href]:hover {color:' . htmlentities($configGlobalCss['EPHTM_COLUMN_TITLE_COLOR_OVER'], ENT_COMPAT, 'UTF-8') . '; text-decoration:' . ($configGlobalCss['EPHTM_COLUMN_FONT_UNDERLINEOV'] ? 'underline' : 'none') . ';}';

        if ($configGlobalCss['EPHTM_COLUMN_FONT_FAMILY']) {
            $css[] = '#ephtm_menu .ephtm_column span.column_wrap_title, #ephtm_menu .ephtm_column span.column_wrap_title a, #ephtm_menu .ephtm_column span.column_wrap_title span[data-href] {font-family:' . htmlentities($configGlobalCss['EPHTM_COLUMN_FONT_FAMILY'], ENT_COMPAT, 'UTF-8') . ';}';
        }

        $css[] = '#ephtm_menu .ephtm_column ul.ephtm_elements li, #ephtm_menu .ephtm_column ul.ephtm_elements li a, #ephtm_menu .ephtm_column ul.ephtm_elements li span[data-href] {' . ($configGlobalCss['EPHTM_COLUMN_ITEM_FONT_SIZE'] ? 'font-size:' . htmlentities($configGlobalCss['EPHTM_COLUMN_ITEM_FONT_SIZE'], ENT_COMPAT, 'UTF-8') . 'px;' : '') . ' font-weight:' . ($configGlobalCss['EPHTM_COLUMN_ITEM_FONT_BOLD'] ? 'bold' : 'normal') . '; text-decoration:' . ($configGlobalCss['EPHTM_COLUMN_ITEM_FONT_UNDERLINE'] ? 'underline' : 'none') . '; text-transform:' . htmlentities($configGlobalCss['EPHTM_COLUMN_ITEM_FONT_TRANSFORM'], ENT_COMPAT, 'UTF-8') . ';}';
        $css[] = '#ephtm_menu .ephtm_column ul.ephtm_elements li:hover, #ephtm_menu .ephtm_column ul.ephtm_elements li a:hover, #ephtm_menu .ephtm_column ul.ephtm_elements li span[data-href]:hover {color:' . htmlentities($configGlobalCss['EPHTM_COLUMN_ITEM_COLOR_OVER'], ENT_COMPAT, 'UTF-8') . '; text-decoration:' . ($configGlobalCss['EPHTM_COLUMN_ITEM_FONT_UNDERLINEOV'] ? 'underline' : 'none') . ';}';

        if ($configGlobalCss['EPHTM_COLUMN_ITEM_FONT_FAMILY']) {
            $css[] = '#ephtm_menu .ephtm_column ul.ephtm_elements li, #ephtm_menu .ephtm_column ul.ephtm_elements li a, #ephtm_menu .ephtm_column ul.ephtm_elements li span[data-href] {font-family:' . htmlentities($configGlobalCss['EPHTM_COLUMN_ITEM_FONT_FAMILY'], ENT_COMPAT, 'UTF-8') . ';}';
        }

        if ((int) $configGlobalCss['EPHTM_SUBMENU_POSITION'] == 1) {
            $css[] = '#ephtm_menu ul#menu li.li-niveau1' . $hoverCSSselector . ', #ephtm_menu ul#menu li.li-niveau1 a.a-niveau1' . $hoverCSSselector . ' {position:relative;}';
        } else

        if ((int) $configGlobalCss['EPHTM_SUBMENU_POSITION'] == 2) {
            $css[] = '.li-niveau1 .ephtm_sub {width: 100%}';
            $css[] = '#ephtm_menu table.columnWrapTable {table-layout:fixed;}';
        }

        if ($configGlobalCss['EPHTM_MENU_GLOBAL_ZINDEX']) {
            $css[] = '#ephtm_menu {z-index:' . (int) $configGlobalCss['EPHTM_MENU_GLOBAL_ZINDEX'] . ';}';
        }

        if ($configGlobalCss['EPHTM_MENU_CONT_POSITION'] == 'sticky') {
            $css[] = '#ephtm_menu-sticky-wrapper {z-index:' . (int) $configGlobalCss['EPHTM_MENU_GLOBAL_ZINDEX'] . ';}';
        }

        if ($configGlobalCss['EPHTM_SUBMENU_ZINDEX']) {
            $css[] = '.li-niveau1 .ephtm_sub {z-index:' . (int) $configGlobalCss['EPHTM_SUBMENU_ZINDEX'] . ';}';
        }

        $css[] = '#ephtm_menu .phtm_hide_desktop {display:none!important;}';

        if ($configResponsiveCss['EPHTM_RESPONSIVE_MODE'] == 1 && (int) $configResponsiveCss['EPHTM_RESPONSIVE_THRESHOLD'] > 0) {
            $css[] = '@media (min-width: ' . (int) $configResponsiveCss['EPHTM_RESPONSIVE_THRESHOLD'] . 'px) {';
            $css[] = implode("\n", $specificDesktopCss);
            $css[] = '}';
        } else {
            $css[] = implode("\n", $specificDesktopCss);
        }

       
        if ($configResponsiveCss['EPHTM_RESPONSIVE_MODE'] == 1 && (int) $configResponsiveCss['EPHTM_RESPONSIVE_THRESHOLD'] > 0) {
            $css[] = 'div#ephtm_menu_inner {width: inherit;}';
            $css[] = '#ephtm_menu ul .phtm_menu_toggle {display: none;}';
            $css[] = '@media (max-width: ' . (int) $configResponsiveCss['EPHTM_RESPONSIVE_THRESHOLD'] . 'px) {';
            $css[] = '#ephtm_menu {position:relative; top:initial; left:initial; right:initial; bottom:initial;}';
            $css[] = '#ephtm_menu .phtm_hide_mobile {display:none!important;}';
            $css[] = '#ephtm_menu.ephtm_menu_toggle_open ul#menu li.li-niveau1.phtm_search.phtm_hide_mobile {display:none!important;}';
            $css[] = '#ephtm_menu a.a-niveau1, #ephtm_menu .phtm_menu_span { height: auto !important; }';
            $css[] = '#ephtm_menu ul li.li-niveau1 {display: none;}';

            if (empty($configResponsiveCss['EPHTM_RESP_TOGGLE_ENABLED'])) {
                $css[] = '#ephtm_menu ul li.phtm_menu_toggle {width: 1px; height: 1px; visibility: hidden; min-height: 1px !important; border: none; padding: 0; margin: 0; line-height: 1px;}';
            } else {
                $css[] = '#ephtm_menu ul li.phtm_menu_toggle {display: block; width: 100%;}';
            }

            $css[] = '#ephtm_menu ul li.phtm_menu_toggle a.ephtm_toggle_menu_button {width: 100%; cursor: pointer;}';
            $css[] = '#ephtm_menu ul li.phtm_menu_toggle a.ephtm_toggle_menu_button span.ephtm_toggle_menu_button_text {background-position: right 15px center; background-repeat: no-repeat;}';
            $css[] = '#ephtm_menu .ephtm_menu_icon { height: auto; max-width: 100%; }';
            $css[] = '#ephtm_menu ul .li-niveau1 .ephtm_sub {width: auto; height: auto; min-height: inherit;}';
            $css[] = '#ephtm_menu ul div.ephtm_column_wrap {min-height: inherit; width: 100% !important;}';

            if (isset($configResponsiveCss['EPHTM_RESP_TOGGLE_ICON']) && !empty($configResponsiveCss['EPHTM_RESP_TOGGLE_ICON'])) {
                $css[] = '#ephtm_menu ul li.phtm_menu_toggle a.ephtm_toggle_menu_button span.ephtm_toggle_menu_button_text {background-image: url(' . $configResponsiveCss['EPHTM_RESP_TOGGLE_ICON'] . '); background-position: right 15px center; background-repeat: no-repeat;}';
            }

            $css[] = '#ephtm_menu .li-niveau1 a.a-niveau1 .phtm_menu_span {' . ($configResponsiveCss['EPHTM_RESP_MENU_FONT_SIZE'] ? 'font-size:' . htmlentities($configResponsiveCss['EPHTM_RESP_MENU_FONT_SIZE'], ENT_COMPAT, 'UTF-8') . 'px;' : '') . ' font-weight:' . ($configResponsiveCss['ATMR_MENU_FONT_BOLD'] ? 'bold' : 'normal') . '; text-transform:' . htmlentities($configResponsiveCss['ATMR_MENU_FONT_TRANSFORM'], ENT_COMPAT, 'UTF-8') . ';' . (!empty($configResponsiveCss['ATMR_MENU_FONT_FAMILY']) ? 'font-family:' . htmlentities($configResponsiveCss['ATMR_MENU_FONT_FAMILY'], ENT_COMPAT, 'UTF-8') . ';' : '') . '}';
            $css[] = '#ephtm_menu .ephtm_column span.column_wrap_title, #ephtm_menu .ephtm_column span.column_wrap_title a, #ephtm_menu .ephtm_column span.column_wrap_title span[data-href] {' . ($configResponsiveCss['EPHTM_RESP_COLUMN_FONT_SIZE'] ? 'font-size:' . htmlentities($configResponsiveCss['EPHTM_RESP_COLUMN_FONT_SIZE'], ENT_COMPAT, 'UTF-8') . 'px;' : '') . ' font-weight:' . ($configResponsiveCss['ATMR_COLUMN_FONT_BOLD'] ? 'bold' : 'normal') . '; text-transform:' . htmlentities($configResponsiveCss['ATMR_COLUMN_FONT_TRANSFORM'], ENT_COMPAT, 'UTF-8') . ';' . (!empty($configResponsiveCss['ATMR_COLUMN_FONT_FAMILY']) ? 'font-family:' . htmlentities($configResponsiveCss['ATMR_COLUMN_FONT_FAMILY'], ENT_COMPAT, 'UTF-8') . ';' : '') . '}';
            $css[] = '#ephtm_menu .ephtm_column ul.ephtm_elements li, #ephtm_menu .ephtm_column ul.ephtm_elements li a, #ephtm_menu .ephtm_column ul.ephtm_elements li span[data-href] {' . ($configResponsiveCss['EPHTM_RESP_COLUMN_ITEM_FONT_SIZE'] ? 'font-size:' . htmlentities($configResponsiveCss['EPHTM_RESP_COLUMN_ITEM_FONT_SIZE'], ENT_COMPAT, 'UTF-8') . 'px;' : '') . ' font-weight:' . ($configResponsiveCss['ATMR_COLUMN_ITEM_FONT_BOLD'] ? 'bold' : 'normal') . '; text-transform:' . htmlentities($configResponsiveCss['ATMR_COLUMN_ITEM_FONT_TRANSFORM'], ENT_COMPAT, 'UTF-8') . ';' . (!empty($configResponsiveCss['ATMR_COLUMN_ITEM_FONT_FAMILY']) ? 'font-family:' . htmlentities($configResponsiveCss['ATMR_COLUMN_ITEM_FONT_FAMILY'], ENT_COMPAT, 'UTF-8') . ';' : '') . '}';
            $css[] = '#ephtm_menu .li-niveau1.ephtm_sub_open a.a-niveau1 .phtm_menu_span, #ephtm_menu .li-niveau1 a.a-niveau1:focus .phtm_menu_span, .li-niveau1:focus > a.a-niveau1 .phtm_menu_span {color:' . htmlentities($configGlobalCss['EPHTM_MENU_COLOR_OVER'], ENT_COMPAT, 'UTF-8') . '; text-decoration:' . ($configGlobalCss['EPHTM_MENU_FONT_UNDERLINEOV'] ? 'underline' : 'none') . ';}';

            if (isset($configResponsiveCss['EPHTM_RESP_TOGGLE_COLOR_OP']) && !empty($configResponsiveCss['EPHTM_RESP_TOGGLE_COLOR_OP'])) {
                $css[] = '#ephtm_menu.ephtm_menu_toggle_open ul li.phtm_menu_toggle a.ephtm_toggle_menu_button span.ephtm_toggle_menu_button_text {color:' . htmlentities($configResponsiveCss['EPHTM_RESP_TOGGLE_COLOR_OP'], ENT_COMPAT, 'UTF-8') . ';}';
            }

            if (isset($configResponsiveCss['EPHTM_RESP_TOGGLE_COLOR_CL']) && !empty($configResponsiveCss['EPHTM_RESP_TOGGLE_COLOR_CL'])) {
                $css[] = '#ephtm_menu ul li.phtm_menu_toggle a.ephtm_toggle_menu_button span.ephtm_toggle_menu_button_text {color:' . htmlentities($configResponsiveCss['EPHTM_RESP_TOGGLE_COLOR_CL'], ENT_COMPAT, 'UTF-8') . ';}';
            }

            $css[] = '#ephtm_menu ul li.phtm_menu_toggle a.ephtm_toggle_menu_button span.ephtm_toggle_menu_button_text {' . ($configResponsiveCss['EPHTM_RESP_MENU_FONT_SIZE'] ? 'font-size:' . htmlentities($configResponsiveCss['EPHTM_RESP_TOGGLE_FONT_SIZE'], ENT_COMPAT, 'UTF-8') . 'px;' : '') . 'min-height:' . (int) $configResponsiveCss['EPHTM_RESP_TOGGLE_HEIGHT'] . 'px;line-height:' . (int) $configResponsiveCss['EPHTM_RESP_TOGGLE_HEIGHT'] . 'px;}';
            $configResponsiveCss['EPHTM_RESP_TOGGLE_BG_COLOR_OP'] = explode($this->gradient_separator, $configResponsiveCss['EPHTM_RESP_TOGGLE_BG_COLOR_OP']);

            if (isset($configResponsiveCss['EPHTM_RESP_TOGGLE_BG_COLOR_OP'][1])) {
                $color1 = htmlentities($configResponsiveCss['EPHTM_RESP_TOGGLE_BG_COLOR_OP'][0], ENT_COMPAT, 'UTF-8');
                $color2 = htmlentities($configResponsiveCss['EPHTM_RESP_TOGGLE_BG_COLOR_OP'][1], ENT_COMPAT, 'UTF-8');

                if (isset($configResponsiveCss['EPHTM_RESP_TOGGLE_ICON']) && !empty($configResponsiveCss['EPHTM_RESP_TOGGLE_ICON'])) {
                    $css[] = '#ephtm_menu.ephtm_menu_toggle_open li.phtm_menu_toggle a.ephtm_toggle_menu_button span.ephtm_toggle_menu_button_text {background-color: ' . $color1 . '; background: url(' . $configResponsiveCss['EPHTM_RESP_TOGGLE_ICON'] . ') no-repeat right 15px center, linear-gradient(' . $color1 . ', ' . $color2 . ');}';
                } else {
                    $css[] = '#ephtm_menu.ephtm_menu_toggle_open li.phtm_menu_toggle a.ephtm_toggle_menu_button span.ephtm_toggle_menu_button_text {background-color: ' . $color1 . '; background: linear-gradient(' . $color1 . ', ' . $color2 . ');}';
                }

            } else {
                $css[] = '#ephtm_menu.ephtm_menu_toggle_open li.phtm_menu_toggle a.ephtm_toggle_menu_button span.ephtm_toggle_menu_button_text {background-color:' . htmlentities($configResponsiveCss['EPHTM_RESP_TOGGLE_BG_COLOR_OP'][0], ENT_COMPAT, 'UTF-8') . ';}';
            }

            $configResponsiveCss['EPHTM_RESP_TOGGLE_BG_COLOR_CL'] = explode($this->gradient_separator, $configResponsiveCss['EPHTM_RESP_TOGGLE_BG_COLOR_CL']);

            if (isset($configResponsiveCss['EPHTM_RESP_TOGGLE_BG_COLOR_CL'][1])) {
                $color1 = htmlentities($configResponsiveCss['EPHTM_RESP_TOGGLE_BG_COLOR_CL'][0], ENT_COMPAT, 'UTF-8');
                $color2 = htmlentities($configResponsiveCss['EPHTM_RESP_TOGGLE_BG_COLOR_CL'][1], ENT_COMPAT, 'UTF-8');

                if (isset($configResponsiveCss['EPHTM_RESP_TOGGLE_ICON']) && !empty($configResponsiveCss['EPHTM_RESP_TOGGLE_ICON'])) {
                    $css[] = '#ephtm_menu ul li.phtm_menu_toggle a.ephtm_toggle_menu_button span.ephtm_toggle_menu_button_text {background-color: ' . $color1 . '; background: url(' . $configResponsiveCss['EPHTM_RESP_TOGGLE_ICON'] . ') no-repeat right 15px center, linear-gradient(' . $color1 . ', ' . $color2 . ');}';
                } else {
                    $css[] = '#ephtm_menu ul li.phtm_menu_toggle a.ephtm_toggle_menu_button span.ephtm_toggle_menu_button_text {background-color: ' . $color1 . '; background: linear-gradient(' . $color1 . ', ' . $color2 . ');}';
                }

            } else {
                $css[] = '#ephtm_menu ul li.phtm_menu_toggle a.ephtm_toggle_menu_button span.ephtm_toggle_menu_button_text {background-color:' . htmlentities($configResponsiveCss['EPHTM_RESP_TOGGLE_BG_COLOR_CL'][0], ENT_COMPAT, 'UTF-8') . ';}';
            }

            if (isset($configResponsiveCss['EPHTM_RESP_SUBMENU_ICON_CL']) && !empty($configResponsiveCss['EPHTM_RESP_SUBMENU_ICON_CL'])) {
                $css[] = '#ephtm_menu.ephtm_menu_toggle_open.atmRtl ul#menu li.li-niveau1.sub a.a-niveau1 span {background-position: left 15px center;}';
                $css[] = '#ephtm_menu.ephtm_menu_toggle_open ul#menu li.li-niveau1.sub a.a-niveau1 span {background-image: url(' . $configResponsiveCss['EPHTM_RESP_SUBMENU_ICON_CL'] . '); background-repeat: no-repeat; background-position: right 15px center;}';
            }

            if (isset($configResponsiveCss['EPHTM_RESP_SUBMENU_ICON_OP']) && !empty($configResponsiveCss['EPHTM_RESP_SUBMENU_ICON_OP'])) {
                $css[] = '#ephtm_menu.ephtm_menu_toggle_open ul#menu li.li-niveau1.sub.ephtm_sub_open a.a-niveau1 span {background-image: url(' . $configResponsiveCss['EPHTM_RESP_SUBMENU_ICON_OP'] . '); background-repeat: no-repeat; background-position: right 15px center;}';
            }

            $css[] = '.li-niveau1 a span {' . $this->generateOptimizedCssRule('padding', $configResponsiveCss['EPHTM_RESP_MENU_PADDING']) . $this->generateOptimizedCssRule('margin', $configResponsiveCss['ATMR_MENU_MARGIN']) . '}';
            $css[] = '.li-niveau1 a.a-niveau1 {border-color:' . htmlentities($configResponsiveCss['ATMR_MENU_BORDERCOLOR'], ENT_COMPAT, 'UTF-8') . ';border-width:' . htmlentities($configResponsiveCss['ATMR_MENU_BORDERSIZE'], ENT_COMPAT, 'UTF-8') . ';}';
            $css[] = '.li-niveau1 .phtm_menu_span, .li-niveau1 a .phtm_menu_span {color:' . htmlentities($configResponsiveCss['ATMR_MENU_COLOR'], ENT_COMPAT, 'UTF-8') . ';}';
            $configResponsiveCss['ATMR_MENU_BGCOLOR_CL'] = explode($this->gradient_separator, $configResponsiveCss['ATMR_MENU_BGCOLOR_CL']);

            if (isset($configResponsiveCss['ATMR_MENU_BGCOLOR_CL'][1])) {
                $color1 = htmlentities($configResponsiveCss['ATMR_MENU_BGCOLOR_CL'][0], ENT_COMPAT, 'UTF-8');
                $color2 = htmlentities($configResponsiveCss['ATMR_MENU_BGCOLOR_CL'][1], ENT_COMPAT, 'UTF-8');

                if (isset($configResponsiveCss['EPHTM_RESP_SUBMENU_ICON_CL']) && !empty($configResponsiveCss['EPHTM_RESP_SUBMENU_ICON_CL'])) {
                    $css[] = '#ephtm_menu.ephtm_menu_toggle_open ul#menu li.li-niveau1.sub a.a-niveau1 span {background-color: ' . $color1 . '; background: url(' . $configResponsiveCss['EPHTM_RESP_SUBMENU_ICON_CL'] . ') no-repeat right 15px center, linear-gradient(' . $color1 . ', ' . $color2 . ');}';
                }

                $css[] = '.li-niveau1 a .phtm_menu_span, .li-niveau1 .phtm_menu_span {background: ' . $color1 . '; background: linear-gradient(' . $color1 . ', ' . $color2 . ');}';
            } else {
                $css[] = '.li-niveau1 a .phtm_menu_span, .li-niveau1 .phtm_menu_span {background:' . htmlentities($configResponsiveCss['ATMR_MENU_BGCOLOR_CL'][0], ENT_COMPAT, 'UTF-8') . ';}';
            }

            $configResponsiveCss['ATMR_MENU_BGCOLOR_OP'] = explode($this->gradient_separator, $configResponsiveCss['ATMR_MENU_BGCOLOR_OP']);

            if (isset($configResponsiveCss['ATMR_MENU_BGCOLOR_OP'][1])) {
                $color1 = htmlentities($configResponsiveCss['ATMR_MENU_BGCOLOR_OP'][0], ENT_COMPAT, 'UTF-8');
                $color2 = htmlentities($configResponsiveCss['ATMR_MENU_BGCOLOR_OP'][1], ENT_COMPAT, 'UTF-8');

                if (isset($configResponsiveCss['EPHTM_RESP_SUBMENU_ICON_OP']) && !empty($configResponsiveCss['EPHTM_RESP_SUBMENU_ICON_OP'])) {
                    $css[] = '#ephtm_menu.ephtm_menu_toggle_open ul#menu li.li-niveau1.sub.ephtm_sub_open a.a-niveau1 span, #ephtm_menu.ephtm_menu_toggle_open ul#menu li.li-niveau1.sub a.a-niveau1.phtm_menu_actif span {background-color: ' . $color1 . '; background: url(' . $configResponsiveCss['EPHTM_RESP_SUBMENU_ICON_OP'] . ') no-repeat right 15px center, linear-gradient(' . $color1 . ', ' . $color2 . ');}';
                }

                $css[] = '#ephtm_menu.ephtm_menu_toggle_open .li-niveau1.sub.ephtm_sub_open a .phtm_menu_span, .li-niveau1 a:focus .phtm_menu_span, .li-niveau1 a.phtm_menu_actif .phtm_menu_span, .li-niveau1 .phtm_menu_span:focus, .li-niveau1:focus > a.a-niveau1 .phtm_menu_span {background-color: ' . $color1 . '; background: linear-gradient(' . $color1 . ', ' . $color2 . ');}';
            } else {
                $css[] = '#ephtm_menu.ephtm_menu_toggle_open .li-niveau1.sub.ephtm_sub_open a .phtm_menu_span, .li-niveau1 a:focus .phtm_menu_span, .li-niveau1 a.phtm_menu_actif .phtm_menu_span, .li-niveau1 .phtm_menu_span:focus, .li-niveau1:focus > a.a-niveau1 .phtm_menu_span {background-color:' . htmlentities($configResponsiveCss['ATMR_MENU_BGCOLOR_OP'][0], ENT_COMPAT, 'UTF-8') . ';}';
            }

            $configResponsiveCss['ATMR_SUBMENU_BGCOLOR'] = explode($this->gradient_separator, $configResponsiveCss['ATMR_SUBMENU_BGCOLOR']);

            if (isset($configResponsiveCss['ATMR_SUBMENU_BGCOLOR'][1])) {
                $color1 = htmlentities($configResponsiveCss['ATMR_SUBMENU_BGCOLOR'][0], ENT_COMPAT, 'UTF-8');
                $color2 = htmlentities($configResponsiveCss['ATMR_SUBMENU_BGCOLOR'][1], ENT_COMPAT, 'UTF-8');
                $css[] = '.li-niveau1 .ephtm_sub {background-color: ' . $color1 . '; background: linear-gradient(' . $color1 . ', ' . $color2 . ');}';
            } else {
                $css[] = '.li-niveau1 .ephtm_sub {background-color:' . htmlentities($configResponsiveCss['ATMR_SUBMENU_BGCOLOR'][0], ENT_COMPAT, 'UTF-8') . ';}';
            }

            $css[] = '.li-niveau1 .ephtm_sub {border-color:' . htmlentities($configResponsiveCss['ATMR_SUBMENU_BORDERCOLOR'], ENT_COMPAT, 'UTF-8') . '; border-width:' . htmlentities($configResponsiveCss['ATMR_SUBMENU_BORDERSIZE'], ENT_COMPAT, 'UTF-8') . ';}';
            $css[] = '#ephtm_menu .ephtm_column_wrap {' . $this->generateOptimizedCssRule('padding', $configResponsiveCss['ATMR_COLUMNWRAP_PADDING']) . $this->generateOptimizedCssRule('margin', $configResponsiveCss['ATMR_COLUMNWRAP_MARGIN']) . '}';
            $css[] = '#ephtm_menu .ephtm_column_wrap_td {border-color:' . htmlentities($configResponsiveCss['ATMR_COLUMNWRAP_BORDERCOLOR'], ENT_COMPAT, 'UTF-8') . ';border-width:' . htmlentities($configResponsiveCss['ATMR_COLUMNWRAP_BORDERSIZE'], ENT_COMPAT, 'UTF-8') . ';}';
            $css[] = '#ephtm_menu .ephtm_column {' . $this->generateOptimizedCssRule('padding', $configResponsiveCss['ATMR_COLUMN_PADDING']) . $this->generateOptimizedCssRule('margin', $configResponsiveCss['ATMR_COLUMN_MARGIN']) . '}';
            $css[] = '#ephtm_menu .ephtm_column_wrap span.column_wrap_title {' . $this->generateOptimizedCssRule('padding', $configResponsiveCss['ATMR_COLUMNTITLE_PADDING']) . $this->generateOptimizedCssRule('margin', $configResponsiveCss['ATMR_COLUMNTITLE_MARGIN']) . '}';
            $css[] = '.ephtm_column_wrap span.column_wrap_title, .ephtm_column_wrap span.column_wrap_title a, .ephtm_column_wrap span.column_wrap_title span[data-href] {color:' . htmlentities($configResponsiveCss['ATMR_COLUMN_TITLE_COLOR'], ENT_COMPAT, 'UTF-8') . ';}';
            $css[] = '#ephtm_menu .ephtm_column ul.ephtm_elements li a, #ephtm_menu .ephtm_column ul.ephtm_elements li span[data-href] {' . $this->generateOptimizedCssRule('padding', $configResponsiveCss['ATMR_COLUMN_ITEM_PADDING']) . $this->generateOptimizedCssRule('margin', $configResponsiveCss['ATMR_COLUMN_ITEM_MARGIN']) . '}';
            $css[] = '.ephtm_column_wrap a {color:' . htmlentities($configResponsiveCss['ATMR_COLUMN_ITEM_COLOR'], ENT_COMPAT, 'UTF-8') . ';}';
            $css[] = '#ephtm_menu.ephtm_menu_toggle_open ul#menu .phtm_hide_desktop {display: block !important;}';
            $css[] = '#ephtm_menu.ephtm_menu_toggle_open ul#menu li.li-niveau1 {display: block !important;}';
            $css[] = '#ephtm_menu.ephtm_menu_toggle_open ul#menu li.li-niveau1.phtm_hide_mobile {display: none !important;}';

            if (empty($configResponsiveCss['EPHTM_RESP_TOGGLE_ENABLED'])) {
                $css[] = '#ephtm_menu.ephtm_menu_toggle_open ul#menu li.li-niveau1.phtm_menu_toggle.ephtm_menu_mobile_mode {display: none !important;}';
            }

            $css[] = '#ephtm_menu.ephtm_menu_toggle_open ul#menu li.li-niveau1 a.a-niveau1 {float: none;}';
            $css[] = '#ephtm_menu.ephtm_menu_toggle_open ul#menu li div.ephtm_sub  {display: none; position: static; height: auto;}';
            $css[] = '#ephtm_menu.ephtm_menu_toggle_open ul#menu li div.ephtm_sub.ephtm_submenu_toggle_open  {display: block;}';
            $css[] = '#ephtm_menu.ephtm_menu_toggle_open table.columnWrapTable {display: table !important; width: 100% !important;}';
            $css[] = '#ephtm_menu.ephtm_menu_toggle_open table.columnWrapTable tr td {display: block;}';
            $css[] = '#ephtm_menu.ephtm_menu_toggle_open ul#menu li.phtm_search .searchboxATM { display: flex; }';
            $css[] = '#ephtm_menu.ephtm_menu_toggle_open ul#menu li.phtm_search .searchboxATM .search_query_atm { padding: 15px 5px; width: 100%; }';
            $css[] = '#ephtm_menu ul#menu .li-niveau1 div.ephtm_sub {opacity: 1;visibility:visible;}';
            $css[] = '#ephtm_menu ul#menu .li-niveau1:hover div.ephtm_sub, #ephtm_menu ul#menu .li-niveau1:focus div.ephtm_sub {transition: none;}';
            $css[] = '}';
        }

        if ($id_shop != false) {
            $ids_shop = [$id_shop];
        } else {
            $ids_shop = array_values(Shop::getContextListShopID());
        }

      
        $global_css_file = [];

        foreach ($ids_shop as $id_shop) {
            $global_css_file[] = str_replace('.css', '-' . $id_shop . '.css', self::GLOBAL_CSS_FILE);
        }

        if (count($css) && count($global_css_file)) {

            foreach ($global_css_file as $value) {
                file_put_contents($value, implode("\n", $css));
            }

        }

    }

    private function generateOptimizedCssRule($property, $value) {

        $instruction = '';
        $shortPropertyOrder = [
            'top',
            'right',
            'bottom',
            'left',
        ];

        switch ($property) {
        case 'padding':
        case 'margin':

            if (substr_count($value, 'unset') == 4) {
                return $instruction;
            }

            $containsUnset = strpos($value, 'unset');
            $explodedValues = array_map('trim', explode(' ', $value));
            $filteredValues = array_filter($explodedValues);
            $nbValues = count($filteredValues);

            if (!$nbValues) {
                return $instruction;
            }

            if ($nbValues == 4 && $containsUnset === false) {
                return $property . ':' . $value . ';';
            }

            foreach ($filteredValues as $key => $val) {

                if ($val == 'unset' || !isset($shortPropertyOrder[$key])) {
                    continue;
                }

                $instruction .= $property . '-' . $shortPropertyOrder[$key] . ':' . $val . ';';
            }

            break;
        default:
            break;
        }

        return $instruction;
    }

    private function _getConfigKeys() {

        $config = $configResponsive = [];

        foreach ($this->_fieldsOptions as $key => $data) {

            if (isset($data['mobile']) && $data['mobile']) {
                $configResponsive[] = $key;
            } else {
                $config[] = $key;
            }

        }

        return [$config, $configResponsive];
    }

    protected function hex2rgb($hexstr, $opacity = false) {

        if (Tools::strlen($hexstr) < 7) {
            $hexstr = $hexstr . str_repeat(Tools::substr($hexstr, -1), 7 - Tools::strlen($hexstr));
        }

        $int = hexdec($hexstr);

        if ($opacity === false) {
            return 'rgb(' . (0xFF & ($int >> 0x10)) . ', ' . (0xFF & ($int >> 0x8)) . ', ' . (0xFF & $int) . ')';
        } else {
            return 'rgba(' . (0xFF & ($int >> 0x10)) . ', ' . (0xFF & ($int >> 0x8)) . ', ' . (0xFF & $int) . ', ' . $opacity . ')';
        }

    }

    public function ajaxProcessSaveAdvanceConfig() {

        foreach ($this->_fieldsOptions as $key => $field) {

            if (!isset($field['advanced']) || isset($field['advanced']) && !$field['advanced']) {
                continue;
            }

            Configuration::updateValue($key, Tools::getValue($key));

        }

        $contextShops = array_values(Shop::getContextListShopID());
        $error = false;

        foreach ($contextShops as $id_shop) {
            $advanced_css_file_shop = str_replace('.css', '-' . $id_shop . '.css', self::ADVANCED_CSS_FILE);

            if (!file_put_contents($advanced_css_file_shop, Tools::getValue('advancedConfig'))) {
                $error = $this->l('Error while saving advanced styles');
            }

        }

        if ($error) {
            $this->context->controller->errors[] = $error;
        } else {
            $this->context->controller->confirmations[] = $this->l('Styles updated successfully');
        }

    }

}
