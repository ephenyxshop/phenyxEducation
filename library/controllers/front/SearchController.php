<?php

/**
 * Class SearchControllerCore
 *
 * @since 1.8.1.0
 */
class SearchControllerCore extends FrontController {

    // @codingStandardsIgnoreStart
    /** @var string $php_self */
    public $php_self = 'search';
    /** @var string $instant_search */
    public $instant_search;
    /** @var string $ajax_search */
    public $ajax_search;
    // @codingStandardsIgnoreEnd

    /**
     * Initialize search controller
     *
     * @see   FrontController::init()
     *
     * @return void
     *
     * @since 1.8.1.0
     */
    public function init() {

        parent::init();

        $this->instant_search = Tools::getValue('instantSearch');

        $this->ajax_search = Tools::getValue('ajaxSearch');

        if ($this->instant_search || $this->ajax_search) {
            $this->display_header = false;
            $this->display_footer = false;
        }

    }

    /**
     * Assign template vars related to page content
     *
     * @see   FrontController::initContent()
     *
     * @return void
     *
     * @since 1.8.1.0
     */
    public function initContent() {

        $originalQuery = Tools::getValue('q');
        $query = Tools::replaceAccentedChars(urldecode($originalQuery));

        if ($this->ajax_search) {
            $searchResults = Search::find((int) (Tools::getValue('id_lang')), $query, 1, 10, 'position', 'desc', true);

            if (is_array($searchResults)) {

                foreach ($searchResults as &$product) {
                    $product['product_link'] = $this->context->link->getProductLink($product['id_product'], $product['prewrite'], $product['crewrite']);
                }

                Hook::exec('actionSearch', ['expr' => $query, 'total' => count($searchResults)]);
            }

            $this->ajaxDie(json_encode($searchResults));
        }

        //Only controller content initialization when the user use the normal search
        parent::initContent();

        $productPerPage = isset($this->context->cookie->nb_item_per_page) ? (int) $this->context->cookie->nb_item_per_page : Configuration::get('EPH_PRODUCTS_PER_PAGE');

        if ($this->instant_search && !is_array($query)) {
            $this->productSort();
            $this->n = abs((int) (Tools::getValue('n', $productPerPage)));
            $this->p = abs((int) (Tools::getValue('p', 1)));
            $search = Search::find($this->context->language->id, $query, 1, 10, 'position', 'desc');
            Hook::exec('actionSearch', ['expr' => $query, 'total' => $search['total']]);
            $nbProducts = $search['total'];
            $this->pagination($nbProducts);

            $this->addColorsToProductList($search['result']);

            $this->context->smarty->assign(
                [
                    'products'        => $search['result'], // DEPRECATED (since to 1.4), not use this: conflict with block_cart module
                    'search_products' => $search['result'],
                    'nbProducts'      => $search['total'],
                    'search_query'    => $originalQuery,
                    'instant_search'  => $this->instant_search,
                    'homeSize'        => Image::getSize(ImageType::getFormatedName('home')),
                ]
            );
        } else if (($query = Tools::getValue('search_query', Tools::getValue('ref'))) && !is_array($query)) {
            $this->productSort();
            $this->n = abs((int) (Tools::getValue('n', $productPerPage)));
            $this->p = abs((int) (Tools::getValue('p', 1)));
            $originalQuery = $query;
            $query = Tools::replaceAccentedChars(urldecode($query));
            $search = Search::find($this->context->language->id, $query, $this->p, $this->n, $this->orderBy, $this->orderWay);

            if (is_array($search['result'])) {

                foreach ($search['result'] as &$product) {
                    $product['link'] .= (strpos($product['link'], '?') === false ? '?' : '&') . 'search_query=' . urlencode($query) . '&results=' . (int) $search['total'];
                }

            }

            Hook::exec('actionSearch', ['expr' => $query, 'total' => $search['total']]);
            $nbProducts = $search['total'];
            $this->pagination($nbProducts);

            $this->addColorsToProductList($search['result']);

            $this->context->smarty->assign(
                [
                    'products'        => $search['result'], // DEPRECATED (since to 1.4), not use this: conflict with block_cart module
                    'search_products' => $search['result'],
                    'nbProducts'      => $search['total'],
                    'search_query'    => $originalQuery,
                    'homeSize'        => Image::getSize(ImageType::getFormatedName('home')),
                ]
            );
        } else if (($tag = urldecode(Tools::getValue('tag'))) && !is_array($tag)) {
            $nbProducts = (int) (Search::searchTag($this->context->language->id, $tag, true));
            $this->pagination($nbProducts);
            $result = Search::searchTag($this->context->language->id, $tag, false, $this->p, $this->n, $this->orderBy, $this->orderWay);
            Hook::exec('actionSearch', ['expr' => $tag, 'total' => count($result)]);

            $this->addColorsToProductList($result);

            $this->context->smarty->assign(
                [
                    'search_tag'      => $tag,
                    'products'        => $result, // DEPRECATED (since to 1.4), not use this: conflict with block_cart module
                    'search_products' => $result,
                    'nbProducts'      => $nbProducts,
                    'homeSize'        => Image::getSize(ImageType::getFormatedName('home')),
                ]
            );
        } else {
            $this->context->smarty->assign(
                [
                    'products'        => [],
                    'search_products' => [],
                    'pages_nb'        => 1,
                    'nbProducts'      => 0,
                ]
            );
        }

        $this->context->smarty->assign(['add_prod_display' => Configuration::get('EPH_ATTRIBUTE_CATEGORY_DISPLAY'), 'comparator_max_item' => Configuration::get('EPH_COMPARATOR_MAX_ITEM')]);

        $this->setTemplate(_EPH_THEME_DIR_ . 'search.tpl');
    }

    /**
     * Display header
     *
     * @param bool $display
     *
     * @return void
     *
     * @since 1.8.1.0
     */
    public function displayHeader($display = true) {

        if (!$this->instant_search && !$this->ajax_search) {
            parent::displayHeader();
        } else {
            $this->context->smarty->assign('static_token', Tools::getToken(false));
        }

    }

    /**
     * Display footer
     *
     * @param bool $display
     *
     * @return void
     *
     * @since 1.8.1.0
     */
    public function displayFooter($display = true) {

        if (!$this->instant_search && !$this->ajax_search) {
            parent::displayFooter();
        }

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

        if (!$this->instant_search && !$this->ajax_search) {
            $this->addCSS(_THEME_CSS_DIR_ . 'product_list.css');
        }

    }

}
