<?php

require_once dirname(__FILE__) . '/../db/AggregatorDBClient.php';
require_once dirname(__FILE__) . '/../social/CrossPost.php';

/**
 * User: ortemij
 * Date: 07.05.12
 * Time: 15:13
 */
class SocialPhoto {

	private $id;
	private $socialPostId;
	private $socialWebAuthorId;
	private $urls = array();

	private function __construct($id, $data = null) {
		if ($data == null) {
			$iterator = AggregatorDBClient::getPhotoById($id);
			if ($iterator->valid()) {
				$data = $iterator->current();
			}
		}

		$this->id = 				$data['id'];
		$this->socialPostId = 		$data['agg_post_id'];
		$this->socialWebAuthorId =  $data['owner_id'];

		if ($data['urls'][0] == '{') {
			$json = json_decode($data['urls']);

			$this->urls = array(
				Photo::SIZE_MINI => $json->src,
				Photo::SIZE_MIDDLE => $json->src_big
			);
			if ($json->src_xxbig) {
				$this->urls[Photo::SIZE_HQ] = $json->src_xxbig;
			} else if ($json->src_xbig) {
				$this->urls[Photo::SIZE_HQ] = $json->src_xbig;
			}
		} else {
			$this->urls = array(
				Photo::SIZE_MIDDLE => $data['urls']
			);
		}

	}

	public function getUrls() {
		return $this->urls;
	}

	public function getId() {
		return $this->id;
	}

	public static function getBySocialPost(SocialPost $post) {
		$it = AggregatorDBClient::getPhotoByPostId($post->getId());
		$photos = array();
		while ($it->valid()) {
			$data = $it->current();
			$photos[] = new SocialPhoto(-1, $data);
			$it->next();
		}
		return $photos;
	}
}
