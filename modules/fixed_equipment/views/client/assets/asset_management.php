<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php hooks()->do_action('head_element_client'); ?>
<h4 class="tw-mt-0 tw-mb-3 tw-font-semibold tw-text-lg tw-text-neutral-700 section-heading section-heading-projects">
    <?php echo _l('fe_assets'); ?>
</h4>
<!-- assets_name
quantity
order_number
datecreator
type
assets -->
<div class="panel_s">
    <div class="panel-body">
        <table class="table dt-table table-projects" data-order-col="2" data-order-type="desc">
            <thead>
                <tr>
                    <th><?php echo _l('fe_asset_name'); ?></th>
                    <th><?php echo _l('fe_quantity'); ?></th>
                    <th><?php echo _l('fe_order_number'); ?></th>
                    <th><?php echo _l('fe_order_date'); ?></th>
                    <th><?php echo _l('fe_options'); ?></th>
                    <th><?php echo _l('fe_issue_closes'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($asset_list as $key => $asset) { ?>

                    <?php if(preg_match('/^model_/', $key)){ ?>
                        <?php 
                        $key_explode = explode('_', $key);
                        $model_id = isset($key_explode[1]) ? $key_explode[1] : 0;
                        $cart_id = isset($key_explode[2]) ? $key_explode[2] : 0;
                        $issue_open = $this->fixed_equipment_model->get_issue(false, 'cart_id = '.$asset['cart_id'].' AND status != "closed" AND model_id = '.$model_id);
                        $order_issues_closed = $this->fixed_equipment_model->get_issue(false, 'cart_id = '.$asset['cart_id'].' AND status = "closed" AND model_id = '.$model_id);

                        ?>
                    <?php }else{ ?>
                        <?php 
                        $key_explode = explode('_', $key);
                        $asset_id = isset($key_explode[0]) ? $key_explode[0] : 0;
                        $issue_open = $this->fixed_equipment_model->get_issue(false, 'cart_id = '.$asset['cart_id'].' AND status != "closed" AND asset_id = '.$asset_id);
                        $order_issues_closed = $this->fixed_equipment_model->get_issue(false, 'cart_id = '.$asset['cart_id'].' AND status = "closed" AND asset_id = '.$asset_id);

                        ?>
                    <?php } ?>

                    <?php 
                    
                     ?>
                <tr>
                    <td><?php echo fe_htmldecode($asset['assets_name']); ?>
                    <?php if($asset['type'] == 'booking'){ ?>
                        &nbsp;&nbsp;&nbsp;&nbsp;<span class="badge badge-primary"><?php echo _l('fe_booking') ?></span>
                    <?php } ?>
                </td>
                    <td><?php echo fe_htmldecode($asset['quantity']); ?></td>
                    <td>
                        <a href="<?php echo site_url('fixed_equipment/fixed_equipment_client/view_order_detail/' . $asset['order_number']); ?>"><?php echo fe_htmldecode($asset['order_number']); ?></a>
                    </td>
                    <td><?php echo _d($asset['datecreator']); ?></td>
                    <?php 
                    $option = '';
                    if(is_numeric($asset['datecreator']) || $asset['datecreator'] == 0){
                        if($asset['invoice_id'] == 0 && ($asset['status'] == 'complete' || $asset['status'] == 'confirm')){

                            $option .= '<a href="'. site_url('service_management/service_management_client/create_invoice_from_order/'.$asset['id']).'" class="btn btn-success text-right mright5">'. _l('sm_create_invoice') .'</a>';

                        }elseif($asset['invoice_id'] != 0){
                            $option .= '<a href="'.site_url('invoice/'.$asset['invoice_id'].'/'.sm_get_invoice_hash($asset['invoice_id'])).'" class="btn btn-primary btn-icon" data-original-title="View Invoice" data-toggle="tooltip" data-placement="top">
                            <i class="fa fa-eye"></i>
                            </a>';
                        }
                    }

                    $_data = $option;
                    ?>
                    <td>
                        <?php if(count($issue_open) > 0){ ?>
                            <a href="<?php echo site_url('fixed_equipment/fixed_equipment_client/issue_detail/'.$issue_open[0]['id']); ?>" class="btn btn-warning"><?php echo _l('fe_view_issue'); ?></a>
                        <?php }else{ ?>

                            <?php if(preg_match('/^model_/', $key)){ ?>
                                <?php 
                                $key_explode = explode('_', $key);
                                $model_id = isset($key_explode[1]) ? $key_explode[1] : 0;
                                $cart_id = isset($key_explode[2]) ? $key_explode[2] : 0;
                                ?>
                                <a href="<?php echo site_url('fixed_equipment/fixed_equipment_client/add_edit_issue/0/'.$asset['cart_id'].'/'.$model_id); ?>" class="btn btn-danger"><?php echo _l('fe_new_issue'); ?></a>
                            <?php }else{ ?>
                                <?php 
                                $key_explode = explode('_', $key);
                                $asset_id = isset($key_explode[0]) ? $key_explode[0] : 0;
                                ?>
                                <a href="<?php echo site_url('fixed_equipment/fixed_equipment_client/add_edit_issue/0/'.$asset['cart_id'].'/0/'.$asset_id); ?>" class="btn btn-danger"><?php echo _l('fe_new_issue'); ?></a>

                            <?php } ?>

                        <?php } ?>


                    </td>
                    <td>
                         <?php if(count($order_issues_closed) > 0){ ?>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <select name="view_issue_closes" id="view_issue_closes" class="selectpicker" data-live-search="true" data-width="100%" data-none-selected-text="<?php echo _l('fe_The_issue_is_closed'); ?>"  >
                                        <option value=""></option>
                                        <?php foreach($order_issues_closed as $issues_closed) { ?>
                                            <option value="<?php echo site_url('fixed_equipment/fixed_equipment_client/issue_detail/'.$issues_closed['id']) ; ?>"><?php echo html_entity_decode($issues_closed['code'].' '.$issues_closed['ticket_subject']); ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                        <?php } ?>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>
<?php hooks()->do_action('client_pt_footer_js'); ?>
<script type="text/javascript">
    $("body").on('change', 'select[name="view_issue_closes"]', function () {
        var itemid = $(this).selectpicker('val');
        if (itemid != '') {
            window.location.href = $(this).val()
            
        }
    });
</script>




