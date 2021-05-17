$(document).ready(function() {

  var customer_table = $('#customers-table').DataTable({
    'processing': true,
    'serverSide': true,
    'ajax': base_url + '/customers/get-customers',
    'columns': [
      { 'data': 'id' },
      { 'data': 'parent_id' },
      { 'data': 'grandadmin_customer_id' },
      { 'data': 'grandadmin_customer_name' },
      { 'data': 'offset_acct' },
      { 'data': 'offset_acct_name' },
      { 
        'class': 'text-center',
        'data': 'main_business',
        'render': function(data, type) {
          if (data == 0) {
            return 'No';
          } else {
            return 'Yes';
          }
        }
      },
      { 
        'class': 'text-center',
        'data': 'company',
        'render': function(data, type) {
          return data;
        }
      },
      { 
        'class': 'text-center',
        'data': 'payment_method', 
        'render': function(data, type) {
          return data;
        }
      },
      { 'data': 'created_at' },
      { 'data': 'updated_at' },
      { 'data': 'updated_by' },
      { 
        'data': 'id', 
        'render': function (data, type) {
          if (true) {
            return "<div class='d-flex justify-content-center'>" +
                      "<div class='col-6'>" +
                        "<i class='bx bxs-edit edit-customer' data-customer-id='" + data + "' style='cursor: pointer;'></i>" +
                      "</div>" +
                      "<div class='col-6'>" +
                        "<i class='bx bx-trash text-danger delete-customer' data-customer-id='" + data + "' style='cursor: pointer;'></i>" +
                      "</div>" +
                    "</div>";
          }

          return data;
        }
      },
    ],
    "columnDefs": [
      {
        "targets": -1,
        "orderable": false
      }
    ],
    'scrollX': true
  });


  $('#customers-table tbody').on('click', '.edit-customer', function() {
    var customer_data = customer_table.row($(this).parents('tr')).data();
    // set value
    $('#inputID').val(customer_data.id);
    $('#inputParentID').val(customer_data.parent_id);
    $('#inputCustomerID').val(customer_data.grandadmin_customer_id);
    $('#inputOffsetAcct').val(customer_data.offset_acct);
    $('#inputCustomerName').val(customer_data.grandadmin_customer_name);
    $('#inputOffsetAcctName').val(customer_data.offset_acct_name);
    $('#inputCompany').val(customer_data.company);
    $('#inputPaymentMethod').val(customer_data.payment_method);
    if (customer_data.main_business == '0') {
      $('#inputMainBusiness').prop('checked', false);
      $('#inputMainBusiness').val('false');
    } else {
      $('#inputMainBusiness').prop('checked', true);
      $('#inputMainBusiness').val('true');
    }

    // open modal
    $('#modal-edit-customer').modal('toggle');
  });

});

