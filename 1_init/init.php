<?php

/**
 * Class init
 *
 * @todo: Refactor - rename init is awful calss name
 */
final class init
{
	private $connection;
	private $count;
	private $tableName = 'test';
	private $columns = [
		'id' => [
			'sql' => 'INT (11) UNSIGNED',
			'primary' => true,
			'autoincrement' => true,
		],
		'script-name' => [
			'sql' => 'VARCHAR(25)',
		],
		'start_time' => [
			'sql' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
		],
		'sort_index' => [
			'sql' => 'INT(3) UNSIGNED',
		],
		'result' => [
			'sql' => 'ENUM("normal", "illegal", "failed", "success")',
		],
	];

	/**
	 * init constructor.
	 *
	 * Opens connection to database, create table and fill it with demo data
	 *
	 * @param int $count Number of demo data row to create
	 *
	 * @todo: Remove db call/write. Calling db in constructor is bad practice, but writing into db - is totally evil.
	 *
	 */
	public function __construct($count = 100)
	{
		$this->count = $count;
		$this->connection = new PDO('mysql:host=1init_mysql_1;port=3306;dbname=test', 'root', 'root');
		$this->connection->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

		$this->create();
		$this->fill();
	}

	/**
	 * Return data with "success" and "normal" result status
	 *
	 * @return array
	 */
	public function get()
	{
		$query = $this->connection->prepare("SELECT * FROM `{$this->tableName}` WHERE `result` IN ('success', 'normal')");
		$query->execute();
		return $query->fetchAll(PDO::FETCH_ASSOC);
	}

	/**
	 * Creates test table
	 *
	 * In case the table exists - add all fields.
	 * NB: In case table with such name already exists and strongly differ by structure UB is possible.
	 *
	 * return void
	 */
	private function create()
	{
		$tableExists = $this->connection->query("SHOW TABLES LIKE '{$this->tableName}'")->rowCount();
		return !$tableExists ? $this->createTable() : $this->updateTable();
	}

	/**
	 * Fills test table with demo data.
	 *
	 * return void
	 */
	private function fill()
	{
		$this->connection->exec("TRUNCATE TABLE `{$this->tableName}`");
		$availableOptions = [
			'normal', 'illegal', 'failed', 'success',
		];
		$sql = [];
		for($i = 0; $i < $this->count; $i++) {
			$scriptName = $this->randomString(mt_rand(0, 25));
			$time = date('Y-m-d H:i:s', mt_rand(0, time()));
			$sort = mt_rand(0, 999);
			$result = $availableOptions[array_rand($availableOptions)];
			$sql[] = "('{$scriptName}','{$time}', {$sort}, '{$result}')";
		}
		$sql = 'INSERT INTO `test` (`script-name`, `start_time`, `sort_index`, `result`) VALUES ' . implode(',', $sql);
		$this->connection->exec($sql);
	}

	/**
	 * Generates random string with given length
	 *
	 * @param int $length
	 *
	 * @return string
	 */
	private function randomString($length)
	{
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$charactersLength = strlen($characters);
		$randomString = '';
		for ($i = 0; $i < $length; $i++) {
			$randomString .= $characters[rand(0, $charactersLength - 1)];
		}
		return $randomString;
	}

	/**
	 * @return int
	 */
	private function createTable()
	{
		$result = $this->connection->exec("CREATE TABLE `{$this->tableName}` (
			`id` INT (11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
			`script-name` VARCHAR(25),
			`start_time` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
			`sort_index` INT (3) UNSIGNED,
			`result` ENUM('normal', 'illegal', 'failed', 'success')
		);");
		return $result !== false;
	}

	/**
	 * @return bool
	 */
	private function updateTable()
	{
		$query = 'SHOW COLUMNS FROM `' . $this->tableName . '` WHERE `Field` IN ("' . implode('","', array_keys($this->columns)) .'")';
		$columns = array_map(function($c) {
			return $c['Field'];
		}, $this->connection->query($query)->fetchAll(PDO::FETCH_ASSOC));
		$notExistedColumns = array_diff(array_keys($this->columns), $columns);

		$sql = [];
		$modify = [];
		foreach ($notExistedColumns as $columnName) {
			$columnData = $this->columns[$columnName];
			$sql[] = "ADD `{$columnName}` {$columnData['sql']}";
			if (!empty($columnData['autoincrement'])) {
				$columnData['primary'] = true;
				$modify[] = "MODIFY COLUMN `{$columnName}` {$columnData['sql']} AUTO_INCREMENT";
			}
			if (!empty($columnData['primary'])) {
				$sql[] = "ADD PRIMARY KEY(`{$columnName}`)";
			}
		}

		$result = true;
		if (!empty($sql)) {
			$status = $this->connection->exec("ALTER TABLE `$this->tableName` " . implode(',', $sql));
			$result = $result && ($status !== false);
		}

		if (!empty($modify)) {
			$status = $this->connection->exec("ALTER TABLE `{$this->tableName}` " . implode(',', $modify));
			$result = $result && ($status !== false);
		}
		return $result;
	}

}
