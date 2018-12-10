<?php
namespace Dot\Crud\System\Database;

class GenericReflection {

    const DEFAULT_DRIVER    = 'mysql';

    private $_pdo           = null;
    private $_driver        = null;
    private $_database      = null;
    private $_typeConverter = null;

    private function _getTablesSql()    {
        switch ($this->_driver) {
            default:
                return 'SELECT "TABLE_NAME" FROM "INFORMATION_SCHEMA"."TABLES" WHERE "TABLE_TYPE" IN (\'BASE TABLE\') AND "TABLE_SCHEMA" = ? ORDER BY BINARY "TABLE_NAME"';
        }
    }

    private function _getTableColumnsSql()  {
        switch ($this->_driver) {
            default:
                return 'SELECT "COLUMN_NAME", "IS_NULLABLE", "DATA_TYPE", "CHARACTER_MAXIMUM_LENGTH", "NUMERIC_PRECISION", "NUMERIC_SCALE" FROM "INFORMATION_SCHEMA"."COLUMNS" WHERE "TABLE_NAME" = ? AND "TABLE_SCHEMA" = ?';
        }
    }

    private function _getTablePrimaryKeysSQL()   {
        switch ($this->_driver) {
            default:
                return 'SELECT "COLUMN_NAME" FROM "INFORMATION_SCHEMA"."KEY_COLUMN_USAGE" WHERE "CONSTRAINT_NAME" = \'PRIMARY\' AND "TABLE_NAME" = ? AND "TABLE_SCHEMA" = ?';
        }
    }

    private function _getTableForeignKeysSQL()   {
        switch ($this->_driver) {
            default:
                return 'SELECT "COLUMN_NAME", "REFERENCED_TABLE_NAME" FROM "INFORMATION_SCHEMA"."KEY_COLUMN_USAGE" WHERE "REFERENCED_TABLE_NAME" IS NOT NULL AND "TABLE_NAME" = ? AND "TABLE_SCHEMA" = ?';
        }
    }

    private function _query($sql = null, array $parameters = [])   {
        $stmt = $this->_pdo->prepare($sql);
        $stmt->execute($parameters);
        return $stmt->fetchAll();
    }

    public function __construct(\PDO $pdo = null, $driver = null, $database = null)   {
        $this->_pdo = $pdo;
        $this->_driver = $driver;
        $this->_database = $database;
        $this->_typeConverter = new TypeConverter($driver);
    }

    public function getIgnoredTables()  {
        switch ($this->_driver) {
            default: return [];
        }
    }

    public function getDatabaseName()   {
        return $this->_database;
    }

    public function getTables() {
        return $this->_query($this->_getTablesSql(), [$this->_database]);
    }

    public function getTableColumns($tableName = null)   {
        return $this->_query($this->_getTableColumnsSql(), [$tableName, $this->_database]);
    }

    public function getTablePrimaryKeys($tableName = null)   {
        $primaryKeys = [];
        $sql = $this->_getTablePrimaryKeysSQL();
        $results = $this->_query($sql, [$tableName, $this->_database]);
        foreach ($results as $result) {
            $primaryKeys[] = $result['COLUMN_NAME'];
        }
        return $primaryKeys;
    }

    public function getTableForeignKeys($tableName = null)   {
        $sql = $this->_getTableForeignKeysSQL();
        $results = $this->_query($sql, [$tableName, $this->_database]);
        $foreignKeys = [];
        foreach ($results as $result) {
            $foreignKeys[$result['COLUMN_NAME']] = $result['REFERENCED_TABLE_NAME'];
        }
        return $foreignKeys;
    }

    public function toJdbcType($type = null, $size = null)    {
        return $this->_typeConverter->toJdbc($type, $size);
    }

}