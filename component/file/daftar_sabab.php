<script type="text/javascript">
	function konfirmasi(){
		var pilihan = confirm("are you sure to delete this record ?");
		
		if(pilihan){
			return true;
		}else{
			return false;
		}
	}
</script>

<div class="col-12">

		<div class="widget-box">
			<div class="widget-title">
				<span class="icon">
					<i class="glyphicon glyphicon-th"></i>
				</span>
				<h5>Daftar Sabab</h5>
			</div>
			<div class="widget-content nopadding">
				<table class="table table-bordered table-striped table-hover data-table">
					<thead>
					<tr>
						<th>No</th>
						<th>Nama</th>
						<th>Alamat</th>
						<th>No.Hp</th>
						<th>Email</th>
						<th>Action</th>
					</tr>
					</thead>
					<tbody>
					<?php
						include "component/config/koneksi.php";
						$query = mysql_query("select * from user WHERE level = 4");
						$i = 1;
						while($pecah = mysql_fetch_array($query)){
						echo"	
							<tr class=\"gradeA\">
						
							<td>$i</td>
							<td>$pecah[nama]</td>
							<td>$pecah[alamat]</td>
							<td>$pecah[hp]</td>
							<td>$pecah[email]</td>
							<td align=\"center\"><a href=\"main.php?s=edit_sabab&id=$pecah[id_user]\" class=\"btn btn-info btn-mini\">Ubah</a> | <a href=\"main.php?s=lihat_detail_sabab&id=$pecah[id_user]\" class=\"btn btn-info btn-mini\">Detail Sabab</a></td>
							
						
						</tr>";
						$i++;
						}
					?>
					</tbody>
					</table>  
			</div>
		</div>
	</div>

