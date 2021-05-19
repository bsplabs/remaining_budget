<link href="<?php echo BASE_URL; ?>/public/css/report.css" rel="stylesheet">

<?php require_once APPROOT . "/views/layout/head.php"; ?>

<script>
  var base_url = "<?php echo BASE_URL; ?>";
  const month = "<?php echo $data["month"]; ?>";
  const year = "<?php echo $data["year"]; ?>";
</script>

<br>
<!-- MONTH/YEAR SELECTION -->
<div class="row justify-content-end">
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

<div class="mt-3">
  <!-- <h1 class="text-danger">Broken Data</h1> -->
  <div class="jumbotron" style="position: relative;">
    <h1 class="display-4 text-danger">Data are broken or not already</h1>
    <p class="lead">Remaining budget data at this month (<?php echo $data["month"] . "/" . $data["year"]; ?>) is missing or not already.</p>
    <hr class="my-4">
    <p>Please contact admin or IT department</p>
    <p class="lead">
      <a class="btn btn-primary btn-lg" href="#" role="button">Contact</a>
    </p>
    <div class="broken-data-icon">
      <i class='bx bx-error-circle' ></i>
    </div>
  </div>
</div>

<?php require_once APPROOT . "/views/layout/script.php"; ?>

<script src="<?php echo BASE_URL; ?>/public/js/report/index.js"></script>