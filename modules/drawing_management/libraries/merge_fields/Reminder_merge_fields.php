<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Reminder_merge_fields extends App_merge_fields
{
    public function build()
    {
        return [
            [
                'name'      => 'Link',
                'key'       => '{link}',
                'available' => [
                    'drawing_management',
                ],
            ],
            [
                'name'      => 'Message',
                'key'       => '{message}',
                'available' => [
                    'drawing_management',
                ],
            ]
        ];
    }

    /**
     * Merge field for appointments
     * @param  mixed $attendance 
     * @return array
     */
    public function format($data)
    {        
        $fields['{link}'] = $data->link;
        $fields['{message}'] = $data->message;
        return $fields;
    }
}
