<?php
/***********************************************************************
| Cerberus Helpdesk(tm) developed by WebGroup Media, LLC.
|-----------------------------------------------------------------------
| All source code & content (c) Copyright 2005, WebGroup Media LLC
|   unless specifically noted otherwise.
|
| This source code is released under the Cerberus Public License.
| The latest version of this license can be found here:
| http://www.cerberusweb.com/license.php
|
| By using this software, you acknowledge having read this license
| and agree to be bound thereby.
|
| Developers involved with this file:
|		Jeff Standen		(jeff@webgroupmedia.com)		[JAS]
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once(FILESYSTEM_PATH . "cerberus-api/database/cer_Database.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/security/CerSecurityUtils.class.php");

define("ENTITY_LOG_PREFIX", "[CERBERUS ENTITY]: ");

/* abstract */
class CerEntityObjectController {
	var $_columns = array();
	var $_entityClass = null;
	var $_tableName = null;

	function CerEntityObjectController($tableName,$entityClass) {
		$this->setTableName($tableName);
		$this->setEntityClass($entityClass);
	}
	
	function _addColumn($localName,$dbName,$type) {
		$localName = ucfirst($localName);
		
		$column = new CerEntityObjectColumn(
			$localName,
			$dbName,
			$type
		);
		
		$this->_columns[$localName] = $column;
	}
	
	/*
	 * [JAS]: Return a list of arbitrary entity objects
	 * [TODO]: We should be able to pass a list of contraints too
	 */
	function getList($orderByCol="",$orderByDir="ASC") {
		/* @var $db cer_Database */
		/* @var $sortCol CerEntityObjectColumn */

		// [JAS]: [TODO] We should probably be testing for the existence of getId() on the $obj
		$db = cer_Database::getInstance();
		$tableName = $this->getTableName();
		$columns = $this->getColumns();
		$sqlOrderBy = "";

		if(empty($tableName) || empty($columns))
			return null;
		
		if(!empty($orderByCol)) {
			$sortCol = $columns[ucfirst($orderByCol)];
			$sqlOrderBy = sprintf("ORDER BY `%s`.`%s` %s",
				$tableName,
				$sortCol->getDbName(),
				$orderByDir
			);
		}
		$sql = $this->getBaseSelectSql() . $sqlOrderBy;
		$res = $db->query($sql);
		$objs = $this->_getObjectsFromResult($res);
		
		return $objs;
	}

	function getListByKeyword($keyword,$searchCol,$orderByCol="",$orderByDir="ASC") {
		// [JAS]: [TODO] We should probably be testing for the existence of getId() on the $obj
		$db = cer_Database::getInstance();
		$tableName = $this->getTableName();
		$columns = $this->getColumns();
		$sqlOrderBy = "";
		$sqlWhere = "";

		if(empty($tableName) || empty($columns))
			return null;
		
		if(!empty($keyword) && !empty($searchCol)) {
			$findCol = $columns[ucfirst($searchCol)];
			$sqlWhere = sprintf("WHERE `%s`.`%s` LIKE %s ",
				$tableName,
				$findCol->getDbName(),
				$db->escape('%' . $keyword . '%')
			);
		}
			
		if(!empty($orderByCol)) {
			$sortCol = $columns[ucfirst($orderByCol)];
			$sqlOrderBy = sprintf("ORDER BY `%s`.`%s` %s",
				$tableName,
				$sortCol->getDbName(),
				$orderByDir
			);
		}
		$sql = $this->getBaseSelectSql() . $sqlWhere . ' ' . $sqlOrderBy;
		$res = $db->query($sql);
		$objs = $this->_getObjectsFromResult($res);
		
		return $objs;
	}
	
	/*
	 * [JAS]: Return a single arbitrary entity object by Id
	 */
	function getById($id) {
		/* @var $db cer_Database */
		/* @var $idCol CerEntityObjectColumn */
		
		$db = cer_Database::getInstance();
		
		$tableName = $this->getTableName();
		$columns = $this->getColumns();
		$idCol = $columns["Id"];

		if(empty($tableName) || empty($columns) || empty($idCol))
			return null;	
		
		$sql = sprintf($this->getBaseSelectSql() . 
			"WHERE `%s`.`%s` = %d ",
				$tableName,
				$idCol->getDbName(),
				$id
		);
		
		$res = $db->query($sql);
		$objs = $this->_getObjectsFromResult($res);

		if(!is_array($objs)) {
			// [JAS]: [TODO] Should test object type here
			return $objs;
		} else {
			return array_shift($objs); // grab one
		}
	}
	
	function _getObjectsFromResult(&$res) {
		/* @var $db cer_Database */
		
		$db = cer_Database::getInstance();
		$objs = array();
		
		if(empty($res))
			return null;
		
		if($db->num_rows($res)) {
			while($row = $db->fetch_row($res)) {
				$obj = $this->_createObjectFromRow($row);
				$objs[$obj->getId()] = $obj;
			}
		}
		
		return $objs;
	}
	
	/*
	 * [JAS]: Save an arbitrary entity object back to the database, using a diff
	 * on a new copy of the object to determine the changed columns.
	 */
	function save(&$obj) {
		/* @var $db cer_Database */
		/* @var $obj CerEntityObject */
		/* @var $copy CerEntityObject */
		/* @var $idCol CerEntityObjectColumn */
		
		$db = cer_Database::getInstance();
		$className = $this->getEntityClass();
		$tableName = $this->getTableName();
		$columns = $this->getColumns();
		$idCol = $columns["Id"];

		if(null == $obj || empty($obj) || !is_a($obj,$className))
			return null;

		if(empty($tableName) || empty($className))
			return null;

		if(empty($columns) || !is_array($columns) || empty($idCol))
			return null;
			
		/* We're inserting, get an id */	
		if(0 == $obj->getId()) {
			$sql = sprintf("INSERT INTO `%s` () VALUES ()",
					$tableName
				);
			$db->query($sql);
			unset($sql);
			$obj->setId($db->insert_id());
		}
		
		/* If we still don't have an id, abort -- or we'll mass update db below */
		$id = $obj->getId();
		if(empty($id))
			return null;
		
		/* Pull up a copy of the object before we sync to db and compare */
		$copy = $this->getById($id);
		
		$changes = array();

		/* @var $column CerEntityObjectColumn */
		/* @var $db cer_Database */
		foreach($columns as $column) {
			// [JAS]: Don't compare IDs, we shouldn't update them.
			if($column->getLocalName() == "Id")
				continue;
			
			$copyVal = '';
			$objVal = '';
			if(method_exists($copy,$column->getGetter())) {
				$copyVal = call_user_method($column->getGetter(),$copy);
			}
			if(method_exists($obj,$column->getGetter())) {
				$objVal = call_user_method($column->getGetter(),$obj);
			}
			$dbVal = $objVal;
			
			// [JAS]: Any special case handling for the column value by type before going to db.
			if($column->getType() == "string") {
				$dbVal = $db->escape($objVal);
			}
			else if ($column->getType() == "boolean") {
				$dbVal = ($objVal) ? 1 : 0;
			}
			else if($column->getType() == "integer") {
				$dbVal = sprintf("%d", $objVal);
			}
			
			if(0 != strcmp($copyVal,$objVal)) {
				$changes[] = sprintf("`%s` = %s",
					$column->getDbName(),
					$dbVal
				);
			}
		}
		
		if(empty($changes))
			return $id;
		
		$sql = sprintf("UPDATE `%s` SET %s WHERE `%s`.`%s` = %d",
				$tableName,
				implode(', ', $changes),
				$tableName,
				$idCol->getDbName(),
				$obj->getId()
			);
		$db->query($sql);	
		return $obj->getId();	
	}	 
	
	/*
	 * [JAS]: Delete arbitrary objects by single id or array of ids
	 */
	function delete($ids) {
		/* @var $db cer_Database */
		$db = cer_Database::getInstance();
		$tableName = $this->getTableName();
		$columns = $this->getColumns();
		/* @var $idCol CerEntityObjectColumn */
		$idCol = $columns["Id"];
		
		if(!is_array($ids)) $ids = array($ids);

		if(empty($tableName) || empty($ids) || empty($columns) || empty($idCol))
			return null;
		
		CerSecurityUtils::integerArray($ids);
		
		$sql = sprintf("DELETE FROM `%s` ".
			"WHERE `%s` IN (%s)",
				$tableName,
				$idCol->getDbName(),
				implode(',', $ids)
		);
		$db->query($sql);
		
		return $db->affected_rows();
	}
	
	/*
	 * [JAS]: Create an arbitrary entity object from a database row
	 */
	function _createObjectFromRow($row) {
		/* @var $agent CerAgent */
		
		$className = $this->getEntityClass();
		$columns = $this->getColumns();
		
		if(empty($row) || !is_array($row) || empty($className) || empty($columns))
			return null;
		
		$new = new $className;
		
		/* @var $col CerEntityObjectColumn */
		foreach($columns as $idx => $col) {
			$setterName = $col->getSetter();
			$dbColName = $col->getDbName();

			// If we didn't have this db column somehow, skip it.
			if(!isset($row[$dbColName]))
				continue;
			
			$setterValue = $row[$dbColName];
			
			// Stripslashes if it's a string.
			if($col->getType() == "string")
				$setterValue = stripslashes($setterValue);
				
			call_user_func_array(array(&$new, $setterName),array("value"=>$setterValue));
		}
		
		return $new;
	}
	
	/*
	 * [JAS]: [TODO] This needs to validate our object getter/setters and other implementations
	 * before we allow the class to be used.
	 */
	function _validate() {
		/* @var $column CerEntityObjectColumn */
		
		/*
		 * [JAS]: Make sure we've set an entity class
		 */
		 $className = $this->getEntityClass();
		 if(empty($className))
		 	die(ENTITY_LOG_PREFIX . "No entity class set.");

		 $classFile = FILESYSTEM_PATH . "cerberus-api/entity/model/$className.class.php";
		 if(!file_exists($classFile))
		 	die(ENTITY_LOG_PREFIX . "Entity class file '" . $classFile . "' doesn't exist.");
		 
		 require_once($classFile);
		 	
//		 if(class_exists($className))
//		 	die(ENTITY_LOG_PREFIX . "Entity class '" . $className . "' from '" . $classFile . "' doesn't exist.");
		
		/*
		 * [JAS]: Loop through all our columns and make sure we have getter/setters.
		 */
		 $cols = $this->getColumns();
		 foreach($cols as $column) {
		 	if(!$this->_checkGetterSetter($column->getLocalName())) {
		 		die(ENTITY_LOG_PREFIX . "Column '" . $column->getLocalName() . "' does not have getter/setter methods defined.");
		 	}
		 }
	}
	
	function _checkGetterSetter($localName) {
		/*
		 * Try instantiating a copy of our entity template class
		 */
		$className = $this->getEntityClass();
		$class = new $className;
		
		if(null == $className || null == $class || !is_a($class,$this->getEntityClass()))
			die(sprintf(ENTITY_LOG_PREFIX . "Could not instantiate the entity class '%s' for '%s'",
				$this->getEntityClass(),
				$localName
			));
		 
		/*
		 * Make sure we have getters and setters
		 */
		if(!method_exists($class,"get".$localName) 
			|| !method_exists($class,"set".$localName))
				return false;
		
		return true;
	}
	
	function getBaseSelectSql() {
		/* @var $col CerEntityObjectColumn */
		
		$columns = $this->getColumns();
		$tableName = $this->getTableName();
			
		if(empty($tableName) || empty($columns) || !is_array($columns))
			return null;
		
		$sql = "SELECT ";
		
		// Build a selected column list
		$colAry = array();
		foreach($columns as $col) {
			$colAry[] = sprintf("`%s`.`%s`",
				$tableName,
				$col->getDbName()
			);
		}
		$sql .= implode(', ', $colAry);
		unset($colAry);
		
		// Append the table name
		$sql .= sprintf(" FROM `%s` ", $tableName);
		
		return $sql;
	}
	
	/*
	 * Getters + Setters
	 */
	function getColumns() {
		return $this->_columns;
	}
	
	function getEntityClass() {
		return $this->_entityClass;
	}
	function setEntityClass($class) {
		$this->_entityClass = $class; 
	}
	
	function getTableName() {
		return $this->_tableName;
	}
	function setTableName($value) {
		$this->_tableName = $value;
	}
}

/* abstract & faux interface */
class CerEntityObject {
	function CerEntityObject() {
	}
	
	function getId() { die(ENTITY_LOG_PREFIX . "You must implement a getId() method."); } // override
	function setId() { die(ENTITY_LOG_PREFIX . "You must implement a setId() method."); } // override
}

class CerEntityObjectColumn {
	var $_localName;
	var $_dbName;
	var $_type;
	
	function CerEntityObjectColumn($local,$dbcol,$type) {
		settype($local,"string");
		settype($dbcol,"string");
		settype($type,"string");
		
		$this->_localName = $local;
		$this->_dbName = $dbcol;
		$this->_type = $type;
	}
	
	function getLocalName() {
		return $this->_localName;
	}
	function getDbName() {
		return $this->_dbName;
	}
	function getType() {
		return $this->_type;
	}
	function getGetter() {
		return "get" . $this->getLocalName();
	}
	function getSetter() {
		return "set" . $this->getLocalName();
	}
}