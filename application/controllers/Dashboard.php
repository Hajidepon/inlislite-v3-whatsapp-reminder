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

        // Show loans due up to 7 days from now
        $threshold = date('Y-m-d', strtotime('+7 days'));

        $this->db->select('m.Fullname, m.NoHp, cli.LoanDate, cli.DueDate, cat.Title');
        $this->db->from('collectionloanitems cli');
        $this->db->join('members m', 'm.ID = cli.member_id');
        $this->db->join('collections c', 'c.ID = cli.Collection_id');
        $this->db->join('catalogs cat', 'cat.ID = c.Catalog_id');
        $this->db->where('cli.LoanStatus', 'Loan');
        $this->db->where('cli.DueDate <=', $threshold);

        $query = $this->db->get();
        return array(
            'count' => $query->num_rows(),
            'list' => $query->result()
        );
    }

    private function get_logs()
    {
        $log_file = APPPATH . 'logs/reminder_history.txt';
        $parsed_logs = array();

        if (file_exists($log_file)) {
            $content = read_file($log_file);
            $lines = explode("\n", trim($content));
            $last_lines = array_slice($lines, -50); // Fetch last 50 logs

            foreach ($last_lines as $line) {
                if (empty(trim($line)))
                    continue;

                // Simple parser (Expected format: "[Timestamp] Message")
                if (preg_match('/\[(.*?)\] (.*)/', $line, $matches)) {
                    $timestamp = $matches[1];
                    $raw_msg = $matches[2];
                    $original_msg = $raw_msg; // Keep original for detail popup

                    $status = 'info';
                    $icon = 'info-circle';
                    $label = 'Info';
                    $detail = $original_msg;

                    // Categorize
                    if (strpos($raw_msg, 'START PROCESS') !== false) {
                        $raw_msg = "Memulai Proses Pengiriman...";
                        $status = 'primary';
                        $label = 'Sistem';
                    } elseif (strpos($raw_msg, 'Sending to') !== false) {
                        // Clean up message
                        $raw_msg = str_replace(['Sending to <b>', '</b>', '...'], ['', '', ''], $raw_msg);
                        $status = 'warning text-dark';
                        $icon = 'paper-plane';
                        $label = 'Proses';
                    } elseif (strpos($raw_msg, 'SUCCESS') !== false) {
                        // Individual Success
                        $status = 'success';
                        $icon = 'check-circle';
                        $raw_msg = "Pesan Terkirim";
                        $label = 'Berhasil';
                    } elseif (strpos($raw_msg, 'failed') !== false || strpos($raw_msg, 'Error') !== false) {
                        // Check if it's the Summary Line "FINISH. Sent: X, Failed: Y"
                        if (preg_match('/Sent: (\d+), Failed: (\d+)/', $raw_msg, $counts)) {
                            // This handles the Summary Logic in next block, but if it has 'failed' word
                            // Logic below catches it.
                        } else {
                            $status = 'danger';
                            $icon = 'times-circle';
                            $label = 'Gagal';
                        }
                    } elseif (strpos($raw_msg, 'SKIP') !== false) {
                        $status = 'secondary';
                        $raw_msg = "Dilewati";
                        $label = 'Skip';
                    }

                    // Special Logic for COMPLETION Summary
                    if (strpos($raw_msg, 'FINISH') !== false || strpos($raw_msg, 'COMPLETE') !== false) {
                        if (preg_match('/Sent: (\d+), Failed: (\d+)/', $raw_msg, $counts)) {
                            $sent = intval($counts[1]);
                            $failed = intval($counts[2]);

                            if ($failed > 0 && $sent > 0) {
                                $label = 'Sebagian';
                                $status = 'warning text-dark'; // Orange
                                $icon = 'exclamation-circle';
                                $raw_msg = "Selesai ($sent Berhasil, $failed Gagal)";
                            } elseif ($failed > 0 && $sent == 0) {
                                $label = 'Gagal';
                                $status = 'danger';
                                $icon = 'times-circle';
                                $raw_msg = "Selesai (Semua Gagal)";
                            } else {
                                $label = 'Berhasil';
                                $status = 'success';
                                $icon = 'check-circle';
                                $raw_msg = "Selesai ($sent Terkirim)";
                            }
                        } else {
                            $label = 'Berhasil';
                            $status = 'success';
                            $icon = 'flag-checkered';
                            $raw_msg = "Proses Selesai";
                        }
                    }

                    $parsed_logs[] = array(
                        'time' => date('d M H:i', strtotime($timestamp)),
                        'message' => $raw_msg, // Short/Friendly Message
                        'detail' => $original_msg, // Full Tecnhical Message
                        'status' => $status,
                        'icon' => $icon,
                        'label' => $label
                    );
                }
                // else: Skip unformatted lines to keep UI clean
            }
        }
        return array_reverse($parsed_logs);
    }

    public function clear_logs()
    {
        $log_file = APPPATH . 'logs/reminder_history.txt';
        if (file_exists($log_file)) {
            file_put_contents($log_file, ""); // Empty the file
        }
        redirect('dashboard?msg=cleared');
    }

    public function save_settings()
    {
        $token = $this->input->post('token');
        if ($token) {
            $this->update_env('FONNTE_TOKEN', $token);
            redirect('dashboard?msg=saved');
        }
    }

    private function update_env($key, $value)
    {
        $path = FCPATH . '.env';
        if (file_exists($path)) {
            $content = file_get_contents($path);
            // Regex to replace specific key
            $pattern = "/^$key=.*$/m";
            $replacement = "$key=\"$value\"";

            if (preg_match($pattern, $content)) {
                $content = preg_replace($pattern, $replacement, $content);
            } else {
                $content .= "\n$key=\"$value\"";
            }

            file_put_contents($path, $content);
        }
    }
}
