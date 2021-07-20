<?php
class AdminManager {
	public static function activeUsers($paginate = false) {
		return Recordset::query("SELECT
			u.id
		FROM
			users u
		WHERE
			u.active = 1  AND
			u.removed = 0 AND
			u.id IN (SELECT p.user_id FROM players p) AND
			u.id NOT IN (SELECT
				b.user_id
			FROM
				banishments b
			WHERE
				b.type = 'user' AND
				(NOW() BETWEEN b.created_at AND b.finishes_at)
			)")->result();
	}

	public static function inactiveUsers($paginate = false) {
		return Recordset::query("SELECT
			u.id
		FROM
			users u
		WHERE
			(
				u.active = 0 OR
				u.id NOT IN (SELECT p.user_id FROM players p)
			) AND
			u.removed = 0 AND
			u.id NOT IN (SELECT
				b.user_id
			FROM
				banishments b
			WHERE
				b.type = 'user' AND
				(NOW() BETWEEN b.created_at AND b.finishes_at)
			)")->result();
	}

	public static function bannedUsers($paginate = false) {
		return Recordset::query("SELECT
			u.id
		FROM
			users u
		WHERE
			u.removed = 0 AND
			u.id IN (SELECT
				b.user_id
			FROM
				banishments b
			WHERE
				b.type = 'user' AND
				(NOW() BETWEEN b.created_at AND b.finishes_at)
			)")->result();
	}
}
