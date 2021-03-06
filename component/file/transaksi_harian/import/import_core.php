<?php
/*
 * import_core.php
 * ==> Prosedur utama dalam proses ekstrak dari excel masuk ke stage
 * 
 * Included in: AM_SIZ_IMPORTTRX (Impor transaksi)
 * Output: None (internal use only)
 */

	$doProcess = $_GET['do'];
	$doProcess = strtolower($doProcess);
	
	// Load library untuk upload dan query
	require COMPONENT_PATH.'/libraries/helper_upload.php';
	require COMPONENT_PATH.'/libraries/querybuilder.php';
	
	$uploadError = null;
	$uploadSetting = array(
			'path' => '/uploads',
			'exts' => array('xlsx'),
			'size' => 2 * 1024 * 1024,
			'name' => 'siz_spreadsheet_file',
	);
	
	$uploadResult = do_upload($uploadSetting, $uploadError);
	
	if ($uploadResult != null) {
		$spreadsheetUrl = FCPATH.$uploadResult['url'];
		// Baca spreadsheet yang terupload...
		require_once COMPONENT_PATH."/libraries/phpexcel/PHPExcel.php";
		
		try {
			$objReader = PHPExcel_IOFactory::createReader('Excel2007');
			$objReader->setReadDataOnly(TRUE);
			$objPHPExcel = $objReader->load($spreadsheetUrl);
				
			$objWorksheet = $objPHPExcel->getActiveSheet();
			
			/*=====================================================================
			 * Impor Transaksi Penerimaan
			 *=====================================================================*/
			if ($doProcess == "penerimaan") {
				// Begin transaction...
				mysqli_autocommit($mysqli, false);
				
				$trxCount = 0;
				$trxCountSuccess = 0;
				$rowSkipped = 0;
				$totalNominalImpor = 0;
				
				$rowIndex = 0;
				foreach ($objWorksheet->getRowIterator() as $row) {
					$tglSekarang = date("Y-m-d H:i:s");
					$rowIndex = $row->getRowIndex();
					
					// Baris 1 adalah header, jadi diabaikan...
					if ($rowIndex==1) continue;
					
					$cellIterator = $row->getCellIterator();
					$cellIterator->setIterateOnlyExistingCells(FALSE);
					
					$tanggalTrxExcel	= $objWorksheet->getCellByColumnAndRow(0,$rowIndex)->getValue();
					$noNotaTrx			= trim($objWorksheet->getCellByColumnAndRow(1,$rowIndex)->getValue());
					
					// Jika no nota kosong, maka dilewati...
					if (empty($noNotaTrx)) {
						$rowSkipped++;
						continue;
					}
					
					$trxCount++;
					
					// Validasi record spreadsheet
					$keteranganTrx		= trim($objWorksheet->getCellByColumnAndRow(2,$rowIndex)->getValue());
					$jumlahTrx			= trim($objWorksheet->getCellByColumnAndRow(3,$rowIndex)->getValue());
					$txtAmilin			= trim($objWorksheet->getCellByColumnAndRow(4,$rowIndex)->getValue());
					$txtDonatur			= trim($objWorksheet->getCellByColumnAndRow(5,$rowIndex)->getValue());
					$txtAlamatDonatur	= trim($objWorksheet->getCellByColumnAndRow(6,$rowIndex)->getValue());
					
					$txtBank			= trim($objWorksheet->getCellByColumnAndRow(7,$rowIndex)->getValue());
					$txtUkmKubah		= trim($objWorksheet->getCellByColumnAndRow(8,$rowIndex)->getValue());
					$txtThnRamadhan		= trim($objWorksheet->getCellByColumnAndRow(9,$rowIndex)->getValue());
					
					if (!empty($jumlahTrx)) {
						if (!is_numeric($jumlahTrx)) {
							$processWarnings[] = "Record pada baris ".$rowIndex." (Nota: ".
									htmlspecialchars($noNotaTrx).") gagal diload. Nominal transaksi harus numerik.";
							continue;
						} else {
							$jumlahTrx = intval($jumlahTrx);
							$totalNominalImpor += $jumlahTrx;
						}
					} else {
						$processWarnings[] = "Record pada baris ".$rowIndex." (Nota: ".
								htmlspecialchars($noNotaTrx).") gagal diload. Nominal kosong.";
						$rowSkipped++;
						continue;
					}
					
					if (!empty($txtThnRamadhan)) {
						if (!is_numeric($txtThnRamadhan)) {
							$processWarnings[] = "Tahun Ramadhan pada record pada baris ".$rowIndex." (Nota: ".
									htmlspecialchars($noNota).") harus numerik. Transaksi diload sebagai ".
									"transaksi harian bukan Ramadhan.";
							continue;
						} else {
							$txtThnRamadhan = intval($txtThnRamadhan);
						}
					}
					// Hilangkan karakter spasi pada nomor nota
					$noNotaTrx = preg_replace("/\s/", "", $noNotaTrx);
					
					$tanggalTrx = date("Y-m-d", PHPExcel_Shared_Date::ExcelToPHP($tanggalTrxExcel));
					
					$recordFields = array(
							'no_nota'	=> $noNotaTrx,
							'tanggal'	=> $tanggalTrx,
							'id_donatur' => 0,
							'nama_donatur' => $txtDonatur,
							'alamat_donatur' => $txtAlamatDonatur,
							'id_teller' => 0,
							'nama_amilin' => $txtAmilin,
							'jumlah'	=> $jumlahTrx,
							'keterangan' => $keteranganTrx.", dari ".$txtDonatur,
							'kode_akun'	=> '0',
							'ket_akun'	=> $keteranganTrx,
							'id_bank'	=> (!empty($txtBank)?-1:0),
							'nama_bank'	=> $txtBank,
							'nama_ukm'	=> $txtUkmKubah,
							'th_kubah'	=> 0,
							'th_ramadhan' => $txtThnRamadhan,
							'tgl_load'	=> $tglSekarang
					);
					
					$querySetPart = querybuilder_generate_set($recordFields);
					$queryInsertStage = ("INSERT INTO stage_penerimaan SET ".$querySetPart);
					
					$resultInsert = mysqli_query($mysqli, $queryInsertStage);
					if ($resultInsert != null) {
						$trxCountSuccess++;
					} else {
						$processWarnings[] = "Record pada baris ".$rowIndex." (Nota: ".
								htmlspecialchars($noNota).") gagal diload. MySQL error: ".
								mysqli_error($mysqli);
						continue;
					}
					
				} // end foreach
				
				if (empty($processErrors)) {
					mysqli_commit($mysqli);
					$processSuccess[] = "<b>".$trxCountSuccess." dari ".
							$trxCount." record</b> berhasil dimuat (".$rowSkipped." dilewati).";
					$processSuccess[] = "Jumlah nominal penerimaan yang berhasil di-load: <b>".to_rupiah($totalNominalImpor)."</b>";
				} else {
					
				}
			
			/*=====================================================================
			 * Impor Transaksi Pengeluaran
			 *=====================================================================*/
			} else if ($doProcess == "pengeluaran") {
				// Begin transaction...
				mysqli_autocommit($mysqli, false);
				
				$trxCount = 0;
				$trxCountSuccess = 0;
				$rowSkipped = 0;
				$totalNominalImpor = 0;
				
				$rowIndex = 0;
				foreach ($objWorksheet->getRowIterator() as $row) {
					$tglSekarang = date("Y-m-d H:i:s");
					$rowIndex = $row->getRowIndex();
						
					// Baris 1 adalah header, jadi diabaikan...
					if ($rowIndex==1) continue;
						
					$cellIterator = $row->getCellIterator();
					$cellIterator->setIterateOnlyExistingCells(FALSE);
						
					$tanggalTrxExcel	= $objWorksheet->getCellByColumnAndRow(0,$rowIndex)->getValue();
					$keteranganTrx		= trim($objWorksheet->getCellByColumnAndRow(1,$rowIndex)->getValue());
					
						
					// Jika keterangan kosong, maka dilewati...
					if (empty($keteranganTrx)) {
						$rowSkipped++;
						continue;
					}
						
					$trxCount++;
						
					// Validasi record spreadsheet
					$noNotaTrx			= trim($objWorksheet->getCellByColumnAndRow(2,$rowIndex)->getValue());
					$jumlahTrx			= trim($objWorksheet->getCellByColumnAndRow(3,$rowIndex)->getValue());
					$txtInfoTrx			= trim($objWorksheet->getCellByColumnAndRow(4,$rowIndex)->getValue());
					$txtBank			= trim($objWorksheet->getCellByColumnAndRow(5,$rowIndex)->getValue());
					$txtAmilin			= trim($objWorksheet->getCellByColumnAndRow(6,$rowIndex)->getValue());
					$txtPenerima		= trim($objWorksheet->getCellByColumnAndRow(7,$rowIndex)->getValue());
					
					$txtUkmKubah		= trim($objWorksheet->getCellByColumnAndRow(8,$rowIndex)->getValue());
					$txtThnRamadhan		= trim($objWorksheet->getCellByColumnAndRow(9,$rowIndex)->getValue());
						
					if (!is_numeric($jumlahTrx)) {
						$processWarnings[] = "Record pada baris ".$rowIndex." (Nota: ".
								htmlspecialchars($noNotaTrx).") gagal diload. Nominal transaksi harus numerik.";
						continue;
					} else {
						$jumlahTrx = intval($jumlahTrx);
						$totalNominalImpor += $jumlahTrx;
					}
					if (!empty($txtThnRamadhan)) {
						if (!is_numeric($txtThnRamadhan)) {
							$processWarnings[] = "Tahun Ramadhan pada record pada baris ".$rowIndex." (Nota: ".
									htmlspecialchars($noNota).") harus numerik. Transaksi diload sebagai ".
									"transaksi harian bukan Ramadhan.";
							continue;
						} else {
							$txtThnRamadhan = intval($txtThnRamadhan);
						}
					}
					// Hilangkan karakter spasi pada nomor nota
					$noNotaTrx = preg_replace("/\s/", "", $noNotaTrx);
						
					$tanggalTrx = date("Y-m-d", PHPExcel_Shared_Date::ExcelToPHP($tanggalTrxExcel));
					
					$ketAkun = $keteranganTrx.(empty($txtDonatur)? "" : " untuk ".$txtDonatur)." (".$txtInfoTrx.")";
					$recordFields = array(
							'no_nota'	=> $noNotaTrx,
							'tanggal'	=> $tanggalTrx,
							'id_penerima' => (!empty($txtPenerima)?-1:0),
							'nama_penerima' => $txtPenerima,
							'id_pj' => (!empty($txtAmilin)?-1:0),
							'nama_pj' => $txtAmilin,
							'jumlah'	=> $jumlahTrx,
							'keterangan' => $ketAkun,
							'kode_akun'	=> '0',
							'ket_akun'	=> $keteranganTrx,
							'id_bank'	=> (!empty($txtBank)?-1:0),
							'nama_bank'	=> $txtBank,
							'nama_ukm'	=> $txtUkmKubah,
							'th_kubah'	=> 0,
							'th_ramadhan' => $txtThnRamadhan,
							'tgl_load'	=> $tglSekarang
					);
						
					$querySetPart = querybuilder_generate_set($recordFields);
					$queryInsertStage = ("INSERT INTO stage_pengeluaran SET ".$querySetPart);
						
					$resultInsert = mysqli_query($mysqli, $queryInsertStage);
					if ($resultInsert != null) {
						$trxCountSuccess++;
					} else {
						$processWarnings[] = "Record pada baris ".$rowIndex." (Nota: ".
								htmlspecialchars($noNota).") gagal diload. MySQL error: ".
								mysqli_error($mysqli);
						continue;
					}
						
				} // end foreach
				
				if (empty($processErrors)) {
					mysqli_commit($mysqli);
					$processSuccess[] = "<b>".$trxCountSuccess." dari ".
							$trxCount." record</b> berhasil dimuat (".$rowSkipped." dilewati).";
					$processSuccess[] = "Jumlah nominal pengeluaran yang berhasil di-load: <b>".to_rupiah($totalNominalImpor)."</b>";
				} else {
						
				}
			}
		} catch (Exception $e) {
			$processErrors[] = '[PHPExcel error] '.$e->getMessage().
				" Trace:<br>".$e->getTraceAsString();
		}
		@unlink($spreadsheetUrl);
	} else {
		if ($uploadError != null) {
			$processErrors[] = $uploadError;
		}
		
	}
	