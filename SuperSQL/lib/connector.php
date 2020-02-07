<?php
namespace SuperSQL\lib;
/*
MIT License

Copyright (c) 2017 Andrew S (Andrews54757_at_gmail.com)

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
*/
// BUILD BETWEEN
class SQLResponse implements \ArrayAccess, \Iterator
{
    public $result;
    public $affected;
    public $ind = 0;
    public $error = false;
    public $outTypes;
    public $complete = true;
    public $stmt;
    private $filter;
    /**
     * Gets data from a query
     */
    function __construct($data, $error, $outtypes, $mode)
    {
        if (!$error) {
            $this->error = $data->errorInfo();
        } else {
            $this->outTypes = $outtypes;
            if ($mode === 0) { // fetch all
                $d        = $data->fetchAll(\PDO::FETCH_ASSOC);
                if ($outtypes) {
                    foreach ($d as $i => &$row) {
                        $this->map($row, $outtypes);
                    }
                }
                $this->result = $d;
            } else if ($mode === 1) { // fetch row-by-row
                $this->stmt     = $data;
                $this->complete = false;
                $this->result   = array();
            }
            $this->affected = $data->rowCount();
        }
    }
    /**
     * Tries to close the connection and resets cursor.
     */
    function close()
    {
        $this->complete = true;
        if ($this->stmt) {
            $this->stmt->closeCursor();
            $this->stmt = null;
        }
    }

    /**
    * Add middleware to filter fetched rows
    */
    public function setFilter($callable){
        $this->filter = $callable;
    }

    /**
     * Gets next row from database
     */
    private function fetchNextRow()
    {
        $row = $this->stmt->fetch(\PDO::FETCH_ASSOC);
        if ($row) {
            if ($this->outTypes) {
                $this->map($row, $this->outTypes);
            }
            if($this->filter != null)
                $this->filter(&$row);
            array_push($this->result, $row);
            return $row;
        } else {
            $this->close();
            return false;
        }
    }
    /**
     * Gets all rows from database
     */
    private function fetchAll()
    {
        while ($this->fetchNextRow()) {
        }
    }
    /**
     * Casts values to correct types
     */
    private function map(&$row, &$outtypes)
    {
        foreach ($outtypes as $col => $dt) {
            if (isset($row[$col])) {
                switch ($dt) {
                    case 'int':
                        $row[$col] = (int) $row[$col];
                        break;
                    case 'double':
                        $row[$col] = (double) $row[$col];
                        break;
                    case 'string':
                        $row[$col] = (string) $row[$col];
                        break;
                    case 'bool':
                        $row[$col] = $row[$col] ? true : false;
                        break;
                    case 'json':
                        $row[$col] = json_decode($row[$col]);
                        break;
                    case 'object':
                        $row[$col] = unserialize($row[$col]);
                        break;
                }
            }
        }
    }
    /**
     * Returns error data if there is one
     */
    function error()
    {
        return $this->error;
    }
    /**
     * Gets data from the query
     *
     * @returns {Array}
     */
    function getData($current = false)
    {
        if (!$this->complete && !$current)
            $this->fetchAll();
        return $this->result;
    }
    /**
     * Gets number of affected rows from the query
     *
     * @returns {Int}
     */
    function rowCount()
    {
        return $this->affected;
    }
    // BEGIN ARRAYACCESS
    function offsetSet($offset, $value) // do nothing
    {
    }
    /**
     * Checks if a value exsists
     */
    function offsetExists($offset)
    {
        return $this->offsetGet($offset) === null ? false : true;
    }
    function offsetUnset($offset)
    {
    }
    /**
     * Gets a value
     */
    function offsetGet($offset)
    {
        if (is_int($offset)) {
            if (isset($this->result[$offset])) {
                return $this->result[$offset];
            } else if (!$this->complete) {
                while ($this->fetchNextRow()) {
                    if (isset($this->result[$offset]))
                        return $this->result[$offset];
                }
            }
        }
        return null;
    }
    // END ARRAYACCESS, BEGIN ITERATOR
    /**
     * Gets next row
     */
    function next()
    {
        if (isset($this->result[$this->ind])) {
            return $this->result[$this->ind++];
        } else if (!$this->complete) {
            $row = $this->fetchNextRow();
            $this->ind++;
            return $row;
        } else {
            return false;
        }
    }
    /**
     * resets pointer
     */
    function rewind()
    {
        $this->ind = 0;
    }
    /**
     * Get current row
     */
    function current()
    {
        return $this->result[$this->ind];
    }
    /**
     * Get pointer
     */
    function key()
    {
        return $this->ind;
    }
    /**
     * Alias for offsetExsists
     */
    function valid()
    {
        return $this->offsetExists($this->ind);
    }
}
class SQLConnector
{
    public $db;
    public $log = array();
    public $dev = false;
    /**
     * Creates a connection
     * @param {String} dsn - DSN of the connection
     * @param {String} user - Username
     * @param {String} pass - Password
     */
    function __construct($dsn, $user, $pass)
    {
        $this->db = new \PDO($dsn, $user, $pass);
    }
    /**
     * Queries database
     * @param {String} query - Query to make
     *
     * @returns {SQLResponse}
     */
    function query($query, $obj = null, $outtypes = null, $mode = 0)
    {
        $q = $this->db->prepare($query);
        if ($obj)
            $e = $q->execute($obj);
        else
            $e = $q->execute();
        if ($this->dev)
            array_push($this->log, array(
                $query,
                $obj
            ));
        if ($mode !== 3) {
            return new SQLResponse($q, $e, $outtypes, $mode);
        } else {
            return $q;
        }
    }
    /**
     * Queries database Advanced
     * @param {String} sql - Base query
     * @param {Array} values - array of values and types
     * @param {Array} insert - Multi-query inserts
     * @param {String} typeString - Type checking
     *
     * @returns {SQLResponse|SQLResponse[]}
     */
    function _query($sql, $values, $insert, $outtypes = null, $mode = 0)
    {
        $q = $this->db->prepare($sql);
        if ($this->dev)
            array_push($this->log, array(
                $sql,
                $values,
                $insert
            ));
        foreach ($values as $key => &$va) {
            $q->bindParam($key + 1, $va[0], $va[1]);
        }
        $e = $q->execute();
        if (!isset($insert[0])) { // Single query
            return new SQLResponse($q, $e, $outtypes, $mode);
        } else { // Multi Query
            $responses = array();
            array_push($responses, new SQLResponse($q, $e, $outtypes, 0));
            foreach ($insert as $key => $value) {
                foreach ($value as $k => &$val) {
                    $values[$k][0] = $val;
                }
                $e = $q->execute();
                array_push($responses, new SQLResponse($q, $e, $outtypes, 0));
            }
            return $responses;
        }
    }
    /**
     * Closes the connection
     */
    function close()
    {
        $this->db = null;
    }
}
// BUILD BETWEEN
?>
