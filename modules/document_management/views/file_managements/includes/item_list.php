	<table class="table table-items scroll-responsive no-mtop">
		<thead class="bg-light-gray">
			<tr>
				<th scope="col"><input type="checkbox" id="mass_select_all" data-to-table="checkout_managements"></th>
				<th scope="col"><?php echo _l('dmg_name'); ?></th>
				<th scope="col"><?php echo _l('dmg_date'); ?></th>
				<th scope="col"><?php echo _l('dmg_option'); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php 
			foreach ($child_items as $key => $value) { 
				$item_icon = '';
				if($value['filetype'] == 'folder'){
					$item_icon = '<i class="fa fa-folder text-yellow fs-19"></i> ';
				}
				else{
					$item_icon = '<i class="fa fa-file text-primary fs-14"></i> ';
				}
				$a1 = '<a href="'.admin_url('document_management?id='.$value['id']).'" >';
				$a2 = '</a>';
				?>
				<tr>
					<td>
						<input type="checkbox" class="individual" class="w-100" data-id="<?php echo htmldecode($value['id']); ?>" onchange="checked_add(this); return false;"/>
					</td>
					<td>
						<?php echo htmldecode('<div class="display-flex">'.$item_icon.$a1.'<strong class="fs-14 mleft10">'.$value['name'].'</strong>'.$a2.'</div>'); ?>											
					</td>
					<td>
						<?php echo htmldecode($a1._dt($value['dateadded']).$a2); ?>											
					</td>
					<td>
						<div class="dropdown pull-right">
							<button class="btn btn-tool pull-right dropdown-toggle" role="button" id="dropdown_menu_<?php echo htmldecode($value['id']); ?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
								<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-more-horizontal"><circle cx="12" cy="12" r="1"/><circle cx="19" cy="12" r="1"/><circle cx="5" cy="12" r="1"/></svg>
							</button>	
							<ul class="dropdown-menu" aria-labelledby="dropdown_menu_<?php echo htmldecode($value['id']); ?>">
								<?php 
								$download = '';
								if($value['filetype'] == 'folder'){ 
									$download = '<a href="'.admin_url('document_management/download_folder/'.$value['id']).'" >'._l('dmg_dowload').'</a>';
									?>
									<li class="no-padding">
										<a href="#" data-name="<?php echo htmldecode($value['name']); ?>" onclick="edit_folder(this, '<?php echo htmldecode($value['id']); ?>')"><?php echo _l('dmg_edit') ?></a>											
									</li>
								<?php }
								else{ 
									$download = '<a href="'.site_url('modules/document_management/uploads/files/'.$parent_id.'/'.$value['name']).'" download>'._l('dmg_dowload').'</a>';
									?>
									<?php
									if(!check_file_locked($value['id'])){ ?>
										<li class="no-padding">
											<a href="<?php echo admin_url('document_management?id='.$value['id'].'&edit=1') ?>" data-name="<?php echo htmldecode($value['name']); ?>"><?php echo _l('dmg_edit_metadata') ?></a>											
										</li>
									<?php } 
								}
								?>
								<li class="no-padding">
									<a href="#" data-type="<?php echo htmldecode($value['filetype']); ?>" onclick="share_document(this, '<?php echo htmldecode($value['id']); ?>')"><?php echo _l('dmg_share') ?></a>
								</li>
								<li class="no-padding">
									<a href="#" onclick="duplicate_item('<?php echo htmldecode($value['id']); ?>')"><?php echo _l('dmg_duplicate') ?></a>
								</li>
								<li class="no-padding">
									<a href="#" onclick="move_item('<?php echo htmldecode($value['id']); ?>')"><?php echo _l('dmg_move') ?></a>
								</li>
								<li class="no-padding">
									<?php echo htmldecode($download); ?>
								</li>
								<li class="no-padding">
									<a class="_swaldelete" href="<?php echo admin_url('document_management/delete_section/'.$value['id'].'/'.$parent_id) ?>" ><?php echo _l('dmg_delete') ?></a>
								</li>
							</ul>
						</div>
					</td>
				</tr>
			<?php } ?>
		</tbody>
	</table>