<?php

/**
 * Class AgentCommissionControllerCore
 *
 * @since 1.8.1.0
 */
class AgentCommissionControllerCore extends FrontController {

    // @codingStandardsIgnoreStart
    /** @var bool $auth */
    public $auth = true;
    /** @var string $php_self */
    public $php_self = 'agent-commission';
    /** @var string $authRedirection */
    public $authRedirection = 'agent-commission';
    /** @var bool $ssl */
    public $ssl = true;

    public $params;
    // @codingStandardsIgnoreEnd

    /**
     * Set media
     *
     * @return void
     *
     * @since 1.8.1.0
     */
    public function setMedia() {

        parent::setMedia();
        $this->addCSS(_AGENT_CSS_DIR_ . 'index.css');
        $this->addCSS(_AGENT_CSS_DIR_ . 'dashboard.css');
        $this->addJS(_AGENT_JS_DIR_ . 'dashboard.js');
        Media::addJsDef([
            'AjaxAgentCommissionLink' => $this->context->link->getPageLink('agent-dashboard', true),

        ]);
    }

    public function postProcess() {

        parent::postProcess();

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

        parent::initContent();
        $agent = new SaleAgent($this->context->cookie->id_agent);
        $commissions = SaleAgentCommission::getCommissionDueBySaleAgent($agent->id);
        $invoices = SaleAgentCommission::getInvoiceBySaleAgent($agent->id);
        $this->context->smarty->assign(
            [
                'invoices'    => $invoices,
                'commissions' => $commissions,
                'agent'       => $agent,
            ]
        );

        $this->setTemplate(_PS_AGENT_DIR_ . 'commission.tpl');
    }

}
