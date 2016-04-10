<?php

namespace NextCellent\RSS\Writer\Media\Optional;

/**
 * This is the hash of the binary media file. It can appear multiple times as
 * long as each instance is a different algo. It has one optional attribute.
 *
 * @author Niko Strijbol
 */
class MediaHash extends MediaElement
{
    /**
     * MediaCategory constructor.
     *
     * @param string $hash
     */
    public function __construct($hash)
    {
        parent::__construct('media:hash', $hash);
    }

    /**
     * Indicates the algorithm used to create the hash. Possible values are "md5"
     * and "sha-1". Default value is "md5". It is an optional attribute.
     *
     * @param string $algo
     *
     * @return $this
     */
    public function algo($algo)
    {
        $this->attributes['algo'] = $algo;

        return $this;
    }
}