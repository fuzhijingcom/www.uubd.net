/**
 * Created by chochik on 7/11/16.
 */
$(document).ready(function() {
    $("#reservation").daterangepicker(null, function(start, end, label) {
        console.log(start.toISOString(), end.toISOString(), label);
    });
    $("#s_type").val(sType);
    console.log(sType);
    console.log(role);
    if(role!=1){
        $("#s_type").attr('disabled',true);
    }
});
