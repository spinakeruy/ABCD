<?php
	/*
* @file        fst.php
* @description Simplified Type of Records definition using a basic editable table
* @author      Refactored by Roger C. Guilherme
* @date        2025-11-18
* 
* CHANGE LOG
* 2021-02-08 fho4abcd Remove code in comment & languaje->language
* 2021-02-09 fho4abcd Original name for dhtmlX.js
* 2022-01-20 fho4abcd div-helper+ cancel button
* 2024-03-28 fho4abcd stylesheet from assets + new look
* 2024-11-15 rogercgui Refactored and eliminated the use of dhtmlx
*/


	session_start();
if (!isset($_SESSION["permiso"])) {
	header("Location: ../common/error_page.php");
	die;
}
include("../common/get_post.php");
include("../config.php");
$lang = $_SESSION["lang"];
include("../lang/dbadmin.php");

// Verifica permissões
if (isset($arrHttp["Opcion"]) && $arrHttp["Opcion"] != "new") {
	if (!isset($_SESSION["permiso"]["CENTRAL_ALL"]) and !isset($_SESSION["permiso"][$arrHttp["base"] . "CENTRAL_MODIFYDEF"])  and !isset($_SESSION["permiso"][$arrHttp["base"] . "_CENTRAL_MODIFYDEF"]) and !isset($_SESSION["permiso"][$arrHttp["base"] . "_CENTRAL_ALL"])) {
		header("Location: ../common/error_page.php");
		die;
	}
} else {
	$arrHttp["Opcion"] = "new";
	if (!isset($_SESSION["permiso"]["CENTRAL_ALL"]) and !isset($_SESSION["permiso"]["CENTRAL_CRDB"]) and !isset($_SESSION["permiso"][$arrHttp["base"] . "_CENTRAL_CRDB"])) {
		header("Location: ../common/error_page.php");
		die;
	}
}

// Define se é update ou new baseado na existência do arquivo
$fst_file = $db_path . $arrHttp["base"] . "/data/" . $arrHttp["base"] . ".fst";
if (file_exists($fst_file)) {
	$arrHttp["Opcion"] = "update";
} else {
	$arrHttp["Opcion"] = "new";
}

include("../common/header.php");

// Prepara as opções do Select usando as traduções do sistema
// Isso garante que o texto seja "0 por linha", etc.
$tech_options_html = '<option value=""></option>';
for ($i = 0; $i <= 8; $i++) {
	$label = isset($msgstr["fst_$i"]) ? $msgstr["fst_$i"] : "$i - Indefinido";
	$tech_options_html .= "<option value=\"$i\">$label</option>";
}
?>


<body>
	<?php
	if (isset($arrHttp["encabezado"])) {
		include("../common/institutional_info.php");
	}
	?>

	<div class="sectionInfo">
		<div class="breadcrumb"><?php echo $msgstr["fst"] . ": " . $arrHttp["base"] ?></div>
		<div class="actions">
			<?php
			if ($arrHttp["Opcion"] == "new") {
				$backtoscript = "../dbadmin/fdt.php?Opcion=new";
				include "../common/inc_back.php";
				include "../common/inc_cancel.php";
			} else {
				$backtoscript = "../dbadmin/menu_modificardb.php";
				include "../common/inc_back.php";
				include("../common/inc_home.php");
			}
			?>
		</div>
		<div class="spacer">&#160;</div>
	</div>

	<?php include "../common/inc_div-helper.php"; ?>

	<div class="middle form">
		<div class="formContent">

			<div class="layout-wrapper">

				<div class="col-editor">

					<div class="helper-box">
						<h5><i class="fas fa-magic"></i> <?php echo $msgstr["fst_assist"]; ?></h5>
						<div class="form-row-custom">
							<div class="form-group-custom" style="width: 60px;">
								<label>Tag (ID)</label>
								<input type="text" id="new_id" class="text-input" placeholder="100">
							</div>

							<div class="form-group-custom" style="flex-grow: 0.5;">
								<label><?php echo $msgstr["tech"]; ?></label>
								<select id="new_tech" class="text-input">
									<?php echo $tech_options_html; ?>
								</select>
							</div>

							<div class="form-group-custom" style="width: 100px;">
								<label><?php echo $msgstr["prefix"]; ?></label>
								<input type="text" id="new_prefix" class="text-input" placeholder="Ex: TW_">
							</div>

							<div class="form-group-custom" style="width: 100px;">
								<label><?php echo $msgstr["ft_f"]; ?></label>
								<input type="text" id="new_field" class="text-input" placeholder="Ex: v245^a">
							</div>

							<div class="form-group-custom">
								<button type="button" class="bt bt-blue" onclick="insertHelperRow()">
									<i class="fas fa-plus"></i> <?php echo $msgstr["insert"]; ?>
								</button>
							</div>
						</div>
					</div>

					<form name="fst" id="fstForm" method="post" onsubmit="return false;">
						<div class="row">
							<div class="col-md-12">
								<table class="table striped table-fst" id="fstTable">
									<thead>
										<tr>
											<th width="10%">ID</th>
											<th width="30%"><?php echo $msgstr["itech"] ?></th>
											<th width="45%"><?php echo $msgstr["extrpft"] ?></th>
											<th width="15%" style="text-align: center;"><?php echo $msgstr["actions"] ?? "Ações" ?></th>
										</tr>
									</thead>
									<tbody id="fstBody">
										<?php
										$lines = array();
										if ($arrHttp["Opcion"] == "update" && file_exists($fst_file)) {
											$lines = file($fst_file);
										} elseif (isset($_SESSION["FST"])) {
											$lines = explode("\n", $_SESSION["FST"]);
										}

										foreach ($lines as $line) {
											$line = trim($line);
											if (empty($line)) continue;

											$parts = explode(" ", $line, 3);
											$id = isset($parts[0]) ? $parts[0] : "";
											$tech = isset($parts[1]) ? $parts[1] : "";
											$fmt = isset($parts[2]) ? $parts[2] : "";

											echo "<tr class='fst-row'>";
											echo "<td><input type='text' name='row_id' value='" . htmlspecialchars($id) . "'></td>";
											echo "<td><select name='row_tech' data-selected='$tech'>" . $tech_options_html . "</select></td>";
											echo "<td><textarea name='row_fmt' rows='1'>" . htmlspecialchars($fmt) . "</textarea></td>";
											echo "<td class='actions-cell'>";
											echo "<button type='button' class='bt bt-gray' onclick='moveRow(this, -1)'><i class='fas fa-arrow-up'></i></button>";
											echo "<button type='button' class='bt bt-gray' onclick='moveRow(this, 1)'><i class='fas fa-arrow-down'></i></button>";
											echo "<button type='button' class='bt bt-blue' onclick='duplicateRow(this)'><i class='far fa-copy'></i></button>";
											echo "<button type='button' class='bt bt-red' onclick='deleteRow(this)'><i class='fas fa-trash-alt'></i></button>";
											echo "</td>";
											echo "</tr>";
										}
										?>
									</tbody>
								</table>

								<div style="margin-top: 10px;">
									<button type="button" class="bt bt-gray" onclick="addEmptyRow()">
										<i class="fas fa-plus"></i> <?php echo $msgstr["addrowbef"] ?? "Adicionar Linha" ?>
									</button>
								</div>
							</div>
						</div>

						<div style="margin-top: 20px; border-top: 1px solid #eee; padding-top: 10px;">
							<?php if ($arrHttp["Opcion"] != "new") { ?>
								<label><?php echo $msgstr["testmfn"]; ?></label>
								<input type="text" size="5" name="MfnTest" id="MfnTest">
								<a class="bt bt-blue" href="javascript:Test()"><?php echo $msgstr["test"]; ?></a>
							<?php } ?>

							<a class="bt bt-green" href="javascript:Enviar()"><i class="fas fa-save"></i> <?php echo $msgstr["update"] ?></a>
						</div>
					</form>
				</div>

				<div class="col-help">
					<div class="sectionInfo" style="margin-bottom: 5px;">
						<div class="breadcrumb">FDT: <?php echo $arrHttp["base"] ?></div>
					</div>
					<iframe src="fdt_leer.php?Opcion=<?php echo $arrHttp["Opcion"] ?>&base=<?php echo $arrHttp["base"] ?>" class="iframe-help" scrolling="yes" name="fdt"></iframe>
				</div>

			</div>
			<form name="forma1" action="fst_update.php" method="post">
				<input type="hidden" name="ValorCapturado">
				<input type="hidden" name="desc">
				<input type="hidden" name="Opcion" value="<?php echo $arrHttp["Opcion"] ?>">
				<input type="hidden" name="base" value="<?php echo $arrHttp["base"] ?>">
				<?php if (isset($arrHttp["encabezado"])) echo "<input type=hidden name=encabezado value=S>"; ?>
			</form>

			<form name="test" action="fst_test.php" method="post" target="FST_Test">
				<input type="hidden" name="ValorCapturado">
				<input type="hidden" name="desc">
				<input type="hidden" name="Mfn">
				<input type="hidden" name="Opcion" value="<?php echo $arrHttp["Opcion"] ?>">
				<input type="hidden" name="base" value="<?php echo $arrHttp["base"] ?>">
			</form>

		</div>
	</div>

	<script>
		// Guarda o HTML das opções gerado pelo PHP para uso no JS
		const techSelectOptions = `<?php echo $tech_options_html; ?>`;

		// Inicializa os selects que já vieram do PHP
		document.addEventListener("DOMContentLoaded", function() {
			var selects = document.querySelectorAll("select[name='row_tech']");
			selects.forEach(function(sel) {
				var val = sel.getAttribute("data-selected");
				if (val) sel.value = val;
			});
		});

		function insertHelperRow() {
			var id = document.getElementById('new_id').value.trim();
			var tech = document.getElementById('new_tech').value;
			var prefix = document.getElementById('new_prefix').value.trim();
			var field = document.getElementById('new_field').value.trim();

			if (id === "" || field === "") {
				alert("<?php echo $msgstr['err_idfield'] ?? 'Preencha ID e Campo'; ?>");
				return;
			}

			var fmt = "";

			// Lógica de geração baseada na técnica
			switch (tech) {
				case "0": // Campo Inteiro: mpu,(|PREFIX_|vFIELD|%|/)/
					if (prefix !== "") {
						fmt = `mpu,(|${prefix}|${field}|%|/)/`;
					} else {
						fmt = `mpu,(${field}|%|/)/`;
					}
					break;
				case "8": // Palavras ABCD: mpu,'/PREFIX_/' (vFIELD|%|/),
					if (prefix !== "") {
						fmt = `mpu,'/${prefix}/' (${field}|%|/),`;
					} else {
						fmt = `mpu,(${field}|%|/),`;
					}
					break;
				case "5": // Repetitivo: 'PREFIX', mpu, (vFIELD/)
					if (prefix !== "") {
						fmt = `'${prefix}', mpu, (${field}/)`;
					} else {
						fmt = `mpu, (${field}/)`;
					}
					break;
				case "1": // Subcampo
					fmt = `mpu, ${field}`;
					if (prefix) fmt = `'${prefix}' ` + fmt;
					break;
				default:
					// Padrão genérico
					fmt = `mpu, ${field}`;
					if (prefix) fmt = `'${prefix}' ` + fmt;
					break;
			}

			addRow(id, tech, fmt);

			// Limpar campos do assistente
			document.getElementById('new_prefix').value = "";
			document.getElementById('new_field').value = "";
		}

		function addEmptyRow() {
			addRow("", "0", "");
		}

		function addRow(id, tech, fmt) {
			var tbody = document.getElementById("fstBody");
			var tr = document.createElement("tr");
			tr.className = "fst-row";

			tr.innerHTML = `
            <td><input type="text" name="row_id" value="${id}"></td>
            <td><select name="row_tech">${techSelectOptions}</select></td>
            <td><textarea name="row_fmt" rows="1">${fmt}</textarea></td>
            <td class="actions-cell">
                <button type="button" class="bt bt-gray" onclick="moveRow(this, -1)"><i class="fas fa-arrow-up"></i></button>
                <button type="button" class="bt bt-gray" onclick="moveRow(this, 1)"><i class="fas fa-arrow-down"></i></button>
                <button type="button" class="bt bt-blue" onclick="duplicateRow(this)"><i class="far fa-copy"></i></button>
                <button type="button" class="bt bt-red" onclick="deleteRow(this)"><i class="fas fa-trash-alt"></i></button>
            </td>
        `;

			tbody.appendChild(tr);

			// Define o valor do select
			var select = tr.querySelector("select[name='row_tech']");
			if (select) select.value = tech;
		}

		function deleteRow(btn) {
			if (confirm("<?php echo $msgstr['are_you_sure'] ?? 'Tem certeza?'; ?>")) {
				var row = btn.closest("tr");
				row.remove();
			}
		}

		function duplicateRow(btn) {
			var row = btn.closest("tr");
			var clone = row.cloneNode(true);

			// Preserva valores editados
			var origId = row.querySelector("input[name='row_id']").value;
			var origTech = row.querySelector("select[name='row_tech']").value;
			var origFmt = row.querySelector("textarea[name='row_fmt']").value;

			clone.querySelector("input[name='row_id']").value = origId;
			clone.querySelector("textarea[name='row_fmt']").value = origFmt;

			// Reinsere o HTML do select para garantir integridade e define o valor
			var cloneSelect = clone.querySelector("select[name='row_tech']");
			cloneSelect.innerHTML = techSelectOptions;
			cloneSelect.value = origTech;

			row.parentNode.insertBefore(clone, row.nextSibling);
		}

		function moveRow(btn, direction) {
			var row = btn.closest("tr");
			var tbody = row.parentNode;
			if (direction === -1 && row.previousElementSibling) {
				tbody.insertBefore(row, row.previousElementSibling);
			} else if (direction === 1 && row.nextElementSibling) {
				tbody.insertBefore(row.nextElementSibling, row);
			}
		}

		function coletarDados() {
			var rows = document.querySelectorAll(".fst-row");
			var data = [];

			rows.forEach(function(row) {
				var id = row.querySelector("input[name='row_id']").value.trim();
				var tech = row.querySelector("select[name='row_tech']").value;
				var fmt = row.querySelector("textarea[name='row_fmt']").value;

				// Remove quebras de linha do formato
				fmt = fmt.replace(/(\r\n|\n|\r)/gm, " ");

				if (id !== "") {
					data.push(id + " " + tech + " " + fmt);
				}
			});

			return data.join("\n");
		}

		function Enviar() {
			var content = coletarDados();
			document.forma1.ValorCapturado.value = content;
			document.forma1.submit();
		}

		function Test() {
			var mfn = document.getElementById("MfnTest").value.trim();
			if (mfn === "") {
				alert("<?php echo $msgstr['mismfn'] ?? 'Indique um MFN'; ?>");
				return;
			}
			document.test.Mfn.value = mfn;
			document.test.ValorCapturado.value = coletarDados();
			var msgwin = window.open("", "FST_Test", "width=800,height=600,scrollbars=yes,resizable=yes");
			msgwin.focus();
			document.test.submit();
		}
	</script>

	<?php include("../common/footer.php"); ?>
</body>