<?php

/**
 * Class AdminImagesControllerCore
 *
 * @since 1.9.5.0
 */
class AdminImagesControllerCore extends AdminController {

    public $php_self = 'adminimages';
	// @codingStandardsIgnoreStart
    /** @var int $start_time */
    protected $start_time = 0;
    /** @var int $max_execution_time */
    protected $max_execution_time = 7200;
    /** @var bool $display_move */
    protected $display_move;
    // @codingStandardsIgnoreEnd

    const IMAGE_TYPE_SINGULAR = [
       
        'education'      => 'educations',
        'img'          => 'imgs',
        'module'       => 'modules',
    ];

    const IMG_DIR = [

    ];

    const SINGULAR_DIR = [
        'img'          => ['dir' => _EPH_IMG_DIR_, 'iterate' => false],
        'education'      => ['dir' => _EPH_EDUC_IMG_DIR_, 'iterate' => true],

    ];

   
    public function __construct() {

        $this->bootstrap = true;
        $this->table = 'image_type';
        $this->className = 'ImageType';
        $this->publicName = $this->la('Gestion des Images');
        $this->lang = false;
        $this->context = Context::getContext();

        // No need to display the old image system migration tool except if product images are in _EPH_PROD_IMG_DIR_
        $this->display_move = false;
        $dir = _EPH_PROD_IMG_DIR_;

        if (is_dir($dir)) {

            if ($dh = opendir($dir)) {

                while (($file = readdir($dh)) !== false && $this->display_move == false) {

                    if (!is_dir($dir . DIRECTORY_SEPARATOR . $file) && $file[0] != '.' && is_numeric($file[0])) {
                        $this->display_move = true;
                    }

                }

                closedir($dh);
            }

        }

        $this->fields_options = [
            'images'     => [
                'title'       => $this->la('Images generation options'),
                'icon'        => 'icon-picture',
                'top'         => '',
                'bottom'      => '',
                'description' => $this->la('JPEG images have a small file size and standard quality. PNG images have a larger file size, a higher quality and support transparency. Note that in all cases the image files will have the .jpg extension.') . '<br /><br />' . $this->la('WARNING: This feature may not be compatible with your theme, or with some of your modules. In particular, PNG mode is not compatible with the Watermark module. If you encounter any issues, turn it off by selecting "Use JPEG".'),
                'fields'      => [
                    'EPH_IMAGE_QUALITY'            => [
                        'title'    => $this->la('Image format'),
                        'show'     => true,
                        'required' => true,
                        'type'     => 'radio',
                        'choices'  => ['jpg' => $this->la('Use JPEG.'), 'png' => $this->la('Use PNG only if the base image is in PNG format.'), 'png_all' => $this->la('Use PNG for all images.')],
                    ],
                    'EPH_JPEG_QUALITY'             => [
                        'title'      => $this->la('JPEG compression'),
                        'hint'       => $this->la('Ranges from 0 (worst quality, smallest file) to 100 (best quality, biggest file).') . ' ' . $this->la('Recommended: 90.'),
                        'validation' => 'isUnsignedId',
                        'required'   => true,
                        'cast'       => 'intval',
                        'type'       => 'text',
                    ],
                    'EPH_PNG_QUALITY'              => [
                        'title'      => $this->la('PNG compression'),
                        'hint'       => $this->la('PNG compression is lossless: unlike JPG, you do not lose image quality with a high compression ratio. However, photographs will compress very badly.') . ' ' . $this->la('Ranges from 0 (biggest file) to 9 (smallest file, slowest decompression).') . ' ' . $this->la('Recommended: 7.'),
                        'validation' => 'isUnsignedId',
                        'required'   => true,
                        'cast'       => 'intval',
                        'type'       => 'text',
                    ],
                    'EPH_IMAGE_GENERATION_METHOD'  => [
                        'title'      => $this->la('Generate images based on one side of the source image'),
                        'validation' => 'isUnsignedId',
                        'required'   => false,
                        'cast'       => 'intval',
                        'type'       => 'select',
                        'list'       => [
                            [
                                'id'   => '0',
                                'name' => $this->la('Automatic (longest side)'),
                            ],
                            [
                                'id'   => '1',
                                'name' => $this->la('Width'),
                            ],
                            [
                                'id'   => '2',
                                'name' => $this->la('Height'),
                            ],
                        ],
                        'identifier' => 'id',
                        'visibility' => Shop::CONTEXT_ALL,
                    ],
                    'EPH_PRODUCT_PICTURE_MAX_SIZE' => [
                        'title'      => $this->la('Maximum file size of product customization pictures'),
                        'hint'       => $this->la('The maximum file size of pictures that customers can upload to customize a product (in bytes).'),
                        'validation' => 'isUnsignedInt',
                        'required'   => true,
                        'cast'       => 'intval',
                        'type'       => 'text',
                        'suffix'     => $this->la('bytes'),
                        'visibility' => Shop::CONTEXT_ALL,
                    ],
                    'EPH_PRODUCT_PICTURE_WIDTH'    => [
                        'title'      => $this->la('Product picture width'),
                        'hint'       => $this->la('Width of product customization pictures that customers can upload (in pixels).'),
                        'validation' => 'isUnsignedInt',
                        'required'   => true,
                        'cast'       => 'intval',
                        'type'       => 'text',
                        'width'      => 'px',
                        'suffix'     => $this->la('pixels'),
                        'visibility' => Shop::CONTEXT_ALL,
                    ],
                    'EPH_PRODUCT_PICTURE_HEIGHT'   => [
                        'title'      => $this->la('Product picture height'),
                        'hint'       => $this->la('Height of product customization pictures that customers can upload (in pixels).'),
                        'validation' => 'isUnsignedInt',
                        'required'   => true,
                        'cast'       => 'intval',
                        'type'       => 'text',
                        'height'     => 'px',
                        'suffix'     => $this->la('pixels'),
                        'visibility' => Shop::CONTEXT_ALL,
                    ],
                    'EPH_HIGHT_DPI'                => [
                        'type'       => 'bool',
                        'title'      => $this->la('Generate high resolution images'),
                        'required'   => false,
                        'is_bool'    => true,
                        'hint'       => $this->la('This will generate an additional file for each image (thus doubling your total amount of images). Resolution of these images will be twice higher.'),
                        'desc'       => $this->la('Enable to optimize the display of your images on high pixel density screens.'),
                        'visibility' => Shop::CONTEXT_ALL,
                    ],

                ],
                'submit'      => ['title' => $this->la('Save')],
            ],
            'webPconfig' => [
                'title'       => $this->la('WebP Images options'),
                'icon'        => 'icon-picture',
                'top'         => '',
                'bottom'      => '',
                'description' => $this->la('WebP is a modern image format that provides superior lossless and lossy compression for images on the web. Using WebP, webmasters and web developers can create smaller, richer images that make the web faster.'),
                'fields'      => [
                    'WEBCONVERTOR_DEMO_MODE'       => [
                        'type'     => 'bool',
                        'title'    => $this->la('Demo mode'),
                        'show'     => true,
                        'required' => true,
                        'is_bool'  => true,
                        'hint'     => $this->la(
                            'This option is recommended during image generation process to avoid 404 errors of images that have not yet been generated. Once the process is complete you will need to turn this option off in order for the webp images to appear on the website.'
                        ),
                        'desc'     => $this->la(
                            'This option is recommended during image generation process to avoid 404 errors of images that have not yet been generated. Once the process is complete you will need to turn this option off in order for the webp images to appear on the website.'
                        ),
                    ],
                    'WEBP_CONVERTOR_TO_USE'        => [
                        'title'    => $this->la('Converter to use'),
                        'show'     => true,
                        'required' => true,
                        'json'     => true,
                        'type'     => 'radialbox',
                        'choices'  => [
                            'cwebp'   => [
                                'label'    => $this->la('CWebP (Calls cwebp binary directly)') .
                                (!$this->isCwebpCompatible()
                                    ?
                                    '<b class="conversion-not-available"> ' .
                                    $this->la('Not available') .
                                    '</b>'
                                    :
                                    ''),
                                'disabled' => !$this->isCwebpCompatible(),
                            ],
                            'imagick' => [
                                'label'    => $this->la('Imagick extension (ImageMagick wrapper)') .
                                (!$this->isImagickCompatible()
                                    ?
                                    '<b class="conversion-not-available"> ' .
                                    $this->la('Not available') .
                                    '</b>'
                                    :
                                    ''),
                                'disabled' => !$this->isImagickCompatible(),
                            ],
                            'gmagick' => [

                                'label'    => $this->la('Gmagick extension (ImageMagick wrapper)') .
                                (!$this->isGmagickCompatible()
                                    ?
                                    '<b class="conversion-not-available"> ' .
                                    $this->la('Not available') .
                                    '</b>'
                                    :
                                    ''),
                                'disabled' => !$this->isGmagickCompatible(),
                            ],
                            'gd'      => [

                                'label'    => $this->la('GD Graphics (Draw) extension (LibGD wrapper)') .
                                (!$this->isGdCompatible()
                                    ?
                                    '<b class="conversion-not-available"> ' .
                                    $this->la('Not available') .
                                    '</b>'
                                    :
                                    ''),
                                'disabled' => !$this->isGdCompatible(),
                            ],
                            'ewww'    => [
                                'label'    => $this->la('EWW Connects to EWWW Image Optimizer cloud service') .
                                (!$this->isEwwwCompatible()
                                    ?
                                    '<b class="conversion-not-available"> ' .
                                    $this->la('Not available') .
                                    '</b>'
                                    :
                                    ''),

                                'disabled' => !$this->isEwwwCompatible(),
                            ],
                        ],
                    ],
                    'WEBP_COMMON_QUALITY'          => [
                        'title'      => $this->la('Quality'),
                        'hint'       => $this->la('Specify the compression factor for RGB channels between 0 and 100'),
                        'validation' => 'isUnsignedId',
                        'required'   => false,
                        'cast'       => 'intval',
                        'type'       => 'select',
                        'list'       => [
                            [
                                'id'   => 10,
                                'name' => 10,
                            ],
                            [
                                'id'   => 20,
                                'name' => 20,
                            ],
                            [
                                'id'   => 30,
                                'name' => 30,
                            ],
                            [
                                'id'   => 40,
                                'name' => 40,
                            ],
                            [
                                'id'   => 50,
                                'name' => 50,
                            ],
                            [
                                'id'   => 60,
                                'name' => 60,
                            ],
                            [
                                'id'   => 70,
                                'name' => 70,
                            ],
                            [
                                'id'   => 80,
                                'name' => 80,
                            ],
                            [
                                'id'   => 90,
                                'name' => 90,
                            ],
                            [
                                'id'   => 100,
                                'name' => 100,
                            ],
                        ],
                        'identifier' => 'id',
                        'visibility' => Shop::CONTEXT_ALL,
                    ],
                    'WEBP_CONFIG_COMMON_METHOD'    => [
                        'title'    => $this->la('Image format'),
                        'show'     => true,
                        'required' => true,
                        'type'     => 'radio',
                        'choices'  => [
                            0 => $this->la('1 (best performance)'),
                            1 => $this->la('2 (good performance)'),
                            2 => $this->la('3 (decent performance with better quality)'),
                            3 => $this->la('4 (balance between performance and quality)'),
                            4 => $this->la('5 (decent quality with better performance)'),
                            5 => $this->la('6 (high quality)'),
                            6 => $this->la('7 (best quality)'),
                        ],
                    ],
                    'WEBP_CONFIG_LOW_MEMORY'       => [
                        'type'     => 'bool',
                        'title'    => $this->la('Low memory'),
                        'show'     => true,
                        'required' => true,
                        'is_bool'  => true,
                        'hint'     => $this->la(
                            'Reduce memory usage of lossy encoding at the cost of ~30% longer encoding time and marginally larger output size.'
                        ),
                        'desc'     => $this->la(
                            'In case of low or limited server resources (specifically memory) enabling this option will reduce memory usage.'
                        ),
                    ],
                    'WEBP_CONFIG_COMMON_LOSSLESS'  => [
                        'type'     => 'bool',
                        'title'    => $this->la('Lossless'),
                        'show'     => true,
                        'required' => true,
                        'is_bool'  => true,
                        'hint'     => $this->la(
                            'Encode the image without any quality loss. The option is ignored for PNG\'s. Recommended to No'
                        ),
                        'desc'     => $this->la('Recommended to no'),
                    ],
                    'WEBP_CONFIG_COMMON_META_DATA' => [
                        'title'    => $this->la('Metadata'),
                        'show'     => true,
                        'required' => true,
                        'type'     => 'radio',
                        'hint'     => $this->la(
                            'Metadata to copy from the input to the output if present'
                        ),
                        'desc'     => $this->la(
                            'Note: Only cwebp supports all values. gd will always remove all metadata. ewww, imagick and gmagick can either strip all, or keep all (they will keep all, unless metadata is set to none)'
                        ),
                        'choices'  => [
                            'all'  => $this->la('All'),
                            'none' => $this->la('None (recommended)'),
                            'exif' => $this->la('EXIF'),
                            'icc'  => $this->la('ICC'),
                            'xmp'  => $this->la('XMP'),
                        ],
                    ],

                ],
                'submit'      => ['title' => $this->la('Save')],

            ],
            'ewwwconfig' => [
                'title'       => $this->la('EWWW cloud convert'),
                'icon'        => 'icon-cloud',
                'top'         => '',
                'bottom'      => '',
                'description' => $this->la(
                    'EWWW Image Optimizer is a very cheap cloud service for optimizing images. After purchasing an API key, add the converter in the extra-converters option, with key set to the key.'
                ) . '<br>' . $this->la(
                    'The EWWW api doesn\'t support the lossless option, but it does automatically convert PNG\'s losslessly. Metadata is either all or none. If you have set it to something else than one of these, all metadata will be preserved.'
                ),
                'fields'      => [
                    'WEBP_CONVERTER_EWWW_API_KEY' => [
                        'title' => $this->la('API Key'),
                        'type'  => 'text',
                    ],

                ],
                'submit'      => ['title' => $this->la('Save')],

            ],
            'cwebconfig' => [
                'title'       => $this->la('CWebP conversion settings'),
                'icon'        => 'icon-terminal',
                'top'         => '',
                'bottom'      => '',
                'description' => $this->la(
                    'EWWW Image Optimizer is a very cheap cloud service for optimizing images. After purchasing an API key, add the converter in the extra-converters option, with key set to the key.'
                ) . '<br>' . $this->la(
                    'The EWWW api doesn\'t support the lossless option, but it does automatically convert PNG\'s losslessly. Metadata is either all or none. If you have set it to something else than one of these, all metadata will be preserved.'
                ),
                'fields'      => [
                    'WEBP_CONVERTER_CWEBP_USE_NICE'                => [
                        'type'     => 'bool',
                        'title'    => $this->la('Use `nice` command'),
                        'show'     => true,
                        'required' => true,
                        'is_bool'  => true,
                        'hint'     => $this->la(
                            'If `nice` command is found on host, binary is executed with low priority in order to save system resources'
                        ),
                    ],
                    'WEBP_CONVERTER_CWEBP_TRY_COMMON_SYSTEM_PATHS' => [
                        'type'     => 'bool',
                        'title'    => $this->la('Try common system paths'),
                        'show'     => true,
                        'required' => true,
                        'is_bool'  => true,
                        'hint'     => $this->la(
                            'It is tested whether cwebp is available in a common system path (eg /usr/bin/cwebp, ..)'
                        ),
                    ],
                    'WEBP_CONVERTER_CWEBP_TRY_SUPPLIED_BINARY'     => [
                        'type'     => 'bool',
                        'title'    => $this->la('Try supplied binary'),
                        'show'     => true,
                        'required' => true,
                        'is_bool'  => true,
                        'hint'     => $this->la(
                            'If CWebP is not installed on the server, then supplied binary is selected from Converters/Binaries (according to OS) - after validating checksum'
                        ),
                    ],
                    'WEBP_CONVERTER_CWEBP_AUTO_FILTER'             => [
                        'type'     => 'bool',
                        'title'    => $this->la('Turns auto-filter on'),
                        'show'     => true,
                        'required' => true,
                        'is_bool'  => true,
                        'hint'     => $this->la(
                            'This algorithm will spend additional time optimizing the filtering strength to reach a well-balanced quality. Unfortunately, it is extremely expensive in terms of computation. It takes about 5-10 times longer to do a conversion. A 1MB picture which perhaps typically takes about 2 seconds to convert, will takes about 15 seconds to convert with auto-filter. So in most cases, you will want to leave this at its default, which is off.'
                        ),
                    ],
                    'WEBP_CONVERTER_CWEBP_CMD_OPTIONS'             => [
                        'title' => $this->la('Command line options'),
                        'type'  => 'text',
                        'hint'  => $this->la(
                            'This allows you to set any parameter available for cwebp in the same way as you would do when executing cwebp. You could ie set it to "-sharpness 5 -mt -crop 10 10 40 40". '
                        ),
                        'desc'  =>
                        $this->la(
                            'Read more about all the available parameters here: https://developers.google.com/speed/webp/docs/cwebp#additional_options'
                        ),
                    ],

                ],
                'submit'      => ['title' => $this->la('Save')],

            ],
        ];

        if ($this->display_move) {
            $this->fields_options['product_images']['fields']['EPH_LEGACY_IMAGES'] = [
                'title'      => $this->la('Use the legacy image filesystem'),
                'hint'       => $this->la('This should be set to yes unless you successfully moved images in "Images" page under the "Preferences" menu.'),
                'validation' => 'isBool',
                'cast'       => 'intval',
                'required'   => false,
                'type'       => 'bool',
                'visibility' => Shop::CONTEXT_ALL,
            ];
        }

        parent::__construct();

        $this->paragridScript = EmployeeConfiguration::get('EXPERT_IMAGES_SCRIPT');

        if (empty($this->paragridScript)) {
            EmployeeConfiguration::updateValue('EXPERT_IMAGES_SCRIPT', $this->generateParaGridScript(true));
            $this->paragridScript = EmployeeConfiguration::get('EXPERT_IMAGES_SCRIPT');
        }

        $this->configurationField = Tools::jsonDecode(EmployeeConfiguration::get('EXPERT_IMAGES_FIELDS'), true);

        if (empty($this->configurationField)) {
            EmployeeConfiguration::updateValue('EXPERT_IMAGES_FIELDS', Tools::jsonEncode($this->getImageTypeFields()));
            $this->configurationField = Tools::jsonDecode(EmployeeConfiguration::get('EXPERT_IMAGES_FIELDS'), true);
        }
		
		$this->extracss = $this->pushCSS([
			$this->admin_webpath . _EPH_ADMIN_THEME_DIR_.  $this->bo_theme . '/css/toastr.css',
			$this->admin_webpath . _EPH_ADMIN_THEME_DIR_.  $this->bo_theme . '/css/images.css'
		]);

    }
    
	public function setAjaxMedia() {
		
		return $this->pushJS([
			'/js/toastr.min.js',
			'/js/ajaxq.js',
			'/js/regenerate.js',
			'/js/images.js',
		]);
	}
	
	public function ajaxProcessOpenTargetController() {

		$targetController = $this->targetController;			
		$this->paragridScript = $this->generateParaGridScript();
		$this->setAjaxMedia();
		$tabs = $this->generateImageConfigurator();
		$data = $this->createTemplate($this->table.'.tpl');		
		$images = ImageManager::getImages();
		$images = Tools::jsonDecode($images, true);
		$file = fopen("testImageOpenTarget.txt","w");
		fwrite($file, print_r($images, true));
				
		$jsDef = [
            'imageList'              => Tools::jsonEncode($images),
            'educationProgress'        => count($images['education']['done']),
            'imgProgress'            => count($images['img']['done']),
			'education_management' => _EDUCATION_MODE_
        ];		

		$data->assign([
			'paragridScript'     => $this->paragridScript,
			'manageHeaderFields' => $this->manageHeaderFields,
			'customHeaderFields' => $this->manageFieldsVisibility($this->configurationField),
			'controller'     => $this->controller_name,
			'tableName'      => $this->table,
			'className'      => $this->className,
			'link'           => $this->context->link,
			'extraJs'        => $this->push_js_files,
			'extracss'			=> $this->extracss,
			'tabs'			 => $tabs,
			'educationImageCount'      => $images['education']['total'],    
			'imgImageCount'          => $images['img']['total'],
            'educationProgress'        => !is_null(count($images['education']['done'])) ? count($images['education']['done']) : 0,
            'imgProgress'            => !is_null(count($images['img']['done'])) ? count($images['img']['done']) : 0,
			'jsDef'				=> $jsDef,
			'education_management' => _EDUCATION_MODE_
		]);

		$li = '<li id="uper'.$targetController.'" data-controller="AdminDashboard" data-self="'.$this->link_rewrite.'" data-name="'.$this->page_title.'"><a href="#content'.$targetController.'">'.$this->publicName.'</a><button type="button" class="close tabdetail" data-id="uper'.$targetController.'"><i class="icon icon-times-circle"></i></button></li>';
		$html = '<div id="content'.$targetController.'" class="panel col-lg-12" style="display; flow-root;">' . $data->fetch() . '</div>';

		$result = [
			'li'   => $li,

			'html' => $html,
		];

		die(Tools::jsonEncode($result));
	}
	
	
    public function generateImageConfigurator() {
		
		$tabs = [];
		$tabs['Options des Images'] = [
			'key'		=> 'optImage',
			'content'	=> $this->generateOptions('images')
		];
		$tabs['Gestion WebP'] = [
			'key'		=> 'webP',
			'content'	=> $this->generateOptions('webPconfig')
		];
		$tabs['Conversion WebP'] = [
			'key'		=> 'webPconversion',
			'content'	=> $this->generateOptions('cwebconfig')
		];
		
		
		return $tabs;
	}
	
	public function generateOptions($tab) {

        $fields_options = [
            $tab => $this->fields_options[$tab],
        ];

        if ($fields_options && is_array($fields_options)) {
            $this->tpl_option_vars['titleList'] = $this->la('List') . ' ' . $this->toolbar_title[0];
            $this->tpl_option_vars['controller'] = Tools::getValue('controller');

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


   
    
	public function generateParaGridScript($regenerate = false) {
		
		
		
		$gridExtraFunction = ['
			
			function addNewImageType() {

				$.ajax({
        			type: "GET",
        			url: AjaxLinkAdminImages,
        			data: {
						action: "addImageType",
            			ajax: !0
        			},
        			dataType: "json",
        			success: function(data) {
            			$("#imageFormArea").html(data.html);
						$("#imageArea").slideUp();
						$("#imageFormArea").slideDown();
        			}
    			});
				}
				
				
				
				

			', ];
		
		$paragrid = new ParamGrid($this->className, $this->controller_name, $this->table, $this->identifier);
		
		$paragrid->height = 700;
		$paragrid->showNumberCell = 0;
		$paragrid->create = 'function (evt, ui) {
			buildHeadingAction(\'' . 'grid_' . $this->controller_name . '\', \'' . $this->controller_name . '\');
        }';
		$paragrid->rowInit = 'function (ui) {
			return {' . PHP_EOL . '
                    attr: \'data-link="\'+AjaxLink' . $this->controller_name . '+\'" data-class="' . $this->className . '" data-rowIndx="\' + ui.rowIndx+\'" data-object="\' + ui.rowData.' . $this->identifier . '+\' "\',
            };
        }';
		$paragrid->complete = 'function(){
		window.dispatchEvent(new Event(\'resize\'));
        }';
		$paragrid->selectionModelType = 'row';
		$paragrid->showTitle =1;
		$paragrid->title = '\'' .$this->la('Gestion des images') . '\'';
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
                selected = selgrid' . $this->className . '.getSelection().length;
                var dataLenght = grid' . $this->className . '.option(\'dataModel.data\').length;
                return {
                    callback: function(){},
                    items: {
                        "add": {
                            name: \'' . $this->la('Ajouter un nouveau type d‘image') . ' \',
                            icon: "add",
                            callback: function(itemKey, opt, e) {
                                addNewImageType();
                            }
                        },
                        "edit": {
                            name : \'' . $this->la('Modifier le type ') . '\'' . '+rowData.name,
                            icon: "edit",
                            callback: function(itemKey, opt, e) {
                             	editImageType(rowData.id_image_type);
                            }
                        },						
                        "sep1": "---------",
                        "delete": {
                            name: \'' . $this->la('Supprimer ') . '\'' . '+rowData.name,
                            icon: "delete",
                            callback: function(itemKey, opt, e) {
                                deleteImageType(rowData.id_image_type);
                            }
                        },
                    },
                };
            }',
			]];
       
		$paragrid->gridExtraFunction = $gridExtraFunction;
		$option = $paragrid->generateParaGridOption();
		$script =  $paragrid->generateParagridScript() ;
		
		$this->paragridScript = $script;
		return '<script type="text/javascript">'. PHP_EOL . $this->paragridScript . PHP_EOL .  '</script>';
	}
	
	public function generateParaGridOption() {

        return '';

    }

    public function getImageTypeRequest() {

      
		$images = Db::getInstance()->executeS(
            (new DbQuery())
                ->select('*')
                ->from('image_type')
                ->orderBy('`id_image_type` ASC')
        );
        $imageLink = $this->context->link->getAdminLink($this->controller_name);
		$excludeKeys = ['id_image_type', 'name', 'width', 'height'];

        foreach ($images as &$image) {
			
			foreach( $image as $key => $value) {     
				if(!in_array($key, $excludeKeys)) {
					if ($value == 1) {
                		$image[$key] = '<div class="i-active"></div>';
					} else {
                		$image[$key] = '<div class="i-inactive"></div>';
					}
					
				}
				
			}
			
        }
		
        return $images;
    }

    public function ajaxProcessgetImageTypeRequest() {

        die(Tools::jsonEncode($this->getImageTypeRequest()));

    }

    public function getImageTypeFields() {

       
		$structures = Db::getInstance()->executeS('SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = \''._DB_NAME_.'\' AND TABLE_NAME = \''._DB_PREFIX_.'image_type\'');
		
	       
		$excludeKeys = ['id_image_type', 'name', 'width', 'height'];
		
		$imagesFields = [
			
			[
                'title'      => $this->la('ID'),
                'width'      => 50,
                'dataIndx'   => 'id_image_type',
                'dataType'   => 'integer',
                'editable'   => false,
                'align'      => 'center',
                'hidden'     => true,
                'hiddenable' => 'no',
            ],
          

            [
                'title'      => $this->la('Name'),
                'width'      => 200,
                'dataIndx'   => 'name',
                'align'      => 'left',
                'editable'   => false,
                'dataType'   => 'string',
                'hiddenable' => 'no',
            ],
            [
                'title'    => $this->la('Width'),
                'width'    => 150,
                'dataIndx' => 'width',
                'align'    => 'center',
                'editable' => false,
                'dataType' => 'integer',
                'format'   => '# px',
            ],
            [
                'title'    => $this->la('Height'),
                'width'    => 150,
                'dataIndx' => 'height',
                'align'    => 'center',
                'editable' => false,
                'dataType' => 'integer',
                'format'   => '# px',
            ],
			
		];
		
		foreach($structures as $key => $column) {
			
			if(!in_array($column['COLUMN_NAME'], $excludeKeys)) {
				$controller = 'Admin'.ucfirst($column['COLUMN_NAME']).'Controller';
				if (class_exists($controller)) {
            		$targetClass = new $controller();
					$targetName = $targetClass->publicName;
				} else {
					$targetName = ucfirst($column['COLUMN_NAME']);
				}
				
				$imagesFields[] = [
					
					'title'    => $targetName,
                	'width'    => 200,
                	'dataIndx' => $column['COLUMN_NAME'],
                	'align'    => 'center',
                	'editable' => false,
                	'dataType' => 'html',
				];
			}
			
		}
		
		return $imagesFields;
    }

    public function ajaxProcessgetImageTypeFields() {

        die(Tools::jsonEncode($this->getImageTypeFields()));
    }
	


    public function postProcess() {

        // When moving images, if duplicate images were found they are moved to a folder named duplicates/
        parent::postProcess();

    }
	
   
    public function ajaxProcessRegenerateThumbnails() {

        $request = json_decode(file_get_contents('php://input'));
        $entityType = $request->entity_type;

        if (!$entityType) {
            $this->ajaxDie(json_encode([
                'hasError' => true,
                'errors'   => [$this->la('Entity type missing')],
            ]));
        } else

        if (!in_array($entityType, ['products', 'categories', 'manufacturers', 'suppliers', 'scenes', 'stores'])) {
            $this->ajaxDie(json_encode([
                'hasError' => true,
                'errors'   => [$this->la('Wrong entity type')],
            ]));
        }

        try {
            $idEntity = $this->getNextEntityId($request->entity_type);

            if (!$idEntity) {
                $this->ajaxDie(json_encode([
                    'hasError'    => true,
                    'errors'      => [$this->la('Thumbnails of this type have already been generated')],
                    'indexStatus' => $this->getIndexationStatus(),
                ]));
            }

            $this->regenerateNewImage($request->entity_type, $idEntity);
            Configuration::updateValue('EPH_IMAGES_LAST_UPD_' . strtoupper($request->entity_type), $idEntity);
        } catch (Exception $e) {
            $this->errors[] = $e->getMessage();
        }

        $indexationStatus = $this->getIndexationStatus();

        if (!$indexationStatus || !array_sum(array_column(array_values($indexationStatus), 'indexed'))) {
            // First run, regenerate no picture images, too
            $process = [
                'categories'    => _EPH_CAT_IMG_DIR_,
                'manufacturers' => _EPH_MANU_IMG_DIR_,
                'suppliers'     => _EPH_SUPP_IMG_DIR_,
                'scenes'        => _EPH_SCENE_IMG_DIR_,
                'products'      => _EPH_PROD_IMG_DIR_,
                'stores'        => _EPH_STORE_IMG_DIR_,
            ];

            foreach ($process as $type => $dir) {
                $this->_regenerateNoPictureImages(
                    $dir,
                    ImageType::getImagesTypes($type),
                    Language::getLanguages(false)
                );
            }

        }

        $this->ajaxDie(json_encode([
            'hasError'    => true,
            'errors'      => $this->errors,
            'indexStatus' => $indexationStatus,
        ]));
    }

    /**
     * Ajax - delete all previous images
     *
     * @since 1.0.4
     */
    public function ajaxProcessDeleteOldImages() {

        $process = [

            ['type' => 'education', 'dir' => _EPH_PROD_IMG_DIR_]
        ];

        foreach ($process as $proc) {
            try {
                // Getting format generation
                $formats = ImageType::getImagesTypes($proc['type']);
                Configuration::updateValue('EPH_IMAGES_LAST_UPD_' . strtoupper($proc['type']), 0);
                $this->_deleteOldImages($proc['dir'], $formats, ($proc['type'] == 'products' ? true : false));
            } catch (PhenyxShopException $e) {
                $this->errors[] = $e->getMessage();
            }

        }

        $this->ajaxDie(json_encode([
            'hasError'    => !empty($this->errors),
            'errors'      => $this->errors,
            'indexStatus' => $this->getIndexationStatus(),
        ]));
    }
    /**
     * Ajax - delete all previous images
     *
     * @since 1.0.4
     */
    public function ajaxProcessResetImageStats() {

        $process = [

            ['type' => 'education', 'dir' => _EPH_PROD_IMG_DIR_]
        ];

        foreach ($process as $proc) {
            try {
                // Getting format generation
                Configuration::updateValue('EPH_IMAGES_LAST_UPD_' . strtoupper($proc['type']), 0);
            } catch (PhenyxShopException $e) {
                $this->errors[] = $e->getMessage();
            }

        }

        $this->ajaxDie(json_encode([
            'hasError'    => !empty($this->errors),
            'errors'      => $this->errors,
            'indexStatus' => $this->getIndexationStatus(),
        ]));
    }

   
    protected function _regenerateThumbnails($type = 'all', $deleteOldImages = false) {

        $this->start_time = time();
        ini_set('max_execution_time', $this->max_execution_time); // ini_set may be disabled, we need the real value
        $this->max_execution_time = (int) ini_get('max_execution_time');
        $languages = Language::getLanguages(false);

        $process = [

            ['type' => 'education', 'dir' => _EPH_PROD_IMG_DIR_]
        ];

        // Launching generation process

        foreach ($process as $proc) {

            if ($type != 'all' && $type != $proc['type']) {
                continue;
            }

            // Getting format generation
            $formats = ImageType::getImagesTypes($proc['type']);

            if ($type != 'all') {
                $format = strval(Tools::getValue('format_' . $type));

                if ($format != 'all') {

                    foreach ($formats as $k => $form) {

                        if ($form['id_image_type'] != $format) {
                            unset($formats[$k]);
                        }

                    }

                }

            }

            if ($deleteOldImages) {
                $this->_deleteOldImages($proc['dir'], $formats, ($proc['type'] == 'products' ? true : false));
            }

            if (($return = $this->_regenerateNewImages($proc['dir'], $formats, ($proc['type'] == 'products' ? true : false))) === true) {

                if (!count($this->errors)) {
                    $this->errors[] = sprintf(Tools::displayError('Cannot write images for this type: %s. Please check the %s folder\'s writing permissions.'), $proc['type'], $proc['dir']);
                }

            } else if ($return == 'timeout') {
                $this->errors[] = Tools::displayError('Only a part of the images have been regenerated. The server timed out before finishing.');
            }

            if ($proc['type'] == 'products') {

                if ($this->_regenerateWatermark($proc['dir'], $formats) == 'timeout') {
                    $this->errors[] = Tools::displayError('Server timed out. The watermark may not have been applied to all images.');
                }

            }

            if (!count($this->errors)) {

                if ($this->_regenerateNoPictureImages($proc['dir'], $formats, $languages)) {
                    $this->errors[] = sprintf(Tools::displayError('Cannot write "No picture" image to (%s) images folder. Please check the folder\'s writing permissions.'), $proc['type']);
                }

            }

        }

        return (count($this->errors) > 0 ? false : true);
    }
	
	public function ajaxProcessDeleteImageType() {

        $idImageType = Tools::getValue('idImageType');
        $imageType = new ImageType($idImageType);
        $type = $imageType->name;

        $result = $imageType->delete();

        if ($result) {
            ImageManager::deleteImageType($type);
        }

        $return = [
            'success' => true,
            'message' => $this->la('Le type d‘image a été supprimé avec succès'),
        ];

        die(Tools::jsonEncode($return));

    }

    public function ajaxProcessDeleteWebP() {

        ImageManager::deleteWebP();

        $return = [
            'success' => true,
            'message' => $this->la('Les images WepP ont supprimées avec succès'),
        ];

        die(Tools::jsonEncode($return));
    }


    protected function _deleteOldImages($dir, $type, $product = false) {

        if (!is_dir($dir)) {
            return;
        }

        // Faster delete on servers that support it

        if (function_exists('chdir') && function_exists('exec') && shell_exec('which find')) {
            exec('cd ' . escapeshellarg($dir) . ' && find . -name "*_default.jpg" -type f -delete');
            exec('cd ' . escapeshellarg($dir) . ' && find . -name "*_thumbs.jpg" -type f -delete');
            exec('cd ' . escapeshellarg($dir) . ' && find . -name "*2x.jpg" -type f -delete');
            exec('cd ' . escapeshellarg($dir) . ' && find . -name "*-watermark.jpg" -type f -delete');
            exec('cd ' . escapeshellarg($dir) . ' && find . -name "*.webp" -type f -delete');

            return;
        }

        $toDel = scandir($dir);

        foreach ($toDel as $d) {

            foreach ($type as $imageType) {

                if (preg_match('/^[0-9]+\-' . ($product ? '[0-9]+\-' : '') . $imageType['name'] . '\.(jpg|webp)$/', $d)
                    || (count($type) > 1 && preg_match('/^[0-9]+\-[_a-zA-Z0-9-]*\.(jpg|webp)$/', $d))
                    || preg_match('/^([[:lower:]]{2})\-default\-' . $imageType['name'] . '\.(jpg|webp)$/', $d)
                ) {

                    if (file_exists($dir . $d)) {
                        unlink($dir . $d);
                    }

                }

            }

        }

        // delete product images using new filesystem.

        if ($product) {
            $productsImages = Image::getAllImages();

            foreach ($productsImages as $image) {
                $imageObj = new Image($image['id_image']);
                $imageObj->id_product = $image['id_product'];

                if (file_exists($dir . $imageObj->getImgFolder())) {
                    $toDel = scandir($dir . $imageObj->getImgFolder());

                    foreach ($toDel as $d) {

                        foreach ($type as $imageType) {

                            if (preg_match('/^[0-9]+\-' . $imageType['name'] . '\.(jpg|webp)$/', $d) || (count($type) > 1 && preg_match('/^[0-9]+\-[_a-zA-Z0-9-]*\.(jpg|webp)$/', $d))) {

                                if (file_exists($dir . $imageObj->getImgFolder() . $d)) {
                                    unlink($dir . $imageObj->getImgFolder() . $d);
                                }

                            }

                        }

                    }

                }

            }

        }

    }

    protected function regenerateNewImage($entityType, $idEntity) {

        $process = [

            'education' => _EPH_PROD_IMG_DIR_
        ];
        $type = ImageType::getImagesTypes($entityType);

        $watermarkModules = Db::getInstance()->executeS(
            (new DbQuery())
                ->select('m.`name`')
                ->from('module', 'm')
                ->leftJoin('hook_module', 'hm', 'hm.`id_module` = m.`id_module`')
                ->leftJoin('hook', 'h', 'hm.`id_hook` = h.`id_hook`')
                ->where('h.`name` = \'actionWatermark\'')
                ->where('m.`active` = 1')
        );

        if ($entityType !== 'education') {

            foreach ($type as $k => $imageType) {
                // Customizable writing dir
                $dir = $newDir = $process[$entityType];
                $image = $idEntity . '.jpg';

                if ($imageType['name'] == 'thumb_scene') {
                    $newDir .= 'thumbs/';
                }

                if (!file_exists($newDir)) {
                    $this->errors[] = $this->la('Unable to generate new image');
                }

                $newFile = $newDir . substr($image, 0, -4) . '-' . stripslashes($imageType['name']) . '.jpg';

                if (file_exists($newFile) && !unlink($newFile)) {
                    $this->errors[] = $this->la('Unable to generate new image');
                }

                if (!file_exists($newFile)) {

                    if (!file_exists($dir . $image) || !filesize($dir . $image)) {
                        $this->errors[] = sprintf($this->la('Source file for type %s and ID %s does not exist', $entityType, $idEntity));
                    } else {
                        $success = ImageManager::resize(
                            $dir . $image,
                            $newFile,
                            (int) $imageType['width'],
                            (int) $imageType['height']
                        );

                        if (ImageManager::retinaSupport()) {

                            if (!ImageManager::resize(
                                $dir . $image,
                                $newDir . substr($image, 0, -4) . '-' . stripslashes($imageType['name']) . '2x.jpg',
                                (int) $imageType['width'] * 2,
                                (int) $imageType['height'] * 2
                            )) {
                                $this->errors[] = sprintf(Tools::displayError('Failed to resize image file to high resolution (%s)'), $dir . $image);
                            }

                        }

                        if (ImageManager::webpSupport()) {
                            $success &= ImageManager::resize(
                                $dir . $image,
                                $newDir . substr($image, 0, -4) . '-' . stripslashes($imageType['name']) . '.webp',
                                (int) $imageType['width'],
                                (int) $imageType['height'],
                                'webp'
                            );

                            if (ImageManager::retinaSupport()) {
                                $success &= ImageManager::resize(
                                    $dir . $image,
                                    $newDir . substr($image, 0, -4) . '-' . stripslashes($imageType['name']) . '2x.webp',
                                    (int) $imageType['width'] * 2,
                                    (int) $imageType['height'] * 2
                                );
                            }

                        }

                        if (!$success) {
                            $this->errors[] = $this->la('Unable to resize image');
                        }

                    }

                }

            }

        } else {
            $productsImages = array_column((array) Db::getInstance()->executeS(
                (new DbQuery())
                    ->select('`id_image`')
                    ->from('image')
                    ->where('`id_product` = ' . (int) $idEntity)
            ), 'id_image');

            foreach ($productsImages as $idImage) {
                $imageObj = new Image($idImage);
                $existingImage = $process[$entityType] . $imageObj->getExistingImgPath() . '.jpg';

                if (count($type) > 0) {

                    foreach ($type as $imageType) {
                        $newFile = $process[$entityType] . $imageObj->getExistingImgPath() . '-' . stripslashes($imageType['name']) . '.jpg';

                        if (file_exists($newFile) && !unlink($newFile)) {
                            $this->errors[] = $this->la('Unable to generate new file');
                        }

                        if (!file_exists($newFile)) {

                            if (!ImageManager::resize($existingImage, $newFile, (int) ($imageType['width']), (int) ($imageType['height']))) {
                                $this->errors[] = sprintf($this->la('Original image is corrupt (%s) or bad permission on folder'), $existingImage);
                            }

                            if (ImageManager::webpSupport()) {
                                ImageManager::resize(
                                    $existingImage,
                                    $process[$entityType] . $imageObj->getExistingImgPath() . '-' . stripslashes($imageType['name']) . '.webp',
                                    (int) $imageType['width'],
                                    (int) $imageType['height'],
                                    'webp'
                                );
                            }

                        }

                    }

                }

                if (is_array($watermarkModules) && count($watermarkModules)) {

                    if (file_exists($process[$entityType] . $imageObj->getExistingImgPath() . '.jpg')) {

                        foreach ($watermarkModules as $module) {
                            $moduleInstance = Module::getInstanceByName($module['name']);

                            if ($moduleInstance && is_callable([$moduleInstance, 'hookActionWatermark'])) {
                                call_user_func([$moduleInstance, 'hookActionWatermark'], [
                                    'id_image'   => $imageObj->id,
                                    'id_product' => $imageObj->id_product,
                                    'image_type' => $type,
                                ]);
                            }

                        }

                    }

                }

            }

        }

    }
    
    protected function getNextEntityId($entityType) {

        if ($entityType === 'categories') {
            $primary = 'id_category';
            $table = 'category';
        } else {
            $primary = 'id_' . rtrim($entityType, 's');
            $table = rtrim($entityType, 's');
        }

        $lastId = (int) Configuration::get('EPH_IMAGES_LAST_UPD_' . strtoupper($entityType));

        return (int) Db::getInstance(_EPH_USE_SQL_SLAVE_)->getValue(
            (new DbQuery())
                ->select('MIN(`' . bqSQL($primary) . '`)')
                ->from($table)
                ->where('`' . bqSQL($primary) . '` > ' . (int) $lastId)
        );
    }

   
    protected function _regenerateNewImages($dir, $type, $productsImages = false) {

        if (!is_dir($dir)) {
            return false;
        }

        $generateHighDpiImages = (bool) Configuration::get('EPH_HIGHT_DPI');

        if (!$productsImages) {
            $formattedThumbScene = ImageType::getFormatedName('thumb_scene');
            $formattedMedium = ImageType::getFormatedName('medium');

            foreach (scandir($dir) as $image) {

                if (preg_match('/^[0-9]*\.jpg$/', $image)) {

                    foreach ($type as $k => $imageType) {
                        // Customizable writing dir
                        $newDir = $dir;

                        if ($imageType['name'] == $formattedThumbScene) {
                            $newDir .= 'thumbs/';
                        }

                        if (!file_exists($newDir)) {
                            continue;
                        }

                        if (($dir == _EPH_CAT_IMG_DIR_) && ($imageType['name'] == $formattedMedium) && is_file(_EPH_CAT_IMG_DIR_ . str_replace('.', '_thumb.', $image))) {
                            $image = str_replace('.', '_thumb.', $image);
                        }

                        if (!file_exists($newDir . substr($image, 0, -4) . '-' . stripslashes($imageType['name']) . '.jpg')) {

                            if (!file_exists($dir . $image) || !filesize($dir . $image)) {
                                $this->errors[] = sprintf(Tools::displayError('Source file does not exist or is empty (%s)'), $dir . $image);
                            } else

                            if (!ImageManager::resize($dir . $image, $newDir . substr(str_replace('_thumb.', '.', $image), 0, -4) . '-' . stripslashes($imageType['name']) . '.jpg', (int) $imageType['width'], (int) $imageType['height'])) {
                                $this->errors[] = sprintf(Tools::displayError('Failed to resize image file (%s)'), $dir . $image);
                            }

                            if ($generateHighDpiImages) {

                                if (!ImageManager::resize($dir . $image, $newDir . substr($image, 0, -4) . '-' . stripslashes($imageType['name']) . '2x.jpg', (int) $imageType['width'] * 2, (int) $imageType['height'] * 2)) {
                                    $this->errors[] = sprintf(Tools::displayError('Failed to resize image file to high resolution (%s)'), $dir . $image);
                                }

                            }

                        }

                        // stop 4 seconds before the timeout, just enough time to process the end of the page on a slow server

                        if (time() - $this->start_time > $this->max_execution_time - 4) {
                            return 'timeout';
                        }

                    }

                }

            }

        } else {

            foreach (Image::getAllImages() as $image) {
                $imageObj = new Image($image['id_image']);
                $existingImg = $dir . $imageObj->getExistingImgPath() . '.jpg';

                if (file_exists($existingImg) && filesize($existingImg)) {

                    foreach ($type as $imageType) {

                        if (!file_exists($dir . $imageObj->getExistingImgPath() . '-' . stripslashes($imageType['name']) . '.jpg')) {

                            if (!ImageManager::resize($existingImg, $dir . $imageObj->getExistingImgPath() . '-' . stripslashes($imageType['name']) . '.jpg', (int) $imageType['width'], (int) $imageType['height'])) {
                                $this->errors[] = sprintf(Tools::displayError('Original image is corrupt (%s) for product ID %2$d or bad permission on folder'), $existingImg, (int) $imageObj->id_product);
                            }

                            if (ImageManager::retinaSupport()) {

                                if (!ImageManager::resize($existingImg, $dir . $imageObj->getExistingImgPath() . '-' . stripslashes($imageType['name']) . '2x.jpg', (int) $imageType['width'] * 2, (int) $imageType['height'] * 2)) {
                                    $this->errors[] = sprintf(Tools::displayError('Original image is corrupt (%s) for product ID %2$d or bad permission on folder'), $existingImg, (int) $imageObj->id_product);
                                }

                            }

                            if (!$this->errors && ImageManager::webpSupport()) {
                                $imgRes = imagecreatefromjpeg($dir . $imageObj->getExistingImgPath() . '-' . stripslashes($imageType['name']) . '.jpg');
                                ImageManager::resize(
                                    $imgRes,
                                    $dir . $imageObj->getExistingImgPath() . '-' . stripslashes($imageType['name']) . '.webp',
                                    (int) $imageType['width'] * 2,
                                    (int) $imageType['height'] * 2,
                                    'webp'
                                );
                            }

                        }

                    }

                } else {
                    $this->errors[] = sprintf(Tools::displayError('Original image is missing or empty (%1$s) for product ID %2$d'), $existingImg, (int) $imageObj->id_product);
                }

                if (time() - $this->start_time > $this->max_execution_time - 4) {
                    // stop 4 seconds before the tiemout, just enough time to process the end of the page on a slow server
                    return 'timeout';
                }

            }

        }

        return (bool) count($this->errors);
    }

    /**
     * Regenerate watermark
     *
     * @param string $dir
     * @param null   $type
     *
     * @return string
     *
     * @throws Adapter_Exception
     * @throws PrestaShopDatabaseException
     * @throws PhenyxShopException
     * @since 1.8.1.0
     */
    protected function _regenerateWatermark($dir, $type = null) {

        $result = Db::getInstance()->executeS(
            (new DbQuery())
                ->select('m.`name`')
                ->from('module', 'm')
                ->leftJoin('hook_module', 'hm', 'hm.`id_module` = m.`id_module`')
                ->leftJoin('hook', 'h', 'hm.`id_hook` = h.`id_hook`')
                ->where('h.`name` = \'actionWatermark\'')
                ->where('m.`active` = 1')
        );

        if ($result && count($result)) {
            $productsImages = Image::getAllImages();

            foreach ($productsImages as $image) {
                $imageObj = new Image($image['id_image']);

                if (file_exists($dir . $imageObj->getExistingImgPath() . '.jpg')) {

                    foreach ($result as $module) {
                        $moduleInstance = Module::getInstanceByName($module['name']);

                        if ($moduleInstance && is_callable([$moduleInstance, 'hookActionWatermark'])) {
                            call_user_func([$moduleInstance, 'hookActionWatermark'], ['id_image' => $imageObj->id, 'id_product' => $imageObj->id_product, 'image_type' => $type]);
                        }

                        if (time() - $this->start_time > $this->max_execution_time - 4) {
                            // stop 4 seconds before the tiemout, just enough time to process the end of the page on a slow server
                            return 'timeout';
                        }

                    }

                }

            }

        }

        return '';
    }

    /**
     * Regenerate no-pictures images
     *
     * @param string     $dir
     * @param string[][] $type
     * @param string[][] $languages
     *
     * @return bool
     *
     * @since 1.8.1.0
     * @throws PhenyxShopException
     */
    protected function _regenerateNoPictureImages($dir, $type, $languages) {

        $errors = false;

        foreach ($type as $imageType) {

            foreach ($languages as $language) {
                $file = $dir . $language['iso_code'] . '.jpg';

                if (!file_exists($file)) {
                    $file = _EPH_PROD_IMG_DIR_ . Language::getIsoById((int) Configuration::get('EPH_LANG_DEFAULT')) . '.jpg';
                }

                if (!file_exists($dir . $language['iso_code'] . '-default-' . stripslashes($imageType['name']) . '.jpg')) {

                    if (!ImageManager::resize($file, $dir . $language['iso_code'] . '-default-' . stripslashes($imageType['name']) . '.jpg', (int) $imageType['width'], (int) $imageType['height'])) {
                        $errors = true;
                    }

                    if (ImageManager::webpSupport()) {
                        ImageManager::resize(
                            $file,
                            $dir . $language['iso_code'] . '-default-' . stripslashes($imageType['name']) . '.webp',
                            (int) $imageType['width'],
                            (int) $imageType['height'],
                            'webp'
                        );
                    }

                    if (ImageManager::retinaSupport()) {

                        if (!ImageManager::resize($file, $dir . $language['iso_code'] . '-default-' . stripslashes($imageType['name']) . '2x.jpg', (int) $imageType['width'] * 2, (int) $imageType['height'] * 2)) {
                            $errors = true;
                        }

                        if (ImageManager::webpSupport()) {
                            ImageManager::resize(
                                $file,
                                $dir . $language['iso_code'] . '-default-' . stripslashes($imageType['name']) . '2x.webp',
                                (int) $imageType['width'],
                                (int) $imageType['height'],
                                'webp'
                            );
                        }

                    }

                }

            }

        }

        return $errors;
    }

    /**
     * Move product images to the new filesystem
     *
     * @return bool
     *
     * @since 1.8.1.0
     */
    protected function _moveImagesToNewFileSystem() {

        if (!Image::testFileSystem()) {
            $this->errors[] = Tools::displayError('Error: Your server configuration is not compatible with the new image system. No images were moved.');
        } else {
            ini_set('max_execution_time', $this->max_execution_time); // ini_set may be disabled, we need the real value
            $this->max_execution_time = (int) ini_get('max_execution_time');
            $result = Image::moveToNewFileSystem($this->max_execution_time);

            if ($result === 'timeout') {
                $this->errors[] = Tools::displayError('Not all images have been moved. The server timed out before finishing. Click on "Move images" again to resume the moving process.');
            } else

            if ($result === false) {
                $this->errors[] = Tools::displayError('Error: Some -- or all -- images cannot be moved.');
            }

        }

        return (count($this->errors) > 0 ? false : true);
    }

    
    public function initRegenerate() {

        $types = [

            'education' => $this->la('Formations'),
        ];

        $formats = [];

        foreach ($types as $i => $type) {
            $formats[$i] = ImageType::getImagesTypes($i);
        }

        $this->context->smarty->assign(
            [
                'types'   => $types,
                'formats' => $formats,
            ]
        );
    }
   
    public function initMoveImages() {

        $this->context->smarty->assign(
            [
                'link_ppreferences' => 'index.php?tab=AdminPPreferences&token=' . Tools::getAdminTokenLite('AdminPPreferences') . '#EPH_LEGACY_IMAGES_on',
            ]
        );
    }

    
    protected function _childValidation() {

        if (!Tools::getValue('id_image_type') && Validate::isImageTypeName($typeName = Tools::getValue('name')) && ImageType::typeAlreadyExists($typeName)) {
            $this->errors[] = Tools::displayError('This name already exists.');
        }

    }

    
    protected function getIndexationStatus() {

        try {
            return [
                'education' => [
                    'indexed' => (int) Db::getInstance(_EPH_USE_SQL_SLAVE_)->getValue(
                        (new DbQuery())
                            ->select('COUNT(*)')
                            ->from(bqSQL(Education::$definition['table']))
                            ->where('`' . bqSQL(Education::$definition['primary']) . '` <= ' . (int) Configuration::get('EPH_IMAGES_LAST_UPD_PRODUCTS'))
                    ),
                    'total'   => (int) Db::getInstance(_EPH_USE_SQL_SLAVE_)->getValue(
                        (new DbQuery())
                            ->select('COUNT(*)')
                            ->from(bqSQL(Education::$definition['table']))
                    ),
                ],

            ];
        } catch (Exception $e) {
            return false;
        }

    }
    public function ajaxProcessCleanProductFolder() {

        $images = ImageManager::cleanProductFolder();

        if ($images > 0) {
            $message = $images . ' ' . $this->la('unused images has been successfully removed.');
        } else

        if ($images == 0) {
            $message = $this->la('Product folder is clean, we did not found unused format type images.');
        }

        $this->ajaxDie([
            'success' => true,
            'message' => $images . ' ' . $this->la('has been successfully removed.'),
        ]);
    }

   
    public function ajaxProcessWebpRegenerate() {

        $image = Tools::getValue('image');
        $baseType = (string) Tools::getValue('type');
        $currentIndex = (int) Tools::getValue('currentIndex', 0);
		$file = fopen("testWebpRegenerate.txt","a");
		fwrite($file, $image .PHP_EOL);
		$result = Tools::resizeImg($image);
		fwrite($file, $result.PHP_EOL);
        if($result) {
			WebPGeneratorConfig::updateRegenerationProgress($baseType, $currentIndex);
        	$this->ajaxDie([
            	'success'       => true,
            	'error'         => null,
            	'current_index' => WebPGeneratorConfig::getRegenerationProgress($baseType),
        	]);
		}

        
    }


    /**
     * @throws PrestaShopDatabaseException
     */
    public function ajaxProcessDelete() {

        $baseType = (string) Tools::getValue('type');
        $type = ImageType::getImagesTypes(self::IMAGE_TYPE_SINGULAR[$baseType]);

        $image = Tools::getValue('image');

        try {

            if ($baseType !== 'product') {
                $result = ImageDeleteService::deleteOtherImage($image, $baseType, $type);
            } else {
                $result = ImageDeleteService::deleteProductImage($image);
            }

            if (!$result) {
                throw new RuntimeException("Can't resize image");
            }

        } catch (Exception $exception) {
            $this->ajaxDie(['success' => false, 'error' => $exception->getMessage()]);
        }

        $this->ajaxDie(['success' => true, 'error' => null]);
    }

   public function ajaxProcessUpdateAdminImages() {

        foreach ($_POST as $key => $value) {

            if ($key == 'action' || $key == 'ajax') {

                continue;
            }
			if($key == 'WEBP_CONVERTOR_TO_USE') {
				
				$value = implode(",", $value);
			}

            Configuration::updateValue($key, $value);
        }

        $result = [
            "success" => true,
            "message" => "Lindexation des images a été réalisé avec succès",
        ];

        die(Tools::jsonEncode($result));
    }
	
	public function ajaxProcessRegenerateDataFile() {
		
		ImageManager::getImages();
		


		$result = [
			"success" => true,
			"message" => "Lindexation des images a été réalisé avec succès"
		];
		
		die(Tools::jsonEncode($result));
	}

    public function ajaxProcessEditImageType() {

        $idImageType = Tools::getValue('idImageType');
        $this->identifier = 'id_image_type';
        $_GET['id_image_type'] = $idImageType;
        $_GET['updateimage_type'] = "";

        $obj = $this->loadObject();

        $html = PHP_EOL . $this->renderForm();
        $result = [
            'html' => $html,
        ];

        die(Tools::jsonEncode($result));
    }

    public function ajaxProcessUpdateImageType() {

        $idImageType = Tools::getValue('id_image_type');
        $imageType = new ImageType($idImageType);

        foreach ($_POST as $key => $value) {

            if (property_exists($imageType, $key) && $key != 'id_image_type') {
                $imageType->{$key}
                = $value;
            }

        }

        $result = $imageType->update();

        $return = [
            'success' => true,
            'message' => $this->la('La mise à jour a été effectuée avec succès'),
        ];

        die(Tools::jsonEncode($return));
    }

    public function ajaxProcessAddImageType() {

        $imageType = new ImageType();

        foreach ($_POST as $key => $value) {

            if (property_exists($imageType, $key) && $key != 'id_image_type') {
                $imageType->{$key}
                = $value;
            }

        }

        $result = $imageType->add();
        $return = [
            'success' => true,
            'message' => $this->la('Le type  avec succès'),
        ];

        die(Tools::jsonEncode($return));
    }

    public function renderForm() {

        $obj = $this->loadObject();

        $this->fields_form = [
            'legend' => [
                'title' => $this->la('Image type'),
                'icon'  => 'icon-picture',
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
                    'label'    => $this->la('Name for the image type'),
                    'name'     => 'name',
                    'required' => true,
                    'hint'     => $this->la('Letters, underscores and hyphens only (e.g. "small_custom", "cart_medium", "large", "thickbox_extra-large").'),
                ],
                [
                    'type'      => 'text',
                    'label'     => $this->la('Width'),
                    'name'      => 'width',
                    'required'  => true,
                    'maxlength' => 5,
                    'suffix'    => $this->la('pixels'),
                    'hint'      => $this->la('Maximum image width in pixels.'),
                ],
                [
                    'type'      => 'text',
                    'label'     => $this->la('Height'),
                    'name'      => 'height',
                    'required'  => true,
                    'maxlength' => 5,
                    'suffix'    => $this->la('pixels'),
                    'hint'      => $this->la('Maximum image height in pixels.'),
                ],
                [
                    'type'     => 'switch',
                    'label'    => $this->la('Formations'),
                    'name'     => 'esucation',
                    'required' => false,
                    'is_bool'  => true,
                    'hint'     => $this->la('This type will be used for Product images.'),
                    'values'   => [
                        [
                            'id'    => 'esucation_on',
                            'value' => 1,
                            'label' => $this->la('Enabled'),
                        ],
                        [
                            'id'    => 'esucation_off',
                            'value' => 0,
                            'label' => $this->la('Disabled'),
                        ],
                    ],
                ],
                [
                    'type'     => 'switch',
                    'label'    => $this->la('Utilisateurs'),
                    'name'     => 'users',
                    'required' => false,
                    'class'    => 't',
                    'is_bool'  => true,
                    'hint'     => $this->la('This type will be used for Category images.'),
                    'values'   => [
                        [
                            'id'    => 'users_on',
                            'value' => 1,
                            'label' => $this->la('Enabled'),
                        ],
                        [
                            'id'    => 'users_off',
                            'value' => 0,
                            'label' => $this->la('Disabled'),
                        ],
                    ],
                ],

            ],
            'submit' => [
                'title' => $this->la('Save'),
            ],
        ];

        $this->fields_value['ajax'] = 1;

        if ($obj->id > 0) {
            $this->fields_value['action'] = 'updateImageType';
        } else {
            $this->fields_value['action'] = 'addImageType';
        }

        return parent::renderForm();
    }

    public function ajaxDie($value = null, $controller = null, $method = null, $statusCode = 200) {

        header('Content-Type: application/json');

        if (!is_scalar($value)) {
            $value = json_encode($value);
        }

        http_response_code($statusCode);
        parent::ajaxDie($value, $controller, $method);
    }

    protected function isCwebpCompatible() {

        return function_exists('exec');
    }

    protected function isImagickCompatible() {

        try {

            if (!class_exists('Imagick')) {
                return false;
            }

            /**
             * Check if the Imagick::queryFormats method exists
             */

            if (!method_exists(\Imagick::class, 'queryFormats')) {
                return false;
            }

            return in_array('WEBP', \Imagick::queryFormats(), false);
        } catch (Exception $exception) {
            return false;
        }

    }

    protected function isGmagickCompatible() {

        try {

            if (!extension_loaded('Gmagick')) {
                // Required Gmagick extension is not available.
                return false;
            }

            if (!class_exists('Gmagick')) {
                // 'Gmagick is installed, but not correctly. The class Gmagick is not available'
                return false;
            }

            $gmagick = new Gmagick();

            if (!in_array('WEBP', $gmagick->queryformats(), false)) {
                // 'Gmagick was compiled without WebP support.'
                return false;
            }

        } catch (GmagickException $e) {
            return false;
        }

        return true;
    }

    protected function isGdCompatible() {

        if (!extension_loaded('gd')) {
            // Required Gd extension is not available
            return false;
        }

        if (!function_exists('imagewebp')) {
            // Required imagewebp() function is not available. It seems Gd has been compiled without webp support
            return false;
        }

        if (!function_exists('imagecreatefrompng')) {
            // Required imagecreatefrompng() function is not available
            return false;
        }

        if (!function_exists('imagecreatefromjpeg')) {
            // Required imagecreatefromjpeg() function is not available
            return false;
        }

        return true;
    }

    protected function isEwwwCompatible() {

        if (!extension_loaded('curl')) {
            // Required cURL extension is not available
            return false;
        }

        if (!function_exists('curl_init')) {
            // Required url_init() function is not available
            return false;
        }

        if (!function_exists('curl_file_create')) {
            // Required curl_file_create() function is not available (requires PHP > 5.5).
            return false;
        }

        return true;

    }

}
