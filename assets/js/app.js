$(document).ready(function () {
    // set active tab
    $('.nav-tabs a').on('click', function() {
       var activeTab = $(this).attr('href').replace('#',''); 
       
       $.getJSON('set-active-tab', {'activeTab':activeTab}, function(){
           
       });
    });
    
    // search for the items to remove
    $('#removeForm #search').on('click', function() {
        var host = $('#removeForm #host').val();
        var key_template = $('#removeForm #keyTemplate').val();
        
        $.getJSON('remove-search', {'host':host, 'key_template':key_template}, function(data){
            
            if(data.length) {
                $('#removeForm #hostFormGroup').addClass('hidden');
                $('#removeForm #keyTemplateFormGroup').addClass('hidden');
                $('#removeForm #search').addClass('hidden');
                
                $.each(data, function () {
                        $('#itemsToRemoveContainer').append($("<input type='hidden' />").attr('name', "itemsToRemove[]").val(this.itemid));
                        $('#itemsToRemoveContainer').append(this.key+"<br/>");
                });
                
                $('#removeForm #itemsToRemoveFormGroup').removeClass('hidden');
                $('#removeForm #submit').removeClass('hidden');
            } else {
                $('#removeForm #noResults').removeClass('hidden');
            }
        });
        
        return false;
    });
    
    $('#removeForm #submit').on('click', function() {
       
        var itemsNumber = $('#removeForm #itemsToRemoveFormGroup input').length;
        
        if(confirm("Do you really want to remove "+itemsNumber+' items?')) {
            //$('#removeForm').submit();
            
            return true;
        }
        
        return false;
    });
    
    // load applications for the host
    $('#createForm #host').change(function () {
        var host_id = $('#createForm #host').val();

        if (host_id == "") {
            $("#createForm #application option:gt(0)").remove();
            $('#createForm #application').attr('disabled', true);
        } else {
            $.getJSON('get-applications-by-host-id', {'host_id': host_id}, function (data) {
                var options = $("#createForm #application");

                $("#createForm #application option:gt(0)").remove();

                $.each(data, function () {
                    options.append($("<option />").val(this.applicationid).text(this.name));
                });

                $('#createForm #application').attr('disabled', false);
            });
        }
    });

    // enable the formula field if necessary
    $('#createForm #type').change(function () {
        var type = $('#createForm #type').val();

        if (type == 'calculated') {
            $('#formulaTemplateFormGroup').removeClass('hidden');
        } else {
            $('#formulaTemplateFormGroup').addClass('hidden');
        }
    });
});