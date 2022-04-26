<?php

class AdminLayerSliderControllerCore extends AdminController {

    public $lsDefaults;

    public function __construct() {

        $this->bootstrap = true;
        $this->table = 'layerslider';
        $this->className = 'FrontSlider';
        $this->publicName = $this->l('Gestion des Slides Front Office');

        $this->context = Context::getContext();

        parent::__construct();

        $this->lsDefaults = [

            'slider'  => [

                'createdWith'              => [
                    'value' => '',
                    'keys'  => 'createdWith',
                ],

                'sliderVersion'            => [
                    'value' => '',
                    'keys'  => 'sliderVersion',
                    'props' => [
                        'forceoutput' => true,
                    ],
                ],

                'status'                   => [
                    'value' => true,
                    'name'  => 'Status',
                    'keys'  => 'status',
                    'desc'  => 'Unpublished sliders will not be visible for your visitors until you re-enable this option. This also applies to scheduled sliders, thus leaving this option enabled is recommended in most cases.',
                    'props' => [
                        'meta' => true,
                    ],
                ],

                'scheduleStart'            => [
                    'value' => '',
                    'name'  => 'Schedule From',
                    'keys'  => 'schedule_start',
                    'desc'  => "<ul>
    <li>Scheduled sliders will only be visible to your visitors between the time period you set here.</li>
    <li>We're using international date and time format to avoid ambiguity.</li>
    <li>Clear the text field above and left it empty if you want to cancel the schedule.</li>
</ul>

<span>IMPORTANT:</span>
<ul>
    <li>You will still need to set the slider status as published,</li>
    <li>and insert the slider to the target page with one of the methods described in the <a href=\"http://docs.webshopworks.com/creative-slider/56-place-slider-on-the-site/\" target=\"_blank\">documentation</a>.</li>
</ul>",
                    'attrs' => [
                        'placeholder' => 'No schedule',
                    ],
                    'props' => [
                        'meta' => true,
                    ],
                ],

                'scheduleEnd'              => [
                    'value' => '',
                    'name'  => 'Schedule Until',
                    'keys'  => 'schedule_end',
                    'desc'  => '',
                    'attrs' => [
                        'placeholder' => 'No schedule',
                    ],
                    'props' => [
                        'meta' => true,
                    ],
                ],

                // ============= //
                // |   Layout  | //
                // ============= //

                'hook'                     => [
                    'value' => '',
                    'name'  => 'Module Position',
                    'keys'  => 'hook',
                    'desc'  => 'Slider will appear on the selected position.',
                    'props' => ['meta' => true],
                    'attrs' => [
                        'type'         => 'text',
                        'placeholder'  => '- None -',
                        'data-options' => $this->ls_get_hook_list(),
                    ],
                ],

                // responsive | fullwidth | fullsize | fixedsize
                'type'                     => [
                    'value' => 'responsive',
                    'name'  => 'Slider type',
                    'keys'  => 'type',
                    'desc'  => '',
                    'attrs' => [
                        'type' => 'hidden',
                    ],

                ],

                // The width of a new slider.
                'width'                    => [
                    'value' => 1280,
                    'name'  => 'Canvas width',
                    'keys'  => 'width',
                    'desc'  => 'The width of the slider canvas in pixels.',
                    'attrs' => [
                        'type'        => 'text',
                        'placeholder' => 1280,
                    ],
                    'props' => [
                        'meta' => true,
                    ],
                ],

                // The height of a new slider.
                'height'                   => [
                    'value' => 720,
                    'name'  => 'Canvas height',
                    'keys'  => 'height',
                    'desc'  => 'The height of the slider canvas in pixels.',
                    'attrs' => [
                        'type'        => 'text',
                        'placeholder' => 720,
                    ],
                    'props' => [
                        'meta' => true,
                    ],
                ],

                // The maximum width that the slider can get in responsive mode.
                'maxWidth'                 => [
                    'value' => '',
                    'name'  => 'Max-width',
                    'keys'  => 'maxwidth',
                    'desc'  => 'The maximum width your slider can take in pixels when responsive mode is enabled.',
                    'attrs' => [
                        'type'        => 'number',
                        'min'         => 0,
                        'placeholder' => '100%',
                    ],
                    'props' => [
                        'meta' => true,
                    ],
                ],

                // Turn on responsiveness under a given width of the slider.
                // Depends on: enabled fullWidth option. Defaults to: 0
                'responsiveUnder'          => [
                    'value'    => '',
                    'name'     => 'Responsive under',
                    'keys'     => ['responsiveunder', 'responsiveUnder'],
                    'desc'     => 'Turns on responsive mode in a full-width slider under the specified value in pixels. Can only be used with full-width mode.',
                    'advanced' => true,
                    'attrs'    => [
                        'type'        => 'number',
                        'min'         => 0,
                        'placeholder' => 'Canvas width',
                    ],
                ],

                'layersContrainer'         => [
                    'value' => '',
                    'keys'  => ['sublayercontainer', 'layersContainer'],
                ],

                'fullSizeMode'             => [
                    'value'   => 'normal',
                    'name'    => 'Mode',
                    'keys'    => 'fullSizeMode',
                    'desc'    => 'Select the sizing behavior of your full size sliders (e.g. hero scene).',
                    'options' => [
                        'normal'    => 'Normal',
                        'hero'      => 'Hero scene',
                        'fitheight' => 'Fit to parent height',
                    ],
                    'attrs'   => [
                        'min' => 0,
                    ],
                ],

                'allowFullscreen'          => [
                    'value' => false,
                    'name'  => 'Allow fullscreen mode',
                    'keys'  => 'allowFullscreen',
                    'desc'  => 'Visitors can enter OS native full-screen mode when double clicking on the slider.',
                ],

                'maxRatio'                 => [
                    'value'    => '',
                    'name'     => 'Maximum responsive ratio',
                    'keys'     => 'maxRatio',
                    'desc'     => 'The slider will not enlarge your layers above the target ratio. The value 1 will keep your layers in their initial size, without any upscaling.',
                    'advanced' => true,
                ],

                'fitScreenWidth'           => [
                    'value'    => true,
                    'name'     => 'Fit to screen width',
                    'keys'     => 'fitScreenWidth',
                    'desc'     => 'If enabled, the slider will always have the same width as the viewport, even if a theme uses a boxed layout, unless you choose the "Fit to parent height" full size mode.',
                    'advanced' => true,
                ],

                'preventSliderClip'        => [
                    'value'    => true,
                    'name'     => 'Prevent slider clipping',
                    'keys'     => 'preventSliderClip',
                    'desc'     => 'Ensures that the theme cannot clip parts of the slider when used in a boxed layout.',
                    'advanced' => true,
                ],

                'insertMethod'             => [
                    'value'   => 'prependTo',
                    'name'    => 'Move the slider by',
                    'keys'    => 'insertMethod',
                    'desc'    => 'Move your slider to a different part of the page by providing a jQuery DOM manipulation method & selector for the target destination.',
                    'options' => [
                        'prependTo'    => 'prepending to',
                        'appendTo'     => 'appending to',
                        'insertBefore' => 'inserting before',
                        'insertAfter'  => 'inserting after',
                    ],
                ],

                'insertSelector'           => [
                    'value' => '',
                    'keys'  => 'insertSelector',
                    'attrs' => [
                        'placeholder' => '#id, .class',
                    ],
                ],

                'clipSlideTransition'      => [
                    'value'    => 'disabled',
                    'name'     => 'Clip slide transition',
                    'keys'     => 'clipSlideTransition',
                    'desc'     => 'Choose on which axis (if any) you want to clip the overflowing content (i.e. that breaks outside of the slider bounds).',
                    'advanced' => true,
                    'options'  => [
                        'disabled' => 'Do not hide',
                        'enabled'  => 'Hide on both axis',
                        'x'        => 'X Axis',
                        'y'        => 'Y Axis',
                    ],
                ],

                // == COMPATIBILITY ==

                'responsiveness'           => [
                    'value' => true,
                    'keys'  => 'responsive',
                    'props' => [
                        'meta'   => true,
                        'output' => true,
                    ],
                ],
                'fullWidth'                => [
                    'value' => false,
                    'keys'  => 'forceresponsive',
                    'props' => [
                        'meta'   => true,
                        'output' => true,
                    ],
                ],

                // == END OF COMPATIBILITY ==

                'slideBGSize'              => [
                    'value'   => 'cover',
                    'name'    => 'Background size',
                    'keys'    => 'slideBGSize',
                    'desc'    => 'This will be used as a default on all slides, unless you choose to explicitly override it on a per slide basis.',
                    'options' => [
                        'auto'      => 'Auto',
                        'cover'     => 'Cover',
                        'contain'   => 'Contain',
                        '100% 100%' => 'Stretch',
                    ],
                ],

                'slideBGPosition'          => [
                    'value'   => '50% 50%',
                    'name'    => 'Background position',
                    'keys'    => 'slideBGPosition',
                    'desc'    => 'This will be used as a default on all slides, unless you choose the explicitly override it on a per slide basis.',
                    'options' => [
                        '0% 0%'     => 'left top',
                        '0% 50%'    => 'left center',
                        '0% 100%'   => 'left bottom',
                        '50% 0%'    => 'center top',
                        '50% 50%'   => 'center center',
                        '50% 100%'  => 'center bottom',
                        '100% 0%'   => 'right top',
                        '100% 50%'  => 'right center',
                        '100% 100%' => 'right bottom',
                    ],
                ],

                'parallaxSensitivity'      => [
                    'value' => 10,
                    'name'  => 'Parallax sensitivity',
                    'keys'  => 'parallaxSensitivity',
                    'desc'  => 'Increase or decrease the sensitivity of parallax content when moving your mouse cursor or tilting your mobile device.',
                ],

                'parallaxCenterLayers'     => [
                    'value'   => 'center',
                    'name'    => 'Parallax center layers',
                    'keys'    => 'parallaxCenterLayers',
                    'desc'    => 'Choose a center point for parallax content where all layers will be aligned perfectly according to their original position.',
                    'options' => [
                        'center' => 'At center of the viewport',
                        'top'    => 'At the top of the viewport',
                    ],
                ],

                'parallaxCenterDegree'     => [
                    'value' => 40,
                    'name'  => 'Parallax center degree',
                    'keys'  => 'parallaxCenterDegree',
                    'desc'  => 'Provide a comfortable holding position (in degrees) for mobile devices, which should be the center point for parallax content where all layers should align perfectly.',
                ],

                'parallaxScrollReverse'    => [
                    'value' => false,
                    'name'  => 'Reverse scroll direction',
                    'keys'  => 'parallaxScrollReverse',
                    'desc'  => 'Your parallax layers will move to the opposite direction when scrolling the page.',
                ],

                // ================= //
                // |    Mobile    | //
                // ================= //

                'optimizeForMobile'        => [
                    'value'    => true,
                    'name'     => 'Optimize for mobile',
                    'keys'     => 'optimizeForMobile',
                    'advanced' => true,
                    'desc'     => 'Enable optimizations on mobile devices to avoid performance issues (e.g. fewer tiles in slide transitions, reducing performance-heavy effects with very similar results, etc).',
                ],

                // Disable the slider on mobile devices.
                // Defaults to: false
                'disableOnMobile'          => [
                    'value' => false,
                    'name'  => 'Disable on mobile',
                    'keys'  => 'disableonmobile',
                    'desc'  => 'Disable the slider on mobile devices.',
                    'props' => ['meta' => true],
                ],

                // Disable the slider on tablet devices.
                // Defaults to: false
                'disableOnTablet'          => [
                    'value' => false,
                    'name'  => 'Disable on tablet',
                    'keys'  => 'disableontablet',
                    'desc'  => 'Disable the slider on tablet devices.',
                    'props' => ['meta' => true],
                ],

                // Disable the slider on desktop devices.
                // Defaults to: false
                'disableOnDesktop'         => [
                    'value' => false,
                    'name'  => 'Disable on desktop',
                    'keys'  => 'disableondesktop',
                    'desc'  => 'Disable the slider on desktop devices.',
                    'props' => ['meta' => true],
                ],

                // Hides the slider under the given value of browser width in pixels.
                // Defaults to: 0
                'hideUnder'                => [
                    'value' => '',
                    'name'  => 'Hide under',
                    'keys'  => ['hideunder', 'hideUnder'],
                    'desc'  => 'Hides the slider when the viewport width goes under the specified value.',
                    'attrs' => [
                        'type' => 'number',
                        'min'  => -1,
                    ],
                ],

                // Hides the slider over the given value of browser width in pixel.
                // Defaults to: 100000
                'hideOver'                 => [
                    'value' => '',
                    'name'  => 'Hide over',
                    'keys'  => ['hideover', 'hideOver'],
                    'desc'  => 'Hides the slider when the viewport becomes wider than the specified value.',
                    'attrs' => [
                        'type' => 'number',
                        'min'  => -1,
                    ],
                ],

                'slideOnSwipe'             => [
                    'value' => true,
                    'name'  => 'Use slide effect when swiping',
                    'keys'  => 'slideOnSwipe',
                    'desc'  => 'Ignore selected slide transitions and use sliding effects only when users are changing slides with a swipe gesture on mobile devices.',
                ],

                // ================ //
                // |   Slideshow  | //
                // ================ //

                // Automatically start slideshow.
                'autoStart'                => [
                    'value' => true,
                    'name'  => 'Auto-start slideshow',
                    'keys'  => ['autostart', 'autoStart'],
                    'desc'  => 'Slideshow will automatically start after page load.',
                ],

                'startInViewport'          => [
                    'value' => true,
                    'name'  => 'Start only in viewport',
                    'keys'  => ['startinviewport', 'startInViewport'],
                    'desc'  => 'The slider will not start until it becomes visible.',
                ],

                'hashChange'               => [
                    'value'    => false,
                    'name'     => 'Change URL hash',
                    'keys'     => 'hashChange',
                    'desc'     => 'Updates the hash in the page URL when changing slides based on the deeplinks you’ve set to your slides. This makes it possible to share URLs that will start the slider with the currently visible slide.',
                    'advanced' => true,
                ],

                'pauseLayers'              => [
                    'value'    => false,
                    'name'     => 'Pause layers',
                    'keys'     => 'pauseLayers',
                    'desc'     => 'If you enable this option, layer transitions will not start playing as long the slideshow is in a paused state.',
                    'advanced' => true,
                ],

                'pauseOnHover'             => [
                    'value'   => 'enabled',
                    'name'    => 'Pause on hover',
                    'keys'    => ['pauseonhover', 'pauseOnHover'],
                    'options' => [
                        'disabled'   => 'Do nothing',
                        'enabled'    => 'Pause slideshow',
                        'layers'     => 'Pause slideshow and layer transitions',
                        'looplayers' => 'Pause slideshow and layer transitions, including loops',
                    ],
                    'desc'    => 'Decide what should happen when you move your mouse cursor over the slider.',
                ],

                // The starting slide of a slider. Non-index value, starts with 1.
                'firstSlide'               => [
                    'value' => 1,
                    'name'  => 'Start with slide',
                    'keys'  => ['firstlayer', 'firstSlide'],
                    'desc'  => 'The slider will start with the specified slide. You can also use the value "random".',
                    'attrs' => ['type' => 'text', 'data-options' => '["random"]'],
                ],

                // Use global shortcuts to control the slider.
                'keybNavigation'           => [
                    'value' => true,
                    'name'  => 'Keyboard navigation',
                    'keys'  => ['keybnav', 'keybNav'],
                    'desc'  => 'You can navigate through slides with the left and right arrow keys.',
                ],

                // Accepts touch gestures if enabled.
                'touchNavigation'          => [
                    'value' => true,
                    'name'  => 'Touch navigation',
                    'keys'  => ['touchnav', 'touchNav'],
                    'desc'  => 'Gesture-based navigation when swiping on touch-enabled devices.',
                ],

                'playByScroll'             => [
                    'value'   => false,
                    'name'    => 'Play By Scroll',
                    'keys'    => 'playByScroll',
                    'desc'    => 'Play the slider by scrolling the web page. <a href="https://creativeslider.webshopworks.com/play-by-scroll-26" target="_blank">Click here</a> to see a live example.',
                    'premium' => true,
                ],

                'playByScrollSpeed'        => [
                    'value'   => 1,
                    'name'    => 'Play By Scroll Speed',
                    'keys'    => 'playByScrollSpeed',
                    'desc'    => 'Play By Scroll speed multiplier.',
                    'premium' => true,
                ],

                'playByScrollStart'        => [
                    'value'   => false,
                    'name'    => 'Start immediately',
                    'keys'    => 'playByScrollStart',
                    'desc'    => 'Instead of freezing the slider until visitors start scrolling, the slider will automatically start playback and will only pause at the first keyframe.',
                    'premium' => true,
                ],

                // Number of loops taking by the slideshow.
                // Depends on: shuffle. Defaults to: 0 => infinite
                'loops'                    => [
                    'value' => 0,
                    'name'  => 'Cycles',
                    'keys'  => ['loops', 'cycles'],
                    'desc'  => 'Number of cycles if slideshow is enabled.',
                    'attrs' => [
                        'type' => 'number',
                        'min'  => 0,
                    ],
                ],

                // The slideshow will always stop at the given number of
                // loops, even when the user restarts slideshow.
                // Depends on: loop. Defaults to: true
                'forceLoopNumber'          => [
                    'value'    => true,
                    'name'     => 'Force number of cycles',
                    'keys'     => ['forceloopnum', 'forceCycles'],
                    'advanced' => true,
                    'desc'     => 'The slider will always stop at the given number of cycles, even if the slideshow restarts.',
                ],

                // The slideshow will change slides in random order.
                'shuffle'                  => [
                    'value' => false,
                    'name'  => 'Shuffle mode',
                    'keys'  => ['randomslideshow', 'shuffleSlideshow'],
                    'desc'  => 'Slideshow will proceed in random order. This feature does not work with looping.',
                ],

                // Whether slideshow should goind backwards or not
                // when you switch to a previous slide.
                'twoWaySlideshow'          => [
                    'value'    => false,
                    'name'     => 'Two way slideshow',
                    'keys'     => ['twowayslideshow', 'twoWaySlideshow'],
                    'advanced' => true,
                    'desc'     => 'Slideshow can go backwards if someone switches to a previous slide.',
                ],

                'forceLayersOutDuration'   => [
                    'value'    => 750,
                    'name'     => 'Forced animation duration',
                    'keys'     => 'forceLayersOutDuration',
                    'advanced' => true,
                    'desc'     => 'The animation speed in milliseconds when the slider forces remaining layers out of scene before swapping slides.',
                    'attrs'    => [
                        'min' => 0,
                    ],
                ],

                // ================= //
                // |   Appearance  | //
                // ================= //

                // The default skin.
                'skin'                     => [
                    'value' => 'v6',
                    'name'  => 'Skin',
                    'keys'  => 'skin',
                    'desc'  => "The skin used for this slider. The 'noskin' skin is a border- and buttonless skin. Your custom skins will appear in the list when you create their folders.", "LayerSlider",
                ],

                'sliderFadeInDuration'     => [
                    'value'    => 350,
                    'name'     => 'Initial fade duration',
                    'keys'     => ['sliderfadeinduration', 'sliderFadeInDuration'],
                    'advanced' => true,
                    'desc'     => 'Change the duration of the initial fade animation when the page loads. Enter 0 to disable fading.',
                    'attrs'    => [
                        'min' => 0,
                    ],
                ],

                'sliderClasses'            => [
                    'value' => '',
                    'name'  => 'Slider Classes',
                    'keys'  => 'sliderclass',
                    'desc'  => 'One or more space-separated class names to be added to the slider container element.',
                    'props' => [
                        'meta' => true,
                    ],
                ],

                // Some CSS values you can append on each slide individually
                // to make some adjustments if needed.
                'sliderStyle'              => [
                    'value' => 'margin-bottom: 0px;',
                    'name'  => 'Slider CSS',
                    'keys'  => ['sliderstyle', 'sliderStyle'],
                    'desc'  => 'You can enter custom CSS to change some style properties on the slider wrapper element. More complex CSS should be applied with the Custom Styles Editor.',
                    'props' => [
                        'meta' => true,
                    ],
                ],

                // Global background color on all slides.
                'globalBGColor'            => [
                    'value' => '',
                    'name'  => 'Background color',
                    'keys'  => ['backgroundcolor', 'globalBGColor'],
                    'desc'  => 'Global background color of the slider. Slides with non-transparent background will cover this one. You can use all CSS methods such as HEX or RGB(A) values.',
                ],

                // Global background image on all slides.
                'globalBGImage'            => [
                    'value' => '',
                    'name'  => 'Background image',
                    'keys'  => ['backgroundimage', 'globalBGImage'],
                    'desc'  => 'Global background image of the slider. Slides with non-transparent backgrounds will cover it. This image will not scale in responsive mode.',
                ],

                'globalBGImageId'          => [
                    'value' => '',
                    'keys'  => ['backgroundimageId', 'globalBGImageId'],
                    'props' => [
                        'meta' => true,
                    ],
                ],

                // Global background image repeat
                'globalBGRepeat'           => [
                    'value'   => 'no-repeat',
                    'name'    => 'Background repeat',
                    'keys'    => 'globalBGRepeat',
                    'desc'    => 'Global background image repeat.',
                    'options' => [
                        'no-repeat' => 'No-repeat',
                        'repeat'    => 'Repeat',
                        'repeat-x'  => 'Repeat-x',
                        'repeat-y'  => 'Repeat-y',
                    ],
                ],

                // Global background image behavior
                'globalBGAttachment'       => [
                    'value'   => 'scroll',
                    'name'    => 'Background behavior',
                    'keys'    => 'globalBGAttachment',
                    'desc'    => 'Choose between a scrollable or fixed global background image.',
                    'options' => [
                        'scroll' => 'Scroll',
                        'fixed'  => 'Fixed',
                    ],
                ],

                // Global background image position
                'globalBGPosition'         => [
                    'value' => '50% 50%',
                    'name'  => 'Background position',
                    'keys'  => 'globalBGPosition',
                    'desc'  => 'Global background image position of the slider. The first value is the horizontal position and the second value is the vertical.',
                ],

                // Global background image size
                'globalBGSize'             => [
                    'value' => 'auto',
                    'name'  => 'Background size',
                    'keys'  => 'globalBGSize',
                    'desc'  => 'Global background size of the slider. You can set the size in pixels, percentages, or constants: auto | cover | contain ',
                    'attrs' => ['data-options' => '[{
                "name": "auto",
                "value": "auto"
            }, {
                "name": "cover",
                "value": "cover"
            }, {
                "name": "contain",
                "value": "contain"
            }, {
                "name": "stretch",
                "value": "100% 100%"
            }]'],
                ],

                // ================= //
                // |   Navigation  | //
                // ================= //

                // Show the next and previous buttons.
                'navPrevNextButtons'       => [
                    'value' => true,
                    'name'  => 'Show Prev & Next buttons',
                    'keys'  => ['navprevnext', 'navPrevNext'],
                    'desc'  => 'Disabling this option will hide the Prev and Next buttons.',
                ],

                // Show the next and previous buttons
                // only when hovering over the slider.
                'hoverPrevNextButtons'     => [
                    'value' => true,
                    'name'  => 'Show Prev & Next buttons on hover',
                    'keys'  => ['hoverprevnext', 'hoverPrevNext'],
                    'desc'  => 'Show the buttons only when someone moves the mouse cursor over the slider. This option depends on the previous setting.',
                ],

                // Show the start and stop buttons
                'navStartStopButtons'      => [
                    'value' => true,
                    'name'  => 'Show Start & Stop buttons',
                    'keys'  => ['navstartstop', 'navStartStop'],
                    'desc'  => 'Disabling this option will hide the Start & Stop buttons.',
                ],

                // Show the slide buttons or thumbnails.
                'navSlideButtons'          => [
                    'value' => true,
                    'name'  => 'Show slide navigation buttons',
                    'keys'  => ['navbuttons', 'navButtons'],
                    'desc'  => 'Disabling this option will hide slide navigation buttons or thumbnails.',
                ],

                // Show the slider buttons or thumbnails
                // ony when hovering over the slider.
                'hoverSlideButtons'        => [
                    'value' => false,
                    'name'  => 'Slide navigation on hover',
                    'keys'  => ['hoverbottomnav', 'hoverBottomNav'],
                    'desc'  => 'Slide navigation buttons (including thumbnails) will be shown on mouse hover only.',
                ],

                // Show bar timer
                'barTimer'                 => [
                    'value' => false,
                    'name'  => 'Show bar timer',
                    'keys'  => ['bartimer', 'showBarTimer'],
                    'desc'  => 'Show the bar timer to indicate slideshow progression.',
                ],

                // Show circle timer. Requires CSS3 capable browser.
                // This setting will overrule the 'barTimer' option.
                'circleTimer'              => [
                    'value' => true,
                    'name'  => 'Show circle timer',
                    'keys'  => ['circletimer', 'showCircleTimer'],
                    'desc'  => 'Use circle timer to indicate slideshow progression.',
                ],

                'slideBarTimer'            => [
                    'value' => false,
                    'name'  => 'Show slidebar timer',
                    'keys'  => ['slidebartimer', 'showSlideBarTimer'],
                    'desc'  => 'You can grab the slidebar timer playhead and seek the whole slide real-time like a movie.',
                ],

                // ========================== //
                // |  Thumbnail navigation  | //
                // ========================== //

                // Use thumbnails for slide buttons
                // Depends on: navSlideButtons.
                // Possible values: 'disabled', 'hover', 'always'
                'thumbnailNavigation'      => [
                    'value'   => 'hover',
                    'name'    => 'Thumbnail navigation',
                    'keys'    => ['thumb_nav', 'thumbnailNavigation'],
                    'desc'    => 'Use thumbnail navigation instead of slide bullet buttons.',
                    'options' => [
                        'disabled' => 'Disabled',
                        'hover'    => 'Hover',
                        'always'   => 'Always',
                    ],
                ],

                // The width of the thumbnail area in percents.
                'thumbnailAreaWidth'       => [
                    'value' => '60%',
                    'name'  => 'Thumbnail container width',
                    'keys'  => ['thumb_container_width', 'tnContainerWidth'],
                    'desc'  => 'The width of the thumbnail area relative to the slider size.',
                ],

                // Thumbnails' width in pixels.
                'thumbnailWidth'           => [
                    'value' => 100,
                    'name'  => 'Thumbnail width',
                    'keys'  => ['thumb_width', 'tnWidth'],
                    'desc'  => 'The width of thumbnails in the navigation area.',
                    'attrs' => [
                        'min' => 0,
                    ],
                ],

                // Thumbnails' height in pixels.
                'thumbnailHeight'          => [
                    'value' => 60,
                    'name'  => 'Thumbnail height',
                    'keys'  => ['thumb_height', 'tnHeight'],
                    'desc'  => 'The height of thumbnails in the navigation area.',
                    'attrs' => [
                        'min' => 0,
                    ],
                ],

                // The opacity of the active thumbnail in percents.
                'thumbnailActiveOpacity'   => [
                    'value' => 35,
                    'name'  => 'Active thumbnail opacity',
                    'keys'  => ['thumb_active_opacity', 'tnActiveOpacity'],
                    'desc'  => "Opacity in percentage of the active slide's thumbnail.", "LayerSlider",
                    'attrs' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],

                // The opacity of inactive thumbnails in percents.
                'thumbnailInactiveOpacity' => [
                    'value' => 100,
                    'name'  => 'Inactive thumbnail opacity',
                    'keys'  => ['thumb_inactive_opacity', 'tnInactiveOpacity'],
                    'desc'  => 'Opacity in percentage of inactive slide thumbnails.',
                    'attrs' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],

                // ============ //
                // |  Videos  | //
                // ============ //

                // Automatically starts vidoes on the given slide.
                'autoPlayVideos'           => [
                    'value' => true,
                    'name'  => 'Automatically play videos',
                    'keys'  => ['autoplayvideos', 'autoPlayVideos'],
                    'desc'  => 'Videos will be automatically started on the active slide.',
                ],

                // Automatically pauses the slideshow when a video is playing.
                // Auto means it only pauses the slideshow while the video is playing.
                // Possible values: 'auto', 'enabled', 'disabled'
                'autoPauseSlideshow'       => [
                    'value'   => 'auto',
                    'name'    => 'Pause slideshow',
                    'keys'    => ['autopauseslideshow', 'autoPauseSlideshow'],
                    'desc'    => 'The slideshow can temporally be paused while videos are playing. You can choose to permanently stop the pause until manual restarting.',
                    'options' => [
                        'auto'     => 'While playing',
                        'enabled'  => 'Permanently',
                        'disabled' => 'No action',
                    ],
                ],

                // The preview image quality of a YouTube video.
                // Some videos doesn't have HD preview images and
                // you may have to lower the quality settings.
                // Possible values:
                // 'maxresdefault.jpg',
                // 'hqdefault.jpg',
                // 'mqdefault.jpg',
                // 'default.jpg'
                'youtubePreviewQuality'    => [
                    'value'   => 'maxresdefault.jpg',
                    'name'    => 'Youtube preview',
                    'keys'    => ['youtubepreview', 'youtubePreview'],
                    'desc'    => 'The automatically fetched preview image quaility for YouTube videos when you do not set your own. Please note, some videos do not have HD previews, and you may need to choose a lower quaility.',
                    'options' => [
                        'maxresdefault.jpg' => 'Maximum quality',
                        'hqdefault.jpg'     => 'High quality',
                        'mqdefault.jpg'     => 'Medium quality',
                        'default.jpg'       => 'Default quality',
                    ],
                ],

                // ========== //
                // |  Misc  | //
                // ========== //

                // Ignores the host/domain names in URLS by converting the to
                // relative format. Useful when you move your site.
                // Prevents linking content from 3rd party servers.
                'relativeURLs'             => [
                    'value' => false,
                    'name'  => 'Use relative URLs',
                    'keys'  => 'relativeurls',
                    'desc'  => 'Use relative URLs for local images. This setting could be important when moving your PS installation.',
                    'props' => [
                        'meta' => true,
                    ],
                ],

                'allowRestartOnResize'     => [
                    'value'    => false,
                    'name'     => 'Allow restarting slides on resize',
                    'keys'     => 'allowRestartOnResize',
                    'desc'     => 'Certain transformation and transition options cannot be updated on the fly when the browser size or device orientation changes. By enabling this option, the slider will automatically detect such situations and will restart the itself to preserve its appearance.',
                    'advanced' => true,
                ],

                'useSrcset'                => [
                    'value' => true,
                    'name'  => 'Use srcset attribute',
                    'keys'  => 'useSrcset',
                    'desc'  => 'The srcset attribute allows loading dynamically scaled images based on screen resolution. It can save bandwidth and allow using retina-ready images on high resolution devices. In some rare edge cases, this option might cause blurry images.',
                ],

                'preferBlendMode'          => [
                    'value'    => 'disabled',
                    'name'     => 'Prefer Blend Mode',
                    'keys'     => 'preferBlendMode',
                    'desc'     => 'Enable this option to avoid blend mode issues with slide transitions. Due to technical limitations, this will also clip your slide transitions regardless of your settings.',
                    'options'  => [
                        'enabled'  => 'Enabled',
                        'disabled' => 'Disabled',
                    ],
                    'advanced' => true,
                ],

                // ============== //
                // |  YourLogo  | //
                // ============== //

                // Places a fixed image on the top of the slider.
                'yourLogoImage'            => [
                    'value' => '',
                    'name'  => 'YourLogo',
                    'keys'  => ['yourlogo', 'yourLogo'],
                    'desc'  => 'A fixed image layer can be shown above the slider that remains still throughout the whole slider. Can be used to display logos or watermarks.',
                ],

                // Custom CSS style settings for the YourLogo image.
                // Depends on: yourLogoImage
                'yourLogoStyle'            => [
                    'value' => 'left: -10px; top: -10px;',
                    'name'  => 'YourLogo style',
                    'keys'  => ['yourlogostyle', 'yourLogoStyle'],
                    'desc'  => 'CSS properties to control the image placement and appearance.',
                ],

                // Linking the YourLogo image to a given URL.
                // Depends on: yourLogoImage
                'yourLogoLink'             => [
                    'value' => '',
                    'name'  => 'YourLogo link',
                    'keys'  => ['yourlogolink', 'yourLogoLink'],
                    'desc'  => 'Enter a URL to link the YourLogo image.',
                ],

                // Link target for yourLogoLink.
                // Depends on: yourLogoLink
                'yourLogoTarget'           => [
                    'value'   => '_self',
                    'name'    => 'Link target',
                    'keys'    => ['yourlogotarget', 'yourLogoTarget'],
                    'desc'    => '',
                    'options' => [
                        '_self'   => 'Open on the same page',
                        '_blank'  => 'Open on new page',
                        '_parent' => 'Open in parent frame',
                        '_top'    => 'Open in main frame',
                    ],
                ],

                // Post options
                'postType'                 => [
                    'value' => '',
                    'keys'  => 'post_type',
                    'props' => [
                        'meta' => true,
                    ],
                ],

                'postOrderBy'              => [
                    'value'   => 'date',
                    'keys'    => 'post_orderby',
                    'options' => [
                        'date'          => 'Date Created',
                        'modified'      => 'Last Modified',
                        'ID'            => 'Post ID',
                        'title'         => 'Post Title',
                        'comment_count' => 'Number of Comments',
                        'rand'          => 'Random',
                    ],
                    'props'   => [
                        'meta' => true,
                    ],
                ],

                'postOrder'                => [
                    'value'   => 'DESC',
                    'keys'    => 'post_order',
                    'options' => [
                        'ASC'  => 'Ascending',
                        'DESC' => 'Descending',
                    ],
                    'props'   => [
                        'meta' => true,
                    ],
                ],

                'postCategories'           => [
                    'value' => '',
                    'keys'  => 'post_categories',
                    'props' => [
                        'meta' => true,
                    ],
                ],

                'postTags'                 => [
                    'value' => '',
                    'keys'  => 'post_tags',
                    'props' => [
                        'meta' => true,
                    ],
                ],

                'postTaxonomy'             => [
                    'value' => '',
                    'keys'  => 'post_taxonomy',
                    'props' => [
                        'meta' => true,
                    ],
                ],

                // Old and obsolete API
                'cbInit'                   => [
                    'value' => "function(element) {\r\n\r\n}",
                    'keys'  => ['cbinit', 'cbInit'],
                    'props' => [
                        'meta' => true,
                    ],
                ],

                'cbStart'                  => [
                    'value' => "function(data) {\r\n\r\n}",
                    'keys'  => ['cbstart', 'cbStart'],
                    'props' => [
                        'meta' => true,
                    ],
                ],

                'cbStop'                   => [
                    'value' => "function(data) {\r\n\r\n}",
                    'keys'  => ['cbstop', 'cbStop'],
                    'props' => [
                        'meta' => true,
                    ],
                ],

                'cbPause'                  => [
                    'value' => "function(data) {\r\n\r\n}",
                    'keys'  => ['cbpause', 'cbPause'],
                    'props' => [
                        'meta' => true,
                    ],
                ],

                'cbAnimStart'              => [
                    'value' => "function(data) {\r\n\r\n}",
                    'keys'  => ['cbanimstart', 'cbAnimStart'],
                    'props' => [
                        'meta' => true,
                    ],
                ],

                'cbAnimStop'               => [
                    'value' => "function(data) {\r\n\r\n}",
                    'keys'  => ['cbanimstop', 'cbAnimStop'],
                    'props' => [
                        'meta' => true,
                    ],
                ],

                'cbPrev'                   => [
                    'value' => "function(data) {\r\n\r\n}",
                    'keys'  => ['cbprev', 'cbPrev'],
                    'props' => [
                        'meta' => true,
                    ],
                ],

                'cbNext'                   => [
                    'value' => "function(data) {\r\n\r\n}",
                    'keys'  => ['cbnext', 'cbNext'],
                    'props' => [
                        'meta' => true,
                    ],
                ],
            ],

            'slides'  => [

                // The background image of slides
                // Defaults to: void
                'image'                   => [
                    'value'   => '',
                    'name'    => 'Set a slide image',
                    'keys'    => 'background',
                    'tooltip' => 'The slide image/background. Click on the image to open the Image Manager to choose or upload an image.',
                    'props'   => ['meta' => true],
                ],

                'imageId'                 => [
                    'value' => '',
                    'keys'  => 'backgroundId',
                    'props' => ['meta' => true],
                ],

                'imageSize'               => [
                    'value'   => 'inherit',
                    'name'    => 'Size',
                    'keys'    => 'bgsize',
                    'tooltip' => 'The size of the slide background image. Leave this option on inherit if you want to set it globally from Slider Settings.',
                    'options' => [
                        'inherit'   => 'Inherit',
                        'auto'      => 'Auto',
                        'cover'     => 'Cover',
                        'contain'   => 'Contain',
                        '100% 100%' => 'Stretch',
                    ],
                ],

                'imagePosition'           => [
                    'value'   => 'inherit',
                    'name'    => 'Position',
                    'keys'    => 'bgposition',
                    'tooltip' => 'The position of the slide background image. Leave this option on inherit if you want to set it globally from Slider Settings.',
                    'options' => [
                        'inherit'   => 'Inherit',
                        '0% 0%'     => 'left top',
                        '0% 50%'    => 'left center',
                        '0% 100%'   => 'left bottom',
                        '50% 0%'    => 'center top',
                        '50% 50%'   => 'center center',
                        '50% 100%'  => 'center bottom',
                        '100% 0%'   => 'right top',
                        '100% 50%'  => 'right center',
                        '100% 100%' => 'right bottom',
                    ],
                ],

                'imageColor'              => [
                    'value'   => '',
                    'name'    => 'Color',
                    'keys'    => 'bgcolor',
                    'tooltip' => 'The slide background color. You can use color names, hexadecimal, RGB or RGBA values.',
                ],

                'thumbnail'               => [
                    'value'   => '',
                    'name'    => 'Set a slide thumbnail',
                    'keys'    => 'thumbnail',
                    'tooltip' => 'The thumbnail image of this slide. Click on the image to open the Image Manager to choose or upload an image. If you leave this field empty, the slide image will be used.',
                    'props'   => ['meta' => true],
                ],

                'thumbnailId'             => [
                    'value' => '',
                    'keys'  => 'thumbnailId',
                    'props' => ['meta' => true],
                ],

                // Default slide delay in millisecs.
                // Defaults to: 4000 (ms) => 4secs
                'delay'                   => [
                    'value'   => '',
                    'name'    => 'Duration',
                    'keys'    => ['slidedelay', 'duration'],
                    'tooltip' => "Here you can set the time interval between slide changes, this slide will stay visible for the time specified here. This value is in millisecs, so the value 1000 means 1 second. Please don't use 0 or very low values.", "LayerSlider",
                    'attrs'   => [
                        'type'        => 'number',
                        'min'         => 0,
                        'step'        => 500,
                        'placeholder' => 'auto',
                    ],
                ],

                '2dTransitions'           => [
                    'value' => '',
                    'keys'  => ['2d_transitions', 'transition2d'],
                ],

                '3dTransitions'           => [
                    'value' => '',
                    'keys'  => ['3d_transitions', 'transition3d'],
                ],

                'custom2dTransitions'     => [
                    'value' => '',
                    'keys'  => ['custom_2d_transitions', 'customtransition2d'],
                ],

                'custom3dTransitions'     => [
                    'value' => '',
                    'keys'  => ['custom_3d_transitions', 'customtransition3d'],
                ],

                'transitionOrigami'       => [
                    'value'   => false,
                    'keys'    => 'transitionorigami',
                    'premium' => true,
                ],

                'transitionDuration'      => [
                    'value'   => '',
                    'name'    => 'Duration',
                    'keys'    => 'transitionduration',
                    'tooltip' => "We've made our pre-defined slide transitions with special care to fit in most use cases. However, if you would like to increase or decrease the speed of these transitions, you can override their timing here by providing your own transition length in milliseconds. (1 second = 1000 milliseconds)", "LayerSlider",
                    'attrs'   => [
                        'type'        => 'number',
                        'min'         => 0,
                        'step'        => 500,
                        'placeholder' => 'custom duration',
                    ],

                ],

                'timeshift'               => [
                    'value'   => 0,
                    'name'    => 'Time Shift',
                    'keys'    => 'timeshift',
                    'tooltip' => "You can shift the starting point of the slide animation timeline, so layers can animate in an earlier time after a slide change. This value is in milliseconds. A second is 1000 milliseconds. You can only use a negative value.",
                    'attrs'   => [
                        'step' => 50,
                    ],
                ],

                'linkUrl'                 => [
                    'value'   => '',
                    'name'    => 'Enter URL',
                    'keys'    => ['layer_link', 'linkUrl'],
                    'tooltip' => 'If you want to link the whole slide, type the URL here. You can choose one of the pre-defined options from the dropdown list when you click into this field. You can also type a hash mark followed by a number to link this layer to another slide. Example: #3 - this will switch to the third slide.',
                    'attrs'   => [
                        'data-options' => '[{
                    "name": "Switch to the next slide",
                    "value": "#next"
                }, {
                    "name": "Switch to the previous slide",
                    "value": "#prev"
                }, {
                    "name": "Stop the slideshow",
                    "value": "#stop"
                }, {
                    "name": "Resume the slideshow",
                    "value": "#start"
                }, {
                    "name": "Replay the slide from the start",
                    "value": "#replay"
                }, {
                    "name": "Reverse the slide, then pause it",
                    "value": "#reverse"
                }, {
                    "name": "Reverse the slide, then replay it",
                    "value": "#reverse-replay"
                }]',
                    ],
                    'props'   => [
                        'meta' => true,
                    ],

                ],

                'linkId'                  => [
                    'value' => '',
                    'keys'  => 'linkId',
                    'props' => ['meta' => true],
                ],

                'linkTarget'              => [
                    'value'   => '_self',
                    'name'    => 'Link Target',
                    'keys'    => ['layer_link_target', 'linkTarget'],
                    'options' => [
                        '_self'     => 'Open on the same page',
                        '_blank'    => 'Open on new page',
                        '_parent'   => 'Open in parent frame',
                        '_top'      => 'Open in main frame',
                        'ls-scroll' => 'Scroll to element (Enter selector)',
                    ],
                    'props'   => [
                        'meta' => true,
                    ],

                ],

                'linkType'                => [
                    'value'   => 'over',
                    'keys'    => ['layer_link_type', 'linkType'],
                    'tooltip' => 'Choose whether the slide link should be on top or underneath your layers. The later option makes the link clickable only at empty spaces where the slide background is visible, and enables you to link both slides and layers independently from each other.',
                    'options' => [
                        'over'  => 'On top of layers',
                        'under' => 'Underneath layers',
                    ],
                    'props'   => [
                        'meta' => true,
                    ],
                ],

                'ID'                      => [
                    'value'   => '',
                    'name'    => '#ID',
                    'keys'    => 'id',
                    'tooltip' => 'You can apply an ID attribute on the HTML element of this slide to work with it in your custom CSS or Javascript code.',
                    'props'   => [
                        'meta' => true,
                    ],
                ],

                'deeplink'                => [
                    'value'   => '',
                    'name'    => 'Deeplink',
                    'keys'    => 'deeplink',
                    'tooltip' => 'You can specify a slide alias name which you can use in your URLs with a hash mark, so LayerSlider will start with the correspondig slide.',
                ],

                'globalHover'             => [
                    'value'   => false,
                    'name'    => 'Global Hover',
                    'keys'    => 'globalhover',
                    'tooltip' => 'By turning this option on, all layers will trigger their Hover Transitions at the same time when you hover over the slider with your mouse cursor. It’s useful to create spectacular effects that involve multiple layer transitions and activate on hovering over the slider instead of individual layers.',
                    'premium' => true,
                ],

                'postContent'             => [
                    'value' => null,
                    'keys'  => 'post_content',
                    'props' => [
                        'meta' => true,
                    ],
                ],

                'postOffset'              => [
                    'value' => '',
                    'keys'  => 'post_offset',
                    'props' => [
                        'meta' => true,
                    ],
                ],

                'skipSlide'               => [
                    'value'   => false,
                    'name'    => 'Hidden',
                    'keys'    => 'skip',
                    'tooltip' => "If you don't want to use this slide in your front-page, but you want to keep it, you can hide it with this switch.",
                    'props'   => [
                        'meta' => true,
                    ],
                ],

                'overflow'                => [
                    'value'   => false,
                    'name'    => 'Overflow layers',
                    'keys'    => 'overflow',
                    'tooltip' => 'By default the slider clips the layers outside of its bounds. Enable this option to allow overflowing content.',
                ],

                'scheduleStart'           => [
                    'value' => '',
                    'name'  => 'Start on',
                    'keys'  => 'schedule_start',
                    'desc'  => "Scheduled slide will only be visible to your visitors between the time period you set here.<br>We're using international date and time format to avoid ambiguity.",
                    'attrs' => [
                        'placeholder' => 'No schedule',
                    ],
                    'props' => [
                        'meta' => true,
                    ],
                ],

                'scheduleEnd'             => [
                    'value' => '',
                    'name'  => 'Stop on',
                    'keys'  => 'schedule_end',
                    'desc'  => 'Clear the text field above and left it empty if you want to cancel the schedule.',
                    'attrs' => [
                        'placeholder' => 'No schedule',
                    ],
                    'props' => [
                        'meta' => true,
                    ],
                ],

                'title'                   => [
                    'value' => '',
                    'name'  => 'Title',
                    'keys'  => 'title',
                    'props' => ['meta' => true],
                ],

                'alt'                     => [
                    'value'   => '',
                    'name'    => 'Alt',
                    'keys'    => 'alt',
                    'tooltip' => 'Name or describe your slide image, so search engines and VoiceOver softwares can properly identify it.',
                    'props'   => ['meta' => true],
                ],

                // Ken Burns effect
                'kenBurnsZoom'            => [
                    'value'   => 'disabled',
                    'name'    => 'Zoom',
                    'keys'    => 'kenburnszoom',
                    'options' => [
                        'disabled' => 'Disabled',
                        'in'       => 'Zoom In',
                        'out'      => 'Zoom Out',
                    ],
                ],

                'kenBurnsRotate'          => [
                    'value'   => '',
                    'name'    => 'Rotate',
                    'keys'    => 'kenburnsrotate',
                    'tooltip' => 'The amount of rotation (if any) in degrees used in the Ken Burns effect. Negative values are allowed for counterclockwise rotation.',

                ],

                'kenBurnsScale'           => [
                    'value'   => 1.2,
                    'name'    => 'Scale',
                    'keys'    => 'kenburnsscale',
                    'tooltip' => 'Increase or decrease the size of the slide background image in the Ken Burns effect. The default value is 1, the value 2 will double the image, while 0.5 results half the size. Negative values will flip the image.',
                    'attrs'   => [
                        'type' => 'number',
                        'step' => 0.1,
                    ],
                    'props'   => [
                        'output' => true,
                    ],
                ],

                // Parallax
                'parallaxType'            => [
                    'value'   => '2d',
                    'name'    => 'Type',
                    'keys'    => 'parallaxtype',
                    'tooltip' => 'The default value for parallax layers on this slide, which they will inherit, unless you set it otherwise on the affected layers.',
                    'options' => [
                        '2d' => '2D',
                        '3d' => '3D',
                    ],
                ],

                'parallaxEvent'           => [
                    'value'   => 'cursor',
                    'name'    => 'Event',
                    'keys'    => 'parallaxevent',
                    'tooltip' => 'You can trigger the parallax effect by either scrolling the page, or by moving your mouse cursor / tilting your mobile device. This is the default value on this slide, which parallax layers will inherit, unless you set it otherwise directly on them.',
                    'options' => [
                        'cursor' => 'Cursor or Tilt',
                        'scroll' => 'Scroll',
                    ],
                ],

                'parallaxAxis'            => [
                    'value'   => 'both',
                    'name'    => 'Axes',
                    'keys'    => 'parallaxaxis',
                    'tooltip' => 'Choose on which axes parallax layers should move. This is the default value on this slide, which parallax layers will inherit, unless you set it otherwise directly on them.',
                    'options' => [
                        'none' => 'None',
                        'both' => 'Both axes',
                        'x'    => 'Horizontal only',
                        'y'    => 'Vertical only',
                    ],
                ],

                'parallaxTransformOrigin' => [
                    'value'   => '50% 50% 0',
                    'name'    => 'Transform Origin',
                    'keys'    => 'parallaxtransformorigin',
                    'tooltip' => 'Sets a point on canvas from which transformations are calculated. For example, a layer may rotate around its center axis or a completely custom point, such as one of its corners. The three values represent the X, Y and Z axes in 3D space. Apart from the pixel and percentage values, you can also use the following constants: top, right, bottom, left, center.',
                ],

                'parallaxDurationMove'    => [
                    'value'   => 1500,
                    'name'    => 'Move duration',
                    'keys'    => 'parallaxdurationmove',
                    'tooltip' => 'Controls the speed of animating layers when you move your mouse cursor or tilt your mobile device. This is the default value on this slide, which parallax layers will inherit, unless you set it otherwise directly on them.',
                    'attrs'   => [
                        'type' => 'number',
                        'step' => 100,
                        'min'  => 0,
                    ],
                ],

                'parallaxDurationLeave'   => [
                    'value'   => 1200,
                    'name'    => 'Leave duration',
                    'keys'    => 'parallaxdurationleave',
                    'tooltip' => 'Controls how quickly your layers revert to their original position when you move your mouse cursor outside of a parallax slider. This value is in milliseconds. 1 second = 1000 milliseconds. This is the default value on this slide, which parallax layers will inherit, unless you set it otherwise directly on them.',
                    'attrs'   => [
                        'type' => 'number',
                        'step' => 100,
                        'min'  => 0,
                    ],
                ],

                'parallaxDistance'        => [
                    'value'   => 10,
                    'name'    => 'Distance',
                    'keys'    => 'parallaxdistance',
                    'tooltip' => 'Increase or decrease the amount of layer movement when moving your mouse cursor or tilting on a mobile device. This is the default value on this slide, which parallax layers will inherit, unless you set it otherwise directly on them.',
                    'attrs'   => [
                        'type' => 'number',
                        'step' => 1,
                    ],

                ],

                'parallaxRotate'          => [
                    'value'   => 10,
                    'name'    => 'Rotation',
                    'keys'    => 'parallaxrotate',
                    'tooltip' => 'Increase or decrease the amount of layer rotation in the 3D space when moving your mouse cursor or tilting on a mobile device. This is the default value on this slide, which parallax layers will inherit, unless you set it otherwise directly on them.',
                    'attrs'   => [
                        'type' => 'number',
                        'step' => 1,
                    ],
                ],

                'parallaxPerspective'     => [
                    'value'   => 500,
                    'name'    => 'Perspective',
                    'keys'    => 'parallaxtransformperspective',
                    'tooltip' => 'Changes the perspective of layers in the 3D space. This is the default value on this slide, which parallax layers will inherit, unless you set it otherwise directly on them.',
                    'attrs'   => [
                        'type' => 'number',
                        'step' => 100,
                    ],
                ],
            ],

            'layers'  => [

                // ======================= //
                // |  Content  | //
                // ======================= //

                'uuid'                         => [
                    'value' => '',
                    'keys'  => 'uuid',
                    'props' => [
                        'meta' => true,
                    ],
                ],

                'type'                         => [
                    'value' => '',
                    'keys'  => 'type',
                    'props' => [
                        'meta' => true,
                    ],
                ],

                'hide_on_desktop'              => [
                    'value' => false,
                    'keys'  => 'hide_on_desktop',
                    'props' => [
                        'meta' => true,
                    ],
                ],

                'hide_on_tablet'               => [
                    'value' => false,
                    'keys'  => 'hide_on_tablet',
                    'props' => [
                        'meta' => true,
                    ],
                ],

                'hide_on_phone'                => [
                    'value' => false,
                    'keys'  => 'hide_on_phone',
                    'props' => [
                        'meta' => true,
                    ],
                ],

                'media'                        => [
                    'value' => '',
                    'keys'  => 'media',
                    'props' => [
                        'meta' => true,
                    ],
                ],

                'image'                        => [
                    'value' => '',
                    'keys'  => 'image',
                    'props' => [
                        'meta' => true,
                    ],
                ],

                'imageId'                      => [
                    'value' => '',
                    'keys'  => 'imageId',
                    'props' => ['meta' => true],
                ],

                'html'                         => [
                    'value' => '',
                    'keys'  => 'html',
                    'props' => [
                        'meta' => true,
                    ],
                ],

                'mediaAutoPlay'                => [
                    'value'   => 'inherit',
                    'name'    => 'Autoplay',
                    'keys'    => 'autoplay',
                    'options' => [
                        'inherit'  => 'Inherit',
                        'enabled'  => 'Enabled',
                        'disabled' => 'Disabled',
                    ],
                ],

                'mediaInfo'                    => [
                    'value'   => true,
                    'name'    => 'Show Info',
                    'keys'    => 'showinfo',
                    'options' => [
                        'auto'     => 'Auto',
                        'enabled'  => 'Enabled',
                        'disabled' => 'Disabled',
                    ],
                ],

                'mediaControls'                => [
                    'value'   => true,
                    'name'    => 'Controls',
                    'keys'    => 'controls',
                    'options' => [
                        'auto'     => 'Auto',
                        'enabled'  => 'Enabled',
                        'disabled' => 'Disabled',
                    ],
                ],

                'mediaPoster'                  => [
                    'value' => '',
                    'keys'  => 'poster',
                ],

                'mediaFillMode'                => [
                    'value'   => 'cover',
                    'name'    => 'Fill mode',
                    'keys'    => 'fillmode',
                    'options' => [
                        'contain' => 'Contain',
                        'cover'   => 'Cover',
                    ],
                ],

                'mediaVolume'                  => [
                    'value' => '',
                    'name'  => 'Volume',
                    'keys'  => 'volume',
                    'attrs' => [
                        'type'        => 'number',
                        'min'         => 0,
                        'max'         => 100,
                        'placeholder' => 'auto',
                    ],
                ],

                'mediaBackgroundVideo'         => [
                    'value'   => false,
                    'name'    => 'Use this video as slide background',
                    'keys'    => 'backgroundvideo',
                    'tooltip' => 'Forces this layer to act like the slide background by covering the whole slider and ignoring some transitions. Please make sure to provide your own poster image with the option above, so the slider can display it immediately on page load.',
                ],

                'mediaOverlay'                 => [
                    'value'   => 'disabled',
                    'name'    => 'Choose an overlay image:',
                    'keys'    => 'overlay',
                    'tooltip' => 'Cover your videos with an overlay image to have dotted or striped effects on them.',
                ],

                'postTextLength'               => [
                    'value' => '',
                    'keys'  => 'post_text_length',
                    'props' => [
                        'meta' => true,
                    ],
                ],

                // ======================= //
                // |  Animation options  | //
                // ======================= //
                'transition'                   => ['value' => '', 'keys' => 'transition', 'props' => ['meta' => true]],

                'transitionIn'                 => [
                    'value' => true,
                    'keys'  => 'transitionin',
                ],

                'transitionInOffsetX'          => [
                    'value'   => '0',
                    'name'    => 'OffsetX',
                    'keys'    => 'offsetxin',
                    'tooltip' => "Shifts the layer starting position from its original on the horizontal axis with the given number of pixels. Use negative values for the opposite direction. Percentage values are relative to the width of this layer. The values 'left' or 'right' position the layer out the staging area, so it enters the scene from either side when animating to its destination location.", "LayerSlider",
                    'attrs'   => ['type' => 'text', 'data-options' => '[{
                "name": "Enter the stage from left",
                "value": "left"
            }, {
                "name": "Enter the stage from right",
                "value": "right"
            }, {
                "name": "100% layer width",
                "value": "100lw"
            }, {
                "name": "-100% layer width",
                "value": "-100lw"
            }, {
                "name": "50% slider width",
                "value": "50sw"
            }, {
                "name": "-50% slider width",
                "value": "-50sw"
            }, {
                "name": "Random",
                "value": "random(-100,100)"
            }]'],
                ],

                'transitionInOffsetY'          => [
                    'value'   => '0',
                    'name'    => 'OffsetY',
                    'keys'    => 'offsetyin',
                    'tooltip' => "Shifts the layer starting position from its original on the vertical axis with the given number of pixels. Use negative values for the opposite direction. Percentage values are relative to the height of this layer. The values 'top' or 'bottom' position the layer out the staging area, so it enters the scene from either vertical side when animating to its destination location.", "LayerSlider",
                    'attrs'   => ['type' => 'text', 'data-options' => '[{
                "name": "Enter the stage from top",
                "value": "top"
            }, {
                "name": "Enter the stage from bottom",
                "value": "bottom"
            }, {
                "name": "100% layer height",
                "value": "100lh"
            }, {
                "name": "-100% layer height",
                "value": "-100lh"
            }, {
                "name": "50% slider height",
                "value": "50sh"
            }, {
                "name": "-50% slider height",
                "value": "-50sh"
            }, {
                "name": "Random",
                "value": "random(-100,100)"
            }]'],
                ],

                // Duration of the transition in millisecs when a layer animates in.
                // Original: durationin
                // Defaults to: 1000 (ms) => 1sec
                'transitionInDuration'         => [
                    'value'   => 1000,
                    'name'    => 'Duration',
                    'keys'    => 'durationin',
                    'tooltip' => 'The length of the transition in milliseconds when the layer enters the scene. A second equals to 1000 milliseconds.',
                    'attrs'   => ['min' => 0, 'step' => 50],
                ],

                // Delay before the transition in millisecs when a layer animates in.
                // Original: delayin
                // Defaults to: 0 (ms)
                'transitionInDelay'            => [
                    'value'   => 0,
                    'name'    => 'Start at',
                    'keys'    => 'delayin',
                    'tooltip' => 'Delays the transition with the given amount of milliseconds before the layer enters the scene. A second equals to 1000 milliseconds.',
                    'attrs'   => ['min' => 0, 'step' => 50],
                ],

                // Easing of the transition when a layer animates in.
                // Original: easingin
                // Defaults to: 'easeInOutQuint'
                'transitionInEasing'           => [
                    'value'   => 'easeInOutQuint',
                    'name'    => 'Easing',
                    'keys'    => 'easingin',
                    'tooltip' => "The timing function of the animation. With this function you can manipulate the movement of the animated object. Please click on the link next to this select field to open easings.net for more information and real-time examples.", "LayerSlider",
                ],

                'transitionInFade'             => [
                    'value'   => true,
                    'name'    => 'Fade',
                    'keys'    => 'fadein',
                    'tooltip' => 'Fade the layer during the transition.',
                ],

                // Initial rotation degrees when a layer animates in.
                // Original: rotatein
                // Defaults to: 0 (deg)
                'transitionInRotate'           => [
                    'value'   => 0,
                    'name'    => 'Rotate',
                    'keys'    => 'rotatein',
                    'tooltip' => 'Rotates the layer by the given number of degrees. Negative values are allowed for counterclockwise rotation.',
                    'attrs'   => ['type' => 'text', 'data-options' => '[{
                "name": "Random",
                "value": "random(-45,45)"
            }]'],
                ],

                'transitionInRotateX'          => [
                    'value'   => 0,
                    'name'    => 'RotateX',
                    'keys'    => 'rotatexin',
                    'tooltip' => 'Rotates the layer along the X (horizontal) axis by the given number of degrees. Negative values are allowed for reverse direction.',
                    'attrs'   => ['type' => 'text', 'data-options' => '[{
                "name": "Random",
                "value": "random(-45,45)"
            }]'],
                ],

                'transitionInRotateY'          => [
                    'value'   => 0,
                    'name'    => 'RotateY',
                    'keys'    => 'rotateyin',
                    'tooltip' => 'Rotates the layer along the Y (vertical) axis by the given number of degrees. Negative values are allowed for reverse direction.',
                    'attrs'   => ['type' => 'text', 'data-options' => '[{
                "name": "Random",
                "value": "random(-45,45)"
            }]'],
                ],

                'transitionInSkewX'            => [
                    'value'   => 0,
                    'name'    => 'SkewX',
                    'keys'    => 'skewxin',
                    'tooltip' => 'Skews the layer along the X (horizontal) by the given number of degrees. Negative values are allowed for reverse direction.',
                    'attrs'   => ['type' => 'text', 'data-options' => '[{
                "name": "Random",
                "value": "random(-45,45)"
            }]'],
                ],

                'transitionInSkewY'            => [
                    'value'   => 0,
                    'name'    => 'SkewY',
                    'keys'    => 'skewyin',
                    'tooltip' => 'Skews the layer along the Y (vertical) by the given number of degrees. Negative values are allowed for reverse direction.',
                    'attrs'   => ['type' => 'text', 'data-options' => '[{
                "name": "Random",
                "value": "random(-45,45)"
            }]'],
                ],

                'transitionInScaleX'           => [
                    'value'   => 1,
                    'name'    => 'ScaleX',
                    'keys'    => 'scalexin',
                    'tooltip' => "Scales the layer along the X (horizontal) axis by the specified vector. Use the value 1 for the original size. The value 2 will double, while 0.5 shrinks the layer compared to its original size.", "LayerSlider",
                    'attrs'   => ['type' => 'text', 'data-options' => '[{
                "name": "Random",
                "value": "random(2,4)"
            }]'],
                ],

                'transitionInScaleY'           => [
                    'value'   => 1,
                    'name'    => 'ScaleY',
                    'keys'    => 'scaleyin',
                    'tooltip' => "Scales the layer along the Y (vertical) axis by the specified vector. Use the value 1 for the original size. The value 2 will double, while 0.5 shrinks the layer compared to its original size.", "LayerSlider",
                    'attrs'   => ['type' => 'text', 'data-options' => '[{
                "name": "Random",
                "value": "random(2,4)"
            }]'],
                ],

                'transitionInTransformOrigin'  => [
                    'value'   => '50% 50% 0',
                    'name'    => 'Transform Origin',
                    'keys'    => 'transformoriginin',
                    'tooltip' => 'Sets a point on canvas from which transformations are calculated. For example, a layer may rotate around its center axis or a completely custom point, such as one of its corners. The three values represent the X, Y and Z axes in 3D space. Apart from the pixel and percentage values, you can also use the following constants: top, right, bottom, left, center, slidercenter, slidermiddle, slidertop, sliderright, sliderbottom, sliderleft.',
                ],

                'transitionInClip'             => [
                    'value'   => '',
                    'name'    => 'Mask',
                    'keys'    => 'clipin',
                    'tooltip' => "Clips (cuts off) the sides of the layer by the given amount specified in pixels or percentages. The 4 value in order: top, right, bottom and the left side of the layer.", "LayerSlider",
                    'attrs'   => ['data-options' => '[{
                "name": "From top",
                "value": "0 0 100% 0"
            }, {
                "name": "From right",
                "value": "0 0 0 100%"
            }, {
                "name": "From bottom",
                "value": "100% 0 0 0"
            }, {
                "name": "From left",
                "value": "0 100% 0 0"
            }]'],
                ],

                'transitionInBGColor'          => [
                    'value'   => '',
                    'name'    => 'Background',
                    'keys'    => 'bgcolorin',
                    'tooltip' => "The background color of your layer. You can use color names, hexadecimal, RGB or RGBA values as well as the 'transparent' keyword. Example: #FFF",
                ],

                'transitionInColor'            => [
                    'value'   => '',
                    'name'    => 'Color',
                    'keys'    => 'colorin',
                    'tooltip' => "The color of your text. You can use color names, hexadecimal, RGB or RGBA values. Example: #333",
                ],

                'transitionInRadius'           => [
                    'value'   => '',
                    'name'    => 'Rounded Corners',
                    'keys'    => 'radiusin',
                    'tooltip' => 'If you want rounded corners, you can set its radius here in pixels. Example: 5px',
                ],

                'transitionInWidth'            => [
                    'value'   => '',
                    'name'    => 'Width',
                    'keys'    => 'widthin',
                    'tooltip' => 'The initial width of this layer from which it will be animated to its proper size during the transition.',
                ],

                'transitionInHeight'           => [
                    'value'   => '',
                    'name'    => 'Height',
                    'keys'    => 'heightin',
                    'tooltip' => 'The initial height of this layer from which it will be animated to its proper size during the transition.',
                ],

                'transitionInFilter'           => [
                    'value'   => '',
                    'name'    => 'Filter',
                    'keys'    => 'filterin',
                    'tooltip' => 'Filters provide effects like blurring or color shifting your layers. Click into the text field to see a selection of filters you can use. Although clicking on the pre-defined options will reset the text field, you can apply multiple filters simply by providing a space separated list of all the filters you would like to use. Click on the "Filter" link for more information.',
                    'premium' => true,
                    'attrs'   => [
                        'data-options' => '[{
                    "name": "Blur",
                    "value": "blur(5px)"
                }, {
                    "name": "Brightness",
                    "value": "brightness(40%)"
                }, {
                    "name": "Contrast",
                    "value": "contrast(200%)"
                }, {
                    "name": "Grayscale",
                    "value": "grayscale(50%)"
                }, {
                    "name": "Hue-rotate",
                    "value": "hue-rotate(90deg)"
                }, {
                    "name": "Invert",
                    "value": "invert(75%)"
                }, {
                    "name": "Saturate",
                    "value": "saturate(30%)"
                }, {
                    "name": "Sepia",
                    "value": "sepia(60%)"
                }]',
                    ],
                ],

                'transitionInPerspective'      => [
                    'value'   => '500',
                    'name'    => 'Perspective',
                    'keys'    => 'transformperspectivein',
                    'tooltip' => 'Changes the perspective of this layer in the 3D space.',
                ],

                // ======

                'transitionOut'                => [
                    'value' => true,
                    'keys'  => 'transitionout',
                ],

                'transitionOutOffsetX'         => [
                    'value'   => 0,
                    'name'    => 'OffsetX',
                    'keys'    => 'offsetxout',
                    'tooltip' => "Shifts the layer from its original position on the horizontal axis with the given number of pixels. Use negative values for the opposite direction. Percentage values are relative to the width of this layer. The values 'left' or 'right' animate the layer out the staging area, so it can leave the scene on either side.", "LayerSlider",
                    'attrs'   => ['type' => 'text', 'data-options' => '[{
                "name": "Leave the stage on left",
                "value": "left"
            }, {
                "name": "Leave the stage on right",
                "value": "right"
            }, {
                "name": "100% layer width",
                "value": "100lw"
            }, {
                "name": "-100% layer width",
                "value": "-100lw"
            }, {
                "name": "50% slider width",
                "value": "50sw"
            }, {
                "name": "-50% slider width",
                "value": "-50sw"
            }, {
                "name": "Random",
                "value": "random(-100,100)"
            }]'],
                ],

                'transitionOutOffsetY'         => [
                    'value'   => 0,
                    'name'    => 'OffsetY',
                    'keys'    => 'offsetyout',
                    'tooltip' => "Shifts the layer from its original position on the vertical axis with the given number of pixels. Use negative values for the opposite direction. Percentage values are relative to the height of this layer. The values 'top' or 'bottom' animate the layer out the staging area, so it can leave the scene on either vertical side.", "LayerSlider",
                    'attrs'   => ['type' => 'text', 'data-options' => '[{
                "name": "Leave the stage on top",
                "value": "top"
            }, {
                "name": "Leave the stage on bottom",
                "value": "bottom"
            }, {
                "name": "100% layer height",
                "value": "100lh"
            }, {
                "name": "-100% layer height",
                "value": "-100lh"
            }, {
                "name": "50% slider height",
                "value": "50sh"
            }, {
                "name": "-50% slider height",
                "value": "-50sh"
            }, {
                "name": "Random",
                "value": "random(-100,100)"
            }]'],
                ],

                // Duration of the transition in millisecs when a layer animates out.
                // Original: durationout
                // Defaults to: 1000 (ms) => 1sec
                'transitionOutDuration'        => [
                    'value'   => 1000,
                    'name'    => 'Duration',
                    'keys'    => 'durationout',
                    'tooltip' => 'The length of the transition in milliseconds when the layer leaves the slide. A second equals to 1000 milliseconds.',
                    'attrs'   => ['min' => 0, 'step' => 50],
                ],

                'showUntil'                    => [
                    'value' => '0',
                    'keys'  => 'showuntil',
                ],

                'transitionOutStartAt'         => [
                    'value'   => 'slidechangeonly',
                    'name'    => 'Start at',
                    'keys'    => 'startatout',
                    'tooltip' => 'You can set the starting time of this transition. Use one of the pre-defined options to use relative timing, which can be shifted with custom operations.',
                    'attrs'   => ['type' => 'hidden'],
                ],

                'transitionOutStartAtTiming'   => [
                    'value'   => 'slidechangeonly',
                    'keys'    => 'startatouttiming',
                    'props'   => ['meta' => true],
                    'options' => [
                        'slidechangeonly'        => 'Slide change starts (ignoring modifier)',
                        'transitioninend'        => 'Opening Transition completes',
                        'textinstart'            => 'Opening Text Transition starts',
                        'textinend'              => 'Opening Text Transition completes',
                        'allinend'               => 'Opening and Opening Text Transition complete',
                        'loopstart'              => 'Loop starts',
                        'loopend'                => 'Loop completes',
                        'transitioninandloopend' => 'Opening and Loop Transitions complete',
                        'textinandloopend'       => 'Opening Text and Loop Transitions complete',
                        'allinandloopend'        => 'Opening, Opening Text and Loop Transitions complete',
                        'textoutstart'           => 'Ending Text Transition starts',
                        'textoutend'             => 'Ending Text Transition completes',
                        'textoutandloopend'      => 'Ending Text and Loop Transitions complete',
                    ],
                ],

                'transitionOutStartAtOperator' => [
                    'value'   => '+',
                    'keys'    => 'startatoutoperator',
                    'props'   => ['meta' => true],
                    'options' => ['+', '-', '/', '*'],
                ],

                'transitionOutStartAtValue'    => [
                    'value' => 0,
                    'keys'  => 'startatoutvalue',
                    'props' => ['meta' => true],
                ],

                // Easing of the transition when a layer animates out.
                // Original: easingout
                // Defaults to: 'easeInOutQuint'
                'transitionOutEasing'          => [
                    'value'   => 'easeInOutQuint',
                    'name'    => 'Easing',
                    'keys'    => 'easingout',
                    'tooltip' => "The timing function of the animation. With this function you can manipulate the movement of the animated object. Please click on the link next to this select field to open easings.net for more information and real-time examples.",
                ],

                'transitionOutFade'            => [
                    'value'   => true,
                    'name'    => 'Fade',
                    'keys'    => 'fadeout',
                    'tooltip' => 'Fade the layer during the transition.',
                ],

                // Initial rotation degrees when a layer animates out.
                // Original: rotateout
                // Defaults to: 0 (deg)
                'transitionOutRotate'          => [
                    'value'   => 0,
                    'name'    => 'Rotate',
                    'keys'    => 'rotateout',
                    'tooltip' => 'Rotates the layer by the given number of degrees. Negative values are allowed for counterclockwise rotation.',
                    'attrs'   => ['type' => 'text', 'data-options' => '[{
                "name": "Random",
                "value": "random(-45,45)"
            }]'],
                ],

                'transitionOutRotateX'         => [
                    'value'   => 0,
                    'name'    => 'RotateX',
                    'keys'    => 'rotatexout',
                    'tooltip' => 'Rotates the layer along the X (horizontal) axis by the given number of degrees. Negative values are allowed for reverse direction.',
                    'attrs'   => ['type' => 'text', 'data-options' => '[{
                "name": "Random",
                "value": "random(-45,45)"
            }]'],
                ],

                'transitionOutRotateY'         => [
                    'value'   => 0,
                    'name'    => 'RotateY',
                    'keys'    => 'rotateyout',
                    'tooltip' => 'Rotates the layer along the Y (vertical) axis by the given number of degrees. Negative values are allowed for reverse direction.',
                    'attrs'   => ['type' => 'text', 'data-options' => '[{
                "name": "Random",
                "value": "random(-45,45)"
            }]'],
                ],

                'transitionOutSkewX'           => [
                    'value'   => 0,
                    'name'    => 'SkewX',
                    'keys'    => 'skewxout',
                    'tooltip' => 'Skews the layer along the X (horizontal) axis by the given number of degrees. Negative values are allowed for reverse direction.',
                    'attrs'   => ['type' => 'text', 'data-options' => '[{
                "name": "Random",
                "value": "random(-45,45)"
            }]'],
                ],

                'transitionOutSkewY'           => [
                    'value'   => 0,
                    'name'    => 'SkewY',
                    'keys'    => 'skewyout',
                    'tooltip' => 'Skews the layer along the Y (vertical) axis by the given number of degrees. Negative values are allowed for reverse direction.',
                    'attrs'   => ['type' => 'text', 'data-options' => '[{
                "name": "Random",
                "value": "random(-45,45)"
            }]'],
                ],

                'transitionOutScaleX'          => [
                    'value'   => 1,
                    'name'    => 'ScaleX',
                    'keys'    => 'scalexout',
                    'tooltip' => "Scales the layer along the X (horizontal) axis by the specified vector. Use the value 1 for the original size. The value 2 will double, while 0.5 shrinks the layer compared to its original size.",
                    'attrs'   => ['type' => 'text', 'data-options' => '[{
                "name": "Random",
                "value": "random(2,4)"
            }]'],
                ],

                'transitionOutScaleY'          => [
                    'value'   => 1,
                    'name'    => 'ScaleY',
                    'keys'    => 'scaleyout',
                    'tooltip' => "Scales the layer along the Y (vertical) axis by the specified vector. Use the value 1 for the original size. The value 2 will double, while 0.5 shrinks the layer compared to its original size.",
                    'attrs'   => ['type' => 'text', 'data-options' => '[{
                "name": "Random",
                "value": "random(2,4)"
            }]'],
                ],

                'transitionOutTransformOrigin' => [
                    'value'   => '50% 50% 0',
                    'name'    => 'Transform Origin',
                    'keys'    => 'transformoriginout',
                    'tooltip' => 'Sets a point on canvas from which transformations are calculated. For example, a layer may rotate around its center axis or a completely custom point, such as one of its corners. The three values represent the X, Y and Z axes in 3D space. Apart from the pixel and percentage values, you can also use the following constants: top, right, bottom, left, center, slidercenter, slidermiddle, slidertop, sliderright, sliderbottom, sliderleft.',
                ],

                'transitionOutClip'            => [
                    'value'   => '',
                    'name'    => 'Mask',
                    'keys'    => 'clipout',
                    'tooltip' => "Clips (cuts off) the sides of the layer by the given amount specified in pixels or percentages. The 4 value in order: top, right, bottom and the left side of the layer.",
                    'attrs'   => ['data-options' => '[{
                "name": "From top",
                "value": "0 0 100% 0"
            }, {
                "name": "From right",
                "value": "0 0 0 100%"
            }, {
                "name": "From bottom",
                "value": "100% 0 0 0"
            }, {
                "name": "From left",
                "value": "0 100% 0 0"
            }]'],
                ],

                'transitionOutFilter'          => [
                    'value'   => '',
                    'name'    => 'Filter',
                    'keys'    => 'filterout',
                    'tooltip' => 'Filters provide effects like blurring or color shifting your layers. Click into the text field to see a selection of filters you can use. Although clicking on the pre-defined options will reset the text field, you can apply multiple filters simply by providing a space separated list of all the filters you would like to use. Click on the "Filter" link for more information.',
                    'premium' => true,
                    'attrs'   => [
                        'data-options' => '[{
                    "name": "Blur",
                    "value": "blur(5px)"
                }, {
                    "name": "Brightness",
                    "value": "brightness(40%)"
                }, {
                    "name": "Contrast",
                    "value": "contrast(200%)"
                }, {
                    "name": "Grayscale",
                    "value": "grayscale(50%)"
                }, {
                    "name": "Hue-rotate",
                    "value": "hue-rotate(90deg)"
                }, {
                    "name": "Invert",
                    "value": "invert(75%)"
                }, {
                    "name": "Saturate",
                    "value": "saturate(30%)"
                }, {
                    "name": "Sepia",
                    "value": "sepia(60%)"
                }]',
                    ],
                ],

                'transitionOutPerspective'     => [
                    'value'   => '500',
                    'name'    => 'Perspective',
                    'keys'    => 'transformperspectiveout',
                    'tooltip' => 'Changes the perspective of this layer in the 3D space.',
                ],

                // -----

                'skipLayer'                    => [
                    'value'   => false,
                    'name'    => 'Hidden',
                    'keys'    => 'skip',
                    'tooltip' => "If you don't want to use this layer, but you want to keep it, you can hide it with this switch.",
                    'props'   => [
                        'meta' => true,
                    ],
                ],

                'transitionOutBGColor'         => [
                    'value'   => '',
                    'name'    => 'Background',
                    'keys'    => 'bgcolorout',
                    'tooltip' => 'Animates the background toward the color you specify here when the layer leaves the slider canvas.',
                ],

                'transitionOutColor'           => [
                    'value'   => '',
                    'name'    => 'Color',
                    'keys'    => 'colorout',
                    'tooltip' => 'Animates the text color toward the color you specify here when the layer leaves the slider canvas.',
                ],

                'transitionOutRadius'          => [
                    'value'   => '',
                    'name'    => 'Rounded Corners',
                    'keys'    => 'radiusout',
                    'tooltip' => 'Animates rounded corners toward the value you specify here when the layer leaves the slider canvas.',
                ],

                'transitionOutWidth'           => [
                    'value'   => '',
                    'name'    => 'Width',
                    'keys'    => 'widthout',
                    'tooltip' => 'Animates the layer width toward the value you specify here when the layer leaves the slider canvas.',
                ],

                'transitionOutHeight'          => [
                    'value'   => '',
                    'name'    => 'Height',
                    'keys'    => 'heightout',
                    'tooltip' => 'Animates the layer height toward the value you specify here when the layer leaves the slider canvas.',
                ],

                // == Compatibility ==
                'transitionInType'             => [
                    'value' => 'auto',
                    'keys'  => 'slidedirection',
                ],
                'transitionOutType'            => [
                    'value' => 'auto',
                    'keys'  => 'slideoutdirection',
                ],

                'transitionOutDelay'           => [
                    'value' => 0,
                    'keys'  => 'delayout',
                ],

                'transitionInScale'            => [
                    'value' => '1.0',
                    'keys'  => 'scalein',
                ],

                'transitionOutScale'           => [
                    'value' => '1.0',
                    'keys'  => 'scaleout',
                ],

                // Text Animation IN
                // -----------------

                'textTransitionIn'             => [
                    'value' => false,
                    'keys'  => 'texttransitionin',
                ],

                'textTypeIn'                   => [
                    'value'   => 'chars_asc',
                    'name'    => 'Text Animation',
                    'keys'    => 'texttypein',
                    'tooltip' => 'Select how your text should be split and animated.',
                    'options' => [
                        'chars_asc'    => 'by chars ascending',
                        'chars_desc'   => 'by chars descending',
                        'chars_rand'   => 'by chars random',
                        'chars_center' => 'by chars center to edge',
                        'chars_edge'   => 'by chars edge to center',
                        'words_asc'    => 'by words ascending',
                        'words_desc'   => 'by words descending',
                        'words_rand'   => 'by words random',
                        'words_center' => 'by words center to edge',
                        'words_edge'   => 'by words edge to center',
                        'lines_asc'    => 'by lines ascending',
                        'lines_desc'   => 'by lines descending',
                        'lines_rand'   => 'by lines random',
                        'lines_center' => 'by lines center to edge',
                        'lines_edge'   => 'by lines edge to center',
                    ],
                    'props'   => [
                        'output' => true,
                    ],
                ],

                'textShiftIn'                  => [
                    'value'   => 50,
                    'name'    => 'Shift In',
                    'tooltip' => 'Delays the transition of each text nodes relative to each other. A second equals to 1000 milliseconds.',
                    'keys'    => 'textshiftin',
                    'attrs'   => ['type' => 'number'],
                ],

                'textOffsetXIn'                => [
                    'value'   => 0,
                    'name'    => 'OffsetX',
                    'tooltip' => "Shifts the starting position of text nodes from their original on the horizontal axis with the given number of pixels. Use negative values for the opposite direction. Percentage values are relative to the width of this layer. The values 'left' or 'right' position text nodes out the staging area, so they enter the scene from either side when animating to their destination location. By listing multiple values separated with a | character, the slider will use different transition variations on each text node by cycling between the provided values.",
                    'keys'    => 'textoffsetxin',
                    'attrs'   => ['type' => 'text', 'data-options' => '[{
                "name": "Enter the stage from left",
                "value": "left"
            }, {
                "name": "Enter the stage from right",
                "value": "right"
            }, {
                "name": "100% layer width",
                "value": "100lw"
            }, {
                "name": "-100% layer width",
                "value": "-100lw"
            }, {
                "name": "50% slider width",
                "value": "50sw"
            }, {
                "name": "-50% slider width",
                "value": "-50sw"
            }, {
                "name": "Cycle between values",
                "value": "50|-50"
            }, {
                "name": "Random",
                "value": "random(-100,100)"
            }]'],
                ],

                'textOffsetYIn'                => [
                    'value'   => 0,
                    'name'    => 'OffsetY',
                    'tooltip' => "Shifts the starting position of text nodes from their original on the vertical axis with the given number of pixels. Use negative values for the opposite direction. Percentage values are relative to the width of this layer. The values 'top' or 'bottom' position text nodes out the staging area, so they enter the scene from either vertical side when animating to their destination location. By listing multiple values separated with a | character, the slider will use different transition variations on each text node by cycling between the provided values.",
                    'keys'    => 'textoffsetyin',
                    'attrs'   => ['type' => 'text', 'data-options' => '[{
                "name": "Enter the stage from top",
                "value": "top"
            }, {
                "name": "Enter the stage from bottom",
                "value": "bottom"
            }, {
                "name": "100% layer height",
                "value": "100lh"
            }, {
                "name": "-100% layer height",
                "value": "-100lh"
            }, {
                "name": "50% slider height",
                "value": "50sh"
            }, {
                "name": "-50% slider height",
                "value": "-50sh"
            }, {
                "name": "Cycle between values",
                "value": "50|-50"
            }, {
                "name": "Random",
                "value": "random(-100,100)"
            }]'],
                ],

                'textDurationIn'               => [
                    'value'   => 1000,
                    'name'    => 'Duration',
                    'tooltip' => 'The transition length in milliseconds of the individual text fragments. A second equals to 1000 milliseconds.',
                    'keys'    => 'textdurationin',
                    'attrs'   => ['min' => 0, 'step' => 50],
                ],

                'textEasingIn'                 => [
                    'value'   => 'easeInOutQuint',
                    'name'    => 'Easing',
                    'tooltip' => "The timing function of the animation. With this function you can manipulate the movement of animated text fragments. Please click on the link next to this select field to open easings.net for more information and real-time examples.",
                    'keys'    => 'texteasingin',
                ],

                'textFadeIn'                   => [
                    'value'   => true,
                    'name'    => 'Fade',
                    'tooltip' => 'Fade the text fragments during their transition.',
                    'keys'    => 'textfadein',
                ],

                'textStartAtIn'                => [
                    'value'   => 'transitioninend',
                    'name'    => 'StartAt',
                    'tooltip' => 'You can set the starting time of this transition. Use one of the pre-defined options to use relative timing, which can be shifted with custom operations.',
                    'keys'    => 'textstartatin',
                    'attrs'   => ['type' => 'hidden'],
                ],

                'textStartAtInTiming'          => [
                    'value'   => 'transitioninend',
                    'keys'    => 'textstartatintiming',
                    'props'   => ['meta' => true],
                    'options' => [
                        'transitioninstart'      => 'Opening Transition starts',
                        'transitioninend'        => 'Opening Transition completes',
                        'loopstart'              => 'Loop starts',
                        'loopend'                => 'Loop completes',
                        'transitioninandloopend' => 'Opening and Loop Transitions complete',
                    ],
                ],

                'textStartAtInOperator'        => [
                    'value'   => '+',
                    'keys'    => 'textstartatinoperator',
                    'props'   => ['meta' => true],
                    'options' => ['+', '-', '/', '*'],
                ],

                'textStartAtInValue'           => [
                    'value' => 0,
                    'keys'  => 'textstartatinvalue',
                    'props' => ['meta' => true],
                ],

                'textRotateIn'                 => [
                    'value'   => 0,
                    'name'    => 'Rotate',
                    'tooltip' => 'Rotates text fragments clockwise by the given number of degrees. Negative values are allowed for counterclockwise rotation. By listing multiple values separated with a | character, the slider will use different transition variations on each text node by cycling between the provided values.',
                    'keys'    => 'textrotatein',
                    'attrs'   => ['type' => 'text', 'data-options' => '[{
                "name": "Cycle between values",
                "value": "30|-30"
            }, {
                "name": "Random",
                "value": "random(-45,45)"
            }]'],
                ],

                'textRotateXIn'                => [
                    'value'   => 0,
                    'name'    => 'RotateX',
                    'tooltip' => 'Rotates text fragments along the X (horizontal) axis by the given number of degrees. Negative values are allowed for reverse direction. By listing multiple values separated with a | character, the slider will use different transition variations on each text node by cycling between the provided values.',
                    'keys'    => 'textrotatexin',
                    'attrs'   => ['type' => 'text', 'data-options' => '[{
                "name": "Cycle between values",
                "value": "30|-30"
            }, {
                "name": "Random",
                "value": "random(-45,45)"
            }]'],
                ],

                'textRotateYIn'                => [
                    'value'   => 0,
                    'name'    => 'RotateY',
                    'tooltip' => 'Rotates text fragments along the Y (vertical) axis by the given number of degrees. Negative values are allowed for reverse direction. By listing multiple values separated with a | character, the slider will use different transition variations on each text node by cycling between the provided values.',
                    'keys'    => 'textrotateyin',
                    'attrs'   => ['type' => 'text', 'data-options' => '[{
                "name": "Cycle between values",
                "value": "30|-30"
            }, {
                "name": "Random",
                "value": "random(-45,45)"
            }]'],
                ],

                'textScaleXIn'                 => [
                    'value'   => 1,
                    'name'    => 'ScaleX',
                    'keys'    => 'textscalexin',
                    'tooltip' => "Scales text fragments along the X (horizontal) axis by the specified vector. Use the value 1 for the original size. The value 2 will double, while 0.5 shrinks text fragments compared to their original size. By listing multiple values separated with a | character, the slider will use different transition variations on each text node by cycling between the provided values.",
                    'attrs'   => ['type' => 'text', 'data-options' => '[{
                "name": "Cycle between values",
                "value": "30|-30"
            }, {
                "name": "Random",
                "value": "random(2,4)"
            }]'],
                ],

                'textScaleYIn'                 => [
                    'value'   => 1,
                    'name'    => 'ScaleY',
                    'keys'    => 'textscaleyin',
                    'tooltip' => "Scales text fragments along the Y (vertical) axis by the specified vector. Use the value 1 for the original size. The value 2 will double, while 0.5 shrinks text fragments compared to their original size. By listing multiple values separated with a | character, the slider will use different transition variations on each text node by cycling between the provided values.",
                    'attrs'   => ['type' => 'text', 'data-options' => '[{
                "name": "Cycle between values",
                "value": "30|-30"
            }, {
                "name": "Random",
                "value": "random(2,4)"
            }]'],
                ],

                'textSkewXIn'                  => [
                    'value'   => 0,
                    'name'    => 'SkewX',
                    'tooltip' => 'Skews text fragments along the X (horizontal) axis by the given number of degrees. Negative values are allowed for reverse direction. By listing multiple values separated with a | character, the slider will use different transition variations on each text node by cycling between the provided values.',
                    'keys'    => 'textskewxin',
                    'attrs'   => ['type' => 'text', 'data-options' => '[{
                "name": "Cycle between values",
                "value": "30|-30"
            }, {
                "name": "Random",
                "value": "random(-45,45)"
            }]'],
                ],

                'textSkewYIn'                  => [
                    'value'   => 0,
                    'name'    => 'SkewY',
                    'tooltip' => 'Skews text fragments along the Y (vertical) axis by the given number of degrees. Negative values are allowed for reverse direction. By listing multiple values separated with a | character, the slider will use different transition variations on each text node by cycling between the provided values.',
                    'keys'    => 'textskewyin',
                    'attrs'   => ['type' => 'text', 'data-options' => '[{
                "name": "Cycle between values",
                "value": "30|-30"
            }, {
                "name": "Random",
                "value": "random(-45,45)"
            }]'],
                ],

                'textTransformOriginIn'        => [
                    'value'   => '50% 50% 0',
                    'name'    => 'Transform Origin',
                    'tooltip' => 'Sets a point on canvas from which transformations are calculated. For example, a layer may rotate around its center axis or a completely custom point, such as one of its corners. The three values represent the X, Y and Z axes in 3D space. Apart from the pixel and percentage values, you can also use the following constants: top, right, bottom, left, center, slidercenter, slidermiddle, slidertop, sliderright, sliderbottom, sliderleft.',
                    'keys'    => 'texttransformoriginin',
                    'attrs'   => ['data-options' => '[{
                "name": "Cycle between values",
                "value": "50% 50% 0|100% 100% 0"
            }]'],
                ],

                'textPerspectiveIn'            => [
                    'value'   => '500',
                    'name'    => 'Perspective',
                    'keys'    => 'texttransformperspectivein',
                    'tooltip' => 'Changes the perspective of this layer in the 3D space.',
                ],

                // Text Animation OUT
                // -----------------

                'textTransitionOut'            => [
                    'value' => false,
                    'keys'  => 'texttransitionout',
                ],

                'textTypeOut'                  => [
                    'value'   => 'chars_desc',
                    'name'    => 'Text Animation',
                    'keys'    => 'texttypeout',
                    'tooltip' => 'Select how your text should be split and animated.',
                    'options' => [
                        'chars_asc'    => 'by chars ascending',
                        'chars_desc'   => 'by chars descending',
                        'chars_rand'   => 'by chars random',
                        'chars_center' => 'by chars center to edge',
                        'chars_edge'   => 'by chars edge to center',
                        'words_asc'    => 'by words ascending',
                        'words_desc'   => 'by words descending',
                        'words_rand'   => 'by words random',
                        'words_center' => 'by words center to edge',
                        'words_edge'   => 'by words edge to center',
                        'lines_asc'    => 'by lines ascending',
                        'lines_desc'   => 'by lines descending',
                        'lines_rand'   => 'by lines random',
                        'lines_center' => 'by lines center to edge',
                        'lines_edge'   => 'by lines edge to center',
                    ],
                    'props'   => [
                        'output' => true,
                    ],
                ],

                'textShiftOut'                 => [
                    'value'   => '',
                    'name'    => 'Shift Out',
                    'tooltip' => 'Delays the transition of each text nodes relative to each other. A second equals to 1000 milliseconds.',
                    'keys'    => 'textshiftout',
                    'attrs'   => ['type' => 'number'],
                ],

                'textOffsetXOut'               => [
                    'value'   => 0,
                    'name'    => 'OffsetX',
                    'tooltip' => "Shifts the ending position of text nodes from their original on the horizontal axis with the given number of pixels. Use negative values for the opposite direction. Percentage values are relative to the width of this layer. The values 'left' or 'right' position text nodes out the staging area, so they leave the scene from either side when animating to their destination location. By listing multiple values separated with a | character, the slider will use different transition variations on each text node by cycling between the provided values.",
                    'keys'    => 'textoffsetxout',
                    'attrs'   => ['type' => 'text', 'data-options' => '[{
                "name": "Leave the stage on left",
                "value": "left"
            }, {
                "name": "Leave the stage on right",
                "value": "right"
            }, {
                "name": "100% layer width",
                "value": "100lw"
            }, {
                "name": "-100% layer width",
                "value": "-100lw"
            }, {
                "name": "50% slider width",
                "value": "50sw"
            }, {
                "name": "-50% slider width",
                "value": "-50sw"
            }, {
                "name": "Cycle between values",
                "value": "50|-50"
            }, {
                "name": "Random",
                "value": "random(-100,100)"
            }]'],
                ],

                'textOffsetYOut'               => [
                    'value'   => 0,
                    'name'    => 'OffsetY',
                    'tooltip' => "Shifts the ending position of text nodes from their original on the vertical axis with the given number of pixels. Use negative values for the opposite direction. Percentage values are relative to the width of this layer. The values 'top' or 'bottom' position text nodes out the staging area, so they leave the scene from either vertical side when animating to their destination location. By listing multiple values separated with a | character, the slider will use different transition variations on each text node by cycling between the provided values.",
                    'keys'    => 'textoffsetyout',
                    'attrs'   => ['type' => 'text', 'data-options' => '[{
                "name": "Leave the stage on top",
                "value": "top"
            }, {
                "name": "Leave the stage on bottom",
                "value": "bottom"
            }, {
                "name": "100% layer height",
                "value": "100lh"
            }, {
                "name": "-100% layer height",
                "value": "-100lh"
            }, {
                "name": "50% slider height",
                "value": "50sh"
            }, {
                "name": "-50% slider height",
                "value": "-50sh"
            }, {
                "name": "Cycle between values",
                "value": "50|-50"
            }, {
                "name": "Random",
                "value": "random(-100,100)"
            }]'],
                ],

                'textDurationOut'              => [
                    'value'   => 1000,
                    'name'    => 'Duration',
                    'tooltip' => 'The transition length in milliseconds of the individual text fragments. A second equals to 1000 milliseconds.',
                    'keys'    => 'textdurationout',
                    'attrs'   => ['min' => 0, 'step' => 50],
                ],

                'textEasingOut'                => [
                    'value'   => 'easeInOutQuint',
                    'name'    => 'Easing',
                    'tooltip' => "The timing function of the animation. With this function you can manipulate the movement of animated text fragments. Please click on the link next to this select field to open easings.net for more information and real-time examples.",
                    'keys'    => 'texteasingout',
                    'attrs'   => ['type' => 'hidden'],
                ],

                'textFadeOut'                  => [
                    'value'   => true,
                    'name'    => 'Fade',
                    'tooltip' => 'Fade the text fragments during their transition.',
                    'keys'    => 'textfadeout',
                ],

                'textStartAtOut'               => [
                    'value'   => 'allinandloopend',
                    'name'    => 'StartAt',
                    'tooltip' => 'You can set the starting time of this transition. Use one of the pre-defined options to use relative timing, which can be shifted with custom operations.',
                    'keys'    => 'textstartatout',
                    'attrs'   => ['type' => 'hidden'],
                ],

                'textStartAtOutTiming'         => [
                    'value'   => 'allinandloopend',
                    'keys'    => 'textstartatouttiming',
                    'props'   => ['meta' => true],
                    'options' => [
                        'transitioninend'        => 'Opening Transition completes',
                        'textinstart'            => 'Opening Text Transition starts',
                        'textinend'              => 'Opening Text Transition completes',
                        'allinend'               => 'Opening and Opening Text Transition complete',
                        'loopstart'              => 'Loop starts',
                        'loopend'                => 'Loop completes',
                        'transitioninandloopend' => 'Opening and Loop Transitions complete',
                        'textinandloopend'       => 'Opening Text and Loop Transitions complete',
                        'allinandloopend'        => 'Opening, Opening Text and Loop Transitions complete',
                    ],
                ],

                'textStartAtOutOperator'       => [
                    'value'   => '+',
                    'keys'    => 'textstartatoutoperator',
                    'props'   => ['meta' => true],
                    'options' => ['+', '-', '/', '*'],
                ],

                'textStartAtOutValue'          => [
                    'value' => 0,
                    'keys'  => 'textstartatoutvalue',
                    'props' => ['meta' => true],
                ],

                'textRotateOut'                => [
                    'value'   => 0,
                    'name'    => 'Rotate',
                    'tooltip' => 'Rotates text fragments clockwise by the given number of degrees. Negative values are allowed for counterclockwise rotation. By listing multiple values separated with a | character, the slider will use different transition variations on each text node by cycling between the provided values.',
                    'keys'    => 'textrotateout',
                    'attrs'   => ['type' => 'text', 'data-options' => '[{
            "name": "Cycle between values",
                "value": "30|-30"
            }, {
                "name": "Random",
                "value": "random(-45,45)"
            }]'],
                ],

                'textRotateXOut'               => [
                    'value'   => 0,
                    'name'    => 'RotateX',
                    'tooltip' => 'Rotates text fragments along the X (horizontal) axis by the given number of degrees. Negative values are allowed for reverse direction. By listing multiple values separated with a | character, the slider will use different transition variations on each text node by cycling between the provided values.',
                    'keys'    => 'textrotatexout',
                    'attrs'   => ['type' => 'text', 'data-options' => '[{
                "name": "Cycle between values",
                "value": "30|-30"
            }, {
                "name": "Random",
                "value": "random(-45,45)"
            }]'],
                ],

                'textRotateYOut'               => [
                    'value'   => 0,
                    'name'    => 'RotateY',
                    'tooltip' => 'Rotates text fragments along the Y (vertical) axis by the given number of degrees. Negative values are allowed for reverse direction. By listing multiple values separated with a | character, the slider will use different transition variations on each text node by cycling between the provided values.',
                    'keys'    => 'textrotateyout',
                    'attrs'   => ['type' => 'text', 'data-options' => '[{
                "name": "Cycle between values",
                "value": "30|-30"
            }, {
                "name": "Random",
                "value": "random(-45,45)"
            }]'],
                ],

                'textScaleXOut'                => [
                    'value'   => 1,
                    'name'    => 'ScaleX',
                    'keys'    => 'textscalexout',
                    'tooltip' => "Scales text fragments along the X (horizontal) axis by the specified vector. Use the value 1 for the original size. The value 2 will double, while 0.5 shrinks text fragments compared to their original size. By listing multiple values separated with a | character, the slider will use different transition variations on each text node by cycling between the provided values.",
                    'attrs'   => ['type' => 'text', 'data-options' => '[{
                "name": "Cycle between values",
                "value": "30|-30"
            }, {
                "name": "Random",
                "value": "random(2,4)"
            }]'],
                ],

                'textScaleYOut'                => [
                    'value'   => 1,
                    'name'    => 'ScaleY',
                    'keys'    => 'textscaleyout',
                    'tooltip' => "Scales text fragments along the Y (vertical) axis by the specified vector. Use the value 1 for the original size. The value 2 will double, while 0.5 shrinks text fragments compared to their original size. By listing multiple values separated with a | character, the slider will use different transition variations on each text node by cycling between the provided values.",
                    'attrs'   => ['type' => 'text', 'data-options' => '[{
                "name": "Cycle between values",
                "value": "30|-30"
            }, {
                "name": "Random",
                "value": "random(2,4)"
            }]'],
                ],

                'textSkewXOut'                 => [
                    'value'   => 0,
                    'name'    => 'SkewX',
                    'tooltip' => 'Skews text fragments along the X (horizontal) axis by the given number of degrees. Negative values are allowed for reverse direction. By listing multiple values separated with a | character, the slider will use different transition variations on each text node by cycling between the provided values.',
                    'keys'    => 'textskewxout',
                    'attrs'   => ['type' => 'text', 'data-options' => '[{
                "name": "Cycle between values",
                "value": "30|-30"
            }, {
                "name": "Random",
                "value": "random(-45,45)"
            }]'],
                ],

                'textSkewYOut'                 => [
                    'value'   => 0,
                    'name'    => 'SkewY',
                    'tooltip' => 'Skews text fragments along the Y (vertical) axis by the given number of degrees. Negative values are allowed for reverse direction. By listing multiple values separated with a | character, the slider will use different transition variations on each text node by cycling between the provided values.',
                    'keys'    => 'textskewyout',
                    'attrs'   => ['type' => 'text', 'data-options' => '[{
                "name": "Cycle between values",
                "value": "30|-30"
            }, {
                "name": "Random",
                "value": "random(-45,45)"
            }]'],
                ],

                'textTransformOriginOut'       => [
                    'value'   => '50% 50% 0',
                    'name'    => 'Transform Origin',
                    'tooltip' => 'Sets a point on canvas from which transformations are calculated. For example, a layer may rotate around its center axis or a completely custom point, such as one of its corners. The three values represent the X, Y and Z axes in 3D space. Apart from the pixel and percentage values, you can also use the following constants: top, right, bottom, left, center, slidercenter, slidermiddle, slidertop, sliderright, sliderbottom, sliderleft.',
                    'keys'    => 'texttransformoriginout',
                    'attrs'   => ['type' => 'text', 'data-options' => '[{
                "name": "Cycle between values",
                "value": "50% 50% 0|100% 100% 0"
            }]'],
                ],

                'textPerspectiveOut'           => [
                    'value'   => '500',
                    'name'    => 'Perspective',
                    'keys'    => 'texttransformperspectiveout',
                    'tooltip' => 'Changes the perspective of this layer in the 3D space.',
                ],

                // ======

                // LOOP

                'loop'                         => [
                    'value' => false,
                    'keys'  => 'loop',
                ],

                'loopOffsetX'                  => [
                    'value'   => 0,
                    'name'    => 'OffsetX',
                    'keys'    => 'loopoffsetx',
                    'tooltip' => "Shifts the layer starting position from its original on the horizontal axis with the given number of pixels. Use negative values for the opposite direction. Percentage values are relative to the width of this layer. The values 'left' or 'right' position the layer out the staging area, so it can leave and re-enter the scene from either side during the transition.",
                    'attrs'   => ['type' => 'text', 'data-options' => '[{
                "name": "Move out of stage on left",
                "value": "left"
            }, {
                "name": "Move out of stage on right",
                "value": "right"
            }, {
                "name": "100% layer width",
                "value": "100lw"
            }, {
                "name": "-100% layer width",
                "value": "-100lw"
            }, {
                "name": "50% slider width",
                "value": "50sw"
            }, {
                "name": "-50% slider width",
                "value": "-50sw"
            }, {
                "name": "Random",
                "value": "random(-100,100)"
            }]'],
                ],

                'loopOffsetY'                  => [
                    'value'   => 0,
                    'name'    => 'OffsetY',
                    'keys'    => 'loopoffsety',
                    'tooltip' => "Shifts the layer starting position from its original on the vertical axis with the given number of pixels. Use negative values for the opposite direction. Percentage values are relative to the height of this layer. The values 'top' or 'bottom' position the layer out the staging area, so it can leave and re-enter the scene from either vertical side during the transition.",
                    'attrs'   => ['type' => 'text', 'data-options' => '[{
                "name": "Move out of stage on top",
                "value": "top"
            }, {
                "name": "Move out of stage on bottom",
                "value": "bottom"
            }, {
                "name": "100% layer height",
                "value": "100lh"
            }, {
                "name": "-100% layer height",
                "value": "-100lh"
            }, {
                "name": "50% slider height",
                "value": "50sh"
            }, {
                "name": "-50% slider height",
                "value": "-50sh"
            }, {
                "name": "Random",
                "value": "random(-100,100)"
            }]'],
                ],

                'loopDuration'                 => [
                    'value'   => 1000,
                    'name'    => 'Duration',
                    'keys'    => 'loopduration',
                    'tooltip' => 'The length of the transition in milliseconds. A second is equal to 1000 milliseconds.',
                    'attrs'   => ['min' => 0, 'step' => 100],
                ],

                'loopStartAt'                  => [
                    'value'   => 'allinend',
                    'name'    => 'Start at',
                    'keys'    => 'loopstartat',
                    'tooltip' => 'You can set the starting time of this transition. Use one of the pre-defined options to use relative timing, which can be shifted with custom operations.',
                    'attrs'   => ['type' => 'hidden', 'step' => 100],
                ],

                'loopStartAtTiming'            => [
                    'value'   => 'allinend',
                    'keys'    => 'loopstartattiming',
                    'props'   => ['meta' => true],
                    'options' => [
                        'transitioninstart' => 'Opening Transition starts',
                        'transitioninend'   => 'Opening Transition completes',
                        'textinstart'       => 'Opening Text Transition starts',
                        'textinend'         => 'Opening Text Transition completes',
                        'allinend'          => 'Opening and Opening Text Transition complete',
                    ],
                ],

                'loopStartAtOperator'          => [
                    'value'   => '+',
                    'keys'    => 'loopstartatoperator',
                    'props'   => ['meta' => true],
                    'options' => ['+', '-', '/', '*'],
                ],

                'loopStartAtValue'             => [
                    'value' => 0,
                    'keys'  => 'loopstartatvalue',
                    'props' => ['meta' => true],
                ],

                'loopEasing'                   => [
                    'value'   => 'linear',
                    'name'    => 'Easing',
                    'keys'    => 'loopeasing',
                    'tooltip' => "The timing function of the animation to manipualte the layer's movement. Click on the link next to this field to open easings.net for examples and more information",
                ],

                'loopOpacity'                  => [
                    'value'   => 1,
                    'name'    => 'Opacity',
                    'keys'    => 'loopopacity',
                    'tooltip' => 'Fades the layer. You can use values between 1 and 0 to set the layer fully opaque or transparent respectively. For example, the value 0.5 will make the layer semi-transparent.',
                    'attrs'   => ['min' => 0, 'max' => 1, 'step' => 0.01],
                ],

                'loopRotate'                   => [
                    'value'   => 0,
                    'name'    => 'Rotate',
                    'keys'    => 'looprotate',
                    'tooltip' => 'Rotates the layer by the given number of degrees. Negative values are allowed for counterclockwise rotation.',
                    'attrs'   => ['type' => 'text', 'data-options' => '[{
                "name": "Random",
                "value": "random(-45,45)"
            }]'],
                ],

                'loopRotateX'                  => [
                    'value'   => 0,
                    'name'    => 'RotateX',
                    'keys'    => 'looprotatex',
                    'tooltip' => 'Rotates the layer along the X (horizontal) axis by the given number of degrees. Negative values are allowed for reverse direction.',
                    'attrs'   => ['type' => 'text', 'data-options' => '[{
                "name": "Random",
                "value": "random(-45,45)"
            }]'],
                ],

                'loopRotateY'                  => [
                    'value'   => 0,
                    'name'    => 'RotateY',
                    'keys'    => 'looprotatey',
                    'tooltip' => 'Rotates the layer along the Y (vertical) axis by the given number of degrees. Negative values are allowed for reverse direction.',
                    'attrs'   => ['type' => 'text', 'data-options' => '[{
                "name": "Random",
                "value": "random(-45,45)"
            }]'],
                ],

                'loopSkewX'                    => [
                    'value'   => 0,
                    'name'    => 'SkewX',
                    'keys'    => 'loopskewx',
                    'tooltip' => 'Skews the layer along the X (horizontal) axis by the given number of degrees. Negative values are allowed for reverse direction.',
                    'attrs'   => ['type' => 'text', 'data-options' => '[{
                "name": "Random",
                "value": "random(-45,45)"
            }]'],
                ],

                'loopSkewY'                    => [
                    'value'   => 0,
                    'name'    => 'SkewY',
                    'keys'    => 'loopskewy',
                    'tooltip' => 'Skews the layer along the Y (vertical) axis by the given number of degrees. Negative values are allowed for reverse direction.',
                    'attrs'   => ['type' => 'text', 'data-options' => '[{
                "name": "Random",
                "value": "random(-45,45)"
            }]'],
                ],

                'loopScaleX'                   => [
                    'value'   => 1,
                    'name'    => 'ScaleX',
                    'keys'    => 'loopscalex',
                    'tooltip' => "Scales the layer along the X (horizontal) axis by the specified vector. Use the value 1 for the original size. The value 2 will double, while 0.5 shrinks the layer compared to its original size.",
                    'attrs'   => ['type' => 'text', 'data-options' => '[{
                "name": "Random",
                "value": "random(2,4)"
            }]'],
                ],

                'loopScaleY'                   => [
                    'value'   => 1,
                    'name'    => 'ScaleY',
                    'keys'    => 'loopscaley',
                    'tooltip' => "Scales the layer along the X (horizontal) axis by the specified vector. Use the value 1 for the original size. The value 2 will double, while 0.5 shrinks the layer compared to its original size.",
                    'attrs'   => ['type' => 'text', 'data-options' => '[{
                "name": "Random",
                "value": "random(2,4)"
            }]'],
                ],

                'loopTransformOrigin'          => [
                    'value'   => '50% 50% 0',
                    'name'    => 'Transform Origin',
                    'keys'    => 'looptransformorigin',
                    'tooltip' => 'Sets a point on canvas from which transformations are calculated. For example, a layer may rotate around its center axis or a completely custom point, such as one of its corners. The three values represent the X, Y and Z axes in 3D space. Apart from the pixel and percentage values, you can also use the following constants: top, right, bottom, left, center, slidercenter, slidermiddle, slidertop, sliderright, sliderbottom, sliderleft.',
                ],

                'loopClip'                     => [
                    'value'   => '',
                    'name'    => 'Mask',
                    'keys'    => 'loopclip',
                    'tooltip' => 'Clips (cuts off) the sides of the layer by the given amount specified in pixels or percentages. The 4 value in order: top, right, bottom and the left side of the layer.',
                    'attrs'   => ['data-options' => '[{
                "name": "From top",
                "value": "0 0 100% 0"
            }, {
                "name": "From right",
                "value": "0 0 0 100%"
            }, {
                "name": "From bottom",
                "value": "100% 0 0 0"
            }, {
                "name": "From left",
                "value": "0 100% 0 0"
            }]'],
                ],

                'loopCount'                    => [
                    'value'   => 1,
                    'name'    => 'Count',
                    'keys'    => 'loopcount',
                    'tooltip' => 'The number of times repeating the Loop transition. The count includes the reverse part of the transitions when you use the Yoyo feature. Use the value -1 to repeat infinitely or zero to disable looping.',
                    'attrs'   => [
                        'step'         => 1,
                        'data-options' => '[{
                    "name": "Infinite",
                    "value": -1
                }]',
                    ],
                    'props'   => [
                        'output' => true,
                    ],
                ],

                'loopWait'                     => [
                    'value'   => 0,
                    'name'    => 'Wait',
                    'keys'    => 'looprepeatdelay',
                    'tooltip' => 'Waiting time between repeats in milliseconds. A second is 1000 milliseconds.',
                    'attrs'   => ['min' => 0, 'step' => 100],
                ],

                'loopYoyo'                     => [
                    'value'   => false,
                    'name'    => 'Yoyo',
                    'keys'    => 'loopyoyo',
                    'tooltip' => 'Enable this option to allow reverse transition, so you can loop back and forth seamlessly.',
                ],

                'loopPerspective'              => [
                    'value'   => '500',
                    'name'    => 'Perspective',
                    'keys'    => 'looptransformperspective',
                    'tooltip' => 'Changes the perspective of this layer in the 3D space.',
                ],

                'loopFilter'                   => [
                    'value'   => '',
                    'name'    => 'Filter',
                    'keys'    => 'loopfilter',
                    'tooltip' => 'Filters provide effects like blurring or color shifting your layers. Click into the text field to see a selection of filters you can use. Although clicking on the pre-defined options will reset the text field, you can apply multiple filters simply by providing a space separated list of all the filters you would like to use. Click on the "Filter" link for more information.',
                    'premium' => true,
                    'attrs'   => [
                        'data-options' => '[{
                    "name": "Blur",
                    "value": "blur(5px)"
                }, {
                    "name": "Brightness",
                    "value": "brightness(40%)"
                }, {
                    "name": "Contrast",
                    "value": "contrast(200%)"
                }, {
                    "name": "Grayscale",
                    "value": "grayscale(50%)"
                }, {
                    "name": "Hue-rotate",
                    "value": "hue-rotate(90deg)"
                }, {
                    "name": "Invert",
                    "value": "invert(75%)"
                }, {
                    "name": "Saturate",
                    "value": "saturate(30%)"
                }, {
                    "name": "Sepia",
                    "value": "sepia(60%)"
                }]',
                    ],
                ],

                // HOVER

                'hover'                        => [
                    'value' => false,
                    'keys'  => 'hover',
                ],

                'hoverOffsetX'                 => [
                    'value'   => 0,
                    'name'    => 'OffsetX',
                    'keys'    => 'hoveroffsetx',
                    'tooltip' => "Moves the layer horizontally by the given number of pixels. Use negative values for the opposite direction. Percentage values are relative to the width of this layer. ",
                    'attrs'   => ['type' => 'text', 'data-options' => '[{
                "name": "20% layer width",
                "value": "20lw"
            }, {
                "name": "-20% layer width",
                "value": "-20lw"
            }, {
                "name": "Random",
                "value": "random(-100,100)"
            }]'],
                ],

                'hoverOffsetY'                 => [
                    'value'   => 0,
                    'name'    => 'OffsetY',
                    'keys'    => 'hoveroffsety',
                    'tooltip' => "Moves the layer vertically by the given number of pixels. Use negative values for the opposite direction. Percentage values are relative to the width of this layer. ",
                    'attrs'   => ['type' => 'text', 'data-options' => '[{
                "name": "20% layer height",
                "value": "20lh"
            }, {
                "name": "-20% layer height",
                "value": "-20lh"
            }, {
                "name": "Random",
                "value": "random(-100,100)"
            }]'],
                ],

                'hoverInDuration'              => [
                    'value'   => 500,
                    'name'    => 'Duration',
                    'keys'    => 'hoverdurationin',
                    'tooltip' => 'The length of the transition in milliseconds. A second is equal to 1000 milliseconds.',
                    'attrs'   => ['min' => 0, 'step' => 100],
                ],

                'hoverOutDuration'             => [
                    'value'   => '',
                    'name'    => 'Reverse<br>duration',
                    'keys'    => 'hoverdurationout',
                    'tooltip' => 'The duration of the reverse transition in milliseconds. A second is equal to 1000 milliseconds.',
                    'attrs'   => ['min' => 0, 'step' => 100, 'placeholder' => 'same'],
                ],

                'hoverInEasing'                => [
                    'value'   => 'easeInOutQuint',
                    'name'    => 'Easing',
                    'keys'    => 'hovereasingin',
                    'tooltip' => "The timing function of the animation to manipualte the layer's movement. Click on the link next to this field to open easings.net for examples and more information",
                ],

                'hoverOutEasing'               => [
                    'value'   => '',
                    'name'    => 'Reverse<br>easing',
                    'keys'    => 'hovereasingout',
                    'tooltip' => "The timing function of the reverse animation to manipualte the layer's movement. Click on the link next to this field to open easings.net for examples and more information",
                    'attrs'   => ['placeholder' => 'same'],
                ],

                'hoverOpacity'                 => [
                    'value'   => '',
                    'name'    => 'Opacity',
                    'keys'    => 'hoveropacity',
                    'tooltip' => 'Fades the layer. You can use values between 1 and 0 to set the layer fully opaque or transparent respectively. For example, the value 0.5 will make the layer semi-transparent.',
                    'attrs'   => [
                        'min'  => 0,
                        'max'  => 1,
                        'step' => 0.1,
                    ],
                ],

                'hoverRotate'                  => [
                    'value'   => 0,
                    'name'    => 'Rotate',
                    'keys'    => 'hoverrotate',
                    'tooltip' => 'Rotates the layer clockwise by the given number of degrees. Negative values are allowed for counterclockwise rotation.',
                    'attrs'   => ['type' => 'text', 'data-options' => '[{
                "name": "Random",
                "value": "random(-45,45)"
            }]'],
                ],

                'hoverRotateX'                 => [
                    'value'   => 0,
                    'name'    => 'RotateX',
                    'keys'    => 'hoverrotatex',
                    'tooltip' => 'Rotates the layer along the X (horizontal) axis by the given number of degrees. Negative values are allowed for reverse direction.',
                    'attrs'   => ['type' => 'text', 'data-options' => '[{
                "name": "Random",
                "value": "random(-45,45)"
            }]'],
                ],

                'hoverRotateY'                 => [
                    'value'   => 0,
                    'name'    => 'RotateY',
                    'keys'    => 'hoverrotatey',
                    'tooltip' => 'Rotates the layer along the Y (vertical) axis by the given number of degrees. Negative values are allowed for reverse direction.',
                    'attrs'   => ['type' => 'text', 'data-options' => '[{
                "name": "Random",
                "value": "random(-45,45)"
            }]'],
                ],

                'hoverSkewX'                   => [
                    'value'   => 0,
                    'name'    => 'SkewX',
                    'keys'    => 'hoverskewx',
                    'tooltip' => 'Skews the layer along the X (horizontal) axis by the given number of degrees. Negative values are allowed for reverse direction.',
                    'attrs'   => ['type' => 'text', 'data-options' => '[{
                "name": "Random",
                "value": "random(-45,45)"
            }]'],
                ],

                'hoverSkewY'                   => [
                    'value'   => 0,
                    'name'    => 'SkewY',
                    'keys'    => 'hoverskewy',
                    'tooltip' => 'Skews the layer along the Y (vertical) axis by the given number of degrees. Negative values are allowed for reverse direction.',
                    'attrs'   => ['type' => 'text', 'data-options' => '[{
                "name": "Random",
                "value": "random(-45,45)"
            }]'],
                ],

                'hoverScaleX'                  => [
                    'value'   => 1,
                    'name'    => 'ScaleX',
                    'keys'    => 'hoverscalex',
                    'tooltip' => "Scales the layer along the X (horizontal) axis by the specified vector. Use the value 1 for the original size. The value 2 will double, while 0.5 shrinks the layer compared to its original size.",
                    'attrs'   => ['type' => 'text', 'data-options' => '[{
                "name": "Random",
                "value": "random(2,4)"
            }]'],
                ],

                'hoverScaleY'                  => [
                    'value'   => 1,
                    'name'    => 'ScaleY',
                    'keys'    => 'hoverscaley',
                    'tooltip' => "Scales the layer along the Y (vertical) axis by the specified vector. Use the value 1 for the original size. The value 2 will double, while 0.5 shrinks the layer compared to its original size.",
                    'attrs'   => ['type' => 'text', 'data-options' => '[{
                "name": "Random",
                "value": "random(2,4)"
            }]'],
                ],

                'hoverTransformOrigin'         => [
                    'value'   => '50% 50% 0',
                    'attrs'   => ['placeholder' => 'inherit'],
                    'name'    => 'Transform Origin',
                    'keys'    => 'hovertransformorigin',
                    'tooltip' => 'Sets a point on canvas from which transformations are calculated. For example, a layer may rotate around its center axis or a completely custom point, such as one of its corners. The three values represent the X, Y and Z axes in 3D space. Apart from the pixel and percentage values, you can also use the following constants: top, right, bottom, left, center.',
                ],

                'hoverBGColor'                 => [
                    'value'   => '',
                    'name'    => 'Background',
                    'keys'    => 'hoverbgcolor',
                    'tooltip' => "The background color of this layer. You can use color names, hexadecimal, RGB or RGBA values as well as the 'transparent' keyword. Example: #FFF",
                ],

                'hoverColor'                   => [
                    'value'   => '',
                    'name'    => 'Color',
                    'keys'    => 'hovercolor',
                    'tooltip' => 'The text color of this text. You can use color names, hexadecimal, RGB or RGBA values. Example: #333',
                ],

                'hoverBorderRadius'            => [
                    'value'   => '',
                    'name'    => 'Rounded corners',
                    'keys'    => 'hoverborderradius',
                    'tooltip' => 'If you want rounded corners, you can set here its radius in pixels. Example: 5px',
                ],

                'hoverTransformPerspective'    => [
                    'value'   => 500,
                    'name'    => 'Perspective',
                    'keys'    => 'hovertransformperspective',
                    'tooltip' => 'Changes the perspective of layers in the 3D space.',
                ],

                'hoverTopOn'                   => [
                    'value'   => true,
                    'name'    => 'Always on top',
                    'keys'    => 'hoveralwaysontop',
                    'tooltip' => 'Show this layer above every other layer while hovering.',
                ],

                // Parallax
                'parallax'                     => [
                    'value' => false,
                    'keys'  => 'parallax',
                ],

                'parallaxLevel'                => [
                    'value'   => 10,
                    'name'    => 'Parallax Level',
                    'tooltip' => 'Set the intensity of the parallax effect. Use negative values to shift layers in the opposite direction.',
                    'keys'    => 'parallaxlevel',
                    'props'   => [
                        'output' => true,
                    ],
                ],

                'parallaxType'                 => [
                    'value'   => 'inherit',
                    'name'    => 'Type',
                    'tooltip' => 'Choose if you want 2D or 3D parallax layers.',
                    'keys'    => 'parallaxtype',
                    'options' => [
                        'inherit' => 'Inherit from Slide Options',
                        '2d'      => '2D',
                        '3d'      => '3D',
                    ],
                ],

                'parallaxEvent'                => [
                    'value'   => 'inherit',
                    'name'    => 'Event',
                    'tooltip' => 'You can trigger the parallax effect by either scrolling the page, or by moving your mouse cursor / tilting your mobile device.',
                    'keys'    => 'parallaxevent',
                    'options' => [
                        'inherit' => 'Inherit from Slide Options',
                        'cursor'  => 'Cursor or Tilt',
                        'scroll'  => 'Scroll',
                    ],
                ],

                'parallaxAxis'                 => [
                    'value'   => 'inherit',
                    'name'    => 'Axes',
                    'tooltip' => 'Choose on which axes parallax layers should move.',
                    'keys'    => 'parallaxaxis',
                    'options' => [
                        'inherit' => 'Inherit from Slide Options',
                        'none'    => 'None',
                        'both'    => 'Both',
                        'x'       => 'Horizontal only',
                        'y'       => 'Vertical only',
                    ],
                ],

                'parallaxTransformOrigin'      => [
                    'value'   => '',
                    'name'    => 'Transform Origin',
                    'tooltip' => 'Sets a point on canvas from which transformations are calculated. For example, a layer may rotate around its center axis or a completely custom point, such as one of its corners. The three values represent the X, Y and Z axes in 3D space. Apart from the pixel and percentage values, you can also use the following constants: top, right, bottom, left, center.',
                    'keys'    => 'parallaxtransformorigin',
                    'attrs'   => [
                        'placeholder' => 'Inherit from Slide Options',
                    ],
                ],

                'parallaxDurationMove'         => [
                    'value'   => '',
                    'name'    => 'Move Duration',
                    'tooltip' => 'Controls the speed of animating layers when you move your mouse cursor or tilt your mobile device.',
                    'keys'    => 'parallaxdurationmove',
                    'attrs'   => [
                        'type'        => 'number',
                        'step'        => 100,
                        'min'         => 0,
                        'placeholder' => 'Inherit from Slide Options',
                    ],
                ],

                'parallaxDurationLeave'        => [
                    'value'   => '',
                    'name'    => 'Leave Duration',
                    'tooltip' => 'Controls how quickly parallax layers revert to their original position when you move your mouse cursor outside of the slider. This value is in milliseconds. A second equals to 1000 milliseconds.',
                    'keys'    => 'parallaxdurationleave',
                    'attrs'   => [
                        'type'        => 'number',
                        'step'        => 100,
                        'min'         => 0,
                        'placeholder' => 'Inherit from Slide Options',
                    ],
                ],

                'parallaxRotate'               => [
                    'value'   => '',
                    'name'    => 'Rotation',
                    'tooltip' => 'Increase or decrease the amount of layer rotation in the 3D space when moving your mouse cursor or tilting on a mobile device.',
                    'keys'    => 'parallaxrotate',
                    'attrs'   => [
                        'type'        => 'number',
                        'step'        => 1,
                        'placeholder' => 'Inherit from Slide Options',
                    ],
                ],

                'parallaxDistance'             => [
                    'value'   => '',
                    'name'    => 'Distance',
                    'tooltip' => 'Increase or decrease the amount of layer movement when moving your mouse cursor or tilting on a mobile device.',
                    'keys'    => 'parallaxdistance',
                    'attrs'   => [
                        'type'        => 'number',
                        'step'        => 1,
                        'placeholder' => 'Inherit from Slide Options',
                    ],
                ],

                'parallaxPerspective'          => [
                    'value'   => '',
                    'name'    => 'Perspective',
                    'tooltip' => 'Changes the perspective of layers in the 3D space.',
                    'keys'    => 'parallaxtransformperspective',
                    'attrs'   => [
                        'type'        => 'number',
                        'step'        => 100,
                        'placeholder' => 'Inherit from Slide Options',
                    ],
                ],

                // TRANSITON MISC
                'transitionStatic'             => [
                    'value'   => 'none',
                    'name'    => 'Static layer',
                    'keys'    => 'static',
                    'tooltip' => "You can keep this layer on top of the slider across multiple slides. Just select the slide on which this layer should animate out. Alternatively, you can make this layer global on all slides after it transitioned in.",
                    'options' => [
                        'none'    => 'Disabled (default)',
                        'forever' => 'Enabled (never animate out)',
                    ],
                ],

                'transitionKeyframe'           => [
                    'value'   => false,
                    'name'    => 'Play By Scroll Keyframe',
                    'keys'    => 'keyframe',
                    'tooltip' => 'A Play by Scroll slider will pause when this layer finished its opening transition.',
                ],

// Attributes

                'linkURL'                      => [
                    'value'   => '',
                    'name'    => 'Enter URL',
                    'keys'    => 'url',
                    'tooltip' => 'If you want to link your layer, type here the URL. You can use a hash mark followed by a number to link this layer to another slide. Example: #3 - this will switch to the third slide.',
                    'attrs'   => [
                        'data-options' => '[{
                    "name": "Switch to the next slide",
                    "value": "#next"
                }, {
                    "name": "Switch to the previous slide",
                    "value": "#prev"
                }, {
                    "name": "Stop the slideshow",
                    "value": "#stop"
                }, {
                    "name": "Resume the slideshow",
                    "value": "#start"
                }, {
                    "name": "Replay the slide from the start",
                    "value": "#replay"
                }, {
                    "name": "Reverse the slide, then pause it",
                    "value": "#reverse"
                }, {
                    "name": "Reverse the slide, then replay it",
                    "value": "#reverse-replay"
                }]',
                    ],
                    'props'   => [
                        'meta' => true,
                    ],
                ],

                'linkTarget'                   => [
                    'value'   => '_self',
                    'name'    => 'URL target',
                    'keys'    => 'target',
                    'options' => [
                        '_self'     => 'Open on the same page',
                        '_blank'    => 'Open on new page',
                        '_parent'   => 'Open in parent frame',
                        '_top'      => 'Open in main frame',
                        'ls-scroll' => 'Scroll to element (Enter selector)',
                    ],
                    'props'   => [
                        'meta' => true,
                    ],
                ],

                'innerAttributes'              => [
                    'value' => '',
                    'name'  => 'Custom Attributes',
                    'keys'  => 'innerAttributes',
                    'desc'  => 'Your list of custom attributes. Use this feature if your needs are not covered by the common attributes above or you want to override them. You can use data-* as well as regular attribute names. Empty attributes (without value) are also allowed. For example, to make a FancyBox gallery, you may enter "data-fancybox-group" and "gallery1" for the attribute name and value, respectively.',
                    'props' => [
                        'meta' => true,
                    ],
                ],

                'outerAttributes'              => [
                    'value' => '',
                    'name'  => 'Custom Attributes',
                    'keys'  => 'outerAttributes',
                    'desc'  => 'Your list of custom attributes. Use this feature if your needs are not covered by the common attributes above or you want to override them. You can use data-* as well as regular attribute names. Empty attributes (without value) are also allowed. For example, to make a FancyBox gallery, you may enter "data-fancybox-group" and "gallery1" for the attribute name and value, respectively.',
                    'props' => [
                        'meta' => true,
                    ],
                ],

                // Styles

                'width'                        => [
                    'value'   => '',
                    'name'    => 'Width',
                    'keys'    => 'width',
                    'tooltip' => "You can set the width of your layer. You can use pixels, percentage, or the default value 'auto'. Examples: 100px, 50% or auto.",
                    'props'   => [
                        'meta' => true,
                    ],
                ],

                'height'                       => [
                    'value'   => '',
                    'name'    => 'Height',
                    'keys'    => 'height',
                    'tooltip' => "You can set the height of your layer. You can use pixels, percentage, or the default value 'auto'. Examples: 100px, 50% or auto",
                    'props'   => [
                        'meta' => true,
                    ],
                ],

                'top'                          => [
                    'value'   => '10px',
                    'name'    => 'Top',
                    'keys'    => 'top',
                    'tooltip' => "The layer position from the top of the slide. You can use pixels and percentage. Examples: 100px or 50%. You can move your layers in the preview above with a drag n' drop, or set the exact values here.",
                    'props'   => [
                        'meta' => true,
                    ],
                ],

                'left'                         => [
                    'value'   => '10px',
                    'name'    => 'Left',
                    'keys'    => 'left',
                    'tooltip' => "The layer position from the left side of the slide. You can use pixels and percentage. Examples: 100px or 50%. You can move your layers in the preview above with a drag n' drop, or set the exact values here.",
                    'props'   => [
                        'meta' => true,
                    ],
                ],

                'paddingTop'                   => [
                    'value'   => '',
                    'name'    => 'Top',
                    'keys'    => 'padding-top',
                    'tooltip' => 'Padding on the top of the layer. Example: 10px',
                    'props'   => [
                        'meta' => true,
                    ],
                ],

                'paddingRight'                 => [
                    'value'   => '',
                    'name'    => 'Right',
                    'keys'    => 'padding-right',
                    'tooltip' => 'Padding on the right side of the layer. Example: 10px',
                    'props'   => [
                        'meta' => true,
                    ],
                ],

                'paddingBottom'                => [
                    'value'   => '',
                    'name'    => 'Bottom',
                    'keys'    => 'padding-bottom',
                    'tooltip' => 'Padding on the bottom of the layer. Example: 10px',
                    'props'   => [
                        'meta' => true,
                    ],
                ],

                'paddingLeft'                  => [
                    'value'   => '',
                    'name'    => 'Left',
                    'keys'    => 'padding-left',
                    'tooltip' => 'Padding on the left side of the layer. Example: 10px',
                    'props'   => [
                        'meta' => true,
                    ],
                ],

                'borderTop'                    => [
                    'value'   => '',
                    'name'    => 'Top',
                    'keys'    => 'border-top',
                    'tooltip' => 'Border on the top of the layer. Example: 5px solid #000',
                    'props'   => [
                        'meta' => true,
                    ],
                ],

                'borderRight'                  => [
                    'value'   => '',
                    'name'    => 'Right',
                    'keys'    => 'border-right',
                    'tooltip' => 'Border on the right side of the layer. Example: 5px solid #000',
                    'props'   => [
                        'meta' => true,
                    ],
                ],

                'borderBottom'                 => [
                    'value'   => '',
                    'name'    => 'Bottom',
                    'keys'    => 'border-bottom',
                    'tooltip' => 'Border on the bottom of the layer. Example: 5px solid #000',
                    'props'   => [
                        'meta' => true,
                    ],
                ],

                'borderLeft'                   => [
                    'value'   => '',
                    'name'    => 'Left',
                    'keys'    => 'border-left',
                    'tooltip' => 'Border on the left side of the layer. Example: 5px solid #000',
                    'props'   => [
                        'meta' => true,
                    ],
                ],

                'fontFamily'                   => [
                    'value'   => '',
                    'name'    => 'Family',
                    'keys'    => 'font-family',
                    'tooltip' => 'List of your chosen fonts separated with a comma. Please use apostrophes if your font names contains white spaces. Example: Helvetica, Arial, sans-serif',
                ],

                'fontSize'                     => [
                    'value'   => '',
                    'name'    => 'Font size',
                    'keys'    => 'font-size',
                    'tooltip' => 'The font size in pixels. Example: 16px.',
                    'attrs'   => ['data-options' => '["9", "10", "11", "12", "13", "14", "18", "24", "36", "48", "64", "96"]'],
                    'props'   => [
                        'meta' => true,
                    ],
                ],

                'lineHeight'                   => [
                    'value'   => '',
                    'name'    => 'Line height',
                    'keys'    => 'line-height',
                    'tooltip' => "The line height of your text. The default setting is 'normal'. Example: 22px",
                    'props'   => [
                        'meta' => true,
                    ],
                ],

                'fontWeight'                   => [
                    'value'   => 400,
                    'name'    => 'Font weight',
                    'keys'    => 'font-weight',
                    'tooltip' => 'Sets the font boldness. Please note, not every font supports all the listed variants, thus some settings may have the same result.',
                    'options' => [
                        '100' => '100 (UltraLight)',
                        '200' => '200 (Thin)',
                        '300' => '300 (Light)',
                        '400' => '400 (Regular)',
                        '500' => '500 (Medium)',
                        '600' => '600 (Semibold)',
                        '700' => '700 (Bold)',
                        '800' => '800 (Heavy)',
                        '900' => '900 (Black)',
                    ],
                    'props'   => [
                        'meta' => true,
                    ],
                ],

                'fontStyle'                    => [
                    'value'   => 'normal',
                    'name'    => 'Font style',
                    'keys'    => 'font-style',
                    'tooltip' => 'Oblique is an auto-generated italic version of your chosen font and can force slating even if there is no italic font variant available. However, you should use the regular italic option whenever is possible. Please double check to load italic font variants when using Google Fonts.',
                    'options' => [
                        'normal'  => 'Normal',
                        'italic'  => 'Italic',
                        'oblique' => 'Oblique (Forced slant)',
                    ],
                    'props'   => [
                        'meta' => true,
                    ],
                ],

                'textDecoration'               => [
                    'value'   => 'none',
                    'name'    => 'Text decoration',
                    'keys'    => 'text-decoration',
                    'options' => [
                        'none'         => 'None',
                        'underline'    => 'Underline',
                        'overline'     => 'Overline',
                        'line-through' => 'Line through',

                    ],
                    'props'   => [
                        'meta' => true,
                    ],
                ],

                'letterSpacing'                => [
                    'value'   => '',
                    'name'    => 'Letter spacing',
                    'keys'    => 'letter-spacing',
                    'tooltip' => 'Controls the amount of space between each character. Useful the change letter density in a line or block of text. Negative values and decimals can be used.',
                    'attrs'   => [
                        'type' => 'number',
                        'step' => 0.5,
                    ],
                    'props'   => [
                        'meta' => true,
                    ],
                ],

                'textAlign'                    => [
                    'value'   => 'none',
                    'name'    => 'Text align',
                    'keys'    => 'text-align',
                    'options' => [
                        'initial' => 'Initial (Language default)',
                        'left'    => 'Left',
                        'right'   => 'Right',
                        'center'  => 'Center',
                        'justify' => 'Justify',

                    ],
                    'props'   => [
                        'meta' => true,
                    ],
                ],

                'opacity'                      => [
                    'value'   => 1,
                    'name'    => 'Opacity',
                    'keys'    => 'opacity',
                    'tooltip' => 'Fades the layer. You can use values between 1 and 0 to set the layer fully opaque or transparent respectively. For example, the value 0.5 will make the layer semi-transparent.',
                    'attrs'   => [
                        'min'  => 0,
                        'max'  => 1,
                        'step' => 0.1,
                    ],
                    'props'   => [
                        'meta' => true,
                    ],
                ],

                'minFontSize'                  => [
                    'value'   => '',
                    'name'    => 'Min. font size',
                    'keys'    => 'minfontsize',
                    'tooltip' => 'The minimum font size in a responsive slider. This option allows you to prevent your texts layers becoming too small on smaller screens.',
                ],

                'minMobileFontSize'            => [
                    'value'   => '',
                    'name'    => 'Min. mobile font size',
                    'keys'    => 'minmobilefontsize',
                    'tooltip' => 'The minimum font size in a responsive slider on mobile devices. This option allows you to prevent your texts layers becoming too small on smaller screens.',
                ],

                'color'                        => [
                    'value'   => '',
                    'name'    => 'Color',
                    'keys'    => 'color',
                    'tooltip' => 'The color of your text. You can use color names, hexadecimal, RGB or RGBA values. Example: #333',
                    'props'   => [
                        'meta' => true,
                    ],
                ],

                'background'                   => [
                    'value'   => '',
                    'name'    => 'Background',
                    'keys'    => 'background',
                    'tooltip' => "The background color of your layer. You can use color names, hexadecimal, RGB or RGBA values as well as the 'transparent' keyword. Example: #FFF",
                    'props'   => [
                        'meta' => true,
                    ],
                ],

                'borderRadius'                 => [
                    'value'   => '',
                    'name'    => 'Rounded corners',
                    'keys'    => 'border-radius',
                    'tooltip' => 'If you want rounded corners, you can set its radius here. Example: 5px',
                    'props'   => [
                        'meta' => true,
                    ],
                ],

                'wordWrap'                     => [
                    'value'   => false,
                    'name'    => 'Word-wrap',
                    'keys'    => 'wordwrap',
                    'tooltip' => 'Enable this option to allow line breaking if your text content does not fit into one line. By default, layers have auto sizes based on the text length. If you set custom sizes, it\'s recommended to enable this option in most cases.',
                    'props'   => [
                        'meta' => true,
                    ],
                ],

                'style'                        => [
                    'value'   => '',
                    'name'    => 'Custom styles',
                    'keys'    => 'style',
                    'tooltip' => 'If you want to set style settings other than above, you can use here any CSS codes. Please make sure to write valid markup.',
                    'props'   => [
                        'meta' => true,
                    ],
                ],

                'styles'                       => [
                    'value' => '',
                    'keys'  => 'styles',
                    'props' => [
                        'meta' => true,
                        'raw'  => true,
                    ],
                ],

                'rotate'                       => [
                    'value'   => 0,
                    'name'    => 'Rotate',
                    'keys'    => 'rotation',
                    'tooltip' => 'The rotation angle where this layer animates toward when entering into the slider canvas. Negative values are allowed for counterclockwise rotation.',
                ],

                'rotateX'                      => [
                    'value'   => 0,
                    'name'    => 'RotateX',
                    'keys'    => 'rotationX',
                    'tooltip' => 'The rotation angle on the horizontal axis where this animates toward when entering into the slider canvas. Negative values are allowed for reversed direction.',
                ],

                'rotateY'                      => [
                    'value'   => 0,
                    'name'    => 'RotateY',
                    'keys'    => 'rotationY',
                    'tooltip' => 'The rotation angle on the vertical axis where this layer animates toward when entering into the slider canvas. Negative values are allowed for reversed direction.',
                ],

                'scaleX'                       => [
                    'value'   => 1,
                    'name'    => 'ScaleX',
                    'keys'    => 'scaleX',
                    'tooltip' => 'The layer horizontal scale where this layer animates toward when entering into the slider canvas.',
                ],

                'scaleY'                       => [
                    'value'   => 1,
                    'name'    => 'ScaleY',
                    'keys'    => 'scaleY',
                    'tooltip' => 'The layer vertical scale where this layer animates toward when entering into the slider canvas.',
                ],

                'skewX'                        => [
                    'value'   => 0,
                    'name'    => 'SkewX',
                    'keys'    => 'skewX',
                    'tooltip' => 'The layer horizontal skewing angle where this layer animates toward when entering into the slider canvas.',
                ],

                'skewY'                        => [
                    'value'   => 0,
                    'name'    => 'SkewY',
                    'keys'    => 'skewY',
                    'tooltip' => 'The layer vertical skewing angle where this layer animates toward when entering into the slider canvas.',
                ],

                'position'                     => [
                    'value'   => 'relative',
                    'name'    => 'Calculate positions from',
                    'keys'    => 'position',
                    'tooltip' => 'Sets the layer position origin from which top and left values are calculated. The default is the upper left corner of the slider canvas. In a full width and full size slider, your content is centered based on the screen size to achieve the best possible fit. By selecting the "sides of the screen" option in those scenarios, you can allow layers to escape the centered inner area and stick to the sides of the screen.',
                    'options' => [
                        'relative' => 'sides of the slider',
                        'fixed'    => 'sides of the screen',
                    ],
                ],

                'zIndex'                       => [
                    'value'   => '',
                    'name'    => 'Stacking order',
                    'keys'    => 'z-index',
                    'tooltip' => "This option controls the vertical stacking order of layers that overlap. In CSS, it's commonly called as z-index. Elements with a higher value are stacked in front of elements with a lower one, effectively covering them. By default, this value is calculated automatically based on the order of your layers, thus simply re-ordering them can fix overlap issues. Use this option only if you want to set your own value manually in special cases like using static layers.<br><br>On each slide, the stacking order starts counting from 100. Providing a number less than 100 will put the layer behind every other layer on all slides. Specifying a much greater number, for example 500, will make the layer to be on top of everything else.",
                    'attrs'   => [
                        'type'        => 'number',
                        'min'         => 1,
                        'placeholder' => 'auto',
                    ],
                ],

                'blendMode'                    => [
                    'value'   => 'normal',
                    'name'    => 'Blend mode',
                    'keys'    => 'mix-blend-mode',
                    'tooltip' => 'Choose how layers and the slide background should blend into each other. Blend modes are an easy way to add eye-catching effects and is one of the most frequently used features in graphic and print design.',
                    'premium' => true,
                    'options' => [
                        'normal'      => 'Normal',
                        'multiply'    => 'Multiply',
                        'screen'      => 'Screen',
                        'overlay'     => 'Overlay',
                        'darken'      => 'Darken',
                        'lighten'     => 'Lighten',
                        'color-dodge' => 'Color-dodge',
                        'color-burn'  => 'Color-burn',
                        'hard-light'  => 'Hard-light',
                        'soft-light'  => 'Soft-light',
                        'difference'  => 'Difference',
                        'exclusion'   => 'Exclusion',
                        'hue'         => 'Hue',
                        'saturation'  => 'Saturation',
                        'color'       => 'Color',
                        'luminosity'  => 'Luminosity',
                    ],
                ],

                'filter'                       => [
                    'value'   => '',
                    'name'    => 'Filter',
                    'keys'    => 'filter',
                    'tooltip' => 'Filters provide effects like blurring or color shifting your layers. Click into the text field to see a selection of filters you can use. Although clicking on the pre-defined options will reset the text field, you can apply multiple filters simply by providing a space separated list of all the filters you would like to use. Click on the "Filter" link for more information.',
                    'premium' => true,
                    'attrs'   => [
                        'data-options' => '[{
                    "name": "Blur",
                    "value": "blur(5px)"
                }, {
                    "name": "Brightness",
                    "value": "brightness(40%)"
                }, {
                    "name": "Contrast",
                    "value": "contrast(200%)"
                }, {
                    "name": "Grayscale",
                    "value": "grayscale(50%)"
                }, {
                    "name": "Hue-rotate",
                    "value": "hue-rotate(90deg)"
                }, {
                    "name": "Invert",
                    "value": "invert(75%)"
                }, {
                    "name": "Saturate",
                    "value": "saturate(30%)"
                }, {
                    "name": "Sepia",
                    "value": "sepia(60%)"
                }]',
                    ],
                ],

                // Attributes

                'ID'                           => [
                    'value'   => '',
                    'name'    => 'ID',
                    'keys'    => 'id',
                    'tooltip' => "You can apply an ID attribute on the HTML element of this layer to work with it in your custom CSS or Javascript code.",
                    'props'   => [
                        'meta' => true,
                    ],
                ],

                'class'                        => [
                    'value'   => '',
                    'name'    => 'Classes',
                    'keys'    => 'class',
                    'tooltip' => 'You can apply classes on the HTML element of this layer to work with it in your custom CSS or Javascript code.',
                    'props'   => [
                        'meta' => true,
                    ],
                ],

                'title'                        => [
                    'value'   => '',
                    'name'    => 'Title',
                    'keys'    => 'title',
                    'tooltip' => 'You can add a title to this layer which will display as a tooltip if someone holds his mouse cursor over the layer.',
                    'props'   => [
                        'meta' => true,
                    ],
                ],

                'alt'                          => [
                    'value'   => '',
                    'name'    => 'Alt',
                    'keys'    => 'alt',
                    'tooltip' => 'Name or describe your image layer, so search engines and VoiceOver softwares can properly identify it.',
                    'props'   => [
                        'meta' => true,
                    ],
                ],

                'rel'                          => [
                    'value'   => '',
                    'name'    => 'Rel',
                    'keys'    => 'rel',
                    'tooltip' => 'Plugins and search engines may use this attribute to get more information about the role and behavior of a link.',
                    'props'   => [
                        'meta' => true,
                    ],
                ],

            ],

            'easings' => [
                'linear',
                'swing',
                'easeInQuad',
                'easeOutQuad',
                'easeInOutQuad',
                'easeInCubic',
                'easeOutCubic',
                'easeInOutCubic',
                'easeInQuart',
                'easeOutQuart',
                'easeInOutQuart',
                'easeInQuint',
                'easeOutQuint',
                'easeInOutQuint',
                'easeInSine',
                'easeOutSine',
                'easeInOutSine',
                'easeInExpo',
                'easeOutExpo',
                'easeInOutExpo',
                'easeInCirc',
                'easeOutCirc',
                'easeInOutCirc',
                'easeInElastic',
                'easeOutElastic',
                'easeInOutElastic',
                'easeInBack',
                'easeOutBack',
                'easeInOutBack',
                'easeInBounce',
                'easeOutBounce',
                'easeInOutBounce',
            ],
        ];
		
		$this->extracss = $this->pushCSS([
            $this->admin_webpath . '/themes/' . $this->bo_theme . '/css/wp-pointer.min.css',
            $this->admin_webpath . '/themes/' . $this->bo_theme . '/css/wp-specs.css',
            $this->admin_webpath . '/themes/' . $this->bo_theme . '/css/global.css',
            $this->admin_webpath . '/themes/' . $this->bo_theme . '/css/dashicons.css',
            $this->admin_webpath . '/themes/' . $this->bo_theme . '/css/admin.css',
            $this->admin_webpath . '/themes/' . $this->bo_theme . '/css/admin_new.css',
            $this->admin_webpath . '/themes/' . $this->bo_theme . '/css/km-ui.css',
            $this->admin_webpath . '/themes/' . $this->bo_theme . '/css/codemirror.css',
            $this->admin_webpath . '/themes/' . $this->bo_theme . '/css/solarized.mod.css',
            $this->admin_webpath . '/themes/' . $this->bo_theme . '/css/layerslider.css',
        ]);

    }

    public function ajaxProcessOpenTargetController() {

		$targetController = $this->targetController;

		$data = $this->createTemplate('controllers/layer_slider/slider_list.tpl');
		
		$jsToAdd = '';

        foreach ($this->push_js_files as $jsUri) {
            $jsToAdd .= '<script type="text/javascript" src="' . $jsUri . '"></script>' . PHP_EOL;
        }
		
		$cssToAdd = '';

        foreach ($this->extracss as $css_uri => $media) {
            $cssToAdd .= '<link rel="stylesheet" href="' . $css_uri . '" type="text/css" media="' . $media . '" />' . PHP_EOL;
        }

        $sliders = FrontSlider::find();

        $mediamanagerurl = $this->context->link->getAdminLink('AdminLayerSliderMedia');
        $adminmodulesurl = $this->context->link->getAdminLink('AdminModules') . '&configure=layerslider&module_name=layerslider';
        $lsVersion = _EPH_VERSION_;
        $lsSaveHistory = Configuration::get('LS_SAVE_HISTORY') ? 1 : 0;
        $userSettings = Tools::jsonEncode([
            'time' => time(),
            'uid'  => $this->context->employee->id,
            'url'  => __PS_BASE_URI__,
        ]);
        $_wpPluploadSettings = Tools::jsonEncode([
            'defaults' => [
                'multipart_params' => [
                    '_wpnonce' => $GLOBALS['ls_token'],
                ],
            ],
        ]);

        $data->assign([
            'mediamanagerurl'          => $mediamanagerurl,
            'adminmodulesurl'          => $adminmodulesurl,
            'lsVersion'                => $lsVersion,
            'lsSaveHistory'            => $lsSaveHistory,
            'userSettings'             => $userSettings,
            '_wpPluploadSettings'      => $_wpPluploadSettings,
            'jsToAdd'                  => $jsToAdd,
            'cssToAdd'                 => $cssToAdd,
            'link'                     => $this->context->link,
            'controller'               => 'AdminLayerSlider',
            'AjaxLinkAdminLayerSlider' => $this->context->link->getAdminLink('AdminLayerSlider'),
            'sliders'                  => $sliders,
        ]);

		$li = '<li id="uper'.$targetController.'" data-controller="AdminDashboard"><a href="#content'.$targetController.'">'.$this->publicName.'</a><button type="button" class="close tabdetail" data-id="uper'.$targetController.'"><i class="icon icon-times-circle"></i></button></li>';
		$html = '<div id="content'.$targetController.'" class="panel col-lg-12" style="display; flow-root;">' . $data->fetch() . '</div>';

		$result = [
			'li'   => $li,

			'html' => $html,
		];

		die(Tools::jsonEncode($result));
	}

    public function setAjaxMedia() {
		
		return $this->pushJS([
			$this->admin_webpath . '/js/wp-pointer.min.js',
            $this->admin_webpath . '/js/greensock.js',
            $this->admin_webpath . '/js/km-ui.js',
            $this->admin_webpath . '/js/ls-admin-common.js',
            $this->admin_webpath . '/js/codemirror.js',
            $this->admin_webpath . '/js/css.js',
            $this->admin_webpath . '/js/javascript.js',
            $this->admin_webpath . '/js/foldcode.js',
            $this->admin_webpath . '/js/foldgutter.js',
            $this->admin_webpath . '/js/brace-fold.js',
            $this->admin_webpath . '/js/active-line.js',
            $this->admin_webpath . '/js/ls-admin-sliders.js',
			$this->admin_webpath . '/js/layerslider/layerslider.webshopworks.jquery.js',
            $this->admin_webpath . '/js/layerslider/layerslider.transitions.js',
            $this->admin_webpath . '/js/slider.js',
		]);
	}

    public function ajaxProcessBackToList() {

        $extraJs = $this->pushJS([
            $this->admin_webpath . '/js/wp-pointer.min.js',
            $this->admin_webpath . '/js/greensock.js',
            $this->admin_webpath . '/js/km-ui.js',
            $this->admin_webpath . '/js/ls-admin-common.js',
            $this->admin_webpath . '/js/codemirror.js',
            $this->admin_webpath . '/js/css.js',
            $this->admin_webpath . '/js/javascript.js',
            $this->admin_webpath . '/js/foldcode.js',
            $this->admin_webpath . '/js/foldgutter.js',
            $this->admin_webpath . '/js/brace-fold.js',
            $this->admin_webpath . '/js/active-line.js',
            $this->admin_webpath . '/js/ls-admin-sliders.js',
            $this->admin_webpath . '/js/layerslider/layerslider.webshopworks.jquery.js',
            $this->admin_webpath . '/js/layerslider/layerslider.transitions.js',
        ]);

        $jsToAdd = '';

        foreach ($extraJs as $jsUri) {
            $jsToAdd .= '<script type="text/javascript" src="' . $jsUri . '"></script>' . PHP_EOL;
        }

        $extracss = $this->pushCSS([
            $this->admin_webpath . '/themes/' . $this->bo_theme . '/css/wp-pointer.min.css',
            $this->admin_webpath . '/themes/' . $this->bo_theme . '/css/wp-specs.css',
            $this->admin_webpath . '/themes/' . $this->bo_theme . '/css/global.css',
            $this->admin_webpath . '/themes/' . $this->bo_theme . '/css/dashicons.css',
            $this->admin_webpath . '/themes/' . $this->bo_theme . '/css/admin.css',
            $this->admin_webpath . '/themes/' . $this->bo_theme . '/css/admin_new.css',
            $this->admin_webpath . '/themes/' . $this->bo_theme . '/css/km-ui.css',
            $this->admin_webpath . '/themes/' . $this->bo_theme . '/css/codemirror.css',
            $this->admin_webpath . '/themes/' . $this->bo_theme . '/css/solarized.mod.css',
            $this->admin_webpath . '/themes/' . $this->bo_theme . '/css/layerslider.css',
        ]);

        $cssToAdd = '';

        foreach ($extracss as $css_uri => $media) {
            $cssToAdd .= '<link rel="stylesheet" href="' . $css_uri . '" type="text/css" media="' . $media . '" />' . PHP_EOL;
        }

        $result = [
            'jsToAdd'  => $jsToAdd,
            'cssToAdd' => $cssToAdd,
        ];
        die(Tools::jsonEncode($result));
    }

    public function generateDisplay() {

        $data = $this->createTemplate('controllers/layer_slider/slider_list.tpl');

        $extraJs = $this->pushJS([
            $this->admin_webpath . '/js/wp-pointer.min.js',
            $this->admin_webpath . '/js/greensock.js',
            $this->admin_webpath . '/js/km-ui.js',
            $this->admin_webpath . '/js/ls-admin-common.js',
            $this->admin_webpath . '/js/codemirror.js',
            $this->admin_webpath . '/js/css.js',
            $this->admin_webpath . '/js/javascript.js',
            $this->admin_webpath . '/js/foldcode.js',
            $this->admin_webpath . '/js/foldgutter.js',
            $this->admin_webpath . '/js/brace-fold.js',
            $this->admin_webpath . '/js/active-line.js',
            $this->admin_webpath . '/js/ls-admin-sliders.js',
			$this->admin_webpath . '/js/layerslider/layerslider.webshopworks.jquery.js',
            $this->admin_webpath . '/js/layerslider/layerslider.transitions.js',
        ]);

        $jsToAdd = '';

        foreach ($extraJs as $jsUri) {
            $jsToAdd .= '<script type="text/javascript" src="' . $jsUri . '"></script>' . PHP_EOL;
        }

        $extracss = $this->pushCSS([
            $this->admin_webpath . '/themes/' . $this->bo_theme . '/css/wp-pointer.min.css',
            $this->admin_webpath . '/themes/' . $this->bo_theme . '/css/wp-specs.css',
            $this->admin_webpath . '/themes/' . $this->bo_theme . '/css/global.css',
            $this->admin_webpath . '/themes/' . $this->bo_theme . '/css/dashicons.css',
            $this->admin_webpath . '/themes/' . $this->bo_theme . '/css/admin.css',
            $this->admin_webpath . '/themes/' . $this->bo_theme . '/css/admin_new.css',
            $this->admin_webpath . '/themes/' . $this->bo_theme . '/css/km-ui.css',
            $this->admin_webpath . '/themes/' . $this->bo_theme . '/css/codemirror.css',
            $this->admin_webpath . '/themes/' . $this->bo_theme . '/css/solarized.mod.css',
            $this->admin_webpath . '/themes/' . $this->bo_theme . '/css/layerslider.css',
        ]);

        $cssToAdd = '';

        foreach ($extracss as $css_uri => $media) {
            $cssToAdd .= '<link rel="stylesheet" href="' . $css_uri . '" type="text/css" media="' . $media . '" />' . PHP_EOL;
        }

        $sliders = FrontSlider::find();

        $mediamanagerurl = $this->context->link->getAdminLink('AdminLayerSliderMedia');
        $adminmodulesurl = $this->context->link->getAdminLink('AdminModules') . '&configure=layerslider&module_name=layerslider';
        $lsVersion = _EPH_VERSION_;
        $lsSaveHistory = Configuration::get('LS_SAVE_HISTORY') ? 1 : 0;
        $userSettings = Tools::jsonEncode([
            'time' => time(),
            'uid'  => $this->context->employee->id,
            'url'  => __PS_BASE_URI__,
        ]);
        $_wpPluploadSettings = Tools::jsonEncode([
            'defaults' => [
                'multipart_params' => [
                    '_wpnonce' => $GLOBALS['ls_token'],
                ],
            ],
        ]);

        $data->assign([
            'mediamanagerurl'          => $mediamanagerurl,
            'adminmodulesurl'          => $adminmodulesurl,
            'lsVersion'                => $lsVersion,
            'lsSaveHistory'            => $lsSaveHistory,
            'userSettings'             => $userSettings,
            '_wpPluploadSettings'      => $_wpPluploadSettings,
            'jsToAdd'                  => $jsToAdd,
            'cssToAdd'                 => $cssToAdd,
            'link'                     => $this->context->link,
            'controller'               => 'AdminLayerSlider',
            'AjaxLinkAdminLayerSlider' => $this->context->link->getAdminLink('AdminLayerSlider'),
            'sliders'                  => $sliders,
        ]);

        return $data->fetch();

    }

    public function ls_get_hook_list() {

        $hooks = [
            ['name' => '- None -', 'value' => ''],
            ['name' => 'Home', 'value' => 'displayHome'],
            ['name' => 'Slider Financement', 'value' => 'displayFinancement'],
            ['name' => 'Top of pages', 'value' => 'displayTop'],
            ['name' => 'Banner', 'value' => 'displayBanner'],
            ['name' => 'Navigation', 'value' => 'displayNav'],
            ['name' => 'Top column blocks', 'value' => 'displayTopColumn'],
            ['name' => 'Left column blocks', 'value' => 'displayLeftColumn'],
            ['name' => 'Right column blocks', 'value' => 'displayRightColumn'],
            ['name' => 'Footer', 'value' => 'displayFooter'],
            ['name' => 'Creative Slider', 'value' => 'displayCreativeSlider'],
            ['name' => 'Home Slider', 'value' => 'displayRevsliderHome'],
            ['name' => 'Logo Slider', 'value' => 'displayLogo'],

        ];
        return json_encode($hooks);
    }

    public function setMedia() {

        parent::setMedia();

        MediaAdmin::addJsDef([
            'AjaxLinkAdminLayerSlider' => $this->context->link->getAdminLink('AdminLayerSlider'),

        ]);

    }

    public function displayAjax() {

        $tmpl = '<script type="text/html" id="tmpl-template-store">
            <div id="ls-importing-modal-window">
                <header>
                    <h1>Template Store</h1>
                    <b class="dashicons dashicons-no"></b>
                </header>
                <div class="km-ui-modal-scrollable">
                    <p>
                        ' . 'Premium templates are only available after you connected your site with PhenyxShop\'s marketplace.' . '
                        <a href="https://www.youtube.com/watch?v=SLFFWyY2NYM" target="_blank" style="font-size:13px">Check this video for more details.</a>
                    </p>
                    <button class="button button-primary button-hero" id="btn-connect-ps">Connect to PhenyxShop Addons</button>
                </div>
            </div>
        </script>';
        $this->context->smarty->assign(['content' => $tmpl . $this->content]);
        $this->display_footer = false;

        parent::displayAjax();
    }

    public function display() {

        $tmpl = '<script type="text/html" id="tmpl-template-store">
            <div id="ls-importing-modal-window">
                <header>
                    <h1>Template Store</h1>
                    <b class="dashicons dashicons-no"></b>
                </header>
                <div class="km-ui-modal-scrollable">
                    <p>
                        ' . 'Premium templates are only available after you connected your site with PhenyxShop\'s marketplace.' . '
                        <a href="https://www.youtube.com/watch?v=SLFFWyY2NYM" target="_blank" style="font-size:13px">Check this video for more details.</a>
                    </p>
                    <button class="button button-primary button-hero" id="btn-connect-ps">Connect to PhenyxShop Addons</button>
                </div>
            </div>
        </script>';
        $this->context->smarty->assign(['content' => $tmpl . $this->content]);
        $this->display_footer = false;

        parent::display();
    }

    public function ajaxProcessAddNewSlider() {

        $slider = new FrontSlider();
        $slider->name = Tools::getValue('title');
        $slider->author = $this->context->employee->id;
        $slider->data = Tools::jsonEncode([
            'properties' => [
                'createdWith'   => _EPH_VERSION_,
                'sliderVersion' => _EPH_VERSION_,
                'title'         => Tools::getValue('title'),
                'new'           => true,
            ],
            'layers'     => [[]],
        ]);
        $slider->date_c = time();
        $slider->date_m = time();

        $slider->add();
        $data = $this->createTemplate('controllers/layer_slider/sliderItem.tpl');

        $data->assign([
            'slider'                   => $slider,
            'AjaxLinkAdminLayerSlider' => $this->context->link->getAdminLink('AdminLayerSlider'),
        ]);

        $result = [
            'html' => $data->fetch(),
        ];
        die(Tools::jsonEncode($result));
    }

    public function ajaxProcessRemoveSlider() {

        $idLayerSlider = Tools::getValue('idSlider');
        $slider = new FrontSlider((int) $idLayerSlider);
        $result = $slider->delete();

        if ($result) {
            $return = [
                'success' => true,
            ];
        } else {
            $return = [
                'success' => false,
            ];
        }

        die(Tools::jsonEncode($return));
    }

    public function _ss($value) {

        return stripcslashes($value);
    }

    public function ajaxProcessEditLayerSlider() {

        
        $idSlider = Tools::getValue('idSlider');
        $sliderItem = new FrontSlider($idSlider);

        $slider = $sliderItem->data;

        $extraJs = $this->pushJS([
            $this->admin_webpath . '/js/wp-pointer.min.js',
            $this->admin_webpath . '/js/wp-specs.js',
            $this->admin_webpath . '/js/layerslider/greensock.js',
            $this->admin_webpath . '/js/km-ui.js',
            $this->admin_webpath . '/js/ls-admin-common.js',
            $this->admin_webpath . '/js/codemirror.js',
            $this->admin_webpath . '/js/css.js',
            $this->admin_webpath . '/js/javascript.js',
            $this->admin_webpath . '/js/foldcode.js',
            $this->admin_webpath . '/js/foldgutter.js',
            $this->admin_webpath . '/js/brace-fold.js',
            $this->admin_webpath . '/js/active-line.js',
            $this->admin_webpath . '/js/ls-admin-slider-builder.js',
            $this->admin_webpath . '/js/layerslider/layerslider.webshopworks.jquery.js',
            $this->admin_webpath . '/js/layerslider/layerslider.transitions.js',
            $this->admin_webpath . '/js/layerslider.transition.gallery.js',
            $this->admin_webpath . '/js/layerslider/plugins/timeline/layerslider.timeline.js',
            $this->admin_webpath . '/js/layerslider/plugins/origami/layerslider.origami.js',
            $this->admin_webpath . '/js/minicolors/jquery.minicolors.js',
            $this->admin_webpath . '/js/air-datepicker/air-datepicker.min.js',
            $this->admin_webpath . '/js/html2canvas/html2canvas.min.js',
        ]);

        $jsToAdd = '';

        foreach ($extraJs as $jsUri) {
            $jsToAdd .= '<script type="text/javascript" src="' . $jsUri . '"></script>' . PHP_EOL;
        }

        $extracss = $this->pushCSS([

            $this->admin_webpath . '/themes/' . $this->bo_theme . '/css/wp-pointer.min.css',
            $this->admin_webpath . '/themes/' . $this->bo_theme . '/css/wp-specs.css',
            $this->admin_webpath . '/themes/' . $this->bo_theme . '/css/global.css',
            $this->admin_webpath . '/themes/' . $this->bo_theme . '/css/dashicons.css',
            $this->admin_webpath . '/themes/' . $this->bo_theme . '/css/admin.css',
            $this->admin_webpath . '/themes/' . $this->bo_theme . '/css/admin_new.css',
            $this->admin_webpath . '/themes/' . $this->bo_theme . '/css/km-ui.css',
            $this->admin_webpath . '/themes/' . $this->bo_theme . '/css/codemirror.css',
            $this->admin_webpath . '/themes/' . $this->bo_theme . '/css/solarized.mod.css',
            $this->admin_webpath . '/themes/' . $this->bo_theme . '/css/layerslider.css',
            $this->admin_webpath . '/themes/' . $this->bo_theme . '/css/layerslider.transitiongallery.css',
            $this->admin_webpath . '/themes/' . $this->bo_theme . '/css/layerslider.timeline.css',
            $this->admin_webpath . '/themes/' . $this->bo_theme . '/css/layerslider.origami.css',
            $this->admin_webpath . '/themes/' . $this->bo_theme . '/css/jquery.minicolors.css',
            $this->admin_webpath . '/themes/' . $this->bo_theme . '/css/air-datepicker.min.css',

        ]);
        $cssToAdd = '';

        foreach ($extracss as $css_uri => $media) {
            $cssToAdd .= '<link rel="stylesheet" href="' . $css_uri . '" type="text/css" media="' . $media . '" />' . PHP_EOL;
        }

        $data = $this->createTemplate('controllers/layer_slider/slider_edit.tpl');

        $lsScreenOptions = Tools::jsonDecode(Configuration::get('LS_SCREEN_OPTIONS'), true);
        $lsScreenOptions = ($lsScreenOptions == 0) ? [] : $lsScreenOptions;
        $lsScreenOptions = is_array($lsScreenOptions) ? $lsScreenOptions : unserialize($lsScreenOptions);
        $lsDefaults = $this->lsDefaults;

        if (!isset($lsScreenOptions['showTooltips'])) {
            $lsScreenOptions['showTooltips'] = 'true';
        }

        if (!isset($lsScreenOptions['useKeyboardShortcuts'])) {
            $lsScreenOptions['useKeyboardShortcuts'] = 'true';
        }

        if (!isset($lsScreenOptions['useNotifyOSD'])) {
            $lsScreenOptions['useNotifyOSD'] = 'true';
        }

        if (!isset($slider['properties']['status'])) {
            $slider['properties']['status'] = true;
        }

        if (!isset($slider['properties']['slideBGSize']) && !isset($slider['properties']['new'])) {

            if (empty($slider['properties']['forceresponsive'])) {
                $slider['properties']['slideBGSize'] = 'auto';
            }

        }

        $slider['properties']['schedule_start'] = '';
        $slider['properties']['schedule_end'] = '';

        if (!empty($sliderItem->schedule_start)) {
            $slider['properties']['schedule_start'] = (int) $sliderItem->schedule_start;
        }

        if (!empty($sliderItem->schedule_end)) {
            $slider['properties']['schedule_end'] = (int) $sliderItem->schedule_end;
        }

        if (!empty($slider['properties']['yourlogoId'])) {
            $slider['properties']['yourlogo'] = FrontSlider::ls_get_image($slider['properties']['yourlogoId'], $slider['properties']['yourlogo']);
            $slider['properties']['yourlogoThumb'] = FrontSlider::ls_get_thumbnail($slider['properties']['yourlogoId'], $slider['properties']['yourlogo']);
        }

        $slider['properties']['cbinit'] = !empty($slider['properties']['cbinit']) ? $this->_ss($slider['properties']['cbinit']) : $lsDefaults['slider']['cbInit']['value'];
        $slider['properties']['cbstart'] = !empty($slider['properties']['cbstart']) ? $this->_ss($slider['properties']['cbstart']) : $lsDefaults['slider']['cbStart']['value'];
        $slider['properties']['cbstop'] = !empty($slider['properties']['cbstop']) ? $this->_ss($slider['properties']['cbstop']) : $lsDefaults['slider']['cbStop']['value'];
        $slider['properties']['cbpause'] = !empty($slider['properties']['cbpause']) ? $this->_ss($slider['properties']['cbpause']) : $lsDefaults['slider']['cbPause']['value'];
        $slider['properties']['cbanimstart'] = !empty($slider['properties']['cbanimstart']) ? $this->_ss($slider['properties']['cbanimstart']) : $lsDefaults['slider']['cbAnimStart']['value'];
        $slider['properties']['cbanimstop'] = !empty($slider['properties']['cbanimstop']) ? $this->_ss($slider['properties']['cbanimstop']) : $lsDefaults['slider']['cbAnimStop']['value'];
        $slider['properties']['cbprev'] = !empty($slider['properties']['cbprev']) ? $this->_ss($slider['properties']['cbprev']) : $lsDefaults['slider']['cbPrev']['value'];
        $slider['properties']['cbnext'] = !empty($slider['properties']['cbnext']) ? $this->_ss($slider['properties']['cbnext']) : $lsDefaults['slider']['cbNext']['value'];

        if (empty($slider['properties']['new']) && empty($slider['properties']['type'])) {

            if (!empty($slider['properties']['forceresponsive'])) {
                $slider['properties']['type'] = 'fullwidth';

                if (strpos($slider['properties']['width'], '%') !== false) {

                    if (!empty($slider['properties']['responsiveunder'])) {
                        $slider['properties']['width'] = $slider['properties']['responsiveunder'];
                    } else

                    if (!empty($slider['properties']['sublayercontainer'])) {
                        $slider['properties']['width'] = $slider['properties']['sublayercontainer'];
                    }

                }

            } else

            if (empty($slider['properties']['responsive'])) {
                $slider['properties']['type'] = 'fixedsize';
            } else {
                $slider['properties']['type'] = 'responsive';
            }

        }

        if (!empty($slider['properties']['width'])) {

            if (strpos($slider['properties']['width'], '%') !== false) {
                $slider['properties']['width'] = 1000;
            }

        }

        if (!empty($slider['properties']['sublayercontainer'])) {
            unset($slider['properties']['sublayercontainer']);
        }

        if (!empty($slider['properties']['width'])) {
            $slider['properties']['width'] = (int) $slider['properties']['width'];
        }

        if (!empty($slider['properties']['width'])) {
            $slider['properties']['height'] = (int) $slider['properties']['height'];
        }

        if (empty($slider['properties']['pauseonhover'])) {
            $slider['properties']['pauseonhover'] = 'enabled';
        }

        if (empty($slider['properties']['sliderVersion']) && empty($slider['properties']['circletimer'])) {
            $slider['properties']['circletimer'] = false;
        }

        foreach ($slider['properties'] as $optionKey => $optionValue) {

            switch ($optionValue) {
            case 'on':
                $slider['properties'][$optionKey] = true;
                break;

            case 'off':
                $slider['properties'][$optionKey] = false;
                break;
            }

        }

        foreach ($slider['layers'] as $slideKey => $slideVal) {

            if (!empty($slideVal['properties']['backgroundId'])) {
                $slideVal['properties']['background'] = FrontSlider::ls_get_image($slideVal['properties']['backgroundId'], $slideVal['properties']['background']);
                $slideVal['properties']['backgroundThumb'] = FrontSlider::ls_get_thumbnail($slideVal['properties']['backgroundId'], $slideVal['properties']['background']);
            }

            if (!empty($slideVal['properties']['thumbnailId'])) {
                $slideVal['properties']['thumbnail'] = FrontSlider::ls_get_image($slideVal['properties']['thumbnailId'], $slideVal['properties']['thumbnail']);
                $slideVal['properties']['thumbnailThumb'] = FrontSlider::ls_get_thumbnail( $slideVal['properties']['thumbnailId'], $slideVal['properties']['thumbnail']);
            }

            if (!empty($slideVal['sublayers']) && is_array($slideVal['sublayers'])) {
                $slideVal['sublayers'] = array_values($slideVal['sublayers']);
            }

            $slider['layers'][$slideKey] = $slideVal;

            if (!empty($slideVal['sublayers']) && is_array($slideVal['sublayers'])) {
                $slideVal['sublayers'] = array_reverse($slideVal['sublayers']);

                foreach ($slideVal['sublayers'] as $layerKey => $layerVal) {

                    if (!empty($layerVal['imageId'])) {
                        $layerVal['image'] = FrontSlider::ls_get_image($layerVal['imageId'], $layerVal['image']);
                        $layerVal['imageThumb'] = FrontSlider::ls_get_thumbnail($layerVal['imageId'], $layerVal['image']);
                    }

                    if (!empty($layerVal['posterId'])) {
                        $layerVal['poster'] = FrontSlider::ls_get_image( $layerVal['posterId'], $layerVal['poster']);
                        $layerVal['posterThumb'] = FrontSlider::ls_get_thumbnail($layerVal['posterId'], $layerVal['poster']);
                    }

                    $layerVal['styles'] = Tools::stripslashes($layerVal['styles']);
                    $layerVal['transition'] = Tools::stripslashes($layerVal['transition']);

                    $layerVal['styles'] = !empty($layerVal['styles']) ? (object) Tools::jsonDecode($this->_ss($layerVal['styles']), true) : new stdClass;
                    $layerVal['transition'] = !empty($layerVal['transition']) ? (object) Tools::jsonDecode($this->_ss($layerVal['transition']), true) : new stdClass;
                    $layerVal['html'] = !empty($layerVal['html']) ? $this->_ss($layerVal['html']) : '';

                    if (isset($layerVal['top'])) {
                        $layerVal['styles']->top = $layerVal['top'];
                        unset($layerVal['top']);
                    }

                    if (isset($layerVal['left'])) {
                        $layerVal['styles']->left = $layerVal['left'];
                        unset($layerVal['left']);
                    }

                    if (isset($layerVal['wordwrap'])) {
                        $layerVal['styles']->wordwrap = $layerVal['wordwrap'];
                        unset($layerVal['wordwrap']);
                    }

                    if (!empty($layerVal['transition']->showuntil)) {
                        $layerVal['transition']->startatout = 'transitioninend + ' . $layerVal['transition']->showuntil;
                        $layerVal['transition']->startatouttiming = 'transitioninend';
                        $layerVal['transition']->startatoutvalue = $layerVal['transition']->showuntil;
                        unset($layerVal['transition']->showuntil);
                    }

                    if (!empty($layerVal['transition']->parallaxlevel)) {
                        $layerVal['transition']->parallax = true;
                    }

                    $layerVal['innerAttributes'] = !empty($layerVal['innerAttributes']) ? (object) $layerVal['innerAttributes'] : new stdClass;
                    $layerVal['outerAttributes'] = !empty($layerVal['outerAttributes']) ? (object) $layerVal['outerAttributes'] : new stdClass;

                    if (isset($layerVal['transition']->controls)) {

                        if (true === $layerVal['transition']->controls) {
                            $layerVal['transition']->controls = 'auto';
                        } else

                        if (false === $layerVal['transition']->controls) {
                            $layerVal['transition']->controls = 'disabled';
                        }

                    }

                    $slider['layers'][$slideKey]['sublayers'][$layerKey] = $layerVal;
                }

            } else {
                $slider['layers'][$slideKey]['sublayers'] = [];
            }

        }

        if (!empty($slider['callbacks'])) {

            foreach ($slider['callbacks'] as $key => $callback) {
                $slider['callbacks'][$key] = $this->_ss($callback);
            }

        }

        $slider['properties']['sliderVersion'] = _EPH_VERSION_;

        $location = _MODULE_DIR_ . 'layerslider/views/img/layerslider/overlays/*';
        $overlays = ['disabled' => 'No overlay image'];

        foreach (glob($location) as $file) {
            $basename = basename($file);
            $url = _MODULE_DIR_ . 'layerslider/views/img/layerslider/overlays/' . $basename;
            $overlays[$url] = $basename;
        }

        $fontList = [
            ['name' => 'Arial', 'font' => true],
            ['name' => 'Helvetica', 'font' => true],
            ['name' => 'Georgia', 'font' => true],
            ['name' => 'Comic Sans MS', 'value' => "'Comic Sans MS'", 'font' => true],
            ['name' => 'Impact', 'font' => true],
            ['name' => 'Tahoma', 'font' => true],
            ['name' => 'Verdana', 'font' => true],
        ];
        $googleFonts = Tools::jsonDecode(Configuration::get('LS_GOOGLE_FONTS'));
        $sDefs = $lsDefaults['slider'];
        $sProps = $slider['properties'];

        $skins = FrontSlider::addSkins(_PS_MODULE_DIR_ . 'layerslider/views/css/layerslider/skins/');

       
        $ajaxurl = 'index.php?controller=AdminLayerSlider&ajax=1&token=' . $GLOBALS['ls_token'];

        $data->assign([
            'link'                     => $this->context->link,
            'controller'               => 'AdminLayerSlider',
            'ajaxurl'                  => $ajaxurl,
            'AjaxLinkAdminLayerSlider' => $this->context->link->getAdminLink('AdminLayerSlider'),
            'sliderItem'               => $sliderItem,
            'slider'                   => $slider,
            'lsScreenOptions'          => $lsScreenOptions,
            'lsDefaults'               => $lsDefaults,
            'overlays'                 => $overlays,
            'fontList'                 => Tools::jsonEncode($fontList),
            'googleFonts'              => $googleFonts,
            'sDefs'                    => $sDefs,
            'sProps'                   => $sProps,
            'skins'                    => $skins,
            '_EPH_VERSION_'            => _EPH_VERSION_,
        ]);

        $result = [
            'html'     => $data->fetch(),
            'jsToAdd'  => $jsToAdd,
            'cssToAdd' => $cssToAdd,
        ];
        die(Tools::jsonEncode($result));
    }

    public function ajaxProcessSaveSlider() {

       
		
		$idSlider = Tools::getValue('id');
		$sliderItem = new FrontSlider($idSlider);
		$data = Tools::getValue('sliderData');

        $slideLayers = $data['layers'];
        $data['properties'] = Tools::jsonDecode($this->_ss(html_entity_decode($data['properties'])), true);

        // Parse slide data

        if (!empty($slideLayers) && is_array($slideLayers)) {

            foreach ($data['layers'] as $slideKey => $slideData) {
                $slideData = Tools::jsonDecode($slideData, true);

                if (!empty($slideData['sublayers'])) {

                    foreach ($slideData['sublayers'] as $layerKey => $layerData) {

                        if (!empty($layerData['transition'])) {
                            $slideData['sublayers'][$layerKey]['transition'] = addslashes($layerData['transition']);
                        }

                        if (!empty($layerData['styles'])) {
                            $slideData['sublayers'][$layerKey]['styles'] = addslashes($layerData['styles']);
                        }
					}

                }

                $data['layers'][$slideKey] = $slideData;
            }

        }
		$title  = $data['properties']['title'];
		if (Tools::strlen($title) > 99) {
            $title = Tools::substr($title, 0, (99-Tools::strlen($title)));
        }
		
		$sliderItem->name = $title;
		$sliderItem->slug = !empty($data['properties']['slug']) ? $data['properties']['slug'] : '';
		
		$status = 0;
        if (empty($data['properties']['status']) || $data['properties']['status'] === 'false') {
            $status = 1;
        }

        // Schedule
        $schedule = array('schedule_start' => 0, 'schedule_end' => 0);
        foreach ($schedule as $key => $val) {
            if (! empty($data['properties'][$key])) {
                if (is_numeric($data['properties'][$key])) {
                    $schedule[$key] = (int) $data['properties'][$key];
                } else {
                    $tz = date_default_timezone_get();
                    date_default_timezone_set(date_default_timezone_get());
                    $schedule[$key] = (int) strtotime($data['properties'][$key]);
                    date_default_timezone_set($tz);
                }
            }
        }

        

        if (isset($data['properties']['relativeurls'])) {
            $data = FrontSlider::layerslider_convert_urls($data);
        }
		
		
		
		$sliderItem->data = Tools::jsonEncode($data, JSON_UNESCAPED_UNICODE);
		$sliderItem->schedule_start = $schedule['schedule_start'];
        $sliderItem->schedule_end = $schedule['schedule_end'];
        $sliderItem->date_m = time();
        $sliderItem->flag_hidden = $status;
		$sliderItem->author = $this->context->employee->id;
		
		
		try {
  			$result = $sliderItem->update();
 
		}
		catch(Exception $e) {
   			
		}

        
        $result = $sliderItem->update();
		
		
		if($result) {
			
			$revision = new LsRevision();
			$revision->id_layer_slider = $sliderItem->id;
			$revision->author = $sliderItem->author;
			$revision->data = $sliderItem->data;
			$revision->date_c = $sliderItem->date_m;
			try {
  				$revision->add(); 
			}
			catch(Exception $e) {
   				
			}
			
		}
		
		

        die(Tools::jsonEncode(['status' => 'ok']));
    }

}
