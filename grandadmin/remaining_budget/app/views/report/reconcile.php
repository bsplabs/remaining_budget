<link href="<?php echo BASE_URL; ?>/public/css/home.css" rel="stylesheet">
<link href="<?php echo BASE_URL; ?>/public/css/report.css" rel="stylesheet">
<link href="<?php echo BASE_URL; ?>/public/css/font-awesome.min.css" rel="stylesheet">
<script>
var base_url = "<?php echo BASE_URL; ?>";
var year = "<?php echo $data["year"];?>";
var month = "<?php echo $data["month"];?>";
</script>
<?php require_once APPROOT . "/views/layout/head.php"; ?>

<div class="container-fluid report-reconcile-page">
  <br>
  <!-- MONTH/YEAR SELECTION -->
  <div class="row justify-content-end">
    <div class="col-md-9"><h2>Reconcile Management</h2></div>
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

  <div class="d-flex justify-content-end mt-3">
    <button class="btn btn-primary" data-bs-toggle="modal" href="#modal" role="button"><i class='bx bx-add-to-queue'></i> Add / Replace</button>
    <button class="btn btn-warning" style="margin-left: 5px;" data-bs-toggle="modal" href="#modal" role="button"><i class='bx bx-export'></i> Export</button>
  </div>

  <div class="table-responsive mt-3">
    <table class="table table-bordered text-nowrap table-hover">
      <!-- COLGROUP -->
      <colgroup>
        <col />
        <col />
        <col />
        <col />
        <col />
        <col />
        <col />
        <col />
        <col />
        <col />
        <col />
        <col />
        <col />
        <col />
        <col />
        <col />
        <col />
        <col />
        <col />
        <!--  -->
        <col style="background-color: #FFF2CC;" />
        <!--  -->
        <col />
        <col />
        <col />
        <col />
        <col />
        <col />
        <!--  -->
        <col style="background-color: #FFF2CC;" />
        <!--  -->
        <col />
        <col />
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
          <th scope="col"></th>
          <!--  -->
          <th scope="col" colspan="6" class="text-center">Other system</th>
          <!-- <th scope="col"></th>
          <th scope="col"></th>
          <th scope="col"></th>
          <th scope="col"></th>
          <th scope="col"></th>
          <th scope="col"></th> -->
          <!--  -->
          <th scope="col"></th>
          <!--  -->
          <th scope="col" colspan="2" class="text-center">Result</th>
          <!-- <th scope="col"></th> -->
        </tr>
        <tr>
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
          <th scope="col"></th>
          <th scope="col"></th>
          <th scope="col"></th>
          <th scope="col"></th>
          <th class="text-center" style="background-color: #FCE5CD;" scope="col" colspan="4">Adjustment</th>
          <!-- <th scope="col"></th>
          <th scope="col"></th>
          <th scope="col"></th> -->
          <th scope="col"></th>
          <!--  -->
          <th scope="col"></th>
          <!--  -->
          <th scope="col"></th>
          <th scope="col"></th>
          <th scope="col"></th>
          <th scope="col"></th>
          <th scope="col"></th>
          <th scope="col"></th>
          <!--  -->
          <th scope="col"></th>
          <!--  -->
          <th scope="col"></th>
          <th scope="col"></th>
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
          <th scope="col"></th>
          <th scope="col">Remaining ICE</th>
          <th scope="col">Wallet</th>
          <th scope="col">Wallet - Free Clickcost (-)</th>
          <th scope="col">Withholding Tax</th>
          <th scope="col">Adjust</th>
          <th scope="col">Remaining Budget</th>
          <th scope="col"></th>
          <th scope="col">Difference</th>
          <th scope="col">Note</th>
        </tr>
      </thead>
      <!-- TBODY -->
      <tbody id="reconcile_content">
      </tbody>
    </table>
    <div id="reconcile_content_loading">loading...</div>
  </div>

  <div class="row mt-4">
    <nav aria-label="Page navigation example">
      <ul class="pagination justify-content-center">
        <li class="page-item disabled">
          <a class="page-link" href="#" tabindex="-1" aria-disabled="true">Previous</a>
        </li>
        <li class="page-item"><a class="page-link" href="#">1</a></li>
        <li class="page-item"><a class="page-link" href="#">2</a></li>
        <li class="page-item"><a class="page-link" href="#">3</a></li>
        <li class="page-item"><a class="page-link" href="#">4</a></li>
        <li class="page-item"><a class="page-link" href="#">5</a></li>
        <li class="page-item">
          <a class="page-link" href="#">Next</a>
        </li>
      </ul>
    </nav>
  </div>

  <!-- First modal dialog -->
  <div class="modal fade" id="modal" aria-hidden="true" aria-labelledby="..." tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Upload File to Repare Your Data</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form action="">
            <!--  -->
            <div class="mb-4">
              <label for="exampleFormControlInput1" class="form-label">Choose file type to upload</label>
              <select class="form-select" aria-label="Default select example" id="file-re-upload-selector">
                <option selected value="gl_revenue">GL Revenue (Receive)</option>
                <option value="transfer">Transfer</option>
                <option value="adjust_begining">Adjust ยอดยกมา</option>
                <option value="adjust_accounting">Adjust Accounting</option>
                <option value="adjust_frontend">Adjust Other System</option>
                <option value="apis">APIs</option>
              </select>
            </div>
            <!--  -->
            <div class="mb-3" id="input-file-upload">
              <div class="upload-container">
                <div class="border-container">
                  <input type="file" id="customer-file-upload" class="form-control">
                </div>
              </div>
            </div>
            <!--  -->
            <div class="border p-3" id="checkbox-api-group" style="display: none;">
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
                  ICE
                </label>
              </div>
            </div>
            <!--  -->
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button class="btn btn-primary" id="re-upload-file-button" data-bs-target="#modal2" data-bs-toggle="modal" data-bs-dismiss="modal">Upload</button>
          <button class="btn btn-primary" id="get-and-update-button" style="display: none;" data-bs-target="#modal2" data-bs-toggle="modal" data-bs-dismiss="modal">Get and Update Data</button>
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
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="button" class="btn btn-primary">Save</button>
        </div>
      </div>
    </div>
  </div>

</div>

<div class="modal" tabindex="-1" id="myModal">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"></h5>
      </div>
      <div class="modal-body">
      <div class="form-group">
        <label id="edit_able_value_label" for="edit_able_value">Adjust ยอดยกมา</label>
        <input type="text" class="form-control text-right" id="edit_able_value">
        <input type="hidden" class="form-control text-right" id="edit_able_type">
        <input type="hidden" class="form-control text-right" id="edit_able_report_id">
      </div>
      <div class="form-group">
        <label for="edit_able_note">Note</label>
        <textarea class="form-control" id="edit_able_note" rows="3"></textarea>
      </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" id="edit_able_submit">Save changes</button>
      </div>
    </div>
  </div>
</div>

<?php require_once APPROOT . "/views/layout/script.php"; ?>

<script src="<?php echo BASE_URL; ?>/public/js/report/reconcile.js"></script>