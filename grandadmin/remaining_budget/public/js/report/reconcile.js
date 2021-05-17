function getReconcileDate(){
  $.ajax({
    url: base_url + '/reports/get-report-data',
    type: 'POST',
    data: {year:year,month:month},
    success: function(res) {
      result = res.data
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
                html = `<tr data-report_id="`+value_child.report_id+`" class="child-row expand-able-child-`+value_child.parent_id+`" style="background-color:#eeffff;display:none;">
          <td class="text-right">`+value_child.parent_id+`</td>
          <td>`+value_child.grandadmin_customer_id+`</td>
          <td>`+value_child.grandadmin_customer_name+`</td>
          <td>`+value_child.offset_acct+`</td>
          <td>`+value_child.offset_acct_name+`</td>
          <td>`+value_child.company+`</td>
          <td>`+value_child.payment_method+`</td>
          <td class="text-right">`+currencyFormat(value_child.last_month_remaining)+`</td>
          <td data-column_name="Adjust ยอดยกมา" data-column_type="adjustment_remain" class="text-right edit-able" title="click to edit">`+currencyFormat(value_child.adjustment_remain)+`</td>
          <td class="text-right">`+currencyFormat(value_child.receive)+`</td>
          <td class="text-right">`+currencyFormat(value_child.invoice)+`</td>
          <td class="text-right">`+currencyFormat(value_child.transfer)+`</td>
          <td class="text-right">`+currencyFormat(value_child.ads_credit_note)+`</td>
          <td class="text-right">(`+currencyFormat(value_child.spending_invoice)+`)</td>
          <td class="text-right">`+currencyFormat(value_child.adjustment_free_click_cost)+`</td>
          <td class="text-right">`+currencyFormat(value_child.adjustment_free_click_cost_old)+`</td>
          <td class="text-right">`+currencyFormat(value_child.adjustment_cash_advance)+`</td>
          <td class="text-right">`+currencyFormat(value_child.adjustment_max)+`</td>
          <td class="text-right">`+currencyFormat(value_child.cash_advance)+`</td>
          <td></td>
          <td class="text-right">`+currencyFormat(value_child.remaining_ice)+`</td>
          <td class="text-right">`+currencyFormat(value_child.wallet)+`</td>
          <td class="text-right">`+currencyFormat(value_child.wallet_free_click_cost)+`</td>
          <td class="text-right">`+currencyFormat(value_child.withholding_tax)+`</td>
          <td class="text-right">`+currencyFormat(value_child.adjustment_front_end)+`</td>
          <td class="text-right">`+currencyFormat(value_child.remaining_budget)+`</td>
          <td></td>
          <td class="text-right">`+currencyFormat(value_child.difference)+`</td>
          <td>`+stringNullToDash(value_child.note)+`</td>
          </tr>`
          $("#reconcile_content").append(html);
              });
            }
          });
          
        }else{
          html = `<tr data-report_id="`+value.report_id+`">
          <td>`+value.parent_id+`</td>
          <td>`+value.grandadmin_customer_id+`</td>
          <td>`+value.grandadmin_customer_name+`</td>
          <td>`+value.offset_acct+`</td>
          <td>`+value.offset_acct_name+`</td>
          <td>`+value.company+`</td>
          <td>`+value.payment_method+`</td>
          <td class="text-right">`+currencyFormat(value.last_month_remaining)+`</td>
          <td data-column_name="Adjust ยอดยกมา" data-column_type="adjustment_remain" class="text-right edit-able" title="click to edit" >`+currencyFormat(value.adjustment_remain)+`</td>
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
        }
        
        
      });
    }
  });
}

$( document ).ready(function() {
  getReconcileDate();

  $(document).on('click','.edit-able',function(event){
    column_name = $(this).data('column_name');
    column_type = $(this).data('column_type');
    report_id = $(this).parent().data('report_id');
    $("#myModal .modal-title").html("แก้ไข "+column_name);
    $("#edit_able_value_label").html(column_name);
    $("#edit_able_type").val(column_type);
    $("#edit_able_value").val($(this).text());
    $("#edit_able_report_id").val(report_id);
    
    $("#myModal").modal()
  })

  $(document).on('click','#edit_able_submit',function(event){
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
          $("#myModal").modal('hide')
          $("#reconcile_content").html('');
          $("#reconcile_content_loading").show();
          getReconcileDate();
        }
      }
    })
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
});

