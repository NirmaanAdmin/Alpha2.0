<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
  <div class="content">
    <div class="row">
      <div class="col-md-6">
        <div class="panel_s">
          <div class="panel-body">
            <?php if($pur_request_payment->approval_status == 1){ ?>
              <div class="ribbon info"><span class="fontz9" ><?php echo _l('purchase_not_yet_approve'); ?></span></div>
            <?php }elseif($pur_request_payment->approval_status == 2){ ?>
              <div class="ribbon success"><span><?php echo _l('purchase_approved'); ?></span></div>
            <?php }elseif($pur_request_payment->approval_status == 3){ ?>  
              <div class="ribbon danger"><span><?php echo _l('purchase_reject'); ?></span></div>
            <?php } ?>
            <h4 class="pull-left "><?php echo _l('payment_for').' '; ?><a href="<?php echo admin_url('purchase/view_pur_request/'. $pur_request_payment->pur_request); ?>" target="_blank"><?php echo pur_html_entity_decode(get_pur_rq_code($pur_request_payment->pur_request)); ?></a></h4>
            <div class="clearfix"></div>
            <hr class="hr-panel-heading" />
            <div class="col-md-12">
              <div class="row">
                <div class="col-md-6 col-sm-6">
                  <address>
                    <?php echo format_organization_info(); ?>
                  </address>
                </div>
              </div>
              <div class="col-md-12 text-center">
                <h3 class="text-uppercase"><?php echo _l('payment_receipt'); ?></h3>
              </div>
              <div class="col-md-12 mtop30">
                <div class="row">
                  <div class="col-md-6">
                    <p><?php echo _l('payment_date'); ?> <span class="pull-right bold"><?php echo _d($pur_request_payment->date); ?></span></p>
                    <hr />
                    <p><?php echo _l('payment_view_mode'); ?>
                    <span class="pull-right bold">

                      <?php if(!empty($pur_request_payment->paymentmode)){
                        echo get_payment_mode_name_by_id($pur_request_payment->paymentmode);
                      }
                      ?>
                    </span></p>
                    <?php if(!empty($pur_request_payment->transactionid)) { ?>
                      <hr />
                      <p><?php echo _l('payment_transaction_id'); ?>: <span class="pull-right bold"><?php echo pur_html_entity_decode($pur_request_payment->transactionid); ?></span></p>
                    <?php } ?>
                  </div>
                  <div class="clearfix"></div>
                  <div class="col-md-6">
                    <div class="payment-preview-wrapper">
                      <?php echo _l('payment_total_amount'); ?><br />
                      <?php echo app_format_money($pur_request_payment->amount,$base_currency->name); ?>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php init_tail(); ?>
</body>
</html>