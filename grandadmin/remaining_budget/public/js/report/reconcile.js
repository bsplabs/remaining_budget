var datatable;
var is_loaded_data = false;
var is_loaded_child_data = false;

function getReconcileDate(){
  var data_amount = 0;
  $.ajax({
    url: base_url + '/reports/get-report-data',
    type: 'POST',
    data: {year:year,month:month},
    success: function(res) {
      result = res.data
      total = result.length;
      $("#reconcile_content_loading").hide();
      $("#reconcile_content").html("");
      $.each(result, function(index,value){
        if(value.amount > 1 ){
          $.ajax({
            url: base_url + '/reports/get-report-data-by-parent',
            type: 'POST',
            data: {parent_id:value.parent_id},
            success: function(res) {
              html = `<tr data-report_id="`+value.report_id+`" style="background-color:#eeeeff;">
                <td>`+value.parent_id+`<i data-parent_id="`+value.parent_id+`" class="fa fa-chevron-up pull-right expand-able" aria-hidden="true"></i></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td class="text-right">`+currencyFormat(value.last_month_remaining)+`</td>
                <td class="text-right">`+currencyFormat(value.adjustment_remain)+`</td>
                <td class="text-right">`+currencyFormat(value.receive)+`</td>
                <td class="text-right">`+currencyFormat(value.invoice)+`</td>
                <td class="text-right">`+currencyFormat(value.transfer)+`</td>
                <td class="text-right">`+currencyFormat(value.ads_credit_note)+`</td>
                <td class="text-right">(`+currencyFormat(value.spending_invoice)+`)</td>
                <td class="text-right">`+currencyFormat(value.adjustment_free_click_cost)+`</td>
                <td class="text-right">`+currencyFormat(value.adjustment_free_click_cost_old)+`</td>
                <td class="text-right">`+currencyFormat(value.adjustment_cash_advance)+`</td>
                <td class="text-right">`+currencyFormat(value.adjustment_max)+`</td>
                <td class="text-right">`+currencyFormat(value.cash_advance)+`</td>
                <td></td>
                <td class="text-right">`+currencyFormat(value.remaining_ice)+`</td>
                <td class="text-right">`+currencyFormat(value.wallet)+`</td>
                <td class="text-right">`+currencyFormat(value.wallet_free_click_cost)+`</td>
                <td class="text-right">`+currencyFormat(value.withholding_tax)+`</td>
                <td class="text-right">`+currencyFormat(value.adjustment_front_end)+`</td>
                <td class="text-right">`+currencyFormat(value.remaining_budget)+`</td>
                <td></td>
                <td class="text-right">`+currencyFormat(value.difference)+`</td>
                <td>`+stringNullToDash(value.note)+`</td>
                </tr>`
              $("#reconcile_content").append(html);
              child = res.data
              $.each(child, function(index_child,value_child){
                appendReconcileRow(value_child,true);
              });
              data_amount++
              if(data_amount >= total){
                is_loaded_child_data = true;
                // renderDataTables()
              }
            }
          });
          
        }else{
          appendReconcileRow(value,false)
          data_amount++
        }
      });
      
      if(data_amount >= total){
        is_loaded_data = true;
        // renderDataTables()
      }
    }
  });
  
}

function appendReconcileRow(value,is_child){
  if(value.adjustment_remain_note != "" && value.adjustment_remain_note != null){
    var adjustment_remain_html = `<td data-value="`+value.adjustment_remain+`" data-note="`+value.adjustment_remain_note+`" data-column_name="Adjust ยอดยกมา" data-column_type="adjustment_remain" class="text-right edit-able more-note-td" data-toggle="tooltip" data-placement="top" title="" data-original-title="Tooltip on top"><div class="more-note-icon"></div>`+currencyFormat(value.adjustment_remain)+`</td>`;
  }else{
    var adjustment_remain_html = `<td data-value="`+value.adjustment_remain+`" data-note="" data-column_name="Adjust ยอดยกมา" data-column_type="adjustment_remain" class="text-right edit-able" title="click to edit" >`+currencyFormat(value.adjustment_remain)+`</td>`;
  }
  
  if(value.adjustment_free_click_cost_note != "" && value.adjustment_free_click_cost_note != null){
    var adjustment_free_click_cost_html = `<td data-value="`+value.adjustment_free_click_cost+`" data-note="`+value.adjustment_free_click_cost_note+`" data-column_name="JE + Free Clickcost" data-column_type="adjustment_free_click_cost" class="text-right edit-able more-note-td" data-toggle="tooltip" data-placement="top" title="" data-original-title="Tooltip on top"><div class="more-note-icon"></div>`+currencyFormat(value.adjustment_free_click_cost)+`</td>`;
  }else{
    var adjustment_free_click_cost_html = `<td data-value="`+value.adjustment_free_click_cost+`" data-note="" data-column_name="JE + Free Clickcost" data-column_type="adjustment_free_click_cost" class="text-right edit-able" title="click to edit" >`+currencyFormat(value.adjustment_free_click_cost)+`</td>`;
  }
  if(value.adjustment_free_click_cost_old_note != "" && value.adjustment_free_click_cost_old_note != null){
    var adjustment_free_click_cost_old_html = `<td data-value="`+value.adjustment_free_click_cost_old+`" data-note="`+value.adjustment_free_click_cost_old_note+`" data-column_name="Free Clickcost (ค่าใช้จ่ายต้องห้าม)" data-column_type="adjustment_free_click_cost_old" class="text-right edit-able more-note-td" data-toggle="tooltip" data-placement="top" title="" data-original-title="Tooltip on top"><div class="more-note-icon"></div>`+currencyFormat(value.adjustment_free_click_cost_old)+`</td>`;
  }else{
    var adjustment_free_click_cost_old_html = `<td data-value="`+value.adjustment_free_click_cost_old+`" data-note="" data-column_name="Free Clickcost (ค่าใช้จ่ายต้องห้าม)" data-column_type="adjustment_free_click_cost_old" class="text-right edit-able" title="click to edit" >`+currencyFormat(value.adjustment_free_click_cost_old)+`</td>`;
  }
  if(value.adjustment_cash_advance_note != "" && value.adjustment_cash_advance_note != null){
    var adjustment_cash_advance_html = `<td data-value="`+value.adjustment_cash_advance+`" data-note="`+value.adjustment_cash_advance_note+`" data-column_name="adjustment" data-column_type="adjustment_cash_advance" class="text-right edit-able more-note-td" data-toggle="tooltip" data-placement="top" title="" data-original-title="Tooltip on top"><div class="more-note-icon"></div>`+currencyFormat(value.adjustment_cash_advance)+`</td>`;
  }else{
    var adjustment_cash_advance_html = `<td data-value="`+value.adjustment_cash_advance+`" data-note="" data-column_name="adjustment" data-column_type="adjustment_cash_advance" class="text-right edit-able" title="click to edit" >`+currencyFormat(value.adjustment_cash_advance)+`</td>`;
  }
  if(value.adjustment_max_note != "" && value.adjustment_max_note != null){
    var adjustment_max_html = `<td data-value="`+value.adjustment_max+`" data-note="`+value.adjustment_max_note+`" data-column_name="Max" data-column_type="adjustment_max" class="text-right edit-able more-note-td" data-toggle="tooltip" data-placement="top" title="" data-original-title="Tooltip on top"><div class="more-note-icon"></div>`+currencyFormat(value.adjustment_max)+`</td>`;
  }else{
    var adjustment_max_html = `<td data-value="`+value.adjustment_max+`" data-note="" data-column_name="Max" data-column_type="adjustment_max" class="text-right edit-able" title="click to edit" >`+currencyFormat(value.adjustment_max)+`</td>`;
  }
  if(value.adjustment_front_end_note != "" && value.adjustment_front_end_note != null){
    var adjustment_front_end_html = `<td data-value="`+value.adjustment_front_end+`" data-note="`+value.adjustment_front_end_note+`" data-column_name="Adjust Front-end" data-column_type="adjustment_front_end" class="text-right edit-able more-note-td" data-toggle="tooltip" data-placement="top" title="" data-original-title="Tooltip on top"><div class="more-note-icon"></div>`+currencyFormat(value.adjustment_front_end)+`</td>`;
  }else{
    var adjustment_front_end_html = `<td data-value="`+value.adjustment_front_end+`" data-note="" data-column_name="Adjust Front-end" data-column_type="adjustment_front_end" class="text-right edit-able" title="click to edit" >`+currencyFormat(value.adjustment_front_end)+`</td>`;
  }

  if(is_child){
    html = `<tr data-customer_name="`+value.grandadmin_customer_name+`" data-report_id="`+value.report_id+`" class="child-row expand-able-child-`+value.parent_id+`" style="background-color:#eeffff;display:none;">`
  }else{
    html = `<tr data-customer_name="`+value.grandadmin_customer_name+`" data-report_id="`+value.report_id+`">`
  }
  html += `
    <td>`+value.parent_id+`</td>
    <td>`+value.grandadmin_customer_id+`</td>
    <td>`+value.grandadmin_customer_name+`</td>
    <td>`+value.offset_acct+`</td>
    <td>`+value.offset_acct_name+`</td>
    <td>`+value.company+`</td>
    <td>`+value.payment_method+`</td>
    <td class="text-right">`+currencyFormat(value.last_month_remaining)+`</td>`
    + adjustment_remain_html +
    `<td class="text-right">`+currencyFormat(value.receive)+`</td>
    <td class="text-right">`+currencyFormat(value.invoice)+`</td>
    <td class="text-right">`+currencyFormat(value.transfer)+`</td>
    <td class="text-right">`+currencyFormat(value.ads_credit_note)+`</td>
    <td class="text-right">(`+currencyFormat(value.spending_invoice)+`)</td>`
    + adjustment_free_click_cost_html + ``
    + adjustment_free_click_cost_old_html + ``
    + adjustment_cash_advance_html + ``
    + adjustment_max_html + 
    `<td class="text-right">`+currencyFormat(value.cash_advance)+`</td>
    <td></td>
    <td class="text-right">`+currencyFormat(value.remaining_ice)+`</td>
    <td class="text-right">`+currencyFormat(value.wallet)+`</td>
    <td class="text-right">`+currencyFormat(value.wallet_free_click_cost)+`</td>
    <td class="text-right">`+currencyFormat(value.withholding_tax)+`</td>`
    + adjustment_front_end_html +
    `<td class="text-right">`+currencyFormat(value.remaining_budget)+`</td>
    <td></td>
    <td class="text-right">`+currencyFormat(value.difference)+`</td>
    <td>`+stringNullToDash(value.note)+`</td>
    </tr>`
  $("#reconcile_content").append(html);
}

function renderDataTables(){
  datatable = $('#report_reconcile_table').DataTable({
    "scrollY": 300,
    "scrollX": true,
    "lengthChange": false,
    "searching": true,
    "pageLength": 100
  }); 
  
  $(".loader-overlay").hide()
  $.fn.dataTable.ext.search.push(
    function( settings, data, dataIndex ) {
        var filter_cash_advance = parseFloat( $('#filter_cash_advance').val(), 10 );
        var filter_remaining_budget = parseFloat( $('#filter_remaining_budget').val(), 10 );
        var filter_difference = parseFloat( $('#filter_difference').val(), 10 );
        var filter_cash_advance_condition = $('#filter_cash_advance_condition').val();
        var filter_remaining_budget_condition = $('#filter_remaining_budget_condition').val();
        var filter_difference_condition = $('#filter_difference_condition').val();
        
        var cash_advance = parseFloat( data[18] ) || 0;
        var remaining_budget = parseFloat( data[25] ) || 0;
        var difference = parseFloat( data[27] ) || 0;

        var is_cash_advance_valid = false;
        var is_remaining_budget_valid = false;
        var is_difference_valid = false;

        if(isNaN( filter_cash_advance )){
          is_cash_advance_valid = true;
        }else{
          if(filter_cash_advance_condition == "equal"){
            if(filter_cash_advance == cash_advance){
              is_cash_advance_valid = true;
            }
          }else if(filter_cash_advance_condition == "less_than"){
            if(filter_cash_advance > cash_advance){
              is_cash_advance_valid = true;
            }
          }else if(filter_cash_advance_condition == "greater_than"){
            if(filter_cash_advance < cash_advance){
              is_cash_advance_valid = true;
            }
          }else if(filter_cash_advance_condition == "not"){
            if(filter_cash_advance != cash_advance){
              is_cash_advance_valid = true;
            }
          }
        }

        if(isNaN( filter_remaining_budget )){
          is_remaining_budget_valid = true;
        }else{
          if(filter_remaining_budget_condition == "equal"){
            if(filter_remaining_budget == remaining_budget){
              is_remaining_budget_valid = true;
            }
          }else if(filter_remaining_budget_condition == "less_than"){
            if(filter_remaining_budget > remaining_budget){
              is_remaining_budget_valid = true;
            }
          }else if(filter_remaining_budget_condition == "greater_than"){
            if(filter_remaining_budget < remaining_budget){
              is_remaining_budget_valid = true;
            }
          }else if(filter_remaining_budget_condition == "not"){
            if(filter_remaining_budget != remaining_budget){
              is_remaining_budget_valid = true;
            }
          }
        }

        if(isNaN( filter_difference )){
          is_difference_valid = true;
        }else{
          if(filter_difference_condition == "equal"){
            if(filter_difference == difference){
              is_difference_valid = true;
            }
          }else if(filter_difference_condition == "less_than"){
            if(filter_difference > difference){
              is_difference_valid = true;
            }
          }else if(filter_difference_condition == "greater_than"){
            if(filter_difference < difference){
              is_difference_valid = true;
            }
          }else if(filter_difference_condition == "not"){
            if(filter_difference != difference){
              is_difference_valid = true;
            }
          }
        }

        if ( is_cash_advance_valid && is_remaining_budget_valid && is_difference_valid )
        {
            return true;
        }
        return false;
    }
);
}

function closePeriod(){
  var created_by = "kittisak"
  $.ajax({
    url: base_url + '/reports/close-period',
    type: 'POST',
    data: {year:year,month:month,created_by: created_by},
    success:function(res) {
      if(res.status == "success"){

        location.reload();
      }else{
        $("#modalClosePeriod").modal('hide');
      }
    }
  });
}

function updateGoogleSpending() {

  var formData = new FormData();

  formData.append("month", month);
  formData.append("year", year);
  formData.append("googleSpendingInputFile",$('#reconcile_add_replace_file')[0].files[0])
  formData.append("updated_by", "kittisak");


  $.ajax({
    url: base_url + '/resources/update-google-spending',
    type: 'POST',
    data: formData,
    processData: false,
    contentType: false,
    success:function(res) {
      if(res.status == "success"){
        if($("#reconcile_add_replace_modal").hasClass('show')){
          $("#reconcile_add_replace_modal").modal('hide');
        }
      }
    }
  });
  
}

function updateFacebookSpending() {

  var formData = new FormData();

  formData.append("month", month);
  formData.append("year", year);
  formData.append("facebookSpendingInputFile",$('#reconcile_add_replace_file')[0].files[0])
  formData.append("updated_by", "kittisak");


  $.ajax({
    url: base_url + '/resources/update-facebook-spending',
    type: 'POST',
    data: formData,
    processData: false,
    contentType: false,
    success:function(res) {
      if(res.status == "success"){
        if($("#reconcile_add_replace_modal").hasClass('show')){
          $("#reconcile_add_replace_modal").modal('hide');
        }
      }
    }
  });
  
}

$( document ).ready(function() {
  getReconcileDate();
  setTimeout(function(){ renderDataTables() }, 5000);

  $(document).on('click','.edit-able',function(event){
    if(!is_closed){
      column_name = $(this).data('column_name');
      column_type = $(this).data('column_type');
      report_id = $(this).parent().data('report_id');
      customer_name = $(this).parent().data('customer_name');
      $("#AdjustModal .modal-title").html("แก้ไข "+column_name);
      $("#edit_able_customer_name").html(customer_name);
      $("#edit_able_value_label").html(column_name);
      $("#edit_able_type").val(column_type);
      $("#edit_able_value").val($(this).data("value"));
      $("#edit_able_note").val($(this).data("note"));
      $("#edit_able_report_id").val(report_id);
      
      $("#AdjustModal").modal()
    }
  })

  $(document).on('click','#edit_able_submit',function(event){
    if(!is_closed){
      value = $("#edit_able_value").val();
      note = $("#edit_able_note").val();
      type = $("#edit_able_type").val();
      report_id = $("#edit_able_report_id").val();
      $.ajax({
        url: base_url + '/reports/update-report-data',
        type: 'POST',
        data: {value:value,note:note,type: type,report_id: report_id},
        success: function(res) {
          if(res.status == 'success'){
            $("#AdjustModal").modal('hide')
            $("#reconcile_content").html('');
            $("#reconcile_content_loading").show();
            getReconcileDate();
          }
        }
      })
    }
    
  })

  $(document).on('click','#reconcile_add_replace_btn',function(event){
    $("#reconcile_add_replace_modal").modal();
  })

  $(document).on('click','.expand-able', function(event){
    $(".expand-able-child-"+$(this).data('parent_id')).toggle();
  })

  //  Month and year drop-down change
  $('#month-year-selector').change(function() {
    var month_year_exp = $(this).val().split('_');
    var month = month_year_exp[0];
    var year = month_year_exp[1];
    window.location.replace(base_url + "/reports/" + year + "/" + month);
  });

  $("#reconcile_export_btn").click(function(){
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
        if(data.status == 'fail'){
          console.log(data);
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

  $("#file-re-upload-selector").change(function(){
    if($(this).val() == 'apis'){
      $("#input-file-upload").hide();
      $("#checkbox-api-group").show();
      $("#get-and-update-button").show();
      $('#re-upload-file-button').hide();
    }else{
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
        if(data.status == "success"){
          $("#reconcile_add_replace_modal").modal('hide')
        }
      }
    });
  });

  $('#re-upload-file-button').click(function(event){
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
      updateGoogleSpending();
    }

    if($("#file-re-upload-selector").val() == 'facebook_spending'){
      updateFacebookSpending();
    }

  })

  $('#apply_filter').click( function() {
    datatable.draw();
  });

  $('#reset_filter').click( function() {
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
      $("#filter-container").show();
    }else{
      $("#filter-container").hide();
    }
  })

  $("#reconcile_close_period").click(function (){
    $("#modalClosePeriod").modal();
  })
  $("#confirmClosePeriodButton").click(function(){
    closePeriod();
  })

  $(".more-note-td").hover(function(event){
    // $(".more-note-td").tooltip()
    console.log('---------')
  })
});

