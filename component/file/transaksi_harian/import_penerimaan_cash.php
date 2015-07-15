<?php
	
	$processErrors = array();
	$processWarnings = array();
	$processSuccess = array();
	
	if (isset($_GET['do'])) {
		require COMPONENT_PATH.'\\file\\transaksi_harian\\import\\import_core.php';
	}
?>
<div class="col-8">
<?php 
	if (!empty($processErrors)) {
		echo "<div class='alert alert-danger'>";
		echo "<div><span class='glyphicon glyphicon-warning-sign'></span>
				<b>Maaf, proses impor gagal. Deskripsi kesalahan:</b></div>";
		foreach ($processErrors as $itemError) {
			echo "<div style='padding: 3px 3px 3px 10px;'>".$itemError."</div>\n";
		}
		echo "</div>\n";
	}
	
	if (!empty($processWarnings)) {
		echo "<div class='alert alert-warning'>";
		echo "<div><span class='glyphicon glyphicon-warning-sign'></span>
				<b>Ada beberapa peringatan yang perlu Anda perhatikan:</b></div>";
		foreach ($processWarnings as $itemWarning) {
			echo "<div style='padding: 3px 3px 3px 10px;'>".$itemWarning."</div>\n";
		}
		echo "</div>\n";
	}
	
	if (!empty($processSuccess)) {
		echo "<div class='alert alert-success'>";
		foreach ($processSuccess as $itemMessage) {
			echo "<div style='padding: 3px 3px 3px 3px;'>";
			echo "<span class='glyphicon glyphicon-ok'></span> ".$itemMessage."</div>\n";
		}
		echo "</div>\n";
	}
?>
	<div class="widget-box">
		<div class="widget-title">
			<span class="icon">
				<i class="glyphicon glyphicon-th"></i>
			</span>
			<h5>Impor data transaksi</h5>
		</div>
		<div class="widget-content">
			<form action="main.php?s=transaksi&amp;action=import&amp;do=penerimaan-cash"
				method="post" enctype="multipart/form-data">
				<input type="file" name="siz_spreadsheet_file" accept=".xlsx" />
				<input type="submit" name="siz_submit" value="Import" />
			</form>
			<div>
				<h3>Ketentuan File</h3>
				<p>File harus berupa format <b>Ms. Excel 2007 (xlsx)</b> dengan konten
				sebagai berikut:</p>
				<ul>
					<li>Format tanggal adalah dd/mm/yyyy (Misal: 13/7/2015)</li>
					<li>Tidak ada tanda baca pada nominal (Misal: 20000)</li>
				</ul>
			</div>
		</div>
	</div>
</div>