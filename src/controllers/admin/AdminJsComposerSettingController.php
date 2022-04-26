<?php


class AdminJsComposerSettingControllerCore extends AdminController {

    public function __construct() {

        $this->bootstrap = true;
        $this->className = 'Configuration';
        $this->table = 'Configuration';

        parent::__construct();

        $arr = [];
        $arr[] = ['id' => 'no', 'name' => 'No'];
        $arr[] = ['id' => 'yes', 'name' => 'Yes'];
        $tab_arr[] = ['id' => 'general', 'name' => 'General Style'];
        $tab_arr[] = ['id' => 'classic', 'name' => 'Classic Style'];

        parent::__construct();

        $this->fields_options = [
            'email' => [
                'title'  => $this->l('General Setting for Visual Composer'),
                'icon'   => 'icon-cogs',
                'fields' => [
                    'vc_load_flex_js'    => [
                        'title'      => $this->l('Load Flexslider JS:'),
                        'desc'       => $this->l('if you want to load Flexslider JS from your theme or module.'),
                        'validation' => 'isGenericName',
                        'type'       => 'select',
                        'identifier' => 'id',
                        'list'       => $arr,
                    ],
                    'vc_load_flex_css'   => [
                        'title'      => $this->l('Load Flexslider CSS:'),
                        'desc'       => $this->l('if you want to load Flexslider CSS from your theme or module.'),
                        'validation' => 'isGenericName',
                        'type'       => 'select',
                        'identifier' => 'id',
                        'list'       => $arr,
                    ],
                    'vc_load_nivo_js'    => [
                        'title'      => $this->l('Load NivoSlider JS:'),
                        'desc'       => $this->l('if you want to load NivoSlider JS from your theme or module.'),
                        'validation' => 'isGenericName',
                        'type'       => 'select',
                        'identifier' => 'id',
                        'list'       => $arr,
                    ],
                    'vc_load_nivo_css'   => [
                        'title'      => $this->l('Load NivoSlider CSS:'),
                        'desc'       => $this->l('if you want to load NivoSlider CSS from your theme or module.'),
                        'validation' => 'isGenericName',
                        'type'       => 'select',
                        'identifier' => 'id',
                        'list'       => $arr,
                    ],
                    'vc_include_modules' => [
                        'title'      => $this->l('Include Modules'),
                        'desc'       => $this->l('You can include modules by putting modules name here(e.g. blockcms) to be appear in visual composer shortcodes list. Put a module name per line.'),
                        'type'       => 'textarea',
                        'identifier' => 'id',
                        'rows'       => 7,
                        'cols'       => 7,
                    ],
                    'vc_exclude_modules' => [
                        'title'      => $this->l('Exclude Modules'),
                        'desc'       => $this->l('You can exclude modules by putting modules name here(e.g. blockcms) to be removed from visual composer shortcodes list. Put a module name per line.'),
                        'type'       => 'textarea',
                        'identifier' => 'id',
                        'rows'       => 7,
                        'cols'       => 7,
                    ],
                ],
                'submit' => ['title' => $this->l('Save')],
            ],
        ];
    }

    public function setMedia() {

        parent::setMedia();
        $this->addCSS(__PS_BASE_URI__ . $this->admin_webpath . '/themes/default/css/black-tie/jquery-ui.css');
        $this->addJS(__PS_BASE_URI__ . $this->admin_webpath . '/js/jquery-ui/jquery-ui.js');

    }
	
	public function generateOptions() {
    	
      	$tabs = [];
       	$tabs['Liste des Hooks'] = [
            'key'     => 'hooks',
            'content' => $this->renderList(),
        ];
		
		$tabs['Liste des Fonctionnalité'] = [
            'key'     => 'fonctionality',
            'content' => $this->externalControllers(),
        ];
		if ($this->fields_options && is_array($this->fields_options)) {
			$this->tpl_option_vars['titleList'] = $this->l('List').' '.$this->toolbar_title[0];
			$this->tpl_option_vars['controller'] = Tools::getValue('controller');
			
            $helper = new HelperOptions();
            $this->setHelperDisplay($helper);
            $helper->toolbar_scroll = true;
            $helper->toolbar_btn = [
                'save' => [
                    'href' => '#',
                    'desc' => $this->l('Save'),
                ],
            ];
            $helper->id = $this->id;
            $helper->tpl_vars = $this->tpl_option_vars;
			
           
			$tabs['Réglages de Visual Composer'] = [
            'key'     => 'seetings',
            'content' => $helper->generateOptions($this->fields_options),
        ];

           
        }
		
		
        

        return $tabs;
    }

    public function initPageHeaderToolbar() {

        parent::initPageHeaderToolbar();
    }

    public function renderList() {

        $custom_hook = unserialize(Configuration::get('vc_custom_hook'));

        $hook_list = [];

        if ($custom_hook[0] == '') {
            unset($custom_hook[0]);
        }

        if (!empty($custom_hook)) {

            foreach ($custom_hook as $inc) {
                $inc = trim($inc);
                $hook_list[] = $inc;
            }

        }

        if (Tools::isSubmit('check_update')) {
            $Smartlisence = new Smartlisence();
            $this_val = [
                'version'      => JsComposer::$vc_version,
                'module_name'  => JsComposer::$vc_mode_name,
                'theme_name'   => basename(_THEME_DIR_),
                'purchase_key' => Tools::getValue('purchase_key'),
            ];
            //remove

            Configuration::deleteByName('jscomposer_update_timeout');

            $Smartlisence->checkUpdate($this_val);
        }

       
        

        $purchase_key = Configuration::get('jscomposer_purchase_key', '');
        $html = "<form method='post' action='' name='lisenceForm' >";

       
        $html .= '<div class="panel">';

        $html .= '<div class="panel-heading" >
            Hook Personalisé <span class="badge">' . count($hook_list) . '</span>
                <span class="panel-heading-action" style="width: 121px; font-size: 18px;">
                    <a id="desc-image_type-new" href="#" data-toggle="modal" data-target="#myModal">
                        <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="Add new Hook" data-html="true" data-placement="top">
                            <i class="process-icon-new" style="font-size: 20px;">Ajouter un nouveau hook</i>
                        </span>
                    </a>
                </div>';

        $html .= '<div class="table-responsive-row clearfix">';
        //-----------

        $html .= '<table class="table">';
        $html .= '<thead>';
        $html .= '<tr class="nodrag nodrop"> ';
        $html .= '<th class="fixed-width-xs">';
        $html .= '<span class="title_box active">Hook Name</span>';
        $html .= '</th> ';
        $html .= '<th class="fixed-width-xs center">';
        $html .= '<span class="title_box active">Description</span>';
        $html .= '</th> ';
        $html .= ' <th class="fixed-width-xs"> ';
        $html .= ' </th>';
        $html .= '</tr>';
        $html .= ' </thead>';
        $html .= '<tbody >';

        foreach ($hook_list as $inc) {
            $url = $_SERVER['REQUEST_URI'] . '&deleteCustomHook=' . $inc;
            $html .= '<tr> ';
            $html .= ' <td class=" fixed-width-xs"> ';
            $html .= $inc;
            $html .= '</td> ';
            $html .= ' <td class=" fixed-width-xs"> ';
            $html .= 'This block is attached to custom hook. To display it in .tpl file use:<strong> {hook h="' . $inc . '"}</strong>';
            $html .= '</td> ';
            $html .= '<td  class="">  ';
            $html .= '<div class="btn-group pull-right">';
            $html .= ' <a href="' . $url . '" class="btn btn-default confirm-delete" title="Delete" class="confirm-delete">';
            $html .= '  <i class="icon-trash"></i> Delete';
            $html .= ' </a>';
            $html .= ' </div>    ';
            $html .= '  </td>';
            $html .= ' </tr>';
        }

        if (empty($hook_list)) {
            $html .= ' <tr>';
            $html .= '<td class="list-empty" colspan="3">';
            $html .= ' <div class="list-empty-msg">';
            $html .= '<i class="icon-warning-sign list-empty-icon"></i>';
            $html .= '   No records found';
            $html .= ' </div>';
            $html .= '  </td>';
            $html .= '</tr> ';
        }

        $html .= '  </tbody>';
        $html .= ' </table>  ';

        //---------
        $html .= '</div></div>';
        $html .= '
<!-- Modal -->
<div id="myModal" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <form method="post" action="">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Add new hook</h4>
      </div>
      <div class="modal-body">
        <p>Put custom hook name</p>

        <input type="text" name="vc_custom_hook">

      </div>
      <div class="modal-footer">
        <input type="submit" class="btn btn-default value="submit" name="customhookadd" >
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
</form>
  </div>
</div>';

        $html .= '<script type="text/javascript">
    $(document).ready(function () {


        $(".confirm-delete").click(function () {
            if (confirm(\'Delete the Custom hook?\')) {
                return true;
            } else {
                return false;
            }
        });
    });
</script>';

        //$html .= $this->externalControllers();
        return $html;
    }

    public function externalControllers() {

        $controllers = Configuration::get('VC_ENQUEUED_CONTROLLERS');
        $controllers = Tools::jsonDecode($controllers, true);

        $template = _PS_MODULE_DIR_ . '/jscomposer/views/templates/admin/backend_hook_list.tpl';

        $this->context->smarty->assign([
            'controllers'     => $controllers,
            'baseDir'         => _PS_BASE_URL_ . __PS_BASE_URI__,
            'url_base'        => Context::getContext()->link->getAdminLink('AdminJsModuleList'),
            'url_admin_ajax'  => Context::getContext()->link->getAdminLink('AdminJsComposerAjax'),
            'url_module_name' => '',
            'url_status'      => '',
        ]);

        $html = '';

        $html = $this->context->smarty->fetch($template);

        return $html;
    }

    public function initContent() {

        if (Tools::isSubmit('deactivateVc')) {

            $this_val = [
                'version'      => JsComposer::$vc_version,
                'module_name'  => JsComposer::$vc_mode_name,
                'theme_name'   => basename(_THEME_DIR_),
                'purchase_key' => Tools::getValue('purchase_key'),
            ];
            $Smartlisence = new Smartlisence();
            $Smartlisence->deactivateModule($this_val);

        }

        if (Tools::isSubmit('activateVc')) {

            $this_val = [
                'version'      => JsComposer::$vc_version,
                'module_name'  => JsComposer::$vc_mode_name,
                'theme_name'   => basename(_THEME_DIR_),
                'purchase_key' => Tools::getValue('purchase_key'),
            ];
            $Smartlisence = new Smartlisence();
            $Smartlisence->activateModule($this_val);

        }

        if (Tools::isSubmit('updateVc')) {

            $this_val = [
                'version'      => JsComposer::$vc_version,
                'module_name'  => JsComposer::$vc_mode_name,
                'theme_name'   => basename(_THEME_DIR_),
                'purchase_key' => Tools::getValue('purchase_key'),
            ];
            $Smartlisence = new Smartlisence();
            $Smartlisence->updateModule($this_val);

        }

        if (isset($_REQUEST['deleteCustomHook'])) {
            $deleteCustomHook = $_REQUEST['deleteCustomHook'];

            if ($deleteCustomHook != '') {

                $old_hook_list = unserialize(Configuration::get('vc_custom_hook'));

                if ($old_hook_list == '') {
                    $old_hook_list = [];
                }

                $key = array_search($deleteCustomHook, $old_hook_list);

                unset($old_hook_list[$key]);

                $updated_hook_list = serialize($old_hook_list);
                Configuration::updateValue('vc_custom_hook', $updated_hook_list);
                $mod_obj = Module::getInstanceByName('jscomposer');
                $id_hook = Hook::getIdByName($deleteCustomHook);
                $mod_obj->unregisterHook($id_hook);

                $url = $this->context->link->getAdminLink('AdminJsComposerSetting', true);
                Tools::redirectAdmin($url);
            }

        }

        if (Tools::isSubmit('customhookadd')) {
            $Smartlisence = new Smartlisence();

            if ($Smartlisence->isActive()) {
                $old_hook_list = unserialize(Configuration::get('vc_custom_hook'));

                if (isset($old_hook_list) && ($old_hook_list == '')) {
                    $old_hook_list = [];
                }

                $new_hook = Tools::getValue('vc_custom_hook');

                if (in_array($new_hook, $old_hook_list)) {
                    $new_hook = '';
                } else {
                    $old_hook_list[] = $new_hook;
                }

                $updated_hook_list = serialize($old_hook_list);
                Configuration::updateValue('vc_custom_hook', $updated_hook_list);

                if ($new_hook != '') {
                   
                    $inc = trim($new_hook);

                    $mod_obj = Module::getInstanceByName('jscomposer');
                    $mod_obj->registerHook($inc);
                   
                }

            }

        }

        parent::initContent();
    }

}
