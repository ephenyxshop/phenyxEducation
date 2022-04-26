<?php

/**
 * Class HelperKpiRowCore
 *
 * @since 1.8.1.0
 */
class HelperKpiRowCore extends Helper {

    // @codingStandardsIgnoreStart
    /** @var string $base_folder */
    public $base_folder = 'helpers/kpi/';
    /** @var string $base_tpl */
    public $base_tpl = 'row.tpl';
    // @codingStandardsIgnoreEnd

    public $kpis = [];

    /**
     * @return mixed
     *
     * @throws Exception
     * @throws SmartyException
     * @since   1.8.1.0
     * @version 1.8.5.0
     */
    public function generate() {

        $this->tpl = $this->createTemplate($this->base_tpl);

        $this->tpl->assign('kpis', $this->kpis);

        return $this->tpl->fetch();
    }
}
