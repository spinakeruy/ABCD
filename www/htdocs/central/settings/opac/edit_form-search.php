<?php
/*
* @file        edit_form-search.php
* @author      Guilda Ascencio
* @author      Roger Craveiro Guilherme
* @date        2022-02-10
* @description File to edit the free search form configuration
*
* CHANGE LOG:
 * 2023-03-05 rogercgui Adds the variable $actparfolder;
 * 2023-03-05 rogercgui Fixes bug in the absence of the file camposbusqueda.tab;
 * 2025-11-02 rogercgui Applies mb_detect_encoding to all file() calls to fix accent issues.
 * 2025-11-09 rogercgui Removes local file_get_contents_utf8 (moved to opac_functions.php)
 * 2025-11-09 rogercgui Standardizes help table inside an accordion
 */

include("conf_opac_top.php");
$wiki_help = "OPAC-ABCD_Configuraci%C3%B3n_de_bases_de_datos#B.C3.BAsqueda_Libre";
include "../../common/inc_div-helper.php";

// A função file_get_contents_utf8() foi movida para opac_functions.php (incluído via conf_opac_top.php)

?>

<?php if ($_REQUEST["base"] == "META") {  ?>
	<script>
		var idPage = "metasearch";
	</script>
<?php } else { ?>
	<script>
		var idPage = "db_configuration";
	</script>
<?php } ?>


<div class="middle form row m-0">
	<div class="formContent col-2 m-2 p-0">
		<?php include("conf_opac_menu.php"); ?>
	</div>
	<div class="formContent col-9 m-2">
		<?php include("menu_dbbar.php");  ?>
		<?php if (isset($_REQUEST['o_conf']) && $_REQUEST['o_conf'] == "libre") { ?>
			<h3><?php echo $msgstr["free_search"]; ?></h3>
		<?php } else { ?>
			<h3><?php echo $msgstr["buscar_a"]; ?></h3>

		<?php } ?>


		<?php
		//foreach ($_REQUEST as $var=>$value) echo "$var=$value<br>";


		$db_path = $_SESSION["db_path"];
		$base = isset($_REQUEST["base"]) ? $_REQUEST["base"] : "";
		$update_message = ""; // Variável para feedback

		if (isset($_REQUEST["Opcion"]) and $_REQUEST["Opcion"] == "Guardar") {

			$archivo_conf = $db_path . $_REQUEST['base'] . "/opac/$lang/" . $_REQUEST["file"];
			$cod_idioma = [];
			$nom_idioma = [];

			foreach ($_REQUEST as $var => $value) {
				if (trim($value) != "") {
					$code = explode("_", $var);
					if ($code[0] == "conf") {
						if ($code[1] == "lc") {
							if (!isset($cod_idioma[$code[2]])) {
								$cod_idioma[$code[2]] = $value;
							}
						} else {

							if (!isset($nom_idioma[$code[2]])) {
								$nom_idioma[$code[2]] = $value;
							}
						}
					}
				}
			}


			$fout = fopen($archivo_conf, "w");
			foreach ($cod_idioma as $key => $value) {
				// Evita salvar linhas vazias se o usuário apagar
				if (trim($value) == "" && trim($nom_idioma[$key]) == "") {
					continue;
				}
				fwrite($fout, $value . "|" . $nom_idioma[$key] . "\n");
			}
			fclose($fout);

			$update_message = "<p class=\"color-green\"><strong>" . $archivo_conf . " " . $msgstr["updated"] . "</strong></p>";
		}

		// Exibe a mensagem de sucesso/erro AQUI, dentro do layout
		if (!empty($update_message)) echo $update_message;


		if (!isset($_REQUEST["Opcion"]) or $_REQUEST["Opcion"] != "Guardar") {

			//DATABASES
			$archivo = $db_path . "opac_conf/" . $lang . "/bases.dat";

			// --- CORREÇÃO DE ENCODING (TARGET 1) ---
			$fp = file_get_contents_utf8($archivo);

			if ($_REQUEST["base"] == "META") {
				Entrada("MetaSearch", $msgstr["metasearch"], $lang, $_REQUEST['o_conf'] . ".tab", "META");
			} else {
				if ($fp) { // Verifica se o arquivo foi lido
					foreach ($fp as $value) {
						if (trim($value) != "") {
							$x = explode('|', $value);
							if ($_REQUEST["base"] != $x[0])  continue;
							Entrada(trim($x[0]), trim($x[1]), $lang, trim($x[0]) . "_" . $_REQUEST['o_conf'] . ".tab", $x[0]);
						}
					}
				}
			}

		?>
	</div>
<?php
		}

?>
</div>
</div>


<?php

function Entrada($iD, $name, $lang, $file, $base)
{
	global $msgstr, $db_path, $archivo_conf;

	echo "<strong>" . htmlspecialchars($name);
	if ($base != "" and $base != "META") echo " (" . htmlspecialchars($base) . ")";
	echo "</strong>";
	echo "<div  id='$iD' >\n";
	echo "<div style=\"display: flex;\">";
	$cuenta = 0;
	$file_fieldsearch = $db_path . $base . "/pfts/" . $_REQUEST["lang"] . "/camposbusqueda.tab";

	// --- CORREÇÃO DE ENCODING (TARGET 2) ---
	$fp_campos_base = file_get_contents_utf8($file_fieldsearch);
	if ($fp_campos_base) {
		$fp_campos[$base] = $fp_campos_base;
	} else {
		// Fallback para 'en' se o idioma atual não existir
		$file_fieldsearch_en = $db_path . $base . "/pfts/en/camposbusqueda.tab";
		$fp_campos_base_en = file_get_contents_utf8($file_fieldsearch_en);
		if ($fp_campos_base_en) {
			$fp_campos[$base] = $fp_campos_base_en;
		} else {
			$fp_campos[$base] = [];
		}
	}
	// --- FIM CORREÇÃO ---

	if ($base != "" and $base != "META") {
		$cuenta = count($fp_campos[$base]);
	}

	if ($base != "" and $base == "META") {

		// --- CORREÇÃO DE ENCODING (TARGET 3) ---
		$file_bases_dat = $db_path . "opac_conf/" . $_REQUEST["lang"] . "/bases.dat";
		$fpbases = file_get_contents_utf8($file_bases_dat);
		// --- FIM CORREÇÃO ---

		if ($fpbases) {
			foreach ($fpbases as $value) {
				$value = trim($value);
				if ($value == "") continue;

				$v = explode('|', $value);
				$b_0 = $v[0];

				// --- CORREÇÃO DE ENCODING (TARGET 4) ---
				$file_fieldsearch_meta = $db_path . $b_0 . "/pfts/" . $_REQUEST["lang"] . "/camposbusqueda.tab";
				$fpbb = file_get_contents_utf8($file_fieldsearch_meta);
				// --- FIM CORREÇÃO ---

				if ($fpbb) {
					foreach ($fpbb as $campos) {
						if (trim($campos) != "") {
							$fp_campos[$b_0][] = $campos;
						}
					}
				} else {
					// Fallback para 'en' no META
					$file_fieldsearch_meta_en = $db_path . $b_0 . "/pfts/en/camposbusqueda.tab";
					$fpbb_en = file_get_contents_utf8($file_fieldsearch_meta_en);
					if ($fpbb_en) {
						foreach ($fpbb_en as $campos) {
							if (trim($campos) != "") {
								$fp_campos[$b_0][] = $campos;
							}
						}
					}
				}
			}
		}
		$cuenta = count($fp_campos);
		//echo "<pre>";print_r($fp_campos);die;
	}
?>


	<div style="flex: 0 0 50%;">
		<form name="<?php echo $iD; ?>Frm" method="post">
			<input type="hidden" name="Opcion" value="Guardar">
			<input type="hidden" name="base" value="<?php echo $base; ?>">
			<input type="hidden" name="file" value="<?php echo $file; ?>">
			<input type="hidden" name="lang" value="<?php echo $lang; ?>">

			<?php
			if (isset($_REQUEST["o_conf"])) {
				echo "<input type=hidden name=o_conf value=" . $_REQUEST["o_conf"] . ">\n";
			}

			// Caminho do arquivo de config (libre.tab ou avanzada.tab)
			if ($base != "" and $base != "META") {
				$file_av = $db_path . $base . "/opac/$lang/$file";
			} else {
				$file_av = $db_path . "/opac_conf/$lang/$file";
			}
			echo "<strong>" . $file_av . "</strong><br>";

			// --- CORREÇÃO DE ENCODING (TARGET 5) ---
			$fp = file_get_contents_utf8($file_av);
			// --- FIM CORREÇÃO ---

			$ix = 0;
			echo "<table id='search_table_" . $iD . "' cellpadding=5>\n";
			echo "<thead><tr><th>" . $msgstr["ix_nombre"] . "</th><th>" . $msgstr["ix_pref"] . "</th><th></th></tr></thead>";
			echo "<tbody id='tbody_search_" . $iD . "'>";


			if ($fp) {
				foreach ($fp as $value) {
					$value = trim($value);
					if ($value != "") {
						$l = explode('|', $value);
						if (count($l) < 2) $l[1] = ""; // Garante que $l[1] exista

						$ix = $ix + 1;

						// --- CORREÇÃO: Adicionado htmlspecialchars ---
						echo "<tr>";
						echo "<td><input type=text name=conf_lc_" . $ix . " size=30 value=\"" . htmlspecialchars(trim($l[0])) . "\"></td>";
						echo "<td><input type=text name=conf_ln_" . $ix . " size=5 value=\"" . htmlspecialchars(trim($l[1])) . "\"></td>";
						echo "<td><button type='button' class='bt bt-red' onclick='removeDynamicRow(this)'><i class='fas fa-trash'></i></button></td>";
						echo "</tr>";
					}
				}
			}

			// LINHA DE TEMPLATE OCULTA
			echo "<tr id='template_row_" . $iD . "' style='display: none;'>";
			echo "<td><input type=text name=conf_lc_ROW_PLACEHOLDER size=30 value=''></td>";
			echo "<td><input type=text name=conf_ln_ROW_PLACEHOLDER size=5 value=''></td>";
			echo "<td><button type='button' class='bt bt-red' onclick='removeDynamicRow(this)'><i class='fas fa-trash'></i></button></td>";
			echo "</tr>";

			echo "</tbody>";
			?>
			</table>
			<div style="margin-top: 10px;">
				<button type="button" class="bt-gray" onclick="addDynamicRow('tbody_search_<?php echo $iD; ?>', 'template_row_<?php echo $iD; ?>', 'ROW_PLACEHOLDER')"><?php echo $msgstr["cfg_add_line"]; ?></button>
			</div>
			<button type="submit" class="bt-green m-2"><?php echo $msgstr["save"]; ?></button>
		</form>
	</div>


	<div style="flex: 1; padding-left: 10px; width: 150px;">
		<?php
		if ($cuenta > 0) {
		?>
			<button type="button" class="accordion">
				<i class="fas fa-question-circle"></i> <?php echo $msgstr["view_searchfields_help"]; ?>
			</button>
			<div class="panel p-0">
				<div class="reference-box" style="max-height: 450px;">
					<?php
					foreach ($fp_campos as $key => $value_campos) {
					?>
						<strong><?php echo $key . "/" . $_REQUEST["lang"] . "/camposbusqueda.tab (central ABCD)</strong><br>"; ?>
							<table class="table striped">
								<thead>
									<tr>
										<th><?php echo $msgstr["ix_nombre"]; ?></th>
										<th><?php echo $msgstr["ix_pref"]; ?></th>
									</tr>
								</thead>
								<tbody>
									<?php
									if (!empty($value_campos))
										foreach ($value_campos as $value) {
											// --- CORREÇÃO: Adicionado trim, verificação e htmlspecialchars ---
											$value = trim($value);
											if ($value == "") continue;
											$v = explode('|', $value);
											if (count($v) < 3) $v[2] = ""; // Garante que $v[2] exista
											echo "<tr><td>" . htmlspecialchars(trim($v[0])) . "</td><td>" . htmlspecialchars(trim($v[2])) . "</td></tr>\n";
										}
									?>
								</tbody>
							</table>
						<?php
					} // Fim foreach $fp_campos
						?>
				</div>
			</div> <?php
				} // Fim if $cuenta > 0
			} // Fim da função Entrada
			echo "</div>"; // Fim flex
			echo "</div>\n"; // Fim div $iD

			include("../../common/footer.php"); ?>