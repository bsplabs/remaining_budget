<link href="<?php echo BASE_URL; ?>/public/css/report.css" rel="stylesheet">

<?php require_once APPROOT . "/views/layout/head.php"; ?>

<script>
  var base_url = "<?php echo BASE_URL; ?>";
  const month = "<?php echo $data["month"]; ?>";
  const year = "<?php echo $data["year"]; ?>";
</script>

<div class="container-fluid mt-5 report-page">
  <br>
  <!-- MONTH/YEAR SELECTION -->
  <div class="row justify-content-end">
    <div class="col-md-9"><h2>Media Reconciliations Management</h2></div>
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

  <!-- DATA STORE -->
  <div class="border p-5 mt-3 resources">

    <div class="row resources-box">
      <!-- Card Data Status -->
      <div class="col-md-3">
        <div class="card resource-box media-wallet">
          <div class="card-body">
            <h5 class="card-title">Media Wallet</h5>
            <h6 class="card-subtitle mb-2 text-muted">System Task</h6>

            <!-- SHOW STATUS RESULT -->
            <div class="resource-box__icon d-flex justify-content-center">
              <i class='bx bx-rotate-right processing'></i>
              <i class='bx bx-check-circle ready' style="display: none;"></i>
            </div>
            <div class="resource-box__detail d-flex justify-content-between">
              <span class="data-count badge badge-pill badge-light bordered m-1 position-left"></span>
              <span class="status-message badge badge-pill badge-warning m-1 position-right">Processing</span>
            </div>
            <!-- END -->
          </div>
        </div>
      </div>
      <!--  -->

      <!-- Card Data Status -->
      <div class="col-md-3">
        <div class="card resource-box withholding-tax">
          <div class="card-body">
            <!-- Header -->
            <h5 class="card-title">Withholding Tax</h5>
            <h6 class="card-subtitle mb-2 text-muted">System Task</h6>

            <!-- SHOW STATUS RESULT -->
            <div class="resource-box__icon d-flex justify-content-center">
              <i class='bx bx-rotate-right processing'></i>
              <i class='bx bx-check-circle ready' style="display: none;"></i>
            </div>

            <div class="resource-box__detail d-flex justify-content-between">
              <span class="data-count badge badge-pill badge-light m-1 position-left"></span>
              <span class="status-message badge badge-pill badge-warning m-1 position-right">Processing</span>
            </div>
            <!-- END -->
          </div>
        </div>
      </div>
      <!--  -->

      <!-- Card Data Status -->
      <div class="col-md-3">
        <div class="card resource-box free-click-cost">
          <div class="card-body">
            <!--  -->
            <h5 class="card-title">Free Click Cost</h5>
            <h6 class="card-subtitle mb-2 text-muted">System Task</h6>
            
            <!-- SHOW STATUS RESULT -->
            <div class="resource-box__icon d-flex justify-content-center">
              <i class='bx bx-rotate-right processing'></i>
              <i class='bx bx-check-circle ready' style="display: none;"></i>
            </div>
            <div class="resource-box__detail d-flex justify-content-between">
              <span class="data-count badge badge-pill badge-light m-1 position-left"></span>
              <span class="status-message badge badge-pill badge-warning m-1 position-right">Processing</span>
            </div>
            <!--  -->
          </div>
        </div>
      </div>
      <!--  -->

      <!-- Card Data Status -->
      <div class="col-md-3">
        <div class="card resource-box ice">
          <div class="card-body">
            <!--  -->
            <h5 class="card-title">Remaining ICE</h5>
            <h6 class="card-subtitle mb-2 text-muted">System Task</h6>

            <!-- SHOW STATUS RESULT -->
            <div class="resource-box__icon d-flex justify-content-center">
              <i class='bx bx-rotate-right processing'></i>
              <i class='bx bx-check-circle ready' style="display: none;"></i>
            </div>
            <div class="resource-box__detail d-flex justify-content-between">
              <span class="data-count badge badge-pill badge-light m-1 position-left"></span>
              <span class="status-message badge badge-pill badge-warning m-1 position-right">Processing</span>
            </div>
            <!--  -->
          </div>
        </div>
      </div>
      <!--  -->
    </div>

    <div class="row resources-box mt-4 justify-content-center">
      <!-- Card Data Status - GL Cash Advance -->
      <div class="col-md-3">
        <div class="card resource-box upload-gl-revenue-file gl-cash-advance">
          <!-- Card Body  -->
          <div class="card-body">
            <div class="d-flex justify-content-between">
              <h5 class="card-title">GL Cash Advance</h5>
              <i class='bx bx-edit text-muted re-upload-file' style="display: none; cursor: pointer; font-size: 22px;"></i>
            </div>

            <!-- DISPLAY UPLOAD FORM -->
            <div class="import-file-form">
              <h6 class="card-subtitle mb-2 text-muted text-danger">File extension support only .xlsx, .xls</h6>
              <form enctype="multipart/form-data" class="pt-3 mt-2" id="gl-cash-advance-form">
                <div class="mb-3">
                  <p class="mb-2"><a id="example-file-link" href="<?php echo BASE_URL . '/temp/example/gl_cash_advance_example.xlsx'; ?>">ตัวอย่างไฟล์</a></p>

                  <div class="upload-container">
                    <div class="border-container">
                      <input type="file" id="cash-advance-input-file" name="cashAdvanceInputFile" class="form-control" accept=".xls,.xlsx" style="height: 100%">
                    </div>
                  </div>
                </div>
                <div class="d-flex justify-content-center">
                  <button type="submit" id="submit" name="importCashAdvanceButton" class="btn btn-add d-flex" style="align-items: center;">
                    <span class="spinner-button spinner-border spinner-border-sm hide" role="status" aria-hidden="true"></span>
                    <span class="text-button ml-2">Import</span>
                  </button>
                  <span class="d-flex justify-content-center align-items-center ml-3 hide cancel-re-upload-file">
                    <a href="#" style="font-size: 16px;padding-top: 1px;" class="cancel-re-upload-file__action">
                      <i class="bx bx-reset"></i> Cancel
                    </a>
                  </span>
                
                </div>
              </form>
            </div>
            <!-- END -->

            <!-- SHOW IMPORTING SUCCESS -->
            <div class="import-file-result hide">
              <h6 class="card-subtitle mb-2 text-muted imported-at"></h6>
              <div class="resource-box__icon d-flex justify-content-center">
                <i class='bx bx-check-circle ready'></i>
              </div>
              <div class="resource-box__detail d-flex justify-content-between">
                <span class="row-count badge badge-pill badge-light m-1 position-left"></span>
                <span class="badge badge-pill badge-success m-1 position-right">Ready</span>
              </div>
            </div>
            <!-- END -->
          </div>
        </div>
      </div>
      <!--  -->

      <!-- Card Data Status - Google Spending -->
      <div class="col-md-3">
        <div class="card resource-box upload-google-spending-file google-spending">
          <div class="card-body">
          <!-- Header -->
            <div class="d-flex justify-content-between">
              <h5 class="card-title">Google Spending</h5>
              <i class='bx bx-edit text-muted re-upload-file' style="display: none; cursor: pointer; font-size: 22px;"></i>
            </div>

            <!-- DISPLAY UPLOAD FORM -->
            <div class="import-file-form">
              <h6 class="card-subtitle mb-2 text-muted">File extension support only .csv</h6>
              <form enctype="multipart/form-data" class="pt-3" id="google-spending-form">
                <div class="mb-3">
                  <p class="mb-2"><a id="example-file-link" href="<?php echo BASE_URL . '/temp/example/google_spending_example.csv'; ?>">ตัวอย่างไฟล์</a></p>
                  
                  <div class="upload-container">
                    <div class="border-container">
                      <input type="file" id="google-spending-input-file" name="googleSpendingInputFile" class="form-control" accept=".csv" style="height: 100%">
                    </div>
                  </div>
                </div>
                <div class="d-flex justify-content-center">
                  <button type="submit" id="submit" name="importGoogleSpendingButton" class="btn btn-add d-flex align-items-center">
                    <span class="spinner-button spinner-border spinner-border-sm hide" role="status" aria-hidden="true"></span>
                    <span class="text-button ml-2">Import</span>
                  </button>
                  <span class="d-flex justify-content-center align-items-center ml-3 hide cancel-re-upload-file">
                    <a href="#" style="font-size: 16px;padding-top: 1px;" class="cancel-re-upload-file__action">
                      <i class="bx bx-reset"></i> Cancel
                    </a>
                  </span>
                
                </div>
              </form>
            </div>
            <!-- END -->

            <!-- SHOW IMPORTING SUCCESS -->
            <div class="import-file-result hide">
              <h6 class="card-subtitle mb-2 text-muted imported-at"></h6>
              <div class="resource-box__icon d-flex justify-content-center">
                <i class='bx bx-check-circle ready'></i>
              </div>
              <div class="resource-box__detail d-flex justify-content-between">
                <span class="row-count badge badge-pill badge-light m-1 position-left"></span>
                <span class="badge badge-pill badge-success m-1 position-right">Ready</span>
              </div>
            </div>
            <!-- END -->
          </div>
        </div>
      </div>
      <!--  -->

      <!-- Card Data Status - Facebook Spending -->
      <div class="col-md-3">
        <div class="card resource-box upload-facebook-spending-file facebook-spending">
          <div class="card-body">
          <!-- Header -->
            <div class="d-flex justify-content-between">
              <h5 class="card-title">Facebook Spending</h5>
              <i class='bx bx-edit text-muted re-upload-file' style="display: none; cursor: pointer; font-size: 22px;"></i>
            </div>

            <!-- DISPLAY UPLOAD FORM -->
            <div class="import-file-form">
              <h6 class="card-subtitle mb-2 text-muted">File extension support only .csv</h6>
              <form enctype="multipart/form-data" class="pt-3" id="facebook-spending-form">
                <div class="mb-3">
                  <p class="mb-2"><a id="example-file-link" href="<?php echo BASE_URL . '/temp/example/facebook_spending_example.csv'; ?>">ตัวอย่างไฟล์</a></p>

                  <div class="upload-container">
                    <div class="border-container">
                      <input type="file" id="facebook-spending-input-file" name="facebookSpendingInputFile" class="form-control" accept=".csv" style="height: 100%">
                    </div>
                  </div>
                </div>
                <div class="d-flex justify-content-center">
                  <button type="submit" id="submit" name="importFacebookSpendingButton" class="btn btn-add d-flex align-items-center">
                    <span class="spinner-button spinner-border spinner-border-sm hide" role="status" aria-hidden="true"></span>
                    <span class="text-button ml-2">Import</span>
                  </button>
                  <span class="d-flex justify-content-center align-items-center ml-3 hide cancel-re-upload-file">
                    <a href="#" style="font-size: 16px;padding-top: 1px;" class="cancel-re-upload-file__action">
                      <i class="bx bx-reset"></i> Cancel
                    </a>
                  </span>

                </div>
              </form>
            </div>
            <!-- END -->

            <!-- SHOW RESULT -->
            <div class="import-file-result hide">
              <h6 class="card-subtitle mb-2 text-muted imported-at"></h6>
              <div class="resource-box__icon d-flex justify-content-center">
                <i class='bx bx-check-circle ready'></i>
              </div>
              <div class="resource-box__detail d-flex justify-content-between">
                <span class="row-count badge badge-pill badge-light m-1 position-left"></span>
                <span class="badge badge-pill badge-success m-1 position-right">Ready</span>
              </div>
            </div>
            <!-- END -->
          </div>
        </div>
      </div>
      <!--  -->

      <!-- Card Data Status - Transfer -->
      <div class="col-md-3">
        <div class="card resource-box upload-transfer-file transfer">
          <div class="card-body">
          <!-- Header -->
            <div class="d-flex justify-content-between">
              <h5 class="card-title">Transfer<small> (optional)</small></h5>
              <i class='bx bx-edit text-muted re-upload-file' style="display: none; cursor: pointer; font-size: 22px;"></i>
            </div>

            <!-- DISPLAY UPLOAD FORM -->
            <div class="import-file-form">
              <h6 class="card-subtitle mb-2 text-muted">File extension support only .csv</h6>
              <form enctype="multipart/form-data" class="pt-3" id="upload_transfer_file_form">
                <div class="mb-3">
                  <p class="mb-2"><a id="example-file-link" href="<?php echo BASE_URL . '/temp/example/wallet_transfer_example.csv'; ?>">ตัวอย่างไฟล์</a></p>

                  <div class="upload-container">
                    <div class="border-container">
                      <input type="file" id="transfer-file-upload" name="transferInputFile" class="form-control" accept=".csv" style="height: 100%">
                    </div>
                  </div>
                </div>
                <div class="d-flex justify-content-center">
                  <button type="submit" id="submit" name="import" class="btn btn-add d-flex align-items-center">
                    <span class="spinner-button spinner-border spinner-border-sm hide" role="status" aria-hidden="true"></span>
                    <span class="text-button ml-2">Import</span>
                  </button>
                  <span class="d-flex justify-content-center align-items-center ml-3 hide cancel-re-upload-file">
                    <a href="#" style="font-size: 16px;padding-top: 1px;" class="cancel-re-upload-file__action">
                      <i class="bx bx-reset"></i> Cancel
                    </a>
                  </span>

                </div>
              </form>
            </div>
            <!-- END -->

            <!-- SHOW RESULT -->
            <div class="import-file-result hide">
              <h6 class="card-subtitle mb-2 text-muted imported-at"></h6>
              <div class="resource-box__icon d-flex justify-content-center">
                <i class='bx bx-check-circle ready'></i>
              </div>
              <div class="resource-box__detail d-flex justify-content-between">
                <span class="row-count badge badge-pill badge-light m-1 position-left"></span>
                <span class="badge badge-pill badge-success m-1 position-right">Ready</span>
              </div>
            </div>
            <!-- END -->
          </div>
        </div>
      </div>
      <!--  -->
    </div>

    <!-- Spinner -->
    <!-- <div class="overlay"></div> -->
    <div class="spanner">
      <div class="loader"></div>
      <p>Getting all about resources status. please wait...</p>
    </div>
  </div>
  <!--  -->

  <!-- GENERATE -->
  <div class="row mt-3 justify-content-end">
    <div class="col-md-3 text-right">
      <button class="btn btn-warning" id="generateButton" type="button" disabled>Generate</button>
    </div>
  </div>
  <!--  -->
  <br>
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

<?php require_once APPROOT . "/views/layout/script.php"; ?>

<script src="<?php echo BASE_URL; ?>/public/js/report/index.js"></script>