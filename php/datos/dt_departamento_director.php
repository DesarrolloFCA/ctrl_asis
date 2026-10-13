<?php
class dt_departamento_director extends ctrl_asis_datos_tabla
{
	function get_listado($filtro=array())
	{
		$where = array();
		if (isset($filtro['legajo_dir'])) {
			$where[] = "legajo_dir = ".quote($filtro['legajo_dir']);
		}
		$sql = "SELECT Distinct id_departamento,
				legajo_dir  ,director_departamento ayn , departamento
            	from reloj.vw_directores
				Where legajo_dir is not null";
		if (count($where)>0) {
			$sql = sql_concatenar_where($sql, $where);
		}
		
		
		return toba::db('ctrl_asis')->consultar($sql);
	}



}
?>