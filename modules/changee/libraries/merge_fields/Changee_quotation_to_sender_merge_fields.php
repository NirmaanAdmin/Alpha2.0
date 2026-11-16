<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Changee_quotation_to_sender_merge_fields extends App_merge_fields
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
                    'changee-quotation-to-sender',
                ],
            ],
            [
                'name'      => 'Contact lastname',
                'key'       => '{contact_lastname}',
                'available' => [
                    
                ],
                'templates' => [
                    'changee-quotation-to-sender',
                ],
            ],
            [
                'name'      => 'Status',
                'key'       => '{status_name}',
                'available' => [
                    
                ],
                'templates' => [
                    'changee-quotation-to-sender',
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
                'name'      => 'Estimate id',
                'key'       => '{estimate_id}',
                'available' => [
                    
                ],
                'templates' => [
                    'changee-quotation-to-sender',
                ],
            ],
            [
                'name'      => 'Project name',
                'key'       => '{project_name}',
                'available' => [
                    
                ],
                'templates' => [
                    'changee-quotation-to-sender',
                ],
            ],
            [
                'name'      => 'Quotation link',
                'key'       => '{quotation_link}',
                'available' => [
                    
                ],
                'templates' => [
                    'changee-quotation-to-sender',
                ],
            ],
            [
                'name'      => 'Quotation title',
                'key'       => '{quotation_title}',
                'available' => [
                    
                ],
                'templates' => [
                    'changee-quotation-to-sender',
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
        $po_id = $data->pur_estimate_id;
        $this->ci->load->model('changee/changee_model');


        $fields = [];

        $this->ci->db->where('id', $po_id);

        $po = $this->ci->db->get(db_prefix() . 'co_estimates')->row();


        if (!$po) {
            return $fields;
        }

        $fields['{contact_firstname}'] =  $data->contact_firstname;
        $fields['{contact_lastname}'] =  $data->contact_lastname;
        $fields['{estimate_id}'] =  changee_format_pur_estimate_number($po_id);
        $fields['{project_name}'] =  get_project_name_by_id($po->project);
        $fields['{status_name}'] =  ($po->status == 2) ? 'approved' : 'rejected';
        $fields['{status_extra}'] =  ($po->status == 2) ? 'approval' : 'rejection';
        $fields['{quotation_title}'] = site_url('changee/vendors_portal/add_update_quotation/' . $po->id.'/1');
        $fields['{quotation_link}'] = site_url('changee/vendors_portal/add_update_quotation/' . $po->id.'/1');

        return $fields;
    }
}
