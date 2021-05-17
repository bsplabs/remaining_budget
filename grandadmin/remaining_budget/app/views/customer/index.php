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
      <button type="button" class="btn btn-primary" id="importCustomers"><i class='bx bx-add-to-queue'></i> Add / Replace</button>
      <button type="button" class="btn btn-warning" style="margin-left: 5px;" id="exportCustomers"><i class='bx bx-export'></i> Export</button>
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

  <!-- Add/Replace Customers Modal -->
  <div class="modal fade" id="modalImportCustomers" tabindex="-1" aria-labelledby="modalImportCustomersLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="staticBackdropLabel">Import Customers</h5>
        </div>

        <div class="modal-body">
          <form>
            <div class="form-group mb-3">
              <div class="upload-container">
                <div class="border-container">
                  <input type="file" class="form-control" id="inputFileImport" style="height: 100%;">
                </div>
              </div>
            </div>
          </form>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          <button type="button" class="btn btn-primary">Import</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Edit Customer Modal -->
  <div class="modal fade" id="modal-edit-customer" tabindex="-1" aria-labelledby="modal-edit-customer-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content">
        <!-- header  -->
        <div class="modal-header">
          <h5 class="modal-title">Edit Customer</h5>
        </div>
        <!-- body -->
        <div class="modal-body">
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
        </div>
        <!-- footer -->
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          <button type="button" class="btn btn-primary" id="saveCustomerEdited">Save</button>
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