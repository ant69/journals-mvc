<?php
	function stripslashes_array($array)
	{
	  return is_array($array) ? array_map('stripslashes_array', $array) : stripslashes($array);
	}

	function htmlspecialchars_array($array)
	{
	  return is_array($array) ? array_map('htmlspecialchars_array', $array) : htmlspecialchars($array, ENT_QUOTES);
	}

?>