<?php
class dt_agentes extends toba_datos_tabla {

	function get_descripciones()
	{
		$sql = "SELECT agente_id, agente_id FROM agentes ORDER BY agente_id";
		return toba::db('ctrl_asis')->consultar($sql);
	}

	function get_agente($agente_id)
	{
		$sql = "SELECT agente_id, legajo, dependencia_id, area_id, cuil, ut  
		FROM agentes WHERE agente_id = '$agente_id'";
		return toba::db('ctrl_asis')->consultar_fila($sql);
	}
	function get_listado ($filtro=array())
	{
		$where = array();
		if (isset($filtro['legajo'])) {
			$where[] = "legajo = ".quote($filtro['legajo']);
		}
		if (isset($filtro['apellido'])) {
			$where[] = "apellido ILIKE ".quote("%{$filtro['apellido']}%");
		}
		
		
		$sql = "SELECT * FROM reloj.agentes;";
		if (count($where)>0) {
			$sql = sql_concatenar_where($sql, $where);
		}
		return toba::db('ctrl_asis')->consultar($sql);
	}

}
?>