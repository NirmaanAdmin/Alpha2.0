<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="form-group mbot25  select-placeholder">
 <select name="item_select" class="selectpicker no-margin<?php if($ajaxItems == true){echo ' ajax-search';} ?>" data-width="100%"  id="item_select" data-none-selected-text="<?php echo _l('select_item'); ?>" data-live-search="true">
   <option value=""></option>
   <?php foreach($items as $group_id => $_items){ ?>
      <option value="<?php echo fe_htmldecode($_items['id']); ?>"><?php echo fe_htmldecode($_items['name']); ?></option>
   <?php } ?>
</select>
</div>
