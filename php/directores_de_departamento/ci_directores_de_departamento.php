<?php
class ci_directores_de_departamento extends ctrl_asis_ci
{
	protected $s__datos_filtro;
	protected $s__datos;
	protected $s__bandera;


	//---- Filtro -----------------------------------------------------------------------

	function conf__filtro(toba_ei_formulario $filtro)
	{
		if (isset($this->s__datos_filtro)) {
			$filtro->set_datos($this->s__datos_filtro);
		}
	}

	function evt__filtro__filtrar($datos)
	{
		$this->s__datos_filtro = $datos;
	}

	function evt__filtro__cancelar()
	{
		unset($this->s__datos_filtro);
	}

	//---- Cuadro -----------------------------------------------------------------------

	function conf__cuadro(toba_ei_cuadro $cuadro)
	{
		if (isset($this->s__datos_filtro)) {
			$cuadro->set_datos($this->dep('datos')->tabla('departamento_director')->get_listado($this->s__datos_filtro));
		} else {
			$cuadro->set_datos($this->dep('datos')->tabla('departamento_director')->get_listado());
		}
		
	/*	$reg = count($dpto);
		ei_arbol($dpto);
		for($i=0; $i<$reg ; $i++){
			$legajo = $dpto [$i]['legajo_dir'];
			$id_dpto= $dpto [$i]['id_departamento'];
		
			if (isset($legajo)){
			$agente = $this->dep('mapuche')->get_legajos_fca_cargos($legajo);
			
			} else {
			$agente[$i]['descripcion'] = '';
			}
			
			$sql = "SELECT departamento FROM reloj.departamentos
					WHERE id_departamento = $id_dpto";
			$depar=toba::db('ctrl_asis')->consultar($sql);


			$dpto [$i]['departamento']= $depar[0]['departamento'];
			$dpto[$i]['ayn'] = $agente[0]['descripcion'];
			

		}*/
		
		//$cuadro->set_datos($dpto);
	}

	function evt__cuadro__seleccion($datos)
	{
		
		$this->dep('datos')->cargar($datos);
		$this->set_pantalla('pant_edicion');
	}

	//---- Formulario -------------------------------------------------------------------

	function conf__formulario(toba_ei_formulario $form)
	{
		if ($this->dep('datos')->esta_cargada()) {
			$form->set_datos($this->dep('datos')->tabla('departamento_director')->get());
		} else {
			$this->s__bandera = true;
			$this->pantalla()->eliminar_evento('eliminar');

		}
	}

	function evt__formulario__modificacion($datos)
	{
			$this->s__datos =$datos;
			$this->dep('datos')->tabla('departamento_director')->set($datos);
	}

	function resetear()
	{
		$this->dep('datos')->resetear();
		$this->set_pantalla('pant_seleccion');
	}

	//---- EVENTOS CI -------------------------------------------------------------------

	function evt__agregar()
	{
		$this->set_pantalla('pant_edicion');
	}

	function evt__volver()
	{
		$this->resetear();
	}

	function evt__eliminar()
	{
		$this->dep('datos')->eliminar_todo();
		$this->resetear();
	}

	function evt__guardar()
	{
		if ($this->s__bandera){
			$datos = $this->s__datos;
			$id_departamento = $datos['id_departamento'];
			$legajo_dir=$datos['legajo_dir'];
			$sql = " INSERT INTO reloj.departamento_director(
			id_departamento, legajo_dir)
			VALUES ($id_departamento, $legajo_dir);";
			toba::db('ctrl_asis')->ejecutar($sql);
		} else {
		
		$this->dep('datos')->sincronizar();
		}
		$this->resetear();
	}

}

?>