<?php

namespace NextCellent\RSS\Writer\Channel;

use NextCellent\RSS\Writer\SimpleXMLElement;

/**
 * Image element for a channel.
 * 
 * @author  Niko Strijbol
 */
class Image extends SimpleXMLElement
{
    /**
     * Image constructor.
     */
    public function __construct()
    {
        parent::__construct('image');
    }

    /**
     * Set the url to the image.
     * 
     * @param string $url
     *
     * @return $this
     */
    public function url($url)
    {
        $this->simpleChild('url', $url);
        return $this;
    }

    /**
     * Describes the image, like the ALT tag on a HTML image.
     * 
     * In practice this should have the same value as the channel's title.
     * 
     * @param string $title
     *
     * @return $this
     */
    public function title($title)
    {
        $this->simpleChild('title', $title, true);
        return $this;
    }

    /**
     * The URL of the site. Is used to make the image clickable when the channel is rendered.
     * 
     * In practice this should have the same value as the channel's link.
     * 
     * @param string $link
     *
     * @return $this
     */
    public function link($link)
    {
        $this->simpleChild('link', $link);
        return $this;
    }

    /**
     * The width of the image.
     * 
     * @param int $width The maximum value is 144. RSS will use 88 by default.
     *
     * @return $this
     */
    public function width($width)
    {
        $this->simpleChild('width', $width);
        
        return $this;
    }

    /**
     * The height of the image.
     *
     * @param int $height The maximum value is 400. RSS will use 31 by default.
     *
     * @return $this
     */
    public function height($height)
    {
        $this->simpleChild('height', $height);

        return $this;
    }

    /**
     * The description of the image. This value can be used to populate the title
     * attribute of the link around the image when rendered in HTML.
     *
     * @param string $description
     *
     * @return $this
     */
    public function description($description)
    {
        $this->simpleChild('description', $description);

        return $this;
    }

    /**
     * Append to channel.
     *
     * @param Channel $channel
     *
     * @return $this
     */
    public function appendTo(Channel $channel)
    {
        $channel->image($this);
        return $this;
    }
}