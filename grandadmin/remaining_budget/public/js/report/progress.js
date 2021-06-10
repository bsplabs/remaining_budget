function getReportStatusPercent(){
  if($("#report-progress-bar").length != 0){
    $.ajax({
      url: base_url + '/reports/get-status-percent',
      type: 'POST',
      data: {month: month, year:year},
      success:function(res) {
        if(res.status == "success"){
          if(res.data == 100){
            location.reload();
          }else{
            $("#report-progress-bar").text(res.data + "%");
            $("#report-progress-bar").attr("aria-valuenow",res.data);
            $("#report-progress-bar").css("width",res.data + "%");
          }
          
        }else{

        }
      }
    });
  } 
}


$( document ).ready(function() {
  getReportStatusPercent();
  setInterval(function(){
    getReportStatusPercent()
  }, 5000)
});