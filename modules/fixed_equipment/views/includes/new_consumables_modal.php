<?php 
$id = '';
$type = '';
$assets_name = '';
$category_id = '';
$manufacturer_id = '';
$asset_location = '';
$model_no = '';
$item_no = '';
$order_number = '';
$unit_price = '';
$date_buy = '';
$quantity = '';
$min_quantity = '';
$for_sell = 0;
$for_rent = 0;
$selling_price = '';
$rental_price = '';
if(isset($consumable)){
	$id = $consumable->id;
	$type = $consumable->type;
	$assets_name = $consumable->assets_name;
	$category_id = $consumable->category_id;
	$manufacturer_id = $consumable->manufacturer_id;
	$asset_location = $consumable->asset_location;
	$model_no = $consumable->model_no;
	$item_no = $consumable->item_no;
	$order_number = $consumable->order_number;
	$unit_price = app_format_money($consumable->unit_price, '');
	$date_buy = $consumable->date_buy;
	$quantity = $consumable->quantity;
	$min_quantity = $consumable->min_quantity;
	$for_sell = $consumable->for_sell;   
	$for_rent = $consumable->for_rent;   
	$selling_price = app_format_money($consumable->selling_price, '');   
	$rental_price = app_format_money($consumable->rental_price, '');   
}

?>
<input type="hidden" value="consumable" name="type">
<input type="hidden" name="id" value="<?php echo fe_htmldecode($id); ?>">
<div class="row">
	<div class="col-md-12">
		<?php echo render_input('assets_name', 'fe_consumables_name', $assets_name) ?>
	</div>
</div>
<div class="row">
	<div class="col-md-12">
		<?php echo render_select('category_id', $categories, array('id','category_name'), 'fe_category_name', $category_id) ?>
	</div>
</div>
<div class="row">
	<div class="col-md-12">
		<?php echo render_select('manufacturer_id', $manufacturers, array('id','name'), 'fe_manufacturer', $manufacturer_id) ?>
	</div>
</div>
<div class="row">
	<div class="col-md-12">
		<?php echo render_select('asset_location', $locations, array('id', 'location_name'), 'fe_locations', $asset_location); ?>
	</div>
</div>
<div class="row">
	<div class="col-md-6">
		<?php echo render_input('model_no', 'fe_model_no', $model_no) ?>
	</div>
	<div class="col-md-6">
		<?php echo render_input('item_no', 'fe_item_no', $item_no) ?>
	</div>
</div>
<div class="row">
	<div class="col-md-12">
		<?php echo render_input('order_number', 'fe_order_number', $order_number) ?>
	</div>
</div>
<div class="row">
	<div class="col-md-6">
		<div class="form-group">
			<label for="gst"><?php echo _l('fe_purchase_cost'); ?></label>            
			<div class="input-group">
				<input type="text" class="form-control" data-type="currency" name="unit_price" value="<?php echo fe_htmldecode($unit_price); ?>">
				<span class="input-group-addon"><?php echo fe_htmldecode($currency_name); ?></span>
			</div>
		</div>
	</div>
	<div class="col-md-6">
		<?php echo render_date_input('date_buy', 'fe_purchase_date', _d($date_buy)) ?>
	</div>
</div>
<div class="row">
	<div class="col-md-6">
		<?php echo render_input('quantity', 'fe_quantity', $quantity, 'number') ?>
	</div>
	<div class="col-md-6">
		<?php echo render_input('min_quantity', 'fe_min_quantity', $min_quantity, 'number') ?>
	</div>
</div>
<div class="row">
	<div class="col-md-6 ptop10">
		<div class="checkbox checkbox-inline checkbox-primary">
			<input type="checkbox" name="for_sell" id="for_sell" value="1" <?php if($for_sell == 1){ echo 'checked'; } ?>>
			<label for="for_sell"><?php echo _l('fe_for_sell'); ?></label>
		</div> 
		<br>
		<br>
	</div>
	<div class="col-md-6 for_sell_fr<?php if($for_sell != 1){ echo ' hide'; } ?>">
		<div class="form-group">
			<label for="selling_price"><?php echo _l('fe_selling_price'); ?></label>            
			<div class="input-group">
				<input data-type="currency" type="text" class="form-control" name="selling_price" value="<?php echo fe_htmldecode($selling_price); ?>">
				<span class="input-group-addon"><?php echo fe_htmldecode($currency_name); ?></span>
			</div>
		</div>
	</div>
</div>
<div class="row">
	<div class="col-md-12" id="ic_pv_file">
		<?php
		if(isset($consumable)){
			$attachments = fe_get_item_file_attachment($consumable->id, 'consumable');
			$file_html = '';
			$type_item = 'consumable';
			if(count($attachments) > 0){
				$file_html .= '<div class="list-file">';
				foreach ($attachments as $f) {
					$href_url = site_url(FIXED_EQUIPMENT_PATH.'consumable/'.$f['rel_id'].'/'.$f['file_name']).'" download';
					if(!empty($f['external'])){
						$href_url = $f['external_link'];
					}
					$file_html .= '<div class="mbot5 row inline-block full-width" data-attachment-id="'. $f['id'].'">
					<div class="col-md-8">
					<a name="preview-ic-btn" onclick="preview_ic_btn(this); return false;" rel_id = "'. $f['rel_id']. '" type_item = "'. $type_item. '" id = "'.$f['id'].'" href="Javascript:void(0);" class="mbot10 btn btn-success pull-left mright5" data-toggle="tooltip" title data-original-title="'. _l('fe_preview_file').'"><i class="fa fa-eye"></i></a>
					<div class="pull-left"><i class="'. get_mime_class($f['filetype']).'"></i></div>
					<a href=" '. $href_url.'" target="_blank" download>'.$f['file_name'].'</a>
					<br />
					<small class="text-muted">'.$f['filetype'].'</small>
					</div>
					<div class="col-md-4 text-right">';
					$file_html .= '<a href="#" class="text-danger" onclick="delete_ic_attachment('. $f['id'].',this); return false;" type_item = "'. $type_item. '"><i class="fa fa-times"></i></a>';
					$file_html .= '</div></div>';
				}
				$file_html .= '</div>';
				echo fe_htmldecode($file_html);
			}
			?>
		<?php } ?>
	</div>
	<div id="ic_file_data"></div>
	<div class="col-md-12">
		<div class="attachments">
			<div class="attachment">
				<div class="mbot15">
					<label for="attachment" class="control-label"><?php echo _l('fe_upload_image'); ?></label>
					<input type="file" extension="<?php echo str_replace('.','',get_option('ticket_attachments_file_extensions')); ?>" filesize="<?php echo file_upload_max_size(); ?>" class="form-control" name="attachments" accept="image/*">
				</div>
			</div>
		</div>
	</div>
</div>