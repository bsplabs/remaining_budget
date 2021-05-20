<!-- Second CSS -->
<link href="<?php echo BASE_URL; ?>/public/vendors/datatables/datatables.min.css" rel="stylesheet">
<!-- <link href="<?php echo BASE_URL; ?>/public/vendors/datatables/DataTables-1.10.24/dataTables.bootstrap4.min.css" rel="stylesheet"> -->
<link href="<?php echo BASE_URL; ?>/public/css/customer.css" rel="stylesheet">

<?php require_once APPROOT . "/views/layout/head.php"; ?>

<script>
  var base_url = "<?php echo BASE_URL; ?>";
</script>

<!-- CONTENT -->
<div class="container-fluid customer-page">
  <br>
  <div class="row">
    <div class="col-md-6">
      <h2>Customers Management</h2>
      <!-- <p class="mb-0">This page show you all customers and you could upload customer resources file to edit customers</p> -->
    </div>
    <!-- <div class="col-md-6 text-right">
      
    </div> -->
  </div>

  <hr>

  <div class="row  p-2 m-0 mb-2">
    <div class="col-md-5 p-0">
      <!-- <button> <span> <i class='bx bx-filter'></i> Filter </span> </button> -->
      <!--  -->
      <div class="p-2 mt-2">
      <div class="form-group row">
        <label for="filterParentId" class="col-sm-3 col-form-label">Parent ID</label>
        <div class="col-sm-5">
          <select name="" id="filterParentId" class="form-control form-control-sm">
            <option value="all">All</option>
            <option value="more_than_one_child">More than one child</option>
            <option value="one_child">One child</option>
          </select>
        </div>
      </div>
      <!--  -->
      <!--  -->
      <div class="form-group row">
        <label for="filterPaymentMethod" class="col-sm-3 col-form-label">Payment Method</label>
        <div class="col-sm-5">
          <select name="" id="filterPaymentMethod" class="form-control form-control-sm">
            <option value="all">All</option>
            <option value="prepaid">Prepaid</option>
            <option value="postpaid">Postpaid</option>
          </select>
        </div>
      </div>
      <!--  -->
      <!--  -->
      <div class="text-center">
        <button style="margin:10px 5px;" id="apply_filter" class="btn btn-add btn-sm pull-right"><i class='bx bx-filter-alt'></i>Apply</button>
        <button style="margin:10px 5px;" id="reset_filter" class="btn btn-tools btn-sm pull-right"><i class='bx bx-reset' ></i>Reset</button>
      </div>
      <!--  -->
      </div>
    </div>
    <!-- RIGHT BUTTON -->
    <div class="col-md-7 text-right">
      <button type="button" class="btn btn-add" id="importCustomers"><i class='bx bx-add-to-queue'></i> Add / Replace</button>
      <button type="button" class="btn btn-export" style="margin-left: 5px;" id="exportCustomers"><i class='bx bx-export'></i> Export</button>
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

        <form method="post" action="" id="importCustomersForm">
          <div class="modal-header">
            <h5 class="modal-title" id="staticBackdropLabel">Import Customers</h5>
          </div>

          <div class="modal-body">
              <div class="form-group mb-3">
                <div class="upload-container">
                  <div class="border-container">
                    <input type="file" class="form-control" name="inputCustomerFileImport" id="inputFileImport" style="height: 100%;">
                  </div>
                </div>
              </div>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary d-flex align-items-center" id="importCustomersSubmitButton">
              <span class="spinner-button spinner-border spinner-border-sm hide" role="status" aria-hidden="true"></span>
              <span class="text-button ml-2">Import</span>
            </button>
          </div>
        </form>

      </div>
    </div>
  </div>
  <!-- Result Customer Import -->
  <div class="modal fade" id="modalCustomerImportResults" tabindex="-1" aria-labelledby="modalCustomerImportResultsLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
      <div class="modal-content">
        <!-- header  -->
        <div class="modal-header">
          <h5 class="modal-title">Customer Import Results</h5>
        </div>
        <!-- body -->
        <div class="modal-body">
          <div class="import-result-detail mt-2 mb-3 border p-3">
            <div class="row mb-2">
              <div class="col-md-6">
                <b>Insert Task</b>: 
                <ul>
                  <li>Total: <span id="resultImportCustomerInsertTotal"></span></li>
                  <li>Success: <span id="resultImportCustomerInsertSuccess"></span></li>
                  <li>Fail: <span id="resultImportCustomerInsertFail"></span></li>
                </ul>
              </div>
              <div class="col-md-6">
                <b>Replace Task</b>: 
                <ul>
                  <li>Total: <span id="resultImportCustomerReplaceTotal"></span></li>
                  <li>Success: <span id="resultImportCustomerReplaceSuccess"></span></li>
                  <li>Fail: <span id="resultImportCustomerReplaceFail"></span></li>
                </ul>
              </div>
            </div>
          </div>
          
          <table class="table table-bordered">
            <thead>
              <tr>
                <th style="width: 10%;">Row</th>
                <th style="width: 20%;">Action</th>
                <th style="width: 70%;">Message</th>
              </tr>
            </thead>
            <tbody id="customerImportResultTbody">
              
            </tbody>
          </table>
        </div>
        <!-- footer -->
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          <!-- <button type="button" class="btn btn-primary" id="saveCustomerEdited">Save</button> -->
        </div>
        <!--  -->
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
                <input disabled type="text" class="form-control form-control-sm" id="inputID" value="xxxx">
              </div>

              <div class="form-group col-md-6">
                <label for="inputParentID">Parent ID</label>
                <input type="text" class="form-control form-control-sm" id="inputParentID">
              </div>
            </div>
            <!--  -->
            <div class="form-row">
              <div class="form-group col-md-12">
                <label for="inputCustomerID">Customer ID</label>
                <input type="text" class="form-control form-control-sm" id="inputCustomerID">
              </div>
            </div>
            <!--  -->
            <div class="form-row">
              <div class="form-group col-md-12">
                <label for="inputCustomerName">Customer Name</label>
                <input type="text" class="form-control form-control-sm" id="inputCustomerName">
              </div>
            </div>
            <!--  -->
            <div class="form-row">
              <div class="form-group col-md-12">
                <label for="inputOffsetAcct">Offset Acct</label>
                <input type="text" class="form-control form-control-sm" id="inputOffsetAcct">
              </div>
            </div>
            <!--  -->
            <div class="form-row">
              <div class="form-group col-md-12">
                <label for="inputOffsetAcctName">Offset Acct Name</label>
                <input type="text" class="form-control form-control-sm" id="inputOffsetAcctName">
              </div>
            </div>
            <!--  -->
            <div class="form-row">
              <div class="form-group col-md-6">
                <label for="inputCompany">Company</label>
                <!-- <input type="text" class="form-control form-control-sm" id="inputCompany"> -->
                <select class="form-control form-control-sm" id="inputCompany">
                  <option value="RPTH">RPTH</option>
                  <!-- <option value="MAX">MAX</option> -->
                </select>
              </div>
              <div class="form-group col-md-6">
                <label for="inputPaymentMethod">Payment Method</label>
                <!-- <input type="text" class="form-control form-control-sm" id="inputPaymentMethod"> -->
                <select class="form-control form-control-sm" id="inputPaymentMethod">
                  <option value="prepaid">prepaid</option>
                  <!-- <option value="postpaid">postpaid</option> -->
                </select>
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

  <!-- Delete Customer Modal -->
  <div class="modal fade" id="modalDeleteCustomer" tabindex="-1" aria-labelledby="modalDeleteCustomerLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <!-- header  -->
        <div class="modal-header">
          <h5 class="modal-title">Delete Customer</h5>
        </div>
        <!-- body -->
        <div class="modal-body">
          <p>Are you sure to delete this customer ?</p>
        </div>
        <!-- footer -->
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">No</button>
          <button type="button" class="btn btn-danger" id="confirmDeleteCustomerButton">
            <span class="spinner-button spinner-border spinner-border-sm hide" role="status" aria-hidden="true"></span>
            <span class="text-button ml-2">Yes</span>
          </button>
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