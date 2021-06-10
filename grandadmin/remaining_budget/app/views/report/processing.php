<link href="<?php echo BASE_URL; ?>/public/css/report.css" rel="stylesheet">
<link href="<?php echo BASE_URL; ?>/public/css/font-awesome.min.css" rel="stylesheet">
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
  <div class="fa-3x text-center" style="margin-top:20px;">
    <i class="fa fa-cog fa-spin"></i><br>
    <label>Report of this month is in Recalculate Process</label><br>
    <small>Please come back later.</small>
  </div>
  <div class="progress" style="margin-top: 50px;">
    <div id="report-progress-bar" class="progress-bar bg-info" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
  </div>
</div>

<?php require_once APPROOT . "/views/layout/script.php"; ?>

<script src="<?php echo BASE_URL; ?>/public/js/report/index.js"></script>
<script src="<?php echo BASE_URL; ?>/public/js/report/progress.js"></script>