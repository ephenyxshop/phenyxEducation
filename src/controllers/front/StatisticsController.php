<?php

/**
 * Class StatisticsControllerCore
 *
 * @since 1.8.1.0
 */
class StatisticsControllerCore extends FrontController {

    // @codingStandardsIgnoreStart
    /** @var bool $display_header */
    public $display_header = false;
    /** @var bool $display_footer */
    public $display_footer = false;
    /** @var string $param_token */
    protected $param_token;
    // @codingStandardsIgnoreEnd

    /**
     * Post processing
     *
     * @return void
     *
     * @since 1.8.1.0
     */
    public function postProcess() {

        $this->param_token = Tools::getValue('token');

        if (!$this->param_token) {
            die;
        }

        if ($_POST['type'] == 'navinfo') {
            $this->processNavigationStats();
        } else if ($_POST['type'] == 'pagetime') {
            $this->processPageTime();
        } else {
            exit;
        }

    }

    /**
     * Log statistics on navigation (resolution, plugins, etc.)
     *
     * @return void
     *
     * @since 1.8.1.0
     */
    protected function processNavigationStats() {

        $idGuest = (int) Tools::getValue('id_guest');

        if (sha1($idGuest . _COOKIE_KEY_) != $this->param_token) {
            die;
        }

        $guest = new Guest((int) substr($_POST['id_guest'], 0, 10));
        $guest->javascript = true;
        $guest->screen_resolution_x = (int) substr($_POST['screen_resolution_x'], 0, 5);
        $guest->screen_resolution_y = (int) substr($_POST['screen_resolution_y'], 0, 5);
        $guest->screen_color = (int) substr($_POST['screen_color'], 0, 3);
        $guest->sun_java = (int) substr($_POST['sun_java'], 0, 1);
        $guest->adobe_flash = (int) substr($_POST['adobe_flash'], 0, 1);
        $guest->adobe_director = (int) substr($_POST['adobe_director'], 0, 1);
        $guest->apple_quicktime = (int) substr($_POST['apple_quicktime'], 0, 1);
        $guest->real_player = (int) substr($_POST['real_player'], 0, 1);
        $guest->windows_media = (int) substr($_POST['windows_media'], 0, 1);
        $guest->update();
    }

    /**
     * Log statistics on time spend on pages
     *
     * @return void
     *
     * @since 1.8.1.0
     */
    protected function processPageTime() {

        $idConnection = (int) Tools::getValue('id_connections');
        $time = (int) Tools::getValue('time');
        $timeStart = Tools::getValue('time_start');
        $idPage = (int) Tools::getValue('id_page');

        if (sha1($idConnection . $idPage . $timeStart . _COOKIE_KEY_) != $this->param_token) {
            die;
        }

        if ($time <= 0) {
            die;
        }

        Connection::setPageTime($idConnection, $idPage, substr($timeStart, 0, 19), $time);
    }

}
