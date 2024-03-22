<?php
if(isset($_GET['id']) && $_GET['id'] > 0){
    $qry = $conn->query("SELECT * from `book_list` where id = '{$_GET['id']}' ");
    if($qry->num_rows > 0){
        foreach($qry->fetch_assoc() as $k => $v){
            $$k=$v;
        }
    }
	$services_qry = $conn->query("SELECT `id`, `name` from `service_list` where id in (SELECT `service_id` FROM `book_services` where `book_id` = '{$id}')");
	$services_arr = array_column($services_qry->fetch_all(MYSQLI_ASSOC), 'name', 'id');
}
?>
<div class="row mt-lg-n4 mt-md-n4 justify-content-center">
	<div class="col-lg-8 col-md-10 col-sm-12 col-xs-12">
		<div class="card rounded-0">
			<div class="card-header py-0">
				<div class="card-title py-1"><b><?= isset($id) ? "Update Booking Details" : "New Booking Entry" ?></b></div>
			</div>
			<div class="card-body">
				<div class="container-fluid mt-3">
					<form action="" id="booking-form">
						<input type="hidden" name ="id" value="<?php echo isset($id) ? $id : '' ?>">
						<div class="row">
							<div class="form-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
								<label for="fullname" class="control-label">Customer Name</label>
								<input type="text" name="fullname" id="fullname" class="form-control form-control-sm rounded-0" value="<?php echo isset($fullname) ? $fullname : ''; ?>"  autofocus required/>
							</div>
						</div>
						<div class="row">
							<div class="form-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
								<label for="email" class="control-label">Email</label>
								<input type="email" name="email" id="email" class="form-control form-control-sm rounded-0" value="<?php echo isset($email) ? $email : ''; ?>"  required/>
							</div>
						</div>
						<div class="row">
							<div class="form-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
								<label for="contact" class="control-label">Contact #</label>
								<input type="text" name="contact" id="contact" class="form-control form-control-sm rounded-0" value="<?php echo isset($contact) ? $contact : ''; ?>"  required/>
							</div>
						</div>
						<div class="row">
							<div class="form-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
								<label for="address" class="control-label">Address</label>
								<input type="text" name="address" id="address" class="form-control form-control-sm rounded-0" value="<?php echo isset($address) ? $address : ''; ?>"  required/>
							</div>
						</div>
						<div class="row">
							<div class="form-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
							<label for="services" class="form-label">Services</label>
							<select name="services[]" id="services" class="form-select" required="required" multiple="multiple">
								<?php 
								$query = $conn->query("SELECT * FROM `service_list` where `status` = 1 order by `name` asc");
								while($row=$query->fetch_assoc()):
								?>
								<option value="<?= $row['id'] ?>" <?= (in_array($row['id'], array_keys($services_arr))) ? "selected" : "" ?>><?= $row['name'] ?></option>
								<?php endwhile; ?>
							</select>
							</div>
						</div>
						<div class="row">
							<div class="form-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
								<label for="status" class="control-label">Status</label>
								<select name="status" id="status" class="form-select form-select-sm rounded-0" required="required">
									<option value="0" <?= isset($status) && $status == 0 ? 'selected' : '' ?>>Pending</option>
									<option value="1" <?= isset($status) && $status == 1 ? 'selected' : '' ?>>Confirmed</option>
									<option value="2" <?= isset($status) && $status == 2 ? 'selected' : '' ?>>Cancelled</option>
								</select>
							</div>
						</div>
					</form>
				</div>
			</div>
			<div class="card-footer py-1 text-center">
				<button class="btn btn-primary btn-sm bg-gradient-teal btn-flat border-0" form="booking-form"><i class="fa fa-save"></i> Save</button>
				<a class="btn btn-light btn-sm bg-gradient-light border btn-flat" href="./?page=bookings"><i class="fa fa-times"></i> Cancel</a>
			</div>
		</div>
	</div>
</div>
<script>
	$(document).ready(function(){
		$('#services').select2({
			placeholder: 'Please Select Here',
			width: '100%'
		})
		$('#booking-form').submit(function(e){
			e.preventDefault();
            var _this = $(this)
			 $('.err-msg').remove();
			setTimeout(() => {
				start_loader();
				$.ajax({
					url:_base_url_+"classes/Master.php?f=save_book",
					data: new FormData($(this)[0]),
					cache: false,
					contentType: false,
					processData: false,
					method: 'POST',
					type: 'POST',
					dataType: 'json',
					error:err=>{
						console.log(err)
						alert_toast("An error occured",'error');
						end_loader();
					},
					success:function(resp){
						if(typeof resp =='object' && resp.status == 'success'){
							location.replace('./?page=bookings/view_booking&id='+resp.bid)
						}else if(resp.status == 'failed' && !!resp.msg){
							var el = $('<div>')
								el.addClass("alert alert-danger err-msg").text(resp.msg)
								_this.prepend(el)
								el.show('slow')
								$("html, body").scrollTop(0);
								end_loader()
						}else{
							alert_toast("An error occured",'error');
							end_loader();
							console.log(resp)
						}
					}
				})
			}, 200);
			
		})

	})
</script>