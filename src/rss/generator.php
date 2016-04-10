<?php

namespace NextCellent\RSS;

use NextCellent\Models\Image;
use NextCellent\RSS\Writer\Channel\Channel;
use NextCellent\RSS\Writer\Feed;
use NextCellent\RSS\Writer\Item;
use NextCellent\RSS\Writer\Media\MediaContent;
use NextCellent\RSS\Writer\Media\Optional\MediaDescription;
use NextCellent\RSS\Writer\Media\Optional\MediaThumbnail;
use NextCellent\RSS\Writer\Media\Optional\MediaTitle;

/**
 * A class to generate the media RSS feed for NextCellent.
 *
 * This class uses a modified version of the awesome RSSWriter library by Suin (version 1.4.0).
 * In the future we hope to release our modifications as a fork, or pull request.
 *
 * Due to the way WordPress works (or rather doesn't), we have changed the namespace of this package
 * to make it compatible with our custom autoloader and prevent conflicts with other plugins.
 *
 * @see https://github.com/suin/php-rss-writer
 */
class Generator {
	
	const ENDPOINT = 'feed';

	/**
	 * @return string The URL to the media feed.
	 */
	public static function get_feed_base_url() {
		return network_site_url(\NCG::ENDPOINT . '/' . self::ENDPOINT);
	}

	public static function get_feed_image_url() {
		return self::get_feed_base_url() . '/image';
	}

	/**
	 * @param $feed
	 * @param $args
	 *
	 * @return bool If the feed was successfully made or not.
	 */
	public static function doFeed($feed, $args)
	{
		switch ($feed) {
			case 'image':
			case '':
				$feed = self::imageRSS();
				break;
			default:
				return false;
		}

		header("content-type:text/xml;charset=utf-8");

		echo $feed->render();
		
		return true;
	}

	/**
	 * Display the RSS feed for the latest images.
	 */
	protected static function imageRSS() {
		$feed = new Feed();
		$feed->addNamespace(Feed::NS_MEDIA);
		
		$channel = new Channel();
		$channel->title(get_bloginfo('name'))
			->link(network_home_url())
			->description(sprintf(__('The latest images of %s', 'nextcellent'), get_bloginfo('name')))
			->language(get_bloginfo('language'))
			->generator('NextCellent ' . \NCG::VERSION)
			->appendTo($feed);

		$images = Image::all(Image::ID, 'DESC', 0, 50);

		foreach ($images as $image) {
			$item = new Item();
			$item->title($image->alt_text)
			     ->description($image->description)
			     ->link($image->url)
			     ->guid('image-id:' . $image->id)
			     ->appendTo($channel);

			$mediaContent = new MediaContent();
			$mediaContent->url($image->url)
			             ->medium('image')
			             ->appendToItem($item);

			$mediaTitle = new MediaTitle($image->alt_text);
			$mediaTitle->appendToMediaContent($mediaContent);

			$mediaDescription = new MediaDescription($image->description);
			$mediaDescription->appendToMediaContent($mediaContent);

			$mediaThumbnail = new MediaThumbnail();
			$mediaThumbnail->url($image->thumb_url)
			               ->appendToMediaContent($mediaContent);
		}
		
		return $feed;
	}

}