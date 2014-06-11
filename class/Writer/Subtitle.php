<?php
namespace Writer;

interface SubWriter
{
    /**
     * @param array $caps array of \Reader\SubtitleCaption
     */
    function renderLocal(array $caps);
}

class Subtitle
{
    var $caps;

    public function __construct($caps = array())
    {
        $this->setCaps($caps);
    }

    public function setCaps($caps)
    {
        if (!$caps)
            return;

        $this->caps = $caps;
    }

    /**
     * Renders captions using specified subtitle writer
     *
     * @param array $caps array of \Reader\SubtitleCaption
     * @param \Writer\Subtitle $sub Subtitle writer class
     * @return string
     */
    public static function render(array $caps, \Writer\Subtitle $sub)
    {
        return $sub->renderLocal($caps);
    }
}