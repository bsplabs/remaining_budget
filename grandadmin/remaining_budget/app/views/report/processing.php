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
  <div class="fa-3x text-center" style="margin-top:20px;">
    <i class="fa fa-cog fa-spin"></i><br>
    <label>Report of this month is in Recalculate Process</label><br>
    <small>Please come back later.</small>
  </div>
  <div class="progress">
    <div class="progress-bar" role="progressbar" style="width: 25%;" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100">25%</div>
  </div>
</div>

<?php require_once APPROOT . "/views/layout/script.php"; ?>

<script src="<?php echo BASE_URL; ?>/public/js/report/index.js"></script>