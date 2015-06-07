<?php

	$_SESSION["siz_origin_url"] = $_SERVER["REQUEST_URI"];
	
	// List seluruh kegiatan yang diadakan oleh divisi
	
	// Init
	$idDivisi = intval($_SESSION['siz_divisi']);
	$isAdmin = ($idDivisi == RA_ID_ADMIN);
	
	// Query list kegiatan master
	$queryListKegiatan = sprintf(
		"SELECT k.*, (".
			"SELECT SUM(jumlah_anggaran) FROM ra_rincian_awal AS a WHERE k.id_kegiatan=a.id_kegiatan".
		") AS jml_anggaran, n.idakun, n.namaakun ".
		"FROM ra_kegiatan AS k LEFT JOIN akun AS n ON k.akun_pengeluaran=n.kode ORDER BY k.divisi");
	$listResult = $mysqli->query($queryListKegiatan);
	echo $mysqli->error;
?>
<div class="col-12">
	<?php ra_print_status($namaDivisiUser); ?>
	<div class="widget-box">
		<div class="widget-title">
			<span class="icon">
				<i class="glyphicon glyphicon-list"></i>									
			</span>
			<h5>List Kegiatan Master</h5>
		</div>
		<div class="widget-content">
			<table class="table table-bordered table-striped table-hover data-table">
				<thead>
				<tr>
					<th>Divisi</th>
					<th>Nama Kegiatan</th>
					<th>Prioritas</th>
					<th>Anggaran Awal</th>
					<th>Akun Pengeluaran</th>
					<th>Sifat</th>
					<th>Aksi</th>
				</tr>
				</thead>
				<tbody>
			<?php
			$currentDivisi = -1;
			while ($rowKegiatan = $listResult->fetch_array(MYSQLI_ASSOC)) {
				if ($currentDivisi != $rowKegiatan['divisi']) {
					$labelDivisi = "<b>".$listDivisi[$rowKegiatan['divisi']]."</b>";
					$currentDivisi = $rowKegiatan['divisi'];
				} else {
					$labelDivisi = "&nbsp;";
				}
				echo "<tr class=\"siz_tkegiatan_{$rowKegiatan['id_kegiatan']}\">\n";
				echo "	<td style=\"width:100px;\">".$labelDivisi."</td>\n";
				echo "	<td><a href=\"".htmlspecialchars(ra_gen_url("master-kegiatan",null,"id=".$rowKegiatan['id_kegiatan']))."\">";
				echo htmlspecialchars($rowKegiatan['nama_kegiatan'])."</a></td>\n";
				echo "<td class=\"td-prioritas\">".$listPrioritasHTML[$rowKegiatan['prioritas']]."</td>\n";
				echo "	<td>".to_rupiah($rowKegiatan['jml_anggaran'])."</td>\n";
				echo "	<td><a href=\"".htmlspecialchars("main.php?s=akun&action=detail&id=".$rowKegiatan['idakun'])."\" target=\"_blank\">";
				echo $rowKegiatan['akun_pengeluaran']." ".$rowKegiatan['namaakun']."</a></td>\n";
				echo "	<td>".($rowKegiatan['jenis_kegiatan']==1?"Rutin Tahunan":"Eksklusif")."</td>\n";
				echo "	<td><span class=\"glyphicon glyphicon-eye-open\"></span> Lihat</td>\n";
				echo "</tr>\n";
			}
			?>
				</tbody>
			</table>
			<a href="<?php echo htmlspecialchars(ra_gen_url("tambah-kegiatan-master")); ?>"
				class="btn btn-primary">
				<span class="glyphicon glyphicon-plus"></span> Tambah Master Kegiatan
			</a>
		</div>
	</div><!-- End widget-box-->
</div><!-- End col-12 -->