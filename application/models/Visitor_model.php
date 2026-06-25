<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Visitor_model extends CI_Model
{
    private $_bot_patterns = ['bot', 'crawl', 'spider', 'slurp', 'mediapartners'];

    public function log_visit($ip, $uri, $ua, $user_id = null)
    {
        $ip_row = $this->_find_or_create_ip($ip);
        $is_bot = $this->_detect_bot($ua);

        $this->db->insert('visitor_logs', [
            'ip_id'      => $ip_row->id,
            'uri'        => substr($uri, 0, 500),
            'user_agent' => substr($ua, 0, 500),
            'is_bot'     => $is_bot ? 1 : 0,
            'user_id'    => $user_id ?: null,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    private function _find_or_create_ip($ip)
    {
        $row = $this->db->get_where('visitor_ips', ['ip_address' => $ip])->row();
        if ($row) {
            return $row;
        }

        $country = $this->_resolve_country($ip);
        $this->db->insert('visitor_ips', [
            'ip_address'   => $ip,
            'country_code' => $country['code'],
            'country_name' => $country['name'],
            'resolved_at'  => date('Y-m-d H:i:s'),
        ]);
        return $this->db->get_where('visitor_ips', ['id' => $this->db->insert_id()])->row();
    }

    private function _resolve_country($ip)
    {
        $url  = 'http://ip-api.com/json/' . urlencode($ip) . '?fields=countryCode,country';
        $ctx  = stream_context_create(['http' => ['timeout' => 1]]);
        $resp = @file_get_contents($url, false, $ctx);

        if ($resp === false) {
            return ['code' => null, 'name' => null];
        }

        $data = json_decode($resp, true);
        if (!is_array($data) || empty($data['countryCode'])) {
            return ['code' => null, 'name' => null];
        }

        return [
            'code' => $data['countryCode'],
            'name' => $data['country'] ?? null,
        ];
    }

    private function _detect_bot($ua)
    {
        $ua_lower = strtolower($ua);
        foreach ($this->_bot_patterns as $pattern) {
            if (strpos($ua_lower, $pattern) !== false) {
                return true;
            }
        }
        return false;
    }

    public function get_stats()
    {
        $total  = $this->db->count_all('visitor_logs');
        $unique = $this->db->count_all('visitor_ips');
        $today  = $this->db
            ->where('DATE(created_at)', date('Y-m-d'))
            ->count_all_results('visitor_logs');

        return (object) [
            'total_visits' => $total,
            'unique_ips'   => $unique,
            'visits_today' => $today,
        ];
    }

    public function get_top_countries($limit = 5)
    {
        return $this->db
            ->select('vi.country_name, COUNT(vl.id) AS visit_count')
            ->from('visitor_logs vl')
            ->join('visitor_ips vi', 'vi.id = vl.ip_id')
            ->where('vi.country_name IS NOT NULL')
            ->group_by('vi.country_name')
            ->order_by('visit_count', 'DESC')
            ->limit($limit)
            ->get()
            ->result();
    }

    public function get_logs($filters = [], $limit = 25, $offset = 0)
    {
        $this->_apply_log_filters($filters);
        return $this->db
            ->select('vl.id, vl.uri, vl.user_agent, vl.is_bot, vl.user_id, vl.created_at, vi.ip_address, vi.country_name, vi.country_code')
            ->from('visitor_logs vl')
            ->join('visitor_ips vi', 'vi.id = vl.ip_id')
            ->order_by('vl.created_at', 'DESC')
            ->limit($limit, $offset)
            ->get()
            ->result();
    }

    public function count_logs($filters = [])
    {
        $this->_apply_log_filters($filters);
        return $this->db
            ->from('visitor_logs vl')
            ->join('visitor_ips vi', 'vi.id = vl.ip_id')
            ->count_all_results();
    }

    private function _apply_log_filters($filters)
    {
        if (!empty($filters['date_from'])) {
            $this->db->where('DATE(vl.created_at) >=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $this->db->where('DATE(vl.created_at) <=', $filters['date_to']);
        }
        if (!empty($filters['country'])) {
            $this->db->where('vi.country_code', $filters['country']);
        }
        if ($filters['bot'] === '1') {
            $this->db->where('vl.is_bot', 1);
        } elseif ($filters['bot'] === '0') {
            $this->db->where('vl.is_bot', 0);
        }
    }
}
