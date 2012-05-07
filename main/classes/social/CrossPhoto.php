<?php
/**
 * User: ortemij
 * Date: 07.05.12
 * Time: 16:28
 */
class CrossPhoto extends Photo {

	public static function create(SocialPhoto $socialPhoto, CrossPost $post) {
		$urls = $socialPhoto->getUrls();
		$title = $post->getSocialWebAuthorName();
		return Item::create(Item::PHOTO, CONTENT_SOCIAL_PHOTO_ALBUM_ID, CONTENT_SOCIAL_PHOTO_AUTHOR_ID, time(), serialize($urls), $title, "", $post->getId());
	}
}
