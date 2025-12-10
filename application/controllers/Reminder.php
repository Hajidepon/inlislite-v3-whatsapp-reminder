<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Reminder extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->library('Fonnte_lib');
        $this->load->helper('file');
    }

    public function process()
    {
        // Styling for Iframe
        echo "<style>body{font-family:sans-serif; padding:20px;}</style>";
        echo "<h3>⏳ Memulai Proses Reminder...</h3><hr>";

        // Log Path
        $log_file = APPPATH . 'logs/reminder_history.txt';
        $timestamp = date('Y-m-d H:i:s');
        $log_entry = "[$timestamp] START PROCESS";
        write_file($log_file, $log_entry . "\n", 'a+');

        // 1. Get Loans Due Today (or past due)
        $today = date('Y-m-d');

        $this->db->select('cli.ID as LoanItemID, cli.DueDate, m.Fullname, m.NoHp, cat.Title');
        $this->db->from('collectionloanitems cli');
        $this->db->join('members m', 'm.ID = cli.member_id');
        $this->db->join('collections c', 'c.ID = cli.Collection_id');
        $this->db->join('catalogs cat', 'cat.ID = c.Catalog_id');
        $this->db->where('cli.LoanStatus', 'Loan');
        $this->db->where('cli.DueDate <=', $today);

        $query = $this->db->get();
        $results = $query->result();

        if (empty($results)) {
            echo "<div style='color:green'>✅ Tidak ada pinjaman jatuh tempo hari ini.</div>";
            write_file($log_file, "[$timestamp] SKIP: No loans due.\n", 'a+');
            return;
        }

        echo "Found " . count($results) . " loans due.<br><br>";

        $success_count = 0;
        $fail_count = 0;

        foreach ($results as $row) {
            if (empty($row->NoHp)) {
                echo "⚠️ Skipping " . $row->Fullname . " (No Phone Number)<br>";
                continue;
            }

            // Pesan
            $message = "Halo " . $row->Fullname . ",\n\n";
            $message .= "Kami mengingatkan bahwa buku *'" . $row->Title . "'* yang Anda pinjam jatuh tempo pada tanggal *" . $row->DueDate . "*.\n";
            $message .= "Mohon segera dikembalikan ke perpustakaan.\n\n";
            $message .= "Terima kasih.";

            echo "Sending to <b>" . $row->Fullname . "</b> (" . $row->NoHp . ")... ";

            $response = $this->fonnte_lib->send($row->NoHp, $message);

            if ($response && isset($response['status']) && $response['status'] == true) {
                echo "<span style='color:green; font-weight:bold'>SUCCESS</span><br>";
                $success_count++;
            } else {
                echo "<span style='color:red; font-weight:bold'>FAILED</span> (";
                echo isset($response['detail']) ? $response['detail'] : 'Unknown Error';
                echo ")<br>";
                $fail_count++;
            }

            // Flush & Sleep
            flush();
            sleep(10);
        }

        $end_time = date('Y-m-d H:i:s');
        $summary = "[$end_time] FINISH. Sent: $success_count, Failed: $fail_count";
        write_file($log_file, $summary . "\n", 'a+');

        echo "<hr><h3>✅ Proses Selesai!</h3>";
        echo "<p>Berhasil: $success_count, Gagal: $fail_count</p>";
    }
}
