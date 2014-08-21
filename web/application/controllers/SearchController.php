<?php

class SearchController extends App_Controller_Action
{

    /**
     * Init
     *
     * @return void
     */
    public function init()
    {
        parent::init();
        $this->_acl->restrictToAdmin();
    }

    public function indexAction()
    {
        $query = $this->getRequest()->getParam( 'q', null );
        if ( null !== $query )
        {
            $this->view->query = $query;
            if ( false !== strpos( $query, 'project:' ) )
            {

                $this->view->type = 'project';
                $query = trim( str_replace( 'project:', '', $query ) );

                $model = new Application_Model_JobMapper();
                $select = $model->select();

                if ( false !== strpos( $query, 'status:' ) )
                {
                    $query = trim( str_replace( 'status:', '', $query ) );
                    $select->from( array( 'j' => 'jobs' ), array( '*' ) )
                        ->join( array(  'js' => 'lkp_job_statuses' ), 'j.status_id = js.id', array() )
                        ->where( 'js.name = ?', $query );
                }
                elseif( false !== strpos( $query, 'info:' ) )
                {
                    $query = trim( str_replace( 'info:', '', $query ) );
                    if ( 'completed' === strtolower( $query ) )
                    {
                        $select->from( array( 'j' => 'jobs' ), array( '*' ) )
                               ->join( array(  'js' => 'lkp_job_statuses' ), 'j.status_id = js.id', array() )
                               ->where( 'js.completed = ?', '1' );
                    }
                    elseif ( 'deleted' === strtolower( $query ) )
                    {
                        $select->where( 'deleted IS NOT NULL' );
                    }
                    elseif ( 'archived' === strtolower( $query ) )
                    {
                        $select->where( 'archived = ?', '1' );
                    }
                    elseif ( 'invoiced' === strtolower( $query ) )
                    {
                        $select->from( array( 'j' => 'jobs' ), array( '*' ) )
                               ->join( array(  'js' => 'lkp_job_statuses' ), 'j.status_id = js.id', array() )
                               ->where( 'js.invoiced = ?', '1' );
                    }
                }
                elseif ( is_numeric( $query ) )
                {
                    $select->where( 'id = ?', $query );
                }
                else
                {
                    $select->where( 'po_number LIKE ?', '%' . $query . '%' )
                        ->orWhere( 'title LIKE ? ',  '%' . $query . '%' );
                }

                if ( '' !== trim( $query ) )
                {
                    $results = $model->fetchAll( $select );
                }
                else
                {
                    $results = array();
                }
            }
            else
            {
                $this->view->type = 'audiojob';
                $model   = new Application_Model_AudioJobMapper();
                $results = $model->fetchAll( $model->select()->where( 'file_name LIKE ?', '%' . $query . '%' )->where( 'deleted IS NULL' ) );
            }

            $this->view->results = $results;
        }
    }
}