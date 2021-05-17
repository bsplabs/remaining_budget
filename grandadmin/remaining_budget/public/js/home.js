let gl_revenue_uploaded = false;
let transfer_uploaded = true;
let resource1 = false;
let resource2 = true;
let resource3 = true;
let resource4 = true;
let reportUrl = '/reports/generate'

$(document).ready(function() {
  checkGenerateButton();

  $('#testPlayRc1').click(function() {
    $('.box-resource .processing').hide();
    $('.box-resource .ready').show();
    $('.resource1 .status-message')[0].innerHTML = "Ready";
    resource1 = true;
    checkGenerateButton();
  });

  $("#re_upload_gl_revenue_file").click(function () {
    $("#upload_gl_revenue_file_ready").css('display', 'none');
    $("#upload_gl_revenue_file_status").css('display', 'none');
    $("#re_upload_gl_revenue_file").hide();
    $("#upload_gl_revenue_form").show();
    $(".upload-gl-revenue-file .card-subtitle").show();
    gl_revenue_uploaded = false;
    checkGenerateButton();
  });

  $("#upload_gl_revenue_form").submit(function (event) {
    event.preventDefault();
    setStatusForUploadTransferFile();
    gl_revenue_uploaded = true;
    checkGenerateButton();
  });

  $("#edit-transfer-file").click(function () {
    $("#upload-transfer-file-ready").hide();
    $("#upload-transfer-file-status").hide();
    $("#edit-transfer-file").hide();
    $("#upload_transfer_file_form").show();
    $(".upload-transfer-file .card-subtitle").show();
    transfer_uploaded = false;
    checkGenerateButton();
  });

  $("#upload_transfer_file_form").submit(function (event) {
    event.preventDefault();
    setStatusForUploadGLFile();
    transfer_uploaded = true;
    checkGenerateButton();
  });

  // hit generate button
  $('#generateButton').click(function() {
    window.location.href = reportUrl
  })

  function setStatusForUploadGLFile() {
    $("#upload-transfer-file-ready").show();
    $("#upload-transfer-file-status").show();
    $("#upload_transfer_file_form").hide();
    $(".upload-transfer-file .card-subtitle").hide();
  }

  function setStatusForUploadTransferFile() {
    $("#upload_gl_revenue_file_ready").css('display', 'flex');
    $("#upload_gl_revenue_file_status").css('display', 'flex');
    $("#re_upload_gl_revenue_file").show();
    $("#upload_gl_revenue_form").hide();
    $(".upload-gl-revenue-file .card-subtitle").hide();
  }

  function checkGenerateButton() {
    if (gl_revenue_uploaded && transfer_uploaded && resource1 && resource2 && resource3 && resource4) {
      $('#generateButton').prop('disabled', false);
    } else {
      $('#generateButton').prop('disabled', true);
    }
  }

});
