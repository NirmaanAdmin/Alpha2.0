<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
 <div class="content">
    <div class="row">
  
    
    <div class="horizontal-scrollable-tabs  col-md-3">
      
           <ul class="nav navbar-pills navbar-pills-flat nav-tabs nav-stacked customer-tabs" role="tablist">
            <?php
            $i = 0;
            foreach($tab as $groups){
              ?>
              <li <?php if($i == 0){echo " class='active'"; } ?>>
              <a href="<?php echo admin_url('forms/progress_report_setting?group='.$groups); ?>" data-group="<?php echo pur_html_entity_decode($groups); ?>">
               <?php echo _l($groups); ?></a>
              </li>
              <?php $i++; } ?>
            </ul>
       
      </div>

 
  <div class="col-md-9">
    <div class="panel_s">
     <div class="panel-body">

        <?php $this->load->view($tabs['view']); ?>
        
     </div>
  </div>
</div>
<div class="clearfix"></div>
</div>
<?php echo form_close(); ?>
<div class="btn-bottom-pusher"></div>
</div>
</div>
<div id="new_version"></div>
<?php init_tail(); ?>
</body>
</html>
<script type="text/javascript">
  function new_progress_report_type() {
    "use strict";
    $('.edit-title').addClass('hide');
    $('.add-title').removeClass('hide');
    $('#progress_report_type_modal').modal('show');
    $('#additional_progress_report_type').html('');
  }

  function edit_progress_report_type(invoker,id) {
    "use strict";
    $('.edit-title').removeClass('hide');
    $('.add-title').addClass('hide');
    $('#additional_progress_report_type').html('');
    $('#additional_progress_report_type').append(hidden_input('id',id));
    $('#progress_report_type_modal input[name="name"]').val($(invoker).data('name'));
    $('#progress_report_type_modal').modal('show');
  }

  function new_progress_report_sub_type() {
    "use strict";
    $('.edit-title').addClass('hide');
    $('.add-title').removeClass('hide');
    $('#progress_report_sub_type_modal').modal('show');
    $('#additional_progress_report_sub_type').html('');
  }

  function edit_progress_report_sub_type(invoker,id) {
    "use strict";
    $('.edit-title').removeClass('hide');
    $('.add-title').addClass('hide');
    $('#additional_progress_report_sub_type').html('');
    $('#additional_progress_report_sub_type').append(hidden_input('id',id));
    $('#progress_report_sub_type_modal input[name="name"]').val($(invoker).data('name'));
    $('#progress_report_sub_type_modal').modal('show');
  }

  function new_progress_report_machinary() {
    "use strict";
    $('.edit-title').addClass('hide');
    $('.add-title').removeClass('hide');
    $('#progress_report_machinary_modal').modal('show');
    $('#additional_progress_report_machinary').html('');
  }

  function edit_progress_report_machinary(invoker,id) {
    "use strict";
    $('.edit-title').removeClass('hide');
    $('.add-title').addClass('hide');
    $('#additional_progress_report_machinary').html('');
    $('#additional_progress_report_machinary').append(hidden_input('id',id));
    $('#progress_report_machinary_modal input[name="name"]').val($(invoker).data('name'));
    $('#progress_report_machinary_modal').modal('show');
  }
</script>