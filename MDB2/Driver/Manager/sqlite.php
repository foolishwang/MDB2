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
// | Author: Lukas Smith <smith@pooteeweet.org>                           |
// +----------------------------------------------------------------------+
//
// $Id$
//

require_once 'MDB2/Driver/Manager/Common.php';

/**
 * MDB2 SQLite driver for the management modules
 *
 * @package MDB2
 * @category Database
 * @author  Lukas Smith <smith@pooteeweet.org>
 */
class MDB2_Driver_Manager_sqlite extends MDB2_Driver_Manager_Common
{
    // {{{ createDatabase()

    /**
     * create a new database
     *
     * @param string $name name of the database that should be created
     * @return mixed MDB2_OK on success, a MDB2 error on failure
     * @access public
     */
    function createDatabase($name)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }

        $database_file = $db->_getDatabaseFile($name);
        if (file_exists($database_file)) {
            return $db->raiseError(MDB2_ERROR_CANNOT_CREATE, null, null,
                'createDatabase: database already exists');
        }
        $php_errormsg = '';
        $handle = @sqlite_open($database_file, $db->dsn['mode'], $php_errormsg);
        if (!$handle) {
            return $db->raiseError(MDB2_ERROR_CANNOT_CREATE, null, null,
                'createDatabase: '.(isset($php_errormsg) ? $php_errormsg : 'could not create the database file'));
        }
        @sqlite_close($handle);
        return MDB2_OK;
    }

    // }}}
    // {{{ dropDatabase()

    /**
     * drop an existing database
     *
     * @param string $name name of the database that should be dropped
     * @return mixed MDB2_OK on success, a MDB2 error on failure
     * @access public
     */
    function dropDatabase($name)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }

        $database_file = $db->_getDatabaseFile($name);
        if (!@file_exists($database_file)) {
            return $db->raiseError(MDB2_ERROR_CANNOT_DROP, null, null,
                'dropDatabase: database does not exist');
        }
        $result = @unlink($database_file);
        if (!$result) {
            return $db->raiseError(MDB2_ERROR_CANNOT_DROP, null, null,
                'dropDatabase: '.(isset($php_errormsg) ? $php_errormsg : 'could not remove the database file'));
        }
        return MDB2_OK;
    }

    // }}}
    // {{{ listDatabases()

    /**
     * list all databases
     *
     * @return mixed data array on success, a MDB2 error on failure
     * @access public
     */
    function listDatabases()
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }

        return $db->raiseError(MDB2_ERROR_UNSUPPORTED, null, null,
            'listDatabases: list databases is not supported');
    }

    // }}}
    // {{{ listUsers()

    /**
     * list all users
     *
     * @return mixed data array on success, a MDB2 error on failure
     * @access public
     */
    function listUsers()
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }

        return $db->raiseError(MDB2_ERROR_UNSUPPORTED, null, null,
            'listDatabases: list databases is not supported');
    }

    // }}}
    // {{{ listTables()

    /**
     * list all tables in the current database
     *
     * @return mixed data array on success, a MDB2 error on failure
     * @access public
     */
    function listTables()
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }

        $query = "SELECT name FROM sqlite_master WHERE type='table' AND sql NOT NULL ORDER BY name";
        $table_names = $db->queryCol($query);
        if (PEAR::isError($table_names)) {
            return $table_names;
        }
        $result = array();
        for ($i = 0, $j = count($table_names); $i < $j; ++$i) {
            if (!$this->_isSequenceName($table_names[$i]))
                $result[] = $table_names[$i];
        }
        if ($db->options['portability'] & MDB2_PORTABILITY_FIX_CASE) {
            $result = array_map(($db->options['field_case'] == CASE_LOWER ? 'strtolower' : 'strtoupper'), $result);
        }
        return $result;
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
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }

        $query = "SELECT * FROM $table";
        $db->setLimit(1);
        $result2 = $db->query($query);
        if (PEAR::isError($result2)) {
            return $result2;
        }
        $result = $result2->getColumnNames();
        $result2->free();
        if (PEAR::isError($result)) {
            return $result;
        }
        return array_flip($result);
    }

    // }}}
    // {{{ createIndex()

    /**
     * get the stucture of a field into an array
     *
     * @param string    $table         name of the table on which the index is to be created
     * @param string    $name         name of the index to be created
     * @param array     $definition        associative array that defines properties of the index to be created.
     *                                 Currently, only one property named FIELDS is supported. This property
     *                                 is also an associative with the names of the index fields as array
     *                                 indexes. Each entry of this array is set to another type of associative
     *                                 array that specifies properties of the index that are specific to
     *                                 each field.
     *
     *                                Currently, only the sorting property is supported. It should be used
     *                                 to define the sorting direction of the index. It may be set to either
     *                                 ascending or descending.
     *
     *                                Not all DBMS support index sorting direction configuration. The DBMS
     *                                 drivers of those that do not support it ignore this property. Use the
     *                                 function support() to determine whether the DBMS driver can manage indexes.

     *                                 Example
     *                                    array(
     *                                        'fields' => array(
     *                                            'user_name' => array(
     *                                                'sorting' => 'ascending'
     *                                            ),
     *                                            'last_login' => array()
     *                                        )
     *                                    )
     * @return mixed MDB2_OK on success, a MDB2 error on failure
     * @access public
     */
    function createIndex($table, $name, $definition)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }

        $query = 'CREATE';
        if (array_key_exists('unique', $definition) && $definition['unique']) {
            $query.= ' UNIQUE';
        }
        $query .= " INDEX $name ON $table (";
        $fields = array();
        foreach ($definition['fields'] as $field_name => $field) {
            $field_string = $field_name;
            if (array_key_exists('sorting', $field)) {
                switch ($field['sorting']) {
                case 'ascending':
                    $field_string.= ' ASC';
                    break;
                case 'descending':
                    $field_string.= ' DESC';
                    break;
                }
            }
            $fields[] = $field_string;
        }
        $query .= implode(', ', $fields) . ')';
        return $db->query($query);

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
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }

        $query = "SELECT name FROM sqlite_master WHERE type='index' AND tbl_name='$table' AND sql NOT NULL ORDER BY name";
        $result = $db->queryCol($query);
        if (PEAR::isError($result)) {
            return $result;
        }
        if ($db->options['portability'] & MDB2_PORTABILITY_FIX_CASE) {
            $result = array_map(($db->options['field_case'] == CASE_LOWER ? 'strtolower' : 'strtoupper'), $result);
        }
        return $result;
    }


    // }}}
    // {{{ createConstraint()

    /**
     * create a constraint on a table
     *
     * @param string    $table         name of the table on which the constraint is to be created
     * @param string    $name         name of the constraint to be created
     * @param array     $definition        associative array that defines properties of the constraint to be created.
     *                                 Currently, only one property named FIELDS is supported. This property
     *                                 is also an associative with the names of the constraint fields as array
     *                                 constraints. Each entry of this array is set to another type of associative
     *                                 array that specifies properties of the constraint that are specific to
     *                                 each field.
     *
     *                                 Example
     *                                    array(
     *                                        'fields' => array(
     *                                            'user_name' => array(),
     *                                            'last_login' => array()
     *                                        )
     *                                    )
     * @return mixed MDB2_OK on success, a MDB2 error on failure
     * @access public
     */
    function createConstraint($table, $name, $definition)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }

        return $db->raiseError(MDB2_ERROR_UNSUPPORTED, null, null,
            'createConstraint: Creating Constraints is not supported');
    }

    // }}}
    // {{{ dropConstraint()

    /**
     * drop existing constraint
     *
     * @param string    $table         name of table that should be used in method
     * @param string    $name         name of the constraint to be dropped
     * @return mixed MDB2_OK on success, a MDB2 error on failure
     * @access public
     */
    function dropConstraint($table, $name)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }

        return $db->raiseError(MDB2_ERROR_UNSUPPORTED, null, null,
            'dropConstraints: Drop Constraints is not supported');
    }

    // }}}
    // {{{ listTableConstraints()

    /**
     * list all sonstraints in a table
     *
     * @param string    $table      name of table that should be used in method
     * @return mixed data array on success, a MDB2 error on failure
     * @access public
     */
    function listTableConstraints($table)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }

        return $db->raiseError(MDB2_ERROR_UNSUPPORTED, null, null,
            'listTableConstraints: List Constraints is not supported');
    }

    // }}}
    // {{{ createSequence()

    /**
     * create sequence
     *
     * @param string    $seq_name     name of the sequence to be created
     * @param string    $start         start value of the sequence; default is 1
     * @return mixed MDB2_OK on success, a MDB2 error on failure
     * @access public
     */
    function createSequence($seq_name, $start = 1)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }

        $sequence_name = $db->getSequenceName($seq_name);
        $seqcol_name = $db->options['seqcol_name'];
        $query = "CREATE TABLE $sequence_name ($seqcol_name INTEGER PRIMARY KEY DEFAULT 0 NOT NULL)";
        $res = $db->query($query);
        if (PEAR::isError($res)) {
            return $res;
        }
        if ($start == 1) {
            return MDB2_OK;
        }
        $res = $db->query("INSERT INTO $sequence_name ($seqcol_name) VALUES (".($start-1).')');
        if (!PEAR::isError($res)) {
            return MDB2_OK;
        }
        // Handle error
        $result = $db->query("DROP TABLE $sequence_name");
        if (PEAR::isError($result)) {
            return $db->raiseError(MDB2_ERROR, null, null,
                'createSequence: could not drop inconsistent sequence table ('.
                $result->getMessage().' ('.$result->getUserinfo().'))');
        }
        return $db->raiseError(MDB2_ERROR, null, null,
            'createSequence: could not create sequence table ('.
            $res->getMessage().' ('.$res->getUserinfo().'))');
    }

    // }}}
    // {{{ dropSequence()

    /**
     * drop existing sequence
     *
     * @param string    $seq_name     name of the sequence to be dropped
     * @return mixed MDB2_OK on success, a MDB2 error on failure
     * @access public
     */
    function dropSequence($seq_name)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }

        $sequence_name = $db->getSequenceName($seq_name);
        return $db->query("DROP TABLE $sequence_name");
    }

    // }}}
    // {{{ listSequences()

    /**
     * list all sequences in the current database
     *
     * @return mixed data array on success, a MDB2 error on failure
     * @access public
     */
    function listSequences()
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }

        $query = "SELECT name FROM sqlite_master WHERE type='table' AND sql NOT NULL ORDER BY name";
        $table_names = $db->queryCol($query);
        if (PEAR::isError($table_names)) {
            return $table_names;
        }
        $result = array();
        for ($i = 0, $j = count($table_names); $i < $j; ++$i) {
            if ($sqn = $this->_isSequenceName($table_names[$i]))
                $result[] = $sqn;
        }
        if ($db->options['portability'] & MDB2_PORTABILITY_FIX_CASE) {
            $result = array_map(($db->options['field_case'] == CASE_LOWER ? 'strtolower' : 'strtoupper'), $result);
        }
        return $result;
    }

    // }}}
}
?>
