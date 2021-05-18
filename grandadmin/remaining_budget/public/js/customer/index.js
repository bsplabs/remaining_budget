var current_row = "";
var current_id_selected = '';

$(document).ready(function () {

  var customer_table = $("#customers-table").DataTable({
    processing: true,
    serverSide: true,
    ordering: true,
    orderMulti: true,
    scrollY: '50vh',
    scrollCollapse: true,
    bLengthChange: false,
    pageLength: 100,
    order: [
      [1, 'asc'],
      [6, 'desc']
    ],
    ajax: {
      url: base_url + "/customers/get-customers",
      type: 'POST',
      data: {
        filter_parentid: 'more_than_one',
        filter_payment: 'all'
      }
    },
    columns: [
      { data: "id" },
      { data: "parent_id" },
      { data: "grandadmin_customer_id" },
      { data: "grandadmin_customer_name" },
      { data: "offset_acct" },
      { data: "offset_acct_name" },
      {
        class: "text-center",
        // orderable: false,
        data: "main_business",
        render: function (data, type) {
          if (data == 0) {
            return "No";
          } else {
            return "Yes";
          }
        },
      },
      {
        class: "text-center",
        data: "company",
        render: function (data, type) {
          return data;
        },
      },
      {
        class: "text-center",
        data: "payment_method",
        render: function (data, type) {
          return data;
        },
      },
      { data: "created_at" },
      { data: "updated_at" },
      { data: "updated_by" },
      {
        data: "id",
        render: function (data, type) {
          if (true) {
            return (
              "<div class='d-flex justify-content-center'>" +
              "<div class='col-6'>" +
              "<i class='bx bxs-edit edit-customer' data-customer-id='" +
              data +
              "' style='cursor: pointer;'></i>" +
              "</div>" +
              "<div class='col-6'>" +
              "<i class='bx bx-trash text-danger delete-customer' data-customer-id='" +
              data +
              "' style='cursor: pointer;'></i>" +
              "</div>" +
              "</div>"
            );
          }

          return data;
        },
      },
    ],
    columnDefs: [
      {
        targets: -1,
        orderable: false,
      },
    ],
    scrollX: true,
  });

  $("#customers-table tbody").on("click", ".delete-customer", function () {
    current_id_selected = $(this).attr('data-customer-id');
    $('#modalDeleteCustomer').modal('toggle');
  });

  $('#confirmDeleteCustomerButton').click(function() {
    $('#confirmDeleteCustomerButton .spinner-button').removeClass('hide');
    var data = {
      "id": current_id_selected
    };

    $.ajax({
      url: base_url + '/customers/delete-customer',
      type: 'POST',
      data: JSON.stringify(data),
      cache: false,
      processData: false,
      success: function(res) {
        $('#modalDeleteCustomer').modal('toggle');
        $('#confirmDeleteCustomerButton .spinner-button').removeClass('hide').addClass('hide');
        if (res.status === 'success') {
          customer_table.ajax.reload(function () {
            Swal.fire({
              icon: "success",
              title: "Successfully Deleted",
              text: "",
            });
          }, false);
        } else {
          Swal.fire({
            icon: "error",
            title: "Oops...",
            text: res.message,
          });
        }
      }
    });
  });

  $("#customers-table tbody").on("click", ".edit-customer", function () {
    var customer_data = customer_table.row($(this).parents("tr")).data();
    current_row = $(this).parents("tr");
    // set value
    $("#inputID").val(customer_data.id);
    $("#inputParentID").val(customer_data.parent_id);
    $("#inputCustomerID").val(customer_data.grandadmin_customer_id);
    $("#inputOffsetAcct").val(customer_data.offset_acct);
    $("#inputCustomerName").val(customer_data.grandadmin_customer_name);
    $("#inputOffsetAcctName").val(customer_data.offset_acct_name);
    $("#inputCompany").val(customer_data.company);
    $("#inputPaymentMethod").val(customer_data.payment_method);
    if (customer_data.main_business == "0") {
      $("#inputMainBusiness").prop("checked", false);
      $("#inputMainBusiness").val("false");
    } else {
      $("#inputMainBusiness").prop("checked", true);
      $("#inputMainBusiness").val("true");
    }

    // open modal
    $("#modal-edit-customer").modal("toggle");
  });

  $("#saveCustomerEdited").click(function (event) {
    event.preventDefault();

    var data = {
      id: $("#inputID").val(),
      parent_id: $("#inputParentID").val(),
      grandadmin_customer_id: $("#inputCustomerID").val(),
      grandadmin_customer_name: $("#inputCustomerName").val(),
      offset_acct: $("#inputOffsetAcct").val(),
      offset_acct_name: $("#inputOffsetAcctName").val(),
      company: $("#inputCompany").val(),
      payment_method: $("#inputPaymentMethod").val(),
      main_business: $("#inputMainBusiness").is(":checked"),
    };

    $.ajax({
      url: base_url + "/customers/edit-customer",
      type: "POST",
      data: JSON.stringify(data),
      processData: false,
      contentType: "application/json",
      success: function (res) {
        // console.log(res);
        $("#modal-edit-customer").modal("toggle");
        if (res.status == "success") {
          customer_table.ajax.reload(function () {
            Swal.fire({
              icon: "success",
              title: "Successfully Updated",
              text: "",
            });
          }, false);
        } else {
          Swal.fire({
            icon: "error",
            title: "Oops...",
            text: res.message,
          });
        }
      }
    });
  });

  $('#importCustomers').click(function() {
    $('#modalImportCustomers').modal('toggle');
  });

  $('#importCustomersForm').submit(function(event) {
    $('#importCustomersSubmitButton .spinner-button').removeClass('hide');

    event.preventDefault();

    var formData = new FormData($(this)[0]);

    $.ajax({
      url: base_url + '/customers/import-customers',
      type: 'POST',
      data: formData,
      processData: false,
      contentType: false,
      success: function(res) {
        $('#modalImportCustomers').modal('toggle');
        $('#importCustomersSubmitButton .spinner-button').removeClass('hide').addClass('hide');
        if (res.status === 'error') {
          Swal.fire({
            icon: "error",
            title: "Oops...",
            text: res.message,
          });
        } else if (res.status === 'success') {
          $('#resultImportCustomerInsertTotal').text(res.data.insert_total);
          $('#resultImportCustomerReplaceTotal').text(res.data.replace_total);
          $('#resultImportCustomerInsertSuccess').text(res.data.insert_success_total);
          $('#resultImportCustomerReplaceSuccess').text(res.data.replace_success_total);
          $('#resultImportCustomerInsertFail').text(res.data.insert_fail_total);
          $('#resultImportCustomerReplaceFail').text(res.data.replace_fail_total);

          $i = 1;
          res.data.result_lists.forEach(function(el) {
            if ($i > 100) return 0;
            var td = '<td>' + el.row + '</td>';
            td += '<td>' + el.action + '</td>';
            td += '<td class="text-danger">' + el.error_message + '</td>';

            $('#customerImportResultTbody').append('<tr>' + td + '</tr>');
            $i++;
          });
          $('#modalCustomerImportResults').modal('show');
        }
      }
    });

  });

  $("#exportCustomers").click(function () {
    var xhr = new XMLHttpRequest();
    xhr.open("GET", base_url + "/customers/export-customers");
    xhr.responseType = "blob";
    xhr.onload = function () {
      if (this.status === 200) {
        var blob = this.response;
        var filename = "";
        var disposition = xhr.getResponseHeader("Content-Disposition");
        if (disposition && disposition.indexOf("attachment") !== -1) {
          var filenameRegex = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/;
          var matches = filenameRegex.exec(disposition);
          if (matches != null && matches[1]) {
            filename = matches[1].replace(/['"]/g, "");
          }
        }

        if (typeof window.navigator.msSaveBlob !== "undefined") {
          window.navigator.msSaveBlob(blob, filename);
        } else {
          var URL = window.URL || window.webkitURL;
          var downloadUrl = URL.createObjectURL(blob);

          if (filename) {
            var a = document.createElement("a");
            if (typeof a.download === "undefined") {
              window.location.href = downloadUrl;
            } else {
              a.href = downloadUrl;
              a.download = filename;
              document.body.appendChild(a);
              a.click();
            }
          } else {
            window.location.href = downloadUrl;
          }

          setTimeout(function () {
            URL.revokeObjectURL(downloadUrl);
          }, 100); // cleanup
        }
      }
    };
    xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhr.send();
  });

});
