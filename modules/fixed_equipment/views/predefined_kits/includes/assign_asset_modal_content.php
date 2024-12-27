<?php
$_assign_data = [];
if(isset($assign_data) && $assign_data != ''){
    $_assign_data = json_decode($assign_data);
}
$_model_lists = $this->fixed_equipment_model->get_model_predefined_kits($id);
if ($_model_lists) { ?>

    <div class="row">
        <div class="col-md-12">
            <h4><?php echo _l('fe_assign_asset_by_model'); ?>:</h4>
            <hr>
        </div>
        <?php
        foreach ($_model_lists as $model) {
            $model_id = $model['id'];
            $quantity = $model['quantity'];
            $asset_list = $this->fixed_equipment_model->list_asset_checkout_predefined_kit_by_model($model_id, $quantity); 
            ?>
            <div class="col-md-12">
            <div class="col-md-12 panel ptop15">

                <?php
                $selected = '';
                if(isset($_assign_data->{$model_id})){
                    $selected = $_assign_data->{$model_id};
                }
                echo render_select('assign_data[' . $model_id . '][]', $asset_list->list_asset, array('id', array('series', 'assets_name')), $model['model_name'].' <span class="text-success">('._l('fe_please_choose_items', $quantity).')</span>', $selected, ['multiple' => true]);
                if ($asset_list->msg != '') { ?>
                    <div class="alert alert-danger">
                        <?php
                        echo fe_htmldecode($asset_list->msg);
                        ?>
                    </div>
                <?php } ?>
            </div>
            </div>
        <?php
        } ?>
    </div>
<?php } ?>