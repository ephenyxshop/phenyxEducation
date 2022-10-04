<?php

header("Access-control-allow-origin: *");

/**
 * Class AdminDashboardControllerCore
 *
 * @since 1.9.1.0
 */
class AdminDashboardControllerCore extends AdminController {

	
	public $php_self = 'admindashboard';
	private $gradient_separator = '-';

	

	public $rebuildable_type = [
		3,
		4,
		5,
		10,
	];

	public $currentExerciceStart;

	public $currentExerciceEnd;
	/**
	 * AdminDashboardControllerCore constructor.
	 *
	 * @since 1.9.1.0
	 */
	public function __construct() {

		$this->bootstrap = true;

		parent::__construct();

		$this->currentExerciceStart = Configuration::get('EPH_N_ACCOUNT_START');

		$this->currentExerciceEnd = Configuration::get('EPH_N_ACCOUNT_END');
	}

	/**
	 * @since 1.9.1.0
	 */
	public function setMedia() {

		parent::setMedia();

		$this->addCSS(_EPH_ADMIN_THEME_DIR_. $this->bo_theme . '/css/dashboard.css');

		$this->addJS(
			[

				_EPH_JS_DIR_.'nav.js',
				'https://cdn.ephenyxapi.com/education/dashactivity.js',
            	_EPH_JS_DIR_.'datejs/date.min.js',
				_EPH_JS_DIR_.'tools.js',
				_EPH_JS_DIR_.'dashtrends.js',
				'https://cdn.ephenyxapi.com/vendor/d3.v7.min.js',
				_EPH_JS_DIR_.'jquery.datetimepicker.full.js',
				_EPH_JS_DIR_.'colorpicker/colorpicker.js',
				_EPH_JS_DIR_.'dashboard.js',
				_EPH_JS_DIR_.'tabs.js',
			]
		);
		 $this->addCSS(
            [
               'https://cdn.ephenyxapi.com/vendor/nv.d3.css',
               _EPH_ADMIN_THEME_DIR_.$this->bo_theme . '/css/colorpicker/colorpicker.css',
               _EPH_ADMIN_THEME_DIR_.$this->bo_theme . '/css/jquery.datetimepicker.css',
				_EPH_ADMIN_THEME_DIR_.$this->bo_theme . '/css/graph.css',
            ]
        );
		
		Media::addJsDef([
			'AjaxLinkAdminDashboard' => $this->context->link->getAdminLink('AdminDashboard'),
			'imageLink'              => $this->context->link->getEmployeeImageLink(),
			'read_more'              => '',
			'currentProfileId'       => $this->context->employee->id_profile,
		]);

	}

	public function initContent() {

		$newCompany = false;
		$initCompany = null;
		$this->idCompany = Configuration::get('EPH_COMPANY_ID');

		if ($this->idCompany == 0) {
			$newCompany = true;

			$company = new Company();
			$countries = Country::getCountries($this->context->language->id, true);
			$data = $this->createTemplate('paramSociety.tpl');
			$data->assign('company', $company);
			$data->assign('countries', $countries);
			$initCompany = $data->fetch();

		}

		$testStatsDateUpdate = $this->context->cookie->__get('stats_date_update');

		if (!empty($testStatsDateUpdate) && $this->context->cookie->__get('stats_date_update') < strtotime(date('Y-m-d'))) {

			switch ($this->context->employee->preselect_date_range) {
			case 'day':
				$dateFrom = date('Y-m-d');
				$dateTo = date('Y-m-d');
				break;
			case 'prev-day':
				$dateFrom = date('Y-m-d', strtotime('-1 day'));
				$dateTo = date('Y-m-d', strtotime('-1 day'));
				break;
			case 'month':
			default:
				$dateFrom = date('Y-m-01');
				$dateTo = date('Y-m-d');
				break;
			case 'prev-month':
				$dateFrom = date('Y-m-01', strtotime('-1 month'));
				$dateTo = date('Y-m-t', strtotime('-1 month'));
				break;
			case 'year':
				$dateFrom = date('Y-01-01');
				$dateTo = date('Y-m-d');
				break;
			case 'prev-year':
				$dateFrom = date('Y-m-01', strtotime('-1 year'));
				$dateTo = date('Y-12-t', strtotime('-1 year'));
				break;
			case 'exercice':
				$dateFrom = $this->currentExerciceStart;
				$dateTo = $this->currentExerciceEnd;
				break;
			case 'prev-exercice':
				$dateFrom = Configuration::get('EPH_N-1_ACCOUNT_START');
				$dateTo = Configuration::get('EPH_N-1_ACCOUNT_END');
				break;
			}

			$this->context->employee->stats_date_from = $dateFrom;
			$this->context->employee->stats_date_to = $dateTo;
			$this->context->employee->update();
			$this->context->cookie->__set('stats_date_update', strtotime(date('Y-m-d')));
			$this->context->cookie->write();
		}

		$calendarHelper = new HelperCalendar();

		$calendarHelper->setDateFrom(Tools::getValue('date_from', $this->context->employee->stats_date_from));
		$calendarHelper->setDateTo(Tools::getValue('date_to', $this->context->employee->stats_date_to));

		$statsCompareFrom = $this->context->employee->stats_compare_from;
		$statsCompareTo = $this->context->employee->stats_compare_to;

		if (is_null($statsCompareFrom) || $statsCompareFrom == '0000-00-00') {
			$statsCompareFrom = null;
		}

		if (is_null($statsCompareTo) || $statsCompareTo == '0000-00-00') {
			$statsCompareTo = null;
		}

		$calendarHelper->setCompareDateFrom($statsCompareFrom);
		$calendarHelper->setCompareDateTo($statsCompareTo);
		$calendarHelper->setCompareOption(Tools::getValue('compare_date_option', $this->context->employee->stats_compare_option));

		$saleAgents = SaleAgent::getSaleAgents();

		$params = [
			'date_from' => $this->context->employee->stats_date_from,
			'date_to'   => $this->context->employee->stats_date_to,
		];
		$licence = Configuration::get('EPH_LICENCE_SHOP');

		$this->context->smarty->assign([
			'date_from'               => $this->context->employee->stats_date_from,
			'date_to'                 => $this->context->employee->stats_date_to,
			'hookDashboardZoneOne'    => Hook::exec('dashboardZoneOne', $params),
			'hookDashboardZoneTwo'    => Hook::exec('dashboardZoneTwo', $params),
			'action'                  => '#',
			'saleAgents'              => $saleAgents,
			'new_version_url'         => isset($phenyxCurl['tpl']) ? $phenyxCurl['tpl'] : '',
			'dashboard_use_push'      => Configuration::get('EPH_DASHBOARD_USE_PUSH'),
			'calendar'                => $calendarHelper->generate(),
			'EPH_DASHBOARD_SIMULATION' => Configuration::get('EPH_DASHBOARD_SIMULATION'),
			'datepickerFrom'          => Tools::getValue('datepickerFrom', $this->context->employee->stats_date_from),
			'datepickerTo'            => Tools::getValue('datepickerTo', $this->context->employee->stats_date_to),
			'preselect_date_range'    => Tools::getValue('preselectDateRange', $this->context->employee->preselect_date_range),
			'newCompany'              => $newCompany,
			'initCompany'             => $initCompany,
			'link'                    => $this->context->link,
			'domaine'                 => Configuration::get('EPH_SHOP_DOMAIN'),
		    'scoringHot'              => StudentEvaluation::getHotScoring(),
            'employee'                => $this->context->employee
		]);

		return parent::initContent();
	}
	
	public function ajaxProcessRefreshDashboard() {

        $idModule = null;

        if ($module = Tools::getValue('module')) {
            $moduleObj = Module::getInstanceByName($module);

            if (Validate::isLoadedObject($moduleObj)) {
                $idModule = $moduleObj->id;
            }

        }

        $params = [
            'date_from'          => $this->context->employee->stats_date_from,
            'date_to'            => $this->context->employee->stats_date_to,
            'compare_from'       => $this->context->employee->stats_compare_from,
            'compare_to'         => $this->context->employee->stats_compare_to,
            'dashboard_use_push' => (int) Tools::getValue('dashboard_use_push'),
            'extra'              => (int) Tools::getValue('extra'),
        ];

        $this->ajaxDie(json_encode(Hook::exec('dashboardData', $params, $idModule, true, true, (int) Tools::getValue('dashboard_use_push'))));
    }
	
	 public function ajaxProcessSetMainboardDateRange() {

        $value = Tools::getValue('item');

        switch ($value) {
        case 'submitDateDay':
            $from = date('Y-m-d');
            $to = date('Y-m-d');
            $this->context->employee->preselect_date_range = 'day';
            break;
        case 'submitDateDayPrev':
            $yesterday = time() - 60 * 60 * 24;
            $from = date('Y-m-d', $yesterday);
            $to = date('Y-m-d', $yesterday);
            $this->context->employee->preselect_date_range = 'prev-day';
            break;
        case 'submitDateMonth':
            $from = date('Y-m-01');
            $to = date('Y-m-t');
            $this->context->employee->preselect_date_range = 'month';
            break;
        case 'submitDateMonthPrev':
            $m = (date('m') == 1 ? 12 : date('m') - 1);
            $y = ($m == 12 ? date('Y') - 1 : date('Y'));
            $from = $y . '-' . $m . '-01';
            $to = $y . '-' . $m . date('-t', mktime(12, 0, 0, $m, 15, $y));
            $this->context->employee->preselect_date_range = 'prev-month';
            break;
        case 'submitDateYear':
            $from = date('Y-01-01');
            $to = date('Y-12-31');
            $this->context->employee->preselect_date_range = 'year';
            break;
        case 'submitDateYearPrev':
            $from = (date('Y') - 1) . date('-01-01');
            $to = (date('Y') - 1) . date('-12-31');
            $this->context->employee->preselect_date_range = 'prev-year';
            break;
        case 'submitDateExercice':
            $from = Configuration::get('EPH_N_ACCOUNT_START');
            $from = str_replace('/', '-', $from);
            $from = date('Y-m-d', strtotime($from));
            $to = Configuration::get('EPH_N_ACCOUNT_END');
            $to = str_replace('/', '-', $to);
            $to = date('Y-m-d', strtotime($to));
            $this->context->employee->preselect_date_range = 'exercice';
            break;
        case 'submitDateExercicePrev':
            $from = Configuration::get('EPH_N-1_ACCOUNT_START');
            $from = Configuration::get('EPH_N_ACCOUNT_START');
            $from = str_replace('/', '-', $from);
            $from = date('Y-m-d', strtotime($from));
            $to = Configuration::get('EPH_N-1_ACCOUNT_END');
            $to = str_replace('/', '-', $to);
            $to = date('Y-m-d', strtotime($to));
            $this->context->employee->preselect_date_range = 'prev-exercice';
            break;
        }

        if (isset($from) && isset($to) && !count($this->errors)) {

            $this->context->employee->stats_date_from = $from;
            $this->context->employee->stats_date_to = $to;
            $this->context->employee->update();
            $return = [
                'from' => $from,
                'to'   => $to,
            ];
            die(Tools::jsonEncode($return));
        }

    }
	
	public function ajaxProcessSaveDashConfig() {

        $return = ['has_errors' => false, 'errors' => []];
        $module = Tools::getValue('module');
        $hook = Tools::getValue('hook');
        $configs = Tools::getValue('configs');

        $params = [
            'date_from' => $this->context->employee->stats_date_from,
            'date_to'   => $this->context->employee->stats_date_to,
        ];

        if (Validate::isModuleName($module) && $moduleObj = Module::getInstanceByName($module)) {

            if (Validate::isLoadedObject($moduleObj) && method_exists($moduleObj, 'validateDashConfig')) {
                $return['errors'] = $moduleObj->validateDashConfig($configs);
            }

            if (!count($return['errors'])) {

                if (Validate::isLoadedObject($moduleObj) && method_exists($moduleObj, 'saveDashConfig')) {
                    $return['has_errors'] = $moduleObj->saveDashConfig($configs);
                } else if (is_array($configs) && count($configs)) {

                    foreach ($configs as $name => $value) {

                        if (Validate::isConfigName($name)) {
                            Configuration::updateValue($name, $value);
                        }

                    }

                }

            } else {
                $return['has_errors'] = true;
            }

        }

        if (Validate::isHookName($hook) && method_exists($moduleObj, $hook)) {
            $return['widget_html'] = $moduleObj->$hook($params);
        }

        $this->ajaxDie(json_encode($return));
    }
	
	public function ajaxProcessSetSimulationMode() {

        Configuration::updateValue('EPH_DASHBOARD_SIMULATION', (int) Tools::getValue('EPH_DASHBOARD_SIMULATION'));
        $this->ajaxDie('k' . Configuration::get('EPH_DASHBOARD_SIMULATION') . 'k');
    }
	
	public function ajaxProcessGetBlogRss() {

        $return = ['has_errors' => false, 'rss' => []];

        if (!$this->isFresh('/config/xml/blog-' . $this->context->language->iso_code . '.xml', 86400)) {

            if (!$this->refresh('/config/xml/blog-' . $this->context->language->iso_code . '.xml', 'https://ephenyx.com/feed/')) {
                $return['has_errors'] = true;
            }

        }

        if (!$return['has_errors']) {
            $rss = @simplexml_load_file(_SHOP_ROOT_DIR_ . '/config/xml/blog-' . $this->context->language->iso_code . '.xml');

            if (!$rss) {
                $return['has_errors'] = true;
            }

            $articlesLimit = 2;

            if ($rss) {

                foreach ($rss->channel->item as $item) {

                    if ($articlesLimit > 0 && Validate::isCleanHtml((string) $item->title) && Validate::isCleanHtml((string) $item->description)
                        && isset($item->link) && isset($item->title)
                    ) {

                        if (in_array($this->context->mode, [Context::MODE_HOST, Context::MODE_HOST_CONTRIB])) {
                            $utmContent = 'cloud';
                        } else {
                            $utmContent = 'download';
                        }

                        $shopDefaultCountryId = (int) Configuration::get('EPH_COUNTRY_DEFAULT');
                        $shopDefaultIsoCountry = (string) mb_strtoupper(Country::getIsoById($shopDefaultCountryId));
                        $analyticsParams = [
                            'utm_source'   => 'back-office',
                            'utm_medium'   => 'rss',
                            'utm_campaign' => 'back-office-' . $shopDefaultIsoCountry,
                            'utm_content'  => $utmContent,
                        ];
                        $urlQuery = parse_url($item->link, PHP_URL_QUERY);
                        parse_str($urlQuery, $linkQueryParams);

                        if ($linkQueryParams) {
                            $fullUrlParams = array_merge($linkQueryParams, $analyticsParams);
                            $baseUrl = explode('?', (string) $item->link);
                            $baseUrl = (string) $baseUrl[0];
                            $articleLink = $baseUrl . '?' . http_build_query($fullUrlParams);
                        } else {
                            $articleLink = (string) $item->link . '?' . http_build_query($analyticsParams);
                        }

                        $return['rss'][] = [
                            'date'       => Tools::displayDate(date('Y-m-d', strtotime((string) $item->pubDate))),
                            'title'      => (string) Tools::htmlentitiesUTF8($item->title),
                            'short_desc' => Tools::truncateString(strip_tags((string) $item->description), 150),
                            'link'       => (string) $articleLink,
                        ];
                    } else {
                        break;
                    }

                    $articlesLimit--;
                }

            }

        }

        $this->ajaxDie(json_encode($return));
    }

	public function ajaxProcessOpenSociety() {

		$idCompany = Configuration::get('EPH_COMPANY_ID');

		if (!($company = new Company($idCompany))) {
			return '';
		}
        
		$extracss = $this->pushCSS([
            '/js/trumbowyg/ui/trumbowyg.min.css',
			'/js/jquery-ui/general.min.css',

        ]);
        
		$pusjJs = $this->pushJS([
			'/js/society.js',
			'/js/trumbowyg/trumbowyg.min.js',			
            '/js/jquery-jeditable/jquery.jeditable.min.js',
            '/js/jquery-ui/jquery-ui-timepicker-addon.min.js',
			'/js/moment/moment.min.js',
            '/js/moment/moment-timezone-with-data.min.js',
			'/js/calendar/working_plan_exceptions_modal.min.js',
            '/js/datejs/date.min.js'
        ]);

		$country = Country::getCountries($this->context->language->id, true);
		$data = $this->createTemplate('society.tpl');

		$data->assign('company', $company);
		$data->assign('countries', $country);
		$data->assign([
		    'EALang'             => Tools::jsonEncode($this->getEaLang()),
			'pusjJs'             => $pusjJs,
			'extracss'           => $extracss,
			'workin_plan'		 => Tools::jsonEncode($company->working_plan)           
        ]);

		$li = '<li id="uperEditSociety" data-controller="' . $this->controller_name . '" data-self="ma-societe" data-name="Ma société"><a href="#contentEditSociety">Identité de l‘entreprise</a><button type="button" class="close tabdetail" data-id="uperEditSociety"><i class="icon icon-times-circle"></i></button></li>';
		$html = '<div id="contentEditSociety" class="panel col-lg-12">' . $data->fetch() . '</div>';

		$result = [
			'li'   => $li,
			'html' => $html,
		];

		die(Tools::jsonEncode($result));
	}

	public function ajaxProcessUpdateCompany() {

		$idCompany = Tools::getValue('id_company');

		if ($idCompany > 0) {
			$company = new Company($idCompany);
		} else {
			$company = new Company();
		}

		foreach ($_POST as $key => $value) {

			if (property_exists($company, $key) && $key != 'id_company') {

				$company->{$key}
				= $value;

			}

		}

		if ($idCompany > 0) {
			$result = $company->update();
			$return = [
				'success' => true,
				'message' => 'Votre société a été mis à jour avec succès',
			];
		} else {
			$result = $company->add();
			$return = [
				'success' => true,
				'message' => 'Votre société vient d‘être créer avec succès',
			];
		}

		Configuration::updateValue('EPH_SHOP_EMAIL', $company->company_email);
		Configuration::updateValue('EPH_SHOP_ADMIN_EMAIL', $company->administratif_email);
		Configuration::updateValue('EPH_SHOP_NAME', $company->company_name);
		Configuration::updateValue('EPH_SHOP_URL', $company->company_url);

		die(Tools::jsonEncode($return));

	}

	public function ajaxProcessOpenBookingParam() {

		$idCompany = Configuration::get('EPH_COMPANY_ID');

		if (!($company = new Company($idCompany))) {
			return '';
		}

		$data = $this->createTemplate('controllers/booking.tpl');

		$data->assign('company', $company);
		$data->assign('EPH_FIRST_ACCOUNT_START', Configuration::get('EPH_FIRST_ACCOUNT_START'));
		$data->assign('EPH_FIRST_ACCOUNT_END', Configuration::get('EPH_FIRST_ACCOUNT_END'));
		$data->assign('EPH_N_ACCOUNT_START', Configuration::get('EPH_N_ACCOUNT_START'));
		$data->assign('EPH_N_ACCOUNT_END', Configuration::get('EPH_N_ACCOUNT_END'));
		$data->assign('EPH_N1_ACCOUNT_END', Configuration::get('EPH_N1_ACCOUNT_END'));
		$data->assign('EPH_POST_ACCOUNT_END', Configuration::get('EPH_POST_ACCOUNT_END'));

		$data->assign('EPH_STUDENT_AFFECTATION', Configuration::get('EPH_STUDENT_AFFECTATION'));
		$data->assign('EPH_STUDENT_AFFECTATION_1_TYPE', Configuration::get('EPH_STUDENT_AFFECTATION_1_TYPE'));
		$data->assign('EPH_STUDENT_COMMON_ACCOUNT', Configuration::get('EPH_STUDENT_COMMON_ACCOUNT'));
		$data->assign('EPH_STUDENT_COMMON_ACCOUNT_VALUE', StdAccount::getAccountValueById(Configuration::get('EPH_STUDENT_COMMON_ACCOUNT')));

		$data->assign('EPH_SUPPLIER_AFFECTATION', Configuration::get('EPH_SUPPLIER_AFFECTATION'));
		$data->assign('EPH_SUPPLIER_AFFECTATION_1_TYPE', Configuration::get('EPH_STUDENT_AFFECTATION_1_TYPE'));
		$data->assign('EPH_SUPPLIER_COMMON_ACCOUNT', Configuration::get('EPH_SUPPLIER_COMMON_ACCOUNT'));
		$data->assign('EPH_SUPPLIER_COMMON_ACCOUNT_VALUE', StdAccount::getAccountValueById(Configuration::get('EPH_SUPPLIER_COMMON_ACCOUNT')));

		$data->assign('EPH_PROFIT_DEFAULT_ACCOUNT', Configuration::get('EPH_PROFIT_DEFAULT_ACCOUNT'));
		$data->assign('EPH_STUDENT_DEFAULT_ACCOUNT', Configuration::get('EPH_STUDENT_DEFAULT_ACCOUNT'));
		$data->assign('EPH_SUPPLIER_DEFAULT_ACCOUNT', Configuration::get('EPH_SUPPLIER_DEFAULT_ACCOUNT'));
		$data->assign('EPH_PURCHASE_DEFAULT_ACCOUNT', Configuration::get('EPH_PURCHASE_DEFAULT_ACCOUNT'));
		$data->assign('EPH_COLLECTED_VAT_DEFAULT_ACCOUNT', StdAccount::getAccountValueById(Configuration::get('EPH_COLLECTED_VAT_DEFAULT_ACCOUNT')));
		$data->assign('EPH_DEDUCTIBLE_VAT_DEFAULT_ACCOUNT', StdAccount::getAccountValueById(Configuration::get('EPH_DEDUCTIBLE_VAT_DEFAULT_ACCOUNT')));

		$data->assign('EPH_PROFIT_DEFAULT_ACCOUNT_VALUE', StdAccount::getAccountValueById(Configuration::get('EPH_PROFIT_DEFAULT_ACCOUNT')));
		$data->assign('EPH_STUDENT_DEFAULT_ACCOUNT_VALUE', StdAccount::getAccountValueById(Configuration::get('EPH_STUDENT_DEFAULT_ACCOUNT')));
		$data->assign('EPH_SUPPLIER_DEFAULT_ACCOUNT_VALUE', StdAccount::getAccountValueById(Configuration::get('EPH_SUPPLIER_DEFAULT_ACCOUNT')));
		$data->assign('EPH_PURCHASE_DEFAULT_ACCOUNT_VALUE', StdAccount::getAccountValueById(Configuration::get('EPH_PURCHASE_DEFAULT_ACCOUNT')));
		$data->assign('EPH_COLLECTED_VAT_DEFAULT_ACCOUNT_VALUE', StdAccount::getAccountValueById(Configuration::get('EPH_COLLECTED_VAT_DEFAULT_ACCOUNT')));
		$data->assign('EPH_DEDUCTIBLE_VAT_DEFAULT_ACCOUNT_VALUE', StdAccount::getAccountValueById(Configuration::get('EPH_DEDUCTIBLE_VAT_DEFAULT_ACCOUNT')));
		$data->assign([

			'link'       => $this->context->link,
			'controller' => 'AdminCompany',
		]);

		$li = '<li id="uperBookingParam" data-controller="' . $this->controller_name . '" data-self="parametre-comptable" data-name="Paramètre Comptable"><a href="#contentBookingParam">Paramètre comptable</a><button type="button" class="close tabdetail" data-id="uperBookingParam"><i class="icon icon-times-circle"></i></button></li>';
		$html = '<div id="contentBookingParam" class="panel col-lg-12" style="display; flow-root;">' . $data->fetch() . '</div>';


		$result = [
			'li'   => $li,
			'html' => $html,
		];

		die(Tools::jsonEncode($result));
	}

	public function ajaxProcessOpenSaisieK() {

		$controller = new AdminNewBookRecordsController();
		$this->paragridScript = $controller->generateParaGridScript();

		$data = $this->createTemplate('controllers/saisiek.tpl');
		$today = date("Y-m-d");
		$diaries = BookDiary::getBookDiary();
		$data->assign([
			'paragridScript' => $this->paragridScript,
			'diaries'        => $diaries,
			'today'          => $today,
		]);

		$li = '<li id="uperSaisieK" data-controller="' . $this->controller_name . '" data-self="saisie-kilometre" data-name="Ligne comptable"><a href="#contentSaisieK">Paramètre comptable</a><button type="button" class="close tabdetail" data-id="uperSaisieK"><i class="icon icon-times-circle"></i></button></li>';
		$html = '<div id="contentSaisieK" class="panel col-lg-12" style="display; flow-root;">' . $data->fetch() . '</div>';

		$result = [
			'li'   => $li,
			'html' => $html,
		];

		die(Tools::jsonEncode($result));
	}

	public function ajaxProcessUploadProfilPicture() {

		$id_employee = $this->context->employee->id;
		$dir = _EPH_EMPLOYEE_IMG_DIR_;
		$name = 'PicProfil';
		$type == 'profil';

		if ($croped_image = Tools::getValue($name)) {
			list($type, $croped_image) = explode(';', $croped_image);
			list(, $croped_image) = explode(',', $croped_image);
			$croped_image = base64_decode($croped_image);
			$uploadfile = $dir . basename($this->context->employee->id . '.jpg');
			file_put_contents($uploadfile, $croped_image);
			ImageManager::resize($uploadfile, $uploadfile);
			die($this->context->employee->id);
		}

	}

	public function ajaxProcessGetAdminLink() {

		$context = Context::getContext();
		$controller = Tools::getValue('target');
		$controller_name = 'Admin' . $controller;
		$controllerLink = $context->link->getAdminLink($controller_name);

		$result = [
			'link' => $controllerLink,
		];

		die(Tools::jsonEncode($result));
	}

	public function ajaxProcessEraseCache() {

		Tools::clearSmartyCache();
		Tools::cleanFrontCache();
        Tools::cleanModuleDataBase();
		Tools::cleanThemeDirectory();        
		Tools::generateIndex();
		PageCache::flush();

		if (function_exists('opcache_reset')) {
			opcache_reset();
		}

		$result = [
			'success' => true,
			'message' => 'Le cache a été vidé avec succès',
		];

		die(Tools::jsonEncode($result));
	}

	public function ajaxProcessGetFormatPackSuppliesRequest() {

		$supplies = Db::getInstance()->executeS(
			(new DbQuery())
				->select('*')
				->from('formatpack_supplies')
		);

		die(Tools::jsonEncode($supplies));
	}

	public function ajaxProcessGetFormatPackSuppliesFields() {

		$fields = [

			[
				'title'    => $this->la('ID'),
				'maxWidth' => 70,
				'dataIndx' => 'id_formatpack_supplies',
				'dataType' => 'integer',
				'editable' => false,
				'align'    => 'center',
				'valign'   => 'center',
				'filter'   => [
					'crules' => [['condition' => "begin"]],
				],
			],

			[
				'title'    => $this->la('Nom du matériel'),
				'maxWidth' => 200,
				'dataIndx' => 'name',
				'editable' => false,
				'align'    => 'left',
				'valign'   => 'center',
				'dataType' => 'string',
				'filter'   => [
					'crules' => [['condition' => "contain"]],
				],
			],
			[
				'title'    => $this->la('Quantité Facturées'),
				'maxWidth' => 150,
				'dataIndx' => 'sold',
				'align'    => 'right',
				'valign'   => 'center',
				'dataType' => 'integer',
			],

			[
				'title'    => $this->la('Prix HA'),
				'maxWidth' => 150,
				'dataIndx' => 'pamp',
				'align'    => 'right',
				'valign'   => 'center',
				'dataType' => 'float',
				'format'   => '# ##0,00 € ' . $this->la('HT.'),
			],

			[
				'title'    => $this->la('Stock'),
				'dataIndx' => 'stock',
				'dataType' => 'integer',
				'editable' => false,

			],
			[
				'title'    => $this->la('Stock Prévisionnel'),
				'dataIndx' => 'stock_previsionnel',
				'dataType' => 'integer',
				'editable' => false,

			],

		];

		die(Tools::jsonEncode($fields));

	}

	public function ajaxProcessUpdateStock() {

		if (CustomerPieces::getPhenyxSupplies()) {
			die(true);
		}

	}

	public function ajaxProcessAddSupplyStock() {

		$data = $this->createTemplate('controllers/addSupplyStock.tpl');

		$supplies = new PhenyxShopCollection('FormatPackSupplies');

		$data->assign([
			'supplies' => new PhenyxShopCollection('FormatPackSupplies'),
		]);

		$result = [
			'html' => $data->fetch(),
		];

		die(Tools::jsonEncode($result));
	}

	public function ajaxProcessAddEducationStock() {

		$id_formatpack_supplies = Tools::getValue('id_formatpack_supplies');

		$stock = Tools::getValue('stock');

		$wholesale_price = Tools::getValue('wholesale_price');

		$supplies = new FormatPackSupplies($id_formatpack_supplies);
		$supplies->pamp = ($supplies->pamp + $wholesale_price) / 2;
		$supplies->stock = $supplies->stock + $stock;
		$supplies->update();

		$result = [
			'success' => true,
		];

		die(Tools::jsonEncode($result));

	}
	
	public function ajaxProcessbuildCefGraph() {
		
		$data = $this->createTemplate('controllers/dashboard/rapportCef.tpl');
		$perfomrances = SaleAgent::getAgentsPerformances($this->currentExerciceStart);
		
		
		$data->assign([
			'controller' => $this->controller_name,
			'link'       => $this->context->link,
			'perfomrances' => Tools::jsonEncode($perfomrances['performances']),
			'days'			=> $perfomrances['cycle']
		]);

		$result = [
			'html' => $data->fetch(),
		];

		die(Tools::jsonEncode($result));
	}
	
	public function ajaxProcessgetLastDayMax() {
		
		$dateTo = SaleAgent::getLastValidEducationState();
		$startDate = strtotime($this->currentExerciceStart);
  		$endDate = strtotime($dateTo);
		$datediff = $endDate - $startDate;
		$days = floor($datediff / (60 * 60 * 24));
		$return = [
			'limtDay' => floor($datediff / (60 * 60 * 24))
		];
		die(Tools::jsonEncode($return));
	}
	
	public function ajaxProcessGetSaleAgentPerformance() {		
		
		$perfomrances = SaleAgent::getAgentsPerformances($this->currentExerciceStart);
		
		die(Tools::jsonEncode($perfomrances));
	}

}
