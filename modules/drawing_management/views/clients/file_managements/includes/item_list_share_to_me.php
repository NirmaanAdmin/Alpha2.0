	<table class="table table-items scroll-responsive no-mtop">
		<thead class="bg-light-gray">
			<tr>
				<th scope="col"><?php echo _l('dmg_name'); ?></th>
				<th scope="col"><?php echo _l('dms_date'); ?></th>
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
				$a1 = '<a href="'.site_url('drawing_management/drawing_management_client?share_to_me=1&id='.$value['id']).'" >';
				$a2 = '</a>';
				?>
				<tr>
					<td>
						<?php echo drawing_htmldecode('<div class="display-flex">'.$item_icon.$a1.'<strong class="fs-14 mleft10">'.$value['name'].'</strong>'.$a2.'</div>'); ?>											
					</td>
					<td>
						<?php echo drawing_htmldecode($a1._dt($value['dateadded']).$a2); ?>											
					</td>
					<td>

						<?php 

						$editor_permit = drawing_check_share_permission($value['id'], 'editor', 'customer');
						$viewer_permit = drawing_check_share_permission($value['id'], 'viewer', 'customer');
						if($editor_permit || $viewer_permit){ ?>
							<div class="dropdown pull-right">
								<button class="btn btn-tool pull-right dropdown-toggle" role="button" id="dropdown_menu_<?php echo drawing_htmldecode($value['id']); ?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
									<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-more-horizontal"><circle cx="12" cy="12" r="1"/><circle cx="19" cy="12" r="1"/><circle cx="5" cy="12" r="1"/></svg>
								</button>	

								<ul class="dropdown-menu" aria-labelledby="dropdown_menu_<?php echo drawing_htmldecode($value['id']); ?>">
									<?php 
									$download = '';
									if($value['filetype'] == 'folder'){ 
										$download = '<a href="'.site_url('drawing_management/drawing_management_client/download_folder/'.$value['id']).'" >'._l('dmg_dowload').'</a>';
										if($editor_permit){ ?>
											<li class="no-padding">
												<a href="#" data-name="<?php echo drawing_htmldecode($value['name']); ?>" onclick="edit_folder(this, '<?php echo drawing_htmldecode($value['id']); ?>')"><?php echo _l('dmg_edit') ?></a>											
											</li>
											<?php 
										} 
									}
									else{ 
										$download = '<a href="'.site_url('modules/drawing_management/uploads/files/'.$value['parent_id'].'/'.$value['name']).'" download>'._l('dmg_dowload').'</a>';
										if(!drawing_check_file_locked($value['id']) && $editor_permit){ ?>
											<li class="no-padding">
												<a href="<?php echo site_url('drawing_management/drawing_management_client?share_to_me=1&id='.$value['id'].'&edit=1') ?>" data-name="<?php echo drawing_htmldecode($value['name']); ?>"><?php echo _l('dmg_edit_metadata') ?></a>											
											</li>
										<?php } 
									}
									if($editor_permit){ ?>
										<li class="no-padding">
											<a href="#" data-type="<?php echo drawing_htmldecode($value['filetype']); ?>" onclick="share_document(this, '<?php echo drawing_htmldecode($value['id']); ?>')"><?php echo _l('dmg_share') ?></a>
										</li>
										<li class="no-padding">
											<a href="#" onclick="duplicate_item('<?php echo drawing_htmldecode($value['id']); ?>')"><?php echo _l('dmg_duplicate') ?></a>
										</li>
									<?php } 
									if($editor_permit || $viewer_permit){ ?>
										<li class="no-padding">
											<?php echo drawing_htmldecode($download); ?>
										</li>
									<?php } ?>
									
								</ul>
							</div>
						<?php } ?>

					</td>
				</tr>
			<?php } ?>
		</tbody>
	</table>