<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Changee_order_to_sender_merge_fields extends App_merge_fields
{
    public function build()
    {
        return [
            [
                'name'      => 'Contact firstname',
                'key'       => '{contact_firstname}',
                'available' => [
                    
                ],
                'templates' => [
                    'changee-order-to-sender',
                ],
            ],
            [
                'name'      => 'Contact lastname',
                'key'       => '{contact_lastname}',
                'available' => [
                    
                ],
                'templates' => [
                    'changee-order-to-sender',
                ],
            ],
            [
                'name'      => 'Status',
                'key'       => '{status_name}',
                'available' => [
                    
                ],
                'templates' => [
                    'changee-order-to-sender',
                ],
            ],
            [
                'name'      => 'Status extra',
                'key'       => '{status_extra}',
                'available' => [
                    
                ],
                'templates' => [
                    'changee-request-to-sender',
                ],
            ],
            [
                'name'      => 'Vendor name',
                'key'       => '{vendor_name}',
                'available' => [
                    
                ],
                'templates' => [
                    'changee-order-to-sender',
                ],
            ],
            [
                'name'      => 'Po id',
                'key'       => '{po_id}',
                'available' => [
                    
                ],
                'templates' => [
                    'changee-order-to-sender',
                ],
            ],
            [
                'name'      => 'PO name',
                'key'       => '{po_name}',
                'available' => [
                    
                ],
                'templates' => [
                    'changee-order-to-sender',
                ],
            ],
            [
                'name'      => 'Project name',
                'key'       => '{project_name}',
                'available' => [
                    
                ],
                'templates' => [
                    'changee-order-to-sender',
                ],
            ],
            [
                'name'      => 'Changee order link',
                'key'       => '{changee_order_link}',
                'available' => [
                    
                ],
                'templates' => [
                    'changee-order-to-sender',
                ],
            ],
            [
                'name'      => 'Changee order title',
                'key'       => '{changee_order_title}',
                'available' => [
                    
                ],
                'templates' => [
                    'changee-order-to-sender',
                ],
            ],
        ];
    }

    /**
     * Merge field for appointments
     * @param  mixed $teampassword 
     * @return array
     */
    public function format($data)
    {
        $po_id = $data->po_id;
        $this->ci->load->model('changee/changee_model');


        $fields = [];

        $this->ci->db->where('id', $po_id);

        $po = $this->ci->db->get(db_prefix() . 'co_orders')->row();


        if (!$po) {
            return $fields;
        }

        $fields['{contact_firstname}'] =  $data->contact_firstname;
        $fields['{contact_lastname}'] =  $data->contact_lastname;
        $fields['{po_id}'] =  $po->pur_order_number;
        $fields['{po_name}'] =  $po->pur_order_name;
        $fields['{project_name}'] =  get_project_name_by_id($po->project);
        $fields['{status_name}'] =  ($po->approve_status == 2) ? 'approved' : 'rejected';
        $fields['{status_extra}'] =  ($po->approve_status == 2) ? 'approval' : 'rejection';
        $fields['{vendor_name}'] =  $data->vendor_name;
        $fields['{changee_order_title}'] = site_url('changee/vendors_portal/pur_order/' . $po->id.'/'.$po->hash);
        $fields['{changee_order_link}'] = site_url('changee/vendors_portal/pur_order/' . $po->id.'/'.$po->hash);

        return $fields;
    }
}
