<?php

/**
 *
 * @author ortemij
 */
interface ISocialWeb {
	/**
	 * authorization using this social web service failed
	 */
	const FAIL = 0;

	/**
	 * authorization succeded but user is not registered on the site
	 */
    const SUCCESS = 1;

	/**
	 * authorization succeded and user already has an account on the site
	 */
	const FULL_SUCCESS = 2;

	const VKONTAKTE = 'vk';
	const FACEBOOK = 'fb';
	const TWITTER = 'tw';

	/**
	 * @return integer authorization status (see constants)
	 */
	public function login();

	/**
	 * @return ID of user in social web (name, mid, ...)
	 */
	public function getId();
}
?>
