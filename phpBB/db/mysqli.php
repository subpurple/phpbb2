<?php
/***************************************************************************
 *                                 mysqli.php
 *                            -------------------
 *   begin                : Saturday, Feb 13, 2001
 *   copyright            : (C) 2001 The phpBB Group
 *   email                : support@phpbb.com
 *
 *   $Id$
 *
 ***************************************************************************/

/***************************************************************************
 *
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 ***************************************************************************/

if (!defined('SQL_LAYER'))
{
	define('SQL_LAYER','mysqli');

	class sql_db
	{
		var $db_connect_id;
		var $query_result;
		var $num_queries = 0;
		var $persistency;
		var $user;
		var $password;
		var $server;
		var $dbname;

	//
		// Constructor
	//
		function __construct($sqlserver, $sqluser, $sqlpassword, $database, $persistency = true)
		{
			$this->persistency = $persistency;
			$this->user = $sqluser;
			$this->password = $sqlpassword;
			$this->server = $sqlserver;
			$this->dbname = $database;

			// Disable mysqli internal error reporting to handle errors manually
			mysqli_report(MYSQLI_REPORT_OFF);

			if ($this->persistency)
			{
				$this->db_connect_id = @mysqli_connect('p:' . $this->server, $this->user, $this->password, $this->dbname, null);
			}
			else
			{
				$this->db_connect_id = @mysqli_connect($this->server, $this->user, $this->password, $this->dbname, null);
			}

			if ($this->db_connect_id instanceof mysqli)
			{
				if ($database !== '')
				{
					$dbselect = @mysqli_select_db($this->db_connect_id, $this->dbname);
					if (!$dbselect)
					{
						@mysqli_close($this->db_connect_id);
						$this->db_connect_id = false;
					}
				}

				return $this->db_connect_id;
			}

			return false;
		}

	//
		// Other base methods
	//
		function sql_close()
		{
			if ($this->db_connect_id instanceof mysqli)
			{
				if ($this->query_result instanceof mysqli_result)
				{
					try
					{
						@mysqli_free_result($this->query_result);
					}
					catch (Throwable $e)
					{
					}
				}

				return @mysqli_close($this->db_connect_id);
			}

			return false;
		}

	//
		// Base query method
	//
		function sql_query($query = '', $transaction = false)
		{
			// Remove any pre-existing queries
			unset($this->query_result);

			if ($query !== '')
			{
				$this->num_queries++;

				if ($this->db_connect_id instanceof mysqli)
				{
					$this->query_result = @mysqli_query($this->db_connect_id, $query);
				}
				else
				{
					$this->query_result = false;
				}
			}

			if ($this->query_result)
			{
				return $this->query_result;
			}
			else
			{
				return $transaction === END_TRANSACTION;
			}
		}

	//
		// Other query methods
	//
		function sql_numrows($query_id = 0)
		{
			if (!$query_id)
			{
				$query_id = $this->query_result;
			}
			if ($query_id instanceof mysqli_result)
			{
				$result = @mysqli_num_rows($query_id);

				return $result;
			}
			else
			{
				return false;
			}
		}
		function sql_affectedrows()
		{
			if ($this->db_connect_id instanceof mysqli)
			{
				$result = @mysqli_affected_rows($this->db_connect_id);

				return $result;
			}
			else
			{
				return false;
			}
		}
		function sql_numfields($query_id = 0)
		{
			if (!$query_id)
			{
				$query_id = $this->query_result;
			}
			if ($query_id instanceof mysqli_result)
			{
				$result = @mysqli_num_fields($query_id);

				return $result;
			}
			else
			{
				return false;
			}
		}
		function sql_fieldname($offset, $query_id = 0)
		{
			if (!$query_id)
			{
				$query_id = $this->query_result;
			}
			if ($query_id instanceof mysqli_result)
			{
				$field = @mysqli_fetch_field_direct($query_id, $offset);

				// Ensure $field is an object and has the 'name' property before accessing
				return ($field instanceof stdClass && property_exists($field, 'name')) ? $field->name : false;
			}
			else
			{
				return false;
			}
		}
		function sql_fieldtype($offset, $query_id = 0)
		{
			if (!$query_id)
			{
				$query_id = $this->query_result;
			}
			if ($query_id instanceof mysqli_result)
			{
				$field = @mysqli_fetch_field_direct($query_id, $offset);

				// Ensure $field is an object and has the 'type' property before accessing
				return ($field instanceof stdClass && property_exists($field, 'type')) ? $field->type : false;
			}
			else
			{
				return false;
			}
		}
		function sql_fetchrow($query_id = 0)
		{
			if (!$query_id)
			{
				$query_id = $this->query_result;
			}
			if ($query_id instanceof mysqli_result)
			{
				$result = @mysqli_fetch_array($query_id);

				return $result;
			}
			else
			{
				return false;
			}
		}
		function sql_fetchrowset($query_id = 0)
		{
			if (!$query_id)
			{
				$query_id = $this->query_result;
			}
			if ($query_id instanceof mysqli_result)
			{
				$result = array();
				while ($row = @mysqli_fetch_array($query_id))
				{
					$result[] = $row;
				}

				return $result;
			}
			else
			{
				return false;
			}
		}

		function mysqli_result($query_id, $rownum = 0, $field = 0)
		{
			if (!$query_id instanceof mysqli_result)
			{
				return false;
			}

			$numrows = mysqli_num_rows($query_id);
			if ($numrows && $rownum <= ($numrows - 1) && $rownum >= 0)
			{
				if (@mysqli_data_seek($query_id, $rownum))
				{
					$row = (is_numeric($field)) ? mysqli_fetch_row($query_id) : mysqli_fetch_assoc($query_id);
					if (isset($row[$field]))
					{
						return $row[$field];
					}
				}
			}

			return false;
		}

		public function sql_fetchfield($field, $rownum = -1, $query_id = 0)
		{
			if (!$query_id)
			{
				$query_id = $this->query_result;
			}

			$result = false;

			if ($query_id instanceof mysqli_result)
			{
				if ($rownum > -1)
				{
					$result = $this->mysqli_result($query_id, $rownum, $field);
				}
				else
				{
					$row = $this->sql_fetchrow();
					// Check if $row exists and if the key $field exists in $row
					if (is_array($row) && array_key_exists($field, $row))
					{
						$result = $row[$field];
					}
				}

				return $result;
			}
			else
			{
				return false;
			}
		}
		function sql_rowseek($rownum, $query_id = 0)
		{
			if (!$query_id)
			{
				$query_id = $this->query_result;
			}
			if ($query_id instanceof mysqli_result)
			{
				$result = @mysqli_data_seek($query_id, $rownum);

				return $result;
			}
			else
			{
				return false;
			}
		}
		public function sql_nextid()
		{
			if ($this->db_connect_id instanceof mysqli)
			{
				$result = @mysqli_insert_id($this->db_connect_id);

				return $result;
			}
			else
			{
				return false;
			}
		}
		function sql_freeresult($query_id = 0)
		{
			if (!$query_id)
			{
				$query_id = $this->query_result;
			}
			if ($query_id instanceof mysqli_result)
			{
				@mysqli_free_result($query_id);

				return true;
			}
			else
			{
				return false;
			}
		}
		function sql_error($query_id = 0)
		{
			$result = array('message' => '', 'code' => 0);

			if ($this->db_connect_id instanceof mysqli)
			{
				$result['message'] = @mysqli_error($this->db_connect_id);
				$result['code'] = @mysqli_errno($this->db_connect_id);
			}
			else
			{
				$result['message'] = @mysqli_connect_error();
				$result['code'] = @mysqli_connect_errno();
			}

			return $result;
		}
	} // class sql_db
} // if ... define

?>