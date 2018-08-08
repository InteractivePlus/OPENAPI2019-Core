<?php
namespace BoostPHP{
	require_once __DIR__ . '/internal/BoostPHP.internal.php';
	class MySQL{
		/**
		 * Connect to a database using mysqli
		 * returns false on failure
		 * @param string Username for mysql database
		 * @param string Password for mysql database
		 * @param string Database Name for mysql database
		 * @param string Hostname for mysql database
		 * @param int Port Number for mysql database
		 * @access public
		 * @return MySQLi Connection
		 */
		public static function connectDB($Username, $Password, $Database, $Host = "127.0.0.1", $Port = 3306){
			$MySQLiConn = mysqli_connect($Host, $Username, $Password, $Database, $Port);
			return $MySQLiConn;
		}
		
		/**
		 * Query an SQL Statement
		 * returns false on failure
		 * @param MySQLi Connection Data
		 * @param string The statement you want to query
		 * @access public
		 * @return bool
		 */
		public static function querySQL($MySQLiConn, $SQLStatement){
			$SelectRST = mysqli_query($MySQLiConn, $SQLStatement);
			if(!$SelectRST){
				return false;
			}else{
				return true;
			}
		}

		/**
		 * Select datas from the Database
		 * returns array that has a count of 0 on failure
		 * @param MySQLi Connection Data
		 * @param string The statement you want to query for selection, need to be prevented from SQL Injection
		 * @access public
		 * @return array
		 * @returnKey count[int] - how many results can be shown
		 * @returnKey result[array] - the result of the selection(only when count > 0)
		 */
		public static function selectIntoArray_FromStatement($MySQLiConn, $SelectStatement){
			$SelectRST = mysqli_query($MySQLiConn, $SelectStatement);
			
			$ResultArr = array('count' => 0);
			if(!$SelectRST){
				return $ResultArr;
			}
			$Selectcount = mysqli_num_rows($SelectRST);
			$ResultArr['count'] = $Selectcount;
			$ResultArr['result'] = array();
			/* Old(PHP<5.3)
			$SelectTempArr = array();
			if($Selectcount>0){
				for($xh = 0; $xh < $Selectcount; $xh++){
					$SelectTempArr = mysqli_fetch_array($SelectRST);
					$ResultArr['result'][] = $SelectTempArr;
				}
			}
			*/
			if($Selectcount>0){
				$ResultArr['result'] = mysqli_fetch_all($SelectRST,MYSQLI_ASSOC);
			}
			mysqli_free_result($SelectRST);
			return $ResultArr;
		}
		
		/**
		 * Select datas from the Database
		 * returns 0 on failure
		 * @param MySQLi Connection Data
		 * @param string Table name,  need to be prevented from SQL Injection
		 * @param array The array that requirements should fit, should be like array(Key=>Value, Key1=>Value1)
		 * @param array The array of keys to be the key for ordering
		 * @param int The limit number you want to select, -1 means to select all
		 * @param int the offset you want to start with, by default it is 0, which means from the start.
		 * @access public
		 * @return array
		 * @returnKey count[int] - how many results can be shown
		 * @returnKey result[array] - the result of the selection(only when count > 0)
		 */
		public static function selectIntoArray_FromRequirements($MySQLiConn, $Table, $SelectRequirement = array(), $OrderByArray = array(), $NumLimit = -1, $OffsetNum = 0){
			$SelectState = "SELECT * FROM " . $Table;
			
			if(!empty($SelectRequirement)){
				$SelectXH = 0;
				$SelectState .= " WHERE ";
				foreach($SelectRequirement as $BLName=>$BLValue){
					if($SelectXH == 0){
						$SelectState .= mysqli_real_escape_string($MySQLiConn,$BLName) . " = '" . mysqli_real_escape_string($MySQLiConn,$BLValue) . "'";
					}else{
						$SelectState .= " AND " . mysqli_real_escape_string($MySQLiConn,$BLName) . " = '" . mysqli_real_escape_string($MySQLiConn,$BLValue) . "'";
					}
					$SelectXH++;
				}
			}
			if(!empty($OrderByArray)){
				$SelectXH = 0;
				$SelectState .= " ORDER BY ";
				foreach($OrderByArray as $BLName){
					if($SelectXH == 0){
						$SelectState .= mysqli_real_escape_string($MySQLiConn, $BLName);
					}else{
						$SelectState .= ", " . mysqli_real_escape_string($MySQLiConn, $BLName);
					}
					$SelectXH++;
				}
			}
			if($NumLimit != -1){
				$SelectState .= " LIMIT " . $NumLimit;
			}
			if($OffsetNum > 0){
				$SelectState .= " OFFSET " . $OffsetNum;
			}
			$MRST=mysqli_query($MySQLiConn,$SelectState);
			if(!$MRST){
				return 0;
			}
			$Selectcount = mysqli_num_rows($MRST);
			$ResultArr['count'] = $Selectcount;
			$ResultArr['result'] = array();
			/* Old (<PHP 5.3)
			$SelectTempArr = array();
			if($Selectcount>0){
				for($xh = 0; $xh < $Selectcount; $xh++){
					$SelectTempArr = mysqli_fetch_array($MRST);
					$ResultArr['result'][] = $SelectTempArr;
				}
			}
			*/
			if($Selectcount>0){
				$ResultArr['result'] = mysqli_fetch_all($MRST,MYSQLI_ASSOC);
			}
			mysqli_free_result($MRST);
			return $ResultArr;
		}

		/**
		 * Check data exists that fits requirements from the Database
		 * returns 0 on failure
		 * @param MySQLi Connection Data
		 * @param string The table you want to query for selection, need to be prevented from SQL Injection
		 * @param array The array that requirements should fit, should be like array(Key=>Value, Key1=>Value1)
		 * @access public
		 * @return int - how many results can be shown
		 */
		public static function checkExist($MySQLiConn, $Table, $SelectRequirement){
			$SelectState = "SELECT COUNT(*) FROM " . $Table;
			if(!empty($SelectRequirement)){
				$SelectXH = 0;
				$SelectState .= " WHERE ";
				foreach($SelectRequirement as $BLName=>$BLValue){
					if($SelectXH == 0){
						$SelectState .= mysqli_real_escape_string($MySQLiConn,$BLName) . " = '" . mysqli_real_escape_string($MySQLiConn,$BLValue) . "'";
					}else{
						$SelectState .= " AND " . mysqli_real_escape_string($MySQLiConn,$BLName) . " = '" . mysqli_real_escape_string($MySQLiConn,$BLValue) . "'";
					}
					$SelectXH++;
				}
			}
			$MRST=mysqli_query($MySQLiConn,$SelectState);
			if(!$MRST){
				return 0;
			}
			$MyArr = mysqli_fetch_array($MRST);
			$MyRSTNum = $MyArr['COUNT(*)'];
			mysqli_free_result($MRST);
			return $MyRSTNum;
		}

		/**
		 * Insert data into the DB
		 * returns false on failure
		 * @param MySQLi Connection Data
		 * @param string The table you want to query for selection, need to be prevented from SQL Injection
		 * @param array The array that you want the insert value to be, should be like array(Key=>Value, Key1=>Value1)
		 * @access public
		 * @return bool - true if successful
		 */
		public static function insertRow($MySQLiConn, $Table, $InsertArray){
			if(empty($InsertArray)){
				return false;
			}
			$InsertStatement = "INSERT INTO " . $Table . " ";
			$NameState = "(";
			$ValueState = "(";
			$InsertXH = 0;
			foreach ($InsertArray as $BLName=>$BLValue){
				if($InsertXH == 0){
					$NameState .= mysqli_real_escape_string($MySQLiConn,$BLName);
					$ValueState .= "'" . mysqli_real_escape_string($MySQLiConn, $BLValue) . "'";
				}else{
					$NameState .= ', ' . mysqli_real_escape_string($MySQLiConn,$BLName);
					$ValueState .= ", '" . mysqli_real_escape_string($MySQLiConn,$BLValue) . "'";
				}
				$InsertXH++;
			}
			$NameState .= ")";
			$ValueState .= ")";
			$InsertStatement .= $NameState . " VALUES " . $ValueState;
			$InsertRST = mysqli_query($MySQLiConn,$InsertStatement);
			return ((!$InsertRST) ? false : true);
		}
		
		/**
		 * Update the Table of the MYSQL DB
		 * returns false on failure
		 * @param MySQLi Connection Data
		 * @param string The table you want to query for selection, need to be prevented from SQL Injection
		 * @param array The array that you want to update your value, like array(Key=>Value, Key1=>Value1)
		 * @param array The array that requirements should fit, should be like array(Key=>Value, Key1=>Value1)
		 * @access public
		 * @return bool - if succeed, return true.
		 */
		public static function updateRows($MySQLiConn, $Table, $UpdateArray, $SelectRequirement){
			if(empty($UpdateArray)){
				return false;
			}
			$UpdateState = "UPDATE " . $Table . " SET ";
			$UpdateXH = 0;
			foreach($UpdateArray as $BLName=>$BLValue){
				if($UpdateXH != (count($UpdateArray) - 1)){
					$UpdateState .= mysqli_real_escape_string($MySQLiConn,$BLName) . " = '" . mysqli_real_escape_string($MySQLiConn, $BLValue) . "', ";
				}else{
					$UpdateState .= mysqli_real_escape_string($MySQLiConn,$BLName) . " = '" . mysqli_real_escape_string($MySQLiConn, $BLValue) . "' ";
				}
				$UpdateXH++;
			}
			if(!empty($SelectRequirement)){
				$UpdateXH = 0;
				$UpdateState .= "WHERE ";
				foreach($SelectRequirement as $BLName=>$BLValue){
					if($UpdateXH == 0){
						$UpdateState .= mysqli_real_escape_string($MySQLiConn, $BLName) . " = '" . mysqli_real_escape_string($MySQLiConn, $BLValue) . "'";
					}else{
						$UpdateState .= " AND " . mysqli_real_escape_string($MySQLiConn, $BLName) . " = '" . mysqli_real_escape_string($MySQLiConn, $BLValue) . "'";
					}
					$UpdateXH++;
				}
			}
			$UpdateRST = mysqli_query($MySQLiConn,$UpdateState);
			return ((!$UpdateRST) ? false : true);
		}

		/**
		 * Delete Rows from MYSQL DB
		 * returns false on failure
		 * @param MySQLi Connection Data
		 * @param string The table you want to query for selection, need to be prevented from SQL Injection
		 * @param array The array that requirements should fit, should be like array(Key=>Value, Key1=>Value1)
		 * If the third param is empty, it will clear the entire table.
		 * @access public
		 * @return bool - if succeed, return true.
		 */
		public static function deleteRows($MySQLiConn, $Table, $SelectRequirement){
			$DeleteStatement = "DELETE FROM " . $Table;
			if(!empty($SelectRequirement)){
				$DeleteStatement .= " WHERE ";
				$DeleteXH = 0;
				foreach($SelectRequirement as $BLName=>$BLValue){
					if($DeleteXH == 0){
						$DeleteStatement .= mysqli_real_escape_string($MySQLiConn, $BLName) . " = '" . mysqli_real_escape_string($MySQLiConn, $BLValue) . "'";
					}else{
						$DeleteStatement .= " AND " . mysqli_real_escape_string($MySQLiConn, $BLName) . " = '" . mysqli_real_escape_string($MySQLiConn, $BLValue) . "'";
					}
					$DeleteXH++;
				}
			}
			$DeleteRST = mysqli_query($MySQLiConn,$DeleteStatement);
			return ((!$DeleteRST) ? false : true);
		}

		/**
		 * Close a MySQL Connection
		 * @param MySQLi Connection Data
		 * @return void
		 */
		public static function closeConn($MySQLiConn){
			mysqli_close($MySQLiConn);
		}
	}
}