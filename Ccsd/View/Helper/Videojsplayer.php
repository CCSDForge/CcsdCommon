<?php

/**
 * Class Ccsd_View_Helper_Videojsplayer
 */
class Ccsd_View_Helper_Videojsplayer // extends Zend_View_Helper_Abstract
{
    public $view;

    protected $_id = 'videoPreview';

    protected $_src = '';

    protected $_url = '';

    protected $_image = '';

    protected $_width = 600;

    protected $_height = 360;

    protected $_title = '';

    protected $_play = false;

    public function __construct() {
    }

    /**
     * @param array $options
     * @return Ccsd_View_Helper_Videojsplayer
     */
    public function videojsplayer($options = [])
    {
        foreach ($options as $key => $value) {
            $this->{'_' . $key} = $value;
        }

        $this->_url = STREAMING_URL .'/'. $this->_src;
        return $this;
    }

    /**
     * @param $id
     * @return Ccsd_View_Helper_Videojsplayer
     */
    public function setId($id)
    {
        $this->_id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function render()
    {
        /** @var Ccsd_View $view */
        $view = $this->view;
        $view->jQuery()->addJavascriptFile("/js/video.min.js")
            ->addJavascriptFile("/js/videojs-ie8.min.js")
            ->addJavascriptFile("/js/videojs-seek-buttons.min.js")
            ->addStylesheet( '/css/video-js.min.css')
            ->addStylesheet('/css/videojs-seek-buttons.css');
        $render = '<style> .video-js .vjs-time-control { display:block; } .vjs-control :not(.vjs-time-tooltip), .vjs-slider-bar :not(.vjs-time-tooltip){ color: #fff !important; }</style>';
        $render .= '<video id="' . $this->_id . '" class="video-js" controls="true" crossorigin playsinline ><p class="vjs-no-js"> To view this video please enable JavaScript, and consider upgrading to a web browser that <a href="https://videojs.com/html5-video-support/" target="_blank"> supports HTML5 video </a> </p></video>';

        $render .= '<script type="text/javascript">' . "\n";
        $render .= 'var myPlayer = videojs("' . $this->_id . '", {' . "\n";
        $render .= 'width: ' . $this->_width . ',' . "\n";
        $render .= 'height: ' . $this->_height . ',' . "\n";
        if($this->_image)
            $render .= 'poster: "' . $this->_image . '",' . "\n";
        $render .= 'autoplay: '.($this->_play ? 'true':'false').',' . "\n";
        $render .= 'loop: false,' . "\n";
        $render .= 'playbackRates: [0.7, 1.0, 1.5, 2.0],' . "\n";
        $render .= 'timeDivider : false,' . "\n";
        $render .= 'bigPlayButton: true,' . "\n";
        $render .= 'controlBar: {' . "\n";
        $render .= 'currentTimeDisplay: true,' . "\n";
        $render .= 'timeDivider: false,' . "\n";
        $render .= 'durationDisplay: false,' . "\n";
        $render .= 'subtitlesButton: false,' . "\n";
        $render .= 'subsCapsButton: false' . "\n";
        $render .= '},' . "\n";
        $render .= 'sources: [' . "\n";
        /*$render .= '{' . "\n";
        //$render .= 'src: "' . $this->_rtmp .'",' . "\n";
        $render .= 'type: "rtmp/mp4"' . "\n";
        $render .= '},' . "\n";*/
        $render .= '{' . "\n";
        $render .= 'src: "' . $this->_url .'",' . "\n";
        $render .= 'type: "video/mp4"' . "\n";
        $render .= '},' . "\n";
        $render .= ']' . "\n";
        $render .= '  });' . "\n";
        $render .= 'myPlayer.seekButtons({' . "\n";
        $render .= 'forward: 30,' . "\n";
        $render .= 'back: 10' . "\n";
        $render .= '});' . "\n";
        $render .= '</script>';
        return $render;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->render();
    }

    /**
     * @param Zend_View_Interface $view
     */
    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;
    }

    /**
     * @return $this
     */
    public function play()
    {
        $this->_play = true;
        return $this;
    }
}