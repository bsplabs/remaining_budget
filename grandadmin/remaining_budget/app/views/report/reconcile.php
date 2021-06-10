<link href="<?php echo BASE_URL; ?>/public/css/home.css" rel="stylesheet">
<link href="<?php echo BASE_URL; ?>/public/css/report.css" rel="stylesheet">
<link href="<?php echo BASE_URL; ?>/public/css/font-awesome.min.css" rel="stylesheet">
<link href="<?php echo BASE_URL; ?>/public/vendors/datatables_bootstrap4/dataTables.bootstrap4.min.css" rel="stylesheet">
<link href="<?php echo BASE_URL; ?>/public/vendors/datatables_bootstrap4/fixedColumns.dataTables.min.css" rel="stylesheet">
<script>
var base_url = "<?php echo BASE_URL; ?>";
var year = "<?php echo $data["year"];?>";
var month = "<?php echo $data["month"];?>";
var is_closed = "<?php echo $data["is_closed"];?>";
</script>
<?php require_once APPROOT . "/views/layout/head.php"; ?>
<div class="container-fluid report-reconcile-page">
  <br>
  <!-- MONTH/YEAR SELECTION -->
  <div class="row justify-content-end mb-3">
    <div class="col-md-9">
      <h2 style="margin-bottom: 0;">
        Media Reconciliations Management <?php echo $data['is_closed'] ? "(Read-only)" : ""; ?>
      </h2>
      <?php 
        if ($data['is_closed']) {
          echo "<small style='font-size: 100% !important;'>Closed by " . $data['closed_by'] . "</small>";
        }
      ?>
    </div>

    <div class="col-md-3">

    <select class="form-control" aria-label="" id="month-year-selector">
      <?php 
          foreach ($data["month_year_lists"] as $key => $my) { 
            if ($data["month_year_selected"] === $key) {
              echo "<option value='$key' selected>$my</option>";
            } else {
              echo "<option value='$key'>$my</option>";
            }
          }
        ?>
      </select>
    </div>
  </div>
  <!--  -->
  <div class="row">
    <div class="col-md-6">
      <!--  -->
      <label id="filter-container-btn" style="cursor:pointer;"><i class='bx bx-filter-alt'></i> Filter <i id="filter_expand_icon" class='bx bx-chevron-down'></i></label>
      <div class="row" id="filter-container" style="display:none;">
        <div class="col-md-12 border-right1">
          <div>
            <div class="form-inline" style="padding-top:0px;">
              <label style="padding-right:33px;">Cash Advance</label>
              <select class="form-control form-control-sm" id="filter_cash_advance_condition">
                <option value="equal">Equal</option>
                <option value="less_than">Less than</option>
                <option value="greater_than">Greater than</option>
                <option value="not_equal">Not equal</option>
              </select>
              <input id="filter_cash_advance" style="margin-left:10px;" class="form-control form-control-sm" type="text" placeholder="">
            </div>

            <div class="form-inline" style="padding-top:10px;">
              <label style="padding-right:10px;">Remaining Budget</label>
              <select class="form-control form-control-sm" id="filter_remaining_budget_condition">
                <option value="equal">Equal</option>
                <option value="less_than">Less than</option>
                <option value="greater_than">Greater than</option>
                <option value="not_equal">Not equal</option>
              </select>
              <input id="filter_remaining_budget" style="margin-left:10px;" class="form-control form-control-sm" type="text" placeholder="">
            </div>

            <div class="form-inline" style="padding-top:10px;">
              <label style="padding-right:55px;">Difference</label>
              <select class="form-control form-control-sm" id="filter_difference_condition">
                <option value="equal">Equal</option>
                <option value="less_than">Less than</option>
                <option value="greater_than">Greater than</option>
                <option value="not_equal">Not equal</option>
              </select>
              <input id="filter_difference" style="margin-left:10px;" class="form-control form-control-sm" type="text" placeholder="">
            </div>
            <div class="form-inline" style="padding-top:10px;"><small style="color:red;display:none;" id="filter_error_message">Value shold be a Number.</small></div>
          </div>
        </div>

        <div class="col-md-12 col-xs-6" style="padding-top: 10px;">
          <button style="margin:0px 5px;"id="reset_filter" class="btn btn-reset btn-sm " data-bs-toggle="modal" role="button"><i class='bx bx-reset' ></i>Reset</button>
          <button style="margin:0px 0px 0px 5px;" id="apply_filter" class="btn btn-add btn-sm " data-bs-toggle="modal" role="button"><i class='bx bx-filter-alt'></i>Apply</button>
          
        </div>
      </div>
 
    </div>

    <div class="col-md-6 text-right">
      <?php if(!$data["is_closed"]): ?>
        <button id="reconcile_add_replace_btn" class="btn btn-add btn-sm" style="margin-bottom: 5px;" data-bs-toggle="modal" href="#modal" role="button"><i class='bx bx-add-to-queue'></i> Add / Replace</button>
        <button id="reconcile_close_period" class="btn btn-export btn-sm" style="margin-left: 5px;margin-bottom: 5px;" data-bs-toggle="modal" role="button"><i class='bx bx-list-check'></i> Close Period</button>
      <?php endif; ?>
      <button id="reconcile_export_btn" class="btn btn-export btn-sm" style="margin-left: 5px;margin-bottom: 5px;" data-bs-toggle="modal" role="button"><i class='bx bx-export'></i> Export</button>
      
    </div>
  </div>
  
  <div class="mt-3" id="reconcile_table_wrapper">
    <table class="table table-bordered text-nowrap table-hover" id="report_reconcile_table">
      <!-- COLGROUP -->
      <colgroup>
        <col />
        <col />
        <col />
        <col />
        <col />
        <col />
        <col />
        <col style="background-color: #c2dcf840;" />
        <col style="background-color: #c2dcf840;" />
        <col style="background-color: #c2dcf840;" />
        <col style="background-color: #c2dcf840;" />
        <col style="background-color: #c2dcf840;" />
        <col style="background-color: #c2dcf840;" />
        <col style="background-color: #c2dcf840;" />
        <col style="background-color: #c2dcf840;" />
        <col style="background-color: #c2dcf840;" />
        <col style="background-color: #c2dcf840;" />
        <col style="background-color: #c2dcf840;" />
        <col style="background-color: #c2dcf840;" />
        <!--  -->
        <!-- <col style="background-color: #FFF2CC;" /> -->
        <!--  -->
        <col />
        <col />
        <col />
        <col />
        <col />
        <col />
        <!--  -->
        <!-- <col style="background-color: #FFF2CC;" /> -->
        <!--  -->
        <col style="background-color: #c2dcf840;" />
        <col style="background-color: #c2dcf840;" />
      </colg>
      <!-- THEAD -->
      <thead>
        <tr>
          <th scope="col" colspan="7" class="text-center">Identifier</th>
          <!-- <th scope="col"></th>
          <th scope="col"></th>
          <th scope="col"></th>
          <th scope="col"></th> -->
          <th scope="col" colspan="12" class="text-center">Accounting</th>
          <!-- <th scope="col"></th>
          <th scope="col"></th>
          <th scope="col"></th>
          <th scope="col"></th>
          <th scope="col"></th>
          <th scope="col"></th>
          <th scope="col"></th>
          <th scope="col"></th>
          <th scope="col"></th>
          <th scope="col"></th>
          <th scope="col"></th>
          <th scope="col"></th> -->
          <!--  -->
          <!-- <th scope="col"></th> -->
          <!--  -->
          <th scope="col" colspan="6" class="text-center">Other system</th>
          <!-- <th scope="col"></th>
          <th scope="col"></th>
          <th scope="col"></th>
          <th scope="col"></th>
          <th scope="col"></th>
          <th scope="col"></th> -->
          <!--  -->
          <!-- <th scope="col"></th> -->
          <!--  -->
          <th scope="col" colspan="2" class="text-center">Result</th>
          <!-- <th scope="col"></th> -->
        </tr>
        <tr>
          <th scope="col">Parent Customer ID</th>
          <th scope="col">Customer ID</th>
          <th scope="col">Customer Name</th>
          <th scope="col">Offset Acct</th>
          <th scope="col">Offset Acct Name</th>
          <th scope="col">Company</th>
          <th scope="col">Payment Method</th>
          <th scope="col">Remaing ทางบัญชี</th>
          <th scope="col">Adjust ยอดยกมา</th>
          <th scope="col">Receive</th>
          <th scope="col">Invoice</th>
          <th scope="col">Transfer (โอนเงินระหว่างบัญชี)</th>
          <th scope="col">คืนเงินค่าโฆษณา</th>
          <th scope="col">Spending (-)</th>
          <th scope="col">JE + Free Clickcost</th>
          <th scope="col">Free Clickcost (ค่าใช้จ่ายต้องห้าม)</th>
          <th scope="col">Adjustment</th>
          <th scope="col">Max</th>
          <th scope="col">Cash Advance</th>
          <th scope="col">Remaining ICE</th>
          <th scope="col">Wallet</th>
          <th scope="col">Wallet - Free Clickcost (-)</th>
          <th scope="col">Withholding Tax</th>
          <th scope="col">Adjust</th>
          <th scope="col">Remaining Budget</th>
          <th scope="col">Difference</th>
          <th scope="col">Note</th>
        </tr>
      </thead>
      <!-- TBODY -->
      <tbody id="reconcile_content">
      </tbody>
    </table>
  </div>

  <!-- First modal dialog -->
  <div class="modal" tabindex="-1" id="reconcile_add_replace_modal">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Add or Replace New Data</h5>
        </div>
        <div class="modal-body">
          <form enctype="multipart/form-data" class="pt-3" id="reconcile_add_replace_form">
            <!--  -->
            <div class="mb-4">
                <label for="file-re-upload-selector" class="form-label">Choose input type</label>
                <select class="form-control" id="file-re-upload-selector" name="file-re-upload-selector">
                  <option selected value="gl_revenue">GL Revenue (Receive)</option>
                  <option value="google_spending">Google Spending</option>
                  <option value="facebook_spending">FaceBook Spending</option>
                  <option value="transfer">Transfer</option>
                  <option value="adjustment">Adjustment</option>
                  <option value="apis">APIs</option>
                </select>
            </div>
            <!--  -->
            <p class="mb-2"><a id="example-file-link" href="<?php echo BASE_URL . '/temp/example/gl_cash_advance_example.xlsx'; ?>">ตัวอย่างไฟล์</a></p>

            <div class="mb-3" id="input-file-upload">
              <div class="upload-container">
                <div class="border-container">
                  <input type="file" id="reconcile_add_replace_file" name="reconcile_add_replace_file" class="form-control" style="height: 100% !important;">
                </div>
              </div>
              <small id="file_extension_message">File extension support only .xls, .xlsx</small>
            </div>
            <!--  -->
            <div class="border p-3" id="checkbox-api-group" style="display: none;">
              <label>Select input type to update</label>
              <div class="form-check">
                <input class="form-check-input" type="checkbox" value="" id="media-wallet-checkbox">
                <label class="form-check-label" for="media-wallet-checkbox">
                  Media Wallet
                </label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="checkbox" value="" id="withholding-tax-checkbox">
                <label class="form-check-label" for="withholding-tax-checkbox">
                  Withholding Tax
                </label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="checkbox" value="" id="free-click-cost-checkbox">
                <label class="form-check-label" for="free-click-cost-checkbox">
                  Free Click Cost
                </label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="checkbox" value="" id="ice-checkbox">
                <label class="form-check-label" for="ice-checkbox">
                  Remaining ICE
                </label>
              </div>
            </div>
            <!--  -->
          </form>
          <small id="add_replace_error_message" style="color:red;display:none;"></small>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-reset" id="re-upload-file-close-button" data-bs-dismiss="modal">Close</button>
          <button type="button" class="btn btn-add" id="re-upload-file-button" data-bs-target="#modal2" data-bs-toggle="modal" data-bs-dismiss="modal" data-loading-text="<i class='fa fa-circle-o-notch fa-spin'></i> Uploading...">Upload</button>
          <button class="btn btn-add" id="get-and-update-button" style="display: none;" data-bs-target="#modal2" data-bs-toggle="modal" data-bs-dismiss="modal">Get and Update Data</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="modalEditCell" tabindex="-1" aria-labelledby="modalEditCellLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <div>
            <h5 class="modal-title">Edit Adjustment (Back-end)</h5>
            <p class="mb-0 text-muted">customer ID: <span id="show_id"></span></p>
            <p class="mb-0 text-muted">customer name: <span id="show_name"></span></p>
          </div>
          <!-- <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button> -->
        </div>
        <div class="modal-body">
          <form>
            <div class="mb-3">
              <label for="edit-value-input" class="form-label">Value</label>
              <input type="text" class="form-control" id="edit-value-input">
            </div>
            <div class="mb-3">
              <label for="edit-note-input" class="form-label">Note</label>
              <textarea class="form-control" id="edit-note-input" rows="3"></textarea>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-reset" data-bs-dismiss="modal">Close</button>
          <button type="button" class="btn btn-add">Save</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Edit reconcile note modal -->
  <div class="modal fade" tabindex="-1" id="updateReconcileNoteModal">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header" style="display:block;">
        <h5 class="modal-title">Edit Reconcile Note</h5>
        <ul class="m-0 mt-2">
          <li>Customer ID: <span id="customerIdEditNote"></span></li>
          <li>Customer Name: <span id="customerNameEditNote"></span></li>
        </ul>
      </div>
      
      <div class="modal-body">
        <input type="hidden" name="id" value="" id="reconcileId">
        <div class="form-group">
          <label for="edit_able_note">Note</label>
          <textarea class="form-control" id="editReconcileNote" rows="3"></textarea>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-reset" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-add d-flex align-items-center" id="editReconcileNoteBtnSubmit">
          <span class="spinner-button spinner-border spinner-border-sm hide" role="status" aria-hidden="true"></span>
          <span class="text-button ml-2">Save changes</span>
        </button>
      </div>
    </div>
  </div>
</div>

<div class="modal" tabindex="-1" id="AdjustModal">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header" style="display:block;">
        <h5 class="modal-title"></h5>
        <small><b>customer name: </b></small><small id ="edit_able_customer_name"></small>
      </div>
      <div class="modal-body">
      <div class="form-group" id="form-group-input-numeric">
        <label id="edit_able_value_label" for="edit_able_value">Adjust ยอดยกมา</label>
        <input type="text" class="form-control text-right" id="edit_able_value">
        <input type="hidden" class="form-control text-right" id="edit_able_type">
        <input type="hidden" class="form-control text-right" id="edit_able_report_id">
        <input type="hidden" class="form-control text-right" id="edit_able_row_id">
        <div class="error-message hide">* Please enter only numeric</div>
      </div>
      <div class="form-group">
        <label for="edit_able_note">Note</label>
        <textarea class="form-control" id="edit_able_note" rows="3"></textarea>
      </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-reset" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-add" id="edit_able_submit">Save changes</button>
      </div>
    </div>
  </div>
</div>
<!-- Delete Customer Modal -->
<div class="modal fade" id="modalClosePeriod" tabindex="-1" aria-labelledby="modalClosePeriodLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <!-- header  -->
        <div class="modal-header">
          <h5 class="modal-title">Close this period</h5>
        </div>
        <!-- body -->
        <div class="modal-body">
          <p>Are you sure to close this period ?</p>
        </div>
        <!-- footer -->
        <div class="modal-footer">
          <button type="button" class="btn btn-reset" data-dismiss="modal">No</button>
          <button type="button" class="btn btn-add" id="confirmClosePeriodButton">
            <span class="spinner-button spinner-border spinner-border-sm hide" role="status" aria-hidden="true"></span>
            <span class="text-button">Yes</span>
          </button>
        </div>  
        <!--  -->
      </div>
    </div>
  </div>
  <div class="loader-overlay" style="display:none;">
    <div class="spinner-border text-dark spinner-loader" role="status">
      <span class="sr-only">Loading...</span>
    </div>
  </div>
  <div class="position-fixed bottom-0 right-0 p-3" style="z-index: 5; right: 0; bottom: 0;">
  <div id="liveToast" class="toast hide" role="alert" aria-live="assertive" aria-atomic="true" data-delay="2000">
    <div class="toast-header">
      <strong id="toast_header" class="mr-auto"></strong>
      <button type="button" class="ml-2 mb-1 close" data-dismiss="toast" aria-label="Close">
        <span aria-hidden="true">&times;</span>
      </button>
    </div>
    <div id="toast_body" class="toast-body">
    </div>
  </div>
</div>

<!-- Modal -->
<div class="modal fade" id="modal-error-reporting" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
    <div class="modal-content">
      <!--  -->
      <div class="modal-header">
        <h5 class="modal-title">Error Reporting - <span id="modal-resource-name"></span></h5>
      </div>
      <!--  -->
      <div class="modal-body">
        <!-- Assign value : resource type -->
        <input type="hidden" value="" id="re-import-resource-type">
        <!-- Table display result -->
        <table class="table table-striped">
          <thead>
            <tr>
              <th scope="col"></th>
              <th scope="col" class="text-right">Result</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <th scope="row">Total</th>
              <td class="text-muted text-right" id="total"></td>
            </tr>
            <tr>
              <th scope="row">Valid</th>
              <td class="text-success text-right" id="valid-total"></td>
            </tr>
            <tr>
              <th scope="row">Invalid <span class="error-type-lists"></span></th>
              <td class="text-danger text-right" id="invalid-total"></td>
            </tr>
          </tbody>
        </table>
        <div id="message-suggestion">
          <span class="message-suggestion__gl-cash-advance text-danger hide">
            ** Please check in your Excel file.
          </span>
          <span class="message-suggestion__facebook-spending text-danger hide">
            ** Please check billing_period column in your CSV file.
          </span>
        </div>
      </div>
      <!--  -->
      <div class="modal-footer">
        <button type="button" class="btn btn-export" data-dismiss="modal">Edit file & Re-import</button>
        <button type="button" class="btn btn-add" id="re-import-ignore-invalid-btn">Skip invalid data & Continue to import valid data</button>
      </div>
      <!--  -->
    </div>
  </div>
</div>
<!-- End Modal -->

<!-- Spinner -->
<div class="reconcile-overlay">
  <div class="spanner">
    <div class="loader"></div>
    <p id="message_loading">Exporting data. please wait...</p>
  </div>
</div>

<?php require_once APPROOT . "/views/layout/script.php"; ?>

<script src="<?php echo BASE_URL; ?>/public/vendors/datatables_bootstrap4/jquery.dataTables.min.js"></script>
<script src="<?php echo BASE_URL; ?>/public/vendors/datatables_bootstrap4/dataTables.bootstrap4.min.js"></script>
<script src="<?php echo BASE_URL; ?>/public/vendors/datatables_bootstrap4/dataTables.fixedColumns.min.js"></script>
<script src="<?php echo BASE_URL; ?>/public/vendors/datatables_bootstrap4/dataTables.rowGroup.min.js"></script>
<script src="<?php echo BASE_URL; ?>/public/vendors/popper.min.js"></script>
<script src="<?php echo BASE_URL; ?>/public/js/report/reconcile.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/numeral.js/2.0.6/numeral.min.js"></script>
