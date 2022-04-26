<?php

/**
 * Class AdminTranslationsControllerCore
 *
 * @since 1.8.5.0
 */

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\RichText\RichText;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Protection;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class AdminTranslationsControllerCore extends AdminController {

	/** Name of theme by default */
	const DEFAULT_THEME_NAME = _PS_DEFAULT_THEME_NAME_;
	const TEXTAREA_SIZED = 70;

	// @codingStandardsIgnoreStart
	/** @var array $ignore_folder List of folder which must be ignored */
	protected static $ignore_folder = ['.', '..', '.svn', '.git', '.htaccess', 'index.php'];
	/** @var string $link_lang_pack Link which list all pack of language */
	protected $link_lang_pack = 'https://translations.ephenyx.com/packs/';
	/** @var int $total_expression number of sentence which can be translated */
	protected $total_expression = 0;
	/** @var int $missing_translations number of sentence which aren't translated */
	protected $missing_translations = 0;
	/** @var array $all_iso_lang List of ISO code for all languages */
	protected $all_iso_lang = [];
	/** @var array $modules_translations */
	protected $modules_translations = [];
	/** @var array $translations_informations List of theme by translation type : FRONT, BACK, ERRORS... */
	protected $translations_informations = [];
	/** @var array $languages List of all languages */
	protected $languages;
	/** @var array $themes List of all themes */
	protected $themes;
	/** @var string $theme_selected Directory of selected theme */
	protected $theme_selected;
	/** @var string $type_selected Name of translations type */
	protected $type_selected;
	/** @var Language $lang_selected Language for the selected language */
	protected $lang_selected;
	/** @var bool $post_limit_exceed Is true if number of var exceed the suhosin request or post limit */
	protected $post_limit_exceed = false;

	protected $compile_ajax = false;

	protected $compile_grid = false;

	public $colModel = [];

	public $subColModel = [];

	public $translations;

	public $paraGridObj;
	public $paraGridVar;
	public $paraGridId;
	// @codingStandardsIgnoreEnd

	/**
	 * AdminTranslationsControllerCore constructor.
	 *
	 * @since 1.8.5.0
	 */
	public function __construct() {

		$this->bootstrap = true;
		$this->publicName = $this->l('Translations');
		$this->multishop_context = Shop::CONTEXT_ALL;
		$this->table = 'translations';

		parent::__construct();

		$this->colModel = [
			[
				'title'     => '',
				'minWidth'  => 120,
				'maxWidth'  => 120,
				'type'      => 'detail',
				'align'     => 'center',
				'valign'    => 'center',
				'resizable' => false,
				'editable'  => false,
				'sortable'  => false,
				'hidden'    => false,
				'show'      => true,
			],
			[
				'title'      => $this->l('Name'),
				'dataIndx'   => 'name',
				'dataType'   => 'string',
				'editable'   => false,
				'halign'     => 'HORIZONTAL_LEFT',
				'hiddenable' => 'no',
				'align'      => 'left',
				'filter'     => [
					'crules' => [['condition' => "begin"]],
					'style'  => 'max-width:300px;',
				],
			],
		];

		$this->subColModel = [
			[
				'title'      => ' ',
				'width'      => 50,
				'dataIndx'   => 'parent',
				'dataType'   => 'string',
				'hidden'     => true,
				'hiddenable' => 'no',
			],
			[
				'title'    => $this->l('Expression'),
				'width'    => 250,
				'dataIndx' => 'expression',
				'align'    => 'left',
				'halign'   => 'HORIZONTAL_LEFT',
				'dataType' => 'string',
				'editable' => false,
				'filter'   => [
					'crules' => [['condition' => "begin"]],
				],
			],
			[
				'title'    => $this->l('Translation'),
				'width'    => 250,
				'dataIndx' => 'traduction',
				'align'    => 'left',
				'halign'   => 'HORIZONTAL_LEFT',
				'cls'      => 'missingTranslation',
				'dataType' => 'html',
				'editable' => true,
			],
			[
				'title'    => $this->l('Instruction'),
				'width'    => 250,
				'dataIndx' => 'use_sprintf',
				'align'    => 'left',
				'halign'   => 'HORIZONTAL_LEFT',
				'dataType' => 'string',
				'editable' => false,
			],
		];
	}

	public function setMedia() {

		parent::setMedia();
		$this->addjQueryPlugin('dmuploader');

		$this->addJS([
			__PS_BASE_URI__ . $this->admin_webpath . '/js/translations.js',
			__PS_BASE_URI__ . $this->admin_webpath . '/js/pgrid/pqSelect/pqselect.min.js',
			__PS_BASE_URI__ . $this->admin_webpath . '/js/pgrid/pqgrid.min.js',
			__PS_BASE_URI__ . $this->admin_webpath . '/js/pgrid/localize/pq-localize-fr.js',
			__PS_BASE_URI__ . $this->admin_webpath . '/js/pgrid/pqTouch/pqtouch.min.js',
			__PS_BASE_URI__ . $this->admin_webpath . '/js/pgrid/jsZip-2.5.0/jszip.min.js',
			__PS_BASE_URI__ . $this->admin_webpath . '/js/pgrid/FileSaver.js',
			__PS_BASE_URI__ . $this->admin_webpath . '/js/pgrid/javascript-detect-element-resize/jquery.resize.js',

		]);
		$this->addCSS(__PS_BASE_URI__ . $this->admin_webpath . '/themes/' . $this->bo_theme . '/css/translation.css', 'all', PHP_INT_MAX);

		MediaAdmin::addJsDef([
			'AjaxLinkAdminTranslations' => $this->context->link->getAdminLink('AdminTranslations'),
		]);

	}

	/**
	 * @param string $email
	 *
	 * @return string
	 *
	 * @since 1.8.5.0
	 */
	public static function getEmailHTML($email) {

		if (defined('_PS_HOST_MODE_') && strpos($email, _PS_MAIL_DIR_) !== false) {
			$emailFile = $email;
		} else

		if (__PS_BASE_URI__ != '/') {
			$emailFile = str_replace(__PS_BASE_URI__, '', _PS_ROOT_DIR_ . '/') . $email;
		} else {
			$emailFile = _PS_ROOT_DIR_ . $email;
		}

		$emailHtml = file_get_contents($emailFile);

		return $emailHtml;
	}

	/**
	 * @param string $typeSelected
	 *
	 * @return void
	 *
	 * @since 1.8.5.0
	 */
	public function setTypeSelected($typeSelected) {

		$this->type_selected = $typeSelected;
	}

	/**
	 * AdminController::initContent() override
	 *
	 * @see AdminController::initContent()
	 *
	 * @return void
	 *
	 * @since 1.8.5.0
	 */
	public function initContent() {

		$this->initTabModuleList();
		$this->initPageHeaderToolbar();

		if (!is_null($this->type_selected)) {
			$methodName = 'initForm' . $this->type_selected;

			if (method_exists($this, $methodName)) {
				$this->content = $this->initForm($methodName);
			} else {
				$this->errors[] = sprintf(Tools::displayError('"%s" does not exist.'), $this->type_selected);
				$this->content = $this->initMain();
			}

		} else {
			$this->content = $this->initMain();
		}

		$this->context->smarty->assign(
			[
				'content'                   => $this->content,
				'bo_imgdir'                 => __PS_BASE_URI__ . $this->admin_webpath . '/themes/' . $this->bo_theme . '/img/',
				'show_page_header_toolbar'  => $this->show_page_header_toolbar,
				'page_header_toolbar_title' => $this->page_header_toolbar_title,
				'page_header_toolbar_btn'   => $this->page_header_toolbar_btn,

			]
		);
	}

	/**
	 * Generate the Main page
	 *
	 * @return string
	 *
	 * @since 1.8.5.0
	 */
	public function initMain() {

		// Block add/update a language
		$packsToInstall = [];
		$packsToUpdate = [];
		$token = Tools::getAdminToken('AdminLanguages' . (int) EmployeeMenu::getIdFromClassName('AdminLanguages') . (int) $this->context->employee->id);
		$fileName = "{$this->link_lang_pack}/" . _EPH_VERSION_ . "/index.json";

		$langPacks = false;
		$guzzle = new \GuzzleHttp\Client([
			'base_uri' => _PS_TOOL_DIR_ . 'translations.pem',
			'timeout'  => 20,
		]);
		try {
			$langPacks = (string) $guzzle->get($fileName)->getBody();
		} catch (Exception $e) {
			Logger::addLog(sprintf($this->l('%s modification', 'AdminTab', false, false), $this->className), 1, null, $this->className, $e->getMessage(), true, (int) $this->context->employee->id);
		}

		if ($langPacks && $langPacks = json_decode($langPacks, true)) {

			foreach ($langPacks as $key => $langPack) {

				if (!Language::isInstalled($langPack['iso_code'])) {
					$packsToInstall[$key] = $langPack;
				} else {
					$packsToUpdate[$key] = $langPack;
				}

			}

		}

		$this->tpl_view_vars = [
			'bo_imgdir'                 => __PS_BASE_URI__ . $this->admin_webpath . '/themes/' . $this->bo_theme . '/img/',
			'theme_default'             => static::DEFAULT_THEME_NAME,
			'theme_lang_dir'            => _THEME_LANG_DIR_,
			'token'                     => $this->token,
			'languages'                 => $this->languages,
			'translations_type'         => $this->translations_informations,
			'packs_to_install'          => $packsToInstall,
			'packs_to_update'           => $packsToUpdate,
			'url_submit'                => $this->context->link->getAdminLink('AdminTranslations'),
			'themes'                    => $this->themes,
			'id_theme_current'          => $this->context->shop->id_theme,
			'url_create_language'       => 'index.php?controller=AdminLanguages&addlang&token=' . $token,
			'AjaxLinkAdminTranslations' => $this->context->link->getAdminLink('AdminTranslations'),
			'linkController'            => $this->context->link->getAdminLink($this->controller_name),
		];

		$this->toolbar_scroll = false;
		// $this->base_tpl_view = 'main.tpl';

		$this->content .= parent::renderView();

		return $this->content;
	}

	public function ajaxProcessUploadExcelFile() {

		if (isset($_FILES['excelTranslation']['name']) && !empty($_FILES['excelTranslation']['name']) && !empty($_FILES['excelTranslation']['tmp_name'])) {

			$Upload['content'] = Tools::file_get_contents($_FILES['excelTranslation']['tmp_name']);
			$Upload['name'] = $_FILES['excelTranslation']['name'];
			$Upload['mime'] = $_FILES['excelTranslation']['type'];
			$dir = _PS_ADMIN_DIR_ . DIRECTORY_SEPARATOR . 'translations' . DIRECTORY_SEPARATOR;
			$fileName = $_FILES['excelTranslation']['name'];
			$uploadfile = $dir . basename($fileName);
			$sourcePath = $_FILES['excelTranslation']['tmp_name'];
			move_uploaded_file($sourcePath, $uploadfile);
			$return = [
				'status' => 'ok',
				'path'   => $uploadfile,
			];
			die(Tools::jsonEncode($return));

		}

	}

	public function ajaxProcessGridTranslation() {

		$ajaxlink = $this->context->link->getAdminLink($this->controller_name);
		$this->getInformations();
		$methodName = 'initForm' . ucfirst($this->type_selected);
		$translations = [];
		

		if (method_exists($this, $methodName)) {

			if (isset($this->translations_informations[$this->type_selected])) {
				$translationInformation = $this->translations_informations[$this->type_selected];
			} else {
				return false;
			}

			$this->compile_grid = true;
			$tabsArray = $this->{$methodName}

			();
			$missingTranslations = $tabsArray['missingTranslations'];
			$fieldTranslations = $tabsArray['tabsArray'];

			$textareaSize = AdminTranslationsControllerCore::TEXTAREA_SIZED;
			$paraGrid = [];
			$script = '';

			foreach ($fieldTranslations as $key => $values) {

				if (empty($values)) {
					continue;
				}

				$gridKey = str_replace('-', '', str_replace('.php', '', $key));

				$this->paraGridObj = 'obj' . $gridKey;
				$this->paraGridVar = 'grid' . $gridKey;
				$this->paraGridId = 'grid_' . $gridKey;

				$data = [];

				foreach ($values as $expression => $content) {

					$use_sprintf = '';
					$missing = '';
					$style = 'style=""';

					if (empty($content['trad'])) {
						$style = ' class="translationmissing"';
					}

					if (strlen($expression) < $textareaSize) {
						$translateField = '<input type="text" ' . $style . ' name="' . $key . md5($expression) . '" value="' . $content['trad'] . '" />';
					} else {
						$translateField = '<textarea ' . $style . 'rows="' . ceil(strlen($expression) / $textareaSize) . '" name="' . $key . md5($expression) . '">' . $content['trad'] . '</textarea>';
					}

					if (!empty($content['use_sprintf'])) {
						$use_sprintf = $this->l('This expression uses a special syntax:') . ' ' . $content['use_sprintf'];
					}

					$data[] = [
						'parent'      => $key,
						'expression'  => $expression,
						'traduction'  => $translateField,
						'use_sprintf' => $use_sprintf,
					];

				}

				$this->translations = Tools::jsonEncode($data);
				$collapse = 1;

				if (isset($missingTranslations[$key])) {
					$missing = ' ' . $missingTranslations[$key] . ' ' . $this->l('Missing');
					$collapse = 0;
				}

				$title = $key . ' : ' . sizeof($values) . ' ' . $this->l('Expressions') . $missing;

				$script .= $this->gridBuilder($title, $this->translations, $collapse);

				$paraGrid[] = [
					'id'      => $this->paraGridId,
					'obj'     => $this->paraGridObj,
					'var'     => $this->paraGridVar,
					'name'    => $key,
					'missing' => $missing,
				];

			}

			$tpl = $this->createTemplate('translation_grid.tpl');

			$tpl->assign(
				[
					'paraGrids' => $paraGrid,
					'type'      => $this->type_selected,
					'lang'      => $this->lang_selected->iso_code,
				]
			);
			
			$return = [
				'content' => $tpl->fetch(),
				'script'  => $this->generateTranslationScript($paraGrid),
			];
			
			die(Tools::jsonEncode($return));

		}

		die(true);
	}

	public function gridBuilder($title, $data, $collapse) {

		$this->paragrid_option['paragrids'][] = [
			'paragridVar' => $this->paraGridVar,
			'paraGridId'  => $this->paraGridId,
			'paraGridObj' => $this->paraGridObj,
			'builder'     => [
				'height'        => '\'flex\'',
				'width'         => '\'100%\'',
				'dataModel'     => [
					'data' => $data,
				],
				'scrollModel'   => [
					'autoFit' => true,
				],
				'colModel'      => Tools::jsonEncode($this->subColModel),
				'numberCell'    => [
					'show' => 0,
				],
				'showTitle'     => 1,
				'title'         => '\'' . $title . '\'',
				'showTop'       => true,
				'collapsible'   => [
					'on'        => true,
					'toggle'    => true,
					'collapsed' => $collapse,
				],
				'resizable'     => 0,
				'freezeCols'    => 1,
				'rowBorders'    => 1,
				'columnBorders' => 1,
				'stripeRows'    => 1,
				'contextMenu'   => [
					'on'    => 0,
					'items' => 'function(evt, ui){
                    	return (bodyItems.call(this, evt, ui));
                	}',
				],

			],

		];

	}

	public function generateTranslationScript($paraGrid) {

		$jsScript = '<script type="text/javascript">' . PHP_EOL;

		foreach ($this->paragrid_option as $key => $value) {

			if ($key == 'paragrids') {

				foreach ($this->paragrid_option[$key] as $element => $values) {
					$jsScript .= 'function init' . $values['paraGridObj'] . '()  {' . PHP_EOL;
					$jsScript .= '	return {' . PHP_EOL;

					foreach ($values['builder'] as $option => $value) {

						if (is_array($value)) {
							$jsScript .= '      ' . $this->deployArrayScript($option, $value) . PHP_EOL;
						} else {
							$jsScript .= '      ' . $option . ': ' . $value . ',' . PHP_EOL;
						}

					}

					$jsScript .= '	};' . PHP_EOL;

					$jsScript .= '};' . PHP_EOL;

				}

			}

		}

		$jsScript .= '</script>' . PHP_EOL;
		return $jsScript;

	}

	public function ajaxProcessCompileUploadFile() {

		$path = Tools::getValue('path');

		if (file_exists($path)) {

			$inputFileType = 'Xlsx';
			$reader = IOFactory::createReader($inputFileType);
			$spreadsheet = $reader->load($path);
			$this->type_selected = $spreadsheet->getProperties()->getTitle();
			$isoCode = $spreadsheet->getProperties()->getSubject();
			$this->lang_selected = new Language((int) Language::getIdByIso($isoCode));
			$this->theme_selected = $spreadsheet->getProperties()->getKeywords();
			$_POST['lang'] = $isoCode;
			$this->getTranslationsInformations();
			$sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

			$methodName = 'initForm' . ucfirst($this->type_selected);

			if (method_exists($this, $methodName)) {

				if (isset($this->translations_informations[$this->type_selected])) {
					$translationInformation = $this->translations_informations[$this->type_selected];
				} else {
					return false;
				}

				$filePath = $translationInformation['dir'] . $translationInformation['file'];

				if ($filePath && !file_exists($filePath)) {

					if (!file_exists(dirname($filePath)) && !mkdir(dirname($filePath), 0777, true)) {
						throw new PhenyxShopException(sprintf(Tools::displayError('Directory "%s" cannot be created'), dirname($filePath)));
					} else

					if (!touch($filePath)) {
						throw new PhenyxShopException(sprintf(Tools::displayError('File "%s" cannot be created'), $filePath));
					}

				}

				$thmName = str_replace('.', '', $this->theme_selected);
				$kpiKey = substr(strtoupper($thmName . '_' . $this->lang_selected->iso_code), 0, 16);

				$toInsert = [];
				$this->compile_ajax = true;
				$tabsArray = $this->{$methodName}

				();

				foreach ($tabsArray as $key => $value) {

					foreach ($value as $expression => $translation) {
						$keyval = $key . md5($expression);

						foreach ($sheetData as $sheets) {

							if ($sheets['A'] == $key && $sheets['B'] == $expression) {
								$newtranslation = $sheets['C'];
							}

						}

						if (!empty($newtranslation)) {
							$toInsert[$keyval] = $newtranslation;
						}

					}

				}

				ksort($toInsert);
				$file = fopen($filePath, "w");
				$tab = $translationInformation['var'];
				fwrite($file, "<?php\n\nglobal \$" . $tab . ";\n\$" . $tab . " = array();\n");

				foreach ($toInsert as $key => $value) {
					fwrite($file, '$' . $tab . '[\'' . pSQL($key, true) . '\'] = \'' . pSQL($value, true) . '\';' . "\n");
				}

				fwrite($file, "\n?>");
				fclose($file);
				die(true);
			}

		}

	}

	public function ajaxProcessExportTranslation() {

		$methodName = 'initForm' . ucfirst($this->type_selected);

		if (method_exists($this, $methodName)) {

			$this->{'initForm' . ucfirst($this->type_selected)}
			(true);
		} else {
			$answer = [
				'success' => false,
				'message' => 'cacabeurk with ' . $methodName,
			];
			die(Tools::jsonEncode($answer));
		}

	}

	public function returnSpreadSheet($tabsArray, $missingTranslations) {

		$name = $this->l('Translations-') . $this->lang_selected->iso_code . '-' . $this->type_selected . '.xlsx';
		$tag = $this->l('Translations') . $this->lang_selected->iso_code . $this->type_selected;

		$spreadsheet = new Spreadsheet();
		$spreadsheet->getProperties()->setCreator('Ephenyx Shop')
			->setTitle($this->type_selected)
			->setSubject($this->lang_selected->iso_code)
			->setDescription($this->l('Translations') . ' ' . $this->type_selected . ' ' . $this->l('Language:') . ' ' . $this->lang_selected->iso_code)
			->setKeywords((isset($this->theme_selected)) ? $this->theme_selected : '')
			->setCategory($this->l('Translations'));

		$spreadsheet->getActiveSheet()->getColumnDimension('A')->setWidth(70);
		$spreadsheet->getActiveSheet()->getColumnDimension('B')->setWidth(70);
		$spreadsheet->getActiveSheet()->getColumnDimension('C')->setWidth(70);
		$spreadsheet->getActiveSheet()->getColumnDimension('D')->setWidth(70);

		$drawing = new Drawing();
		$drawing->setName('Logo Ephenyx Shop');
		$drawing->setPath(_PS_ADMIN_DIR_ . '/themes/' . $this->bo_theme . '/img/ephenyx-avatar-header_shopname.png');
		$drawing->setHeight(80);
		$drawing->setCoordinates('A1');
		$drawing->setWorksheet($spreadsheet->getActiveSheet());

		$spreadsheet->getActiveSheet()->mergeCells('A1:D4');

		$i = 5;

		foreach ($tabsArray as $key => $value) {

			$nbField = sizeof($value);
			$missing = 0;

			if (isset($missingTranslations[$key])) {
				$missing = ($missingTranslations[$key] > 0) ? $missingTranslations[$key] : 0;
			}

			$richText = new RichText();
			$richText->createText('');
			$richTextContent = $richText->createTextRun($key . ' ' . $nbField . ' ' . $this->l('Expressions '));
			$richTextContent->getFont()->setBold(true);
			$richTextContent->getFont()->setSize(14);

			$payable = $richText->createTextRun($missing . ' ' . $this->l('Missings'));
			$payable->getFont()->setBold(true);
			$payable->getFont()->setItalic(true);
			$payable->getFont()->setColor(new Color(Color::COLOR_DARKGREEN));
			$payable->getFont()->setSize(14);
			$spreadsheet->getActiveSheet()->getCell('A' . $i)->setValue($richText);
			$spreadsheet->getActiveSheet()->getStyle('A' . $i)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

			$missingStyle = [

				'fill'    => [
					'fillType' => Fill::FILL_SOLID,
					'color'    => ['argb' => 'FFCCFFCC'],
				],
				'borders' => [
					'allBorders' => [
						'borderStyle' => Border::BORDER_MEDIUM,
						'color'       => ['argb' => '00000000'],
					],
				],

			];

			$p = $i + 1;
			$spreadsheet->getActiveSheet()->mergeCells('A' . $i . ':D' . $p);
			$i++;

			foreach ($value as $expression => $translation) {

				$i++;
				$use_sprintf = '';

				if (isset($translation['use_sprintf']) && $translation['use_sprintf']) {
					$use_sprintf = $this->l('This expression uses a special syntax:') . ' {' . $translation['use_sprintf'] . '}';
				}

				$spreadsheet->setActiveSheetIndex(0)
					->setCellValue('A' . $i, $key)
					->setCellValue('B' . $i, $expression)
					->setCellValue('C' . $i, $translation['trad'])
					->setCellValue('D' . $i, $use_sprintf);

				if (empty($translation['trad'])) {
					$spreadsheet->getActiveSheet()->getStyle('C' . $i)->applyFromArray($missingStyle);

				}

				$spreadsheet->getActiveSheet()->getStyle('A' . $i . ':D' . $i)->getAlignment()->setWrapText(true);
				$spreadsheet->getActiveSheet()
					->getStyle('C' . $i)
					->getProtection()->setLocked(
					Protection::PROTECTION_UNPROTECTED
				);
			}

			$i++;
		}

		$spreadsheet->getActiveSheet()->setTitle($tag);
		$spreadsheet->getSecurity()->setLockWindows(true);
		$spreadsheet->getSecurity()->setLockStructure(true);
		$spreadsheet->getSecurity()->setWorkbookPassword(_DB_PASSWD_);
		$spreadsheet->getActiveSheet()->getProtection()->setPassword(_DB_PASSWD_);
		$spreadsheet->getActiveSheet()->getProtection()->setSheet(true);
		$spreadsheet->getActiveSheet()->getProtection()->setSort(true);
		$spreadsheet->getActiveSheet()->getProtection()->setInsertRows(true);
		$spreadsheet->getActiveSheet()->getProtection()->setFormatCells(true);

		$filePath = _PS_ADMIN_DIR_ . DIRECTORY_SEPARATOR . 'translations' . DIRECTORY_SEPARATOR . $this->lang_selected->iso_code . DIRECTORY_SEPARATOR;
		$fileSave = new Xlsx($spreadsheet);
		$fileSave->save($filePath . $name);
		$fileToUpload = 'translations' . DIRECTORY_SEPARATOR . $this->lang_selected->iso_code . DIRECTORY_SEPARATOR . $name;
		$result = [
			'success' => true,
			'link'    => '<a download="' . $name . '" id="translationFile" class="btn btn-default" href="' . $fileToUpload . '"><i class="process-export-excel"></i>' . $this->l('Click here to Download the file') . '</a>',
		];
		die(Tools::jsonEncode($result));

	}

	public function ajaxProcessGridTranslate() {

		if ($this->tabAccess['edit'] === '1') {

			$this->getInformations();
			$methodName = 'initForm' . ucfirst($this->type_selected);

			if (method_exists($this, $methodName)) {

				if (isset($this->translations_informations[$this->type_selected])) {
					$translationInformation = $this->translations_informations[$this->type_selected];

					$filePath = $translationInformation['dir'] . $translationInformation['file'];

					if ($filePath && !file_exists($filePath)) {

						if (!file_exists(dirname($filePath)) && !mkdir(dirname($filePath), 0777, true)) {
							$this->errors[] = Tools::displayError('Directory "%s" cannot be created');
						} else

						if (!touch($filePath)) {
							$this->errors[] = sprintf(Tools::displayError('File "%s" cannot be created'), $filePath);
						}

					} else {
						$thmName = str_replace('.', '', $this->theme_selected);
						$kpiKey = substr(strtoupper($thmName . '_' . $this->lang_selected->iso_code), 0, 16);

						unset(
							$_POST['submitTranslations' . $this->type_selected],
							$_POST['submitTranslations' . $this->type_selected . 'AndStay'],
							$_POST['lang'],
							$_POST['token'],
							$_POST['theme'],
							$_POST['type'],
							$_POST['ajax'],
							$_POST['action']
						);
						$toInsert = [];

						foreach ($_POST as $key => $value) {

							if (!empty($value)) {
								$toInsert[$key] = $value;
							}

						}

						$this->compile_ajax = true;
						$tabsArray = $this->{$methodName}
						();

						foreach ($tabsArray as $key => $value) {

							foreach ($value as $expression => $translation) {
								$keyval = $key . md5($expression);

								if (isset($toInsert[$keyval])) {
									continue;
								}

								if (Tools::getValue($keyval)) {

									$newtranslation = Tools::getValue($keyval);
								}

								if (!empty($newtranslation)) {
									$toInsert[$keyval] = $newtranslation;
								} else {

									if (!empty($translation['trad'])) {
										$toInsert[$keyval] = $translation['trad'];
									}

								}

							}

						}

						ksort($toInsert);
						$file = fopen($filePath, "w");
						$tab = $translationInformation['var'];
						fwrite($file, "<?php\n\nglobal \$" . $tab . ";\n\$" . $tab . " = array();\n");

						foreach ($toInsert as $key => $value) {
							fwrite($file, '$' . $tab . '[\'' . pSQL($key, true) . '\'] = \'' . pSQL($value, true) . '\';' . "\n");
						}

						fwrite($file, "\n?>");
						fclose($file);
						$result = [
							'success' => true,
							'message' => $this->_conf[6],
						];
					}

				} else {
					$this->errors[] = Tools::displayError('We encounter a probleme to load information on selected type:' . ucfirst($this->type_selected));
				}

			} else {
				$this->errors[] = Tools::displayError('We did not find the method :' . $methodName);
			}

			if (count($this->errors)) {
				$result = [
					'success' => false,
					'message' => implode(PHP_EOL, $this->errors),
				];
			}

		} else {
			$result = [
				'success' => false,
				'message' => Tools::displayError('You do not have permission to edit this.'),
			];
		}

		die(Tools::jsonEncode($result));
	}

	public function ajaxProcessSubmitTranslations() {

		$_GET['type'] = $type = Tools::getValue('type');

		if ($this->tabAccess['edit'] === '1') {

			if ($this->writeTranslationFile()) {
				$this->confirmations[] = $this->_conf[6];
				$result = [
					'success' => true,
					'message' => $this->_conf[6],
				];
			}

		} else {
			$result = [
				'success' => false,
				'message' => Tools::displayError('You do not have permission to edit this.'),
			];
		}

		die(Tools::jsonEncode($result));
	}

	/**
	 * This function create vars by default and call the good method for generate form
	 *
	 * @param string $methodName
	 *
	 * @return mixed Call the method $this->method_name()
	 *
	 * @since 1.8.5.0
	 */
	public function initForm($methodName) {

		// Create a title for each translation page
		$title = sprintf(
			$this->l('%1$s (Language: %2$s, Theme: %3$s)'),
			$this->translations_informations[$this->type_selected]['name'],
			$this->lang_selected->name,
			$this->theme_selected ? $this->theme_selected : $this->l('none')
		);

		// Set vars for all forms
		$this->tpl_view_vars = [
			'bo_imgdir'           => __PS_BASE_URI__ . $this->admin_webpath . '/themes/' . $this->bo_theme . '/img/',
			'lang'                => $this->lang_selected->iso_code,
			'title'               => $title,
			'type'                => $this->type_selected,
			'theme'               => $this->theme_selected,
			'post_limit_exceeded' => $this->post_limit_exceed,
			'url_submit'          => $this->context->link->getAdminLink('AdminTranslations'),
			'toggle_button'       => $this->displayToggleButton(),
			'textarea_sized'      => AdminTranslationsControllerCore::TEXTAREA_SIZED,
			'linkController'      => $this->context->link->getAdminLink($this->controller_name),
		];

		// Call method initForm for a type
		return $this->{$methodName}

		();
	}

	/**
	 * @param bool $closed
	 *
	 * @return string
	 *
	 * @since 1.8.5.0
	 */
	public function displayToggleButton($closed = false) {

		$strOutput = '
        <script type="text/javascript">';

		if (Tools::getValue('type') == 'mails') {
			$strOutput .= '$(document).ready(function(){
                toggleDiv(\'' . $this->type_selected . '_div\'); toggleButtonValue(this.id, openAll, closeAll);
                });';
		}

		$strOutput .= '
            var openAll = \'' . html_entity_decode($this->l('Expand all fieldsets'), ENT_NOQUOTES, 'UTF-8') . '\';
            var closeAll = \'' . html_entity_decode($this->l('Close all fieldsets'), ENT_NOQUOTES, 'UTF-8') . '\';
        </script>
        <button type="button" class="btn btn-default" id="buttonall" data-status="open" onclick="toggleDiv(\'' . $this->type_selected . '_div\', $(this).data(\'status\')); toggleButtonValue(this.id, openAll, closeAll);"><i class="process-icon-compress"></i> <span>' . $this->l('Close all fieldsets') . '</span></button>';

		return $strOutput;
	}

	/**
	 * AdminController::postProcess() override
	 *
	 * @see AdminController::postProcess()
	 *
	 * @return void
	 *
	 * @since 1.8.5.0
	 */
	public function postProcess() {

		$this->getInformations();

		/* PhenyxShop demo mode */

		if (_PS_MODE_DEMO_) {
			$this->errors[] = Tools::displayError('This functionality has been disabled.');

			return;
		}

		parent::postProcess();

		/* PhenyxShop demo mode */
		try {

			if (Tools::isSubmit('submitCopyLang')) {

				if ($this->tabAccess['add'] === '1') {
					$this->submitCopyLang();
				} else {
					$this->errors[] = Tools::displayError('You do not have permission to add this.');
				}

			} else

			if (Tools::isSubmit('submitExport')) {

				if ($this->tabAccess['add'] === '1') {
					$this->submitExportLang();
				} else {
					$this->errors[] = Tools::displayError('You do not have permission to add this.');
				}

			} else

			if (Tools::isSubmit('submitImport')) {

				if ($this->tabAccess['add'] === '1') {
					$this->submitImportLang();
				} else {
					$this->errors[] = Tools::displayError('You do not have permission to add this.');
				}

			} else

			if (Tools::isSubmit('submitAddLanguage')) {

				if ($this->tabAccess['add'] === '1') {
					$this->submitAddLang();
				} else {
					$this->errors[] = Tools::displayError('You do not have permission to add this.');
				}

			} else

			if (Tools::isSubmit('submitTranslationsPdf')) {

				if ($this->tabAccess['edit'] === '1') {
					// Only the PhenyxShop team should write the translations into the _PS_TRANSLATIONS_DIR_

					if (!$this->theme_selected) {
						$this->writeTranslationFile();
					} else {
						$this->writeTranslationFile(true);
					}

				} else {
					$this->errors[] = Tools::displayError('You do not have permission to edit this.');
				}

			} else

			if (Tools::isSubmit('submitTranslationsBack') || Tools::isSubmit('submitTranslationsErrors') || Tools::isSubmit('submitTranslationsFields') || Tools::isSubmit('submitTranslationsFront')) {

				if ($this->tabAccess['edit'] === '1') {
					$this->writeTranslationFile();
				} else {
					$this->errors[] = Tools::displayError('You do not have permission to edit this.');
				}

			} else

			if (Tools::isSubmit('submitTranslationsFrontcontroller')) {

				if ($this->tabAccess['edit'] === '1') {
					$this->writeTranslationFile();
				} else {
					$this->errors[] = Tools::displayError('You do not have permission to edit this.');
				}

			} else

			if (Tools::isSubmit('submitTranslationsMails') || Tools::isSubmit('submitTranslationsMailsAndStay')) {

				if ($this->tabAccess['edit'] === '1') {
					$this->submitTranslationsMails();
				} else {
					$this->errors[] = Tools::displayError('You do not have permission to edit this.');
				}

			} else

			if (Tools::isSubmit('submitTranslationsModules')) {

				if ($this->tabAccess['edit'] === '1') {
					// Get list of modules

					if ($modules = $this->getListModules()) {
						// Get files of all modules
						$arrFiles = $this->getAllModuleFiles($modules, null, $this->lang_selected->iso_code, true);

						// Find and write all translation modules files

						foreach ($arrFiles as $value) {
							$this->findAndWriteTranslationsIntoFile($value['file_name'], $value['files'], $value['theme'], $value['module'], $value['dir']);
						}

						// Clear modules cache
						Tools::clearCache();

						// Redirect

						if (Tools::getIsset('submitTranslationsModulesAndStay')) {
							$this->redirect(true);
						} else {
							$this->redirect();
						}

					}

				} else {
					$this->errors[] = Tools::displayError('You do not have permission to edit this.');
				}

			}

		} catch (PhenyxShopException $e) {
			$this->errors[] = $e->getMessage();
		}

	}

	/**
	 * Get all informations on : languages, theme and the translation type.
	 *
	 * @return void
	 * @throws PhenyxShopException
	 * @since 1.8.5.0
	 */
	public function getInformations() {

		// Get all Languages
		$this->languages = Language::getLanguages(false);

		// Get all iso_code of languages

		foreach ($this->languages as $language) {
			$this->all_iso_lang[] = $language['iso_code'];
		}

		// Get all themes
		$this->themes = Theme::getThemes();

		// Get folder name of theme

		if (($theme = Tools::getValue('theme')) && !is_array($theme)) {
			$themeExists = $this->theme_exists($theme);

			if (!$themeExists) {
				throw new PhenyxShopException(sprintf(Tools::displayError('Invalid theme "%s"'), Tools::safeOutput($theme)));
			}

			$this->theme_selected = Tools::safeOutput($theme);
		}

		// Set the path of selected theme

		if ($this->theme_selected) {
			define('_PS_THEME_SELECTED_DIR_', _PS_ROOT_DIR_ . '/themes/' . $this->theme_selected . '/');
		} else {
			define('_PS_THEME_SELECTED_DIR_', '');
		}

		// Get type of translation

		if (($type = Tools::getValue('type')) && !is_array($type)) {
			$this->type_selected = strtolower(Tools::safeOutput($type));
		}

		// Get selected language

		if (Tools::getValue('lang') || Tools::getValue('iso_code')) {
			$isoCode = Tools::getValue('lang') ? Tools::getValue('lang') : Tools::getValue('iso_code');

			if (!Validate::isLangIsoCode($isoCode) || !in_array($isoCode, $this->all_iso_lang)) {
				throw new PhenyxShopException(sprintf(Tools::displayError('Invalid iso code "%s"'), Tools::safeOutput($isoCode)));
			}

			$this->lang_selected = new Language((int) Language::getIdByIso($isoCode));
		} else {
			$this->lang_selected = new Language((int) Language::getIdByIso('en'));
		}

		// Get all information for translations
		$this->getTranslationsInformations();
	}

	/**
	 * Checks if theme exists
	 *
	 * @param string $theme
	 *
	 * @return bool
	 *
	 * @since 1.8.5.0
	 */
	protected function theme_exists($theme) {

		if (!is_array($this->themes)) {
			$this->themes = Theme::getThemes();
		}

		foreach ($this->themes as $existingTheme) {
			/** @var Theme $existingTheme */

			if ($existingTheme->directory == $theme) {
				return true;
			}

		}

		return false;
	}

	/**
	 * Get all translations informations for all type of translations
	 *
	 * array(
	 *  'type' => array(
	 *      'name' => string : title for the translation type,
	 *      'var' => string : name of var for the translation file,
	 *      'dir' => string : dir of translation file
	 *      'file' => string : file name of translation file
	 *  )
	 * )
	 *
	 * @return void
	 *
	 * @since 1.8.5.0
	 */
	public function getTranslationsInformations() {

		$this->translations_informations = [
			'frontcontroller' => [
				'name' => $this->l('Front Controller translations'),
				'var'  => '_LANGFRONT',
				'dir'  => _PS_TRANSLATIONS_DIR_ . $this->lang_selected->iso_code . '/',
				'file' => 'front.php',
			],
			'front'           => [
				'name' => $this->l('Front office translations'),
				'var'  => '_LANG',
				'dir'  => defined('_PS_THEME_SELECTED_DIR_') ? _PS_THEME_SELECTED_DIR_ . 'lang/' : '',
				'file' => $this->lang_selected->iso_code . '.php',
			],
			'class'           => [
				'name' => $this->l('Class translations'),
				'var'  => '_LANGCLASS',
				'dir'  => _PS_TRANSLATIONS_DIR_ . $this->lang_selected->iso_code . '/',
				'file' => 'class.php',
			],
			'back'            => [
				'name' => $this->l('Back office translations'),
				'var'  => '_LANGADM',
				'dir'  => _PS_TRANSLATIONS_DIR_ . $this->lang_selected->iso_code . '/',
				'file' => 'admin.php',
			],
			'errors'          => [
				'name' => $this->l('Error message translations'),
				'var'  => '_ERRORS',
				'dir'  => _PS_TRANSLATIONS_DIR_ . $this->lang_selected->iso_code . '/',
				'file' => 'errors.php',
			],
			'fields'          => [
				'name' => $this->l('Field name translations'),
				'var'  => '_FIELDS',
				'dir'  => _PS_TRANSLATIONS_DIR_ . $this->lang_selected->iso_code . '/',
				'file' => 'fields.php',
			],
			'modules'         => [
				'name' => $this->l('Installed modules translations'),
				'var'  => '_MODULES',
				'dir'  => _PS_MODULE_DIR_,
				'file' => '',
			],
			'pdf'             => [
				'name' => $this->l('PDF translations'),
				'var'  => '_LANGPDF',
				'dir'  => _PS_TRANSLATIONS_DIR_ . $this->lang_selected->iso_code . '/',
				'file' => 'pdf.php',
			],
			'mails'           => [
				'name' => $this->l('Email templates translations'),
				'var'  => '_LANGMAIL',
				'dir'  => _PS_MAIL_DIR_ . $this->lang_selected->iso_code . '/',
				'file' => 'lang.php',
			],
		];

		if (defined('_PS_THEME_SELECTED_DIR_')) {
			$this->translations_informations['modules']['override'] = ['dir' => _PS_THEME_SELECTED_DIR_ . 'modules/', 'file' => ''];
			$this->translations_informations['pdf']['override'] = ['dir' => _PS_THEME_SELECTED_DIR_ . 'pdf/lang/', 'file' => $this->lang_selected->iso_code . '.php'];
			$this->translations_informations['mails']['override'] = ['dir' => _PS_THEME_SELECTED_DIR_ . 'mails/' . $this->lang_selected->iso_code . '/', 'file' => 'lang.php'];
		}

	}

	/**
	 * @return void
	 *
	 * @since 1.8.5.0
	 */
	public function submitCopyLang() {

		if (!($fromLang = Tools::getValue('fromLang')) || !($toLang = Tools::getValue('toLang'))) {
			$this->errors[] = $this->l('You must select two languages in order to copy data from one to another.');
		} else

		if (!($fromTheme = Tools::getValue('fromTheme')) || !($toTheme = Tools::getValue('toTheme'))) {
			$this->errors[] = $this->l('You must select two themes in order to copy data from one to another.');
		} else

		if (!Language::copyLanguageData(Language::getIdByIso($fromLang), Language::getIdByIso($toLang))) {
			$this->errors[] = $this->l('An error occurred while copying data.');
		} else

		if ($fromLang == $toLang && $fromTheme == $toTheme) {
			$this->errors[] = $this->l('There is nothing to copy (same language and theme).');
		} else {
			$themeExists = ['from_theme' => false, 'to_theme' => false];

			foreach ($this->themes as $theme) {

				if ($theme->directory == $fromTheme) {
					$themeExists['from_theme'] = true;
				}

				if ($theme->directory == $toTheme) {
					$themeExists['to_theme'] = true;
				}

			}

			if ($themeExists['from_theme'] == false || $themeExists['to_theme'] == false) {
				$this->errors[] = $this->l('Theme(s) not found');
			}

		}

		if (count($this->errors)) {
			return;
		}

		$bool = true;
		$items = Language::getFilesList($fromLang, $fromTheme, $toLang, $toTheme, false, false, true);

		foreach ($items as $source => $dest) {

			if (!$this->checkDirAndCreate($dest)) {
				$this->errors[] = sprintf($this->l('Impossible to create the directory "%s".'), $dest);
			} else

			if (!copy($source, $dest)) {
				$this->errors[] = sprintf($this->l('Impossible to copy "%s" to "%s".'), $source, $dest);
			} else

			if (strpos($dest, 'modules') && basename($source) === $fromLang . '.php' && $bool !== false) {

				if (!$this->changeModulesKeyTranslation($dest, $fromTheme, $toTheme)) {
					$this->errors[] = sprintf($this->l('Impossible to translate "$dest".'), $dest);
				}

			}

		}

		if (!count($this->errors)) {
			$this->redirect(false, 14);
		}

		$this->errors[] = $this->l('A part of the data has been copied but some of the language files could not be found.');
	}

	/**
	 * This method is only used by AdminTranslations::submitCopyLang().
	 *
	 * It try to create folder in new theme.
	 *
	 * When a translation file is copied for a module, its translation key is wrong.
	 * We have to change the translation key and rewrite the file.
	 *
	 * @param string $dest file name
	 *
	 * @return bool
	 *
	 * @since 1.8.5.0
	 */
	protected function checkDirAndCreate($dest) {

		$bool = true;

		// To get only folder path
		$path = dirname($dest);

		// If folder wasn't already added
		// Do not use file_exists because it changes over time!

		if (!file_exists($path)) {

			if (!mkdir($path, 0777, true)) {
				$bool &= false;
				$this->errors[] = sprintf($this->l('Cannot create the folder "%s". Please check your directory writing permissions.'), $path);
			}

		}

		return $bool;
	}

	/**
	 * Change the key translation to according it to theme name.
	 *
	 * @param string $path
	 * @param string $themeFrom
	 * @param string $themeTo
	 *
	 * @return bool
	 *
	 * @since 1.8.5.0
	 */
	public function changeModulesKeyTranslation($path, $themeFrom, $themeTo) {

		$content = file_get_contents($path);
		$arrReplace = [];
		$boolFlag = true;

		if (preg_match_all('#\$_MODULE\[\'([^\']+)\'\]#Ui', $content, $matches)) {

			foreach ($matches[1] as $key => $value) {
				$arrReplace[$value] = str_replace($themeFrom, $themeTo, $value);
			}

			$content = str_replace(array_keys($arrReplace), array_values($arrReplace), $content);
			$boolFlag = (file_put_contents($path, $content) === false) ? false : true;
		}

		return $boolFlag;
	}

	/**
	 * This method redirect in the translation main page or in the translation page
	 *
	 * @param bool $saveAndStay : true if the user has clicked on the button "save and stay"
	 * @param bool $conf        : id of confirmation message
	 *
	 * @return void
	 *
	 * @since 1.8.5.0
	 */
	protected function redirect($saveAndStay = false, $conf = false) {

		$conf = !$conf ? 4 : $conf;
		$urlBase = static::$currentIndex . '&token=' . $this->token . '&conf=' . $conf;

		if ($saveAndStay) {
			Tools::redirectAdmin($urlBase . '&lang=' . $this->lang_selected->iso_code . '&type=' . $this->type_selected . '&theme=' . $this->theme_selected);
		} else {
			Tools::redirectAdmin($urlBase);
		}

	}

	/**
	 * @return void
	 *
	 * @since 1.8.5.0
	 */
	public function submitExportLang() {

		if ($this->lang_selected->iso_code && $this->theme_selected) {
			$this->exportTabs();
			$items = array_flip(Language::getFilesList($this->lang_selected->iso_code, $this->theme_selected, false, false, false, false, true));
			$fileName = _PS_TRANSLATIONS_DIR_ . '/export/' . $this->lang_selected->iso_code . '.gzip';
			$gz = new Archive_Tar($fileName, true);

			if ($gz->createModify($items, null, _PS_ROOT_DIR_)) {
				ob_start();
				header('Pragma: public');
				header('Expires: 0');
				header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
				header('Cache-Control: public');
				header('Content-Description: File Transfer');
				header('Content-type: application/octet-stream');
				header('Content-Disposition: attachment; filename="' . $this->lang_selected->iso_code . '.gzip' . '"');
				header('Content-Transfer-Encoding: binary');
				ob_end_flush();
				readfile($fileName);
				@unlink($fileName);
				exit;
			}

			$this->errors[] = Tools::displayError('An error occurred while creating archive.');
		}

		$this->errors[] = Tools::displayError('Please select a language and a theme.');
	}

	/**
	 * @throws PhenyxShopException
	 *
	 * @return void
	 *
	 * @since 1.8.5.0
	 */
	public function exportTabs() {

		// Get name tabs by iso code
		$tabs = EmployeeMenu::getEmployeeMenus($this->lang_selected->id);

		// Get name of the default tabs
		$tabsDefaultLang = EmployeeMenu::getEmployeeMenus(1);

		$tabsDefault = [];

		foreach ($tabsDefaultLang as $tab) {
			$tabsDefault[$tab['class_name']] = pSQL($tab['name']);
		}

		// Create content
		$content = "<?php\n\n\$_TABS = array();";

		if (!empty($tabs)) {

			foreach ($tabs as $tab) {
				/**
				 * We don't export tab translations that are identical to the default
				 * tab translations to avoid a problem that would occur in the followin scenario:
				 *
				 * 1) install PhenyxShop in, say, Spanish => tabs are by default in Spanish
				 * 2) create a new language, say, Klingon => tabs are populated using the default, Spanish, tabs
				 * 3) export the Klingon language pack
				 *
				 * => Since you have not yet translated the tabs into Klingon,
				 * without the condition below, you would get tabs exported, but in Spanish.
				 * This would lead to a Klingon pack actually containing Spanish.
				 *
				 * This has caused many issues in the past, so, as a precaution, tabs from
				 * the default language are not exported.
				 *
				 */

				if ($tabsDefault[$tab['class_name']] != pSQL($tab['name'])) {
					$content .= "\n\$_TABS['" . $tab['class_name'] . "'] = '" . pSQL($tab['name']) . "';";
				}

			}

		}

		$content .= "\n\nreturn \$_TABS;";

		$dir = _PS_TRANSLATIONS_DIR_ . $this->lang_selected->iso_code . DIRECTORY_SEPARATOR;
		$path = $dir . 'tabs.php';

		// Check if tabs.php exists for the selected Iso Code

		if (!file_exists($dir)) {

			if (!mkdir($dir, 0777, true)) {
				throw new PhenyxShopException('The file ' . $dir . ' cannot be created.');
			}

		}

		if (!file_put_contents($path, $content)) {
			throw new PhenyxShopException('File "' . $path . '" does not exist and cannot be created in ' . $dir);
		}

		if (!is_writable($path)) {
			$this->displayWarning(sprintf(Tools::displayError('This file must be writable: %s'), $path));
		}

	}

	/**
	 * Submit import lang
	 *
	 * @return void
	 *
	 * @since 1.8.5.0
	 */
	public function submitImportLang() {

		if (!isset($_FILES['file']['tmp_name']) || !$_FILES['file']['tmp_name']) {
			$this->errors[] = Tools::displayError('No file has been selected.');
		} else {
			$gz = new Archive_Tar($_FILES['file']['tmp_name'], true);
			$filename = $_FILES['file']['name'];
			$isoCode = str_replace(['.tar.gz', '.gzip'], '', $filename);

			if (Validate::isLangIsoCode($isoCode)) {
				$themesSelected = Tools::getValue('theme', [static::DEFAULT_THEME_NAME]);
				$filesList = AdminTranslationsController::filterTranslationFiles($gz->listContent());
				$filesPaths = AdminTranslationsController::filesListToPaths($filesList);

				$uniqid = uniqid();
				$sandbox = _PS_CACHE_DIR_ . 'sandbox' . DIRECTORY_SEPARATOR . $uniqid . DIRECTORY_SEPARATOR;

				if ($gz->extractList($filesPaths, $sandbox)) {

					foreach ($filesList as $file2check) {
						// Don't validate index.php, will be overwritten when extract in translation directory
						// Also skip directories

						if (pathinfo($file2check['filename'], PATHINFO_BASENAME) === 'index.php' || empty(pathinfo($file2check['filename'], PATHINFO_EXTENSION))) {
							continue;
						}

						if (preg_match('@^[0-9a-z-_/\\\\]+\.php$@i', $file2check['filename'])) {

							if (!@filemtime($sandbox . $file2check['filename']) || !AdminTranslationsController::checkTranslationFile(file_get_contents($sandbox . $file2check['filename']))) {
								$this->errors[] = sprintf(Tools::displayError('Validation failed for: %s'), $file2check['filename']);
							}

						} else

						if (!preg_match('@mails[0-9a-z-_/\\\\]+\.(html|tpl|txt)$@i', $file2check['filename'])) {
							$this->errors[] = sprintf(Tools::displayError('Unidentified file found: %s'), $file2check['filename']);
						}

					}

					Tools::deleteDirectory($sandbox, true);
				}

				$i = 0;
				$tmpArray = [];

				foreach ($filesPaths as $filesPath) {
					$path = dirname($filesPath);

					if (is_dir(_PS_TRANSLATIONS_DIR_ . '../' . $path) && !is_writable(_PS_TRANSLATIONS_DIR_ . '../' . $path) && !in_array($path, $tmpArray)) {
						$this->errors[] = (!$i++ ? Tools::displayError('The archive cannot be extracted.') . ' ' : '') . Tools::displayError('The server does not have permissions for writing.') . ' ' . sprintf(Tools::displayError('Please check rights for %s'), $path);
						$tmpArray[] = $path;
					}

				}

				if (count($this->errors)) {
					return;
				}

				if ($error = $gz->extractList($filesPaths, _PS_TRANSLATIONS_DIR_ . '../')) {

					if (is_object($error) && !empty($error->message)) {
						$this->errors[] = Tools::displayError('The archive cannot be extracted.') . ' ' . $error->message;
					} else {

						foreach ($filesList as $file2check) {

							if (pathinfo($file2check['filename'], PATHINFO_BASENAME) == 'index.php' && file_put_contents(_PS_TRANSLATIONS_DIR_ . '../' . $file2check['filename'], Tools::getDefaultIndexContent())) {
								continue;
							}

						}

						// Clear smarty modules cache
						Tools::clearCache();

						if (Validate::isLanguageFileName($filename)) {

							if (!Language::checkAndAddLanguage($isoCode)) {
								$conf = 20;
							} else {
								// Reset cache
								Language::loadLanguages();

								AdminTranslationsController::checkAndAddMailsFiles($isoCode, $filesList);
								$this->checkAndAddThemesFiles($filesList, $themesSelected);
								$tabErrors = AdminTranslationsController::addNewTabs($isoCode, $filesList);

								if (count($tabErrors)) {
									$this->errors += $tabErrors;

									return;
								}

							}

						}

						$this->redirect(false, (isset($conf) ? $conf : '15'));
					}

				}

				$this->errors[] = Tools::displayError('The archive cannot be extracted.');
			} else {
				$this->errors[] = sprintf(Tools::displayError('ISO CODE invalid "%1$s" for the following file: "%2$s"'), $isoCode, $filename);
			}

		}

	}

	/**
	 * Filter the translation files contained in a .gzip pack
	 * and return only the ones that we want.
	 *
	 * Right now the function only needs to check that
	 * the modules for which we want to add translations
	 * are present on the shop (installed or not).
	 *
	 * @param array $list Is the output of Archive_Tar::listContent()
	 *
	 * @return array
	 *
	 * @since 1.8.5.0
	 */
	public static function filterTranslationFiles($list) {

		$kept = [];

		foreach ($list as $file) {

			if ('index.php' == basename($file['filename'])) {
				continue;
			}

			if (preg_match('#^modules/([^/]+)/#', $file['filename'], $m)) {

				if (is_dir(_PS_MODULE_DIR_ . $m[1])) {
					$kept[] = $file;
				}

			} else {
				$kept[] = $file;
			}

		}

		return $kept;
	}

	/**
	 * Turn the list returned by
	 * AdminTranslationsController::filterTranslationFiles()
	 * into a list of paths that can be passed to
	 * Archive_Tar::extractList()
	 *
	 * @param array $list
	 *
	 * @return array
	 *
	 * @since 1.8.5.0
	 */
	public static function filesListToPaths($list) {

		$paths = [];

		foreach ($list as $item) {
			$paths[] = $item['filename'];
		}

		return $paths;
	}

	/**
	 * @param string $content
	 *
	 * @return bool
	 *
	 * @since 1.8.5.0
	 */
	public static function checkTranslationFile($content) {

		$lines = array_map('trim', explode("\n", $content));
		$global = false;

		foreach ($lines as $line) {
			// PHP tags

			if (in_array($line, ['<?php', '?>', ''])) {
				continue;
			}

			// Global variable declaration

			if (!$global && preg_match('/^global\s+\$([a-z0-9-_]+)\s*;$/i', $line, $matches)) {
				$global = $matches[1];
				continue;
			}

			// Global variable initialization

			if ($global && (
				preg_match('/^\$' . preg_quote($global, '/') . '\s*=\s*array\(\s*\)\s*;$/i', $line)
				|| preg_match('/^\$' . preg_quote($global, '/') . '\s*=\s*\[\s*\]\s*;$/i', $line)
			)) {
				continue;
			}

			// Global variable initialization without declaration

			if (!$global && (
				preg_match('/^\$([a-z0-9-_]+)\s*=\s*array\(\s*\)\s*;$/i', $line, $matches)
				|| preg_match('/^\$([a-z0-9-_]+)\s*=\s*\[\s*\]\s*;$/i', $line, $matches)
			)) {
				$global = $matches[1];
				continue;
			}

			// Assignation

			if (preg_match('/^\$' . preg_quote($global, '/') . '\[\'' . _PS_TRANS_PATTERN_ . '\'\]\s*=\s*\'' . _PS_TRANS_PATTERN_ . '\'\s*;$/i', $line)) {
				continue;
			}

			// Sometimes the global variable is returned...

			if (preg_match('/^return\s+\$' . preg_quote($global, '/') . '\s*;$/i', $line, $matches)) {
				continue;
			}

			return false;
		}

		return true;
	}

	/**
	 * Check and add mail files
	 *
	 * @param string $isoCode
	 * @param array  $filesList
	 *
	 * @return bool
	 *
	 * @since 1.8.5.0
	 */
	public static function checkAndAddMailsFiles($isoCode, $filesList) {

		if (Language::getIdByIso('en')) {
			$defaultLanguage = 'en';
		} else {
			$defaultLanguage = Language::getIsoById((int) Configuration::get('PS_LANG_DEFAULT'));
		}

		if (!$defaultLanguage || !Validate::isLanguageIsoCode($defaultLanguage)) {
			return false;
		}

		// 1 - Scan mails files
		$mails = [];

		if (file_exists(_PS_MAIL_DIR_ . $defaultLanguage . '/')) {
			$mails = scandir(_PS_MAIL_DIR_ . $defaultLanguage . '/');
		}

		$mailsNewLang = [];

		// Get all email files

		foreach ($filesList as $file) {

			if (preg_match('#^(\.\/)?mails\/([a-z0-9]+)\/#Ui', $file['filename'], $matches)) {
				$slashPos = strrpos($file['filename'], '/');
				$mailsNewLang[] = substr($file['filename'], -(strlen($file['filename']) - $slashPos - 1));
			}

		}

		// Get the difference
		$arrMailsNeeded = array_diff($mails, $mailsNewLang);

		// Add mails files

		foreach ($arrMailsNeeded as $mailToAdd) {

			if (!in_array($mailToAdd, static::$ignore_folder)) {
				@copy(_PS_MAIL_DIR_ . $defaultLanguage . '/' . $mailToAdd, _PS_MAIL_DIR_ . $isoCode . '/' . $mailToAdd);
			}

		}

		// 2 - Scan modules files
		$modules = scandir(_PS_MODULE_DIR_);

		$moduleMailEn = [];
		$moduleMailIsoCode = [];

		foreach ($modules as $module) {

			if (!in_array($module, static::$ignore_folder) && file_exists(_PS_MODULE_DIR_ . $module . '/mails/' . $defaultLanguage . '/')) {
				$arrFiles = scandir(_PS_MODULE_DIR_ . $module . '/mails/' . $defaultLanguage . '/');

				foreach ($arrFiles as $file) {

					if (!in_array($file, static::$ignore_folder)) {

						if (file_exists(_PS_MODULE_DIR_ . $module . '/mails/' . $defaultLanguage . '/' . $file)) {
							$moduleMailEn[] = _PS_MODULE_DIR_ . $module . '/mails/ISO_CODE/' . $file;
						}

						if (file_exists(_PS_MODULE_DIR_ . $module . '/mails/' . $isoCode . '/' . $file)) {
							$moduleMailIsoCode[] = _PS_MODULE_DIR_ . $module . '/mails/ISO_CODE/' . $file;
						}

					}

				}

			}

		}

		// Get the difference in this modules
		$arrModulesMailsNeeded = array_diff($moduleMailEn, $moduleMailIsoCode);

		// Add mails files for this modules

		foreach ($arrModulesMailsNeeded as $file) {
			$fileEn = str_replace('ISO_CODE', $defaultLanguage, $file);
			$fileIsoCode = str_replace('ISO_CODE', $isoCode, $file);
			$dirIsoCode = substr($fileIsoCode, 0, -(strlen($fileIsoCode) - strrpos($fileIsoCode, '/') - 1));

			if (!file_exists($dirIsoCode)) {
				mkdir($dirIsoCode);
				file_put_contents($dirIsoCode . '/index.php', Tools::getDefaultIndexContent());
			}

			if (file_exists($fileEn)) {
				copy($fileEn, $fileIsoCode);
			}

		}

	}

	/**
	 * Move theme translations in selected themes
	 *
	 * @param array $files
	 * @param array $themesSelected
	 *
	 * @return void
	 *
	 * @since 1.8.5.0
	 */
	public function checkAndAddThemesFiles($files, $themesSelected) {

		foreach ($files as $file) {
			// Check if file is a file theme

			if (preg_match('#^themes\/([a-z0-9]+)\/lang\/#Ui', $file['filename'], $matches)) {
				$slashPos = strrpos($file['filename'], '/');
				$nameFile = substr($file['filename'], -(strlen($file['filename']) - $slashPos - 1));
				$nameDefaultTheme = $matches[1];
				$deletedOldTheme = false;

				// Get the old file theme

				if (file_exists(_PS_THEME_DIR_ . 'lang/' . $nameFile)) {
					$themeFileOld = _PS_THEME_DIR_ . 'lang/' . $nameFile;
				} else {
					$deletedOldTheme = true;
					$themeFileOld = str_replace(static::DEFAULT_THEME_NAME, $nameDefaultTheme, _PS_THEME_DIR_ . 'lang/' . $nameFile);
				}

				// Move the old file theme in the new folder

				foreach ($themesSelected as $theme_name) {

					if (file_exists($themeFileOld)) {
						copy($themeFileOld, str_replace($nameDefaultTheme, $theme_name, $themeFileOld));
					}

				}

				if ($deletedOldTheme) {
					@unlink($themeFileOld);
				}

			}

		}

	}

	/**
	 * Add new translations tabs by code ISO
	 *
	 * @param string $isoCode
	 * @param array  $files
	 *
	 * @return array
	 *
	 * @since 1.8.5.0
	 */
	public static function addNewTabs($isoCode, $files) {

		$errors = [];

		foreach ($files as $file) {
			// Check if file is a file theme

			if (preg_match('#translations\/' . $isoCode . '\/tabs.php#Ui', $file['filename'], $matches) && Validate::isLanguageIsoCode($isoCode)) {
				// Include array width new translations tabs
				$_TABS = [];
				clearstatcache();

				if (file_exists(_PS_ROOT_DIR_ . DIRECTORY_SEPARATOR . $file['filename'])) {
					include_once _PS_ROOT_DIR_ . DIRECTORY_SEPARATOR . $file['filename'];
				}

				if (is_array($_TABS) && count($_TABS)) {

					foreach ($_TABS as $className => $translations) {
						// Get instance of this tab by class name
						$tab = EmployeeMenu::getInstanceFromClassName($className);
						//Check if class name exists

						if (isset($tab->class_name) && !empty($tab->class_name)) {
							$idLang = Language::getIdByIso($isoCode, true);
							$tab->name[(int) $idLang] = $translations;

							// Do not crash at intall

							if (!isset($tab->name[Configuration::get('PS_LANG_DEFAULT')])) {
								$tab->name[(int) Configuration::get('PS_LANG_DEFAULT')] = $translations;
							}

							if (!Validate::isGenericName($tab->name[(int) $idLang])) {
								$errors[] = sprintf(Tools::displayError('Tab "%s" is not valid'), $tab->name[(int) $idLang]);
							} else {
								$tab->update();
							}

						}

					}

				}

			}

		}

		return $errors;
	}

	/**
	 * @return void
	 *
	 * @since 1.8.5.0
	 */
	public function ajaxProcessSubmitAddLanguage() {

		$arrImportLang = explode('|', Tools::getValue('params_import_language')); /* 0 = Language ISO code, 1 = PS version */

		if (Validate::isLangIsoCode($arrImportLang[0])) {
			$guzzle = new \GuzzleHttp\Client([
				'timeout' => 20,
			]);

			$version = _EPH_VERSION_;
			$file = _PS_TRANSLATIONS_DIR_ . $arrImportLang[0] . '.gzip';

			$arrImportLang[1] = _EPH_VERSION_;
			try {
				$guzzle->get("{$this->link_lang_pack}{$version}/{$arrImportLang[0]}.gzip", ['sink' => $file]);
			} catch (Exception $e) {

			}

			if (file_exists($file)) {
				$gz = new Archive_Tar($file, true);
				$filesList = AdminTranslationsController::filterTranslationFiles($gz->listContent());

				if ($error = $gz->extractList(AdminTranslationsController::filesListToPaths($filesList), _PS_TRANSLATIONS_DIR_ . '../')) {

					if (is_object($error) && !empty($error->message)) {
						$this->errors[] = Tools::displayError('The archive cannot be extracted.') . ' ' . $error->message;
					} else {

						if (!Language::checkAndAddLanguage($arrImportLang[0])) {
							$conf = 20;
						} else {
							// Reset cache
							Language::loadLanguages();
							// Clear smarty modules cache
							Tools::clearCache();
							AdminTranslationsController::checkAndAddMailsFiles($arrImportLang[0], $filesList);

							if ($tabErrors = AdminTranslationsController::addNewTabs($arrImportLang[0], $filesList)) {
								$this->errors += $tabErrors;
							}

						}

						if (!unlink($file)) {
							$this->errors[] = sprintf(Tools::displayError('Cannot delete the archive %s.'), $file);
						}

					}

				} else {
					$this->errors[] = sprintf(Tools::displayError('Cannot decompress the translation file for the following language: %s'), $arrImportLang[0]);
					$checks = [];

					foreach ($filesList as $f) {

						if (isset($f['filename'])) {

							if (is_file(_PS_ROOT_DIR_ . DIRECTORY_SEPARATOR . $f['filename']) && !is_writable(_PS_ROOT_DIR_ . DIRECTORY_SEPARATOR . $f['filename'])) {
								$checks[] = dirname($f['filename']);
							} else

							if (is_dir(_PS_ROOT_DIR_ . DIRECTORY_SEPARATOR . $f['filename']) && !is_writable(_PS_ROOT_DIR_ . DIRECTORY_SEPARATOR . dirname($f['filename']))) {
								$checks[] = dirname($f['filename']);
							}

						}

					}

					$checks = array_unique($checks);

					foreach ($checks as $check) {
						$this->errors[] = sprintf(Tools::displayError('Please check rights for folder and files in %s'), $check);
					}

					if (!unlink($file)) {
						$this->errors[] = sprintf(Tools::displayError('Cannot delete the archive %s.'), $file);
					}

				}

			} else {
				$this->errors[] = Tools::displayError('The server does not have permissions for writing.') . ' ' . sprintf(Tools::displayError('Please check rights for %s'), dirname($file));
			}

		} else {
			$this->errors[] = Tools::displayError('Invalid parameter.');
		}

		if (count($this->errors)) {
			$result = [
				'success' => false,
				'message' => implode(PHP_EOL, $this->errors),
			];
		} else {
			$result = [
				'success' => true,
				'message' => $this->_conf[15],
			];

		}

		die(Tools::jsonEncode($result));
	}

	/**
	 * Read the Post var and write the translation file.
	 * This method overwrites the old translation file.
	 *
	 * @param bool $overrideFile Set true if this file is a override
	 *
	 * @throws PhenyxShopException
	 *
	 * @return void
	 *
	 * @since 1.00
	 */
	protected function writeTranslationFile($overrideFile = false) {

		$this->type_selected = Tools::getValue('type');

		if (isset($this->translations_informations[$this->type_selected])) {
			$translationInformation = $this->translations_informations[$this->type_selected];
		} else {
			return false;
		}

		if ($overrideFile) {
			$filePath = $translationInformation['override']['dir'] . $translationInformation['override']['file'];
		} else {
			$filePath = $translationInformation['dir'] . $translationInformation['file'];
		}

		if ($filePath && !file_exists($filePath)) {

			if (!file_exists(dirname($filePath)) && !mkdir(dirname($filePath), 0777, true)) {
				throw new PhenyxShopException(sprintf(Tools::displayError('Directory "%s" cannot be created'), dirname($filePath)));
			} else

			if (!touch($filePath)) {
				throw new PhenyxShopException(sprintf(Tools::displayError('File "%s" cannot be created'), $filePath));
			}

		}

		$thmName = str_replace('.', '', Tools::getValue('theme'));
		$kpiKey = substr(strtoupper($thmName . '_' . Tools::getValue('lang')), 0, 16);

		if ($fd = fopen($filePath, 'w')) {
			// Get value of button save and stay
			$saveAndStay = Tools::isSubmit('submitTranslations' . $type . 'AndStay');

			// Get language
			$lang = strtolower(Tools::getValue('lang'));

			// Unset all POST which are not translations
			unset(
				$_POST['submitTranslations' . $type],
				$_POST['submitTranslations' . $type . 'AndStay'],
				$_POST['lang'],
				$_POST['token'],
				$_POST['theme'],
				$_POST['type']
			);

			// Get all POST which aren't empty
			$toInsert = [];

			foreach ($_POST as $key => $value) {

				if (!empty($value)) {
					$toInsert[$key] = $value;
				}

			}

			ConfigurationKPI::updateValue('FRONTOFFICE_TRANSLATIONS_EXPIRE', time());
			ConfigurationKPI::updateValue('TRANSLATE_TOTAL_' . $kpiKey, count($_POST));
			ConfigurationKPI::updateValue('TRANSLATE_DONE_' . $kpiKey, count($toInsert));

			// translations array is ordered by key (easy merge)
			ksort($toInsert);
			$tab = $translationInformation['var'];
			fwrite($fd, "<?php\n\nglobal \$" . $tab . ";\n\$" . $tab . " = array();\n");

			foreach ($toInsert as $key => $value) {
				fwrite($fd, '$' . $tab . '[\'' . pSQL($key, true) . '\'] = \'' . pSQL($value, true) . '\';' . "\n");
			}

			fwrite($fd, "\n?>");
			fclose($fd);

			// Redirect
			return true;
		} else {
			throw new PhenyxShopException(sprintf(Tools::displayError('Cannot write this file: "%s"'), $filePath));
		}

	}

	/**
	 * This method is used to write translation for mails.
	 * This writes subject translation files
	 * (in root/mails/lang_choosen/lang.php or root/_PS_THEMES_DIR_/mails/lang_choosen/lang.php)
	 * and mails files.
	 *
	 * @return void
	 * @throws PhenyxShopException
	 * @since 1.8.5.0
	 */
	protected function submitTranslationsMails() {

		$arrMailContent = [];
		$arrMailPath = [];

		if (Tools::getValue('core_mail')) {
			$arrMailContent['core_mail'] = Tools::getValue('core_mail');

			// Get path of directory for find a good path of translation file

			if (!$this->theme_selected) {
				$arrMailPath['core_mail'] = $this->translations_informations[$this->type_selected]['dir'];
			} else {
				$arrMailPath['core_mail'] = $this->translations_informations[$this->type_selected]['override']['dir'];
			}

		}

		if (Tools::getValue('module_mail')) {
			$arrMailContent['module_mail'] = Tools::getValue('module_mail');

			// Get path of directory for find a good path of translation file

			if (!$this->theme_selected) {
				$arrMailPath['module_mail'] = $this->translations_informations['modules']['dir'] . '{module}/mails/' . $this->lang_selected->iso_code . '/';
			} else {
				$arrMailPath['module_mail'] = $this->translations_informations['modules']['override']['dir'] . '{module}/mails/' . $this->lang_selected->iso_code . '/';
			}

		}

		// Save each mail content

		foreach ($arrMailContent as $groupName => $allContent) {

			foreach ($allContent as $typeContent => $mails) {

				foreach ($mails as $mailName => $content) {
					$moduleName = false;
					$moduleNamePipePos = stripos($mailName, '|');

					if ($moduleNamePipePos) {
						$moduleName = substr($mailName, 0, $moduleNamePipePos);

						if (!Validate::isModuleName($moduleName)) {
							throw new PhenyxShopException(sprintf(Tools::displayError('Invalid module name "%s"'), Tools::safeOutput($moduleName)));
						}

						$mailName = substr($mailName, $moduleNamePipePos + 1);

						if (!Validate::isTplName($mailName)) {
							throw new PhenyxShopException(sprintf(Tools::displayError('Invalid mail name "%s"'), Tools::safeOutput($mailName)));
						}

					}

					if ($typeContent == 'html') {
						$content = Tools::htmlentitiesUTF8($content);
						$content = htmlspecialchars_decode($content);
						// replace correct end of line
						$content = str_replace("\r\n", PHP_EOL, $content);

						$title = '';

						if (Tools::getValue('title_' . $groupName . '_' . $mailName)) {
							$title = Tools::getValue('title_' . $groupName . '_' . $mailName);
						}

						// Magic Quotes shall... not.. PASS!

						if (_PS_MAGIC_QUOTES_GPC_) {
							$content = stripslashes($content);
						}

						$content = preg_replace('/<title>.*<\/title>/', '<title>' . $title . '</title>', $content);
					}

					if (Validate::isCleanHTML($content)) {
						$path = $arrMailPath[$groupName];

						if ($moduleName) {
							$path = str_replace('{module}', $moduleName, $path);
						}

						if (!file_exists($path) && !mkdir($path, 0777, true)) {
							throw new PhenyxShopException(sprintf(Tools::displayError('Directory "%s" cannot be created'), dirname($path)));
						}

						file_put_contents($path . $mailName . '.' . $typeContent, $content);
					} else {
						throw new PhenyxShopException(Tools::displayError('Your HTML email templates cannot contain JavaScript code.'));
					}

				}

			}

		}

		// Update subjects
		$arraySubjects = [];

		if (($subjects = Tools::getValue('subject')) && is_array($subjects)) {
			$arraySubjects['core_and_modules'] = ['translations' => [], 'path' => $arrMailPath['core_mail'] . 'lang.php'];

			foreach ($subjects as $subjectTranslation) {
				$arraySubjects['core_and_modules']['translations'] = array_merge($arraySubjects['core_and_modules']['translations'], $subjectTranslation);
			}

		}

		if (!empty($arraySubjects)) {

			foreach ($arraySubjects as $infos) {
				$this->writeSubjectTranslationFile($infos['translations'], $infos['path']);
			}

		}

		if (Tools::isSubmit('submitTranslationsMailsAndStay')) {
			$this->redirect(true);
		} else {
			$this->redirect();
		}

	}

	/**
	 * @param $sub
	 * @param $path
	 *
	 * @throws PhenyxShopException
	 *
	 * @return void
	 *
	 * @since 1.8.5.0
	 */
	protected function writeSubjectTranslationFile($sub, $path) {

		if (!file_exists(dirname($path))) {

			if (!mkdir(dirname($path), 0700)) {
				throw new PhenyxShopException('Directory ' . dirname($path) . ' cannot be created.');
			}

		}

		if ($fd = @fopen($path, 'w')) {
			$tab = 'LANGMAIL';
			fwrite($fd, "<?php\n\nglobal \$_" . $tab . ";\n\$_" . $tab . " = array();\n");

			foreach ($sub as $key => $value) {
				// Magic Quotes shall... not.. PASS!

				if (_PS_MAGIC_QUOTES_GPC_) {
					$value = stripslashes($value);
				}

				fwrite($fd, '$_' . $tab . '[\'' . pSQL($key) . '\'] = \'' . pSQL($value) . '\';' . "\n");
			}

			fwrite($fd, "\n?>");
			fclose($fd);
		} else {
			throw new PhenyxShopException(sprintf(Tools::displayError('Cannot write language file for email subjects. Path is: %s'), $path));
		}

	}

	/**
	 * Check if directory and file exist and return an list of modules
	 *
	 * @return array List of modules
	 * @throws PhenyxShopException
	 *
	 * @since 1.8.5.0
	 */
	public function getListModules() {

		if (!file_exists($this->translations_informations['modules']['dir'])) {
			throw new PhenyxShopException(Tools::displayError('Fatal error: The module directory does not exist.') . '(' . $this->translations_informations['modules']['dir'] . ')');
		}

		if (!is_writable($this->translations_informations['modules']['dir'])) {
			throw new PhenyxShopException(Tools::displayError('The module directory must be writable.'));
		}

		// Get all module which are installed for to have a minimum of POST
		$modules = Module::getModulesInstalled();

		foreach ($modules as &$module) {
			$module = $module['name'];
		}

		return $modules;
	}

	/**
	 * This method get translation in each translations file.
	 * The file depend on $lang param.
	 *
	 * @param array       $modules    List of modules
	 * @param string|null $rootDir    path where it get each modules
	 * @param string      $lang       ISO code of chosen language to translate
	 * @param bool        $isDefault  Set it if modules are located in root/PhenyxShop/modules folder
	 *                                This allow to distinguish overridden PhenyxShop theme and original module
	 *
	 * @return array
	 *
	 * @since 1.8.5.0
	 */
	protected function getAllModuleFiles($modules, $rootDir = null, $lang, $isDefault = false) {

		$arrayFiles = [];
		$initialRootDir = $rootDir;

		foreach ($modules as $module) {
			$rootDir = $initialRootDir;

			if ($module[0] == '.') {
				continue;
			}

			// First we load the default translation file

			if ($rootDir == null) {
				$i18NDir = $this->translations_informations[$this->type_selected]['dir'];

				if (is_dir($i18NDir . $module)) {
					$rootDir = $i18NDir;
				}

				$langFile = $rootDir . $module . '/translations/' . $lang . '.php';

				if (!file_exists($rootDir . $module . '/translations/' . $lang . '.php') && file_exists($rootDir . $module . '/' . $lang . '.php')) {
					$langFile = $rootDir . $module . '/' . $lang . '.php';
				}

				@include $langFile;
				$this->getModuleTranslations();
				// If a theme is selected, then the destination translation file must be in the theme

				if ($this->theme_selected) {
					$langFile = $this->translations_informations[$this->type_selected]['override']['dir'] . $module . '/translations/' . $lang . '.php';
				}

				$this->recursiveGetModuleFiles($rootDir . $module . '/', $arrayFiles, $module, $langFile, $isDefault);
			}

			$rootDir = $initialRootDir;
			// Then we load the overriden translation file

			if ($this->theme_selected && isset($this->translations_informations[$this->type_selected]['override'])) {
				$i18NDir = $this->translations_informations[$this->type_selected]['override']['dir'];

				if (is_dir($i18NDir . $module)) {
					$rootDir = $i18NDir;
				}

				if (file_exists($rootDir . $module . '/translations/' . $lang . '.php')) {
					$langFile = $rootDir . $module . '/translations/' . $lang . '.php';
				} else

				if (file_exists($rootDir . $module . '/' . $lang . '.php')) {
					$langFile = $rootDir . $module . '/' . $lang . '.php';
				}

				@include $langFile;
				$this->getModuleTranslations();
				$this->recursiveGetModuleFiles($rootDir . $module . '/', $arrayFiles, $module, $langFile, $isDefault);
			}

		}

		return $arrayFiles;
	}

	/**
	 * This method merge each arrays of modules translation in the array of modules translations
	 *
	 * @return void
	 *
	 * @since 1.8.5.0
	 */
	protected function getModuleTranslations() {

		global $_MODULE;
		$nameVar = $this->translations_informations[$this->type_selected]['var'];

		if (!isset($_MODULE) && !isset($GLOBALS[$nameVar])) {
			$GLOBALS[$nameVar] = [];
		} else

		if (isset($_MODULE)) {

			if (is_array($GLOBALS[$nameVar]) && is_array($_MODULE)) {
				$GLOBALS[$nameVar] = array_merge($GLOBALS[$nameVar], $_MODULE);
			} else {
				$GLOBALS[$nameVar] = $_MODULE;
			}

		}

	}

	/**
	 * This get files to translate in module directory.
	 * Recursive method allow to get each files for a module no matter his depth.
	 *
	 * @param string $path       directory path to scan
	 * @param array  $arrayFiles by reference - array which saved files to parse.
	 * @param string $moduleName module name
	 * @param string $langFile   full path of translation file
	 * @param bool   $isDefault
	 *
	 * @return void
	 *
	 * @since 1.8.5.0
	 */
	protected function recursiveGetModuleFiles($path, &$arrayFiles, $moduleName, $langFile, $isDefault = false) {

		$filesModule = [];

		if (file_exists($path)) {
			$filesModule = scandir($path);
		}

		$filesForModule = $this->clearModuleFiles($filesModule, 'file');

		if (!empty($filesForModule)) {
			$arrayFiles[] = [
				'file_name'  => $langFile,
				'dir'        => $path,
				'files'      => $filesForModule,
				'module'     => $moduleName,
				'is_default' => $isDefault,
				'theme'      => $this->theme_selected,
			];
		}

		$dirModule = $this->clearModuleFiles($filesModule, 'directory', $path);

		if (!empty($dirModule)) {

			foreach ($dirModule as $folder) {
				$this->recursiveGetModuleFiles($path . $folder . '/', $arrayFiles, $moduleName, $langFile, $isDefault);
			}

		}

	}

	/**
	 * Clear the list of module file by type (file or directory)
	 *
	 * @param        $files     : list of files
	 * @param string $typeClear (file|directory)
	 * @param string $path
	 *
	 * @return array : list of good files
	 *
	 * @since 1.8.5.0
	 */
	public function clearModuleFiles($files, $typeClear = 'file', $path = '') {

		// List of directory which not must be parsed
		$arrExclude = ['img', 'js', 'mails', 'override'];

		// List of good extension files
		$arrGoodExt = ['.tpl', '.php'];

		foreach ($files as $key => $file) {

			if ($file[0] === '.' || in_array(substr($file, 0, strrpos($file, '.')), $this->all_iso_lang)) {
				unset($files[$key]);
			} else

			if ($typeClear === 'file' && !in_array(substr($file, strrpos($file, '.')), $arrGoodExt)) {
				unset($files[$key]);
			} else

			if ($typeClear === 'directory' && (!is_dir($path . $file) || in_array($file, $arrExclude))) {
				unset($files[$key]);
			}

		}

		return $files;
	}

	/**
	 * This method check each file (tpl or php file), get its sentences to translate,
	 * compare with posted values and write in iso code translation file.
	 *
	 * @param string      $fileName
	 * @param array       $files
	 * @param string      $themeName
	 * @param string      $moduleName
	 * @param string|bool $dir
	 *
	 * @throws PhenyxShopException
	 *
	 * @return void
	 *
	 * @since 1.8.5.0
	 */
	protected function findAndWriteTranslationsIntoFile($fileName, $files, $themeName, $moduleName, $dir = false) {

		// These static vars allow to use file to write just one time.
		static $cacheFile = [];
		static $strWrite = '';
		static $arrayCheckDuplicate = [];

		// Set file_name in static var, this allow to open and wright the file just one time

		if (!isset($cacheFile[$themeName . '-' . $fileName])) {
			$strWrite = '';
			$cacheFile[$themeName . '-' . $fileName] = true;

			if (!file_exists(dirname($fileName))) {
				mkdir(dirname($fileName), 0777, true);
			}

			if (!file_exists($fileName)) {
				file_put_contents($fileName, '');
			}

			if (!is_writable($fileName)) {
				throw new PhenyxShopException(
					sprintf(
						Tools::displayError('Cannot write to the theme\'s language file (%s). Please check writing permissions.'),
						$fileName
					)
				);
			}

			// this string is initialized one time for a file
			$strWrite .= "<?php\n\nglobal \$_MODULE;\n\$_MODULE = array();\n";
			$arrayCheckDuplicate = [];
		}

		foreach ($files as $file) {

			if (preg_match('/^(.*)\.(tpl|php)$/', $file) && file_exists($dir . $file) && !in_array($file, static::$ignore_folder)) {
				// Get content for this file
				$content = file_get_contents($dir . $file);

				// Get file type
				$typeFile = substr($file, -4) == '.tpl' ? 'tpl' : 'php';

				// Parse this content
				$matches = $this->userParseFile($content, $this->type_selected, $typeFile, $moduleName);

				// Write each translation on its module file
				$templateName = substr(basename($file), 0, -4);

				foreach ($matches as $key) {

					if ($themeName) {
						$postKey = md5(strtolower($moduleName) . '_' . strtolower($themeName) . '_' . strtolower($templateName) . '_' . md5($key));
						$pattern = '\'<{' . strtolower($moduleName) . '}' . strtolower($themeName) . '>' . strtolower($templateName) . '_' . md5($key) . '\'';
					} else {
						$postKey = md5(strtolower($moduleName) . '_' . strtolower($templateName) . '_' . md5($key));
						$pattern = '\'<{' . strtolower($moduleName) . '}PhenyxShop>' . strtolower($templateName) . '_' . md5($key) . '\'';
					}

					if (array_key_exists($postKey, $_POST) && !in_array($pattern, $arrayCheckDuplicate)) {

						if ($_POST[$postKey] == '') {
							continue;
						}

						$arrayCheckDuplicate[] = $pattern;
						$strWrite .= '$_MODULE[' . $pattern . '] = \'' . pSQL(str_replace(["\r\n", "\r", "\n"], ' ', $_POST[$postKey])) . '\';' . "\n";
						$this->total_expression++;
					}

				}

			}

		}

		if (isset($cacheFile[$themeName . '-' . $fileName]) && $strWrite != "<?php\n\nglobal \$_MODULE;\n\$_MODULE = array();\n") {
			file_put_contents($fileName, $strWrite);
		}

	}

	/**
	 * This method parse a file by type of translation and type file
	 *
	 * @param             $content
	 * @param             $typeTranslation : front, back, errors, modules...
	 * @param string|bool $typeFile        : (tpl|php)
	 * @param string      $moduleName      : name of the module
	 *
	 * @return array
	 *
	 * @since 1.8.5.0
	 */
	protected function userParseFile($content, $typeTranslation, $typeFile = false, $moduleName = '') {

		switch ($typeTranslation) {
		case 'front':
			// Parsing file in Front office
			$regex = '/\{l\s*s=([\'\"])' . _PS_TRANS_PATTERN_ . '\1(\s*sprintf=.*)?(\s*js=1)?\s*\}/U';
			break;

		case 'back':
			// Parsing file in Back office

			if ($typeFile == 'php') {
				$regex = '/this->l\((\')' . _PS_TRANS_PATTERN_ . '\'[\)|\,]/U';
			} else

			if ($typeFile == 'specific') {
				$regex = '/Translate::getAdminTranslation\((\')' . _PS_TRANS_PATTERN_ . '\'(?:,.*)*\)/U';
			} else {
				$regex = '/\{l\s*s\s*=([\'\"])' . _PS_TRANS_PATTERN_ . '\1(\s*sprintf=.*)?(\s*js=1)?(\s*slashes=1)?.*\}/U';
			}

			break;

		case 'frontcontroller':
			// Parsing file in Back office

			if ($typeFile == 'php') {
				$regex = '/this->l\((\')' . _PS_TRANS_PATTERN_ . '\'[\)|\,]/U';
			} else

			if ($typeFile == 'specific') {
				$regex = '/Translate::getFrontTranslation\((\')' . _PS_TRANS_PATTERN_ . '\'(?:,.*)*\)/U';
			} else {
				$regex = '/\{l\s*s\s*=([\'\"])' . _PS_TRANS_PATTERN_ . '\1(\s*sprintf=.*)?(\s*js=1)?(\s*slashes=1)?.*\}/U';
			}

			break;
		case 'class':

			$regex = '/this->l\((\')' . _PS_TRANS_PATTERN_ . '\'[\)|\,]/U';

			break;

		case 'errors':
			// Parsing file for all errors syntax
			$regex = '/Tools::displayError\((\')' . _PS_TRANS_PATTERN_ . '\'(,\s*(.+))?\)/U';
			break;

		case 'modules':
			// Parsing modules file

			if ($typeFile == 'php') {
				$regex = '/->l\((\')' . _PS_TRANS_PATTERN_ . '\'(, ?\'(.+)\')?(, ?(.+))?\)/U';
			} else {
				// In tpl file look for something that should contain mod='module_name' according to the documentation
				$regex = '/\{l\s*s=([\'\"])' . _PS_TRANS_PATTERN_ . '\1.*\s+mod=\'' . $moduleName . '\'.*\}/U';
			}

			break;

		case 'pdf':
			// Parsing PDF file

			if ($typeFile == 'php') {
				$regex = [
					'/HTMLTemplate.*::l\((\')' . _PS_TRANS_PATTERN_ . '\'[\)|\,]/U',
					'/->l\((\')' . _PS_TRANS_PATTERN_ . '\'(, ?\'(.+)\')?(, ?(.+))?\)/U',
				];
			} else {
				$regex = '/\{l\s*s=([\'\"])' . _PS_TRANS_PATTERN_ . '\1(\s*sprintf=.*)?(\s*js=1)?(\s*pdf=\'true\')?\s*\}/U';
			}

			break;
		}

		if (!is_array($regex)) {
			$regex = [$regex];
		}

		$strings = [];

		foreach ($regex as $regexRow) {
			$matches = [];
			$n = preg_match_all($regexRow, $content, $matches);

			for ($i = 0; $i < $n; $i += 1) {
				$quote = $matches[1][$i];
				$string = $matches[2][$i];

				if ($quote === '"') {
					// Escape single quotes because the core will do it when looking for the translation of this string
					$string = str_replace('\'', '\\\'', $string);
					// Unescape double quotes
					$string = preg_replace('/\\\\+"/', '"', $string);
				}

				$strings[] = $string;
			}

		}

		return array_unique($strings);
	}

	/**
	 * This method generate the form for front translations
	 *
	 * @retunr void
	 *
	 * @since 1.8.5.0
	 */
	public function initFormFront($excel = false) {

		if (!$this->theme_exists(Tools::getValue('theme'))) {
			$this->errors[] = sprintf(Tools::displayError('Invalid theme "%s"'), Tools::getValue('theme'));

			return;
		}

		$missingTranslationsFront = [];
		$nameVar = $this->translations_informations[$this->type_selected]['var'];
		$GLOBALS[$nameVar] = $this->fileExists();

		/* List templates to parse */
		$filesByDirectory = $this->getFileToParseByTypeTranslation();
		$count = 0;
		$tabsArray = [];

		foreach ($filesByDirectory['tpl'] as $dir => $files) {
			$prefix = '';

			if ($dir == _PS_THEME_OVERRIDE_DIR_) {
				$prefix = 'override_';
			}

			foreach ($files as $file) {

				if (preg_match('/^(.*).tpl$/', $file) && (file_exists($filePath = $dir . $file))) {
					$prefixKey = $prefix . substr(basename($file), 0, -4);
					$newLang = [];

					// Get content for this file
					$content = file_get_contents($filePath);

					// Parse this content
					$matches = $this->userParseFile($content, $this->type_selected);

					/* Get string translation */

					foreach ($matches as $key) {

						if (empty($key)) {
							$this->errors[] = sprintf($this->l('Empty string found, please edit: "%s"'), $filePath);
							$newLang[$key] = '';
						} else {
							// Caution ! front has underscore between prefix key and md5, back has not

							if (isset($GLOBALS[$nameVar][$prefixKey . '_' . md5($key)])) {
								$newLang[$key]['trad'] = stripslashes(html_entity_decode($GLOBALS[$nameVar][$prefixKey . '_' . md5($key)], ENT_COMPAT, 'UTF-8'));
							} else {

								if (!isset($newLang[$key]['trad'])) {
									$newLang[$key]['trad'] = '';

									if (!isset($missingTranslationsFront[$prefixKey])) {
										$missingTranslationsFront[$prefixKey] = 1;
									} else {
										$missingTranslationsFront[$prefixKey]++;
									}

								}

							}

							$newLang[$key]['use_sprintf'] = $this->checkIfKeyUseSprintf($key);
						}

					}

					if (isset($tabsArray[$prefixKey])) {
						$tabsArray[$prefixKey] = array_merge($tabsArray[$prefixKey], $newLang);
					} else {
						$tabsArray[$prefixKey] = $newLang;
					}

					$count += count($newLang);
				}

			}

		}

		if ($this->compile_grid) {

			return [
				'tabsArray'           => $tabsArray,
				'missingTranslations' => $missingTranslationsFront,
			];
		}

		if ($this->compile_ajax) {
			return $tabsArray;
		}

		if ($excel) {
			$this->returnSpreadSheet($tabsArray, $missingTranslationsFront);
		}

		$this->tpl_view_vars = array_merge(
			$this->tpl_view_vars,
			[
				'missing_translations' => $missingTranslationsFront,
				'count'                => $count,
				'cancel_url'           => $this->context->link->getAdminLink('AdminTranslations'),
				'limit_warning'        => $this->displayLimitPostWarning($count),
				'mod_security_warning' => Tools::apacheModExists('mod_security'),
				'tabsArray'            => $tabsArray,
			]
		);

		$this->initToolbar();
		$this->base_tpl_view = 'translation_form.tpl';

		return parent::renderView();
	}

	/**
	 * Include file $dir/$file and return the var $var declared in it.
	 * This create the file if not exists
	 *
	 * @return array : translations
	 *
	 * @since 1.8.5.0
	 */
	public function fileExists() {

		$var = $this->translations_informations[$this->type_selected]['var'];
		$dir = $this->translations_informations[$this->type_selected]['dir'];
		$file = $this->translations_informations[$this->type_selected]['file'];

		$$var = [];

		if (!file_exists($dir)) {

			if (!mkdir($dir, 0700)) {
				throw new PhenyxShopException('Directory ' . $dir . ' cannot be created.');
			}

		}

		if (!file_exists($dir . DIRECTORY_SEPARATOR . $file)) {

			if (!file_put_contents($dir . '/' . $file, "<?php\n\nglobal \$" . $var . ";\n\$" . $var . " = array();\n\n?>")) {
				throw new PhenyxShopException('File "' . $file . '" doesn\'t exists and cannot be created in ' . $dir);
			}

		}

		if (!is_writable($dir . DIRECTORY_SEPARATOR . $file)) {
			$this->displayWarning(Tools::displayError('This file must be writable:') . ' ' . $dir . '/' . $file);
		}

		include $dir . DIRECTORY_SEPARATOR . $file;

		return $$var;
	}

	/**
	 * Get list of files which must be parsed by directory and by type of translations
	 *
	 * @return array : list of files by directory
	 *
	 * @since 1.8.5.0
	 */
	public function getFileToParseByTypeTranslation() {

		$directories = [];

		switch ($this->type_selected) {
		case 'front':
			$directories['tpl'] = [_PS_ALL_THEMES_DIR_ => scandir(_PS_ALL_THEMES_DIR_)];
			static::$ignore_folder[] = 'modules';
			$directories['tpl'] = array_merge($directories['tpl'], $this->listFiles(_PS_THEME_SELECTED_DIR_));

			if (isset($directories['tpl'][_PS_THEME_SELECTED_DIR_ . 'pdf/'])) {
				unset($directories['tpl'][_PS_THEME_SELECTED_DIR_ . 'pdf/']);
			}

			if (file_exists(_PS_THEME_OVERRIDE_DIR_)) {
				$directories['tpl'] = array_merge($directories['tpl'], $this->listFiles(_PS_THEME_OVERRIDE_DIR_));
			}

			break;

		case 'back':
			$directories = [
				'php'      => [
					_PS_ADMIN_CONTROLLER_DIR_                => scandir(_PS_ADMIN_CONTROLLER_DIR_),
					_PS_OVERRIDE_DIR_ . 'controllers/admin/' => scandir(_PS_OVERRIDE_DIR_ . 'controllers/admin/'),
					_PS_CLASS_DIR_ . 'helper/'               => scandir(_PS_CLASS_DIR_ . 'helper/'),
					_PS_CLASS_DIR_ . 'controller/'           => ['AdminController.php'],
					_PS_CLASS_DIR_                           => ['PaymentModule.php'],
				],
				'tpl'      => $this->listFiles(_PS_ADMIN_DIR_ . DIRECTORY_SEPARATOR . 'themes/'),
				'specific' => [
					_PS_ADMIN_DIR_ . DIRECTORY_SEPARATOR => [
						'header.inc.php',
						'footer.inc.php',
						'index.php',
						'functions.php',
					],
				],
			];

			// For translate the template which are overridden

			if (file_exists(_PS_OVERRIDE_DIR_ . 'controllers' . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'templates')) {
				$directories['tpl'] = array_merge($directories['tpl'], $this->listFiles(_PS_OVERRIDE_DIR_ . 'controllers' . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'templates'));
			}

			break;

		case 'frontcontroller':
			$directories = [
				'php' => [
					_PS_FRONT_CONTROLLER_DIR_                => scandir(_PS_FRONT_CONTROLLER_DIR_),
					_PS_OVERRIDE_DIR_ . 'controllers/front/' => scandir(_PS_OVERRIDE_DIR_ . 'controllers/front/'),
					_PS_CLASS_DIR_ . 'controller/'           => ['FrontController.php'],
				],
			];
			break;

		case 'class':
			$directories = [
				'php' => [
					_PS_CLASS_DIR_                           => scandir(_PS_CLASS_DIR_),
					_PS_OVERRIDE_DIR_ . 'controllers/class/' => scandir(_PS_OVERRIDE_DIR_ . 'controllers/class/'),
				],
			];
			break;

		case 'errors':
			$directories['php'] = [
				_PS_ROOT_DIR_                            => scandir(_PS_ROOT_DIR_),
				_PS_ADMIN_DIR_ . DIRECTORY_SEPARATOR     => scandir(_PS_ADMIN_DIR_ . DIRECTORY_SEPARATOR),
				_PS_FRONT_CONTROLLER_DIR_                => scandir(_PS_FRONT_CONTROLLER_DIR_),
				_PS_ADMIN_CONTROLLER_DIR_                => scandir(_PS_ADMIN_CONTROLLER_DIR_),
				_PS_OVERRIDE_DIR_ . 'controllers/front/' => scandir(_PS_OVERRIDE_DIR_ . 'controllers/front/'),
				_PS_OVERRIDE_DIR_ . 'controllers/admin/' => scandir(_PS_OVERRIDE_DIR_ . 'controllers/admin/'),
			];

			// Get all files for folders classes/ and override/classes/ recursively
			$directories['php'] = array_merge($directories['php'], $this->listFiles(_PS_CLASS_DIR_, [], 'php'));
			$directories['php'] = array_merge($directories['php'], $this->listFiles(_PS_OVERRIDE_DIR_ . 'classes/', [], 'php'));
			break;

		case 'fields':
			$directories['php'] = $this->listFiles(_PS_CLASS_DIR_, [], 'php');
			break;

		case 'pdf':
			$tplTheme = file_exists(_PS_THEME_SELECTED_DIR_ . 'pdf/') ? scandir(_PS_THEME_SELECTED_DIR_ . 'pdf/') : [];
			$directories = [
				'php' => [
					_PS_CLASS_DIR_ . 'pdf/'            => scandir(_PS_CLASS_DIR_ . 'pdf/'),
					_PS_OVERRIDE_DIR_ . 'classes/pdf/' => scandir(_PS_OVERRIDE_DIR_ . 'classes/pdf/'),
				],
				'tpl' => [
					_PS_PDF_DIR_                     => scandir(_PS_PDF_DIR_),
					_PS_THEME_SELECTED_DIR_ . 'pdf/' => $tplTheme,
				],
			];
			$directories['tpl'] = array_merge($directories['tpl'], $this->getModulesHasPDF());
			$directories['php'] = array_merge($directories['php'], $this->getModulesHasPDF(true));
			break;

		case 'mails':
			$directories['php'] = [
				_PS_FRONT_CONTROLLER_DIR_                      => scandir(_PS_FRONT_CONTROLLER_DIR_),
				_PS_ADMIN_CONTROLLER_DIR_                      => scandir(_PS_ADMIN_CONTROLLER_DIR_),
				_PS_OVERRIDE_DIR_ . 'controllers/front/'       => scandir(_PS_OVERRIDE_DIR_ . 'controllers/front/'),
				_PS_OVERRIDE_DIR_ . 'controllers/admin/'       => scandir(_PS_OVERRIDE_DIR_ . 'controllers/admin/'),
				_PS_ADMIN_DIR_ . DIRECTORY_SEPARATOR           => scandir(_PS_ADMIN_DIR_ . DIRECTORY_SEPARATOR),
				_PS_ADMIN_DIR_ . DIRECTORY_SEPARATOR . 'tabs/' => scandir(_PS_ADMIN_DIR_ . DIRECTORY_SEPARATOR . '/tabs'),
			];

			// Get all files for folders classes/ and override/classes/ recursively
			$directories['php'] = array_merge($directories['php'], $this->listFiles(_PS_CLASS_DIR_, [], 'php'));
			$directories['php'] = array_merge($directories['php'], $this->listFiles(_PS_OVERRIDE_DIR_ . 'classes/', [], 'php'));
			$directories['php'] = array_merge($directories['php'], $this->getModulesHasMails());
			break;

		}

		return $directories;
	}

	/**
	 * Recursively list files in directory $dir
	 *
	 * @param string $dir
	 * @param array  $list
	 * @param string $fileExt
	 *
	 * @return array
	 *
	 * @since 1.8.5.0
	 */
	public function listFiles($dir, $list = [], $fileExt = 'tpl') {

		$dir = rtrim($dir, '/') . DIRECTORY_SEPARATOR;

		$toParse = scandir($dir);
		// copied (and kind of) adapted from AdminImages.php

		foreach ($toParse as $file) {

			if (!in_array($file, static::$ignore_folder)) {

				if (preg_match('#' . preg_quote($fileExt, '#') . '$#i', $file)) {
					$list[$dir][] = $file;
				} else

				if (is_dir($dir . $file)) {
					$list = $this->listFiles($dir . $file, $list, $fileExt);
				}

			}

		}

		return $list;
	}

	/**
	 * Check in each module if contains pdf folder.
	 *
	 * @param bool $classes
	 *
	 * @return array Array of modules which have pdf
	 *
	 * @since 1.8.5.0
	 */
	public function getModulesHasPDF($classes = false) {

		$arrModules = [];

		foreach (scandir($this->translations_informations['modules']['dir']) as $moduleDir) {

			if (!in_array($moduleDir, static::$ignore_folder)) {
				$dir = false;

				if ($classes) {

					if ($this->theme_selected && file_exists($this->translations_informations['modules']['override']['dir'] . $moduleDir . '/classes/')) {
						$dir = $this->translations_informations['modules']['override']['dir'] . $moduleDir . '/classes/';
					} else

					if (file_exists($this->translations_informations['modules']['dir'] . $moduleDir . '/classes/')) {
						$dir = $this->translations_informations['modules']['dir'] . $moduleDir . '/classes/';
					}

					if ($dir !== false) {
						$arrModules[$dir] = scandir($dir);
					}

				} else {

					if ($this->theme_selected && file_exists($this->translations_informations['modules']['override']['dir'] . $moduleDir . '/pdf/')) {
						$dir = $this->translations_informations['modules']['override']['dir'] . $moduleDir . '/pdf/';
					} else

					if (file_exists($this->translations_informations['modules']['dir'] . $moduleDir . '/pdf/')) {
						$dir = $this->translations_informations['modules']['dir'] . $moduleDir . '/pdf/';
					}

					if ($dir !== false) {
						$arrModules[$dir] = scandir($dir);
					}

				}

			}

		}

		return $arrModules;
	}

	/**
	 * Check in each module if contains mails folder.
	 *
	 * @param bool $withModuleName
	 *
	 * @return array Array of modules which have mails
	 *
	 * @since 1.8.5.0
	 */
	public function getModulesHasMails($withModuleName = false) {

		$arrModules = [];

		foreach (scandir($this->translations_informations['modules']['dir']) as $moduleDir) {

			if (!in_array($moduleDir, static::$ignore_folder)) {
				$dir = false;

				if ($this->theme_selected && file_exists($this->translations_informations['modules']['override']['dir'] . $moduleDir . '/mails/')) {
					$dir = $this->translations_informations['modules']['override']['dir'] . $moduleDir . '/';
				} else

				if (file_exists($this->translations_informations['modules']['dir'] . $moduleDir . '/mails/')) {
					$dir = $this->translations_informations['modules']['dir'] . $moduleDir . '/';
				}

				if ($dir !== false) {

					if ($withModuleName) {
						$arrModules[$moduleDir] = $dir;
					} else {

						if ($this->theme_selected) {
							$dir = $this->translations_informations['modules']['dir'] . $moduleDir . '/';
						}

						$arrModules[$dir] = scandir($dir);
					}

				}

			}

		}

		return $arrModules;
	}

	/**
	 * Find sentence which use %d, %s, %%, %1$d, %1$s...
	 *
	 * @param $key : english sentence
	 *
	 * @return array|bool return list of matches
	 *
	 * @since 1.8.5.0
	 */
	public function checkIfKeyUseSprintf($key) {

		if (preg_match_all('#(?:%%|%(?:[0-9]+\$)?[+-]?(?:[ 0]|\'.)?-?[0-9]*(?:\.[0-9]+)?[bcdeufFosxX])#', $key, $matches)) {
			return implode(', ', $matches[0]);
		}

		return false;
	}

	/**
	 * @param int $count
	 *
	 * @return array
	 *
	 * @since 1.8.5.0
	 */
	public function displayLimitPostWarning($count) {

		$return = [];

		if ((ini_get('suhosin.post.max_vars') && ini_get('suhosin.post.max_vars') < $count) || (ini_get('suhosin.request.max_vars') && ini_get('suhosin.request.max_vars') < $count)) {
			$return['error_type'] = 'suhosin';
			$return['post.max_vars'] = ini_get('suhosin.post.max_vars');
			$return['request.max_vars'] = ini_get('suhosin.request.max_vars');
			$return['needed_limit'] = $count + 100;
		} else

		if (ini_get('max_input_vars') && ini_get('max_input_vars') < $count) {
			$return['error_type'] = 'conf';
			$return['max_input_vars'] = ini_get('max_input_vars');
			$return['needed_limit'] = $count + 100;
		}

		return $return;
	}

	/**
	 * AdminController::initToolbar() override
	 *
	 * @see AdminController::initToolbar()
	 *
	 * @return void
	 *
	 * @since 1.8.5.0
	 */
	public function initToolbar() {

		$this->toolbar_btn['save-and-stay'] = [
			'short' => 'SaveAndStay',
			'href'  => '#',
			'desc'  => $this->l('Save and stay'),
		];
		$this->toolbar_btn['save'] = [
			'href' => '#',
			'desc' => $this->l('Update translations'),
		];
		$this->toolbar_btn['cancel'] = [
			'href' => static::$currentIndex . '&token=' . $this->token,
			'desc' => $this->l('Cancel'),
		];
	}

	/**
	 * This method generate the form for back translations
	 *
	 * @return string
	 *
	 * @since 1.8.5.0
	 */

	public function initPrefixBack() {

		$nameVar = $this->translations_informations[$this->type_selected]['var'];
		$GLOBALS[$nameVar] = $this->fileExists();
		$prefix = [];

		$filesPerDirectory = $this->getFileToParseByTypeTranslation();

		foreach ($filesPerDirectory['php'] as $dir => $files) {

			foreach ($files as $file) {

				if (preg_match('/^(.*)\.php$/', $file) && file_exists($filePath = $dir . $file) && !in_array($file, static::$ignore_folder)) {
					$prefixKey = basename($file);

					if (strpos($file, 'Controller.php') !== false) {
						$prefixKey = basename(substr($file, 0, -14));
					} else

					if (strpos($file, 'Helper') !== false) {
						$prefixKey = 'Helper';
					}

					if ($prefixKey == 'Admin') {
						$prefixKey = 'AdminController';
					}

					if ($prefixKey == 'PaymentModule.php') {
						$prefixKey = 'PaymentModule';
					}

					$prefix[] = $prefixKey;

				}

			}

		}

		$prefix = array_unique($prefix);

	}

	public function initFormBack($excel = false) {

		$nameVar = $this->translations_informations[$this->type_selected]['var'];
		$GLOBALS[$nameVar] = $this->fileExists();
		$missingTranslationsBack = [];
		$missingTranslationsFound = [];
		$filesPerDirectory = $this->getFileToParseByTypeTranslation();

		foreach ($filesPerDirectory['php'] as $dir => $files) {

			foreach ($files as $file) {
				// Check if is a PHP file and if the override file exists

				if (preg_match('/^(.*)\.php$/', $file) && file_exists($filePath = $dir . $file) && !in_array($file, static::$ignore_folder)) {
					$prefixKey = basename($file);
					// -4 becomes -14 to remove the ending "Controller.php" from the filename

					if (strpos($file, 'Controller.php') !== false) {
						$prefixKey = basename(substr($file, 0, -14));
					} else

					if (strpos($file, 'Helper') !== false) {
						$prefixKey = 'Helper';
					}

					if ($prefixKey == 'Admin') {
						$prefixKey = 'AdminController';
					}

					if ($prefixKey == 'PaymentModule.php') {
						$prefixKey = 'PaymentModule';
					}

					// Get content for this file
					$content = file_get_contents($filePath);

					// Parse this content
					$matches = $this->userParseFile($content, $this->type_selected, 'php');

					foreach ($matches as $key) {
						// Caution ! front has underscore between prefix key and md5, back has not

						if (isset($GLOBALS[$nameVar][$prefixKey . md5($key)])) {
							$tabsArray[$prefixKey][$key]['trad'] = stripslashes(html_entity_decode($GLOBALS[$nameVar][$prefixKey . md5($key)], ENT_COMPAT, 'UTF-8'));
						} else {

							if (!isset($tabsArray[$prefixKey][$key]['trad'])) {
								$tabsArray[$prefixKey][$key]['trad'] = '';

								if (!isset($missingTranslationsBack[$prefixKey])) {
									$missingTranslationsBack[$prefixKey] = 1;
									$missingTranslationsFound[$prefixKey] = [];
									$missingTranslationsFound[$prefixKey][] = $key;
								} else

								if (!in_array($key, $missingTranslationsFound[$prefixKey])) {
									$missingTranslationsBack[$prefixKey]++;
									$missingTranslationsFound[$prefixKey][] = $key;
								}

							}

						}

						$tabsArray[$prefixKey][$key]['use_sprintf'] = $this->checkIfKeyUseSprintf($key);
					}

				}

			}

		}

		foreach ($filesPerDirectory['specific'] as $dir => $files) {

			foreach ($files as $file) {

				if (file_exists($filePath = $dir . $file) && !in_array($file, static::$ignore_folder)) {
					$prefixKey = 'index';

					// Get content for this file
					$content = file_get_contents($filePath);

					// Parse this content
					$matches = $this->userParseFile($content, $this->type_selected, 'specific');

					foreach ($matches as $key) {
						// Caution ! front has underscore between prefix key and md5, back has not

						if (isset($GLOBALS[$nameVar][$prefixKey . md5($key)])) {
							$tabsArray[$prefixKey][$key]['trad'] = stripslashes(html_entity_decode($GLOBALS[$nameVar][$prefixKey . md5($key)], ENT_COMPAT, 'UTF-8'));
						} else {

							if (!isset($tabsArray[$prefixKey][$key]['trad'])) {
								$tabsArray[$prefixKey][$key]['trad'] = '';

								if (!isset($missingTranslationsBack[$prefixKey])) {
									$missingTranslationsBack[$prefixKey] = 1;
									$missingTranslationsFound[$prefixKey] = [];
									$missingTranslationsFound[$prefixKey][] = $key;
								} else

								if (!in_array($key, $missingTranslationsFound[$prefixKey])) {
									$missingTranslationsBack[$prefixKey]++;
									$missingTranslationsFound[$prefixKey][] = $key;
								}

							}

						}

						$tabsArray[$prefixKey][$key]['use_sprintf'] = $this->checkIfKeyUseSprintf($key);
					}

				}

			}

		}

		foreach ($filesPerDirectory['tpl'] as $dir => $files) {

			foreach ($files as $file) {

				if (preg_match('/^(.*).tpl$/', $file) && file_exists($filePath = $dir . $file)) {
					// get controller name instead of file name

					$prefixKey = Tools::toCamelCase(str_replace(_PS_ADMIN_DIR_ . DIRECTORY_SEPARATOR . 'themes', '', $filePath), true);
					$pos = strrpos($prefixKey, DIRECTORY_SEPARATOR);
					$tmp = substr($prefixKey, 0, $pos);

					if (preg_match('#controllers#', $tmp)) {
						$parentClass = explode(DIRECTORY_SEPARATOR, str_replace('/', DIRECTORY_SEPARATOR, $tmp));
						$override = array_search('override', $parentClass);

						if ($override !== false) {
							// case override/controllers/admin/templates/controller_name
							$prefixKey = 'Admin' . ucfirst($parentClass[$override + 4]);
						} else {
							// case admin_name/themes/theme_name/template/controllers/controller_name
							$key = array_search('controllers', $parentClass);
							$prefixKey = 'Admin' . ucfirst($parentClass[$key + 1]);
						}

					} else {
						$prefixKey = 'Admin' . ucfirst(substr($tmp, strrpos($tmp, DIRECTORY_SEPARATOR) + 1, $pos));
					}

					// Adding list, form, option in Helper Translations
					$listPrefixKey = [
						'AdminHelpers', 'AdminList', 'AdminView', 'AdminOptions', 'AdminForm',
						'AdminCalendar', 'AdminTree', 'AdminUploader', 'AdminDataviz', 'AdminKpi', 'AdminModule_list', 'AdminModulesList',
					];

					if (in_array($prefixKey, $listPrefixKey)) {
						$prefixKey = 'Helper';
					}

					// Adding the folder backup/download/ in AdminBackup Translations

					if ($prefixKey == 'AdminDownload') {
						$prefixKey = 'AdminBackup';
					}

					// use the prefix "AdminController" (like old php files 'header', 'footer.inc', 'index', 'login', 'password', 'functions'

					if ($prefixKey == 'Admin' || $prefixKey == 'AdminTemplate') {
						$prefixKey = 'AdminController';
					}

					$newLang = [];

					// Get content for this file
					$content = file_get_contents($filePath);

					// Parse this content
					$matches = $this->userParseFile($content, $this->type_selected, 'tpl');

					/* Get string translation for each tpl file */

					foreach ($matches as $englishString) {

						if (empty($englishString)) {
							$this->errors[] = sprintf($this->l('There is an error in template, an empty string has been found. Please edit: "%s"'), $filePath);

							$newLang[$englishString] = '';
						} else {
							$transKey = $prefixKey . md5($englishString);

							if (isset($GLOBALS[$nameVar][$transKey])) {
								$newLang[$englishString]['trad'] = html_entity_decode($GLOBALS[$nameVar][$transKey], ENT_COMPAT, 'UTF-8');
							} else {

								if (!isset($newLang[$englishString]['trad'])) {
									$newLang[$englishString]['trad'] = '';

									if (!isset($missingTranslationsBack[$prefixKey])) {
										$missingTranslationsBack[$prefixKey] = 1;
										$missingTranslationsFound[$prefixKey] = [];
										$missingTranslationsFound[$prefixKey][] = $englishString;
									} else

									if (!in_array($englishString, $missingTranslationsFound[$prefixKey])) {
										$missingTranslationsBack[$prefixKey]++;
										$missingTranslationsFound[$prefixKey][] = $englishString;
									}

								}

							}

							$newLang[$englishString]['use_sprintf'] = $this->checkIfKeyUseSprintf($englishString);
						}

					}

					if (isset($tabsArray[$prefixKey])) {
						$tabsArray[$prefixKey] = array_merge($tabsArray[$prefixKey], $newLang);
					} else {
						$tabsArray[$prefixKey] = $newLang;
					}

				}

			}

		}

		if ($this->compile_ajax) {

			return $tabsArray;
		}

		if ($this->compile_grid) {

			return [
				'tabsArray'           => $tabsArray,
				'missingTranslations' => $missingTranslationsBack,
			];
		}

		// count will contain the number of expressions of the page
		$count = 0;

		foreach ($tabsArray as $array) {
			$count += count($array);
		}

		if ($excel) {
			$this->returnSpreadSheet($tabsArray, $missingTranslationsBack);
		}

		$this->tpl_view_vars = array_merge(
			$this->tpl_view_vars,
			[
				'count'                => $count,
				'cancel_url'           => $this->context->link->getAdminLink('AdminTranslations'),
				'limit_warning'        => $this->displayLimitPostWarning($count),
				'mod_security_warning' => Tools::apacheModExists('mod_security'),
				'tabsArray'            => $tabsArray,
				'missing_translations' => $missingTranslationsBack,
			]
		);

		$this->initToolbar();
		$this->base_tpl_view = 'translation_form.tpl';

		return parent::renderView();
	}

	public function initFormFrontcontroller($excel = false) {

		$nameVar = $this->translations_informations[$this->type_selected]['var'];
		$GLOBALS[$nameVar] = $this->fileExists();
		$missingTranslationsFrontController = [];
		$missingTranslationsFound = [];

		// Get all types of file (PHP, TPL...) and a list of files to parse by folder
		$filesPerDirectory = $this->getFileToParseByTypeTranslation();

		foreach ($filesPerDirectory['php'] as $dir => $files) {

			foreach ($files as $file) {
				// Check if is a PHP file and if the override file exists

				if (preg_match('/^(.*)\.php$/', $file) && file_exists($filePath = $dir . $file) && !in_array($file, static::$ignore_folder)) {
					$prefixKey = basename($file);
					// -4 becomes -14 to remove the ending "Controller.php" from the filename

					if (strpos($file, 'Controller.php') !== false) {
						$prefixKey = basename(substr($file, 0, -14));
					}

					if ($prefixKey == 'Front') {
						$prefixKey = 'FrontController';
					}

					// Get content for this file
					$content = file_get_contents($filePath);

					// Parse this content
					$matches = $this->userParseFile($content, $this->type_selected, 'php');

					foreach ($matches as $key) {
						// Caution ! front has underscore between prefix key and md5, back has not

						if (isset($GLOBALS[$nameVar][$prefixKey . md5($key)])) {
							$tabsArray[$prefixKey][$key]['trad'] = stripslashes(html_entity_decode($GLOBALS[$nameVar][$prefixKey . md5($key)], ENT_COMPAT, 'UTF-8'));
						} else {

							if (!isset($tabsArray[$prefixKey][$key]['trad'])) {
								$tabsArray[$prefixKey][$key]['trad'] = '';

								if (!isset($missingTranslationsFrontController[$prefixKey])) {
									$missingTranslationsFrontController[$prefixKey] = 1;
									$missingTranslationsFound[$prefixKey] = [];
									$missingTranslationsFound[$prefixKey][] = $key;
								} else

								if (!in_array($key, $missingTranslationsFound[$prefixKey])) {
									$missingTranslationsFrontController[$prefixKey]++;
									$missingTranslationsFound[$prefixKey][] = $key;
								}

							}

						}

						$tabsArray[$prefixKey][$key]['use_sprintf'] = $this->checkIfKeyUseSprintf($key);
					}

				}

			}

		}

		foreach ($filesPerDirectory['specific'] as $dir => $files) {

			foreach ($files as $file) {

				if (file_exists($filePath = $dir . $file) && !in_array($file, static::$ignore_folder)) {
					$prefixKey = 'index';

					// Get content for this file
					$content = file_get_contents($filePath);

					// Parse this content
					$matches = $this->userParseFile($content, $this->type_selected, 'specific');

					foreach ($matches as $key) {
						// Caution ! front has underscore between prefix key and md5, back has not

						if (isset($GLOBALS[$nameVar][$prefixKey . md5($key)])) {
							$tabsArray[$prefixKey][$key]['trad'] = stripslashes(html_entity_decode($GLOBALS[$nameVar][$prefixKey . md5($key)], ENT_COMPAT, 'UTF-8'));
						} else {

							if (!isset($tabsArray[$prefixKey][$key]['trad'])) {
								$tabsArray[$prefixKey][$key]['trad'] = '';

								if (!isset($missingTranslationsFrontController[$prefixKey])) {
									$missingTranslationsFrontController[$prefixKey] = 1;
									$missingTranslationsFound[$prefixKey] = [];
									$missingTranslationsFound[$prefixKey][] = $key;
								} else

								if (!in_array($key, $missingTranslationsFound[$prefixKey])) {
									$missingTranslationsFrontController[$prefixKey]++;
									$missingTranslationsFound[$prefixKey][] = $key;
								}

							}

						}

						$tabsArray[$prefixKey][$key]['use_sprintf'] = $this->checkIfKeyUseSprintf($key);
					}

				}

			}

		}

		if ($this->compile_grid) {

			return [
				'tabsArray'           => $tabsArray,
				'missingTranslations' => $missingTranslationsFrontController,
			];
		}

		if ($this->compile_ajax) {
			return $tabsArray;
		}

		// count will contain the number of expressions of the page
		$count = 0;

		foreach ($tabsArray as $array) {
			$count += count($array);
		}

		if ($excel) {
			$this->returnSpreadSheet($tabsArray, $missingTranslationsFrontController);
		}

		$this->tpl_view_vars = array_merge(
			$this->tpl_view_vars,
			[
				'count'                => $count,
				'cancel_url'           => $this->context->link->getAdminLink('AdminTranslations'),
				'limit_warning'        => $this->displayLimitPostWarning($count),
				'mod_security_warning' => Tools::apacheModExists('mod_security'),
				'tabsArray'            => $tabsArray,
				'missing_translations' => $missingTranslationsFrontController,
			]
		);

		$this->initToolbar();
		$this->base_tpl_view = 'translation_form.tpl';

		return parent::renderView();
	}

	public function initFormClass($excel = false) {

		$nameVar = $this->translations_informations[$this->type_selected]['var'];
		$GLOBALS[$nameVar] = $this->fileExists();
		$missingTranslationsClass = [];
		$missingTranslationsFound = [];

		// Get all types of file (PHP, TPL...) and a list of files to parse by folder
		$filesPerDirectory = $this->getFileToParseByTypeTranslation();

		foreach ($filesPerDirectory['php'] as $dir => $files) {

			foreach ($files as $file) {
				// Check if is a PHP file and if the override file exists

				if (preg_match('/^(.*)\.php$/', $file) && file_exists($filePath = $dir . $file) && !in_array($file, static::$ignore_folder)) {

					$prefixKey = basename(substr($file, 0, -4));
					// -4 becomes -14 to remove the ending "Controller.php" from the filename

					// Get content for this file
					$content = file_get_contents($filePath);

					// Parse this content
					$matches = $this->userParseFile($content, $this->type_selected, 'php');

					foreach ($matches as $key) {
						// Caution ! front has underscore between prefix key and md5, back has not

						if (isset($GLOBALS[$nameVar][$prefixKey . md5($key)])) {
							$tabsArray[$prefixKey][$key]['trad'] = stripslashes(html_entity_decode($GLOBALS[$nameVar][$prefixKey . md5($key)], ENT_COMPAT, 'UTF-8'));
						} else {

							if (!isset($tabsArray[$prefixKey][$key]['trad'])) {
								$tabsArray[$prefixKey][$key]['trad'] = '';

								if (!isset($missingTranslationsClass[$prefixKey])) {
									$missingTranslationsClass[$prefixKey] = 1;
									$missingTranslationsFound[$prefixKey] = [];
									$missingTranslationsFound[$prefixKey][] = $key;
								} else

								if (!in_array($key, $missingTranslationsFound[$prefixKey])) {
									$missingTranslationsClass[$prefixKey]++;
									$missingTranslationsFound[$prefixKey][] = $key;
								}

							}

						}

						$tabsArray[$prefixKey][$key]['use_sprintf'] = $this->checkIfKeyUseSprintf($key);
					}

				}

			}

		}

		foreach ($filesPerDirectory['specific'] as $dir => $files) {

			foreach ($files as $file) {

				if (file_exists($filePath = $dir . $file) && !in_array($file, static::$ignore_folder)) {
					$prefixKey = 'index';

					// Get content for this file
					$content = file_get_contents($filePath);

					// Parse this content
					$matches = $this->userParseFile($content, $this->type_selected, 'specific');

					foreach ($matches as $key) {
						// Caution ! front has underscore between prefix key and md5, back has not

						if (isset($GLOBALS[$nameVar][$prefixKey . md5($key)])) {
							$tabsArray[$prefixKey][$key]['trad'] = stripslashes(html_entity_decode($GLOBALS[$nameVar][$prefixKey . md5($key)], ENT_COMPAT, 'UTF-8'));
						} else {

							if (!isset($tabsArray[$prefixKey][$key]['trad'])) {
								$tabsArray[$prefixKey][$key]['trad'] = '';

								if (!isset($missingTranslationsClass[$prefixKey])) {
									$missingTranslationsClass[$prefixKey] = 1;
									$missingTranslationsFound[$prefixKey] = [];
									$missingTranslationsFound[$prefixKey][] = $key;
								} else

								if (!in_array($key, $missingTranslationsFound[$prefixKey])) {
									$missingTranslationsClass[$prefixKey]++;
									$missingTranslationsFound[$prefixKey][] = $key;
								}

							}

						}

						$tabsArray[$prefixKey][$key]['use_sprintf'] = $this->checkIfKeyUseSprintf($key);
					}

				}

			}

		}

		if ($this->compile_grid) {

			return [
				'tabsArray'           => $tabsArray,
				'missingTranslations' => $missingTranslationsClass,
			];
		}

		if ($this->compile_ajax) {
			return $tabsArray;
		}

		// count will contain the number of expressions of the page
		$count = 0;

		foreach ($tabsArray as $array) {
			$count += count($array);
		}

		if ($excel) {
			$this->returnSpreadSheet($tabsArray, $missingTranslationsClass);
		}

		$this->tpl_view_vars = array_merge(
			$this->tpl_view_vars,
			[
				'count'                => $count,
				'cancel_url'           => $this->context->link->getAdminLink('AdminTranslations'),
				'limit_warning'        => $this->displayLimitPostWarning($count),
				'mod_security_warning' => Tools::apacheModExists('mod_security'),
				'tabsArray'            => $tabsArray,
				'missing_translations' => $missingTranslationsClass,
			]
		);

		$this->initToolbar();
		$this->base_tpl_view = 'translation_form.tpl';

		return parent::renderView();
	}

	/**
	 * This method generate the form for errors translations
	 *
	 * @return string
	 *
	 * @since 1.8.5.0
	 */
	public function initFormErrors($excel = false) {

		$nameVar = $this->translations_informations[$this->type_selected]['var'];
		$GLOBALS[$nameVar] = $this->fileExists();
		$countEmpty = [];

		/* List files to parse */
		$stringToTranslate = [];
		$fileByDirectory = $this->getFileToParseByTypeTranslation();

		if ($modules = $this->getListModules()) {

			foreach ($modules as $module) {

				if (is_dir(_PS_MODULE_DIR_ . $module) && !in_array($module, static::$ignore_folder)) {
					$fileByDirectory['php'] = array_merge($fileByDirectory['php'], $this->listFiles(_PS_MODULE_DIR_ . $module . '/', [], 'php'));
				}

			}

		}

		foreach ($fileByDirectory['php'] as $dir => $files) {

			foreach ($files as $file) {

				if (preg_match('/\.php$/', $file) && file_exists($filePath = $dir . $file) && !in_array($file, static::$ignore_folder)) {

					if (!filesize($filePath)) {
						continue;
					}

					// Get content for this file
					$content = file_get_contents($filePath);

					// Parse this content
					$matches = $this->userParseFile($content, $this->type_selected);

					foreach ($matches as $key) {

						if (array_key_exists(md5($key), $GLOBALS[$nameVar])) {
							$stringToTranslate[$key]['trad'] = html_entity_decode($GLOBALS[$nameVar][md5($key)], ENT_COMPAT, 'UTF-8');
						} else {
							$stringToTranslate[$key]['trad'] = '';

							if (!isset($countEmpty[$key])) {
								$countEmpty[$key] = 1;
							}

						}

						$stringToTranslate[$key]['use_sprintf'] = $this->checkIfKeyUseSprintf($key);
					}

				}

			}

		}

		if ($this->compile_grid) {

			return [
				'tabsArray'           => $tabsArray,
				'missingTranslations' => $countEmpty,
			];
		}

		if ($excel) {
			$this->returnSpreadSheet($stringToTranslate, $countEmpty);
		}

		$this->tpl_view_vars = array_merge(
			$this->tpl_view_vars,
			[
				'count'                => count($stringToTranslate),
				'cancel_url'           => $this->context->link->getAdminLink('AdminTranslations'),
				'limit_warning'        => $this->displayLimitPostWarning(count($stringToTranslate)),
				'mod_security_warning' => Tools::apacheModExists('mod_security'),
				'errorsArray'          => $stringToTranslate,
				'missing_translations' => $countEmpty,
			]
		);

		$this->initToolbar();
		$this->base_tpl_view = 'translation_errors.tpl';

		return parent::renderView();
	}

	/**
	 * This method generate the form for fields translations
	 *
	 * @return string
	 *
	 * @since 1.8.5.0
	 */
	public function initFormFields($excel = false) {

		$nameVar = $this->translations_informations[$this->type_selected]['var'];
		$GLOBALS[$nameVar] = $this->fileExists();
		$missingTranslationsFields = [];
		$classArray = [];
		$tabsArray = [];
		$count = 0;

		$filesByDirectory = $this->getFileToParseByTypeTranslation();

		foreach ($filesByDirectory['php'] as $dir => $files) {

			foreach ($files as $file) {
				$excludeFiles = ['index.php', 'PhenyxShopAutoload.php', 'StockManagerInterface.php', 'TaxManagerInterface.php', 'WebserviceOutputInterface.php', 'WebserviceSpecificManagementInterface.php'];

				if (!preg_match('/\.php$/', $file) || in_array($file, $excludeFiles)) {
					continue;
				}

				$className = substr($file, 0, -4);

				if (!class_exists($className, false) && !class_exists($className . 'Core', false)) {
					PhenyxShopAutoload::getInstance()->load($className);
				}

				if (!is_subclass_of($className . 'Core', 'ObjectModel')) {
					continue;
				}

				$classArray[$className] = call_user_func([$className, 'getValidationRules'], $className);
			}

		}

		foreach ($classArray as $prefixKey => $rules) {

			if (isset($rules['validate'])) {

				foreach ($rules['validate'] as $key => $value) {

					if (isset($GLOBALS[$nameVar][$prefixKey . '_' . md5($key)])) {
						$tabsArray[$prefixKey][$key]['trad'] = html_entity_decode($GLOBALS[$nameVar][$prefixKey . '_' . md5($key)], ENT_COMPAT, 'UTF-8');
						$count++;
					} else {

						if (!isset($tabsArray[$prefixKey][$key]['trad'])) {
							$tabsArray[$prefixKey][$key]['trad'] = '';

							if (!isset($missingTranslationsFields[$prefixKey])) {
								$missingTranslationsFields[$prefixKey] = 1;
							} else {
								$missingTranslationsFields[$prefixKey]++;
							}

							$count++;
						}

					}

				}

			}

			if (isset($rules['validateLang'])) {

				foreach ($rules['validateLang'] as $key => $value) {

					if (isset($GLOBALS[$nameVar][$prefixKey . '_' . md5($key)])) {
						$tabsArray[$prefixKey][$key]['trad'] = '';

						if (array_key_exists($prefixKey . '_' . md5(addslashes($key)), $GLOBALS[$nameVar])) {
							$tabsArray[$prefixKey][$key]['trad'] = html_entity_decode($GLOBALS[$nameVar][$prefixKey . '_' . md5(addslashes($key))], ENT_COMPAT, 'UTF-8');
						}

						$count++;
					} else {

						if (!isset($tabsArray[$prefixKey][$key]['trad'])) {
							$tabsArray[$prefixKey][$key]['trad'] = '';

							if (!isset($missingTranslationsFields[$prefixKey])) {
								$missingTranslationsFields[$prefixKey] = 1;
							} else {
								$missingTranslationsFields[$prefixKey]++;
							}

							$count++;
						}

					}

				}

			}

		}

		if ($this->compile_grid) {

			return [
				'tabsArray'           => $tabsArray,
				'missingTranslations' => $missingTranslationsFields,
			];
		}

		if ($this->compile_ajax) {
			return $tabsArray;
		}

		if ($excel) {
			$this->returnSpreadSheet($tabsArray, $missingTranslationsFields);
		}

		$this->tpl_view_vars = array_merge(
			$this->tpl_view_vars,
			[
				'count'                => $count,
				'limit_warning'        => $this->displayLimitPostWarning($count),
				'mod_security_warning' => Tools::apacheModExists('mod_security'),
				'tabsArray'            => $tabsArray,
				'cancel_url'           => $this->context->link->getAdminLink('AdminTranslations'),
				'missing_translations' => $missingTranslationsFields,
			]
		);

		$this->initToolbar();
		$this->base_tpl_view = 'translation_form.tpl';

		return parent::renderView();
	}

	/**
	 * This method generate the form for mails translations
	 *
	 * @param bool $noDisplay
	 *
	 * @return array|string
	 *
	 * @since 1.8.5.0
	 */
	public function initFormMails($noDisplay = false) {

		$moduleMails = [];

		// get all mail subjects, this method parse each files in PhenyxShop !!
		$subjectMail = [];

		$modulesHasMails = $this->getModulesHasMails(true);

		$filesByDirectiories = $this->getFileToParseByTypeTranslation();

		if (!$this->theme_selected || !@filemtime($this->translations_informations[$this->type_selected]['override']['dir'])) {
			$this->copyMailFilesForAllLanguages();
		}

		foreach ($filesByDirectiories['php'] as $dir => $files) {

			foreach ($files as $file) {
				// If file exist and is not in ignore_folder, in the next step we check if a folder or mail

				if (file_exists($dir . $file) && !in_array($file, static::$ignore_folder)) {
					$subjectMail = $this->getSubjectMail($dir, $file, $subjectMail);
				}

			}

		}

		// Get path of directory for find a good path of translation file

		if ($this->theme_selected && @filemtime($this->translations_informations[$this->type_selected]['override']['dir'])) {
			$i18NDir = $this->translations_informations[$this->type_selected]['override']['dir'];
		} else {
			$i18NDir = $this->translations_informations[$this->type_selected]['dir'];
		}

		$coreMails = $this->getMailFiles($i18NDir, 'core_mail');
		$coreMails['subject'] = $this->getSubjectMailContent($i18NDir);

		foreach ($modulesHasMails as $moduleName => $modulePath) {
			$modulePath = rtrim($modulePath, '/');
			$moduleMails[$moduleName] = $this->getMailFiles($modulePath . '/mails/' . $this->lang_selected->iso_code . '/', 'module_mail');
			$moduleMails[$moduleName]['subject'] = $coreMails['subject'];
			$moduleMails[$moduleName]['display'] = $this->displayMailContent($moduleMails[$moduleName], $subjectMail, $this->lang_selected, mb_strtolower($moduleName), $moduleName, $moduleName);
		}

		if ($noDisplay) {
			$empty = 0;
			$total = 0;
			$total += (int) $coreMails['total_filled'];
			$empty += (int) $coreMails['empty_values'];

			foreach ($moduleMails as $modInfos) {
				$total += (int) $modInfos['total_filled'];
				$empty += (int) $modInfos['empty_values'];
			}

			return ['total' => $total, 'empty' => $empty];
		}

		$this->tpl_view_vars = array_merge(
			$this->tpl_view_vars,
			[
				'limit_warning'        => $this->displayLimitPostWarning($this->total_expression),
				'mod_security_warning' => Tools::apacheModExists('mod_security'),
				'tinyMCE'              => $this->getTinyMCEForMails($this->lang_selected->iso_code),
				'mail_content'         => $this->displayMailContent($coreMails, $subjectMail, $this->lang_selected, 'core', $this->l('Core emails')),
				'cancel_url'           => $this->context->link->getAdminLink('AdminTranslations'),
				'module_mails'         => $moduleMails,
				'theme_name'           => $this->theme_selected,
			]
		);

		$this->initToolbar();
		$this->base_tpl_view = 'translation_mails.tpl';

		return parent::renderView();
	}

	/**
	 * @return bool
	 *
	 * @since 1.8.5.0
	 */
	public function copyMailFilesForAllLanguages() {

		$currentTheme = Tools::safeOutput($this->context->theme->name);
		$languages = Language::getLanguages();

		foreach ($languages as $key => $lang) {
			$dirToCopyIso = [];
			$filesToCopyIso = [];
			$currentIsoCode = $lang['iso_code'];

			$dirToCopyIso[] = _PS_MAIL_DIR_ . $currentIsoCode . '/';

			$modulesHasMails = $this->getModulesHasMails(true);

			foreach ($modulesHasMails as $moduleName => $modulePath) {

				if ($pos = strpos($modulePath, '/modules')) {
					$dirToCopyIso[] = _PS_ROOT_DIR_ . substr($modulePath, $pos) . 'mails/' . $currentIsoCode . '/';
				}

			}

			foreach ($dirToCopyIso as $dir) {

				foreach (scandir($dir) as $file) {

					if (!in_array($file, static::$ignore_folder)) {
						$filesToCopyIso[] = [
							"from" => $dir . $file,
							"to"   => str_replace((strpos($dir, _PS_CORE_DIR_) !== false) ? _PS_CORE_DIR_ : _PS_ROOT_DIR_, _PS_ROOT_DIR_ . '/themes/' . $currentTheme, $dir) . $file,
						];
					}

				}

			}

			foreach ($filesToCopyIso as $file) {

				if (!file_exists($file['to'])) {
					$content = file_get_contents($file['from']);

					$stack = [];
					$folder = dirname($file['to']);

					while (!is_dir($folder)) {
						array_push($stack, $folder);
						$folder = dirname($folder);
					}

					while ($folder = array_pop($stack)) {
						mkdir($folder);
					}

					$success = file_put_contents($file['to'], $content);

					if ($success === false) {
						Logger::addLog(sprintf("%s cannot be copied to %s", $file['from'], $file['to']));
					}

				}

			}

		}

		return true;
	}

	/**
	 * Get list of subjects of mails
	 *
	 * @param $dir
	 * @param $file
	 * @param $subjectMail
	 *
	 * @return array : list of subjects of mails
	 *
	 * @since 1.8.5.0
	 */
	protected function getSubjectMail($dir, $file, $subjectMail) {

		$dir = rtrim($dir, '/');
		// If is file and is not in ignore_folder

		if (is_file($dir . '/' . $file) && !in_array($file, static::$ignore_folder) && preg_match('/\.php$/', $file)) {
			$content = file_get_contents($dir . '/' . $file);
			$content = str_replace("\n", ' ', $content);
			static::extractMailSubjects($content, $subjectMail);
		}

		// Or if is folder, we scan folder for check if found in folder and subfolder
		else

		if (!in_array($file, static::$ignore_folder) && is_dir($dir . '/' . $file)) {

			foreach (scandir($dir . '/' . $file) as $temp) {

				if ($temp[0] != '.') {
					$subjectMail = $this->getSubjectMail($dir . '/' . $file, $temp, $subjectMail);
				}

			}

		}

		return $subjectMail;
	}

	protected static function extractMailSubjects($content, &$subjectMail) {

		// Subject must match with a template, therefore we first grep the Mail::Send() function then the Mail::l() inside.

		if (preg_match_all('/Mail::Send([^;]*);/si', $content, $tab)) {

			for ($i = 0;isset($tab[1][$i]); $i++) {
				$tab2 = explode(',', $tab[1][$i]);

				if (is_array($tab2) && isset($tab2[1])) {
					$template = trim(str_replace('\'', '', $tab2[1]));

					foreach ($tab2 as $tab3) {

						if (preg_match('/Mail::l\(\s*\'' . _PS_TRANS_PATTERN_ . '\'\s*\)/Us', $tab3 . ')', $matches)) {

							if (!isset($subjectMail[$template])) {
								$subjectMail[$template] = [];
							}

							if (!in_array($matches[1], $subjectMail[$template])) {
								$subjectMail[$template][] = $matches[1];
							}

						}

					}

				}

			}

		}

	}

	/**
	 * Get each informations for each mails found in the folder $dir.
	 *
	 * @since 1.4.0.14
	 *
	 * @param string $dir
	 * @param string $groupName
	 *
	 * @return false|array list of mails
	 *
	 * @since 1.8.5.0
	 */
	public function getMailFiles($dir, $groupName = 'mail') {

		$arrReturn = [];

		if (Language::getIdByIso('en')) {
			$defaultLanguage = 'en';
		} else {
			$defaultLanguage = Language::getIsoById((int) Configuration::get('PS_LANG_DEFAULT'));
		}

		if (!$defaultLanguage || !Validate::isLanguageIsoCode($defaultLanguage)) {
			return false;
		}

		// Very usefull to name input and textarea fields
		$arrReturn['group_name'] = $groupName;
		$arrReturn['empty_values'] = 0;
		$arrReturn['total_filled'] = 0;
		$arrReturn['directory'] = $dir;

		// Get path for english mail directory
		$dirEn = str_replace('/' . $this->lang_selected->iso_code . '/', '/' . $defaultLanguage . '/', $dir);

		if (file_exists($dirEn)) {
			// Get all english files to compare with the language to translate

			foreach (scandir($dirEn) as $emailFile) {

				if (strripos($emailFile, '.html') > 0 || strripos($emailFile, '.txt') > 0) {
					$emailName = substr($emailFile, 0, strripos($emailFile, '.'));
					$type = substr($emailFile, strripos($emailFile, '.') + 1);

					if (!isset($arrReturn['files'][$emailName])) {
						$arrReturn['files'][$emailName] = [];
					}

					// $email_file is from scandir ($dir), so we already know that file exists
					$arrReturn['files'][$emailName][$type]['en'] = $this->getMailContent($dirEn, $emailFile);

					// check if the file exists in the language to translate

					if (file_exists($dir . '/' . $emailFile)) {
						$arrReturn['files'][$emailName][$type][$this->lang_selected->iso_code] = $this->getMailContent($dir, $emailFile);
						$this->total_expression++;
					} else {
						$arrReturn['files'][$emailName][$type][$this->lang_selected->iso_code] = '';
					}

					if ($arrReturn['files'][$emailName][$type][$this->lang_selected->iso_code] == '') {
						$arrReturn['empty_values']++;
					} else {
						$arrReturn['total_filled']++;
					}

				}

			}

		} else {
			$this->warnings[] = sprintf(
				Tools::displayError('A mail directory exists for the "%1$s" language, but not for the default language (%3$s) in %2$s'),
				$this->lang_selected->iso_code,
				str_replace(_PS_ROOT_DIR_, '', dirname($dir)),
				$defaultLanguage
			);
		}

		return $arrReturn;
	}

	/**
	 * Get content of the mail file.
	 *
	 * @since 1.4.0.14
	 *
	 * @param string $dir
	 * @param string $file
	 *
	 * @return string content of file
	 *
	 * @since 1.8.5.0
	 */
	protected function getMailContent($dir, $file) {

		$content = file_get_contents($dir . '/' . $file);

		if (mb_strlen($content) === 0) {
			$content = '';
		}

		return $content;
	}

	/**
	 * @param $directory : name of directory
	 *
	 * @return array
	 *
	 * @since 1.8.5.0
	 */
	protected function getSubjectMailContent($directory) {

		$subjectMailContent = [];

		if (file_exists($directory . '/lang.php')) {
			// we need to include this even if already included (no include once)
			include $directory . '/lang.php';

			foreach ($GLOBALS[$this->translations_informations[$this->type_selected]['var']] as $key => $subject) {
				$this->total_expression++;
				$subject = str_replace('\n', ' ', $subject);
				$subject = str_replace("\\'", "\'", $subject);

				$subjectMailContent[$key]['trad'] = htmlentities($subject, ENT_QUOTES, 'UTF-8');
				$subjectMailContent[$key]['use_sprintf'] = $this->checkIfKeyUseSprintf($key);
			}

		} else {
			$this->errors[] = sprintf($this->l('Email subject translation file not found in "%s".'), $directory);
		}

		return $subjectMailContent;
	}

	/**
	 * Display mails in html format.
	 * This was create for factorize the html displaying
	 *
	 * @since 1.4.0.14
	 *
	 * @param array       $mails
	 * @param array       $allSubjectMail
	 * @param Language    $objLang
	 * @param string      $idHtml        Use for set html id attribute for the block
	 * @param string      $title         Set the title for the block
	 * @param string|bool $nameForModule Is not false define add a name for distinguish mails module
	 *
	 * @return string
	 *
	 * @since 1.8.5.0
	 */
	protected function displayMailContent($mails, $allSubjectMail, $objLang, $idHtml, $title, $nameForModule = false) {

		$strReturn = '';
		$groupName = 'mail';

		if (array_key_exists('group_name', $mails)) {
			$groupName = $mails['group_name'];
		}

		if ($mails['empty_values'] == 0) {
			$translationMissingBadgeType = 'badge-success';
		} else {
			$translationMissingBadgeType = 'badge-danger';
		}

		$strReturn .= '<div class="mails_field">
            <h4>
            <span class="badge">' . ((int) $mails['empty_values'] + (int) $mails['total_filled']) . ' <i class="icon-envelope-o"></i></span>
            <a href="javascript:void(0);" onclick="$(\'#' . $idHtml . '\').slideToggle();">' . $title . '</a>
            <span class="pull-right badge ' . $translationMissingBadgeType . '">' . $mails['empty_values'] . ' ' . $this->l('missing translation(s)') . '</span>
            </h4>
            <div name="mails_div" id="' . $idHtml . '" class="panel-group">';

		if (!empty($mails['files'])) {
			$topicAlreadyDisplayed = [];

			foreach ($mails['files'] as $mailName => $mailFiles) {
				$strReturn .= '<div class="panel translations-email-panel">';
				$strReturn .= '<a href="#' . $idHtml . '-' . $mailName . '" class="panel-title" data-toggle="collapse" data-parent="#' . $idHtml . '" >' . $mailName . ' <i class="icon-caret-down"></i> </a>';
				$strReturn .= '<div id="' . $idHtml . '-' . $mailName . '" class="email-collapse panel-collapse collapse">';

				if (array_key_exists('html', $mailFiles) || array_key_exists('txt', $mailFiles)) {

					if (array_key_exists($mailName, $allSubjectMail)) {

						foreach ($allSubjectMail[$mailName] as $subjectMail) {
							$subjectKey = 'subject[' . Tools::htmlentitiesUTF8($groupName) . '][' . Tools::htmlentitiesUTF8($subjectMail) . ']';

							if (in_array($subjectKey, $topicAlreadyDisplayed)) {
								continue;
							}

							$topicAlreadyDisplayed[] = $subjectKey;
							$valueSubjectMail = isset($mails['subject'][$subjectMail]) ? $mails['subject'][$subjectMail] : '';
							$strReturn .= '
                            <div class="label-subject row">
                                <label class="control-label col-lg-3">' . sprintf($this->l('Email subject'));

							if (isset($valueSubjectMail['use_sprintf']) && $valueSubjectMail['use_sprintf']) {
								$strReturn .= '<span class="useSpecialSyntax" title="' . $this->l('This expression uses a special syntax:') . ' ' . $valueSubjectMail['use_sprintf'] . '">
                                    <i class="icon-exclamation-triangle"></i>
                                </span>';
							}

							$strReturn .= '</label><div class="col-lg-9">';

							if (isset($valueSubjectMail['trad']) && $valueSubjectMail['trad']) {
								$strReturn .= '<input class="form-control" type="text" name="subject[' . Tools::htmlentitiesUTF8($groupName) . '][' . Tools::htmlentitiesUTF8($subjectMail) . ']" value="' . $valueSubjectMail['trad'] . '" />';
							} else {
								$strReturn .= '<input class="form-control" type="text" name="subject[' . Tools::htmlentitiesUTF8($groupName) . '][' . Tools::htmlentitiesUTF8($subjectMail) . ']" value="" />';
							}

							$strReturn .= '<p class="help-block">' . stripcslashes($subjectMail) . '</p>';
							$strReturn .= '</div></div>';
						}

					} else {
						$strReturn .= '
                            <hr><div class="alert alert-info">'
						. sprintf($this->l('No Subject was found for %s in the database.'), $mailName)
							. '</div>';
					}

					// tab menu
					$strReturn .= '<hr><ul class="nav nav-pills">
                        <li class="active"><a href="#' . $mailName . '-html" data-toggle="tab">' . $this->l('View HTML version') . '</a></li>
                        <li><a href="#' . $mailName . '-editor" data-toggle="tab">' . $this->l('Edit HTML version') . '</a></li>
                        <li><a href="#' . $mailName . '-text" data-toggle="tab">' . $this->l('View/Edit TXT version') . '</a></li>
                        </ul>';
					// tab-content
					$strReturn .= '<div class="tab-content">';

					if (array_key_exists('html', $mailFiles)) {
						$strReturn .= '<div class="tab-pane active" id="' . $mailName . '-html">';
						$baseUri = str_replace(_PS_ROOT_DIR_, __PS_BASE_URI__, $mails['directory']);
						$baseUri = str_replace('//', '/', $baseUri);
						$urlMail = $baseUri . $mailName . '.html';
						$strReturn .= $this->displayMailBlockHtml($mailFiles['html'], $objLang->iso_code, $urlMail, $mailName, $groupName, $nameForModule);
						$strReturn .= '</div>';
					}

					if (array_key_exists('txt', $mailFiles)) {
						$strReturn .= '<div class="tab-pane" id="' . $mailName . '-text">';
						$strReturn .= $this->displayMailBlockTxt($mailFiles['txt'], $objLang->iso_code, $mailName, $groupName, $nameForModule);
						$strReturn .= '</div>';
					}

					$strReturn .= '<div class="tab-pane" id="' . $mailName . '-editor">';

					if (isset($mailFiles['html'])) {
						$strReturn .= $this->displayMailEditor($mailFiles['html'], $objLang->iso_code, $urlMail, $mailName, $groupName, $nameForModule);
					}

					$strReturn .= '</div>';

					$strReturn .= '</div>';
					$strReturn .= '</div><!--end .panel-collapse -->';
					$strReturn .= '</div><!--end .panel -->';
				}

			}

		} else {
			$strReturn .= '<p class="error">
                ' . $this->l('There was a problem getting the mail files.') . '<br>
                ' . sprintf($this->l('English language files must exist in %s folder'), '<em>' . preg_replace('@/[a-z]{2}(/?)$@', '/en$1', $mails['directory']) . '</em>') . '
            </p>';
		}

		$strReturn .= '</div><!-- #' . $idHtml . ' --></div><!-- end .mails_field -->';

		return $strReturn;
	}

	/**
	 * Just build the html structure for display html mails.
	 *
	 * @since 1.4.0.14
	 *
	 * @param array       $content       With english and language needed contents
	 * @param string      $lang          ISO code of the needed language
	 * @param string      $url           for         The html page and displaying an outline
	 * @param string      $mailName      Name of the file to translate (same for txt and html files)
	 * @param string      $groupName     Group name allow to distinguish each block of mail.
	 * @param string|bool $nameForModule Is not false define add a name for distinguish mails module
	 *
	 * @return string
	 *
	 * @since 1.8.5.0
	 */
	protected function displayMailBlockHtml($content, $lang, $url, $mailName, $groupName, $nameForModule = false) {

		$title = [];
		$this->cleanMailContent($content, $lang, $title);
		$nameForModule = $nameForModule ? $nameForModule . '|' : '';

		return '<div class="block-mail" >
                    <div class="mail-form">
                        <div class="form-group">
                            <label class="control-label col-lg-3">' . $this->l('HTML "title" tag') . '</label>
                            <div class="col-lg-9">
                                <input class="form-control" type="text" name="title_' . $groupName . '_' . $mailName . '" value="' . (isset($title[$lang]) ? $title[$lang] : '') . '" />
                                <p class="help-block">' . (isset($title['en']) ? $title['en'] : '') . '</p>
                            </div>
                        </div>
                        <div class="thumbnail email-html-frame" data-email-src="' . $url . '"></div>
                    </div>
                </div>';
	}

	/**
	 * Clean mail content
	 *
	 * @param $content
	 * @param $lang
	 * @param $title
	 *
	 * @return void
	 *
	 * @since 1.8.5.0
	 */
	protected function cleanMailContent(&$content, $lang, &$title) {

		if (stripos($content[$lang], '<body')) {
			$arrayLang = $lang != 'en' ? ['en', $lang] : [$lang];

			foreach ($arrayLang as $language) {
				$title[$language] = substr($content[$language], 0, stripos($content[$language], '<body'));
				preg_match('#<title>([^<]+)</title>#Ui', $title[$language], $matches);
				$title[$language] = empty($matches[1]) ? '' : $matches[1];
			}

		}

		$content[$lang] = (isset($content[$lang]) ? Tools::htmlentitiesUTF8(stripslashes($content[$lang])) : '');
	}

	/**
	 * Just build the html structure for display txt mails
	 *
	 * @param array       $content       With english and language needed contents
	 * @param string      $lang          ISO code of the needed language
	 * @param string      $mailName      Name of the file to translate (same for txt and html files)
	 * @param string      $groupName     Group name allow to distinguish each block of mail.
	 * @param string|bool $nameForModule Is not false define add a name for distinguish mails module
	 *
	 * @return string
	 *
	 * @since 1.8.5.0
	 */
	protected function displayMailBlockTxt($content, $lang, $mailName, $groupName, $nameForModule = false) {

		return '<div class="block-mail" >
                    <div class="mail-form">
                        <div><textarea class="rte noEditor" name="' . $groupName . '[txt][' . ($nameForModule ? $nameForModule . '|' : '') . $mailName . ']">' . Tools::htmlentitiesUTF8(stripslashes(strip_tags($content[$lang]))) . '</textarea></div>
                    </div>
                </div>';
	}

	/**
	 * @param      $content
	 * @param      $lang
	 * @param      $url
	 * @param      $mailName
	 * @param      $groupName
	 * @param bool $nameForModule
	 *
	 * @return string
	 *
	 * @since 1.8.5.0
	 */
	protected function displayMailEditor($content, $lang, $url, $mailName, $groupName, $nameForModule = false) {

		$title = [];
		$this->cleanMailContent($content, $lang, $title);
		$nameForModule = $nameForModule ? $nameForModule . '|' : '';

		return '<textarea class="rte-mail rte-mail-' . $mailName . ' form-control" data-rte="' . $mailName . '" name="' . $groupName . '[html][' . $nameForModule . $mailName . ']">' . $content[$lang] . '</textarea>';
	}

	/**
	 * @param string $isoLang
	 *
	 * @return string
	 *
	 * @since 1.8.5.0
	 */
	protected function getTinyMCEForMails($isoLang) {

		// TinyMCE
		$isoTinyMce = (file_exists(_PS_ROOT_DIR_ . '/js/tiny_mce/langs/' . $isoLang . '.js') ? $isoLang : 'en');
		$ad = __PS_BASE_URI__ . basename(_PS_ADMIN_DIR_);

		//return false;
		return '
            <script type="text/javascript">
                var iso = \'' . $isoTinyMce . '\' ;
                var pathCSS = \'' . _THEME_CSS_DIR_ . '\' ;
                var ad = \'' . $ad . '\' ;
            </script>
            <script type="text/javascript" src="' . __PS_BASE_URI__ . 'js/tiny_mce/tiny_mce.js"></script>
            <script type="text/javascript" src="' . __PS_BASE_URI__ . 'js/admin/tinymce.inc.js"></script>';
	}

	/**
	 * This method generate the form for modules translations
	 *
	 * @return string
	 *
	 * @since 1.8.5.0
	 */
	public function initFormModules($excel = false) {

		// Get list of modules
		$modules = $this->getListModules();

		if (!empty($modules)) {
			// Get all modules files and include all translation files
			$arrFiles = $this->getAllModuleFiles($modules, null, $this->lang_selected->iso_code, true);

			foreach ($arrFiles as $value) {
				$this->findAndFillTranslations($value['files'], $value['theme'], $value['module'], $value['dir']);
			}

			if ($this->compile_ajax) {
				return $this->modules_translations;
			}

			if ($excel) {

				if (isset($this->modules_translations)) {
					$this->returnSpreadSheet($this->modules_translations, $this->missing_translations);
				}

			}

			if ($this->compile_grid) {

				return [
					'tabsArray'           => $this->modules_translations,
					'missingTranslations' => $this->missing_translations,
				];
			}

			$this->tpl_view_vars = array_merge(
				$this->tpl_view_vars,
				[
					'default_theme_name'   => static::DEFAULT_THEME_NAME,
					'count'                => $this->total_expression,
					'limit_warning'        => $this->displayLimitPostWarning($this->total_expression),
					'mod_security_warning' => Tools::apacheModExists('mod_security'),
					'textarea_sized'       => AdminTranslationsControllerCore::TEXTAREA_SIZED,
					'cancel_url'           => $this->context->link->getAdminLink('AdminTranslations'),
					'modules_translations' => isset($this->modules_translations) ? $this->modules_translations : [],
					'missing_translations' => $this->missing_translations,
				]
			);

			$this->initToolbar();
			$this->base_tpl_view = 'translation_modules.tpl';

			return parent::renderView();
		}

		return '';
	}

	/**
	 * This method get translation for each files of a module,
	 * compare with global $_MODULES array and fill AdminTranslations::modules_translations array
	 * With key as English sentences and values as their iso code translations.
	 *
	 * @param array       $files
	 * @param string      $themeName
	 * @param string      $moduleName
	 * @param string|bool $dir
	 *
	 * @return void
	 *
	 * @since 1.8.5.0
	 */
	protected function findAndFillTranslations($files, $themeName, $moduleName, $dir = false) {

		$nameVar = $this->translations_informations[$this->type_selected]['var'];

		// added for compatibility
		$GLOBALS[$nameVar] = array_change_key_case($GLOBALS[$nameVar]);

		// Thank to this var similar keys are not duplicate
		// in AndminTranslation::modules_translations array
		// see below
		$arrayCheckDuplicate = [];

		foreach ($files as $file) {

			if ((preg_match('/^(.*).tpl$/', $file) || preg_match('/^(.*).php$/', $file)) && file_exists($file_path = $dir . $file)) {
				// Get content for this file
				$content = file_get_contents($file_path);

				// Module files can now be ignored by adding this string in a file

				if (strpos($content, 'IGNORE_THIS_FILE_FOR_TRANSLATION') !== false) {
					continue;
				}

				// Get file type
				$typeFile = substr($file, -4) == '.tpl' ? 'tpl' : 'php';

				// Parse this content
				$matches = $this->userParseFile($content, $this->type_selected, $typeFile, $moduleName);

				// Write each translation on its module file
				$templateName = substr(basename($file), 0, -4);

				foreach ($matches as $key) {
					$md5Key = md5($key);
					$moduleKey = '<{' . mb_strtolower($moduleName) . '}' . strtolower($themeName) . '>' . mb_strtolower($templateName) . '_' . $md5Key;
					$defaultKey = '<{' . mb_strtolower($moduleName) . '}thirtybees>' . mb_strtolower($templateName) . '_' . $md5Key;
					$PhenyxShopKey = '<{' . mb_strtolower($moduleName) . '}PhenyxShop>' . mb_strtolower($templateName) . '_' . $md5Key;
					// to avoid duplicate entry

					if (!in_array($moduleKey, $arrayCheckDuplicate)) {
						$arrayCheckDuplicate[] = $moduleKey;

						if (!isset($this->modules_translations[$themeName][$moduleName][$templateName][$key]['trad'])) {
							$this->total_expression++;
						}

						if ($themeName && array_key_exists($moduleKey, $GLOBALS[$nameVar])) {
							$this->modules_translations[$themeName][$moduleName][$templateName][$key]['trad'] = html_entity_decode($GLOBALS[$nameVar][$moduleKey], ENT_COMPAT, 'UTF-8');
						} else

						if (array_key_exists($defaultKey, $GLOBALS[$nameVar])) {
							$this->modules_translations[$themeName][$moduleName][$templateName][$key]['trad'] = html_entity_decode($GLOBALS[$nameVar][$defaultKey], ENT_COMPAT, 'UTF-8');
						} else

						if (array_key_exists($PhenyxShopKey, $GLOBALS[$nameVar])) {
							$this->modules_translations[$themeName][$moduleName][$templateName][$key]['trad'] = html_entity_decode($GLOBALS[$nameVar][$PhenyxShopKey], ENT_COMPAT, 'UTF-8');
						} else {
							$this->modules_translations[$themeName][$moduleName][$templateName][$key]['trad'] = '';
							$this->missing_translations++;
						}

						$this->modules_translations[$themeName][$moduleName][$templateName][$key]['use_sprintf'] = $this->checkIfKeyUseSprintf($key);
					}

				}

			}

		}

	}

	/**
	 * This method generate the form for PDF translations
	 *
	 * @return string
	 *
	 * @since 1.8.5.0
	 */
	public function initFormPDF() {

		$nameVar = $this->translations_informations[$this->type_selected]['var'];
		$GLOBALS[$nameVar] = [];
		$missingTranslationsPdf = [];

		$i18NDir = $this->translations_informations[$this->type_selected]['dir'];
		$defaultI18NFile = $i18NDir . $this->translations_informations[$this->type_selected]['file'];

		if (!$this->theme_selected) {
			$i18NFile = $defaultI18NFile;
		} else {
			$i18NDir = $this->translations_informations[$this->type_selected]['override']['dir'];
			$i18NFile = $i18NDir . $this->translations_informations[$this->type_selected]['override']['file'];
		}

		$this->checkDirAndCreate($i18NFile);

		if ((!file_exists($i18NFile) && !is_writable($i18NDir)) && !is_writable($i18NFile)) {
			$this->errors[] = sprintf(Tools::displayError('Cannot write into the "%s"'), $i18NFile);
		}

		@include $i18NFile;

		// if the override's translation file is empty load the default file

		if (!isset($GLOBALS[$nameVar]) || count($GLOBALS[$nameVar]) == 0) {
			@include $defaultI18NFile;
		}

		$prefixKey = 'PDF';
		$tabsArray = [$prefixKey => []];

		$filesByDirectory = $this->getFileToParseByTypeTranslation();

		foreach ($filesByDirectory as $type => $directories) {

			foreach ($directories as $dir => $files) {

				foreach ($files as $file) {

					if (!in_array($file, static::$ignore_folder) && file_exists($filePath = $dir . $file)) {

						if ($type == 'tpl') {

							if (file_exists($filePath) && is_file($filePath)) {
								// Get content for this file
								$content = file_get_contents($filePath);

								// Parse this content
								$matches = $this->userParseFile($content, $this->type_selected, 'tpl');

								foreach ($matches as $key) {

									if (isset($GLOBALS[$nameVar][$prefixKey . md5($key)])) {
										$tabsArray[$prefixKey][$key]['trad'] = (html_entity_decode($GLOBALS[$nameVar][$prefixKey . md5($key)], ENT_COMPAT, 'UTF-8'));
									} else {

										if (!isset($tabsArray[$prefixKey][$key]['trad'])) {
											$tabsArray[$prefixKey][$key]['trad'] = '';

											if (!isset($missingTranslationsPdf[$prefixKey])) {
												$missingTranslationsPdf[$prefixKey] = 1;
											} else {
												$missingTranslationsPdf[$prefixKey]++;
											}

										}

									}

									$tabsArray[$prefixKey][$key]['use_sprintf'] = $this->checkIfKeyUseSprintf($key);
								}

							}

						} else

						if (file_exists($filePath)) {
							$tabsArray = $this->parsePdfClass($filePath, 'php', $GLOBALS[$nameVar], $prefixKey, $tabsArray, $missingTranslationsPdf);
						}

					}

				}

			}

		}

		if ($this->compile_grid) {

			return [
				'tabsArray'           => $tabsArray,
				'missingTranslations' => $missingTranslationsPdf,
			];
		}

		$this->tpl_view_vars = array_merge(
			$this->tpl_view_vars,
			[
				'count'                => count($tabsArray['PDF']),
				'limit_warning'        => $this->displayLimitPostWarning(count($tabsArray['PDF'])),
				'mod_security_warning' => Tools::apacheModExists('mod_security'),
				'tabsArray'            => $tabsArray,
				'cancel_url'           => $this->context->link->getAdminLink('AdminTranslations'),
				'missing_translations' => $missingTranslationsPdf,
			]
		);

		$this->initToolbar();
		$this->base_tpl_view = 'translation_form.tpl';

		return parent::renderView();
	}

	/**
	 * Parse PDF class
	 *
	 * @since 1.4.5.0
	 *
	 * @param string $filePath  File to parse
	 * @param string $fileType  Type of file
	 * @param array  $langArray Contains expression in the chosen language
	 * @param string $tab       name      To use with the md5 key
	 * @param array  $tabsArray
	 * @param array  $countMissing
	 *
	 * @return array Array          Containing all datas needed for building the translation form
	 *
	 * @since 1.8.5.0
	 */
	protected function parsePdfClass($filePath, $fileType, $langArray, $tab, $tabsArray, &$countMissing) {

		// Get content for this file
		$content = file_get_contents($filePath);

		// Parse this content
		$matches = $this->userParseFile($content, $this->type_selected, $fileType);

		foreach ($matches as $key) {

			if (stripslashes(array_key_exists($tab . md5(addslashes($key)), $langArray))) {
				$tabsArray[$tab][$key]['trad'] = html_entity_decode($langArray[$tab . md5(addslashes($key))], ENT_COMPAT, 'UTF-8');
			} else {
				$tabsArray[$tab][$key]['trad'] = '';

				if (!isset($countMissing[$tab])) {
					$countMissing[$tab] = 1;
				} else {
					$countMissing[$tab]++;
				}

			}

			$tabsArray[$tab][$key]['use_sprintf'] = $this->checkIfKeyUseSprintf($key);
		}

		return $tabsArray;
	}

	/**
	 * Get mail pattern
	 *

	 * @return string
	 *
	 * @since 1.8.5.0
	 */
	protected function getMailPattern() {

		Tools::displayAsDeprecated('Email pattern is no longer used, emails are always saved like they are.');

		// Let the indentation like it.
		return '<!doctype html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>#title</title>
</head>
<body>
    #content
</body>
</html>';
	}

}
