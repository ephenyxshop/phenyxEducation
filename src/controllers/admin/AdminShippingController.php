<?php


/**
 * Class AdminShippingControllerCore
 *
 * @since 1.9.1.0
 */
class AdminShippingControllerCore extends AdminController
{
    // @codingStandardsIgnoreStart
    protected $_fieldsHandling;
    // @codingStandardsIgnoreEnd

    /**
     * AdminShippingControllerCore constructor.
     *
     * @since 1.9.1.0
     */
    public function __construct()
    {
        $this->bootstrap = true;
        parent::__construct();
        $this->table = 'delivery';

        $carriers = Carrier::getCarriers($this->context->language->id, true, false, false, null, Carrier::PS_CARRIERS_AND_CARRIER_MODULES_NEED_RANGE);
        foreach ($carriers as $key => $carrier) {
            if ($carrier['is_free']) {
                unset($carriers[$key]);
            }
        }

        $carrierDefaultSort = [
            ['value' => Carrier::SORT_BY_PRICE, 'name' => $this->l('Price')],
            ['value' => Carrier::SORT_BY_POSITION, 'name' => $this->l('Position')],
        ];

        $carrierDefaultOrder = [
            ['value' => Carrier::SORT_BY_ASC, 'name' => $this->l('Ascending')],
            ['value' => Carrier::SORT_BY_DESC, 'name' => $this->l('Descending')],
        ];

        $this->fields_options = [
            'handling' => [
                'title'       => $this->l('Handling'),
                'icon'        => 'delivery',
                'fields'      => [
                    'PS_SHIPPING_HANDLING'    => [
                        'title'      => $this->l('Handling charges'),
                        'suffix'     => $this->context->currency->getSign().' '.$this->l('(tax excl.)'),
                        'cast'       => 'floatval',
                        'type'       => 'text',
                        'validation' => 'isPrice',
                    ],
                    'PS_SHIPPING_FREE_PRICE'  => [
                        'title'      => $this->l('Free shipping starts at'),
                        'suffix'     => $this->context->currency->getSign(),
                        'cast'       => 'floatval',
                        'type'       => 'text',
                        'validation' => 'isPrice',
                    ],
                    'PS_SHIPPING_FREE_WEIGHT' => [
                        'title'      => $this->l('Free shipping starts at'),
                        'suffix'     => Configuration::get('PS_WEIGHT_UNIT'),
                        'cast'       => 'floatval',
                        'type'       => 'text',
                        'validation' => 'isUnsignedFloat',
                    ],
                ],
                'description' =>
                    '<ul>
						<li>'.$this->l('If you set these parameters to 0, they will be disabled.').'</li>
						<li>'.$this->l('Coupons are not taken into account when calculating free shipping.').'</li>
					</ul>',
                'submit'      => ['title' => $this->l('Save')],
            ],
            'general'  => [
                'title'  => $this->l('Carrier options'),
                'fields' => [
                    'PS_CARRIER_DEFAULT'       => [
                        'title'      => $this->l('Default carrier'),
                        'desc'       => $this->l('Your shop\'s default carrier'),
                        'cast'       => 'intval',
                        'type'       => 'select',
                        'identifier' => 'id_carrier',
                        'list'       => array_merge(
                            [
                                -1 => ['id_carrier' => -1, 'name' => $this->l('Best price')],
                                -2 => ['id_carrier' => -2, 'name' => $this->l('Best grade')],
                            ],
                            Carrier::getCarriers((int) Configuration::get('PS_LANG_DEFAULT'), true, false, false, null, Carrier::ALL_CARRIERS)
                        ),
                    ],
                    'PS_CARRIER_DEFAULT_SORT'  => [
                        'title'      => $this->l('Sort by'),
                        'desc'       => $this->l('This will only be visible in the front office.'),
                        'cast'       => 'intval',
                        'type'       => 'select',
                        'identifier' => 'value',
                        'list'       => $carrierDefaultSort,
                    ],
                    'PS_CARRIER_DEFAULT_ORDER' => [
                        'title'      => $this->l('Order by'),
                        'desc'       => $this->l('This will only be visible in the front office.'),
                        'cast'       => 'intval',
                        'type'       => 'select',
                        'identifier' => 'value',
                        'list'       => $carrierDefaultOrder,
                    ],
                ],
                'submit' => ['title' => $this->l('Save')],
            ],
        ];
    }
	
	public function setMedia()
    {
        parent::setMedia();
		$this->addCSS(__PS_BASE_URI__.$this->admin_webpath.'/js/default/css/black-tie/jquery-ui.css');
		$this->addJquery('3.4.1');
		$this->addJS(__PS_BASE_URI__.$this->admin_webpath.'/js/jquery-ui/jquery-ui.js');
        
    }

    /**
     * Post processing
     *
     * @return bool
     *
     * @since 1.9.1.0
     */
    public function postProcess()
    {
        /* Shipping fees */
        if (Tools::isSubmit('submitFees'.$this->table)) {
            if ($this->tabAccess['edit'] === '1') {
                if (($idCarrier = (int) (Tools::getValue('id_carrier'))) && $idCarrier == ($idCarrier2 = (int) (Tools::getValue('id_carrier2')))) {
                    $carrier = new Carrier($idCarrier);
                    if (Validate::isLoadedObject($carrier)) {
                        /* Get configuration values */
                        $shippingMethod = $carrier->getShippingMethod();
                        $rangeTable = $carrier->getRangeTable();

                        $carrier->deleteDeliveryPrice($rangeTable);
                        $currentList = Carrier::getDeliveryPriceByRanges($rangeTable, $idCarrier);

                        /* Build prices list */
                        $priceList = [];
                        foreach ($_POST as $key => $value) {
                            if (strstr($key, 'fees_')) {
                                $tmpArray = explode('_', $key);

                                $price = number_format(abs(str_replace(',', '.', $value)), 6, '.', '');
                                $current = 0;
                                foreach ($currentList as $item) {
                                    if ($item['id_zone'] == $tmpArray[1] && $item['id_'.$rangeTable] == $tmpArray[2]) {
                                        $current = $item;
                                    }
                                }
                                if ($current && $price == $current['price']) {
                                    continue;
                                }

                                $priceList[] = [
                                    'id_range_price'  => ($shippingMethod == Carrier::SHIPPING_METHOD_PRICE) ? (int) $tmpArray[2] : null,
                                    'id_range_weight' => ($shippingMethod == Carrier::SHIPPING_METHOD_WEIGHT) ? (int) $tmpArray[2] : null,
                                    'id_carrier'      => (int) $carrier->id,
                                    'id_zone'         => (int) $tmpArray[1],
                                    'price'           => $price,
                                ];
                            }
                        }
                        /* Update delivery prices */
                        $carrier->addDeliveryPrice($priceList);
                        Tools::redirectAdmin(static::$currentIndex.'&conf=6&id_carrier='.$carrier->id.'&token='.$this->token);
                    } else {
                        $this->errors[] = Tools::displayError('An error occurred while attempting to update fees (cannot load carrier object).');
                    }
                } elseif (isset($idCarrier2)) {
                    $_POST['id_carrier'] = $idCarrier2;
                } else {
                    $this->errors[] = Tools::displayError('An error occurred while attempting to update fees (cannot load carrier object).');
                }
            } else {
                $this->errors[] = Tools::displayError('You do not have permission to edit this.');
            }
        } else {
            return parent::postProcess();
        }

        return false;
    }
}
