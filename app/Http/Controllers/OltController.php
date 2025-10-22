<?php

namespace App\Http\Controllers;

use App\Models\Olt;
use App\Services\OltManagementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use FreeDSx\Snmp\SnmpClient;
use FreeDSx\Snmp\Oid;

class OltController extends Controller
{

    public function index()
    {
        $olts = Olt::withCount('onus')->latest()->paginate(20);
        return view('admin.olts', compact('olts'));
    }

    public function create()
    {
        return view('admin.olt-create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'ip_address' => 'required|ip',
            'community_string' => 'required|string|max:255',
            'snmp_port' => 'required|integer|min:1|max:65535',
            'snmp_version' => 'required|integer|in:1,2,3',
            'polling_interval' => 'required|integer|min:30|max:3600',
            'description' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        Olt::create([
            'name' => $request->name,
            'ip_address' => $request->ip_address,
            'community_string' => $request->community_string,
            'snmp_port' => $request->snmp_port,
            'snmp_version' => $request->snmp_version,
            'polling_interval' => $request->polling_interval,
            'is_active' => $request->has('is_active'),
            'description' => $request->description,
        ]);

        return redirect()->route('admin.olts.index')
            ->with('success', 'OLT device added successfully.');
    }

    public function edit(Olt $olt)
    {
        return view('admin.olt-edit', compact('olt'));
    }

    public function update(Request $request, Olt $olt)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'ip_address' => 'required|ip',
            'community_string' => 'required|string|max:255',
            'snmp_port' => 'required|integer|min:1|max:65535',
            'snmp_version' => 'required|integer|in:1,2,3',
            'polling_interval' => 'required|integer|min:30|max:3600',
            'description' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $olt->update([
            'name' => $request->name,
            'ip_address' => $request->ip_address,
            'community_string' => $request->community_string,
            'snmp_port' => $request->snmp_port,
            'snmp_version' => $request->snmp_version,
            'polling_interval' => $request->polling_interval,
            'is_active' => $request->has('is_active'),
            'description' => $request->description,
        ]);

        return redirect()->route('admin.olts.index')
            ->with('success', 'OLT device updated successfully.');
    }

    public function destroy(Olt $olt)
    {
        $olt->delete();
        return redirect()->route('admin.olts.index')
            ->with('success', 'OLT device deleted successfully.');
    }

    public function toggle(Olt $olt)
    {
        $olt->update(['is_active' => !$olt->is_active]);
        
        return redirect()->back()->with('success', 
            'OLT ' . ($olt->is_active ? 'activated' : 'deactivated') . ' successfully.'
        );
    }

    public function testSnmp(Olt $olt)
    {
        try {
            $snmp = new SnmpClient([
                'host' => $olt->ip_address,
                'community' => $olt->community_string,
                'version' => $olt->snmp_version == 1 ? 1 : 2,
                'port' => $olt->snmp_port,
                'timeout' => 5,
            ]);

            $sysDescr = $snmp->getValue(new Oid('1.3.6.1.2.1.1.1.0'));
            
            return response()->json([
                'success' => true,
                'message' => 'SNMP connection successful',
                'system_description' => $sysDescr
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'SNMP connection failed: ' . $e->getMessage()
            ], 400);
        }
    }

    public function testSsh(Request $request, Olt $olt)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Username and password are required'
            ], 400);
        }

        try {
            $ssh = new \phpseclib3\Net\SSH2($olt->ip_address, 22);
            
            if ($ssh->login($request->username, $request->password)) {
                $output = $ssh->exec('show version');
                
                return response()->json([
                    'success' => true,
                    'message' => 'SSH connection successful',
                    'output' => trim($output)
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'SSH login failed: Invalid credentials'
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'SSH connection failed: ' . $e->getMessage()
            ], 400);
        }
    }
}
