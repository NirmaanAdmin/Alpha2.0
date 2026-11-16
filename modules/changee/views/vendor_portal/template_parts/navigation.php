<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<nav class="navbar navbar-default header">
   <div class="container">
      <!-- Brand and toggle get grouped for better mobile display -->
      <div class="navbar-header">
         <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#theme-navbar-collapse" aria-expanded="false">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
         </button>
         <?php get_company_logo('changee/vendors_portal','navbar-brand logo'); ?>
      </div>
      <!-- Collect the nav links, forms, and other content for toggling -->
      <div class="collapse navbar-collapse" id="theme-navbar-collapse">
         <ul class="nav navbar-nav navbar-right">
          <?php  if (changee_is_vendor_logged_in()) { ?>
               <li class="customers-nav-item-items">
                  <a href="<?php echo site_url('changee/vendors_portal/items'); ?>">
                     <?php
                     
                     echo _l('items');
                    ?>
                  </a>
               </li>
               
               <li class="customers-nav-item-items">
                  <a href="<?php echo site_url('changee/vendors_portal/changee_request'); ?>">
                     <?php
                     
                     echo _l('changee_request');
                    ?>
                  </a>
               </li>
               <li class="customers-nav-item-items">
                  <a href="<?php echo site_url('changee/vendors_portal/quotations'); ?>">
                     <?php
                     
                     echo _l('quotations');
                    ?>
                  </a>
               </li>
                <li class="customers-nav-item-items">
                  <a href="<?php echo site_url('changee/vendors_portal/changee_order'); ?>">
                     <?php
                     
                     echo _l('changee_order');
                    ?>
                  </a>
               </li>
               <li class="customers-nav-item-items">
                  <a href="<?php echo site_url('changee/vendors_portal/contracts'); ?>">
                     <?php
                     
                     echo _l('contracts');
                    ?>
                  </a>
               </li>
               <li class="customers-nav-item-items">
                  <a href="<?php echo site_url('changee/vendors_portal/invoices'); ?>">
                     <?php
                     
                     echo _l('pur_invoices');
                    ?>
                  </a>
               </li>

               <li class="customers-nav-item-items">
                  <a href="<?php echo site_url('changee/vendors_portal/order_returns'); ?>">
                     <?php
                     
                     echo _l('pur_order_returns');
                    ?>
                  </a>
               </li> 
        <?php } ?>
        
          
            <?php if(changee_is_vendor_logged_in()) { ?>
               <li class="dropdown customers-nav-item-profile">
                  <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                     <img src="<?php echo changee_vendor_contact_profile_image_url($contact->id,'thumb'); ?>" data-toggle="tooltip" data-title="<?php echo html_escape($contact->firstname . ' ' .$contact->lastname); ?>" data-placement="bottom" class="client-profile-image-small mright5">
                     <span class="caret"></span>
                     </a>
                     <ul class="dropdown-menu animated fadeIn">
                        <li class="customers-nav-item-edit-profile">
                           <a href="<?php echo site_url('changee/vendors_portal/profile'); ?>">
                              <?php echo _l('clients_nav_profile'); ?>
                           </a>
                        </li>
                        <?php if($contact->is_primary == 1){ ?>
                           <li class="customers-nav-item-company-info">
                              <a href="<?php echo site_url('changee/vendors_portal/company'); ?>">
                                 <?php echo _l('client_company_info'); ?>
                              </a>
                           </li>
                        <?php } ?>
                    
                        
                     <li class="customers-nav-item-languages">
                        <a href="<?php echo site_url('changee/vendors_portal/setting_language'); ?>">
                             <?php echo _l('language'); ?>
                        </a>
                     </li>

                     <li class="customers-nav-item-logout">
                        <a href="<?php echo site_url('changee/authentication_vendor/logout'); ?>">
                           <?php echo _l('clients_nav_logout'); ?>
                        </a>
                     </li>
                  </ul>
               </li>
            <?php } ?>
            
         </ul>
      </div>
      <!-- /.navbar-collapse -->
   </div>
   <!-- /.container-fluid -->
</nav>
