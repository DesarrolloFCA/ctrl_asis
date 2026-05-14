<?php
class pant_login extends toba_ei_pantalla
{
	function generar_layout()
	{
		echo "
		<style type='text/css'>
			.encabezado,
			#barra_superior,
			#barra-superior,
			.barra-superior-login,
			#enc-logo,
			.enc-version,
			.login-titulo {
				display: none !important;
			}
		</style>";

		echo "<div style='text-align: center; margin-top: 20px; margin-bottom: 30px;'>";
		echo toba_recurso::imagen_proyecto('logo_grande.gif', true);
		echo "</div>";

		echo "<div style='text-align: center; margin-top: 30px;'>";
		echo "<div style='display: inline-block; text-align: left;'>";
		if ($this->existe_dependencia('seleccion_usuario')) {
			$this->dep('seleccion_usuario')->generar_html();
		}
		echo '<div>';
		if ($this->existe_dependencia('datos')) {
			echo "<div style='float:left;'>";
			$this->dep('datos')->generar_html();
			echo '</div>';
		}
		if ($this->existe_dependencia('openid')) {
			echo "<div style='padding-left: 30px; padding-right: 30px;float:right;'>";
			$this->dep('openid')->generar_html();
			echo '</div>';
		}
		if ($this->existe_dependencia('cas')) {
			echo "<div style='padding-left: 30px; padding-right: 30px;float:right;'>";
			$this->dep('cas')->generar_html();
			echo '</div>';
		}
		echo '</div>';
		echo "<div style='clear: both;'></div>";
		echo "</div>";
		echo "</div>";
	}
}

?>
