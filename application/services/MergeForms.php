<?php

namespace app\services;

class MergeForms
{
    /**
     * @var int
     */
    protected $primaryFormId;

    /**
     * @var array
     */
    protected $ids;

    /**
     * @var int|null
     */
    protected $status;

    /**
     * CI Instance
     */
    protected $ci;

    /**
     * Initiate new MergeForms class
     *
     * @param int $primaryFormId
     * @param array $ids
     */
    public function __construct($primaryFormId, $ids)
    {
        $this->primaryFormId = $primaryFormId;
        $this->ids             = $ids;
        $this->ci              = &get_instance();
    }

    /**
     * Merge the forms into the primary form
     *
     * @return bool
     */
    public function merge()
    {
        $replies = $this->convertToMergeReplies(
            $this->getFormsToMerge()
        );

        $merged = 0;
        $this->ci->db->trans_begin();

        try {
            foreach ($replies as $reply) {
                if ($this->mergeInPrimaryForm($reply)) {
                    if ($reply['merge_type'] === 'form') {
                        $this->markFormAsMerged($reply);
                    }

                    $merged++;
                }
            }

            if ($this->status && $merged > 0) {
                $this->ci->db->set('status', $this->status)
                    ->where('formid', $this->primaryFormId)
                    ->update('forms');
            }

            $this->ci->db->trans_commit();
        } catch (Exception $e) {
            $this->ci->db->trans_rollback();
        }

        return $merged > 0;
    }

    /**
     * After merge, change the primary form status to the given status
     *
     * @param  int $status
     *
     * @return $this
     */
    public function markPrimaryFormAs($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Merge the given reply into the primary form
     *
     * @param  array $reply
     *
     * @return bool
     */
    protected function mergeInPrimaryForm($reply)
    {
        $result = $this->ci->db->insert('form_replies', [
            'formid'  => $this->primaryFormId,
            'userid'    => $reply['userid'],
            'contactid' => $reply['contactid'],
            'name'      => $reply['name'],
            'email'     => $reply['email'],
            'date'      => $reply['date'],
            'message'   => $reply['message'],
            'admin'     => $reply['admin'],
        ]);

        $replyId = $this->ci->db->insert_id();

        if (count($reply['attachments']) > 0) {
            $this->moveAttachments($reply['attachments'], $replyId);
        }

        return $result;
    }

    /**
     * Get the forms to be merged into the primary form
     *
     * @return array
     */
    protected function getFormsToMerge()
    {
        $forms = $this->ci->db->where_in('formid', $this->ids)
            ->order_by('formid', 'ASC')
            ->get('forms')
            ->result_array();

        return array_map(function ($form) {
            return array_merge($form, [
                'merge_type'  => 'form',
                'attachments' => $this->getAttachments($form['formid']),
                'replies'     => $this->getReplies($form['formid']),
            ]);
        }, $this->removeAlreadyMergedForms($forms));
    }

    /**
     * Get attachments for the merge
     *
     * @param  int $id
     * @param  int|null $replyId
     *
     * @return array
     */
    protected function getAttachments($id, $replyId = null)
    {
        return $this->ci->forms_model->get_form_attachments($id, $replyId);
    }

    /**
     * Remove the already merged forms from the given forms list
     *
     * @param  array $forms
     *
     * @return array
     */
    protected function removeAlreadyMergedForms($forms)
    {
        return array_values(
            array_filter($forms, function ($form) {
                return $form['merged_form_id'] === null;
            })
        );
    }

    /**
     * Mark the form as merged
     *
     * @param  array $form
     *
     * @return void
     */
    protected function markFormAsMerged($form)
    {
        $subject = strpos($form['subject'], '[MERGED]') !== false ?
                    $form['subject'] :
                    $form['subject'] . ' [MERGED]';

        $this->ci->db->set('merged_form_id', $this->primaryFormId)
                        ->set('subject', $subject)
                        ->set('status', 5)
                        ->where('formid', $form['formid'])
                        ->update('forms');
    }

    /**
       * Get the replies for merging for the given form
       *
       * @param  int $id
       *
       * @return array
       */
    protected function getReplies($id)
    {
        $this->ci->db->where('formid', $id);
        $replies = $this->ci->db->get('form_replies')->result_array();

        return array_map(function ($reply) use ($id) {
            return array_merge($reply, [
                'merge_type'  => 'reply',
                'attachments' => $this->getAttachments($id, $reply['id']),
            ]);

            return $reply;
        }, $replies);
    }

    /**
     * Convert the given forms with replies to replies for ready for merging
     *
     * @param  array $forms
     *
     * @return array
     */
    protected function convertToMergeReplies($forms)
    {
        $replies = [];

        foreach ($forms as $form) {
            $formReplies = $form['replies'];
            unset($form['replies']);
            $replies = array_merge($replies, [$form], $formReplies);
        }

        return $replies;
    }

    /**
     * Move the given attachment from merged form/reply to the new reply
     *
     * @param  array $attachment
     * @param  int $replyId
     *
     * @return void
     */
    protected function moveAttachments($attachments, $replyId)
    {
        $formsUploadPath = get_upload_path_by_type('form');
        $primaryFormPath = $formsUploadPath . $this->primaryFormId . DIRECTORY_SEPARATOR;
        _maybe_create_upload_path($primaryFormPath);

        foreach ($attachments as $attachment) {
            $filePath = $formsUploadPath . $attachment['formid'] . DIRECTORY_SEPARATOR . $attachment['file_name'];

            $newFilename = unique_filename($primaryFormPath, $attachment['file_name']);
            $newPath     = $primaryFormPath . $newFilename;

            if (xcopy($filePath, $newPath)) {
                $this->ci->db->insert('form_attachments', [
                    'formid'  => $this->primaryFormId,
                    'replyid'   => $replyId,
                    'file_name' => $newFilename,
                    'filetype'  => $attachment['filetype'],
                    'dateadded' => $attachment['dateadded'],
                ]);

                $this->ci->forms_model->delete_form_attachment($attachment['id']);
            }
        }
    }
}
