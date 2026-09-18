<?php

class consultas_agentes {

    static function get_agentes($filtro = null)
    {
        $where = array();
        if (isset($filtro['legajo']))
        {
            $where[] = "uncu.legajo.legajo = " . quote($filtro['legajo']);
        }
        if (isset($filtro['apellido']))
        {
            $where[] = "uncu.legajo.apellido ilike '%" . $filtro['apellido'] . "%'";
        }
        if (isset($filtro['dni']))
        {
            $where[] = "uncu.legajo.dni ilike '%" . $filtro['dni'] . "%'";
        }
        /*if (isset($filtro['cod_depcia']))
        {
           // $where[] = "uncu.legajo.cod_depcia = '" . $filtro['cod_depcia'] . "'";
        }*/
        $where[] = "uncu.legajo.cod_depcia = '04'";

        $sql = "SELECT
                uncu.legajo.legajo,
                uncu.legajo.apellido,
                uncu.legajo.nombre,
                uncu.legajo.apellido || ',' || uncu.legajo.nombre as nombres,
                uncu.legajo.fec_nacim,
                uncu.legajo.dni,
                uncu.legajo.fec_ingreso,
                uncu.legajo.estado_civil,
                uncu.legajo.caracter,
                uncu.legajo.categoria,
                uncu.legajo.agrupamiento,
                uncu.legajo.escalafon,
                uncu.legajo.cod_depcia,
                uncu.dependencia.desc_depcia
                FROM
                uncu.legajo
                INNER JOIN uncu.dependencia ON uncu.legajo.cod_depcia = uncu.dependencia.cod_depcia
                    ORDER BY uncu.legajo.apellido,uncu.legajo.nombre";
        $sql = "SELECT
            uncu.legajo.legajo,
uncu.legajo.apellido,
uncu.legajo.nombre,
uncu.legajo.apellido || ',' || uncu.legajo.nombre AS nombres,
uncu.legajo.fec_nacim,
uncu.legajo.dni,
uncu.legajo.fec_ingreso,
uncu.legajo.estado_civil,
uncu.legajo.caracter,
uncu.legajo.categoria,
uncu.legajo.agrupamiento,
uncu.legajo.escalafon,
uncu.legajo.cod_depcia
FROM
uncu.legajo
ORDER BY
uncu.legajo.apellido ASC,
uncu.legajo.nombre ASC

";
        if (count($where) > 0)
        {
            $sql = sql_concatenar_where($sql, $where);
        }
        
        $resp = toba::db('mapuche')->consultar($sql);

        return $resp;
    }

    static function get_agentes_where($w = null)
    {

        $sql = "SELECT
                uncu.legajo.legajo,
                uncu.legajo.apellido,
                uncu.legajo.nombre,
                uncu.legajo.apellido || ',' || uncu.legajo.nombre as nombres,
                uncu.legajo.fec_nacim,
                uncu.legajo.dni,
                uncu.legajo.fec_ingreso,
                uncu.legajo.estado_civil,
                uncu.legajo.caracter,
                uncu.legajo.categoria,
                uncu.legajo.agrupamiento,
                uncu.legajo.escalafon,
                uncu.legajo.cod_depcia,
                uncu.dependencia.desc_depcia
                FROM
                uncu.legajo
                INNER JOIN uncu.dependencia ON uncu.legajo.cod_depcia = uncu.dependencia.cod_depcia";
        if ($w != null)
            $sql .= ' WHERE ' . $w;
        $sql .= " ORDER BY uncu.legajo.apellido,uncu.legajo.nombre";

        $resp = toba::db('mapuche')->consultar($sql);

        return $resp;
    }

    static function get_vacaciones($l)
    {
        $sql = "SELECT
                    reloj.vacaciones_agentes.vacaciones_agente_id,
                    reloj.vacaciones_agentes.agente_id,
                    reloj.vacaciones_agentes.periodo,
                    reloj.vacaciones_agentes.dias_totales,
                    reloj.vacaciones_agentes.dias_disponibles,
                    reloj.agentes.legajo
                    FROM
                    reloj.vacaciones_agentes
                    INNER JOIN reloj.agentes ON reloj.agentes.agente_id = reloj.vacaciones_agentes.agente_id
                    WHERE reloj.agentes.legajo = " . $l['legajo'];

        return consultar_fuente($sql);
    }

    static function recuperar_id_x_legajo($l)
    {

        if ($l == '')
            return 0;
        $sql = "SELECT * FROM reloj.agentes WHERE legajo = '" . $l . "'";

        $resp = consultar_fuente($sql);

        if (count($resp))
            return $resp[0]['agente_id'];
        else
            return 0;
    }

    static function entrada_o_salida($l, $i)
    {

        if ($i != '')
        {
            $sql = "SELECT marca_id, agente_id, fecha, entrada, salida, estado_marca_id, 
                        observaciones, reloj
                   FROM reloj.marca
                   WHERE fecha = '" . $l[1] . "' AND agente_id = '" . $i . "'";
            //echo $sql."<br>";
            $resp = consultar_fuente($sql);
            if ($resp[0]['entrada'] == NULL)
            {
                return 'E';
            } else
            {
                return 'S';
            }
        }
    }

    static function crear_marca($l)
    {
        if ($l['agente_id'] != 0)
        {
            $sql = "SELECT marca_id
                       FROM reloj.marca
                       WHERE agente_id = '" . $l['agente_id'] . "' and fecha = '" . $l[1] . "' AND reloj = '" . $l[4] . "'";
            $resp = consultar_fuente($sql);
            if (count($resp))//quiere decir que hay una marca por lo que devuelvo el ID de la marca
            {
                return $resp[0]['marca_id'];
            } else
            {
                $sql = "INSERT INTO reloj.marca(
                                    agente_id, fecha, estado_marca_id 
                                    , reloj)
                            VALUES ('" . $l['agente_id'] . "', '" . $l[1] . "', 0, 
                                    '" . $linea[4] . "');
                        ";
                consultar_fuente($sql);
                return toba::db()->recuperar_secuencia('reloj.marca_marca_id_seq');
            }
        }
    }

    static function recuperar_marcas($l, $p = null)
    {
        $where = array();
        if (isset($l['agente_id']))
        {
            $where[] = "reloj.marca.agente_id = " . quote($l['agente_id']);
        }
        if (isset($p['primer']))
        {
            $where[] = "reloj.marca.fecha >= '" . $p['primer'] . "'";
        }

        if (isset($p['ultimo']))
        {
            $where[] = "reloj.marca.fecha <= '" . $p['ultimo'] . "'";
        }

        $sql = "SELECT
                    reloj.marca.marca_id,
                    reloj.marca.agente_id,
                    reloj.marca.fecha,
                    reloj.marca.entrada,
                    reloj.marca.salida,
                    reloj.marca.estado_marca_id,
                    reloj.marca.observaciones,
                    reloj.marca.reloj,
                    reloj.conf_estados_marcas.estado_marca
                    FROM
                    reloj.marca
                    INNER JOIN reloj.conf_estados_marcas ON reloj.marca.estado_marca_id = reloj.conf_estados_marcas.estado_marca_id
                    ORDER BY fecha ASC";
        // echo $sql;
        if (count($where) > 0)
        {
            $sql = sql_concatenar_where($sql, $where);
        }
        $s = consultar_fuente($sql);
        $dif = 0;
        $salida = array();
        foreach ($s as $s1)
        {
            if ($s1['entrada'] != '' and $s1['salida'] != '')
            {
                $s1['prom'] = date('H:i', (strtotime("00:00:00") + strtotime($s1['salida']) - strtotime($s1['entrada'])));
                $salida[] = $s1;
            }else
                $salida[] = $s1;
        }
        return $salida;
    }

    static function horas_promedio($l)
    {
        $sql = "SELECT
                    reloj.marca.marca_id,
                    reloj.marca.agente_id,
                    reloj.marca.fecha,
                    reloj.marca.entrada,
                    reloj.marca.salida,
                    reloj.marca.estado_marca_id,
                    reloj.marca.observaciones,
                    reloj.marca.reloj,
                    reloj.conf_estados_marcas.estado_marca
                    FROM
                    reloj.marca
                    INNER JOIN reloj.conf_estados_marcas ON reloj.marca.estado_marca_id = reloj.conf_estados_marcas.estado_marca_id
                    WHERE 
                    reloj.marca.agente_id = " . $l['agente_id'] . ""
                . " ORDER BY fecha ASC";
        // echo $sql;
        $s = consultar_fuente($sql);
        $cant = 0;
        $dif = 0;
        foreach ($s as $s1)
        {
            if ($s1['entrada'] != '' and $s1['salida'] != '')
            {
                $dif += (strtotime("00:00:00") + strtotime($s1['salida']) - strtotime($s1['entrada']));
                $cant++;
            }
        }
        if($cant == 0) return '00:00';
        else return date('H:i', ($dif / $cant));
    }
    static function horas_promedio_por_periodo($l, $p)
    {
         if (isset($l['agente_id']))
        {
            $where[] = "reloj.marca.agente_id = " . quote($l['agente_id']);
        }
        if (isset($p['primer']))
        {
            $where[] = "reloj.marca.fecha >= '" . $p['primer'] . "'";
        }

        if (isset($p['ultimo']))
        {
            $where[] = "reloj.marca.fecha <= '" . $p['ultimo'] . "'";
        }
         if (isset($l['estado_marca_id']))
        {
            $where[] = "reloj.marca.estado_marca_id = 2";
        }
        $sql = "SELECT
                    reloj.marca.marca_id,
                    reloj.marca.agente_id,
                    reloj.marca.fecha,
                    reloj.marca.entrada,
                    reloj.marca.salida,
                    reloj.marca.estado_marca_id,
                    reloj.marca.observaciones,
                    reloj.marca.reloj,
                    reloj.conf_estados_marcas.estado_marca
                    FROM
                    reloj.marca
                    INNER JOIN reloj.conf_estados_marcas ON reloj.marca.estado_marca_id = reloj.conf_estados_marcas.estado_marca_id
                    ORDER BY fecha ASC";
       
        if (count($where) > 0)
        {
            $sql = sql_concatenar_where($sql, $where);
        }
        //echo $sql."<br>";
        $s = consultar_fuente($sql);
        $cant = 0;
        $dif = 0;
        foreach ($s as $s1)
        {
            if ($s1['entrada'] != '' and $s1['salida'] != '')
            {
                $dif += (strtotime("00:00:00") + strtotime($s1['salida']) - strtotime($s1['entrada']));
                $cant++;
            }
        }
       if($cant == 0) return '00:00';
        else return date('H:i', (($dif) / $cant));

    }

    //recuperar los datos de agente con el id interno del sistema
    static function get_agente_id()
    {
        $sql = "SELECT
                uncu.legajo.legajo,
                uncu.legajo.apellido || ', ' || uncu.legajo.nombre as nombres,
                uncu.legajo.fec_nacim,
                uncu.legajo.dni,
                uncu.legajo.fec_ingreso,
                uncu.legajo.estado_civil,
                uncu.legajo.caracter,
                uncu.legajo.categoria,
                uncu.legajo.agrupamiento,
                uncu.legajo.escalafon,
                uncu.legajo.cod_depcia,
                uncu.dependencia.desc_depcia
                FROM
                uncu.legajo
                INNER JOIN uncu.dependencia ON uncu.legajo.cod_depcia = uncu.dependencia.cod_depcia
                ORDER BY uncu.legajo.apellido,uncu.legajo.nombre
                ";
        $resp = toba::db('mapuche')->consultar($sql);
        $salida = array();
        foreach ($resp as $r)
        {
            $sql = "SELECT
                        reloj.agentes.agente_id,
                        reloj.agentes.legajo
                        FROM
                        reloj.agentes
                        WHERE reloj.agentes.legajo = " . $r['legajo'];
            // echo $sql;
            $resp_a = consultar_fuente($sql);
            //  ei_arbol($resp_a);
            $resp_a[0]['nombres'] = $r['nombres'];
            //me aseguro que cree el array con los que existen en mi base local.
            if($resp_a[0]['agente_id'] != null || $resp_a[0]['agente_id'] != '')
                $salida[] = $resp_a[0];
            //  if($i++ == 50) break;
        }
        return $salida;
    }

    //Recuperar nombres a partir del ID de agente
    static function get_nombres_x_id($d)
    {
        $sql = "SELECT
                    reloj.agentes.agente_id,
                    reloj.agentes.legajo
                FROM
                    reloj.agentes
                WHERE reloj.agentes.agente_id = " . $d['agente_id'];
        $resp = consultar_fuente($sql);
        foreach ($resp as $r)
        {
            $sql = "SELECT
                uncu.legajo.legajo,
                uncu.legajo.apellido,
                uncu.legajo.nombre
                FROM
                uncu.legajo
                WHERE 
                uncu.legajo.legajo = " . $r['legajo'] . "
                ORDER BY uncu.legajo.apellido,uncu.legajo.nombre";
            $resp_i = toba::db('mapuche')->consultar($sql);
            return $resp_i[0]['apellido'] . "," . $resp_i[0]['nombre'];
        }
    }

    static function get_nombres_x_usuario($ut)
    {
        $sql = "SELECT
                    reloj.agentes.agente_id,
                    reloj.agentes.legajo,
                    reloj.agentes.dependencia_id,
                    reloj.agentes.area_id,
                    reloj.agentes.cuil,
                    reloj.agentes.ut,
                   -- reloj.conf_areas.area,
                   -- reloj.conf_dependencia.dependencia
                    FROM
                    reloj.agentes
                   -- INNER JOIN reloj.conf_areas ON reloj.agentes.area_id = reloj.conf_areas.area_id
                   -- INNER JOIN reloj.conf_dependencia ON reloj.agentes.dependencia_id = reloj.conf_dependencia.dependencia_id
                WHERE reloj.agentes.ut ilike '" . $ut . "'";

        $resp = consultar_fuente($sql);
        if (count($resp) < 1)
            return;
        $aux = array();
        foreach ($resp as $r)
        {
            $sql = "SELECT
                uncu.legajo.legajo,
                uncu.legajo.apellido,
                uncu.legajo.nombre
                FROM
                uncu.legajo
                WHERE 
                uncu.legajo.legajo = " . $r['legajo'] . "
                ORDER BY uncu.legajo.apellido,uncu.legajo.nombre";
            $resp_i = toba::db('mapuche')->consultar($sql);
            $aux = $r;
            $aux['nombres'] = $resp_i[0]['apellido'] . "," . $resp_i[0]['nombre'];
        }
        return $aux;
    }

    static function get_subordinados($d)
    {
        if ($d['area_id'] == '' OR $d['dependencia_id'] == '')
            return;
        $sql = "SELECT
                    reloj.agentes.agente_id as agente,
                    reloj.agentes.legajo
                FROM
                    reloj.agentes
                WHERE reloj.agentes.dependencia_id = " . $d['dependencia_id'] . " AND reloj.agentes.area_id = " . $d['area_id'];

        $resp = consultar_fuente($sql);
        $salida = array();
        foreach ($resp as $r)
        {
            $sql = "SELECT
                uncu.legajo.legajo,
                uncu.legajo.apellido,
                uncu.legajo.nombre
                FROM
                uncu.legajo
                WHERE 
                uncu.legajo.legajo = " . $r['legajo'] . "
                ORDER BY uncu.legajo.apellido,uncu.legajo.nombre";
            $resp_i = toba::db('mapuche')->consultar($sql);
            $r['legajo'] = $resp_i[0]['legajo'];
            $r['apellido'] = $resp_i[0]['apellido'];
            $r['nombre'] = $resp_i[0]['nombre'];
            $salida[] = $r;
        }
        return $salida;
    }

    function get_area_responsable($a)
    {
        $sql = "SELECT * FROM conf_areas WHERE agente_id = " . $a;
        $resp = consultar_fuente($sql);
        return $resp[0]['area_id'];
    }

    function get_dep_responsable($a)
    {
        //$sql = "SELECT * FROM conf_areas WHERE (agente_id = " . $a . " OR tobausuario ilike '" . toba::usuario()->get_id() . "')" ;
        $sql = "SELECT * FROM conf_areas WHERE (agente_id = " . $a . "')" ;
        $resp = consultar_fuente($sql);
        return $resp[0]['dependencia_id'];
    }

    function tiene_marca($f, $ag_id)
    {
        $sql = "SELECT * FROM marca WHERE fecha = '" . $f . "' and agente_id = " . $ag_id;
       
        $resp = consultar_fuente($sql);
        //  ei_arbol($resp);
        if (count($resp) > 0)
            return TRUE;
        else
            return FALSE;
    }
    
    static function cant_ausentes_por_periodo($l, $p)
    {
         if (isset($l['agente_id']))
        {
            $where[] = "reloj.marca.agente_id = " . quote($l['agente_id']);
        }
        if (isset($p['primer']))
        {
            $where[] = "reloj.marca.fecha >= '" . $p['primer'] . "'";
        }

        if (isset($p['ultimo']))
        {
            $where[] = "reloj.marca.fecha <= '" . $p['ultimo'] . "'";
        }
        //if (isset($p['estado_marca_id']))
      //  {
            $where[] = "reloj.marca.estado_marca_id = 1";
      //  }

        $sql = "SELECT
                    reloj.marca.marca_id,
                    reloj.marca.agente_id,
                    reloj.marca.fecha,
                    reloj.marca.entrada,
                    reloj.marca.salida,
                    reloj.marca.estado_marca_id,
                    reloj.marca.observaciones,
                    reloj.marca.reloj,
                    reloj.conf_estados_marcas.estado_marca
                    FROM
                    reloj.marca
                    INNER JOIN reloj.conf_estados_marcas ON reloj.marca.estado_marca_id = reloj.conf_estados_marcas.estado_marca_id
                    ORDER BY fecha ASC";
        // echo $sql;
        if (count($where) > 0)
        {
            $sql = sql_concatenar_where($sql, $where);
        }
        $s = consultar_fuente($sql);
        return count($s);
    }
    
    function generar_ausentes_globales($p)
    {
        $agentes = consultar_fuente("SELECT * FROM agentes");
        foreach($agentes as $a)
        {
            
        }
    }

    /**
     * Retorna el personal presente en el momento actual.
     * Regla de Negocio: Cantidad impar de fichadas en el día de hoy (COUNT(*) % 2 != 0).
     * Consulta en tiempo real sobre reloj.user_attendance (FDW ZKTeco) deduplicando agentes multicargo.
     */
    static function get_personal_presente_momento($filtro = array())
    {
        try {
            $where_agentes = array();

            if (!empty($filtro['cod_depcia'])) {
                $where_agentes[] = "a.cod_depcia = " . toba::db('ctrl_asis')->quote($filtro['cod_depcia']);
            }
            if (!empty($filtro['legajo'])) {
                $where_agentes[] = "p.legajo = " . intval($filtro['legajo']);
            }
            if (!empty($filtro['id_catedra'])) {
                $where_agentes[] = intval($filtro['id_catedra']) . " = ANY(cu.id_catedras)";
            }

            $where_extra = "";
            if (count($where_agentes) > 0) {
                $where_extra = "WHERE " . implode(" AND ", $where_agentes);
            }

            $sql = "WITH presentes AS (
                        SELECT 
                            ua.legajo,
                            COUNT(*) AS cant_marcas,
                            MIN(ua.hora) AS primer_ingreso,
                            MAX(ua.hora) AS ultima_marca
                        FROM reloj.user_attendance ua
                        WHERE ua.fecha = CURRENT_DATE
                        GROUP BY ua.legajo
                        HAVING COUNT(*) % 2 != 0
                    ),
                    agentes_unicos AS (
                        SELECT DISTINCT ON (legajo)
                            legajo,
                            btrim(apellido) || ', ' || btrim(nombre) AS nombre_completo,
                            agrupamiento,
                            escalafon,
                            categoria,
                            cod_depcia
                        FROM reloj.agentes
                        ORDER BY legajo, ncargo ASC
                    ),
                    catedras_unicas AS (
                        SELECT 
                            ca.legajo,
                            string_agg(DISTINCT c.nombre_catedra, ', ') AS catedras,
                            array_agg(DISTINCT ca.id_catedra) AS id_catedras
                        FROM reloj.catedras_agentes ca
                        JOIN reloj.catedras c ON ca.id_catedra = c.id_catedra
                        GROUP BY ca.legajo
                    )
                    SELECT 
                        p.legajo,
                        p.cant_marcas,
                        to_char(p.primer_ingreso, 'HH24:MI') AS primer_ingreso,
                        to_char(p.ultima_marca, 'HH24:MI') AS ultima_marca,
                        COALESCE(a.nombre_completo, 'Legajo ' || p.legajo) AS agente,
                        a.agrupamiento,
                        a.escalafon,
                        a.categoria,
                        a.cod_depcia,
                        COALESCE(cu.catedras, 'Sin catedra asignada') AS catedra
                    FROM presentes p
                    LEFT JOIN agentes_unicos a ON p.legajo = a.legajo
                    LEFT JOIN catedras_unicas cu ON p.legajo = cu.legajo
                    $where_extra
                    ORDER BY agente ASC";

            return toba::db('ctrl_asis')->consultar($sql);
        } catch (Exception $e) {
            toba::logger()->error("Error en get_personal_presente_momento: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Retorna métricas cuantitativas del día de hoy:
     * - Total de agentes únicos que registraron marcas hoy
     * - Total de agentes presentes en el momento (fichadas impares)
     * - Total de agentes ya retirados (fichadas pares)
     */
    static function get_resumen_asistencia_hoy($filtro = array())
    {
        try {
            $where_agentes = array();
            if (!empty($filtro['cod_depcia'])) {
                $where_agentes[] = "a.cod_depcia = " . toba::db('ctrl_asis')->quote($filtro['cod_depcia']);
            }
            if (!empty($filtro['legajo'])) {
                $where_agentes[] = "ua.legajo = " . intval($filtro['legajo']);
            }

            $where_extra = "";
            if (count($where_agentes) > 0) {
                $where_extra = "AND " . implode(" AND ", $where_agentes);
            }

            $sql = "WITH agentes_unicos AS (
                        SELECT DISTINCT ON (legajo)
                            legajo,
                            cod_depcia
                        FROM reloj.agentes
                        ORDER BY legajo, ncargo ASC
                    ),
                    fichadas_hoy AS (
                        SELECT 
                            ua.legajo,
                            COUNT(*) AS cant_marcas
                        FROM reloj.user_attendance ua
                        LEFT JOIN agentes_unicos a ON ua.legajo = a.legajo
                        WHERE ua.fecha = CURRENT_DATE
                        $where_extra
                        GROUP BY ua.legajo
                    )
                    SELECT 
                        COUNT(*) AS total_ingresos_hoy,
                        COUNT(CASE WHEN cant_marcas % 2 != 0 THEN 1 END) AS total_presentes,
                        COUNT(CASE WHEN cant_marcas % 2 = 0 THEN 1 END) AS total_retirados
                    FROM fichadas_hoy";

            $res = toba::db('ctrl_asis')->consultar_fila($sql);
            return $res ? $res : array('total_ingresos_hoy' => 0, 'total_presentes' => 0, 'total_retirados' => 0);
        } catch (Exception $e) {
            toba::logger()->error("Error en get_resumen_asistencia_hoy: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Retorna el estado de asistencia actual para un agente específico hoy.
     */
    static function get_estado_agente_hoy($legajo)
    {
        try {
            $legajo = intval($legajo);
            $sql = "SELECT 
                        ua.legajo,
                        COUNT(*) AS cant_marcas,
                        to_char(MIN(ua.hora), 'HH24:MI') AS primer_ingreso,
                        to_char(MAX(ua.hora), 'HH24:MI') AS ultima_marca,
                        (COUNT(*) % 2 != 0) AS presente
                    FROM reloj.user_attendance ua
                    WHERE ua.fecha = CURRENT_DATE AND ua.legajo = $legajo
                    GROUP BY ua.legajo";

            return toba::db('ctrl_asis')->consultar_fila($sql);
        } catch (Exception $e) {
            toba::logger()->error("Error en get_estado_agente_hoy: " . $e->getMessage());
            return false;
        }
    }
}
