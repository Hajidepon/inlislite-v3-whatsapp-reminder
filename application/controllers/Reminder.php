<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Reminder extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->library('Fonnte_lib');
    }

    public function process()
    {
        echo "<h1>Starting Reminder Process...</h1>";

        // 1. Get Loans Due Today (or past due) that are still active
        $today = date('Y-m-d');

        $this->db->select('
            cli.ID as LoanItemID,
            cli.DueDate,
            m.Fullname,
            m.NoHp,
            cat.Title
        ');
        $this->db->from('collectionloanitems cli');
        $this->db->join('members m', 'm.ID = cli.member_id');
        $this->db->join('collections c', 'c.ID = cli.Collection_id');
        $this->db->join('catalogs cat', 'cat.ID = c.Catalog_id');
        $this->db->where('cli.LoanStatus', 'Loan');
        $this->db->where('cli.DueDate <=', $today); // Remind for today and overdue

        $query = $this->db->get();
        $results = $query->result();

        if (empty($results)) {
            echo "No loans due today.<br>";
            return;
        }

        echo "Found " . count($results) . " loans due.<br>";

        foreach ($results as $row) {
            if (empty($row->NoHp)) {
                echo "Skipping " . $row->Fullname . " (No Phone Number)<br>";
                continue;
            }

            $message = "Halo " . $row->Fullname . ",\n\n";
            $message .= "Kami mengingatkan bahwa buku *'" . $row->Title . "'* yang Anda pinjam jatuh tempo pada tanggal *" . $row->DueDate . "*.\n";
            $message .= "Mohon segera dikembalikan ke perpustakaan.\n\n";
            $message .= "Terima kasih.";

            echo "Sending to " . $row->Fullname . " (" . $row->NoHp . ")... ";

            $response = $this->fonnte_lib->send($row->NoHp, $message);

            if ($response && isset($response['status']) && $response['status'] == true) {
                echo "SUCCESS<br>";
            } else {
                echo "FAILED (";
                echo isset($response['detail']) ? $response['detail'] : 'Unknown Error';
                echo ")<br>";
            }

            // Add delay to avoid WhatsApp blocking (10 seconds)
            flush(); // Force output to browser immediately
            sleep(10);
        }

        echo "<h1>Process Completed!</h1>";
    }
}
