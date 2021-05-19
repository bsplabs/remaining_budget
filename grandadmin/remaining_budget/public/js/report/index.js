// 
jQuery.fn.visible = function() {
  return this.css('visibility', 'visible');
};

jQuery.fn.invisible = function() {
  return this.css('visibility', 'hidden');
};

jQuery.fn.visibilityToggle = function() {
  return this.css('visibility', function(i, visibility) {
      return (visibility == 'visible') ? 'hidden' : 'visible';
  });
};

$(document).ready(function() {
  
  loadingUIForFirstGetStatusResources('open');
  init();
  
  $('.adjustment-backend').click(function() {
    console.log($(this).parent().attr("data-id"));
    var dataId = $(this).parent().attr("data-id");
    console.log($(this).parent().attr("data-name"));
    var dataName = $(this).parent().attr("data-name");

    $(this).css('background-color', '#ccc');

    $('#edit-value-input').val($(this)[0].innerText);
    $('#show_id')[0].textContent = dataId;
    $('#show_name')[0].textContent = dataName;

    var myModal = new bootstrap.Modal(document.getElementById('modalEditCell'))
    myModal.show()
  });

  $('#file-re-upload-selector').change(function() {
    console.log($(this).val());
    if ($(this).val() === 'apis') {
      $('#input-file-upload').hide();
      $('#checkbox-api-group').show();
      $('#re-upload-file-button').hide();
      $('#get-and-update-button').show();
    } else {
      $('#input-file-upload').show();
      $('#checkbox-api-group').hide();
      $('#re-upload-file-button').show();
      $('#get-and-update-button').hide();
    }
  });

  $('.table-row-toggle').click(function() {
    console.log($(this).attr('data-bs-target'));
    var elementId = $(this).attr('data-bs-target');
    console.log($(elementId));
    $(elementId).each(function() {
      console.log($(this));
      // $(this).addClass('active');
    });
  })

  // ---------> GL Cash Advance
  $('#gl-cash-advance-form').submit(function(event) {
    event.preventDefault();
    var formData = new FormData($(this)[0]);
    if ($('#cash-advance-input-file').val() == '') {
      Swal.fire({
        icon: 'error',
        title: 'Oops...',
        text: "GL Cash advance file was not found"
      })
      return 0;
    }

    // show loading ui for upload file form
    $('.gl-cash-advance button').prop('disabled', true);
    $('.gl-cash-advance .text-button').text('Loading...');
    $('.gl-cash-advance .spinner-button').toggleClass('hide');

    importGLCashAdvance(formData, false);
  });

  $('#google-spending-form').submit(function(event) {
    event.preventDefault();
    var formData = new FormData($(this)[0]);
    if ($('#google-spending-input-file').val() == '') {
      Swal.fire({
        icon: 'error',
        title: 'Oops...',
        text: "Google spending file was not found"
      })
      return 0;
    }

    // show loading ui for upload file form
    $('.google-spending button').prop('disabled', true);
    $('.google-spending .text-button').text('Loading...');
    $('.google-spending .spinner-button').toggleClass('hide');

    importGoogleSpending(formData, false);

  });

  $('#facebook-spending-form').submit(function(event) {
    event.preventDefault();
    var formData = new FormData($(this)[0]);
    if ($('#facebook-spending-input-file').val() == '') {
      Swal.fire({
        icon: 'error',
        title: 'Oops...',
        text: "Facebook spending file was not found"
      })
      return 0;
    }

    // show loading ui for upload file form
    $('.facebook-spending button').prop('disabled', true);
    $('.facebook-spending .text-button').text('Loading...');
    $('.facebook-spending .spinner-button').toggleClass('hide');

    // use ajax
    importFacebookSpending(formData, false);

  });

  $('#re-import-ignore-invalid-btn').click(function() {
    $('#modal-error-reporting').modal('toggle');
    var resource_type = $('#re-import-resource-type').val();
    if (resource_type === 'gl_cash_advance') {
      // show loading ui for upload file form
      $('.gl-cash-advance button').prop('disabled', true);
      $('.gl-cash-advance .text-button').text('Loading...');
      $('.gl-cash-advance .spinner-button').toggleClass('hide');
      var formData = new FormData($('#gl-cash-advance-form')[0]);
      importGLCashAdvance(formData, true);
    } else if (resource_type === 'facebook_spending') {
      // show loading ui for upload file form
      $('.facebook-spending button').prop('disabled', true);
      $('.facebook-spending .text-button').text('Loading...');
      $('.facebook-spending .spinner-button').toggleClass('hide');
      var formData = new FormData($('#facebook-spending-form')[0]);
      importFacebookSpending(formData, true);
    } else {
      Swal.fire({
        icon: 'error',
        title: 'Oops...',
        text: "Not found resource type"
      })
      return 0;
    }
  });

  $('#upload_transfer_file_form').submit(function(event) {
    event.preventDefault();
    console.log($(this))
    var formData = new FormData($(this)[0]);
    console.log('Upload wallet transfer');
    console.log(formData);

    $.ajax({
      url: base_url + '/resources/import_wallet_transfer',
      type: 'POST',
      data: formData,
      processData: false,
      contentType: false,
      success:function(data) {
        console.log(data);
      }
    });
  });


  //  Month and year drop-down change
  $('#month-year-selector').change(function() {
    var month_year_exp = $(this).val().split('_');
    var month = month_year_exp[0];
    var year = month_year_exp[1];
    window.location.replace(base_url + "/reports/" + year + "/" + month);
  });

  // Re-import file button
  $('.re-upload-file').click(function() {
    var parent = $(this).parent().parent().parent();
    var parent_class = parent[0].className.split(" ");
    resourceImportBoxChangeState('.' + parent_class[parent_class.length - 1], 'pending')
  });
  
  $("#generateButton").click(function(){
    generateReport();
  });

});

function importGLCashAdvance(formData, ignore_invalid_data) {
  if (ignore_invalid_data) {
    formData.append("ignore_invalid_data", true);
  }

  formData.append("month", month);
  formData.append("year", year);

  $.ajax({
    url: base_url + '/reports/upload-cash-advance',
    type: 'POST',
    data: formData,
    processData: false,
    contentType: false,
    success: function(res) {
      console.log(res);
      $('.gl-cash-advance button').prop('disabled', false);
      $('.gl-cash-advance .text-button').text('Import');
      $('.gl-cash-advance .spinner-button').toggleClass('hide');

      if (res.status === 'error' || res.status === 'fail') {
        if (res.type === 'alert') {
          Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: res.message
          })
        } else {
          // initailize modal
          $('#modal-resource-name').text("GL Cash Advance");
          $('#re-import-resource-type').val("gl_cash_advance");
          
          // assign result value
          $('#total').text(res.data.total);
          $('#valid-total').text(res.data.valid_total);
          $('#invalid-total').text(res.data.invalid_total);

          // show recommanded message
          $('.error-type-lists').text('[' + res.data.error_type_lists.join(", ") + ']');
          // $('#message-suggestion .message-suggestion__gl-cash-advance').removeClass('hide');
          $('#message-suggestion .message-suggestion__facebook-spending').removeClass('hide').addClass('hide');

          // display modal
          $('#modal-error-reporting').modal('toggle');
        }
      } else {
        if (res.type === 'alert') {
          Swal.fire({
            icon: 'success',
            title: 'Successfully import',
            text: ''
          })
        } else {

        }
        resourceImportBoxChangeState('.gl-cash-advance', 'waiting', res.data.import_total + " rows", res.data.updated_at);
        buttonGenerateChecking(res.overall_status, res.allowed_generate_data);
      }
    }
  });
}

function importGoogleSpending(formData, ignore_invalid_data) {
  if (ignore_invalid_data) {
    formData.append("ignore_invalid_data", true);
  }

  formData.append("month", month);
  formData.append("year", year);

  $.ajax({
    url: base_url + '/resources/import-google-spending',
    type: 'POST',
    data: formData,
    processData: false,
    contentType: false,
    success:function(res) {
      // 
      $('.google-spending button').prop('disabled', false);
      $('.google-spending .text-button').text('Import');
      $('.google-spending .spinner-button').toggleClass('hide');

      if (res.status === 'error' || res.status === 'fail') {
        if (res.type == 'alert') {
          // display alert
          Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: res.message
          });
        } else {
          // display modal
        }
      } else {
        if (res.type === 'alert') {
          // display alert
          Swal.fire({
            icon: 'success',
            title: 'Successfully import',
            text: res.message
          });
        } else {
          // display modal
        }

        resourceImportBoxChangeState('.google-spending', 'waiting', res.data.import_total + " rows", res.data.updated_at);
        buttonGenerateChecking(res.overall_status, res.allowed_generate_data);
      }
    }
  });
  
}

function importFacebookSpending(formData, ignore_invalid_data) {
  if (ignore_invalid_data) {
    formData.append("ignore_invalid_data", true);
  }

  formData.append("month", month);
  formData.append("year", year);

  $.ajax({
    url: base_url + '/resources/import-facebook-spending',
    type: 'POST',
    data: formData,
    processData: false,
    contentType: false,
    success:function(res) {
      $('.facebook-spending button').prop('disabled', false);
      $('.facebook-spending .text-button').text('Import');
      $('.facebook-spending .spinner-button').toggleClass('hide');

      if (res.status === 'error' || res.status === 'fail') {
        if (res.type === 'alert') {
          Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: res.message
          });
        } else if (res.type === 'modal') {
          // initailize modal
          $('#modal-resource-name').text("Facebook spending");
          $('#re-import-resource-type').val("facebook_spending");
          // assign result value
          $('#total').text(res.data.total);
          $('#valid-total').text(res.data.valid_total);
          $('#invalid-total').text(res.data.invalid_total);
          // show recommanded message
          $('.error-type-lists').text('[Billing Period]');
          // $('#message-suggestion .message-suggestion__facebook-spending').removeClass('hide');
          $('#message-suggestion .message-suggestion__gl-cash-advane').removeClass('hide').addClass('hide');
          // display modal
          $('#modal-error-reporting').modal('toggle');
        } else {
          alert('Something went wrong!');
        }
      } else {
        if (res.type === 'alert') {
          Swal.fire({
            icon: 'success',
            title: 'Successfully import',
            text: ''
          })
          resourceImportBoxChangeState('.facebook-spending', 'waiting', res.data.import_total + " rows", res.data.updated_at);
          buttonGenerateChecking(res.overall_status, res.allowed_generate_data);
        } else {

        }
      }
    }
  });
}



function loadingUIForFirstGetStatusResources(cmd) {
  if (cmd === 'open') {
    $('.resources-box').invisible();
    $('.resources').css('background', '#2a2a2a55');
    $("div.spanner").addClass("show");
    $("div.overlay").addClass("show");
  } else {  
    $('.resources-box').visible();
    $('.resources').css('background', 'unset');
    $("div.spanner").removeClass("show");
    $("div.overlay").removeClass("show");
  }
}

function init() {
  var statusResources = getStatusResources();
}

function resourceBoxWaitingStatus(resource_type, row_count)
{
  console.log(resource_type)
  // update subtitle
  
  // update icon
  $(resource_type + ' .resource-box__icon .processing').css('display', 'none');
  $(resource_type + ' .resource-box__icon .ready').css('display', 'inline-block');
  // change resource box detail
  $(resource_type + ' .resource-box__detail .status-message').removeClass('badge-warning').addClass('badge-success');
  $(resource_type + ' .resource-box__detail .status-message').text("Ready");
  $(resource_type + ' .resource-box__detail .data-count').text(row_count);
}

function resourceImportBoxChangeState(resource_type, state, row_count, last_uploaded) {
  if (state === "waiting") {
    $(resource_type + ' .re-upload-file').css('display', 'inline-block');
    $(resource_type + ' .import-file-form').removeClass('hide').addClass('hide');
    $(resource_type + ' .import-file-result').removeClass('hide');
    $(resource_type + ' .import-file-result .imported-at').text('Last import: ' + last_uploaded);
    $(resource_type + ' .resource-box__detail .row-count').text(row_count);
  } else {
    // console.log($(resource_type + ' .import-file-form'))
    $(resource_type + ' .re-upload-file').css('display', 'none');
    $(resource_type + ' .import-file-form').removeClass('hide');
    $(resource_type + ' .import-file-result').removeClass('hide').addClass('hide');
    $(resource_type + ' .import-file-result .imported-at').text('');
    $(resource_type + ' .resource-box__detail .row-count').text('');
  }
}

function getStatusResources() {
  $.ajax({
    url: base_url + "/resources/get-status-resources/" + year + "/" + month,
    type: 'GET',
    success: function(res) {
      console.log(res);
      if (res.status === "fail" || res.status === "error") {
        Swal.fire({
          icon: 'error',
          title: 'Oops...',
          text: res.message
        })
      } else {

        var resource_lists = res.data;

        // automate get data
        if (resource_lists.media_wallet.status === 'waiting') {
          resourceBoxWaitingStatus('.media-wallet', resource_lists.media_wallet.total + ' rows');
        }
        if (resource_lists.withholding_tax.status === 'waiting') {
          resourceBoxWaitingStatus('.withholding-tax', resource_lists.withholding_tax.total + ' rows');
        }
        if (resource_lists.free_click_cost.status === 'waiting') {
          resourceBoxWaitingStatus('.free-click-cost', resource_lists.free_click_cost.total + ' rows');
        }
        if (resource_lists.remaining_ice.status === 'waiting') {
          resourceBoxWaitingStatus('.ice', resource_lists.remaining_ice.total + ' rows');
        }

        // import file
        if (resource_lists.gl_cash_advance.status === 'waiting') {
          resourceImportBoxChangeState(
            '.gl-cash-advance', 
            'waiting', 
            resource_lists.gl_cash_advance.total + ' rows',
            resource_lists.gl_cash_advance.updated_at ? resource_lists.gl_cash_advance.updated_at : ''
          );
        }
        if (resource_lists.google_spending.status === 'waiting') {
          resourceImportBoxChangeState(
            '.google-spending', 
            'waiting', 
            resource_lists.google_spending.total + ' rows',
            resource_lists.google_spending.updated_at ? resource_lists.google_spending.updated_at : ''
          );
        }
        if (resource_lists.facebook_spending.status === 'waiting') {
          resourceImportBoxChangeState(
            '.facebook-spending', 
            'waiting', 
            resource_lists.facebook_spending.total + ' rows',
            resource_lists.facebook_spending.updated_at ? resource_lists.facebook_spending.updated_at : '' 
          );
        }
        if (resource_lists.wallet_transfer === 'waiting') {
          resourceImportBoxChangeState(
            '.transfer', 
            'waiting',
            resource_lists.wallet_transfer.total + ' rows',
            resource_lists.wallet_transfer.updated_at ? resource_lists.wallet_transfer.updated_at : ''
          );
        }

        buttonGenerateChecking(res.overall_status, res.allowed_generate_data);
      }

      setTimeout(function() {
        loadingUIForFirstGetStatusResources('close')
      }, 3000);
      
    }
  });
}

function buttonGenerateChecking($overall_status, $allowed_generate_data)
{
  if ($overall_status === 'waiting' && $allowed_generate_data) {
    $("#generateButton").prop('disabled', false);
  } else {
    $("#generateButton").prop('disabled', true);
  }
}

function generateReport(){
  updated_by = "kittisak";
  $.ajax({
    url: base_url + '/reports/generate-report',
    type: 'POST',
    data: {month: month, year:year, updated_by: updated_by},
    success:function(res) {
      if(res.status == "success"){
        if($("#reconcile_add_replace_modal").hasClass('show')){
          $("#reconcile_add_replace_modal").modal('hide');
        }
        $("#toast_header").text("Queue created.");
        $("#toast_body").text("Please come back later.");
        $("#liveToast").toast('show');
        location.reload();
      }else{
        $("#toast_header").text("Something wrong!!!");
        $("#toast_body").text("Please try again.");
        $("#liveToast").toast('show');
      }
    }
  });
}