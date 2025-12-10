<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Seeder extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->helper('string');
    }

    public function run()
    {
        echo "<h1>Starting Seeding Process...</h1>";

        $this->seed_member();
        $this->seed_book();
        $this->seed_loan();

        echo "<h1>Seeding Completed!</h1>";
    }

    private function seed_member()
    {
        echo "<p>Seeding Member...</p>";
        // Check if member exists
        $memberNo = 'TEST001';
        $query = $this->db->get_where('members', array('MemberNo' => $memberNo));

        if ($query->num_rows() == 0) {
            $data = array(
                'MemberNo' => $memberNo,
                'Fullname' => 'Test User WhatsApp',
                'PlaceOfBirth' => 'Malang',
                'DateOfBirth' => '1990-01-01',
                'Address' => 'Jl. Test No. 123',
                'NoHp' => '08xxxxxx', // Default dummy, user should update this in DB if they want to test real WA
                'RegisterDate' => date('Y-m-d'),
                'EndDate' => date('Y-m-d', strtotime('+1 year')),
                'StatusAnggota_id' => 1, // Assuming 1 is active
            );
            $this->db->insert('members', $data);
            echo "Member created: $memberNo<br>";
        } else {
            echo "Member $memberNo already exists.<br>";
        }
    }

    private function seed_book()
    {
        echo "<p>Seeding Book...</p>";
        // 1. Seed Catalog
        $title = 'Belajar CodeIgniter 3';
        $query = $this->db->get_where('catalogs', array('Title' => $title));

        if ($query->num_rows() == 0) {
            $catalogData = array(
                'Title' => $title,
                'Author' => 'Antigravity',
                'Publisher' => 'Deepmind Press',
                'PublishYear' => '2024',
                'Worksheet_id' => 1, // Required by FK constraint
                'CreateDate' => date('Y-m-d H:i:s')
            );
            $this->db->insert('catalogs', $catalogData);
            $catalogId = $this->db->insert_id();
            echo "Catalog created: $title (ID: $catalogId)<br>";

            // 2. Seed Collection (Item)
            $barcode = 'B001';
            $collectionData = array(
                'Catalog_id' => $catalogId,
                'NomorBarcode' => $barcode,
                'NoInduk' => 'IND001',
                'TanggalPengadaan' => date('Y-m-d'),
                'Status_id' => 1, // Tersedia
                'CreateDate' => date('Y-m-d H:i:s')
            );
            $this->db->insert('collections', $collectionData);
            echo "Collection created: $barcode<br>";
        } else {
            echo "Book '$title' already exists.<br>";
        }
    }

    private function seed_loan()
    {
        echo "<p>Seeding Loan...</p>";

        // Get Member ID
        $member = $this->db->get_where('members', array('MemberNo' => 'TEST001'))->row();
        if (!$member) {
            echo "Member not found, skipping loan.<br>";
            return;
        }

        // Get Collection ID
        $collection = $this->db->get_where('collections', array('NomorBarcode' => 'B001'))->row();
        if (!$collection) {
            echo "Collection not found, skipping loan.<br>";
            return;
        }

        // Check if active loan exists for this item
        $this->db->select('*');
        $this->db->from('collectionloanitems');
        $this->db->where('Collection_id', $collection->ID);
        $this->db->where('LoanStatus', 'Loan');
        $query = $this->db->get();

        if ($query->num_rows() == 0) {
            // 1. Create Loan Header
            // Inlislite uses UUID/String for ID, so we must generate it manually
            $loanId = $this->generate_uuid();

            $loanHeader = array(
                'ID' => $loanId,
                'Member_id' => $member->ID,
                'LoanCount' => 1,
                'CreateDate' => date('Y-m-d H:i:s')
            );
            $this->db->insert('collectionloans', $loanHeader);
            // $loanId is already set, no need for insert_id()

            // 2. Create Loan Item (Detail)
            // Set DueDate to TODAY so it triggers the reminder immediately
            $loanItem = array(
                'CollectionLoan_id' => $loanId,
                'Collection_id' => $collection->ID,
                'member_id' => $member->ID,
                'LoanDate' => date('Y-m-d', strtotime('-7 days')),
                'DueDate' => date('Y-m-d'), // DUE TODAY!
                'LoanStatus' => 'Loan',
                'CreateDate' => date('Y-m-d H:i:s')
            );
            $this->db->insert('collectionloanitems', $loanItem);
            echo "Loan transaction created for Member " . $member->Fullname . "<br>";
        } else {
            echo "Active loan for this item already exists.<br>";
        }
    }

    private function generate_uuid()
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff)
        );
    }
}
