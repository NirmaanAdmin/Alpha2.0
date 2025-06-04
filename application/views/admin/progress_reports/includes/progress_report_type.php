<?php defined('BASEPATH') or exit('No direct script access allowed');?>
<div>
<div class="_buttons">
    <a href="#" onclick="new_progress_report_type(); return false;" class="btn btn-info pull-left display-block">
        <?php echo _l('new'); ?>
    </a>
</div>
<div class="clearfix"></div>
<hr class="hr-panel-heading" />
<div class="clearfix"></div>
<table class="table dt-table">
 <thead>
    <th><?php echo _l('id'); ?></th>
    <th><?php echo _l('name'); ?></th>
    <th><?php echo _l('options'); ?></th>
 </thead>
 <tbody>
  <?php foreach($progress_report_type as $key => $vc){ ?>
    <tr>
      <td><?php echo $key + 1; ?></td>
      <td><?php echo pur_html_entity_decode($vc['name']); ?></td>
      <td>
        <a href="#" onclick="edit_progress_report_type(this,<?php echo pur_html_entity_decode($vc['id']); ?>); return false" data-name="<?php echo pur_html_entity_decode($vc['name']); ?>" class="btn btn-default btn-icon"><i class="fa fa-pencil-square"></i></a>

          <a href="<?php echo admin_url('forms/delete_progress_report_type/' . $vc['id']); ?>" class="btn btn-danger btn-icon _delete"><i class="fa fa-remove"></i></a>
      </td>
    </tr>
  <?php } ?>
 </tbody>
</table>
<div class="modal fade" id="progress_report_type_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <?php echo form_open(admin_url('forms/progress_report_type')); ?>
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">
                    <span class="edit-title"><?php echo _l('edit_progress_report_type'); ?></span>
                    <span class="add-title"><?php echo _l('new_progress_report_type'); ?></span>
                </h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                     <div id="additional_progress_report_type"></div>
                     <div class="form">
                        <?php echo render_input('name', 'name'); ?>
                    </div>
                    </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                    <button type="submit" class="btn btn-info"><?php echo _l('submit'); ?></button>
                </div>
            </div><!-- /.modal-content -->
            <?php echo form_close(); ?>
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->
</div>
</body>
</html>
