	<table class="table table-items scroll-responsive no-mtop">
		<thead class="bg-light-gray">
			<tr>
				<th scope="col"><?php echo _l('dmg_name'); ?></th>
				<th scope="col"><?php echo _l('dmg_date'); ?></th>
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
				$a1 = '<a href="'.admin_url('document_management/detail_approve/'.$value['hash']).'" >';
				$a2 = '</a>';
				?>
				<tr>
					<td>
						<?php echo htmldecode('<div class="display-flex">'.$item_icon.$a1.'<strong class="fs-14 mleft10">'.$value['name'].'</strong>'.$a2.'</div>'); ?>											
					</td>
					<td>
						<?php echo htmldecode($a1._dt($value['dateadded']).$a2); ?>											
					</td>
				</tr>
			<?php } ?>
		</tbody>
	</table>