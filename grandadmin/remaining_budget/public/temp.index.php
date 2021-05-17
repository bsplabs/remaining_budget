<?php
include __DIR__ . "/upload_excel.php";
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Remaing Budget</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-BmbxuPwQa2lc/FVzBcNJ7UAyJxM6wuqIj61tLrc4wSX0szH/Ev+nYRRuWlolflfl" crossorigin="anonymous">
  <style>
    .navbar {
      background-color: #484848;
    }

    .text-white {
      color: #fff !important;
    }
  </style>
</head>

<body>
  <!-- Nav -->
  <nav class="navbar navbar-expand-lg">
    <div class="container">
      <a class="navbar-brand text-white" href="#"><i class="fa fa-graduation-cap fa-lg mr-2"></i>Remaing Budget</a>
      <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#nvbCollapse" aria-controls="nvbCollapse">
        <span class="navbar-toggler-icon"></span>
      </button>
    </div>
  </nav>

  <!-- Section -->
  <section>
    <div class="container mt-3">
      <div class="row">
        <div class="col-6">
          <form action="" method="post" name="frmExcelImport" id="frmExcelImport" enctype="multipart/form-data">
            <!--  -->
            <div class="mb-3">
              <label for="formFile" class="form-label">อัพโหลดไฟล์ GL_Revenue (Receive) </label>
              <input class="form-control" type="file" name="file" id="file" accept=".xls,.xlsx">
            </div>
            <!--  -->
            <div class="col-auto text-left">
              <button type="submit" id="submit" name="import" class="btn btn-primary mb-3">อัพโหลดข้อมูล</button>
            </div>
            <!--  -->
          </form>
        </div>
      </div>
    </div>
  </section>

  <section>
    <div class="container bt-3">

    </div>
  </section>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/js/bootstrap.bundle.min.js" integrity="sha384-b5kHyXgcpbZJO/tY9Ul7kGkf1S0CWuKcCD38l8YkeH8z8QjE0GmW1gYU5S9FOnJ0" crossorigin="anonymous"></script>
</body>

</html>