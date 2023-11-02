<?php
class dt_legajos_autoridad extends ctrl_asis_datos_tabla
{
	function get_descripciones()
	{
		$sql = "SELECT legajo, autoridad FROM legajos_autoridad ORDER BY autoridad";
		return toba::db('ctrl_asis')->consultar($sql);
	}

}

?>