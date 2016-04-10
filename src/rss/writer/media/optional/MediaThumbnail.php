<?php

namespace NextCellent\RSS\Writer\Media\Optional;

/**
 * Allows particular images to be used as representative images for the media object.
 * If multiple thumbnails are included, and time coding is not at play, it is assumed
 * that the images are in order of importance. It has one required attribute and
 * three optional attributes.
 *
 * @author  Niko Strijbol
 */
class MediaThumbnail extends MediaElement
{
    public function __construct()
    {
        parent::__construct('media:thumbnail');
    }

    /**
     * Specifies the url of the thumbnail. It is a required attribute.
     *
     * @param string $url
     *
     * @return $this
     */
    public function url($url)
    {
        $this->attributes['url'] = $url;
        return $this;
    }

    /**
     * Specifies the height of the thumbnail. It is an optional attribute.
     *
     * @param int $height
     *
     * @return $this
     */
    public function height($height)
    {
        $this->attributes['height'] = $height;
        return $this;
    }

    /**
     * Specifies the width of the thumbnail. It is an optional attribute.
     *
     * @param int $width
     *
     * @return $this
     */
    public function width($width)
    {
        $this->attributes['width'] = $width;
        return $this;
    }

    /**
     * Specifies the time offset in relation to the media object. Typically
     * this is used when creating multiple keyframes within a single video.
     * The format for this attribute should be in the DSM-CC's Normal Play Time (NTP),
     * as used in RTSP [RFC 2326 3.6 Normal Play Time]. It is an optional attribute.
     * 
     * @see RFC 2326, 3.6
     * 
     * @param string $time
     *
     * @return $this
     */
    public function time($time)
    {
        $this->attributes['time'] = $time;
        return $this;
    }
}