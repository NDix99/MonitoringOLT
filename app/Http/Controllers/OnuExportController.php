<?php

namespace App\Http\Controllers;

use App\Models\Onu;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class OnuExportController extends Controller
{
    public function export(Request $request)
    {
        $query = Onu::with('olt');
        
        // Apply filters from request
        if ($request->has('search') && $request->search) {
            $query->where(function($q) use ($request) {
                $q->where('serial_number', 'like', '%' . $request->search . '%')
                  ->orWhere('onu_index', 'like', '%' . $request->search . '%')
                  ->orWhere('model', 'like', '%' . $request->search . '%');
            });
        }
        
        if ($request->has('olt_filter') && $request->olt_filter !== 'all') {
            $query->where('olt_id', $request->olt_filter);
        }
        
        if ($request->has('status_filter') && $request->status_filter !== 'all') {
            $query->where('status_text', $request->status_filter);
        }
        
        if ($request->has('signal_filter') && $request->signal_filter !== 'all') {
            switch ($request->signal_filter) {
                case 'good':
                    $query->where('rx_power', '>=', -27.50);
                    break;
                case 'warning':
                    $query->whereBetween('rx_power', [-28.00, -27.50]);
                    break;
                case 'critical':
                    $query->where('rx_power', '<', -28.00);
                    break;
                case 'other':
                    $query->whereIn('status_text', ['LOS', 'Unknown']);
                    break;
            }
        }
        
        $onus = $query->get();
        
        $filename = 'onus_export_' . date('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function() use ($onus) {
            $file = fopen('php://output', 'w');
            
            // CSV Headers
            fputcsv($file, [
                'OLT Name',
                'ONU Index', 
                'Serial Number',
                'Status',
                'RX Power (dBm)',
                'TX Power (dBm)',
                'Model',
                'Vendor',
                'Last Seen'
            ]);
            
            // CSV Data
            foreach ($onus as $onu) {
                fputcsv($file, [
                    $onu->olt->name ?? 'N/A',
                    $onu->onu_index,
                    $onu->serial_number,
                    $onu->status_text,
                    $onu->rx_power,
                    $onu->tx_power,
                    $onu->model,
                    $onu->vendor,
                    $onu->last_seen
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
}
