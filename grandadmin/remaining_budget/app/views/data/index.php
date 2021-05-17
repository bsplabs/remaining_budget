<br>
<div class="container-fluid">
  <div class="row">
    <h3>Raw Data Management</h3>
    <div class="col-md-6 bordered">
      <div class="card">
        <div class="card-body">
          <h5 class="card-title">Raw Data Import</h5>
          <p class="card-text">Please choose file type for import</p>
          <form action="/data/import-file" method="POST" enctype="multipart/form-data">
            <div class="form-group mt-2 mb-3">
              <div class="form-check">
                <input class="form-check-input" type="radio" name="importType" id="exampleRadios1" value="gl_revenue" checked>
                <label class="form-check-label" for="exampleRadios1">
                  GL Revenue (Receive)
                </label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="radio" name="importType" id="exampleRadios2" value="gl_cost">
                <label class="form-check-label" for="exampleRadios2">
                  GL Cost (Spending)
                </label>
              </div>
              <div class="form-check disabled">
                <input class="form-check-input" type="radio" name="importType" id="exampleRadios3" value="gl_cash_advance">
                <label class="form-check-label" for="exampleRadios3">
                  GL Cash Advance (Remaining)
                </label>
              </div>
              <div class="form-check disabled">
                <input class="form-check-input" type="radio" name="importType" id="exampleRadios3" value="media_wallet">
                <label class="form-check-label" for="exampleRadios3">
                  Media Wallet
                </label>
              </div>
            </div>

            <div class="form-group mb-3">
              <input class="form-control" type="file" name="file" id="file" accept=".xls,.xlsx">
            </div>
            
            <div class="d-flex justify-content-end">
              <button type="submit" class="btn btn-primary" name="importRawData">Import</button>
            </div>
          </form>
          <!-- <a href="#" class="btn btn-primary">อัพโหลดข้อมูล</a> -->
        </div>
      </div>
    </div>

    <div class="col-md-6 bordered">
      <div class="card">
        <div class="card-body">
          <h5 class="card-title">Data Classification</h5>
          <p class="card-text">With supporting text below as a natural lead-in to additional content.</p>
          <a href="#" class="btn btn-primary">Go somewhere</a>
        </div>
      </div>
    </div>
  </div>
  
  <div class="row mt-5">
    <h3>Data Classification</h3>
    <div class="col-md-6 bordered">
      <div class="card">
        <div class="card-body">
          <h5 class="card-title">Reconcile Remainging Budget</h5>
          <p class="card-text">Please choose file type for import</p>
          <!-- FORM -->
          <form action="/data/import-file" method="POST" enctype="multipart/form-data">
            <div class="form-group mt-2 mb-3">
              <div class="form-check">
                <input class="form-check-input" type="radio" name="importType" id="exampleRadios1" value="gl_revenue" checked>
                <label class="form-check-label" for="exampleRadios1">
                  GL Revenue (Receive)
                </label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="radio" name="importType" id="exampleRadios2" value="gl_cost">
                <label class="form-check-label" for="exampleRadios2">
                  GL Cost (Spending)
                </label>
              </div>
              <div class="form-check disabled">
                <input class="form-check-input" type="radio" name="importType" id="exampleRadios3" value="gl_cash_advance">
                <label class="form-check-label" for="exampleRadios3">
                  GL Cash Advance (Remaining)
                </label>
              </div>
              <div class="form-check disabled">
                <input class="form-check-input" type="radio" name="importType" id="exampleRadios3" value="media_wallet">
                <label class="form-check-label" for="exampleRadios3">
                  Media Wallet
                </label>
              </div>
            </div>

            <div class="form-group mb-3">
              <input class="form-control" type="file" name="file" id="file" accept=".xls,.xlsx">
            </div>
            
            <div class="d-flex justify-content-end">
              <button type="submit" class="btn btn-primary" name="importRawData">Import</button>
            </div>
          </form>
          <!-- FORM -->
        </div>
      </div>
    </div>

    <div class="col-md-6 bordered">
      <div class="card">
        <div class="card-body">
          <h5 class="card-title">Export Remaining Budget</h5>
          <p class="card-text">With supporting text below as a natural lead-in to additional content.</p>
          <a href="/data/export-remainig-budget" class="btn btn-warning">Export</a>
        </div>
      </div>
    </div>
  </div>
</div>