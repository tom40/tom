<?php

class StaffReportExportController extends App_Controller_Action
{

    /**
     * Typist pay logged
     * @var array
     */
    protected $_typistPayLogged = array();

    /**
     * Total
     * @var float
     */
    protected $_typistTotal;

    /**
     * Field count
     * @var int
     */
    protected $_fieldCount = 0;

    /**
     * Init
     *
     * @return void
     */
    public function init()
    {
        ini_set('memory_limit', '256M');
        $this->flashMessenger = $this->_helper->flashMessenger;
        parent::init();
        $this->_acl->restrictToSuperAdmin();
    }


    /**
     * Index action
     *
     * @return void
     */
    public function indexAction()
    {
        $this->_forward('download');
    }

    public function downloadAction()
    {
        $form = new Application_Form_StaffReport;
        $this->_assignStaff();

        if ($this->getRequest()->isPost())
        {
            $formData = $this->getRequest()->getPost();
            $data     = array(
                'client_id'       => $formData['client_id'],
                'status_id'       => $formData['status_id'],
                'date'            => array(
                    'start' => $formData['date_start'],
                    'end'   => $formData['date_end']
                ),
                'group_by'        => $formData['group_by'],
                'typists'         => (isset($formData['typists'])) ? $formData['typists'] : array(),
                'proofreaders'    => (isset($formData['proofreaders'])) ? $formData['proofreaders'] : array()
            );

            $audioJobMapper = new Application_Model_AudioJobMapper();
            $reportJobs     = $audioJobMapper->fetchJobsForStaffWorkReport($data);

            $csvData = array(
                array(
                    'audio_job_id'              => 'AUDIO JOB ID',
                    'client'                    => 'CLIENT',
                    'project_title'             => 'PROJECT TITLE',
                    'file_name'                 => 'FILE NAME',
                    'file_duration'             => 'FILE DURATION',
                    'typist_minutes_worked'     => 'TYPIST MINS WORKED',
                    'transcription_type'        => 'TRANSCRIPTION TYPE',
                    'turnaround_time'           => 'TURNAROUND TIME',
                    'speaker_numbers'           => 'NO of SPEAKERS',
                    'additional_services'       => 'ADDITIONAL SERVICES',
                    'typist_name'               => 'TYPIST NAME',
                    'typist_pay_grade'          => 'TYPIST PAY GRADE',
                    'typist_pay_rate'           => 'TYPIST PAY RATE',
                    'typist_assigned_date'      => 'TYPIST ASSIGEND DATE',
                    'typist_due_date'           => 'TYPIST DUE DATE',
                    'typist_comments'           => 'TYPIST COMMENTS',
                    'typist_file_pay'           => 'TYPIST PAY FOR FILE',
                    'proofreader_name'          => 'PROOFREADER NAME',
                    'proofreader_assigned_date' => 'PROOFREADER ASSIGNED DATE',
                    'proofreader_due_date'      => 'PROOFREADER DUE DATE',
                    'proofreader_comments'      => 'PROOFREADER COMMENTS',
                )
            );

            $this->_fieldCount = count($csvData[0]);

            if (!empty($reportJobs))
            {
                $newLineCheck = null;
                $staffTotal   = 0;
                foreach($reportJobs as $row)
                {
                    $typistFilePay = $this->_getTypistPay($row);
                    if ('-' !== $typistFilePay)
                    {
                        $this->_typistTotal += $typistFilePay;
                    }

                    if ('typists' === $formData['group_by'])
                    {
                        if (null !== $newLineCheck && $row['t_id'] !== $newLineCheck)
                        {
                            $csvData[] = $this->_emptyRow();
                            $csvData[] = $this->_getTypistIndividualTotal($staffTotal);
                            $csvData[] = $this->_emptyRow();
                            $staffTotal = 0;
                        }
                        if ('-' !== $typistFilePay)
                        {
                            $staffTotal += $typistFilePay;
                        }
                        $newLineCheck = $row['t_id'];
                    }
                    else
                    {
                        if (null !== $newLineCheck && $row['p_id'] !== $newLineCheck)
                        {
                            $csvData[] = $this->_emptyRow();
                            $csvData[] = $this->_getProofreaderIndividualTotal();
                            $csvData[] = $this->_emptyRow();
                        }
                        $newLineCheck = $row['p_id'];
                    }

                    $csvData[] = array(
                        'audio_job_id'              => $row['audio_job_id'],
                        'client'                    => $row['client_name'],
                        'project_title'             => $row['project_title'],
                        'file_name'                 => $row['file_name'],
                        'file_duration'             => $this->_getFileDuration($row['file_length']),
                        'typist_minutes_worked'     => $this->_getTypistWorkedTime($row),
                        'transcription_type'        => $row['transcription_type'],
                        'turnaround_time'           => $row['turnaround_time'],
                        'speaker_numbers'           => $row['speaker_numbers'],
                        'additional_services'       => $row['additional_services'],
                        'typist_name'               => $row['t_name'],
                        'typist_pay_grade'          => $row['t_paygrade'],
                        'typist_pay_rate'           => $this->_getPayPerMinuteFormat($row),
                        'typist_assigned_date'      => $this->_standardiseDate($row['t_assigned_date']),
                        'typist_due_date'           => $this->_standardiseDate($row['t_due_date']),
                        'typist_comments'           => str_replace('"', "'", $row['t_comment']),
                        'typist_file_pay'           => '£' . $typistFilePay,
                        'proofreader_name'          => $row['p_name'],
                        'proofreader_assigned_date' => $this->_standardiseDate($row['p_assigned_date']),
                        'proofreader_due_date'      => $this->_standardiseDate($row['p_due_date']),
                        'proofreader_comments'      => str_replace('"', "'", $row['p_comment']),
                    );
                }

                if ('typists' === $formData['group_by'])
                {
                    $csvData[] = $this->_emptyRow();
                    $csvData[] = $this->_getTypistIndividualTotal($staffTotal);
                    $csvData[] = $this->_emptyRow();
                }
                else
                {
                    $csvData[] = $this->_emptyRow();
                    $csvData[] = $this->_getProofreaderIndividualTotal();
                    $csvData[] = $this->_emptyRow();
                }

                $csvData[] = $this->_getReportTypistTotalPayRow();
                $csvData[] = $this->_getReportProofreaderTotalPayRow();

                $csvContent = '';
                foreach ($csvData as $item)
                {
                    $csvContent .= '"' . utf8_decode(implode('","', $item)) . "\" \n";
                }

                $fileName = 'staff-work-export-' . date('d-m-Y-h-i') . '.csv';

                header("Content-Type: application/csv; charset=utf-8; encoding=utf-8");
                header("Content-Disposition: attachment;Filename=" . $fileName);
                header("Pragma: public");
                header("Cache-Control: public");

                echo $csvContent;

                exit();
            }
            else
            {
                $this->flashMessenger->addMessage(array('warning' => 'There are no audio job matching your criteria'));
            }
            $form->populate($formData);

        }
        $this->view->form = $form;
        $this->render('index');
    }

    protected function _emptyRow()
    {
        return array_fill(0, $this->_fieldCount, '');
    }

    /**
     * Get pay per minute format
     *
     * @param array $row Data Row
     *
     * @return string
     */
    protected function _getPayPerMinuteFormat($row)
    {
        return number_format(($this->_resolveTypistPayrate($row) / 100), 2);
    }

    /**
     * Resolve typist pay
     *
     * @param array $row Data Row
     *
     * @return int
     */
    protected function _resolveTypistPayrate($row)
    {
        $payRate = $row['t_payrate'];

        if ( '1' == $row['t_substandard'] )
        {
            $payRate = Application_Model_TypistPayrateMapper::SUBSTANDARD_PAYRATE * $payRate;
        }
        elseif( '1' == $row['t_replacement'] )
        {
            $payRate = Application_Model_TypistPayrateMapper::REPLACEMENT_PAYRATE * $payRate;
        }

        return $payRate;
    }

    /**
     * Get pay info
     *
     * @param array $row Data row
     *
     * @return float
     */
    protected function _getTypistPay($row)
    {
        $key = 'typist' . $row['t_id'] . 'audiojob' . $row['audio_job_id'];

        if (isset($this->_typistPayLogged[$key]))
        {
            return '-';
        }
        else
        {

            $minutes                      = $this->_getTypistWorkedTime($row);
            $this->_typistPayLogged[$key] = true;
            return number_format($minutes * $this->_resolveTypistPayrate($row) / 100, 2);
        }
    }

    protected function _getTypistWorkedTime($row)
    {
        if (null !== $row['t_start'])
        {
            $minutes = $row['t_end'] - $row['t_start'];
        }
        else
        {
            $minutes = $this->_getMinutesFromFileTime($row['file_length']);
        }
        return $minutes;
    }

    protected function _getMinutesFromFileTime($fileLength)
    {
        return Application_Model_AudioJobMapper::getMinutesFromFileTime( $fileLength );
    }

    protected function _getFileDuration($lengthSeconds, $display = true)
    {
        return Application_Model_AudioJobMapper::getFileDuration( $lengthSeconds, $display );
    }

    /**
     * Standardise dates
     *
     * @param string $date Date string
     *
     * @return string
     */
    protected function _standardiseDate($date)
    {
        $time = strtotime($date);
        if ($time)
        {
            return date('d.m.Y', strtotime($date));
        }
        return '-';
    }

    /**
     *
     */
    protected function _getTypistIndividualTotal($total)
    {
        $totalRow   = array_fill(0, ($this->_fieldCount - 2), '');
        $totalRow[] = 'Typist Pay';
        $totalRow[] = '£' . number_format($total, 2);
        return $totalRow;
    }

    /**
     *
     */
    protected function _getProofreaderIndividualTotal()
    {
        $totalRow   = array_fill(0, ($this->_fieldCount - 2), '');
        $totalRow[] = 'Proofreader Pay';
        $totalRow[] = 'n/a';
        return $totalRow;
    }

    /**
     *
     */
    protected function _getReportTypistTotalPayRow()
    {
        $totalRow   = array_fill(0, ($this->_fieldCount - 2), '');
        $totalRow[] = 'Total Typist Pay';
        $totalRow[] = '£' . number_format($this->_typistTotal, 2);
        return $totalRow;
    }

    /**
     *
     */
    protected function _getReportProofreaderTotalPayRow()
    {
        $totalRow   = array_fill(0, ($this->_fieldCount - 2), '');
        $totalRow[] = 'Total Proofreader Pay';
        $totalRow[] = 'n/a';
        return $totalRow;
    }

    /**
     * Assign staff to the view
     *
     * @return void
     */
    protected function _assignStaff()
    {
        $groupModel               = new Application_Model_Group();
        $this->view->typists      = $groupModel->getTypists(true);
        $this->view->proofreaders = $groupModel->getProofreaders(true);
    }
}