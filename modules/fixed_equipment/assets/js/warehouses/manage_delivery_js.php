
<script>

"use strict";
    <?php if(isset($invoice_id)){ ?>
    var InvoiceServerParams = {
        "invoice_id": "input[name='invoice_id']",
         "day_vouchers": "input[name='date_add']",
     };
 <?php }else{ ?>
    var InvoiceServerParams = {
        "invoice_id": '',
         "day_vouchers": "input[name='date_add']",
     };

<?php } ?>


var table_manage_delivery = $('.table-table_manage_delivery');

 initDataTable(table_manage_delivery, admin_url+'fixed_equipment/table_manage_delivery',[],[], InvoiceServerParams, [0 ,'desc']);

$('.delivery_sm').DataTable().columns([0]).visible(false, false);


 $('#date_add').on('change', function() {
    table_manage_delivery.DataTable().ajax.reload();
});


  init_goods_delivery();
  function init_goods_delivery(id) {
    "use strict";
    load_small_table_item_proposal(id, '#delivery_sm_view', 'delivery_id', 'fixed_equipment/view_delivery', '.delivery_sm');
  }
  var hidden_columns = [3,4,5];

 
  function load_small_table_item_proposal(pr_id, selector, input_name, url, table) {
    "use strict";
    var _tmpID = $('input[name="' + input_name + '"]').val();
    // Check if id passed from url, hash is prioritized becuase is last
    if (_tmpID !== '' && !window.location.hash) {
        pr_id = _tmpID;
        // Clear the current id value in case user click on the left sidebar credit_note_ids
        $('input[name="' + input_name + '"]').val('');
    } else {
        // check first if hash exists and not id is passed, becuase id is prioritized
        if (window.location.hash && !pr_id) {
            pr_id = window.location.hash.substring(1); //Puts hash in variable, and removes the # character
        }
    }
    if (typeof(pr_id) == 'undefined' || pr_id === '') { return; }
    if (!$("body").hasClass('small-table')) { 
        toggle_small_view_proposal(table, selector); 
    }
   
    $('input[name="' + input_name + '"]').val(pr_id);
    do_hash_helper(pr_id);
    $(selector).load(admin_url + url + '/' + pr_id, function(responseTxt, statusTxt, xhr){
        $('li.tab-separator.toggle_view').remove();
        $('#box-loading').hide();
    });
    if (is_mobile()) {
        $('html, body').animate({
            scrollTop: $(selector).offset().top + 150
        }, 600);
    }
}

function toggle_small_view_proposal(table, main_data) {
    "use strict";
    $("body").toggleClass('small-table');
    var tablewrap = $('#small-table');
    if (tablewrap.length === 0) { return; }
    var _visible = false;
    if (tablewrap.hasClass('hide')) {
        $('#box-loading').hide();
        tablewrap.removeClass('hide');
        _visible = true;
        $('.toggle-small-view').addClass('hide');
    } else {
        $('#box-loading').show();
        tablewrap.addClass('hide');
        $('.toggle-small-view').removeClass('hide');
    }
    var _table = $(table).DataTable();
    // Show hide hidden columns
    _table.columns(hidden_columns).visible(_visible, false);
    _table.columns.adjust();
    $(main_data).toggleClass('hide');
    $(window).trigger('resize');
}

function delivery_status_mark_as(status, task_id, type) {
    var url = 'fixed_equipment/delivery_status_mark_as/' + status + '/' + task_id + '/' + type;
    var taskModalVisible = $('#task-modal').is(':visible');
    url += '?single_task=' + taskModalVisible;
    $("body").append('<div class="dt-loader"></div>');

    requestGetJSON(url).done(function (response) {
        $("body").find('.dt-loader').remove();
        if (response.success === true || response.success == 'true') {
            
          var av_tasks_tables = ['.table-table_manage_delivery'];
          $.each(av_tasks_tables, function (i, selector) {
            if ($.fn.DataTable.isDataTable(selector)) {
              $(selector).DataTable().ajax.reload(null, false);
            }
          });
          alert_float('success', response.message);
        }
    });
}
</script>