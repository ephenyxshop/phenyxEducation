<?php

/**
 * Class HelperKpiCore
 *
 * @since 1.8.1.0
 */
class HelperKpiCore extends Helper {

    // @codingStandardsIgnoreStart
    /** @var string $base_folder */
    public $base_folder = 'helpers/kpi/';
    /** @var string $base_tpl */
    public $base_tpl = 'kpi.tpl';
    /** @var int $id */
    public $id;
    public $icon;
    public $chart;
    public $color;
    public $title;
    public $subtitle;
    public $value;
    public $data;
    public $source;
    public $refresh = true;
    public $href;
    public $tooltip;
    // @codingStandardsIgnoreEnd

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

        $this->tpl->assign(
            [
                'id'       => $this->id,
                'icon'     => $this->icon,
                'chart'    => (bool) $this->chart,
                'color'    => $this->color,
                'title'    => $this->title,
                'subtitle' => $this->subtitle,
                'value'    => $this->value,
                'data'     => $this->data,
                'source'   => $this->source,
                'refresh'  => $this->refresh,
                'href'     => $this->href,
                'tooltip'  => $this->tooltip,
            ]
        );

        return $this->tpl->fetch();
    }
}
