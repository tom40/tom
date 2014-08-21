<?php

class ReportExportController extends App_Controller_Action
{

    /**
     * Report total cost
     * @var float
     */
    protected $_reportTotalCost = 0;

    /**
     * Init
     *
     * @return void
     */
    public function init()
    {
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

    /**
     * Generates downloadable CSV file of completed jobs
     *
     * @return void
     */
    public function downloadAction()
    {
        ini_set('memory_limit','800M');
        $form = new Application_Form_JobReport();
        if ($this->getRequest()->isPost())
        {
            $formData = $this->getRequest()->getPost();

            $data = array(
                'client_id' => $formData['client_id'],
                'status_id' => $formData['status_id'],
                'date'      => array(
                    'start' => $formData['date_start'],
                    'end'   => $formData['date_end']
                )
            );

            $jobMapper  = new Application_Model_JobMapper();
            $reportJobs = $jobMapper->fetchJobsForReport( $data );

            $heading = array
            (
                'audio_job_id'     => 'AUDIO JOB ID',
                'job_ref'          => 'JOB REF',
                'job_title'        => 'JOB TITLE',
                'job_status'       => 'JOB STATUS',
                'start_date'       => 'START DATE',
                'upload_date'      => 'FILE UPLOADED DATE',
                'client'           => 'CLIENT',
                'contact_name'     => 'CONTACT NAME',
                'file_name'        => 'FILE NAME',
                'service_groups'   => 'CLIENT SERVICE GROUPS',
                'type'             => 'TYPE',
                'additional'       => 'ADDITIONAL SERVICES',
                'turnaround'       => 'TURNAROUND',
                'speaker_numbers'  => 'SPEAKER NUMBERS',
                'file_due_date'    => 'FILE DUE DATE',
                'project_due_date' => 'PROJECT DUE DATE',
                'for_billing'      => 'FOR BILLING',
                'billing_rate'     => 'BILLING RATE',
                'per_min_cost'     => 'PER MIN COST',
                'sub_total'        => 'SUB TOTAL',
                'project_discount' => 'PROJECT DISCOUNT',
                'poor_audio'       => 'POOR AUDIO',
                'discount'         => 'AUDIO JOB DISCOUNT',
                'total_cost'       => 'TOTAL COST'
            );

            $csvArray   = array();
            $csvArray[] = $heading;

            $reportTotal = 0;
            if ( count( $reportJobs ) > 0 )
            {
                foreach( $reportJobs as $job )
                {
                    $audioJobs    = $job->getAudioJobs( false );
                    $adHocCharges = $job->getAdHocCharges();
                    foreach ( $audioJobs as $audioJob )
                    {
                        $csvArray[] = $this->_buildAudioJobRow( $audioJob, $job );

                        if ( $audioJob->hasChildren() )
                        {
                            foreach ( $audioJob->getChildren() as $child )
                            {
                                $csvArray[] = $this->_buildAudioJobRow( $child, $job, true );
                            }
                        }
                    }

                    if ( $job->hasAdHocCharges() )
                    {
                        foreach ( $adHocCharges as $adHocCharge )
                        {
                            $adHocCharge = array(
                                'AD HOC',
                                $adHocCharge->description,
                                $adHocCharge->price
                            );
                            $csvArray[] = array_splice( $adHocCharge, 2, array_pad( array(), count( $heading ), '-' ) );
                        }
                    }
                    $jobTotal = array(
                        $job->po_number . ' SUB TOTAL',
                        "£" . number_format( $job->getPrice(), 2 )
                    );

                    if ( count( $audioJobs ) + count( $adHocCharges ) > 0 )
                    {
                        $csvArray[]  = array_pad( $jobTotal, -1 * abs( count( $heading ) ), '' );
                    }
                    $reportTotal += $job->getPrice();
                }

                $reportTotalRow = array(
                    'Report Total',
                    "£" . number_format( $reportTotal, 2 )
                );

                $csvArray[] = array_pad( $reportTotalRow, -1 * abs( count( $heading ) ), '' );

                $fileName = 'invoice-export-' . date('d-m-Y-h-i') . '.csv';
                header("Content-Type: application/csv; charset=utf-8; encoding=utf-8");
                header("Content-Disposition: attachment;Filename=" . $fileName);
                header("Pragma: public");
                header("Cache-Control: public");
                foreach ($csvArray as $item)
                {
                    echo '"' . utf8_decode( implode( '","', $item ) ) . "\" \n";
                }
                exit();
            }
            else
            {
                $this->flashMessenger->addMessage( array( 'warning' => 'There are no jobs matching your criteria' ) );
            }
            $form->populate($formData);
        }

        $this->view->form = $form;
        $this->render('index');
    }

    /**
     * Build Audio job row
     *
     * @param Application_Model_AudioJob_Row $audioJob Audio job row
     * @param Application_Model_Job_Row      $job      Job row
     * @param bool                           $isChild  Is audio job a child
     *
     * @return array
     */
    public function _buildAudioJobRow( $audioJob, $job, $isChild = false )
    {
        $rowArray = array(
            'audio_job_id'     => $audioJob->id,
            'job_ref'          => $job->po_number,
            'job_title'        => $job->title,
            'job_status'       => $job->getStatus()->name,
            'start_date'       => $this->_standardiseDate( $job->job_start_date ),
            'upload_date'      => $this->_standardiseDate( $audioJob->created_date ),
            'client'           => $job->getClient()->name,
            'contact_name'     => $job->getPrimaryUser()->name,
            'file_name'        => $audioJob->file_name,
            'service_groups'   => $job->getClient()->getServiceGroupsString(),
            'type'             => $audioJob->getCorrectServiceObject()->name,
            'additional'       => $audioJob->getPriceModifierString(),
            'turnaround'       => $audioJob->getTurnaroundTime()->name,
            'speaker_numbers'  => $audioJob->getSpeakerNumber()->name,
            'file_due_date'    => $this->_standardiseDate( $audioJob->client_due_date ),
            'project_due_date' => $this->_standardiseDate( $job->job_due_date )
        );

        if ( $isChild )
        {
            $priceArray = array(
                'for_billing'      => '-',
                'billing_rate'     => '-',
                'per_min_cost'     => '-',
                'sub_total'        => '-',
                'project_discount' => '-',
                'poor_audio'       => '(' . $audioJob->getPoorAudioPercentage() . '%)',
                'discount'         => '(' . $audioJob->audio_job_discount . '%)',
                'total_cost'       => '-',
            );
        }
        else
        {
            $priceArray = array(
                'for_billing'      => $audioJob->getHours(),
                'billing_rate'     => $audioJob->rate * 4,
                'per_min_cost'     => $audioJob->getPricePerMinute(),
                'sub_total'        => "£" . number_format( $audioJob->calculatePriceWithChildren( false, false ), 2 ),
                'project_discount' => $job->getDiscount() . '%',
                'poor_audio'       => $audioJob->getPoorAudioPercentage() . '%',
                'discount'         => $audioJob->audio_job_discount . '%',
                'total_cost'       => "£" . number_format( $audioJob->calculatePriceWithChildren( $job->getDiscount() ), 2 )
            );
        }

        $rowArray = $rowArray + $priceArray;

        return $rowArray;
    }

    /**
     * Standardise dates
     *
     * @param string $date Human readable date string
     *
     * @return string
     */
    protected function _standardiseDate( $date )
    {
        return date( 'd.m.Y', strtotime( $date ) );
    }

}

