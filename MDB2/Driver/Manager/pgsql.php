<?php
// +----------------------------------------------------------------------+
// | PHP versions 4 and 5                                                 |
// +----------------------------------------------------------------------+
// | Copyright (c) 1998-2004 Manuel Lemos, Tomas V.V.Cox,                 |
// | Stig. S. Bakken, Lukas Smith                                         |
// | All rights reserved.                                                 |
// +----------------------------------------------------------------------+
// | MDB2 is a merge of PEAR DB and Metabases that provides a unified DB  |
// | API as well as database abstraction for PHP applications.            |
// | This LICENSE is in the BSD license style.                            |
// |                                                                      |
// | Redistribution and use in source and binary forms, with or without   |
// | modification, are permitted provided that the following conditions   |
// | are met:                                                             |
// |                                                                      |
// | Redistributions of source code must retain the above copyright       |
// | notice, this list of conditions and the following disclaimer.        |
// |                                                                      |
// | Redistributions in binary form must reproduce the above copyright    |
// | notice, this list of conditions and the following disclaimer in the  |
// | documentation and/or other materials provided with the distribution. |
// |                                                                      |
// | Neither the name of Manuel Lemos, Tomas V.V.Cox, Stig. S. Bakken,    |
// | Lukas Smith nor the names of his contributors may be used to endorse |
// | or promote products derived from this software without specific prior|
// | written permission.                                                  |
// |                                                                      |
// | THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS  |
// | "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT    |
// | LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS    |
// | FOR A PARTICULAR PURPOSE ARE DISCLAIMED.  IN NO EVENT SHALL THE      |
// | REGENTS OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,          |
// | INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, |
// | BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS|
// |  OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED  |
// | AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT          |
// | LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY|
// | WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE          |
// | POSSIBILITY OF SUCH DAMAGE.                                          |
// +----------------------------------------------------------------------+
// | Author: Paul Cooper <pgc@ucecom.com>                                 |
// +----------------------------------------------------------------------+
//
// $Id$

require_once 'MDB2/Driver/Manager/Common.php';

/**
 * MDB2 MySQL driver for the management modules
 *
 * @package MDB2
 * @category Database
 * @author  Paul Cooper <pgc@ucecom.com>
 */
class MDB2_Driver_Manager_pgsql extends MDB2_Driver_Manager_common
{
    // {{{ createDatabase()

    /**
     * create a new database
     *
     * @param string $name name of the database that should be created
     * @return mixed MDB2_OK on success, a MDB2 error on failure
     * @access public
     **/
    function createDatabase($name)
    {
        $db =& $GLOBALS['_MDB2_databases'][$this->db_index];
        return $db->standaloneQuery("CREATE DATABASE $name");
    }

    // }}}
    // {{{ dropDatabase()

    /**
     * drop an existing database
     *
     * @param string $name name of the database that should be dropped
     * @return mixed MDB2_OK on success, a MDB2 error on failure
     * @access public
     **/
    function dropDatabase($name)
    {
        $db =& $GLOBALS['_MDB2_databases'][$this->db_index];
        return $db->standaloneQuery("DROP DATABASE $name");
    }

    // }}}
    // {{{ alterTable()

    /**
     * alter an existing table
     *
     * @param string $name name of the table that is intended to be changed.
     * @param array $changes associative array that contains the details of each type
     *                              of change that is intended to be performed. The types of
     *                              changes that are currently supported are defined as follows:
     *
     *                              name
     *
     *                                 New name for the table.
     *
     *                             added_fields
     *
     *                                 Associative array with the names of fields to be added as
     *                                  indexes of the array. The value of each entry of the array
     *                                  should be set to another associative array with the properties
     *                                  of the fields to be added. The properties of the fields should
     *                                  be the same as defined by the Metabase parser.
     *
     *
     *                             removed_fields
     *
     *                                 Associative array with the names of fields to be removed as indexes
     *                                  of the array. Currently the values assigned to each entry are ignored.
     *                                  An empty array should be used for future compatibility.
     *
     *                             renamed_fields
     *
     *                                 Associative array with the names of fields to be renamed as indexes
     *                                  of the array. The value of each entry of the array should be set to
     *                                  another associative array with the entry named name with the new
     *                                  field name and the entry named Declaration that is expected to contain
     *                                  the portion of the field declaration already in DBMS specific SQL code
     *                                  as it is used in the CREATE TABLE statement.
     *
     *                             changed_fields
     *
     *                                 Associative array with the names of the fields to be changed as indexes
     *                                  of the array. Keep in mind that if it is intended to change either the
     *                                  name of a field and any other properties, the changed_fields array entries
     *                                  should have the new names of the fields as array indexes.
     *
     *                                 The value of each entry of the array should be set to another associative
     *                                  array with the properties of the fields to that are meant to be changed as
     *                                  array entries. These entries should be assigned to the new values of the
     *                                  respective properties. The properties of the fields should be the same
     *                                  as defined by the Metabase parser.
     *
     *                                 If the default property is meant to be added, removed or changed, there
     *                                  should also be an entry with index ChangedDefault assigned to 1. Similarly,
     *                                  if the notnull constraint is to be added or removed, there should also be
     *                                  an entry with index ChangedNotNull assigned to 1.
     *
     *                             Example
     *                                 array(
     *                                     'name' => 'userlist',
     *                                     'added_fields' => array(
     *                                         'quota' => array(
     *                                             'type' => 'integer',
     *                                             'unsigned' => 1
     *                                         )
     *                                     ),
     *                                     'removed_fields' => array(
     *                                         'file_limit' => array(),
     *                                         'time_limit' => array()
     *                                         ),
     *                                     'changed_fields' => array(
     *                                         'gender' => array(
     *                                             'default' => 'M',
     *                                             'change_default' => 1,
     *                                         )
     *                                     ),
     *                                     'renamed_fields' => array(
     *                                         'sex' => array(
     *                                             'name' => 'gender',
     *                                         )
     *                                     )
     *                                 )
     * @param boolean $check indicates whether the function should just check if the DBMS driver
     *                              can perform the requested table alterations if the value is true or
     *                              actually perform them otherwise.
     * @return mixed MDB2_OK on success, a MDB2 error on failure
     * @access public
     **/
    function alterTable($name, &$changes, $check)
    {
        $db =& $GLOBALS['_MDB2_databases'][$this->db_index];
        foreach ($changes as $change_name => $change) {
            switch ($change_name) {
            case 'added_fields':
                break;
            case 'removed_fields':
                return $db->raiseError(MDB2_ERROR_UNSUPPORTED, null, null,
                'alterTable: database server does not support dropping table columns');
            case 'name':
            case 'renamed_fields':
            case 'changed_fields':
            default:
                return $db->raiseError(MDB2_ERROR_UNSUPPORTED, null, null,
                    'alterTable: change type "'.$change_name.'\" not yet supported');
            }
        }

        if ($check) {
            return MDB2_OK;
        }

        if (isset($changes['added_fields'])) {
            foreach ($changes['added_fields'] as $field_name => $field) {
                if ($query) {
                    $query .= ', ';
                }
                $query .= 'ADD ' . $db->getDeclaration($field['type'], $field_name, $field);
            }
        }

        if (isset($changes['removed_fields'])) {
            foreach ($changes['removed_fields'] as $field_name => $field) {
                if ($query) {
                    $query .= ', ';
                }
                $query .= 'DROP ' . $field_name;
            }
        }

        if (!$query) {
            return MDB2_OK;
        }

        return $db->query("ALTER TABLE $name $query");
    }

    // }}}
    // {{{ listDatabases()

    /**
     * list all databases
     *
     * @return mixed data array on success, a MDB2 error on failure
     * @access public
     **/
    function listDatabases()
    {
        $db =& $GLOBALS['_MDB2_databases'][$this->db_index];
        $result = $db->standaloneQuery('SELECT datname FROM pg_database');
        if (!MDB2::isResultCommon($result)) {
            return $result;
        }

        $col = $result->fetchCol();
        $result->free();
        return $col;
    }

    // }}}
    // {{{ listUsers()

    /**
     * list all users
     *
     * @return mixed data array on success, a MDB2 error on failure
     * @access public
     **/
    function listUsers()
    {
        $db =& $GLOBALS['_MDB2_databases'][$this->db_index];
        $result = $db->standaloneQuery('SELECT usename FROM pg_user');
        if (!MDB2::isResultCommon($result)) {
            return $result;
        }

        $col = $result->fetchCol();
        $result->free();
        return $col;
    }

    // }}}
    // {{{ listViews()

    /**
     * list the views in the database
     *
     * @return mixed MDB2_OK on success, a MDB2 error on failure
     * @access public
     **/
    function listViews()
    {
        $db =& $GLOBALS['_MDB2_databases'][$this->db_index];
        $query = 'SELECT viewname FROM pg_views';
        return $db->queryCol($query);
    }

    // }}}
    // {{{ listFunctions()

    /**
     * list all functions in the current database
     *
     * @return mixed data array on success, a MDB2 error on failure
     * @access public
     */
    function listFunctions()
    {
        $db =& $GLOBALS['_MDB2_databases'][$this->db_index];
        $query = "
            SELECT
                proname
            FROM
                pg_proc pr,
                pg_type tp
            WHERE
                tp.oid = pr.prorettype
                AND pr.proisagg = FALSE
                AND tp.typname <> 'trigger'
                AND pr.pronamespace IN
                    (SELECT oid FROM pg_namespace WHERE nspname NOT LIKE 'pg_%' AND nspname != 'information_schema')";
        return $db->queryCol($query);
    }

    // }}}
    // {{{ listTables()

    /**
     * list all tables in the current database
     *
     * @return mixed data array on success, a MDB2 error on failure
     * @access public
     **/
    function listTables()
    {
        $db =& $GLOBALS['_MDB2_databases'][$this->db_index];
        // gratuitously stolen from PEAR DB _getSpecialQuery in pgsql.php
        $query = 'SELECT c.relname AS "Name"'
                        . ' FROM pg_class c, pg_user u'
                        . ' WHERE c.relowner = u.usesysid'
                        . " AND c.relkind = 'r'"
                        . ' AND NOT EXISTS'
                        . ' (SELECT 1 FROM pg_views'
                        . '  WHERE viewname = c.relname)'
                        . " AND c.relname !~ '^(pg_|sql_)'"
                        . ' UNION'
                        . ' SELECT c.relname AS "Name"'
                        . ' FROM pg_class c'
                        . " WHERE c.relkind = 'r'"
                        . ' AND NOT EXISTS'
                        . ' (SELECT 1 FROM pg_views'
                        . '  WHERE viewname = c.relname)'
                        . ' AND NOT EXISTS'
                        . ' (SELECT 1 FROM pg_user'
                        . '  WHERE usesysid = c.relowner)'
                        . " AND c.relname !~ '^pg_'";
        return $db->queryCol($query);
    }

    // }}}
    // {{{ listTableFields()

    /**
     * list all fields in a tables in the current database
     *
     * @param string $table name of table that should be used in method
     * @return mixed data array on success, a MDB2 error on failure
     * @access public
     */
    function listTableFields($table)
    {
        $db =& $GLOBALS['_MDB2_databases'][$this->db_index];
        $result = $db->query("SELECT * FROM $table");
        if (PEAR::isError($result)) {
            return $result;
        }
        $columns = $result->getColumnNames();
        $result->free();
        if (PEAR::isError($columns)) {
            return $columns;
        }
        return array_flip($columns);
    }

    // }}}
    // {{{ listTableIndexes()

    /**
     * list all indexes in a table
     *
     * @param string    $table      name of table that should be used in method
     * @return mixed data array on success, a MDB2 error on failure
     * @access public
     */
    function listTableIndexes($table)
    {
        $db =& $GLOBALS['_MDB2_databases'][$this->db_index];
        return $db->queryCol("SELECT relname
                                FROM pg_class WHERE oid IN
                                  (SELECT indexrelid FROM pg_index, pg_class
                                   WHERE (pg_class.relname='$table')
                                   AND (pg_class.oid=pg_index.indrelid))");
    }

    // }}}
    // {{{ createSequence()

    /**
     * create sequence
     *
     * @param string $seq_name name of the sequence to be created
     * @param string $start start value of the sequence; default is 1
     * @param boolean   $auto_increment if the seq should be auto inc or not; default is false
     * @param string    $field name of the field that's being turned into auto increment
     * @return mixed MDB2_OK on success, a MDB2 error on failure
     * @access public
     **/
    function createSequence($seq_name, $start = 1, $auto_increment = false, $field = '')
    {
        $db =& $GLOBALS['_MDB2_databases'][$this->db_index];
        $sequence_name = $db->getSequenceName($seq_name);
        return $db->query("CREATE SEQUENCE $sequence_name INCREMENT 1".
            ($start < 1 ? " MINVALUE $start" : '')." START $start");
    }

    // }}}
    // {{{ dropSequence()

    /**
     * drop existing sequence
     *
     * @param string $seq_name name of the sequence to be dropped
     * @return mixed MDB2_OK on success, a MDB2 error on failure
     * @access public
     **/
    function dropSequence($seq_name)
    {
        $db =& $GLOBALS['_MDB2_databases'][$this->db_index];
        $sequence_name = $db->getSequenceName($seq_name);
        return $db->query("DROP SEQUENCE $sequence_name");
    }

    // }}}
    // {{{ listSequences()

    /**
     * list all sequences in the current database
     *
     * @return mixed data array on success, a MDB2 error on failure
     * @access public
     **/
    function listSequences()
    {
        $db =& $GLOBALS['_MDB2_databases'][$this->db_index];
        $query = "SELECT relname FROM pg_class WHERE relkind = 'S' AND relnamespace IN";
        $query.= "(SELECT oid FROM pg_namespace WHERE nspname NOT LIKE 'pg_%' AND nspname != 'information_schema')";
        $table_names = $db->queryCol($query);
        if (PEAR::isError($table_names)) {
            return $table_names;
        }
        $sequences = array();
        for ($i = 0, $j = count($table_names); $i < $j; ++$i) {
            if ($sqn = $this->_isSequenceName($table_names[$i]))
                $sequences[] = $sqn;
        }
        return $sequences;
    }
}
?>
