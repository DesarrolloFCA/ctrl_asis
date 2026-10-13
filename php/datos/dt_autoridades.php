<?php
class dt_autoridades extends ctrl_asis_datos_tabla
{
	function get_listado()
	{
		$sql = "SELECT DISTINCT	legajo_subalterno, t_a.legajo_subalterno || ' - '|| TRIM(apellido) || ', ' || TRIM(nombre) personal,
				legajo_autoridad,autoridad
				FROM
				reloj.autoridades AS  t_a
				RIGHT JOIN reloj.agentes AS a ON legajo_subalterno =a.legajo
				RIGHT JOIN reloj.legajos_autoridad AS b ON b.legajo =legajo_autoridad
				ORDER BY legajo_autoridad";
		return toba::db('ctrl_asis')->consultar($sql);
	}
	static function get_directores()
	{
		$sql= "SELECT  distinct legajo, legajo|| ' - ' || ayn AS director
		FROM reloj.vw_directores 
		WHERE  legajo_dir is not null
		and ayn is not null
		and legajo is not null
		";
		ei_arbol(toba::db('ctrl_asis')->consultar($sql));
		return toba::db('ctrl_asis')->consultar($sql);
	}

	function get_descripciones()
	{
		$sql = "SELECT legajo_autoridad,  FROM autoridades ORDER BY ";
		return toba::db('ctrl_asis')->consultar($sql);
	}
	

}
?>