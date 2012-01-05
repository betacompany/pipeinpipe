<?php
/**
 * @author Artyom Grigoriev
 */
class ConnectionDBClient {
    public static function getById($id) {
		return new MySQLResultIterator(
			mysql_qw(
				'SELECT * FROM `p_content_connection` WHERE `id`=?',
				$id
			)
		);
	}

	public static function getByContent($contentType, $contentId) {
		return new MySQLResultIterator(
			mysql_qw(
				'SELECT * FROM `p_content_connection` WHERE `content_type`=? AND `content_id`=?',
				$contentType, $contentId
			)
		);
	}

	public static function getByHolder($holderType, $holderId) {
		return new MySQLResultIterator(
			mysql_qw(
				'SELECT * FROM `p_content_connection` WHERE `holder_type`=? AND `holder_id`=?',
				$holderType, $holderId
			)
		);
	}

	public static function insert($holderType, $holderId, $contentType, $contentId) {
		mysql_qw(
			'INSERT INTO
				`p_content_connection`
			SET
				`holder_type`=?,
				`holder_id`=?,
				`content_type`=?,
				`content_id`=?',
				
			$holderType,
			$holderId,
			$contentType,
			$contentId
		);

		return mysql_insert_id();
	}

	public static function getTypifiedContentGroups($holderType, $holderId, $contentTypeExtended) {
		
		return new MySQLResultIterator(
			mysql_qw(
				'SELECT * FROM
						(
							SELECT
								`content_id`
							FROM
								`p_content_connection`
							WHERE
								`holder_type`=? AND `holder_id`=? AND `content_type`=\'group\'
						) `connection`
					INNER JOIN
						`p_content_group` AS `content`
					ON
						`connection`.`content_id`=`content`.`id`
				WHERE
					`type`=?
				',
					$holderType, $holderId, $contentTypeExtended
			)
		);
	}

	public static function getTypifiedContentItemsRecursive($holderType, $holderId, $contentTypeExtended) {

		return new MySQLResultIterator(
			mysql_qw(
				'
					SELECT * FROM
					(
						(
							SELECT * FROM
								(
									SELECT
										`content_id`
									FROM
										`p_content_connection`
									WHERE
										`holder_type`=? AND `holder_id`=? AND `content_type`=\'item\'
								) AS `connection`
							INNER JOIN
								`p_content_item`
							ON
								`connection`.`content_id`=`p_content_item`.`id`
						)

						UNION

						(
							SELECT * FROM
							(
								SELECT
									`content_id`
								FROM
									`p_content_connection`
								WHERE
									`holder_type`=? AND `holder_id`=? AND `content_type`=\'group\'
							) AS `connection2`
							INNER JOIN
								`p_content_item`
							ON
								`connection2`.`content_id`=`p_content_item`.`group_id`
						)
					) `result`

					WHERE
						`result`.`type`=?
				',
					$holderType, $holderId, $holderType, $holderId, $contentTypeExtended
			)
		);
	}
}
?>
