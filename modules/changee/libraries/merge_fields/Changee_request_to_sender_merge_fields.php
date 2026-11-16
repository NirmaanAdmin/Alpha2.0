<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Changee_request_to_sender_merge_fields extends App_merge_fields
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
                    'changee-request-to-sender',
                ],
            ],
            [
                'name'      => 'Contact lastname',
                'key'       => '{contact_lastname}',
                'available' => [
                    
                ],
                'templates' => [
                    'changee-request-to-sender',
                ],
            ],
            [
                'name'      => 'Status',
                'key'       => '{status_name}',
                'available' => [
                    
                ],
                'templates' => [
                    'changee-request-to-sender',
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
                'name'      => 'Changee id',
                'key'       => '{changee_id}',
                'available' => [
                    
                ],
                'templates' => [
                    'changee-request-to-sender',
                ],
            ],
            [
                'name'      => 'Changee name',
                'key'       => '{changee_name}',
                'available' => [
                    
                ],
                'templates' => [
                    'changee-request-to-sender',
                ],
            ],
            [
                'name'      => 'Project name',
                'key'       => '{project_name}',
                'available' => [
                    
                ],
                'templates' => [
                    'changee-request-to-sender',
                ],
            ],
            [
                'name'      => 'Changee request link',
                'key'       => '{changee_request_link}',
                'available' => [
                    
                ],
                'templates' => [
                    'changee-request-to-sender',
                ],
            ],
            [
                'name'      => 'Changee request title',
                'key'       => '{changee_request_title}',
                'available' => [
                    
                ],
                'templates' => [
                    'changee-request-to-sender',
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
        $po_id = $data->co_request_id;
        $this->ci->load->model('changee/changee_model');


        $fields = [];

        $this->ci->db->where('id', $po_id);

        $po = $this->ci->db->get(db_prefix() . 'co_request')->row();


        if (!$po) {
            return $fields;
        }

        $fields['{contact_firstname}'] =  $data->contact_firstname;
        $fields['{contact_lastname}'] =  $data->contact_lastname;
        $fields['{status_name}'] =  ($po->status == 2) ? 'approved' : 'rejected';
        $fields['{status_extra}'] =  ($po->status == 2) ? 'approval' : 'rejection';
        $fields['{changee_id}'] =  $po->pur_rq_code;
        $fields['{changee_name}'] =  $po->pur_rq_name;
        $fields['{project_name}'] =  get_project_name_by_id($po->project);
        $fields['{changee_request_title}'] = site_url('changee/vendors_portal/co_request/' . $po->id.'/'.$po->hash);
        $fields['{changee_request_link}'] = site_url('changee/vendors_portal/co_request/' . $po->id.'/'.$po->hash);

        return $fields;
    }
}
