<h1 class="pageTitle text-center">Book Form</h1>
<hr class="mx-auto bg-primary border-primary opacity-100" style="width:50px">
<div class="row justify-content-center">
    <div class="col-lg-8 col-md-10 col-sm-12 col-12">
    <div class="card">
            <div class="card-body py-4">
                <h4 class="pageTitle">Please fill all the required fields</h4>
                <form action="" id="book-form">
                    <input type="hidden" name="id">
                    <input type="hidden" name="customer">
                    <div class="mb-3">
                        <label for="fullname" class="form-label">Fullname</label>
                        <input type="text" class="form-control" id="fullname" name="fullname" required="required" autofocus>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required="required">
                    </div>
                    <div class="mb-3">
                        <label for="contact" class="form-label">Contact No.</label>
                        <input type="text" class="form-control" id="contact" name="contact" required="required">
                    </div>
                    <div class="mb-3">
                        <label for="address" class="form-label">Address</label>
                        <textarea rows="5" class="form-control" id="address" name="address" required="required"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="services" class="form-label">Services</label>
                        <select name="services[]" id="services" class="form-select" required="required" multiple="multiple">
                            <?php 
                            $query = $conn->query("SELECT * FROM `service_list` where `status` = 1 order by `name` asc");
                            while($row=$query->fetch_assoc()):
                            ?>
                            <option value="<?= $row['id'] ?>" <?= (isset($_GET['sid']) && $_GET['sid'] == $row['id']) ? "selected" : "" ?>><?= $row['name'] ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </form>
            </div>
            <div class="card-footer">
                <div class="col-lg-4 col-md-6 col-sm-10 col-12 mx-auto">
                    <button class="btn btn-primary btn-sm w-100" form="book-form"><i class="bi bi-send"></i> Book Now</button>
                </div>
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
    $('#book-form').submit(function(e){
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
                        location.replace('./?page=booking')
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