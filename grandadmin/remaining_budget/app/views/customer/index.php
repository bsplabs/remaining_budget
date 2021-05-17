<!-- Second CSS -->
<link href="<?php echo BASE_URL; ?>/public/vendors/datatables/datatables.min.css" rel="stylesheet">
<!-- <link href="<?php echo BASE_URL; ?>/public/vendors/datatables/DataTables-1.10.24/dataTables.bootstrap4.min.css" rel="stylesheet"> -->
<link href="<?php echo BASE_URL; ?>/public/css/customer.css" rel="stylesheet">

<?php require_once APPROOT . "/views/layout/head.php"; ?>

<script>
  var base_url = "<?php echo BASE_URL; ?>";
</script>

<!-- CONTENT -->
<div class="container-fluid mt-5 customer-page">
  <br>
  <div class="row">
    <div class="col-md-6">
      <h2>Customers</h2>
      <p class="mb-0">This page show you all customers and you could upload customer resources file to edit customers</p>
    </div>
    <div class="col-md-6 text-right">
      <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCustomerUpload"><i class='bx bx-add-to-queue'></i> Add / Replace</button>
      <button class="btn btn-warning" style="margin-left: 5px;" data-bs-toggle="modal" href="#modal" role="button"><i class='bx bx-export'></i> Export</button>
    </div>
  </div>

  <hr>

  <table id="customers-table" class="table table-bordered table- text-nowrap table-striped table-hover">
    <!-- THEAD -->
    <thead>
      <tr>
        <th scope="col">ID</th>
        <th scope="col">Parent ID</th>
        <th scope="col">Customer ID</th>
        <th scope="col">Customer Name</th>
        <th scope="col">Offset Acct</th>
        <th scope="col">Offset Acct Name</th>
        <th scope="col">Main Business</th>
        <th scope="col">Company</th>
        <th scope="col">Payment Method</th>
        <th scope="col">Created at</th>
        <th scope="col">Updated at</th>
        <th scope="col">Updated by</th>
        <th scope="col" class="text-center">Actions</th>
      </tr>
    </thead>
    <!-- TBODY -->
    <tbody></tbody>
  </table>

  <br><br>

  <!-- Add/Replace Modal -->
  <div class="modal fade" id="modalCustomerUpload" tabindex="-1" aria-labelledby="modalCustomerUploadLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="staticBackdropLabel">Upload Customer Resources</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
          <form>
            <div class="mb-3">
              <label for="exampleFormControlInput1" class="form-label">Choose your file type to upload</label>
              <select class="form-select" aria-label="Default select example">
                <!-- <option selected>Open this select menu</option> -->
                <option selected value="customer_id_mapping">Customer ID Mapping</option>
                <option value="post_paid_account">Post-paid Account</option>
                <option value="rpmax_account">RPMAX Account</option>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label">Browse your computer</label>
              <div class="upload-container">
                <div class="border-container">
                  <input type="file" id="customer-file-upload" class="form-control">
                </div>
              </div>
            </div>
          </form>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="button" class="btn btn-primary">Upload</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Edit Modal -->
  <div class="modal fade" id="modal-edit-customer" tabindex="-1" aria-labelledby="modal-edit-customer-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content">
        <!-- header  -->
        <div class="modal-header">
          <h5 class="modal-title">Edit Customer</h5>
        </div>
        <!-- bode -->
        <div class="modal-body">
          <form>
            <div class="form-row">
              <div class="form-group col-md-6">
                <label for="inputID">ID</label>
                <input disabled type="text" class="form-control" id="inputID" value="xxxx">
              </div>

              <div class="form-group col-md-6">
                <label for="inputParentID">Parent ID</label>
                <input type="text" class="form-control" id="inputParentID">
              </div>
            </div>
            <!--  -->
            <div class="form-row">
              <div class="form-group col-md-6">
                <label for="inputCustomerID">Customer ID</label>
                <input type="text" class="form-control" id="inputCustomerID">
              </div>
              <div class="form-group col-md-6">
                <label for="inputOffsetAcct">Offset Acct</label>
                <input type="text" class="form-control" id="inputOffsetAcct">
              </div>
            </div>
            <!--  -->
            <div class="form-row">
              <div class="form-group col-md-12">
                <label for="inputCustomerName">Customer Name</label>
                <input type="text" class="form-control" id="inputCustomerName">
              </div>
            </div>
            <!--  -->
            <div class="form-row">
              <div class="form-group col-md-12">
                <label for="inputOffsetAcctName">Offset Acct Name</label>
                <input type="text" class="form-control" id="inputOffsetAcctName">
              </div>
            </div>
            <!--  -->
            <div class="form-row">
              <div class="form-group col-md-6">
                <label for="inputCompany">Company</label>
                <input type="text" class="form-control" id="inputCompany">
              </div>
              <div class="form-group col-md-6">
                <label for="inputPaymentMethod">Payment Method</label>
                <input type="text" class="form-control" id="inputPaymentMethod">
              </div>
            </div>
            <!--  -->
            <div class="form-group mb-0 mt-2">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="inputMainBusiness">
                <label class="form-check-label" for="inputMainBusiness">
                  Main Business
                </label>
              </div>
            </div>
            <!--  -->
          </form>
        </div>
        <!-- footer -->
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          <button type="button" class="btn btn-primary">Save</button>
        </div>
        <!--  -->
      </div>
    </div>
  </div>
</div>

<?php require_once APPROOT . "/views/layout/script.php"; ?>

<!-- Second JS -->
<script src="<?php echo BASE_URL; ?>/public/vendors/popper.min.js"></script>
<script src="<?php echo BASE_URL; ?>/public/vendors/datatables/datatables.min.js"></script>
<!-- <script src="<?php echo BASE_URL; ?>/public/vendors/datatables/DataTables-1.10.24/dataTables.bootstrap4.min.js"></script> -->
<script src="<?php echo BASE_URL; ?>/public/js/customer/index.js"></script>