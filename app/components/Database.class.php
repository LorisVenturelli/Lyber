<?php
/**
 *  DB - A simple database class
 *
 * @author		Author: Vivek Wicky Aswal. (https://twitter.com/#!/VivekWickyAswal)
 * @git 		https://github.com/indieteq/PHP-MySQL-PDO-Database-Class
 * @version      0.2ab
 *
 */

class Database
{
	# @object, The PDO object
    private static $pdo;

	# @object, PDO statement object
    private static $sQuery;

	# @array,  The database settings
    private static $settings;

	# @bool ,  Connected to the database
    private static $bConnected = false;

	# @array, The parameters of the SQL query
	private static $parameters = array();


       /**
	*	This method makes connection to the database.
	*
	*	1. Reads the database settings from a ini file.
	*	2. Puts  the ini content into the settings array.
	*	3. Tries to connect to the database.
	*	4. If connection failed, exception is displayed and a log file gets created.
	*/
		private static function Connect()
		{
			$dsn = 'mysql:dbname='.Config::get("bdd","dbname").';host='.Config::get("bdd","host");
			try
			{
				# Read settings from INI file, set UTF8
				self::$pdo = new PDO($dsn, Config::get("bdd","user"), Config::get("bdd","password"), array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));

				# We can now log any exceptions on Fatal error.
                self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

				# Disable emulation of prepared statements, use REAL prepared statements instead.
                self::$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

				# Connection succeeded, set the boolean to true.
                self::$bConnected = true;
			}
			catch (PDOException $e)
			{
				# Write into log
				self::ExceptionLog($e->getMessage());
				die();
			}
		}
	/*
	 *   You can use this little method if you want to close the PDO connection
	 *
	 */
        public static function CloseConnection()
	 	{
	 		# Set the PDO object to null to close the connection
	 		# http://www.php.net/manual/en/pdo.connections.php
	 		self::$pdo = null;
	 	}

       /**
	*	Every method which needs to execute a SQL query uses this method.
	*
	*	1. If not connected, connect to the database.
	*	2. Prepare Query.
	*	3. Parameterize Query.
	*	4. Execute Query.
	*	5. On exception : Write Exception into the log + SQL query.
	*	6. Reset the Parameters.
	*/
		private static function Init($query,$parameters = "")
		{
            # Connect to database
            if(!self::$bConnected) { self::Connect(); }
            try {
				# Prepare query
                self::$sQuery = self::$pdo->prepare($query);

				# Add parameters to the parameter array
                self::bindMore($parameters);

				# Bind parameters
				if(!empty(self::$parameters)) {
					foreach(self::$parameters as $param)
					{
						$parameters = explode("\x7F",$param);
						self::$sQuery->bindParam($parameters[0],$parameters[1]);
					}
				}

				# Execute SQL
				$succes = self::$sQuery->execute();
			}
			catch(PDOException $e)
			{
                # Write into log and display Exception
                echo self::ExceptionLog($e->getMessage(), $query);
                //die();
			}

			# Reset the parameters
			self::$parameters = array();
		}

       /**
	*	@void
	*
	*	Add the parameter to the parameter array
	*	@param string $para
	*	@param string $value
	*/
		public static function bind($para, $value)
		{
			self::$parameters[sizeof(self::$parameters)] = ":" . $para . "\x7F" . utf8_encode($value);
		}
       /**
	*	@void
	*
	*	Add more parameters to the parameter array
	*	@param array $parray
	*/
		public static function bindMore($parray)
		{
			if(empty(self::$parameters) && is_array($parray)) {
				$columns = array_keys($parray);
				foreach($columns as $i => &$column)	{
					self::bind($column, $parray[$column]);
				}
			}
		}
       /**
	*   	If the SQL query  contains a SELECT or SHOW statement it returns an array containing all of the result set row
	*	If the SQL statement is a DELETE, INSERT, or UPDATE statement it returns the number of affected rows
	*
	*   	@param  string $query
	*	@param  array  $params
	*	@param  int    $fetchmode
	*	@return mixed
	*/
		public static function query($query,$params = null, $fetchmode = PDO::FETCH_ASSOC)
		{
			$query = trim($query);

			self::Init($query,$params);

			$rawStatement = explode(" ", $query);

			# Which SQL statement is used
			$statement = strtolower($rawStatement[0]);

			if ($statement === 'select' || $statement === 'show') {
				return self::$sQuery->fetchAll($fetchmode);
			}
			elseif ( $statement === 'insert' ||  $statement === 'update' || $statement === 'delete' ) {
				return self::$sQuery->rowCount();
			}
			else {
				return NULL;
			}
		}

      /**
       *  Returns the last inserted id.
       *  @return string
       */
		public static function lastInsertId() {
			return self::$pdo->lastInsertId();
		}

       /**
	*	Returns an array which represents a column from the result set
	*
	*	@param  string $query
	*	@param  array  $params
	*	@return array
	*/
		public static function column($query,$params = null)
		{
			self::Init($query,$params);
			$Columns = self::$sQuery->fetchAll(PDO::FETCH_NUM);

			$column = null;

			foreach($Columns as $cells) {
				$column[] = $cells[0];
			}

			return $column;

		}
       /**
	*	Returns an array which represents a row from the result set
	*
	*	@param  string $query
	*	@param  array  $params
	*   	@param  int    $fetchmode
	*	@return array
	*/
    public static function row($query,$params = null,$fetchmode = PDO::FETCH_ASSOC)
    {
        self::Init($query,$params);
        return self::$sQuery->fetch($fetchmode);
    }
       /**
	*	Returns the value of one single field/column
	*
	*	@param  string $query
	*	@param  array  $params
	*	@return string
	*/
    public static function single($query,$params = null)
    {
        self::Init($query,$params);
        return self::$sQuery->fetchColumn();
    }
       /**
	* Writes the log and returns the exception
	*
	* @param  string $message
	* @return string
	*/
	private function ExceptionLog($message)
	{
		Log::addError($message);
	}
}
