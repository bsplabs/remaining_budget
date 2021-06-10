var datatable;
var edit_note_row_selected = '';


function number_finacial_format(value){
  return numeral(value).format('(0,0.00)');
}

function string_to_number(value){
  if(value != ""){
    return numeral(value).format('0.00');
  }else{
    return value;
  }
}

function renderDataTables(){
  
  datatable = $('#report_reconcile_table').DataTable({
    // "scrollY": "calc(100vh - 330px)",
    "scrollX": true,
    "lengthChange": false,
    "searching": true,
    "pageLength": 50,
    "processing": true,
    "serverSide": true,
    ajax: {
      url: base_url + '/reports/get-report-data-table',
      type: 'POST',
      data: function (d) {
        return $.extend({}, d, {
          year:year,
          month:month,
          filter_cash_advance: string_to_number($('#filter_cash_advance').val()),
          filter_remaining_budget: string_to_number($('#filter_remaining_budget').val()),
          filter_difference: string_to_number($('#filter_difference').val()),
          filter_cash_advance_condition: $('#filter_cash_advance_condition').val(),
          filter_remaining_budget_condition: $('#filter_remaining_budget_condition').val(),
          filter_difference_condition: $('#filter_difference_condition').val()
        });
        
      }
    },
    createdRow: function( nRow, aData, iDisplayIndex) {
      if(aData.amount > 1){
        $(nRow)
          .attr('data-parent_id',aData.parent_id)
          .addClass('parent-tr')
          .find('td').eq(0)
          .append("<i class='bx bx-chevron-down-circle pull-right more-children-data'></i>")
        $(nRow).find('td').eq(1).text('')
        $(nRow).find('td').eq(2).text('')
        $(nRow).find('td').eq(3).text('')
        $(nRow).find('td').eq(4).text('')
        $(nRow).find('td').eq(5).text('')
        $(nRow).find('td').eq(6).text('')
      }else{
        if(aData.amount == 0){
          $(nRow).addClass('children-tr hide children_'+aData.parent_id)
        }
        $( nRow)
        .attr('data-customer_name', aData.grandadmin_customer_name)
        .attr('data-report_id', aData.report_id)
        .attr('data-row-index', iDisplayIndex)
        .find('td').eq(8)
          .attr('data-column_name','Adjust ยอดยกมา')
          .attr('data-column_type', 'adjustment_remain')
          .attr('data-value', aData.adjustment_remain)
          .attr('data-note', "")
          .addClass('edit-able')
        $( nRow)
          .find('td').eq(14)
            .attr('data-column_name','JE + Free Clickcost')
            .attr('data-column_type', 'adjustment_free_click_cost')
            .attr('data-value', aData.adjustment_free_click_cost)
            .attr('data-note', "")
            .addClass('edit-able')
        $( nRow)
          .find('td').eq(15)
            .attr('data-column_name','Free Clickcost (ค่าใช้จ่ายต้องห้าม)')
            .attr('data-column_type', 'adjustment_free_click_cost_old')
            .attr('data-value', aData.adjustment_free_click_cost_old)
            .attr('data-note', "")
            .addClass('edit-able')
        $( nRow)
          .find('td').eq(16)
            .attr('data-column_name','Adjustment')
            .attr('data-column_type', 'adjustment_cash_advance')
            .attr('data-value', aData.adjustment_cash_advance)
            .attr('data-note', "")
            .addClass('edit-able')
        $( nRow)
          .find('td').eq(17)
            .attr('data-column_name','Max')
            .attr('data-column_type', 'adjustment_max')
            .attr('data-value', aData.adjustment_max)
            .attr('data-note', "")
            .addClass('edit-able')
        
        $( nRow)
          .find('td').eq(23)
            .attr('data-column_name','Adjust')
            .attr('data-column_type', 'adjustment_front_end')
            .attr('data-value', aData.adjustment_front_end)
            .attr('data-note', "")
            .addClass('edit-able')
        
        $( nRow)
        .find('td').eq(26)
          .attr('data-reconcile-id',aData.report_id)
          .addClass('reconcile-note')
          // .css('width','300px')

        if(aData.adjustment_remain_note != "" && aData.adjustment_remain_note != null){
          $( nRow)
          .find('td').eq(8)
            .attr('title',aData.adjustment_remain_note)
            .attr('data-note', aData.adjustment_remain_note)
            .append('<div class="more-note-icon"></div>')
        }
        if(aData.adjustment_free_click_cost_note != "" && aData.adjustment_free_click_cost_note != null){
          $( nRow)
          .find('td').eq(14)
            .attr('title',aData.adjustment_free_click_cost_note)
            .attr('data-note', aData.adjustment_free_click_cost_note)
            .append('<div class="more-note-icon"></div>')
        }
  
        if(aData.adjustment_free_click_cost_old_note != "" && aData.adjustment_free_click_cost_old_note != null){
          $( nRow)
          .find('td').eq(15)
            .attr('title',aData.adjustment_free_click_cost_old_note)
            .attr('data-note', aData.adjustment_free_click_cost_old_note)
            .append('<div class="more-note-icon"></div>')
        }
  
        if(aData.adjustment_cash_advance_note != "" && aData.adjustment_cash_advance_note != null){
          $( nRow)
          .find('td').eq(16)
            .attr('title',aData.adjustment_cash_advance_note)
            .attr('data-note', aData.adjustment_cash_advance_note)
            .append('<div class="more-note-icon"></div>')
        }
  
        if(aData.adjustment_max_note != "" && aData.adjustment_max_note != null){
          $( nRow)
          .find('td').eq(17)
            .attr('title',aData.adjustment_max_note)
            .attr('data-note', aData.adjustment_max_note)
            .append('<div class="more-note-icon"></div>')
        }
  
        if(aData.adjustment_front_end_note != "" && aData.adjustment_front_end_note != null){
          $( nRow)
          .find('td').eq(23)
            .attr('title',aData.adjustment_front_end_note)
            .attr('data-note', aData.adjustment_front_end_note)
            .append('<div class="more-note-icon"></div>')
        }
      }

      $('#reconcile_table_wrapper').show();
      loadingHandler(false, '');
          
    },
    "columnDefs": [
      {
      "render": function (data, type,row){
          return number_finacial_format(data)
        },
        "targets": [7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25]
      }
    ],
    "columns": [
      {"data": "parent_id"},
      {"data": "grandadmin_customer_id"},
      {"data": "grandadmin_customer_name"},
      {"data": "offset_acct"},
      {"data": "offset_acct_name"},
      {"data": "company"},
      {"data": "payment_method"},
      {"data": "last_month_remaining","className": "dt-body-right"},
      {"data": "adjustment_remain","className": "dt-body-right"},
      {"data": "receive","className": "dt-body-right"},
      {"data": "invoice","className": "dt-body-right"},
      {"data": "transfer","className": "dt-body-right"},
      {"data": "ads_credit_note","className": "dt-body-right"},
      {"data": "spending_invoice","className": "dt-body-right"},
      {"data": "adjustment_free_click_cost","className": "dt-body-right"},
      {"data": "adjustment_free_click_cost_old","className": "dt-body-right"},
      {"data": "adjustment_cash_advance","className": "dt-body-right"},
      {"data": "adjustment_max","className": "dt-body-right"},
      {"data": "cash_advance","className": "dt-body-right"},
      {"data": "remaining_ice","className": "dt-body-right"},
      {"data": "wallet","className": "dt-body-right"},
      {"data": "wallet_free_click_cost","className": "dt-body-right"},
      {"data": "withholding_tax","className": "dt-body-right"},
      {"data": "adjustment_front_end","className": "dt-body-right"},
      {"data": "remaining_budget","className": "dt-body-right"},
      {"data": "difference","className": "dt-body-right"},
      {"data": "note","width": "300"}
    ]
  }); 
  
}

function closePeriod(){
  var created_by = "kittisak"
  $.ajax({
    url: base_url + '/reports/close-period',
    type: 'POST',
    data: {year:year,month:month,created_by: created_by},
    success:function(res) {
      if(res.status == "success"){
        $("#toast_header").text("Closed");
        $("#toast_body").text("Reconcile Data is closed");
        $("#liveToast").toast('show');
        location.reload(base_url+'/reports/'+res.year+'/'+res.month);
      }else{
        $("#modalClosePeriod").modal('hide');
        $("#toast_header").text("Something wrong!!!");
        $("#toast_body").text("Please try again.");
        $("#liveToast").toast('show');
      }
    }
  });
}

function updateGoogleSpending(ignore_invalid_data) {
  var formData = new FormData();
  formData.append("month", month);
  formData.append("year", year);
  formData.append("ignore_invalid_data", ignore_invalid_data);
  formData.append("googleSpendingInputFile",$('#reconcile_add_replace_file')[0].files[0])
  formData.append("updated_by", "");

  $('#reconcile_add_replace_modal').modal('hide');
  $('#modal-error-reporting').modal('hide');
  loadingHandler(true, 'Adding or replacing google spending data. please wait...');

  $.ajax({
    url: base_url + '/resources/update-google-spending',
    type: 'POST',
    data: formData,
    processData: false,
    contentType: false,
    success:function(res) {
      loadingHandler(false, '');
      if(res.status == "success"){
        location.reload();
      }else{
        if(res.type == 'alert'){
          Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: res.message
          })
          return 0;
        }else if (res.type == 'modal'){
          $("#modal-resource-name").html("GL Cash Advance");
          $("#modal-error-reporting #total").html(res.data.total);
          $("#modal-error-reporting #valid-total").html(res.data.valid_total);
          $("#modal-error-reporting #invalid-total").html(res.data.invalid_total);
          $(".error-type-lists").html("["+res.data.error_type_lists.join(",")+"]");
          $("#modal-error-reporting").modal('show');
        }
      }
    }
  });
  
}

function updateFacebookSpending(ignore_invalid_data) {
  var formData = new FormData();
  formData.append("month", month);
  formData.append("year", year);
  formData.append("ignore_invalid_data", ignore_invalid_data);
  formData.append("facebookSpendingInputFile",$('#reconcile_add_replace_file')[0].files[0])
  formData.append("updated_by", "");

  $('#reconcile_add_replace_modal').modal('hide');
  $('#modal-error-reporting').modal('hide');
  loadingHandler(true, 'Adding or replacing facebook spending data. please wait...');

  $.ajax({
    url: base_url + '/resources/update-facebook-spending',
    type: 'POST',
    data: formData,
    processData: false,
    contentType: false,
    success:function(res) {
      loadingHandler(false, '');
      if(res.status == "success"){
        location.reload();
      }else{
        if(res.type == 'alert'){
          Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: res.message
          })
          return 0;
        }else if (res.type == 'modal'){
          $("#modal-resource-name").html("GL Cash Advance");
          $("#modal-error-reporting #total").html(res.data.total);
          $("#modal-error-reporting #valid-total").html(res.data.valid_total);
          $("#modal-error-reporting #invalid-total").html(res.data.invalid_total);
          $(".error-type-lists").html("["+res.data.error_type_lists.join(",")+"]");
          $("#modal-error-reporting").modal('show');
        }
      }
    }
  });
  
}

function updateWalletTransfer(ignore_invalid_data){
  var formData = new FormData();

  formData.append("month", month);
  formData.append("year", year);
  formData.append("ignore_invalid_data", ignore_invalid_data);
  formData.append("transferInputFile",$('#reconcile_add_replace_file')[0].files[0])
  formData.append("updated_by", "");

  $('#reconcile_add_replace_modal').modal('hide');
  $('#modal-error-reporting').modal('hide');
  loadingHandler(true, 'Adding or replacing wallet transfer data. please wait...');

  $.ajax({
    url: base_url + '/resources/update-wallet-transfer',
    type: 'POST',
    data: formData,
    processData: false,
    contentType: false,
    success:function(res) {
      loadingHandler(false, '');
      if(res.status == "success"){
        location.reload();
      }else{
        if(res.type == 'alert'){
          Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: res.message
          })
          return 0;
        }else if (res.type == 'modal'){
          $("#modal-resource-name").html("GL Cash Advance");
          $("#modal-error-reporting #total").html(res.data.total);
          $("#modal-error-reporting #valid-total").html(res.data.valid_total);
          $("#modal-error-reporting #invalid-total").html(res.data.invalid_total);
          $(".error-type-lists").html("["+res.data.error_type_lists.join(",")+"]");
          $("#modal-error-reporting").modal('show');
        }
      }
    }
  });
}

function updateGlCashAdvance(ignore_invalid_data){
  var formData = new FormData();
  formData.append("month", month);
  formData.append("year", year);
  formData.append("ignore_invalid_data", ignore_invalid_data);
  formData.append("cashAdvanceInputFile",$('#reconcile_add_replace_file')[0].files[0])
  formData.append("updated_by", "");

  $('#reconcile_add_replace_modal').modal('hide');
  $('#modal-error-reporting').modal('hide');
  loadingHandler(true, 'Adding or replacing GL Cash advance data. please wait...');

  $.ajax({
    url: base_url + '/reports/update-cash-advance',
    type: 'POST',
    data: formData,
    processData: false,
    contentType: false,
    success:function(res) {
      loadingHandler(false, '');
      $("#re-upload-file-button").button('reset');
      if(res.status == "success"){
        location.reload();
      }else{
        if(res.type == 'alert'){
          Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: res.message
          })
          return 0;
        }else if (res.type == 'modal'){
          $("#modal-resource-name").html("GL Cash Advance");
          $("#modal-error-reporting #total").html(res.data.total);
          $("#modal-error-reporting #valid-total").html(res.data.valid_total);
          $("#modal-error-reporting #invalid-total").html(res.data.invalid_total);
          $(".error-type-lists").html("["+res.data.error_type_lists.join(",")+"]");
          $("#modal-error-reporting").modal('show');
        }
      }
    }
  });
}

function updateAdjustment(ignore_invalid_data){
  var formData = new FormData();

  formData.append("month", month);
  formData.append("year", year);
  formData.append("ignore_invalid_data", ignore_invalid_data);
  formData.append("AdjustmentInputFile",$('#reconcile_add_replace_file')[0].files[0])
  formData.append("updated_by", "");

  $('#reconcile_add_replace_modal').modal('hide');
  $('#modal-error-reporting').modal('hide');
  loadingHandler(true, 'Adding or replacing adjustment data. please wait...');

  $.ajax({
    url: base_url + '/resources/update-adjustment',
    type: 'POST',
    data: formData,
    processData: false,
    contentType: false,
    success:function(res) {
      loadingHandler(false, '');
      if(res.status == "success"){
        if($("#reconcile_add_replace_modal").hasClass('show')){
          $("#reconcile_add_replace_modal").modal('hide');
        }
        $("#toast_header").text("Queue created.");
        $("#toast_body").text("Please come back later.");
        $("#liveToast").toast('show');
      }else{
        $("#toast_header").text("Something wrong!!!");
        $("#toast_body").text("Please try again.");
        $("#liveToast").toast('show');
      }
    }
  });
}

function loadingHandler(open, message) {
  if (open) {
    $('.reconcile-overlay').removeClass('reconcile-overlay-active').addClass('reconcile-overlay-active');
    $("div.spanner").addClass("show");
    $("div.overlay").addClass("show");
    if (message !== '') {
      $('#message_loading').text(message);
    } 
  } else {
    $('.reconcile-overlay').removeClass('reconcile-overlay-active');
    $("div.spanner").removeClass("show");
    $("div.overlay").removeClass("show");
  }
}

$('#reconcile_table_wrapper').hide();
$( document ).ready(function() {
  loadingHandler(true, 'Initializing reconcile table, please wait...');
  renderDataTables();
  $(document).on('click','.edit-able',function(event){
    if(!is_closed){
      // clear error style
      $('#form-group-input-numeric').removeClass('error');
      $('#form-group-input-numeric .error-message').removeClass('hide').addClass('hide');

      column_name = $(this).data('column_name');
      column_type = $(this).data('column_type');
      report_id = $(this).parent().data('report_id');
      row_id = $(this).parent().data('row_id');
      customer_name = $(this).parent().data('customer_name');
      $("#AdjustModal .modal-title").html("แก้ไข "+column_name);
      $("#edit_able_customer_name").html(customer_name);
      $("#edit_able_value_label").html(column_name);
      $("#edit_able_type").val(column_type);
      $("#edit_able_value").val($(this).data("value"));
      $("#edit_able_note").val($(this).data("note"));
      $("#edit_able_report_id").val(report_id);
      $("#edit_able_row_id").val(row_id);
      
      $("#AdjustModal").modal()
    }
  })

  $(document).on('click','#edit_able_submit',function(event){
    var validNumber = new RegExp(/^\d*\.?\d*$/);
    if (!validNumber.test($("#edit_able_value").val())) {
      $('#form-group-input-numeric').removeClass('error').addClass('error');
      $('#form-group-input-numeric .error-message').removeClass('hide');
      return 0;
    } else {
      $('#form-group-input-numeric').removeClass('error');
      $('#form-group-input-numeric .error-message').removeClass('hide').addClass('hide');
    }

    if(!is_closed){
      value = $("#edit_able_value").val();
      note = $("#edit_able_note").val();
      type = $("#edit_able_type").val();
      report_id = $("#edit_able_report_id").val();
      row_id = $("#edit_able_row_id").val();
      $.ajax({
        url: base_url + '/reports/update-report-data',
        type: 'POST',
        data: {value:value,note:note,type: type,report_id: report_id},
        success: function(res) {
          if(res.status == 'success'){
            $("#AdjustModal").modal('hide')
            $(".loader-overlay").show()
            datatable.row(row_id).data(res.data).draw()
            $(".loader-overlay").hide()
          }
        }
      })
    }
    
  })

  $(document).on('click','#reconcile_add_replace_btn',function(event){
    $("#reconcile_add_replace_modal").modal();
  })

  $(document).on('click','.expand-able', function(event){
    $(".expand-able-child-"+$(this).data('parent_id')).toggle(500);
  })

  $(document).on('click', '.reconcile-note', function(event) {
    var tr = $(this).parent();
    var reconcile_data = datatable.row(tr).data();
    edit_note_row_selected = tr;
    $('#reconcileId').val($(this).attr('data-reconcile-id'));
    $('#customerIdEditNote').text(reconcile_data["grandadmin_customer_id"]);
    $('#customerNameEditNote').text(reconcile_data["grandadmin_customer_name"]);
    if (reconcile_data["note"] == null || reconcile_data["note"] == '-' || reconcile_data["note"].trim() == '') {
      $('#editReconcileNote').text('');
    } else {
      $('#editReconcileNote').text(reconcile_data["note"]);
    }
    $('#updateReconcileNoteModal').modal('show');
  });

  $("#re-upload-file-close-button").click(function(){
    $('#reconcile_add_replace_modal').modal('hide');
  })

  $('#editReconcileNoteBtnSubmit').click(function() {
    $('#editReconcileNoteBtnSubmit .spinner-button').removeClass('hide');
    var reconcile_id = $('#reconcileId').val()
    if (reconcile_id == '' || reconcile_id == false) {
      $('#updateReconcileNoteModal').modal('hide');
      Swal.fire({
        icon: "error",
        title: "Oops...",
        text: "Require reconcile id",
      });
      return 0;
    } 

    var reconcile_note = $('#editReconcileNote').val()
    
    var data = {
      id: reconcile_id,
      note: reconcile_note
    }

    $.ajax({
      url: base_url + '/reports/update-reconcile-note',
      type: 'POST',
      data: JSON.stringify(data),
      processData: false,
      contentType: false,
      success: function(res) {
        $('#updateReconcileNoteModal').modal('hide');
        $('#editReconcileNoteBtnSubmit .spinner-button').removeClass('hide').addClass('hide');
        if (res.status === 'success') {
          Swal.fire({
            position: 'top-end',
            icon: 'success',
            title: 'Successfully updated',
            showConfirmButton: false,
            timer: 1500
          })

          var reconcile_data = datatable.row(edit_note_row_selected).data();
          if (data.note.trim() == '' || data.note == false) {
            reconcile_data["note"] = '-';
          } else {
            reconcile_data["note"] = data.note;
          }
          datatable.row(edit_note_row_selected).data(reconcile_data);

        } else {
          Swal.fire({
            position: 'top-end',
            icon: 'error',
            title: res.message,
            showConfirmButton: false,
            timer: 1500
          })
        }
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

  $("#reconcile_export_btn").click(function(){
    loadingHandler(true, 'Exporting data. please wait...');

    var filter_cash_advance_condition = $("#filter_cash_advance_condition").val();
    var filter_cash_advance = $("#filter_cash_advance").val();
    var filter_remaining_budget_condition = $("#filter_remaining_budget_condition").val();
    var filter_remaining_budget = $("#filter_remaining_budget").val();
    var filter_difference_condition = $("#filter_difference_condition").val();
    var filter_difference = $("#filter_difference").val();
    var search = $("[type='search']").val();

    $.ajax({
      url: base_url + '/reports/export',
      type: 'POST',
      data: {filter_cash_advance_condition:filter_cash_advance_condition,
        filter_cash_advance:filter_cash_advance,
        filter_remaining_budget_condition: filter_remaining_budget_condition,
        filter_remaining_budget: filter_remaining_budget,
        filter_difference_condition: filter_difference_condition,
        filter_difference: filter_difference,
        search: search,
        month: month,
        year:year
      },
      dataType: 'binary',
      xhrFields: {
        'responseType': 'blob'
      },
      success: function(data) {
        loadingHandler(false, '');

        if(data.status == 'fail'){
          
        }else{
          var d = new Date();
          var datestring = d.getDate()  + "" + (d.getMonth()+1) + "" + d.getFullYear() + "" +
  d.getHours() + "" + d.getMinutes()+""+ d.getSeconds();
          var link = document.createElement('a'),
                      filename = year+'_'+month+"_RPTH_Remaining_Budget_"+datestring+".csv";
          link.href = URL.createObjectURL(data);
          link.download = filename;
          link.click();
        }
      }
    })

  })

  $("#file-re-upload-selector").change(function() {
    switch ($(this).val()) {
      case 'gl_revenue':
        $('#example-file-link').attr('href', base_url + '/temp/example/gl_cash_advance_example.xlsx');
        break;
      
      case 'google_spending':
        $('#example-file-link').attr('href', base_url + '/temp/example/google_spending_example.csv');
        break;
    
      case 'facebook_spending':
        $('#example-file-link').attr('href', base_url + '/temp/example/facebook_spending_example.csv');
        break;
    
      case 'transfer':
        $('#example-file-link').attr('href', base_url + '/temp/example/wallet_transfer_example.csv');
        break;
    
      case 'adjustment':
        $('#example-file-link').attr('href', base_url + '/temp/example/adjustment_example.csv');
        break;
    }

    if($(this).val() == 'apis'){
      $('#example-file-link').parent().hide();
      $("#input-file-upload").hide();
      $("#checkbox-api-group").show();
      $("#get-and-update-button").show();
      $('#re-upload-file-button').hide();
    }else {
      $('#example-file-link').parent().show();
      if($(this).val() == 'gl_revenue'){
        $("#file_extension_message").html('File extension support only .xls, .xlsx');
      }else{
        $("#file_extension_message").html('File extension support only .csv');
      }

      $("#input-file-upload").show();
      $("#checkbox-api-group").hide();
      $("#get-and-update-button").hide();
      $('#re-upload-file-button').show();
    }
  })

  $("#get-and-update-button").click(function(event){
    event.preventDefault();
    var media_wallet = $("#media-wallet-checkbox").is(":checked")
    var withholding_tax = $("#withholding-tax-checkbox").is(":checked")
    var free_click_cost = $("#free-click-cost-checkbox").is(":checked")
    var remaining_ice = $("#ice-checkbox").is(":checked")
    var updated_by = "kittisak";

    $('#reconcile_add_replace_modal').modal('hide');
    loadingHandler(true, 'Getting and updating data via API. please wait...');

    $.ajax({
      url: base_url + '/resources/update-api-data',
      type: 'POST',
      data: {media_wallet:media_wallet,
        media_wallet:media_wallet,
        withholding_tax: withholding_tax,
        free_click_cost: free_click_cost,
        remaining_ice: remaining_ice,
        month: month,
        year: year,
        updated_by: updated_by
      },
      success: function(data) {
        loadingHandler(false, '');
        if(data.status == "success"){
          location.reload();
        }
      }
    });
  });

  $('#re-upload-file-button').click(function(event){
    $(this).button('loading');
    event.preventDefault();
    if ($('#reconcile_add_replace_file').val() == '') {
      Swal.fire({
        icon: 'error',
        title: 'Oops...',
        text: "Upload file was not found"
      })
      return 0;
    }

    if($("#file-re-upload-selector").val() == 'google_spending'){
      updateGoogleSpending(false);
    }else if($("#file-re-upload-selector").val() == 'facebook_spending'){
      updateFacebookSpending(false);
    }else if($("#file-re-upload-selector").val() == 'transfer'){
      updateWalletTransfer(false);
    }else if($("#file-re-upload-selector").val() == 'gl_revenue'){
      updateGlCashAdvance(false);
    }else if($("#file-re-upload-selector").val() == 'adjust_remain'){
      updateAdjustRemain(false);
    }else if($("#file-re-upload-selector").val() == 'adjust_accounting'){
      updateAdjustAccounting(false);
    }else if($("#file-re-upload-selector").val() == 'adjust_frontend'){
      updateAdjustFrontEnd(false);
    }else if($("#file-re-upload-selector").val() == 'adjustment'){
      updateAdjustment(false);
    }
    
  })

  $("#re-import-ignore-invalid-btn").click(function(event){
    event.preventDefault();
    if ($('#reconcile_add_replace_file').val() == '') {
      Swal.fire({
        icon: 'error',
        title: 'Oops...',
        text: "Google spending file was not found"
      })
      return 0;
    }
    if($("#file-re-upload-selector").val() == 'google_spending'){
      updateGoogleSpending(true);
    }else if($("#file-re-upload-selector").val() == 'facebook_spending'){
      updateFacebookSpending(true);
    }else if($("#file-re-upload-selector").val() == 'transfer'){
      updateWalletTransfer(true);
    }else if($("#file-re-upload-selector").val() == 'gl_revenue'){
      updateGlCashAdvance(true);
    }else if($("#file-re-upload-selector").val() == 'adjust_remain'){
      updateAdjustRemain(true);
    }else if($("#file-re-upload-selector").val() == 'adjust_accounting'){
      updateAdjustAccounting(true);
    }else if($("#file-re-upload-selector").val() == 'adjust_frontend'){
      updateAdjustFrontEnd(true);
    }else if($("#file-re-upload-selector").val() == 'adjustment'){
      updateAdjustment(true);
    }
  })

  $('#apply_filter').click( function() {
    $("#filter_error_message").hide();
    var filter_cash_advance = $("#filter_cash_advance").val();
    var filter_remaining_budget = $("#filter_remaining_budget").val();
    var filter_difference = $("#filter_difference").val();
    if(isNaN(filter_cash_advance) ||isNaN(filter_remaining_budget) ||isNaN(filter_difference)){
      $("#filter_error_message").show();
    }else{
      datatable.draw();
    }
    
  });

  $('#reset_filter').click( function() {
    $("#filter_error_message").hide();
    $("#filter_cash_advance_condition").val('equal')
    $("#filter_remaining_budget_condition").val('equal')
    $("#filter_difference_condition").val('equal')
    $('#filter_cash_advance').val('')
    $('#filter_remaining_budget').val('')
    $('#filter_difference').val('')
    datatable.draw();
  });

  $("#filter-container-btn").click(function(){
    if($("#filter-container").css("display") == "none"){
      $("#filter-container").show(500);
      $("#filter_expand_icon").removeClass('bx-chevron-down bx-chevron-up').addClass('bx-chevron-up');
    }else{
      $("#filter-container").hide(500);
      $("#filter_expand_icon").removeClass('bx-chevron-down bx-chevron-up').addClass('bx-chevron-down');
    }
  })

  $("#reconcile_close_period").click(function (){
    $("#modalClosePeriod").modal();
  })
  $("#confirmClosePeriodButton").click(function(){
    closePeriod();
  })

  $(".dataTable tbody").on('mouseenter','.edit-able,.reconcile-note',function(event){
    if(!is_closed){
      $(this).prepend("<i class='bx bx-pencil'></i>");
    }
  });

  $(".dataTable tbody").on('mouseleave','.edit-able,.reconcile-note',function(event){
    $(this).children('.bx-pencil').remove();
  });

  $(".dataTable tbody").on("click",'.more-children-data',function(event){
    var parent_id = $(this).parent().parent().attr('data-parent_id')
    console.log(parent_id)
    if($(this).hasClass('bx-chevron-down-circle')){
      $(".children_"+parent_id).removeClass('hide').addClass('show')
      $(this).removeClass('bx-chevron-down-circle').addClass('bx-chevron-up-circle')
    }else{
      $(".children_"+parent_id).removeClass('show').addClass('hide')
      $(this).removeClass('bx-chevron-up-circle').addClass('bx-chevron-down-circle')
    }
    
  })

  // $(".dataTable tbody").on("click",'.more-children-data',function(event){
  //   var tr = $(this).parent()
  //   var reconcile_data = datatable.row(tr).data();
  //   var parent_id = reconcile_data["parent_id"];
  //   var filter_cash_advance_condition = $("#filter_cash_advance_condition").val();
  //   var filter_cash_advance = $("#filter_cash_advance").val();
  //   var filter_remaining_budget_condition = $("#filter_remaining_budget_condition").val();
  //   var filter_remaining_budget = $("#filter_remaining_budget").val();
  //   var filter_difference_condition = $("#filter_difference_condition").val();
  //   var filter_difference = $("#filter_difference").val();
  //   var search = $("[type='search']").val();
  //   $.ajax({
  //     url: base_url + '/reports/get-report-children',
  //     type: 'POST',
  //     data: {
  //       month: month,
  //       year: year,
  //       parent_id: parent_id,
  //       search: search,
  //       filter_cash_advance_condition:filter_cash_advance_condition,
  //       filter_cash_advance:filter_cash_advance,
  //       filter_remaining_budget_condition: filter_remaining_budget_condition,
  //       filter_remaining_budget: filter_remaining_budget,
  //       filter_difference_condition: filter_difference_condition,
  //       filter_difference: filter_difference
  //     },
  //     success: function(data) {
  //       if(data.status == "success"){
  //         var newRow = ""
  //         data.data.forEach(function(child) {
  //           newRow = datatable.row.add({
  //             'parent_id': child["parent_id"],
  //             'grandadmin_customer_id': child["grandadmin_customer_id"],
  //             'grandadmin_customer_name': child["grandadmin_customer_name"],
  //             'offset_acct': child["offset_acct"],
  //             'offset_acct_name': child["offset_acct_name"],
  //             'company': child["company"],
  //             'payment_method': child["payment_method"],
  //             'last_month_remaining': child["last_month_remaining"],
  //             'adjustment_remain': child["adjustment_remain"],
  //             'receive': child["receive"],
  //             'invoice': child["invoice"],
  //             'transfer': child["transfer"],
  //             'ads_credit_note': child["ads_credit_note"],
  //             'spending_invoice': child["spending_invoice"],
  //             'adjustment_free_click_cost': child["adjustment_free_click_cost"],
  //             'adjustment_free_click_cost_old': child["adjustment_free_click_cost_old"],
  //             'adjustment_cash_advance': child["adjustment_cash_advance"],
  //             'adjustment_max': child["adjustment_max"],
  //             'cash_advance': child["cash_advance"],
  //             'remaining_ice': child["remaining_ice"],
  //             'wallet': child["wallet"],
  //             'wallet_free_click_cost': child["wallet_free_click_cost"],
  //             'withholding_tax': child["withholding_tax"],
  //             'adjustment_front_end': child["adjustment_front_end"],
  //             'remaining_budget': child["remaining_budget"],
  //             'difference': child["difference"],
  //             'note': child["note"]
  //           })
  //           newRow.draw(false)
  //           console.log(newRow.index())
  //         })
          
  //       }
  //     }
  //   });
  // })
});

