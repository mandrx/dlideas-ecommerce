<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pages extends MY_Controller
{
    private function _render($view, $data = [])
    {
        $data['content_view'] = $view;
        $this->load->view('layouts/main', $data);
    }

    private function _page($view, $title)
    {
        $this->_render('pages/' . $view, ['page_title' => $title]);
    }

    public function help_center()    { $this->_page('help_center',    'Help Center'); }
    public function returns()        { $this->_page('returns',        'Returns & Refunds'); }
    public function contact()        { $this->_page('contact',        'Contact Us'); }
    public function vendor_guidelines() { $this->_page('vendor_guidelines', 'Vendor Guidelines'); }
    public function press()          { $this->_page('press',          'Press'); }
    public function careers()        { $this->_page('careers',        'Careers'); }
    public function trust_safety()   { $this->_page('trust_safety',   'Trust & Safety'); }
    public function our_story()      { $this->_page('our_story',      'Our Story'); }

    public function contact_submit()
    {
        if ($this->input->server('REQUEST_METHOD') !== 'POST') {
            redirect('contact');
        }

        $this->load->library('form_validation');
        $this->form_validation->set_rules('name',    'Name',    'required|max_length[150]');
        $this->form_validation->set_rules('email',   'Email',   'required|valid_email');
        $this->form_validation->set_rules('message', 'Message', 'required|max_length[2000]');

        if (!$this->form_validation->run()) {
            $this->session->set_flashdata('error', validation_errors(' ', ' '));
            redirect('contact');
            return;
        }

        $this->load->model('Contact_model');
        $saved = $this->Contact_model->save([
            'name'       => $this->input->post('name'),
            'email'      => $this->input->post('email'),
            'subject'    => $this->input->post('subject') ?: 'General Enquiry',
            'message'    => $this->input->post('message'),
            'ip_address' => $this->input->ip_address(),
        ]);

        if ($saved) {
            $this->session->set_flashdata('success', "Thanks! We'll get back to you within 1–2 business days.");
        } else {
            $this->session->set_flashdata('error', 'Something went wrong. Please try again.');
        }

        redirect('contact');
    }
}
