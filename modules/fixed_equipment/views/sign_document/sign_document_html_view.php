<table>
   <tr>

      <td width="30%">
      <?php echo pdf_logo_url() ?>
         <h1>#<?php echo fe_htmldecode($sign_documents->reference); ?></h1>
         <?php echo format_organization_info() ?>
         
      </td>

      <td width="70%" class="text-right">
         <?php
         if (is_numeric($sign_documents->check_to_staff)) { ?>
            <h2>
               <?php echo get_staff_full_name($sign_documents->check_to_staff); ?>
            </h2>
            <?php
            $staff_data = $this->staff_model->get($sign_documents->check_to_staff);
            if ($staff_data) {
               echo fe_htmldecode($staff_data->email . '<h4>' . $staff_data->phonenumber . '</h4>');
            }
            ?>
         <?php } ?>
      </td>
   </tr>
   
</table>

<div></div>

<table cellpadding="10">
   <thead>
      <tr class="dark-background">
         <th width="30%" align="left">
            <h4 class="text-white"><?php echo _l('fe_item') ?></h4>
         </th>
         <th width="10%" align="left">
            <h4 class="text-white"><?php echo _l('fe_asset_tag') ?></h4>
         </th>
         <th width="20%" align="left">
            <h4 class="text-white"><?php echo _l('from') ?></h4>
         </th>
         <th width="20%" align="left">
            <h4 class="text-white"><?php echo _l('to') ?></h4>
         </th>
         <th width="20%" align="right">
            <h4 class="text-white"><?php echo _l('fe_check_in_out_date') ?></h4>
         </th>
      </tr>
   </thead>
   <tbody>
   <tbody>
      <?php
      $item_list_id = explode(',', $sign_documents->checkin_out_id);
      foreach ($item_list_id as $key => $item_id) {
         $data_check_in_out = $this->fixed_equipment_model->get_checkin_out_data($item_id);
         $get_from_location = $this->fixed_equipment_model->get_from_location($item_id);
         $get_to_location = $this->fixed_equipment_model->get_to_location($item_id);
         $from_location = '';

         if (!empty($get_from_location)) {
            if (!empty($get_from_location->location_name)) {
               $from_location = $get_from_location->location_name;
            } elseif (!empty($get_from_location->staff_name)) {
               $from_location = $get_from_location->staff_name;
            } elseif (!empty($get_from_location->project_name)) {
               $from_location = $get_from_location->project_name;
            } elseif (!empty($get_from_location->asset_name)) {
               $from_location = $get_from_location->asset_name;
            }
         }
         // Ensure $get_to_location is not NULL
         $to_location = !empty($get_to_location) ? $get_to_location->location_name : '';
         if ($data_check_in_out) {
            $asset_tag = '';
            $asset_id = $data_check_in_out->item_id;
            if ($data_check_in_out->item_type == 'license') {
               $data_seats = $this->fixed_equipment_model->get_seats($data_check_in_out->item_id);
               if ($data_seats) {
                  $asset_id = $data_seats->license_id;
               }
            }
            $data_asset = $this->fixed_equipment_model->get_assets($asset_id);
            if ($data_asset) {
               $asset_tag = $data_asset->series;
            }
      ?>
            <tr>
               <td width="30%" align="left"><strong><?php echo fe_htmldecode($data_check_in_out->asset_name); ?></strong></td>
               <td width="10%" align="left"><strong><?php echo fe_htmldecode($asset_tag); ?></strong></td>
               <td width="20%" align="left"><strong><?php echo $from_location; ?></strong></td>
               <td width="20%" align="left"><strong><?php echo $to_location; ?></strong></td>
               <td width="20%" align="right"><?php echo _dt($data_check_in_out->date_creator); ?></td>
            </tr>
      <?php }
      } ?>
   </tbody>
   </tbody>
</table>
<div></div>
<div></div>
<table cellpadding="20">
   <tr>
      <?php foreach ($signers as $key => $value) {
         $full_name = '';
         if ($value['firstname'] == null) {
            if (is_numeric($value['staff_id'])) {
               $full_name = get_staff_full_name($value['staff_id']);
            }
         } else {
            $full_name = $value['firstname'] . ' ' . $value['lastname'];
         }
      ?>
         <td align="center">
            <div>
               <?php echo ($key == 0 ? _l('fe_creator_signature') : _l('fe_owner_signature')); ?>
            </div>
            <?php if ($value['date_of_signing'] != null) { ?>
               <div></div>
               <img src="<?php echo fe_get_sign_image($value['id'], 'sign_document') ?>" alt="">
            <?php } else { ?>
               <div></div>
               <div></div>
               <div></div>
            <?php } ?>

            <div>
               <?php echo fe_htmldecode($full_name); ?>
            </div>
         </td>
      <?php  } ?>
   </tr>
</table>