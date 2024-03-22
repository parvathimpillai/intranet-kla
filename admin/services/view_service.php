<?php
if(isset($_GET['id']) && $_GET['id'] > 0){
    $qry = $conn->query("SELECT * from `service_list` where id = '{$_GET['id']}' ");
    if($qry->num_rows > 0){
        foreach($qry->fetch_assoc() as $k => $v){
            $$k=$v;
        }
    }else{
		echo '<script>alert("service ID is not valid."); location.replace("./?page=services")</script>';
	}
}else{
	echo '<script>alert("service ID is Required."); location.replace("./?page=services")</script>';
}
?>
<style>
	#banner-img{
		width:40vw;
		height:55vh;
        margin: 0 auto;
        margin-bottom:1.5em;
		overflow:hidden;
        background-color:#000;
	}
	#banner-img > img{
		width:100%;
		height:100%;
        object-fit: contain;
		object-position:center center;
	}
</style>
<div class="row mt-lg-n4 mt-md-n4 justify-content-center">
	<div class="col-lg-10 col-md-12 col-sm-12 col-xs-12">
		<div class="card rounded-0">
			<div class="card-body">
                <div class="container-fluid mt-4">
					<div id="banner-img">
						<img src="<?= validate_image($image_path ?? "") ?>" alt="">
					</div>
					<h2 class="titleTxt"><b><?= $name ?? "" ?></b> <span> <i class="bi bi-tags"></i> <?= format_num(($price ?? 0), 2) ?></span></h2>
					<div><?= isset($description) ? (htmlspecialchars_decode($description)) : "" ?></div>
                    <dl>
                      <dt class="text-muted">Status</dt>
                        <dd class="ps-4">
                            <?php if($status == 1): ?>
                                <span class="badge bg-success px-3 rounded-pill">Active</span>
                            <?php else: ?>
                                <span class="badge bg-danger px-3 rounded-pill">Inactive</span>
                            <?php endif; ?>
                        </dd>
                    </dl>
                </div>
            </div>
			<div class="card-footer py-1 text-center">
				<button class="btn btn-danger btn-sm bg-gradient-danger rounded-0" type="button" id="delete_data"><i class="fa fa-trash"></i> Delete</button>
				<a class="btn btn-primary btn-sm bg-gradient-teal rounded-0" href="./?page=services/manage_service&id=<?= isset($id) ? $id : '' ?>"><i class="fa fa-edit"></i> Edit</a>
				<a class="btn btn-light btn-sm bg-gradient-light border rounded-0" href="./?page=services"><i class="fa fa-angle-left"></i> Back to List</a>
			</div>
		</div>
	</div>
</div>
<script>
    $(function(){
		$('#delete_data').click(function(){
			_conf("Are you sure to delete this service permanently?","delete_service", ["<?= isset($id) ? $id :'' ?>"])
		})
    })
    function delete_service($id){
		start_loader();
		$.ajax({
			url:_base_url_+"classes/Master.php?f=delete_service",
			method:"POST",
			data:{id: $id},
			dataType:"json",
			error:err=>{
				console.log(err)
				alert_toast("An error occured.",'error');
				end_loader();
			},
			success:function(resp){
				if(typeof resp== 'object' && resp.status == 'success'){
					location.replace("./?page=services");
				}else{
					alert_toast("An error occured.",'error');
					end_loader();
				}
			}
		})
	}
</script>