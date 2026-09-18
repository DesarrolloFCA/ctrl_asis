<?php
require_once 'datos/consultas_agentes.php';

if (!function_exists('h_latin1')) {
    function h_latin1($str) {
        if ($str === null || $str === '') {
            return '';
        }
        return htmlspecialchars((string)$str, ENT_QUOTES | ENT_SUBSTITUTE, 'ISO-8859-1');
    }
}

echo '<div class="logo" style="text-align: center; margin-bottom: 25px;">';
echo toba_recurso::imagen_proyecto('logo_grande.gif', true);
echo '</div>';

$es_admin = !empty($_SESSION['admin']);
$es_operador = !empty($_SESSION['operador']) || !empty($_SESSION['dependencia']);
$es_agente = !empty($_SESSION['agente']);

$filtro = array();
if (!$es_admin && !empty($_SESSION['dependencia'])) {
    $filtro['cod_depcia'] = $_SESSION['dependencia'];
}

$info_agente_actual = null;
if ($es_agente) {
    $legajo_actual = $_SESSION['agente'];
    $info_agente_actual = consultas_agentes::get_estado_agente_hoy($legajo_actual);

    // Si el usuario es únicamente agente, limitamos o contextualizamos a su dependencia
    if (!$es_admin && empty($_SESSION['dependencia'])) {
        try {
            $sql_dep = "SELECT cod_depcia FROM reloj.agentes WHERE legajo = " . intval($legajo_actual) . " LIMIT 1";
            $res_dep = toba::db('ctrl_asis')->consultar_fila($sql_dep);
            if (!empty($res_dep['cod_depcia'])) {
                $filtro['cod_depcia'] = $res_dep['cod_depcia'];
            }
        } catch (Exception $e) {
            // Continuar con filtro por defecto
        }
    }
}

$presentes = consultas_agentes::get_personal_presente_momento($filtro);
$resumen = consultas_agentes::get_resumen_asistencia_hoy($filtro);

$hora_actual = date('H:i');
$total_presentes = ($resumen && isset($resumen['total_presentes'])) ? intval($resumen['total_presentes']) : (is_array($presentes) ? count($presentes) : 0);
$total_ingresos = ($resumen && isset($resumen['total_ingresos_hoy'])) ? intval($resumen['total_ingresos_hoy']) : 0;
$total_retirados = ($resumen && isset($resumen['total_retirados'])) ? intval($resumen['total_retirados']) : 0;
?>

<style>
.dashboard-asis-container {
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    max-width: 1100px;
    margin: 0 auto 30px auto;
    padding: 0 15px;
    color: #333;
}

.asis-cards-grid {
    display: flex;
    gap: 20px;
    margin-bottom: 25px;
    flex-wrap: wrap;
}

.asis-card {
    flex: 1;
    min-width: 220px;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 18px 22px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.04);
    display: flex;
    flex-direction: column;
    justify-content: center;
    position: relative;
    overflow: hidden;
}

.asis-card-presentes {
    border-left: 5px solid #22c55e;
    background: linear-gradient(135deg, #ffffff 0%, #f0fdf4 100%);
}

.asis-card-ingresos {
    border-left: 5px solid #3b82f6;
    background: linear-gradient(135deg, #ffffff 0%, #eff6ff 100%);
}

.asis-card-retirados {
    border-left: 5px solid #94a3b8;
    background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
}

.asis-card-title {
    font-size: 13px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #64748b;
    margin-bottom: 6px;
    display: flex;
    align-items: center;
    gap: 6px;
}

.asis-card-value {
    font-size: 32px;
    font-weight: 700;
    color: #1e293b;
    line-height: 1;
}

.asis-card-subtitle {
    font-size: 12px;
    color: #94a3b8;
    margin-top: 5px;
}

.pulse-dot {
    display: inline-block;
    width: 9px;
    height: 9px;
    border-radius: 50%;
    background-color: #22c55e;
    box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7);
    animation: pulse-green 2s infinite;
}

@keyframes pulse-green {
    0% {
        transform: scale(0.95);
        box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7);
    }
    70% {
        transform: scale(1);
        box-shadow: 0 0 0 6px rgba(34, 197, 94, 0);
    }
    100% {
        transform: scale(0.95);
        box-shadow: 0 0 0 0 rgba(34, 197, 94, 0);
    }
}

.personal-status-banner {
    background-color: #f8fafc;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    padding: 12px 20px;
    margin-bottom: 25px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.personal-status-presente {
    background-color: #f0fdf4;
    border-color: #86efac;
    color: #166534;
}

.personal-status-ausente {
    background-color: #fefce8;
    border-color: #fde047;
    color: #854d0e;
}

.asis-panel {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    overflow: hidden;
}

.asis-panel-header {
    padding: 16px 22px;
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 12px;
}

.asis-panel-title {
    font-size: 16px;
    font-weight: 700;
    color: #1e293b;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 8px;
}

.asis-controls {
    display: flex;
    gap: 10px;
    align-items: center;
}

.asis-search-input {
    padding: 7px 14px;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    font-size: 13px;
    width: 260px;
    outline: none;
    transition: all 0.2s;
}

.asis-search-input:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
}

.asis-btn-refresh {
    background: #ffffff;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    padding: 7px 12px;
    font-size: 13px;
    font-weight: 500;
    color: #475569;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    transition: all 0.2s;
}

.asis-btn-refresh:hover {
    background: #f1f5f9;
    color: #1e293b;
    border-color: #94a3b8;
}

.asis-table-wrapper {
    max-height: 480px;
    overflow-y: auto;
    overflow-x: auto;
}

.asis-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
    text-align: left;
}

.asis-table th {
    background: #f1f5f9;
    color: #475569;
    font-weight: 600;
    padding: 11px 16px;
    position: sticky;
    top: 0;
    z-index: 10;
    border-bottom: 2px solid #e2e8f0;
    white-space: nowrap;
}

.asis-table td {
    padding: 10px 16px;
    border-bottom: 1px solid #f1f5f9;
    color: #334155;
    vertical-align: middle;
}

.asis-table tbody tr:hover {
    background-color: #f8fafc;
}

.badge-legajo {
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
    font-weight: 600;
    color: #475569;
    background: #f1f5f9;
    padding: 3px 7px;
    border-radius: 4px;
    border: 1px solid #e2e8f0;
    font-size: 12px;
}

.badge-estado-presente {
    background: #dcfce7;
    color: #15803d;
    font-weight: 600;
    padding: 3px 8px;
    border-radius: 12px;
    font-size: 11px;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.badge-agrupamiento {
    background: #e0e7ff;
    color: #3730a3;
    font-weight: 500;
    padding: 2px 7px;
    border-radius: 4px;
    font-size: 11px;
}

.asis-empty-state {
    padding: 45px 20px;
    text-align: center;
    color: #64748b;
    font-size: 14px;
}

.asis-footer-info {
    padding: 10px 22px;
    background: #f8fafc;
    border-top: 1px solid #e2e8f0;
    font-size: 12px;
    color: #64748b;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
</style>

<div class="dashboard-asis-container">

    <?php if ($es_agente && $info_agente_actual): ?>
        <?php if (!empty($info_agente_actual['presente']) && $info_agente_actual['presente'] === true): ?>
            <div class="personal-status-banner personal-status-presente">
                <div>
                    <strong>Tu Estado Hoy:</strong> Est&aacute;s registrado como <strong>Presente</strong>.
                    Primer ingreso registrado a las <strong><?php echo h_latin1($info_agente_actual['primer_ingreso']); ?> hs</strong>
                    <?php if ($info_agente_actual['primer_ingreso'] != $info_agente_actual['ultima_marca']): ?>
                        (&Uacute;ltima marca: <?php echo h_latin1($info_agente_actual['ultima_marca']); ?> hs)
                    <?php endif; ?>
                </div>
                <span class="badge-estado-presente"><span class="pulse-dot"></span> En el establecimiento</span>
            </div>
        <?php else: ?>
            <div class="personal-status-banner personal-status-ausente">
                <div>
                    <strong>Tu Estado Hoy:</strong> No figuras actualmente en el edificio.
                    <?php if (!empty($info_agente_actual['cant_marcas'])): ?>
                        (&Uacute;ltima marca registrada de salida a las <strong><?php echo h_latin1($info_agente_actual['ultima_marca']); ?> hs</strong>)
                    <?php else: ?>
                        (A&uacute;n no registras marcaciones en el reloj para el d&iacute;a de hoy)
                    <?php endif; ?>
                </div>
                <span style="font-size: 12px; color: #854d0e;">Sin ingreso activo</span>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <!-- M&eacute;tricas Resumen -->
    <div class="asis-cards-grid">
        <div class="asis-card asis-card-presentes">
            <div class="asis-card-title">
                <span class="pulse-dot"></span> Presentes en el Momento
            </div>
            <div class="asis-card-value" id="card-total-presentes"><?php echo $total_presentes; ?></div>
            <div class="asis-card-subtitle">Fichada activa en reloj</div>
        </div>

        <div class="asis-card asis-card-ingresos">
            <div class="asis-card-title">
                Total Ingresos Hoy
            </div>
            <div class="asis-card-value"><?php echo $total_ingresos; ?></div>
            <div class="asis-card-subtitle">Personas que concurrieron hoy</div>
        </div>

        <div class="asis-card asis-card-retirados">
            <div class="asis-card-title">
                Ya Retirados
            </div>
            <div class="asis-card-value"><?php echo $total_retirados; ?></div>
            <div class="asis-card-subtitle">Salida marcada en el reloj</div>
        </div>
    </div>

    <!-- Panel / Cuadro de Personas Presentes -->
    <div class="asis-panel">
        <div class="asis-panel-header">
            <h3 class="asis-panel-title">
                Personal Presente en la Instituci&oacute;n
            </h3>
            <div class="asis-controls">
                <input type="text" id="buscador-asis" class="asis-search-input" placeholder="Buscar por agente, legajo o &aacute;rea..." onkeyup="filtrarTablaPresentes()">
                <button type="button" class="asis-btn-refresh" onclick="window.location.reload();" title="Refrescar marcaciones">
                    Actualizar
                </button>
            </div>
        </div>

        <div class="asis-table-wrapper">
            <?php if ($presentes === false): ?>
                <div class="asis-empty-state" style="color: #b91c1c;">
                    No se pudo consultar la informaci&oacute;n de relojes en este momento. Por favor, reintente m&aacute;s tarde.
                </div>
            <?php elseif (empty($presentes)): ?>
                <div class="asis-empty-state">
                    No se registran personas presentes en este momento.
                </div>
            <?php else: ?>
                <table class="asis-table" id="tabla-presentes">
                    <thead>
                        <tr>
                            <th style="width: 90px;">Legajo</th>
                            <th>Agente (Apellido y Nombre)</th>
                            <th>C&aacute;tedra / &Aacute;rea</th>
                            <th style="width: 110px;">Agrupamiento</th>
                            <th style="width: 120px; text-align: center;">Primer Ingreso</th>
                            <th style="width: 120px; text-align: center;">&Uacute;ltima Fichada</th>
                            <th style="width: 110px; text-align: center;">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($presentes as $p): ?>
                            <tr class="fila-presente">
                                <td>
                                    <span class="badge-legajo"><?php echo h_latin1($p['legajo']); ?></span>
                                </td>
                                <td>
                                    <strong style="color: #0f172a;"><?php echo h_latin1($p['agente']); ?></strong>
                                </td>
                                <td>
                                    <span style="color: #475569;"><?php echo h_latin1($p['catedra']); ?></span>
                                </td>
                                <td>
                                    <?php if (!empty($p['agrupamiento'])): ?>
                                        <span class="badge-agrupamiento"><?php echo h_latin1($p['agrupamiento']); ?></span>
                                    <?php else: ?>
                                        <span style="color: #94a3b8;">-</span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: center; font-weight: 500;">
                                    <?php echo h_latin1($p['primer_ingreso']); ?> hs
                                </td>
                                <td style="text-align: center; font-weight: 500;">
                                    <?php echo h_latin1($p['ultima_marca']); ?> hs
                                </td>
                                <td style="text-align: center;">
                                    <span class="badge-estado-presente">
                                        <span class="pulse-dot"></span> Presente
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div id="sin-coincidencias" class="asis-empty-state" style="display: none;">
                    No se encontraron coincidencias para la b&uacute;squeda.
                </div>
            <?php endif; ?>
        </div>

        <div class="asis-footer-info">
            <span id="contador-visibles">
                Mostrando <?php echo $total_presentes; ?> personas presentes
            </span>
            <span>
                &Uacute;ltima actualizaci&oacute;n: <strong><?php echo $hora_actual; ?> hs</strong>
            </span>
        </div>
    </div>

</div>

<script>
function normalizarTexto(txt) {
    if (!txt) return "";
    return txt.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "").trim();
}

function filtrarTablaPresentes() {
    var input = document.getElementById('buscador-asis');
    var filter = normalizarTexto(input.value);
    var table = document.getElementById('tabla-presentes');
    if (!table) return;

    var tr = table.getElementsByClassName('fila-presente');
    var visibles = 0;

    for (var i = 0; i < tr.length; i++) {
        var text = normalizarTexto(tr[i].textContent || tr[i].innerText);
        if (text.indexOf(filter) > -1) {
            tr[i].style.display = '';
            visibles++;
        } else {
            tr[i].style.display = 'none';
        }
    }

    var noCoincidencias = document.getElementById('sin-coincidencias');
    if (noCoincidencias) {
        noCoincidencias.style.display = (visibles === 0) ? 'block' : 'none';
    }

    var contador = document.getElementById('contador-visibles');
    if (contador) {
        contador.textContent = 'Mostrando ' + visibles + ' personas presentes';
    }
}
</script>