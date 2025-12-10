<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->helper('url');
        $this->load->helper('file');
    }

    public function index()
    {
        // 1. Get stats
        $stats = $this->get_loan_stats();

        // 2. Get logs
        $logs = $this->get_logs();

        $data = array(
            'count_due' => $stats['count'],
            'due_list' => $stats['list'],
            'logs' => $logs
        );

        $this->load->view('dashboard', $data);
    }

    private function get_loan_stats()
    {
        $today = date('Y-m-d');

        $this->db->select('m.Fullname, cli.DueDate, cat.Title');
        $this->db->from('collectionloanitems cli');
        $this->db->join('members m', 'm.ID = cli.member_id');
        $this->db->join('collections c', 'c.ID = cli.Collection_id');
        $this->db->join('catalogs cat', 'cat.ID = c.Catalog_id');
        $this->db->where('cli.LoanStatus', 'Loan');
        $this->db->where('cli.DueDate <=', $today);

        $query = $this->db->get();
        return array(
            'count' => $query->num_rows(),
            'list' => $query->result()
        );
    }

    private function get_logs()
    {
        $log_file = APPPATH . 'logs/reminder_history.txt';
        if (file_exists($log_file)) {
            $content = read_file($log_file);
            // Get last 5 lines for simple display
            $lines = explode("\n", trim($content));
            $last_lines = array_slice($lines, -5);
            return array_reverse($last_lines);
        }
        return array();
    }
}
